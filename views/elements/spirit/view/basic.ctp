<?php
if ($spirit) {
	if ($is_admin || $is_coordinator || $league['display_sotg'] === 'all') {
		echo $this->element('formbuilder/view', array('questions' => $spirit_obj->questions, 'answers' => $spirit));
	}
	if ($division['most_spirited'] != 'never' && !empty($spirit['most_spirited'])) {
		echo $this->Html->para(null, __('Most spirited player', true) . ': ' .
				$this->element('people/block', array('person' => $spirit['MostSpirited'])));
	}
}
?>
