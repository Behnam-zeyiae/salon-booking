<?php
if (!defined('ABSPATH')) exit;

class Salon_Booking_Database {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // جدول تنظیمات
        $table_settings = $wpdb->prefix . 'salon_settings';
        $sql_settings = "CREATE TABLE IF NOT EXISTS $table_settings (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            option_name varchar(100) NOT NULL,
            option_value longtext NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY option_name (option_name)
        ) $charset_collate;";
        
        // جدول خدمات
        $table_services = $wpdb->prefix . 'salon_services';
        $sql_services = "CREATE TABLE IF NOT EXISTS $table_services (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            title varchar(200) NOT NULL,
            description text,
            duration int(11) DEFAULT 60,
            price decimal(10,2) DEFAULT 0,
            status tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // جدول ساعت‌های کاری
        $table_hours = $wpdb->prefix . 'salon_working_hours';
        $sql_hours = "CREATE TABLE IF NOT EXISTS $table_hours (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            day_of_week tinyint(1) NOT NULL COMMENT '0=شنبه, 6=جمعه',
            start_time time NOT NULL,
            end_time time NOT NULL,
            time_slot varchar(5) NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // جدول رزروها
        $table_bookings = $wpdb->prefix . 'salon_bookings';
        $sql_bookings = "CREATE TABLE IF NOT EXISTS $table_bookings (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            client_name varchar(200) NOT NULL,
            client_phone varchar(20) NOT NULL,
            service_id bigint(20) NOT NULL,
            booking_date date NOT NULL,
            booking_time time NOT NULL,
            status varchar(20) DEFAULT 'pending',
            note text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY service_id (service_id),
            KEY booking_date (booking_date),
            KEY booking_time (booking_time),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_settings);
        dbDelta($sql_services);
        dbDelta($sql_hours);
        dbDelta($sql_bookings);
    }
    
    public static function insert_default_data() {
        global $wpdb;
        
        // تنظیمات پیش‌فرض
        $settings = array(
            'salon_name' => 'آرایشگاه زیبایی',
            'salon_phone' => '02112345678',
            'salon_address' => 'تهران، خیابان ولیعصر',
            'booking_interval' => '60', // به دقیقه
            'max_days_advance' => '30', // تعداد روز برای رزرو
        );
        
        foreach ($settings as $key => $value) {
            $wpdb->insert(
                $wpdb->prefix . 'salon_settings',
                array('option_name' => $key, 'option_value' => $value),
                array('%s', '%s')
            );
        }
        
        // خدمات پیش‌فرض
        $services = array(
            array('title' => 'کوتاهی مو', 'description' => 'کوتاهی و اصلاح مو', 'duration' => 60, 'price' => 150000),
            array('title' => 'رنگ مو', 'description' => 'رنگ کامل مو', 'duration' => 120, 'price' => 350000),
            array('title' => 'هایلایت', 'description' => 'هایلایت و مش مو', 'duration' => 90, 'price' => 250000),
            array('title' => 'شینیون', 'description' => 'شینیون مجلسی', 'duration' => 120, 'price' => 300000),
            array('title' => 'کراتینه', 'description' => 'کراتینه مو', 'duration' => 180, 'price' => 500000),
        );
        
        foreach ($services as $service) {
            $wpdb->insert(
                $wpdb->prefix . 'salon_services',
                $service,
                array('%s', '%s', '%d', '%f')
            );
        }
        
        // ساعت‌های کاری پیش‌فرض (9 صبح تا 6 عصر)
        $times = array('09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00');
        
        for ($day = 0; $day < 6; $day++) { // شنبه تا پنج‌شنبه
            foreach ($times as $time) {
                $wpdb->insert(
                    $wpdb->prefix . 'salon_working_hours',
                    array(
                        'day_of_week' => $day,
                        'start_time' => $time,
                        'end_time' => date('H:i', strtotime($time . ' +1 hour')),
                        'time_slot' => $time,
                        'is_active' => 1
                    ),
                    array('%d', '%s', '%s', '%s', '%d')
                );
            }
        }
    }
    
    // دریافت تنظیمات
    public static function get_option($name, $default = '') {
        global $wpdb;
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT option_value FROM {$wpdb->prefix}salon_settings WHERE option_name = %s",
            $name
        ));
        return $value ? $value : $default;
    }
    
    // بروزرسانی تنظیمات
    public static function update_option($name, $value) {
        global $wpdb;
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}salon_settings WHERE option_name = %s",
            $name
        ));
        
        if ($exists) {
            return $wpdb->update(
                $wpdb->prefix . 'salon_settings',
                array('option_value' => $value),
                array('option_name' => $name),
                array('%s'),
                array('%s')
            );
        } else {
            return $wpdb->insert(
                $wpdb->prefix . 'salon_settings',
                array('option_name' => $name, 'option_value' => $value),
                array('%s', '%s')
            );
        }
    }
}