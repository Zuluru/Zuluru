<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb ($team['Team']['name']);
if ($person_id) {
	$this->Html->addCrumb ($person['full_name']);
	$this->Html->addCrumb (__('Shirt Number', true));
} else {
	$this->Html->addCrumb (__('Shirt Numbers', true));
}
?>

<div class="numbers form">
<?php
echo $this->Form->create(false, array('url' => Router::normalize($this->here)));

if ($person_id):
	echo $this->ZuluruForm->input("TeamsPerson.0.number", array(
		'type' => 'number',
		'default' => $person['TeamsPerson']['number'],
	));
?>
<?php
else:
?>
	<fieldset>
 		<legend><?php __('Shirt Numbers'); ?></legend>
	<?php
	foreach ($team['Person'] as $key => $person) {
		echo $this->ZuluruForm->input("TeamsPerson.$key.number", array(
			'label' => $this->element('people/block', compact('person')),
			'type' => 'number',
			'default' => $person['TeamsPerson']['number'],
			));
		echo $this->Form->hidden("TeamsPerson.$key.person_id", array('value' => $person['id']));
	}
	?>
</fieldset>

<?php
endif;
?>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
