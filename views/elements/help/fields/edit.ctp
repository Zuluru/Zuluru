<p>The "edit <?php __(Configure::read('ui.field')); ?>" page is used to update details of your <?php __(Configure::read('ui.fields')); ?>. Only those with "volunteer" status have permission to edit <?php __(Configure::read('ui.field')); ?> details.</p>
<p>The "create <?php __(Configure::read('ui.field')); ?>" page is essentially identical to this page.</p>
<?php
echo $this->element('help/topics', array(
		'section' => 'fields/edit',
		'topics' => array(
			'num' => 'Number',
			'is_open',
		),
));
?>
