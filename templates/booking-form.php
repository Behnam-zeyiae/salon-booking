<?php
if (!defined('ABSPATH')) exit;

global $wpdb;

// دریافت خدمات فعال
$services = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}salon_services WHERE status = 1 ORDER BY title");
?>

<form id="salonBookingForm" class="salon-booking-form" novalidate>
    <div class="form-header">
        <h2>💇‍♀️ رزرو نوبت آرایشگاه</h2>
        <p><?php echo esc_html(Salon_Booking_Database::get_option('salon_name', 'آرایشگاه زیبایی')); ?></p>
    </div>

    <fieldset>
        <legend>👤 اطلاعات مشتری</legend>

        <div class="form-group">
            <label for="clientFullName">
                نام و نام خانوادگی <span class="required">*</span>
            </label>
            <input type="text" id="clientFullName" name="clientFullName" 
                   placeholder="مثال: محمد احمدی" required>
            <div class="error-message" id="nameError"></div>
        </div>

        <div class="form-group">
            <label for="clientPhone">
                شماره موبایل <span class="required">*</span>
            </label>
            <input type="tel" id="clientPhone" name="clientPhone" 
                   placeholder="09xxxxxxxxx" maxlength="11" required>
            <div class="error-message" id="phoneError"></div>
            <div class="note">📱 فرمت: 09123456789</div>
        </div>
    </fieldset>

    <fieldset>
        <legend>✂️ انتخاب سرویس</legend>

        <div class="form-group">
            <label for="serviceCategory">
                دسته سرویس <span class="required">*</span>
            </label>
            <select id="serviceCategory" name="serviceCategory" required>
                <option value="">انتخاب کنید</option>
                <?php foreach ($services as $service): ?>
                    <option value="<?php echo $service->id; ?>" 
                            data-duration="<?php echo $service->duration; ?>"
                            data-price="<?php echo $service->price; ?>">
                        <?php echo esc_html($service->title); ?>
                        <?php if ($service->price > 0): ?>
                            - <?php echo number_format($service->price); ?> تومان
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="error-message" id="serviceError"></div>
            <div id="serviceDetails" class="service-details" style="display:none;">
                <span class="detail-item">⏱️ مدت زمان: <strong id="serviceDuration"></strong> دقیقه</span>
                <span class="detail-item">💰 قیمت: <strong id="servicePrice"></strong> تومان</span>
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend>📅 زمان رزرو</legend>

        <div class="form-group">
            <label for="reservationDate">
                تاریخ رزرو <span class="required">*</span>
            </label>
            <input data-jdp type="text" id="reservationDate" name="reservationDate" 
                   placeholder="روی تقویم کلیک کنید" readonly required>
            <div class="error-message" id="dateError"></div>
        </div>

        <div class="form-group">
            <label for="reservationTime">
                ساعت رزرو <span class="required">*</span>
            </label>
            <div id="timeSlots" class="time-slots">
                <div class="time-slots-placeholder">
                    📆 ابتدا تاریخ را انتخاب کنید
                </div>
            </div>
            <div class="error-message" id="timeError"></div>
        </div>
    </fieldset>

    <fieldset>
        <legend>📝 توضیحات</legend>
        <div class="form-group">
            <textarea id="clientNote" name="clientNote" rows="4" 
                      placeholder="توضیحات یا درخواست خاص خود را بنویسید..."></textarea>
            <div class="note">💡 اختیاری - می‌توانید درخواست خاصی داشته باشید</div>
        </div>
    </fieldset>

    <div class="form-actions">
        <button type="submit" id="submitReservation" class="btn-submit">
            <span class="spinner"></span>
            <span class="btn-text">✅ ثبت رزرو</span>
        </button>
        <button type="button" id="resetForm" class="btn-reset">
            🔄 پاک کردن فرم
        </button>
    </div>
</form>

<!-- Success Modal -->
<div class="success-modal" id="successModal">
    <div class="success-content">
        <div class="success-icon">✓</div>
        <h3>رزرو با موفقیت ثبت شد!</h3>
        <p id="reservationDetails"></p>
        <button class="close-modal" id="closeModal">متوجه شدم</button>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
</div>