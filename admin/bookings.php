<?php
if (!defined('ABSPATH')) exit;

global $wpdb;

// حذف نوبت
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $wpdb->delete($wpdb->prefix . 'salon_bookings', array('id' => intval($_GET['id'])));
    echo '<div class="notice notice-success"><p>نوبت با موفقیت حذف شد.</p></div>';
}

// تغییر وضعیت
if (isset($_POST['update_status']) && isset($_POST['booking_id'])) {
    $wpdb->update(
        $wpdb->prefix . 'salon_bookings',
        array('status' => sanitize_text_field($_POST['status'])),
        array('id' => intval($_POST['booking_id']))
    );
    echo '<div class="notice notice-success"><p>وضعیت نوبت بروزرسانی شد.</p></div>';
}

// دریافت نوبت‌ها
$bookings = $wpdb->get_results("
    SELECT b.*, s.title as service_name 
    FROM {$wpdb->prefix}salon_bookings b
    LEFT JOIN {$wpdb->prefix}salon_services s ON b.service_id = s.id
    ORDER BY b.booking_date DESC, b.booking_time DESC
    LIMIT 100
");

?>

<div class="wrap salon-admin-page">
    <h1 class="wp-heading-inline">مدیریت نوبت‌ها</h1>
    
    <div class="salon-stats">
        <div class="stat-box">
            <span class="dashicons dashicons-calendar-alt"></span>
            <div>
                <strong><?php echo $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}salon_bookings"); ?></strong>
                <p>کل نوبت‌ها</p>
            </div>
        </div>
        <div class="stat-box pending">
            <span class="dashicons dashicons-clock"></span>
            <div>
                <strong><?php echo $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}salon_bookings WHERE status='pending'"); ?></strong>
                <p>در انتظار تایید</p>
            </div>
        </div>
        <div class="stat-box confirmed">
            <span class="dashicons dashicons-yes-alt"></span>
            <div>
                <strong><?php echo $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}salon_bookings WHERE status='confirmed'"); ?></strong>
                <p>تایید شده</p>
            </div>
        </div>
        <div class="stat-box completed">
            <span class="dashicons dashicons-smiley"></span>
            <div>
                <strong><?php echo $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}salon_bookings WHERE status='completed'"); ?></strong>
                <p>انجام شده</p>
            </div>
        </div>
    </div>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>شناسه</th>
                <th>نام مشتری</th>
                <th>موبایل</th>
                <th>خدمت</th>
                <th>تاریخ</th>
                <th>ساعت</th>
                <th>وضعیت</th>
                <th>یادداشت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($bookings)): ?>
                <tr><td colspan="9" style="text-align:center;">هیچ نوبتی ثبت نشده است.</td></tr>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?php echo $booking->id; ?></td>
                        <td><strong><?php echo esc_html($booking->client_name); ?></strong></td>
                        <td><a href="tel:<?php echo esc_attr($booking->client_phone); ?>"><?php echo esc_html($booking->client_phone); ?></a></td>
                        <td><?php echo esc_html($booking->service_name); ?></td>
                        <td><?php echo esc_html($booking->booking_date); ?></td>
                        <td><?php echo esc_html($booking->booking_time); ?></td>
                        <td>
                            <form method="post" style="display:inline-block;">
                                <input type="hidden" name="booking_id" value="<?php echo $booking->id; ?>">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="pending" <?php selected($booking->status, 'pending'); ?>>در انتظار</option>
                                    <option value="confirmed" <?php selected($booking->status, 'confirmed'); ?>>تایید شده</option>
                                    <option value="completed" <?php selected($booking->status, 'completed'); ?>>انجام شده</option>
                                    <option value="cancelled" <?php selected($booking->status, 'cancelled'); ?>>لغو شده</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                        <td><?php echo esc_html($booking->note); ?></td>
                        <td>
                            <a href="?page=salon-booking&action=delete&id=<?php echo $booking->id; ?>" 
                               class="button button-small button-link-delete"
                               onclick="return confirm('آیا مطمئن هستید؟')">حذف</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>