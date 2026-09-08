<?php
if (!defined('ABSPATH')) exit;

class Salon_Booking_Frontend {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_shortcode('salon_booking_form', array($this, 'booking_form_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
    }
    
    public function enqueue_frontend_scripts() {
        wp_enqueue_style('salon-frontend', SALON_BOOKING_PLUGIN_URL . 'assets/css/frontend.css', array(), SALON_BOOKING_VERSION);
        
        // Jalali Datepicker
        wp_enqueue_style('jalali-datepicker', 'https://unpkg.com/@majidh1/jalalidatepicker/dist/jalalidatepicker.min.css');
        wp_enqueue_script('jalali-datepicker', 'https://unpkg.com/@majidh1/jalalidatepicker/dist/jalalidatepicker.min.js', array(), null, true);
        
        wp_enqueue_script('salon-frontend', SALON_BOOKING_PLUGIN_URL . 'assets/js/frontend.js', array('jquery', 'jalali-datepicker'), SALON_BOOKING_VERSION, true);
        
        wp_localize_script('salon-frontend', 'salonBooking', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('salon_booking_nonce'),
            'max_days' => Salon_Booking_Database::get_option('max_days_advance', '30'),
            'messages' => array(
                'name_required' => 'لطفاً نام و نام خانوادگی را وارد کنید',
                'name_min' => 'نام باید حداقل 3 حرف باشد',
                'name_persian' => 'لطفاً فقط از حروف فارسی استفاده کنید',
                'phone_invalid' => 'شماره موبایل باید با 09 شروع شده و 11 رقم باشد',
                'service_required' => 'لطفاً نوع سرویس را انتخاب کنید',
                'date_required' => 'لطفاً تاریخ را انتخاب کنید',
                'time_required' => 'لطفاً ساعت را انتخاب کنید',
                'success' => 'رزرو شما با موفقیت ثبت شد!',
                'error' => 'خطا در ثبت رزرو. لطفاً دوباره تلاش کنید.',
                'confirm_reset' => 'آیا مطمئن هستید که می‌خواهید فرم را پاک کنید؟'
            )
        ));
    }
    
    public function booking_form_shortcode($atts) {
        ob_start();
        include SALON_BOOKING_PLUGIN_DIR . 'templates/booking-form.php';
        return ob_get_clean();
    }
}