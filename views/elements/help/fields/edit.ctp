<p><?php
printf(__('The "edit %s" page is used to update details of your %s.', true),
	__(Configure::read('ui.field'), true), __(Configure::read('ui.fields'), true)
);
?></p>
<p><?php
printf(__('The "create %s" page is essentially identical to this page.', true),
	__(Configure::read('ui.field'), true)
);
?></p>
<?php
echo $this->element('help/topics', array(
		'section' => 'fields/edit',
		'topics' => array(
			'num' => 'Number',
			'is_open',
		),
));
?>
