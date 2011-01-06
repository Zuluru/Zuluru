function suggestSpirit(index) {
	var sotg = 0;
	$('input:checked[id^=SpiritEntry' + index + 'Q]').each(function() {
//		if ($(this).attr('id') != 'SpiritEntry' + index + 'ScoreEntryPenalty') {
		sotg += parseInt($(this).val());
//		}
	});
	$('#SpiritEntry' + index + 'EnteredSotg').val(sotg);
}

function disableSpirit() {
	$('fieldset.spirit').find('input').attr('disabled', 'disabled');
	$('fieldset.spirit').find('select').attr('disabled', 'disabled');
	$('fieldset.spirit').find('textarea').attr('disabled', 'disabled');
	$('fieldset.spirit').css('display', 'none');
}

function enableSpirit() {
	$('fieldset.spirit').find('input').removeAttr('disabled');
	$('fieldset.spirit').find('select').removeAttr('disabled');
	$('fieldset.spirit').find('textarea').removeAttr('disabled');
	$('fieldset.spirit').css('display', '');
}
