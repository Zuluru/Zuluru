<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb ($person['Person']['full_name']);
$this->Html->addCrumb (__('Sign Waiver', true));
?>

<?php
echo $this->element ("people/waiver/$type");
echo $this->Form->create('Person', array('url' => $this->here));
echo $this->Html->para(null,
	$this->Form->input('signed', array(
			'options' => array(
				'yes' => 'I agree to the above conditions',
				'no' => 'I DO NOT agree to the above conditions',
			),
			'type' => 'radio',
			'legend' => false,
	))
);
echo $this->Form->end(__('Submit', true));
?>
