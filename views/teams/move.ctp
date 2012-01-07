<?php
$this->Html->addCrumb (__('Team', true));
$this->Html->addCrumb ($team['Team']['name']);
$this->Html->addCrumb (__('Move', true));
?>

<div class="teams move">
<h2><?php echo __('Move Team', true) . ': ' . $team['Team']['name'];?></h2>

<?php
echo $this->Form->create('Team', array('url' => Router::normalize($this->here)));
echo $this->Form->input('to', array(
		'label' => __('Division to move this team to:', true),
		'options' => Set::combine ($divisions, '{n}.Division.id', '{n}.Division.full_league_name'),
));

// TODO: Option for swapping this team with another, dynamically load team list into
// drop-down when "swap" checkbox is checked and a destination is selected

echo $this->Form->end('Move');
?>

</div>