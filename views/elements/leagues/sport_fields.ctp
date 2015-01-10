<?php
echo $this->ZuluruForm->input('Division.ratio', array(
		'label' => __('Gender Ratio', true),
		'options' => Configure::read('sport.ratio'),
		'hide_single' => true,
		'empty' => '---',
		'after' => $this->Html->para (null, __('Gender format for the division.', true)),
));
?>
