<?php
if (!defined('ABSPATH')) exit;

class Salon_Booking_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'رزرو آرایشگاه',
            'رزرو آرایشگاه',
            'manage_options',
            'salon-booking',
            array($this, 'bookings_page'),
            'dashicons-calendar-alt',
            30
        );
        
        add_submenu_page(
            'salon-booking',
            'نوبت‌ها',
            'نوبت‌ها',
            'manage_options',
            'salon-booking',
            array($this, 'bookings_page')
        );
        
        add_submenu_page(
            'salon-booking',
            'خدمات',
            'خدمات',
            'manage_options',
            'salon-services',
            array($this, 'services_page')
        );
        
        add_submenu_page(
            'salon-booking',
            'ساعت‌های کاری',
            'ساعت‌های کاری',
            'manage_options',
            'salon-working-hours',
            array($this, 'working_hours_page')
        );
        
        add_submenu_page(
            'salon-booking',
            'تنظیمات',
            'تنظیمات',
            'manage_options',
            'salon-settings',
            array($this, 'settings_page')
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'salon-') === false) {
            return;
        }
        
        wp_enqueue_style('salon-admin', SALON_BOOKING_PLUGIN_URL . 'assets/css/admin.css', array(), SALON_BOOKING_VERSION);
        wp_enqueue_script('salon-admin', SALON_BOOKING_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), SALON_BOOKING_VERSION, true);
        
        wp_localize_script('salon-admin', 'salonAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('salon_admin_nonce')
        ));
    }
    
    public function bookings_page() {
        require_once SALON_BOOKING_PLUGIN_DIR . 'admin/bookings.php';
    }
    
    public function services_page() {
        require_once SALON_BOOKING_PLUGIN_DIR . 'admin/services.php';
    }
    
    public function working_hours_page() {
        require_once SALON_BOOKING_PLUGIN_DIR . 'admin/working-hours.php';
    }
    
    public function settings_page() {
        require_once SALON_BOOKING_PLUGIN_DIR . 'admin/settings.php';
    }
}