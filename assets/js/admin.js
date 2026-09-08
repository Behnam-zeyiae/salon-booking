jQuery(document).ready(function($) {
    'use strict';
    
    // تایید حذف
    $('.button-link-delete').on('click', function(e) {
        if (!confirm('آیا مطمئن هستید که می‌خواهید این مورد را حذف کنید؟')) {
            e.preventDefault();
        }
    });
    
    // انتخاب/عدم انتخاب همه ساعت‌ها
    $('.day-section h3').on('click', function() {
        const checkboxes = $(this).closest('.day-section').find('input[type="checkbox"]');
        const allChecked = checkboxes.filter(':checked').length === checkboxes.length;
        checkboxes.prop('checked', !allChecked);
    });
    
    // جستجو در جدول نوبت‌ها
    $('#searchBookings').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('.wp-list-table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // فیلتر وضعیت
    $('#filterStatus').on('change', function() {
        const status = $(this).val();
        if (status === '') {
            $('.wp-list-table tbody tr').show();
        } else {
            $('.wp-list-table tbody tr').hide();
            $('.wp-list-table tbody tr').filter(function() {
                return $(this).find('select[name="status"]').val() === status;
            }).show();
        }
    });
    
    // پیش‌نمایش تصویر
    $('.upload-image-button').on('click', function(e) {
        e.preventDefault();
        // افزودن آپلودر وردپرس
    });
});