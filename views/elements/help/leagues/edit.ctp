<p><?php __('The "edit league" page is used to update details of your league. Only coordinators have permission to edit league details.'); ?></p>
<p><?php __('The "create league" page is essentially identical to this page.'); ?></p>
<?php
echo $this->element('help/topics', array(
		'section' => 'leagues/edit',
		'topics' => array(
			'name',
		),
));
?>
