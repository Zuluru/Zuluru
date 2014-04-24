<?php // TODO: Replace this with a class ?>
<div id="zuluru">
<p>Following are the various spirit questionnaire options, with a short description and preview of each.</p>
<?php
$options = Configure::read('options.spirit_questions');
foreach ($options as $option => $name):
	$spirit_obj = AppController::_getComponent('Spirit', $option);
?>
<h2><?php echo $name; ?></h2>
<div class="form">
<?php
	echo $this->Html->para(null, __($spirit_obj->description, true));
	echo $this->element('spirit/legend', compact('spirit_obj'));
	echo $this->element('formbuilder/input', array('prefix' => null, 'preview' => true, 'questions' => $spirit_obj->questions));
?>
</div>
<?php
endforeach;
?>
</div>
