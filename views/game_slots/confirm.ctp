<div class="gameSlots form">
<?php echo $this->Form->create('GameSlot', array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php printf(__('Confirm %s', true), __('Game Slots', true)); ?></legend>
		<?php
		// Some of the last form's fields need to be carried through as hidden fields
		$hidden = $this->data;
		unset ($hidden['Field']);
		// ...and one new field
		$hidden['confirm'] = true;
		echo $this->element('hidden', array('fields' => $hidden));

		// Build the list of dates to re-use
		$weeks = $skipped = array();
		$date = strtotime ($this->data['GameSlot']['game_date']);
		while (count($weeks) < $this->data['GameSlot']['weeks']) {
			$key = date ('Y-m-d', $date);
			if (!array_key_exists($key, $holidays)) {
				$weeks[] = $this->ZuluruTime->date ($date);
			} else {
				$skipped[] = $this->ZuluruTime->date ($date) . ': ' . $holidays[$key];
			}
			$date += WEEK;
		}

		if (!empty($skipped)) {
			echo $this->Html->para(null, __('Game slots will not be created on the following holidays:', true) . $this->Html->nestedList ($skipped));
		}
		?>

		<p>Click a field name below to edit the list of game slots that will be created for that field.</p>
		<ul>
		<?php
		foreach ($regions as $region) {
			foreach ($region['Facility'] as $facility) {
				foreach ($facility['Field'] as $field) {
					if (array_key_exists ($field['id'], $this->data['Field'])) {
						echo $this->element ('game_slots/confirm', array('facility' => $facility, 'field' => $field, 'weeks' => $weeks));
					}
				}
			}
		}
		?>
		</ul>
	</fieldset>
<?php echo $this->Form->end(__('Create Slots', true));?>
</div>

<?php
echo $this->Html->scriptBlock("
$(document).ready(function() {
	$('.hidden').hide();

	// When the name of a field is clicked, toggle display of game slots for that field.
	$('span.name').click(function() {
		$(this).next('div').toggle();
	});
});
");
?>
