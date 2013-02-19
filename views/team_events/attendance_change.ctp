<?php
$this->Html->addCrumb (__('Team Events', true));
$this->Html->addCrumb (__('Attendance Change', true));
$this->Html->addCrumb ($team['name']);
?>

<div class="teamEvents form">
<h2><?php  __('Attendance Change'); ?></h2>
	<dl>
		<dt><?php __('Team'); ?></dt>
		<dd><?php echo $this->element('teams/block', array('team' => $team)); ?></dd>
		<dt><?php __('Event'); ?></dt>
		<dd><?php echo $event['TeamEvent']['name']; ?></dd>
		<dt><?php __('Description'); ?></dt>
		<dd><?php echo $event['TeamEvent']['description']; ?></dd>
		<dt><?php __('Date'); ?></dt>
		<dd><?php
		echo $this->ZuluruTime->date($event['TeamEvent']['date']);
		?></dd>
		<dt><?php __('Start Time'); ?></dt>
		<dd><?php
		echo $this->ZuluruTime->time($event['TeamEvent']['start']);
		?></dd>
		<dt><?php __('End Time'); ?></dt>
		<dd><?php
		echo $this->ZuluruTime->time($event['TeamEvent']['end']);
		?></dd>
	</dl>

<?php
$status_descriptions = Configure::read('attendance');
$roster_descriptions = Configure::read('options.roster_role');
echo $this->Html->para(null, __('You are attempting to change attendance for', true) . ' ' .
	$this->element('people/block', compact('person')) .
	' (' . $roster_descriptions[$person['Team'][0]['TeamsPerson']['role']] . ').');
echo $this->Html->para(null, __('Current status:', true) . ' ' .
	$this->Html->tag('strong', __($status_descriptions[$attendance['status']], true)));

echo $this->Html->para(null, __('Possible attendance options are:', true));
echo $this->Form->create('Person', array('url' => Router::normalize($this->here)));
echo $this->Form->input('status', array(
		'legend' => false,
		'type' => 'radio',
		'options' => $attendance_options,
		'default' => $attendance['status'],
));
echo $this->Form->input('comment', array(
		'label' => __('You may optionally add a comment', true),
		'size' => 80,
		'default' => $attendance['comment'],
));
if ($is_captain && array_key_exists(ATTENDANCE_INVITED, $attendance_options)) {
	echo $this->Form->input('note', array(
			'label' => __('You may optionally add a personal note which will be included in the invitation email to the player', true),
			'size' => 80,
	));
}
echo $this->Form->end(__('Submit', true));

$invited = ATTENDANCE_INVITED;
echo $this->Html->scriptBlock("
function statusChanged() {
	if (jQuery('#PersonStatus$invited').attr('checked')) {
		jQuery('#PersonNote').closest('div').show();
	} else {
		jQuery('#PersonNote').closest('div').hide();
	}
}
");
$this->Js->get('input:radio')->event('click', 'statusChanged()', array('stop' => false));
$this->Js->buffer('statusChanged();');

?>
