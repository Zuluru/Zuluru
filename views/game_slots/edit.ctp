<?php
$this->Html->addCrumb (__('Game Slot', true));
$this->Html->addCrumb (__('Edit', true));
?>

<div class="gameSlots form">
<?php echo $this->Form->create('GameSlot', array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php printf(__('Edit %s', true), __('Game Slot', true)); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->ZuluruForm->input('game_start', array(
				'label' => __('Game start time', true),
		));
		echo $this->ZuluruForm->input('game_end', array(
				'label' => __('Game timecap', true),
				'empty' => '---',
				'after' => $this->Html->para(null, __('Choose "---" to assign the default timecap (dark).', true)),
		));
		echo $this->ZuluruForm->input('game_date', array(
				'minYear' => Configure::read('options.year.gameslot.min'),
				'maxYear' => Configure::read('options.year.gameslot.max'),
				'looseYears' => true,
		));
	?>
	</fieldset>
	<fieldset>
		<legend><?php __('Make Gameslot Available To'); ?></legend>
		<div id="division_list">
		<?php
		foreach ($divisions as $key => $division) {
			$checked = Set::extract("/DivisionGameslotAvailability[division_id=$key]", $this->data);
			echo $this->Form->input ("Division.{$key}", array(
					'label' => $division,
					'type' => 'checkbox',
					'hiddenField' => false,
					'checked' => !empty($checked),
			));
		}
		?>
		</div>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<?php
echo $this->ZuluruHtml->script ('datepicker', array('inline' => false));
$url = $this->Html->url (array('controller' => 'divisions', 'action' => 'select', 'affiliate' => $affiliate));

// Add JavaScript functions for "select all" buttons, hiding blocks of fields, and populating the division list
echo $this->Html->scriptBlock("
jQuery(document).ready(function($) {
	$('select[id*=GameSlotGameDate]').change(function(){update_divisions();});
});

function update_divisions(){
	var date = jQuery('#GameSlotGameDateYear').val() + '-' + jQuery('#GameSlotGameDateMonth').val() + '-' + jQuery('#GameSlotGameDateDay').val();

	jQuery.ajax({
		type: 'GET',
		url: '$url/' + date,
		success: function(divisions){
			jQuery('#division_list').html(divisions);
		},
		error: function(message){
			alert(message);
		}
	});
}
");
?>