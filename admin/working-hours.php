<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table = $wpdb->prefix . 'salon_working_hours';

// ذخیره ساعت‌های کاری
if (isset($_POST['save_hours'])) {
    // ابتدا همه را غیرفعال می‌کنیم
    $wpdb->update($table, array('is_active' => 0), array('1' => '1'));
    
    // سپس موارد انتخاب شده را فعال می‌کنیم
    if (isset($_POST['active_hours']) && is_array($_POST['active_hours'])) {
        foreach ($_POST['active_hours'] as $hour_id) {
            $wpdb->update($table, array('is_active' => 1), array('id' => intval($hour_id)));
        }
    }
    
    echo '<div class="notice notice-success"><p>ساعت‌های کاری بروزرسانی شد.</p></div>';
}

$days = array('شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه');
?>

<div class="wrap salon-admin-page">
    <h1>مدیریت ساعت‌های کاری</h1>
    <p class="description">ساعت‌های فعال را انتخاب کنید. مشتریان فقط می‌توانند در این ساعت‌ها رزرو کنند.</p>
    
    <form method="post">
        <div class="salon-working-hours-grid">
            <?php for ($day = 0; $day < 7; $day++): ?>
                <div class="day-section">
                    <h3><?php echo $days[$day]; ?></h3>
                    <div class="hours-checkboxes">
                        <?php
                        $hours = $wpdb->get_results($wpdb->prepare(
                            "SELECT * FROM $table WHERE day_of_week = %d ORDER BY start_time",
                            $day
                        ));
                        
                        foreach ($hours as $hour):
                        ?>
                            <label class="hour-checkbox">
                                <input type="checkbox" name="active_hours[]" value="<?php echo $hour->id; ?>" 
                                       <?php checked($hour->is_active, 1); ?>>
                                <span><?php echo substr($hour->time_slot, 0, 5); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
        
        <p class="submit">
            <button type="submit" name="save_hours" class="button button-primary button-large">ذخیره تغییرات</button>
        </p>
    </form>
</div>