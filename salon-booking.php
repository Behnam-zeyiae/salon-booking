<?php
/**
 * Plugin Name: سیستم رزرو آرایشگاه
 * Plugin URI: https://codebest.ir
 * Description: سیستم پیشرفته رزرو نوبت آرایشگاه با پنل مدیریت کامل
 * Version: 1.0.0
 * Author: Behnam Zeyiae 
 * Author URI: https://codebest.ir
 * Text Domain: salon-booking
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// تعریف ثابت‌های افزونه
define('SALON_BOOKING_VERSION', '1.0.0');
define('SALON_BOOKING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SALON_BOOKING_PLUGIN_URL', plugin_dir_url(__FILE__));

// کلاس اصلی افزونه
class Salon_Booking {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    private function load_dependencies() {
        require_once SALON_BOOKING_PLUGIN_DIR . 'includes/class-database.php';
        require_once SALON_BOOKING_PLUGIN_DIR . 'includes/class-admin.php';
        require_once SALON_BOOKING_PLUGIN_DIR . 'includes/class-frontend.php';
        require_once SALON_BOOKING_PLUGIN_DIR . 'includes/class-ajax.php';
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function init() {
        // راه‌اندازی کلاس‌ها
        Salon_Booking_Database::get_instance();
        Salon_Booking_Admin::get_instance();
        Salon_Booking_Frontend::get_instance();
        Salon_Booking_Ajax::get_instance();
    }
    
    public function activate() {
        Salon_Booking_Database::create_tables();
        Salon_Booking_Database::insert_default_data();
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
}

// راه‌اندازی افزونه
function salon_booking() {
    return Salon_Booking::get_instance();
}

salon_booking();