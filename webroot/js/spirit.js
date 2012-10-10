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
	jQuery('fieldset.spirit').find('input').attr('disabled', 'disabled');
	jQuery('fieldset.spirit').find('select').attr('disabled', 'disabled');
	jQuery('fieldset.spirit').find('textarea').attr('disabled', 'disabled');
	jQuery('fieldset.spirit').css('display', 'none');
}

function enableSpirit() {
	jQuery('fieldset.spirit').find('input').removeAttr('disabled');
	jQuery('fieldset.spirit').find('select').removeAttr('disabled');
	jQuery('fieldset.spirit').find('textarea').removeAttr('disabled');
	jQuery('fieldset.spirit').css('display', '');
}
