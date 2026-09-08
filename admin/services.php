<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table = $wpdb->prefix . 'salon_services';

// افزودن/ویرایش خدمت
if (isset($_POST['save_service'])) {
    $data = array(
        'title' => sanitize_text_field($_POST['title']),
        'description' => sanitize_textarea_field($_POST['description']),
        'duration' => intval($_POST['duration']),
        'price' => floatval($_POST['price']),
        'status' => isset($_POST['status']) ? 1 : 0
    );
    
    if (isset($_POST['service_id']) && $_POST['service_id']) {
        $wpdb->update($table, $data, array('id' => intval($_POST['service_id'])));
        echo '<div class="notice notice-success"><p>خدمت بروزرسانی شد.</p></div>';
    } else {
        $wpdb->insert($table, $data);
        echo '<div class="notice notice-success"><p>خدمت جدید اضافه شد.</p></div>';
    }
}

// حذف خدمت
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $wpdb->delete($table, array('id' => intval($_GET['id'])));
    echo '<div class="notice notice-success"><p>خدمت حذف شد.</p></div>';
}

// ویرایش
$edit_service = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_service = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", intval($_GET['id'])));
}

$services = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC");
?>

<div class="wrap salon-admin-page">
    <h1>مدیریت خدمات</h1>
    
    <div class="salon-two-column">
        <div class="salon-form-section">
            <h2><?php echo $edit_service ? 'ویرایش خدمت' : 'افزودن خدمت جدید'; ?></h2>
            <form method="post" class="salon-form">
                <?php if ($edit_service): ?>
                    <input type="hidden" name="service_id" value="<?php echo $edit_service->id; ?>">
                <?php endif; ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="title">عنوان خدمت *</label></th>
                        <td>
                            <input type="text" name="title" id="title" class="regular-text" 
                                   value="<?php echo $edit_service ? esc_attr($edit_service->title) : ''; ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="description">توضیحات</label></th>
                        <td>
                            <textarea name="description" id="description" rows="3" class="large-text"><?php echo $edit_service ? esc_textarea($edit_service->description) : ''; ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="duration">مدت زمان (دقیقه) *</label></th>
                        <td>
                            <input type="number" name="duration" id="duration" class="small-text" 
                                   value="<?php echo $edit_service ? $edit_service->duration : 60; ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="price">قیمت (تومان)</label></th>
                        <td>
                            <input type="number" name="price" id="price" class="regular-text" 
                                   value="<?php echo $edit_service ? $edit_service->price : 0; ?>" step="1000">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="status">فعال</label></th>
                        <td>
                            <input type="checkbox" name="status" id="status" value="1" 
                                   <?php echo (!$edit_service || $edit_service->status) ? 'checked' : ''; ?>>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" name="save_service" class="button button-primary">
                        <?php echo $edit_service ? 'بروزرسانی' : 'افزودن خدمت'; ?>
                    </button>
                    <?php if ($edit_service): ?>
                        <a href="?page=salon-services" class="button">انصراف</a>
                    <?php endif; ?>
                </p>
            </form>
        </div>
        
        <div class="salon-list-section">
            <h2>لیست خدمات</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>عنوان</th>
                        <th>مدت زمان</th>
                        <th>قیمت</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><strong><?php echo esc_html($service->title); ?></strong></td>
                            <td><?php echo $service->duration; ?> دقیقه</td>
                            <td><?php echo number_format($service->price); ?> تومان</td>
                            <td><?php echo $service->status ? '<span class="dashicons dashicons-yes-alt" style="color:green;"></span>' : '<span class="dashicons dashicons-dismiss" style="color:red;"></span>'; ?></td>
                            <td>
                                <a href="?page=salon-services&action=edit&id=<?php echo $service->id; ?>" class="button button-small">ویرایش</a>
                                <a href="?page=salon-services&action=delete&id=<?php echo $service->id; ?>" 
                                   class="button button-small button-link-delete"
                                   onclick="return confirm('آیا مطمئن هستید؟')">حذف</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>