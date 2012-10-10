<?php
$this->Html->addCrumb (__('Waivers', true));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->Form->value('Waiver.name'));
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="waivers form">
<?php echo $this->Form->create('Waiver', array('url' => Router::normalize($this->here))); ?>
	<fieldset>
 		<legend><?php printf(__(isset($add) ? 'Create %s' : 'Edit %s', true), __('Waiver', true)); ?></legend>
	<?php
		if (!isset($add)) {
			echo $this->Form->input('id');
		}
		echo $this->ZuluruForm->input('name', array(
			'size' => 60,
			'after' => $this->Html->para (null, __('Full name of this waiver.', true)),
		));
		if (isset ($add)) {
			echo $this->ZuluruForm->input('affiliate_id', array(
				'options' => $affiliates,
				'hide_single' => true,
				'empty' => '---',
			));
		}
		echo $this->ZuluruForm->input('description', array(
			'size' => 60,
			'after' => $this->Html->para (null, __('An extended description, shown solely to administrators, for example to differentiate between various "Membership" waivers.', true)),
		));
		if (!isset($can_edit_text) || $can_edit_text) {
			echo $this->ZuluruForm->input('text', array(
				'cols' => 60,
				'rows' => 30,
				'after' => $this->Html->para (null, __('Complete waiver text, HTML is allowed.', true)),
				'class' => 'mceAdvanced',
			));
		} else {
			echo $this->Html->para('highlight-message', __('This waiver has already been signed, so for legal reasons the text cannot be edited.', true));
		}
		echo $this->ZuluruForm->input('active');
		echo $this->ZuluruForm->input('expiry_type', array(
			'empty' => '---',
		));
	?>

	<fieldset id="start_and_end_options">
	<legend><?php __('Expiry Options'); ?></legend>
	<div id="fixed_dates_options">
	<?php
		echo $this->ZuluruForm->input('start_month', array(
			'options' => $this->Form->__generateOptions('month', array('monthNames' => true)),
			'label' => 'From month',
		));
		echo $this->ZuluruForm->input('start_day', array(
			'options' => $this->Form->__generateOptions('day'),
			'label' => 'From day',
		));
		echo $this->ZuluruForm->input('end_month', array(
			'options' => $this->Form->__generateOptions('month', array('monthNames' => true)),
			'label' => 'Through month',
		));
		echo $this->ZuluruForm->input('end_day', array(
			'options' => $this->Form->__generateOptions('day'),
			'label' => 'Through day',
		));
	?>
	</div>
	<div id="elapsed_time_options">
	<?php
		echo $this->ZuluruForm->input('duration', array(
			'size' => 5,
			'after' => ' ' . __('days', true),
		));
	?>
	</div>
	<div id="event_options">
	<?php
	echo $this->Html->para(null, __('Event waivers have no expiry options; they always expire after the event is done.', true));
	?>
	</div>
	</fieldset>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__('List Waivers', true), array('action' => 'index'));?></li>
<?php if (!isset ($add)): ?>
		<li><?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', 'waiver' => $this->Form->value('Waiver.id')), null, sprintf(__('Are you sure you want to delete # %s?', true), $this->Form->value('Waiver.id'))); ?></li>
<?php endif; ?>
	</ul>
</div>

<?php if (Configure::read('feature.tiny_mce')) $this->TinyMce->editor('advanced'); ?>
<?php
echo $this->Html->scriptBlock('
function expiry_type_changed() {
	jQuery("#start_and_end_options").find("select").attr("disabled", true);
	jQuery("#start_and_end_options").find("input").attr("disabled", true);
	jQuery("#start_and_end_options").children("div").css("display", "none");

	var div = jQuery("#WaiverExpiryType").val() + "_options";
	jQuery("#" + div).find("select").attr("disabled", false);
	jQuery("#" + div).find("input").attr("disabled", false);
	jQuery("#" + div).css("display", "");
}
');
$this->Js->get('#WaiverExpiryType')->event('change', 'expiry_type_changed();');
$this->Js->buffer('expiry_type_changed();');
?>