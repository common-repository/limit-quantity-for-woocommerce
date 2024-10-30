jQuery(function ($) {

	jQuery(document).on('keyup', '#_woo_max_qty_limit', function () {
		var regex,
		error;

		regex = new RegExp('[^\-0-9\%\\' + woocommerce_admin.decimal_point + ']+', 'gi');
		error = 'i18n_decimal_error';

		var value = $(this).val();
		var newvalue = value.replace(regex, '');

		if (value !== newvalue) {
			$(document.body).triggerHandler('wc_add_error_tip', [$(this), error]);
		} else {
			$(document.body).triggerHandler('wc_remove_error_tip', [$(this), error]);
		}
	})

})
