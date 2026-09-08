jQuery(document).ready(function($) {
    'use strict';
    
    const form = $('#salonBookingForm');
    const nameInput = $('#clientFullName');
    const phoneInput = $('#clientPhone');
    const serviceSelect = $('#serviceCategory');
    const dateInput = $('#reservationDate');
    const timeSlotsContainer = $('#timeSlots');
    const noteInput = $('#clientNote');
    const submitBtn = $('#submitReservation');
    const resetBtn = $('#resetForm');
    const successModal = $('#successModal');
    const loadingOverlay = $('#loadingOverlay');
    
    let selectedTime = null;
    let disabledDates = [];
    
    // تنظیمات تقویم جلالی
    jalaliDatepicker.startWatch({
        minDate: "today",
        maxDate: salonBooking.max_days,
        // غیرفعال کردن روزهای پر شده
        onShow: function() {
            loadDisabledDates();
        }
    });
    
    // بارگذاری روزهای پر شده
    function loadDisabledDates() {
        $.ajax({
            url: salonBooking.ajax_url,
            type: 'POST',
            data: {
                action: 'get_booked_dates',
                nonce: salonBooking.nonce
            },
            success: function(response) {
                if (response.success) {
                    disabledDates = response.data;
                    // اعمال غیرفعال‌سازی در تقویم
                    disableDatesInCalendar();
                }
            }
        });
    }
    
    // غیرفعال کردن روزها در تقویم
    function disableDatesInCalendar() {
        // این بخش بستگی به API تقویم جلالی دارد
        // باید مستندات کتابخانه را بررسی کنید
    }
    
    // نمایش جزئیات سرویس
    serviceSelect.on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const duration = selectedOption.data('duration');
        const price = selectedOption.data('price');
        
        if ($(this).val()) {
            $('#serviceDuration').text(duration);
            $('#servicePrice').text(price.toLocaleString('fa-IR'));
            $('#serviceDetails').slideDown();
        } else {
            $('#serviceDetails').slideUp();
        }
        
        validateService();
    });
    
    // بارگذاری ساعت‌های خالی هنگام انتخاب تاریخ
    dateInput.on('change', function() {
        const date = $(this).val();
        
        if (!date) return;
        
        timeSlotsContainer.html('<div class="time-slots-placeholder">⏳ در حال بارگذاری...</div>');
        selectedTime = null;
        
        $.ajax({
            url: salonBooking.ajax_url,
            type: 'POST',
            data: {
                action: 'get_available_times',
                nonce: salonBooking.nonce,
                date: date
            },
            success: function(response) {
                if (response.success) {
                    displayTimeSlots(response.data);
                } else {
                    timeSlotsContainer.html('<div class="time-slots-placeholder">❌ خطا در بارگذاری ساعت‌ها</div>');
                }
            },
            error: function() {
                timeSlotsContainer.html('<div class="time-slots-placeholder">❌ خطا در اتصال به سرور</div>');
            }
        });
        
        validateDate();
    });
    
    // نمایش اسلات‌های زمانی
    function displayTimeSlots(times) {
        if (!times || times.length === 0) {
            timeSlotsContainer.html('<div class="time-slots-placeholder">⚠️ ساعتی برای این روز موجود نیست</div>');
            return;
        }
        
        let html = '';
        times.forEach(function(slot) {
            const className = slot.available ? 'time-slot' : 'time-slot booked';
            const disabled = slot.available ? '' : 'data-disabled="true"';
            html += `<div class="${className}" data-time="${slot.time}" ${disabled}>
                        ${slot.time}
                        ${!slot.available ? '<br><small>(پر)</small>' : ''}
                     </div>`;
        });
        
        timeSlotsContainer.html(html);
        
        // کلیک روی ساعت
        $('.time-slot:not(.booked)').on('click', function() {
            $('.time-slot').removeClass('selected');
            $(this).addClass('selected');
            selectedTime = $(this).data('time');
            validateTime();
        });
    }
    
    // اعتبارسنجی نام
    function validateName() {
        const name = nameInput.val().trim();
        const errorEl = $('#nameError');
        
        if (name.length === 0) {
            showError(nameInput, errorEl, salonBooking.messages.name_required);
            return false;
        }
        
        if (name.length < 3) {
            showError(nameInput, errorEl, salonBooking.messages.name_min);
            return false;
        }
        
        if (!/^[\u0600-\u06FF\s]+$/.test(name)) {
            showError(nameInput, errorEl, salonBooking.messages.name_persian);
            return false;
        }
        
        showSuccess(nameInput, errorEl);
        return true;
    }
    
    // اعتبارسنجی موبایل
    function validatePhone() {
        const phone = phoneInput.val().trim();
        const errorEl = $('#phoneError');
        
        if (!/^09[0-9]{9}$/.test(phone)) {
            showError(phoneInput, errorEl, salonBooking.messages.phone_invalid);
            return false;
        }
        
        showSuccess(phoneInput, errorEl);
        return true;
    }
    
    // اعتبارسنجی سرویس
    function validateService() {
        const errorEl = $('#serviceError');
        
        if (!serviceSelect.val()) {
            showError(serviceSelect, errorEl, salonBooking.messages.service_required);
            return false;
        }
        
        showSuccess(serviceSelect, errorEl);
        return true;
    }
    
    // اعتبارسنجی تاریخ
    function validateDate() {
        const errorEl = $('#dateError');
        
        if (!dateInput.val().trim()) {
            showError(dateInput, errorEl, salonBooking.messages.date_required);
            return false;
        }
        
        showSuccess(dateInput, errorEl);
        return true;
    }
    
    // اعتبارسنجی ساعت
    function validateTime() {
        const errorEl = $('#timeError');
        
        if (!selectedTime) {
            showError($('<div>'), errorEl, salonBooking.messages.time_required);
            return false;
        }
        
        showSuccess($('<div>'), errorEl);
        return true;
    }
    
    // نمایش خطا
    function showError(input, errorEl, message) {
        input.addClass('error').removeClass('success');
        errorEl.text(message).addClass('show');
    }
    
    // نمایش موفقیت
    function showSuccess(input, errorEl) {
        input.removeClass('error').addClass('success');
        errorEl.removeClass('show');
    }
    
    // اعتبارسنجی لحظه‌ای
    nameInput.on('blur', validateName);
    phoneInput.on('blur', validatePhone);
    
    // فقط اعداد در فیلد موبایل
    phoneInput.on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    
    // ریست فرم
    resetBtn.on('click', function() {
        if (confirm(salonBooking.messages.confirm_reset)) {
            form[0].reset();
            $('.error, .success').removeClass('error success');
            $('.error-message').removeClass('show');
            $('#serviceDetails').hide();
            timeSlotsContainer.html('<div class="time-slots-placeholder">📆 ابتدا تاریخ را انتخاب کنید</div>');
            selectedTime = null;
        }
    });
    
    // ارسال فرم
    form.on('submit', function(e) {
        e.preventDefault();
        
        // اعتبارسنجی کامل
        const isNameValid = validateName();
        const isPhoneValid = validatePhone();
        const isServiceValid = validateService();
        const isDateValid = validateDate();
        const isTimeValid = validateTime();
        
        if (!isNameValid || !isPhoneValid || !isServiceValid || !isDateValid || !isTimeValid) {
            // اسکرول به اولین خطا
            const firstError = $('.error').first();
            if (firstError.length) {
                $('html, body').animate({
                    scrollTop: firstError.offset().top - 100
                }, 500);
            }
            return;
        }
        
        // نمایش لودینگ
        submitBtn.addClass('loading').prop('disabled', true);
        loadingOverlay.addClass('show');
        
        // ارسال به سرور
        $.ajax({
            url: salonBooking.ajax_url,
            type: 'POST',
            data: {
                action: 'submit_booking',
                nonce: salonBooking.nonce,
                name: nameInput.val().trim(),
                phone: phoneInput.val().trim(),
                service_id: serviceSelect.val(),
                date: dateInput.val().trim(),
                time: selectedTime,
                note: noteInput.val().trim()
            },
            success: function(response) {
                submitBtn.removeClass('loading').prop('disabled', false);
                loadingOverlay.removeClass('show');
                
                if (response.success) {
                    showSuccessModal();
                    form[0].reset();
                    $('.success').removeClass('success');
                    $('#serviceDetails').hide();
                    timeSlotsContainer.html('<div class="time-slots-placeholder">📆 ابتدا تاریخ را انتخاب کنید</div>');
                    selectedTime = null;
                } else {
                    alert(response.data || salonBooking.messages.error);
                }
            },
            error: function() {
                submitBtn.removeClass('loading').prop('disabled', false);
                loadingOverlay.removeClass('show');
                alert(salonBooking.messages.error);
            }
        });
    });
    
    // نمایش مودال موفقیت
    function showSuccessModal() {
        const serviceName = serviceSelect.find('option:selected').text();
        const details = `
            <strong>${nameInput.val().trim()}</strong><br>
            📅 تاریخ: ${dateInput.val().trim()}<br>
            🕐 ساعت: ${selectedTime}<br>
            ✂️ سرویس: ${serviceName}
        `;
        
        $('#reservationDetails').html(details);
        successModal.addClass('show');
    }
    
    // بستن مودال
    $('#closeModal').on('click', function() {
        successModal.removeClass('show');
    });
    
    // بستن با کلیک بیرون مودال
    successModal.on('click', function(e) {
        if (e.target === this) {
            successModal.removeClass('show');
        }
    });
});