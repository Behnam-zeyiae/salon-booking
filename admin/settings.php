<?php
if (!defined('ABSPATH')) exit;

// ذخیره تنظیمات
if (isset($_POST['save_settings'])) {
    $settings = array('salon_name', 'salon_phone', 'salon_address', 'booking_interval', 'max_days_advance');
    
    foreach ($settings as $setting) {
        if (isset($_POST[$setting])) {
            Salon_Booking_Database::update_option($setting, sanitize_text_field($_POST[$setting]));
        }
    }
    
    echo '<div class="notice notice-success"><p>تنظیمات ذخیره شد.</p></div>';
}
?>

<div class="wrap salon-admin-page">
    <h1>تنظیمات عمومی</h1>
    
    <form method="post">
        <table class="form-table">
            <tr>
                <th><label for="salon_name">نام آرایشگاه</label></th>
                <td>
                    <input type="text" name="salon_name" id="salon_name" class="regular-text" 
                           value="<?php echo esc_attr(Salon_Booking_Database::get_option('salon_name')); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="salon_phone">شماره تماس</label></th>
                <td>
                    <input type="text" name="salon_phone" id="salon_phone" class="regular-text" 
                           value="<?php echo esc_attr(Salon_Booking_Database::get_option('salon_phone')); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="salon_address">آدرس</label></th>
                <td>
                    <textarea name="salon_address" id="salon_address" rows="3" class="large-text"><?php echo esc_textarea(Salon_Booking_Database::get_option('salon_address')); ?></textarea>
                </td>
            </tr>
            <tr>
                <th><label for="max_days_advance">تعداد روز قابل رزرو</label></th>
                <td>
                    <input type="number" name="max_days_advance" id="max_days_advance" class="small-text" 
                           value="<?php echo esc_attr(Salon_Booking_Database::get_option('max_days_advance', '30')); ?>">
                    <p class="description">مشتریان چند روز جلوتر می‌توانند رزرو کنند؟</p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <button type="submit" name="save_settings" class="button button-primary">ذخیره تنظیمات</button>
        </p>
    </form>
    
    <hr>
    
    <h2>شورت‌کد</h2>
    <p>برای نمایش فرم رزرو در هر صفحه، از شورت‌کد زیر استفاده کنید:</p>
    <code style="background:#f0f0f0; padding:10px; display:inline-block; font-size:16px;">[salon_booking_form]</code>
</div>