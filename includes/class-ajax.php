<?php
if (!defined('ABSPATH')) exit;

class Salon_Booking_Ajax {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_ajax_get_available_times', array($this, 'get_available_times'));
        add_action('wp_ajax_nopriv_get_available_times', array($this, 'get_available_times'));
        
        add_action('wp_ajax_submit_booking', array($this, 'submit_booking'));
        add_action('wp_ajax_nopriv_submit_booking', array($this, 'submit_booking'));
        
        add_action('wp_ajax_get_booked_dates', array($this, 'get_booked_dates'));
        add_action('wp_ajax_nopriv_get_booked_dates', array($this, 'get_booked_dates'));
    }
    
    // دریافت ساعت‌های خالی برای یک تاریخ
    public function get_available_times() {
        global $wpdb;
        
        $date = sanitize_text_field($_POST['date']);
        
        // تبدیل تاریخ شمسی به میلادی (باید یک تابع تبدیل اضافه کنید)
        // $gregorian_date = $this->jalali_to_gregorian($date);
        $gregorian_date = $date; // فعلاً همان تاریخ را استفاده می‌کنیم
        
        // روز هفته (0 = شنبه)
        $day_of_week = $this->get_persian_day_of_week($gregorian_date);
        
        // ساعت‌های فعال برای این روز
        $working_hours = $wpdb->get_results($wpdb->prepare(
            "SELECT time_slot FROM {$wpdb->prefix}salon_working_hours 
             WHERE day_of_week = %d AND is_active = 1 
             ORDER BY time_slot",
            $day_of_week
        ));
        
        // ساعت‌های رزرو شده
        $booked_times = $wpdb->get_col($wpdb->prepare(
            "SELECT TIME_FORMAT(booking_time, '%%H:%%i') 
             FROM {$wpdb->prefix}salon_bookings 
             WHERE booking_date = %s AND status != 'cancelled'",
            $gregorian_date
        ));
        
        $available_times = array();
        foreach ($working_hours as $hour) {
            $time = $hour->time_slot;
            $available_times[] = array(
                'time' => $time,
                'available' => !in_array($time, $booked_times)
            );
        }
        
        wp_send_json_success($available_times);
    }
    
    // ثبت رزرو
    public function submit_booking() {
        global $wpdb;
        
        // اعتبارسنجی
        $name = sanitize_text_field($_POST['name']);
        $phone = sanitize_text_field($_POST['phone']);
        $service_id = intval($_POST['service_id']);
        $date = sanitize_text_field($_POST['date']);
        $time = sanitize_text_field($_POST['time']);
        $note = sanitize_textarea_field($_POST['note']);
        
        // بررسی خالی نبودن
        if (empty($name) || empty($phone) || empty($service_id) || empty($date) || empty($time)) {
            wp_send_json_error('لطفاً تمام فیلدها را پر کنید.');
        }
        
        // بررسی شماره موبایل
        if (!preg_match('/^09[0-9]{9}$/', $phone)) {
            wp_send_json_error('شماره موبایل نامعتبر است.');
        }
        
        // تبدیل تاریخ (باید تابع تبدیل اضافه شود)
        $gregorian_date = $date;
        
        // بررسی تکراری نبودن
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}salon_bookings 
             WHERE booking_date = %s AND booking_time = %s AND status != 'cancelled'",
            $gregorian_date, $time
        ));
        
        if ($exists) {
            wp_send_json_error('این ساعت قبلاً رزرو شده است.');
        }
        
        // ثبت رزرو
        $inserted = $wpdb->insert(
            $wpdb->prefix . 'salon_bookings',
            array(
                'client_name' => $name,
                'client_phone' => $phone,
                'service_id' => $service_id,
                'booking_date' => $gregorian_date,
                'booking_time' => $time,
                'note' => $note,
                'status' => 'pending'
            ),
            array('%s', '%s', '%d', '%s', '%s', '%s', '%s')
        );
        
        if ($inserted) {
            wp_send_json_success('رزرو شما با موفقیت ثبت شد.');
        } else {
            wp_send_json_error('خطا در ثبت رزرو. لطفاً دوباره تلاش کنید.');
        }
    }
    
    // دریافت روزهای پر شده
    public function get_booked_dates() {
        global $wpdb;
        
        $fully_booked_dates = $wpdb->get_results("
            SELECT booking_date, COUNT(*) as count
            FROM {$wpdb->prefix}salon_bookings
            WHERE status != 'cancelled'
            GROUP BY booking_date
        ");
        
        $disabled_dates = array();
        
        foreach ($fully_booked_dates as $date) {
            // دریافت تعداد ساعت‌های کاری در این روز
            $day_of_week = $this->get_persian_day_of_week($date->booking_date);
            $total_slots = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}salon_working_hours 
                 WHERE day_of_week = %d AND is_active = 1",
                $day_of_week
            ));
            
            // اگر تمام ساعت‌ها پر شده، تاریخ را غیرفعال می‌کنیم
            if ($date->count >= $total_slots) {
                $disabled_dates[] = $date->booking_date;
            }
        }
        
        wp_send_json_success($disabled_dates);
    }
    
    // تابع کمکی: دریافت روز هفته شمسی
    private function get_persian_day_of_week($date) {
        $timestamp = strtotime($date);
        $day = date('w', $timestamp); // 0=یکشنبه, 6=شنبه
        
        // تبدیل به فرمت شمسی (0=شنبه)
        $persian_day = ($day + 1) % 7;
        return $persian_day;
    }
}