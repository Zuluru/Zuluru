// Adapted from http://www.cssnewbie.com/cross-browser-support-for-html5-placeholder-text-in-forms/

jQuery(function() {
	jQuery.support.placeholder = false;
	test = document.createElement('input');
	if('placeholder' in test) jQuery.support.placeholder = true;
});

jQuery(function() {
	if(!jQuery.support.placeholder) { 
		var active = document.activeElement;

		jQuery(':text').focus(function () {
			if (jQuery(this).attr('placeholder') != '' && jQuery(this).attr('placeholder') != undefined && jQuery(this).val() == jQuery(this).attr('placeholder')) {
				jQuery(this).val('').removeClass('hasPlaceholder');
			}
		}).blur(function () {
			if (jQuery(this).attr('placeholder') != '' && jQuery(this).attr('placeholder') != undefined && (jQuery(this).val() == '' || jQuery(this).val() == jQuery(this).attr('placeholder'))) {
				jQuery(this).val(jQuery(this).attr('placeholder'));
				jQuery(this).addClass('hasPlaceholder');
			}
		});

		jQuery(':password').focus(function () {
			if (jQuery(this).attr('placeholder') != '' && jQuery(this).attr('placeholder') != undefined && jQuery(this).val() == jQuery(this).attr('placeholder')) {
				jQuery(this).val('').removeClass('hasPlaceholder');
			}
		}).blur(function () {
			if (jQuery(this).attr('placeholder') != '' && jQuery(this).attr('placeholder') != undefined && (jQuery(this).val() == '' || jQuery(this).val() == jQuery(this).attr('placeholder'))) {
				jQuery(this).val(jQuery(this).attr('placeholder'));
				jQuery(this).addClass('hasPlaceholder');
			}
		});

		jQuery(':text').blur();
		jQuery(':password').blur();
		jQuery(active).focus();
		jQuery('form').submit(function () {
			jQuery(this).find('.hasPlaceholder').each(function() { jQuery(this).val(''); });
		});
	}
});
