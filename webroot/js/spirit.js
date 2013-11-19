function suggestSpirit(index) {
	var sotg = 0;
	jQuery('input:checked[id^=SpiritEntry' + index + 'Q]').each(function() {
//		if (jQuery(this).attr('id') != 'SpiritEntry' + index + 'ScoreEntryPenalty') {
		sotg += parseInt(jQuery(this).val());
//		}
	});
	jQuery('#SpiritEntry' + index + 'EnteredSotg').val(sotg);
}

function disableSpirit() {
	jQuery('fieldset.spirit').find('input').prop('disabled', true);
	jQuery('fieldset.spirit').find('select').prop('disabled', true);
	jQuery('fieldset.spirit').find('textarea').prop('disabled', true);
	jQuery('fieldset.spirit').css('display', 'none');
}

function enableSpirit() {
	jQuery('fieldset.spirit').find('input').prop('disabled', false);
	jQuery('fieldset.spirit').find('select').prop('disabled', false);
	jQuery('fieldset.spirit').find('textarea').prop('disabled', false);
	jQuery('fieldset.spirit').css('display', '');
}
