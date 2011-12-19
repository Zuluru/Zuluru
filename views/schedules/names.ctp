<?php
$this->Html->addCrumb (__('League', true));
$this->Html->addCrumb ($league['League']['long_name']);
$this->Html->addCrumb (__('Add Games', true));
$this->Html->addCrumb (__('Set Bracket Names', true));
?>

<div class="schedules add">

<p>You are scheduling a tournament with multiple brackets. Please provide the names for each bracket.</p>

<?php
echo $this->Form->create ('Game', array('url' => Router::normalize($this->here)));
$this->data['Game']['step'] = 'names';
echo $this->element('hidden', array('fields' => $this->data));
?>

<fieldset>
<legend>Bracket Names</legend>
<?php
for ($i = 1; $i <= $x; ++ $i) {
	echo $this->Form->input("name.$i", array(
			'label' => sprintf(__('Bracket %d (teams %d to %d)', true), $i, ($i - 1) * $size + 1, $i * $size),
			'maxlength' => 25,
			'default' => $i,
	));
}

if ($r > 0) {
	echo $this->Form->input("name.$i", array(
			'label' => sprintf(__('Bracket %d (teams %d to %d)', true), $i, ($i - 1) * $size + 1, ($i - 1) * $size + $r),
			'max_length' => 25,
			'default' => $i,
	));
}
?>

</fieldset>

<?php echo $this->Form->end(__('Next step', true)); ?>

</div>