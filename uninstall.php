<?php
// اگر از طریق وردپرس فراخوانی نشده، خارج شو
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// حذف جداول
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}salon_bookings");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}salon_services");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}salon_working_hours");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}salon_settings");

// حذف تنظیمات
delete_option('salon_booking_version');