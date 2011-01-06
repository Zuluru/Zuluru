<?php
if (isset ($field)) {
	$this->Html->addCrumb ($field['Field']['long_name']);
}
$this->Html->addCrumb (__('Game Slots', true));
$this->Html->addCrumb (__('Create', true));
?>

<div class="gameSlots form">
<?php echo $this->Form->create('GameSlot');?>
	<fieldset>
 		<legend><?php
		printf(__('Add %s', true), __('Game Slots', true));
 		if (isset ($field)) {
 			echo ': ' . $field['Field']['long_name'];
 		}
		?></legend>
<?php
if (isset ($field)) {
	echo $this->Form->hidden("Field.{$field['Field']['id']}", array('value' => 1));
} else {
?>
		<fieldset>
			<legend><?php __('Field Selection'); ?></legend>
			<p class="warning">NOTE: By default, checking a field here will create game slots for ALL open fields at that site.
			If you want to create game slots for selected fields, click the main field name to see the list of other fields at that site.</p>
			<div class="actions">
				<ul>
<?php
	$regions = array();
	foreach ($fields as $field) {
		if (!array_key_exists ($field['Region']['id'], $regions)) {
			$regions[$field['Region']['id']] = array('name' => $field['Region']['name'], 'fields' => '');
			echo $this->Html->tag('li',
				$this->Html->link('Hide ' . __($field['Region']['name'], true), '#', array(
						'id' => "hide{$field['Region']['id']}",
						'onclick' => "hideFieldset('{$field['Region']['id']}'); return false;",
			)));
		}

		// Build the list of child fields to associate with the parent
		$child_fields = '';
		foreach ($field['ChildField'] as $child) {
			$child_fields .= $this->Form->input("Field.{$child['id']}", array(
					'label' => $child['num'],
					'type' => 'checkbox',
					'hiddenField' => false,
			));
		}

		// Build the parent field input and add to the inputs for the region
		$field_details = $this->Form->input("Field.{$field['Field']['id']}", array(
				'div' => 'input checkbox field',
				'label' => "{$field['Field']['name']} {$field['Field']['num']}",
				'type' => 'checkbox',
				'hiddenField' => false,
				'after' => $this->Html->tag ('div', $child_fields, array('class' => 'hidden')),
		));
		$regions[$field['Region']['id']]['fields'] .= $field_details;
	}
?>
				</ul>
			</div>
			<div></div>

<?php
	foreach ($regions as $id => $region):
?>
			<fieldset id="region<?php echo $id; ?>">
				<legend><?php __($region['name']); ?></legend>
				<div class="actions">
					<ul>
						<li><?php
						echo $this->Html->link('Select all', '#', array(
											'id' => "select$id",
											'onclick' => "selectAll('$id'); return false;",
						));
						echo $region['fields'];
						?></li>
					</ul>
				</div>
			</fieldset>
<?php
	endforeach;
?>
		</fieldset>
<?php
}

echo $this->Form->input('game_start', array(
		'label' => __('Game start time', true),
		'after' => $this->Html->para(null, __('Time for games in this timeslot to start.', true)),
));
echo $this->Form->input('game_end', array(
		'label' => __('Game timecap', true),
		'empty' => '---',
		'after' => $this->Html->para(null, __('Time for games in this timeslot to end. Choose "---" to assign the default timecap (dark) for that week.', true)),
));
echo $this->Form->input('game_date', array(
		'label' => __('First date', true),
		'minYear' => Configure::read('options.year.gameslot.min'),
		'maxYear' => Configure::read('options.year.gameslot.max'),
		'after' => $this->Html->para(null, __('Date of the first game slot to add.', true)),
));
echo $this->Form->input('weeks', array(
		'label' => __('Weeks to repeat', true),
		'options' => make_options (range (1, 26)),
		'after' => $this->Html->para(null, __('Number of weeks to repeat this gameslot.', true)),
));
?>
		<fieldset>
			<legend><?php __('Make Gameslot Available To'); ?></legend>
			<div id="league_list">
			</div>
		</fieldset>
	</fieldset>
<?php echo $this->Form->end(__('Continue', true));?>
</div>

<?php
$this->ZuluruHtml->script ('datepicker', array('inline' => false));
$url = $this->Html->url (array('controller' => 'leagues', 'action' => 'select'));

// Add JavaScript functions for "select all" buttons, hiding blocks of fields, and populating the league list
// TODO: Make hideFieldset and selectAll more generic and move to a .js file
echo $this->Html->scriptBlock("
$(document).ready(function() {
	update_leagues();
	$('select[id*=GameSlotGameDate]').change(function(){update_leagues();});
	$('.hidden').hide();

	// When the label for a parent field is clicked, toggle display of child fields.
	// Return false, so that the associated checkbox isn't also toggled.
	$('div.field > label').click(function() {
		$(this).closest('div').children('div.hidden').toggle();
		return false;
	});

	// When the checkbox for a parent field is toggled, also toggle all child fields.
	$('div.field > input').change(function() {
		var checked = $(this).is(':checked');
		$(this).closest('div').children('div.hidden').find('input').attr('checked', checked);
	});
});

function update_leagues(){
	var date = $('#GameSlotGameDateYear').val() + '-' + $('#GameSlotGameDateMonth').val() + '-' + $('#GameSlotGameDateDay').val();

	$.ajax({
		type: 'GET',
		url: '$url/' + date,
		success: function(leagues){
			$('#league_list').html(leagues);
		},
		error: function(message){
			alert(message);
		}
	});
}

function hideFieldset(index) {
	var label = $('#hide' + index).text();
	if (label.substr(0,4) == 'Hide') {
		$('#region' + index).css('display', 'none');
		$('#hide' + index).text('Show' + label.substr(4));
	} else {
		$('#region' + index).css('display', '');
		$('#hide' + index).text('Hide' + label.substr(4));
	}
}

function selectAll(index) {
	var label = $('#select' + index).text();
	var check = true;
	if (label.substr(0,6) == 'Select') {
		$('#select' + index).text('Unselect all');
	} else {
		$('#select' + index).text('Select all');
		check = false;
	}

	$('#region' + index + ' :checkbox').each(function () {
		$(this).attr('checked', check);
	});
}
");
?>
