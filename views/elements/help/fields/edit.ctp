<p>The "edit field" page is used to update details of your fields. Only those with "volunteer" status have permission to edit field details.</p>
<p>The data that can be updated on the edit page depends on whether the field being edited is a parent or child field. A future version of Zuluru will simplify this structure.</p>
<p>The "create field" page is essentially identical to this page, except that it accomodates both parent and child setups.</p>
<?php
echo $this->element('help/topics', array(
		'section' => 'fields/edit',
		'topics' => array(
			'name',
		),
));
?>
