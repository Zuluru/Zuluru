<?php
if (isset ($field)) {
	$this->Html->addCrumb ($field['Field']['long_name']);
}
$this->Html->addCrumb (__('Game Slots', true));
$this->Html->addCrumb (__('Create', true));
?>

<div class="gameSlots form">
<?php echo $this->Form->create('GameSlot', array('url' => Router::normalize($this->here)));?>
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
	echo $this->Form->hidden('sport', array('id' => 'sport', 'value' => $field['Field']['sport']));
} else {
?>
		<fieldset>
			<legend><?php printf(__('%s Selection', true), Configure::read('ui.field_cap')); ?></legend>
			<p class="warning-message">NOTE: By default, checking a facility here will create game slots for ALL open <?php __(Configure::read('ui.fields')); ?> at that facility.
			If you want to create game slots for selected <?php __(Configure::read('ui.fields')); ?>, click the facility name to see the list of <?php __(Configure::read('ui.fields')); ?> at that facility.</p>
<?php
	$sports = Configure::read('options.sport');
	if (count($sports) > 1) {
		echo $this->element('selector', array(
				'title' => 'Sport',
				'options' => $sports,
				'include_form' => false,
				'include_empty' => false,
		));
		$this->Js->get('#sport')->event('change', 'update_divisions();');
	} else {
		echo $this->Form->hidden('sport', array('id' => 'sport', 'value' => current($sports)));
	}
?>
			<div class="actions">
				<ul>
<?php
	foreach ($regions as $key => $region){
		$ids = Set::extract('/Facility/Field/id', $region);
		if (empty($ids)) {
			unset ($regions[$key]);
			continue;
		}
		$region_sports = array_unique(Set::extract('/Facility/Field/sport', $region));

		echo $this->Html->tag('li',
			$this->Html->link('Hide ' . __($region['Region']['name'], true), '#', array(
					'id' => "hide{$region['Region']['id']}",
					'class' => $this->element('selector_classes', array('title' => 'Sport', 'options' => $region_sports)),
					'onclick' => "hideFieldset('{$region['Region']['id']}'); return false;",
		)));
	}
?>
				</ul>
			</div>
			<div></div>

<?php
	foreach ($regions as $region):
		$region_sports = array_unique(Set::extract('/Facility/Field/sport', $region));
?>
			<fieldset id="region<?php echo $region['Region']['id']; ?>" class="<?php echo $this->element('selector_classes', array('title' => 'Sport', 'options' => $region_sports)); ?>">
				<legend><?php __($region['Region']['name']); ?></legend>
				<div class="actions">
					<ul>
						<li><?php
		echo $this->Html->link('Select all', '#', array(
							'id' => "select{$region['Region']['id']}",
							'onclick' => "selectAll('{$region['Region']['id']}'); return false;",
		));

		foreach ($region['Facility'] as $facility) {
			// Build the list of fields to associate with the facility
			$fields = '';
			foreach ($facility['Field'] as $field) {
				$fields .= $this->Html->tag('span',
						$this->Form->input("Field.{$field['id']}", array(
							'label' => $field['num'],
							'class' => $this->element('selector_classes', array('title' => 'Sport', 'options' => $field['sport'])),
							'type' => 'checkbox',
							'hiddenField' => false,
						)),
						array(
							'class' => $this->element('selector_classes', array('title' => 'Sport', 'options' => $field['sport'])),
						)
				);
			}

			// Build the facility input
			$facility_sports = array_unique(Set::extract('/Field/sport', $facility));
			echo $this->Html->tag('span',
					$this->Form->input("Facility.{$facility['id']}", array(
						'div' => 'input checkbox field link_like ' . $this->element('selector_classes', array('title' => 'Sport', 'options' => $facility_sports)),
						'label' => $facility['name'],
						'class' => $this->element('selector_classes', array('title' => 'Sport', 'options' => $facility_sports)),
						'type' => 'checkbox',
						'hiddenField' => false,
						'after' => $this->Html->tag ('div', $fields, array('class' => 'hidden')),
					)),
					array(
						'class' => $this->element('selector_classes', array('title' => 'Sport', 'options' => $facility_sports)),
					)
			);
		}
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
			<div id="division_list">
			</div>
		</fieldset>
	</fieldset>
<?php echo $this->Form->end(__('Continue', true));?>
</div>

<?php
echo $this->ZuluruHtml->script ('datepicker.js', array('inline' => false));
$url = $this->Html->url (array('controller' => 'divisions', 'action' => 'select', 'affiliate' => $affiliate));

// Add JavaScript functions for "select all" buttons, hiding blocks of fields, and populating the division list
// TODO: Make hideFieldset and selectAll more generic and move to a .js file
$spinner = $this->ZuluruHtml->icon('spinner.gif');
echo $this->Html->scriptBlock("
jQuery(document).ready(function($) {
	update_divisions();
	jQuery('select[id*=GameSlotGameDate]').on('change', function(){update_divisions();});
	jQuery('.hidden').hide();

	// When the label for a parent field is clicked, toggle display of child fields.
	// Return false, so that the associated checkbox isn't also toggled.
	jQuery('div.field > label').click(function() {
		jQuery(this).closest('div').children('div.hidden').toggle();
		return false;
	});

	// When the checkbox for a parent field is toggled, also toggle all child fields.
	jQuery('div.field > input').on('change', function() {
		var checked = jQuery(this).is(':checked');
		jQuery(this).closest('div').children('div.hidden').find('input').prop('checked', checked);
	});
});

function update_divisions(){
	var date = jQuery('#GameSlotGameDateYear').val() + '-' + jQuery('#GameSlotGameDateMonth').val() + '-' + jQuery('#GameSlotGameDateDay').val();

	jQuery('#division_list').html('$spinner');
	jQuery.ajax({
		type: 'GET',
		url: '$url/' + date + '/' + jQuery('#sport').val(),
		success: function(divisions){
			jQuery('#division_list').html(divisions);
		},
		error: function(message){
			alert(message);
		}
	});
}

function hideFieldset(index) {
	var label = jQuery('#hide' + index).text();
	if (label.substr(0,4) == 'Hide') {
		jQuery('#region' + index).css('display', 'none');
		jQuery('#hide' + index).text('Show' + label.substr(4));
	} else {
		jQuery('#region' + index).css('display', '');
		jQuery('#hide' + index).text('Hide' + label.substr(4));
	}
}

function selectAll(index) {
	var label = jQuery('#select' + index).text();
	var check = true;
	if (label.substr(0,6) == 'Select') {
		jQuery('#select' + index).text('Unselect all');
	} else {
		jQuery('#select' + index).text('Select all');
		check = false;
	}

	jQuery('#region' + index + ' :checkbox').each(function () {
		jQuery(this).prop('checked', check);
	});
}
");
?>
