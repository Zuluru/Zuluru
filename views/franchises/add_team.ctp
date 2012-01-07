<?php
$this->Html->addCrumb (__('Franchise', true));
$this->Html->addCrumb (__('Add Team to Franchise', true));
$this->Html->addCrumb ($franchise['Franchise']['name']);
?>

<div class="franchises add_team">
<h2><?php echo sprintf(__('Add %s', true), __('Team', true)) . ': ' . $franchise['Franchise']['name'];?></h2>

<?php
echo $this->Html->para(null, __('Select a team from your history below to add to this franchise.', true));
echo $this->Html->para('highlight-message', __('Note that you can only add teams that you are a captain, assistant captain or coach of. This may necessitate temporarily transferring this franchise to someone else.', true));
$options = array();
foreach ($teams['Team'] as $team) {
	$options[$team['id']] = "{$team['name']} ({$team['Division']['full_league_name']})";
}
echo $this->Form->create(false, array('url' => Router::normalize($this->here)));
echo $this->Form->input ('team_id', array(
		'label' => false,
		'options' => $options,
		'empty' => '-- select from list --',
));
echo $this->Form->end(__('Add team', true));
?>

</div>
