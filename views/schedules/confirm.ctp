<?php
$this->Html->addCrumb (__('League', true));
$this->Html->addCrumb ($league['League']['long_name']);
$this->Html->addCrumb (__('Add Games', true));
$this->Html->addCrumb (__('Confirm Selections', true));
?>

<div class="schedules add">
<p>The following information will be used to create your games:</p>
<h3>What:</h3>
<p><?php
echo $desc;
if (array_key_exists('name', $this->data['Game'])) {
	printf(__(' (pool names are %s)', true), implode(', ', $this->data['Game']['name']));
}
?></p>
<h3>Start date:</h3>
<p><?php echo $this->ZuluruTime->fulldate($start_date); ?></p>

<?php echo $this->element('schedules/exclude'); ?>

<h3>Publication:</h3>
<p>Games will <?php echo ($this->data['Game']['publish'] ? '' : 'NOT '); ?>be published.</p>

<?php
echo $this->Form->create ('Game', array('url' => Router::normalize($this->here)));
$this->data['Game']['step'] = 'finalize';
echo $this->element('hidden', array('fields' => $this->data));
?>

<?php echo $this->Form->end(__('Create games', true)); ?>

</div>