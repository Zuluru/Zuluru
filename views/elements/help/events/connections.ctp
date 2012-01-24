<p>The "manage connections" page is used to define logical connections between your events.</p>
<?php
echo $this->element('help/topics', array(
		'section' => 'events/edit',
		'topics' => array(
			'predecessor',
			'successorto' => 'Successor To',
			'successor',
			'predecessorto' => 'Predecessor To',
			'alternate',
		),
));
?>
