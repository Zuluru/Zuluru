<div id="rating_dialog_<?php echo $sport; ?>" class="form" title="<?php echo ZULURU; ?> Player Rating">
<?php
Configure::load("sport/$sport");
$questions = Configure::read('sport.rating_questions');
?>

<?php
// Making this a dialog pulls it out of the main #zuluru div, breaking formatting.
// TODO: Switch zuluru id to class, to avoid creating an invalid DOM.
?>
<div id="zuluru">
<p>Fill out this questionnaire and then click "Calculate" below to figure out the skill level you should use in <?php echo ZULURU; ?>.</p>
<p>The questionnaire is divided into <?php echo implode(' and ', array_keys($questions)); ?> sections.
Answer each as honestly as possible, and the resulting <?php echo ZULURU; ?> rating should be fairly accurate.
When answering questions regarding relative skills, compare yourself to the average of all people playing the sport,
not only those that you regularly compete against.</p>
<p>The calculated value will be entered on the <?php echo ZULURU; ?> profile editing form.</p>

<form name="rating_<?php echo $sport; ?>">

<?php
$i = 1;
$min = $max = 0;
foreach ($questions as $group_label => $group_questions) {
	echo $this->Html->tag('h2', $group_label . ' ' . __('Questions', true) . ':') . "\n";
	foreach ($group_questions as $label => $options) {
		$min += min(array_keys($options));
		$max += max(array_keys($options));
		echo $this->Form->input("{$sport}_q{$i}", array(
			'before' => $this->Html->tag('strong', "$i. $label"),
			'between' => $this->Html->tag('br'),
			'type' => 'radio',
			'options' => $options,
			'hiddenField' => false,
		)) . "\n";
		++ $i;
	}
}
?>

</form>
</div>
</div>

<?php
echo $this->Html->scriptBlock ("
jQuery('#rating_dialog_$sport').dialog({
	autoOpen: false,
	buttons: {
		'Calculate': function() {
			if (calculate_rating('$sport', $min, $max)) {
				jQuery('#rating_dialog_$sport').dialog('close');
			}
		},
		'Cancel': function() {
			jQuery('#rating_dialog_$sport').dialog('close');
		}
	},
	modal: true,
	resizable: false,
	width: 640,
	height: 480
});
");

if (!Configure::read('skill_rating_functions_added')) {
	Configure::write('skill_rating_functions_added', true);
	echo $this->Html->scriptBlock ("
// function to calculate the rating
function calculate_rating(sport, min, max) {
	var sum = 0;

	// Check for skipped questions and show error
	var okay = true;
	jQuery('form[name=rating_' + sport + '] div.radio').each(function() {
		if (jQuery(this).children('input:checked').size() == 0) {
			jQuery(this).addClass('error');
			okay = false;
		} else {
			jQuery(this).removeClass('error');
		}
	});
	if (!okay) {
		alert('You must answer all questions.');
		return false;
	}

	// Sum up all selected answers
	jQuery('form[name=rating_' + sport + '] input:checked').each(function() {
		sum += parseInt(jQuery(this).val());
	});

	// Move the sum so the average is zero
	sum -= (max+min)/2;

	// Scale the result to a rating between 0.5 and 9.5
	var rating = 9/(max-min) * sum + 5;

	// Round to an integer (1 to 10)
	rating = Math.round(rating);

	// put the result into the text box
	jQuery(jQuery('#rating_dialog_' + sport).data('field')).val(rating);
	return true;
}

function dorating(sport, field) {
	jQuery('#rating_dialog_' + sport).data('field', field);
	jQuery('#rating_dialog_' + sport).dialog('open');
}
	");
}
?>
