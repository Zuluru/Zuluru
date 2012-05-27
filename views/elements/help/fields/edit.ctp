<p>The "edit field" page is used to update details of your fields. Only those with "volunteer" status have permission to edit field details.</p>
<p>The "create field" page is essentially identical to this page.</p>
<?php
echo $this->element('help/topics', array(
		'section' => 'fields/edit',
		'topics' => array(
			'num' => 'Number',
			'is_open',
		),
));
?>
