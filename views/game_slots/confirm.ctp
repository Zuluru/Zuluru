<div class="gameSlots form">
<?php echo $this->Form->create('GameSlot', array('url' => $this->here));?>
	<fieldset>
 		<legend><?php printf(__('Confirm %s', true), __('Game Slots', true)); ?></legend>
		<p>Click a field name below to edit the list of game slots that will be created for that field.</p>
		<ul>
		<?php
		// Some of the last form's fields need to be carried through as hidden fields
		$hidden = $this->data;
		unset ($hidden['Field']);
		// ...and one new field
		$hidden['confirm'] = true;
		echo $this->element('hidden', array('fields' => $hidden));

		// Build the list of dates to re-use
		$weeks = array();
		$start = strtotime ($this->data['GameSlot']['game_date']);
		for ($week = 0; $week < $this->data['GameSlot']['weeks']; ++ $week) {
			$weeks[] = $this->ZuluruTime->date ($start + $week * 7 * 24 * 60 * 60);
		}

		foreach ($fields as $field) {
			if (array_key_exists ($field['Field']['id'], $this->data['Field'])) {
				echo $this->element ('game_slots/confirm', array('field' => $field['Field'], 'weeks' => $weeks));
			}

			// Add all of the child fields
			foreach ($field['ChildField'] as $child) {
				if (array_key_exists ($child['id'], $this->data['Field'])) {
					$child['name'] = $field['Field']['name'];
					echo $this->element ('game_slots/confirm', array('field' => $child, 'weeks' => $weeks));
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
