<p>The ratings calculator chosen for the division will affect how team ratings are calculated.</p>
<?php
$types = Configure::read('options.rating_calculator');
echo $this->element('help/topics', array(
		'section' => 'divisions/edit/rating_calculator',
		'topics' => $types,
));
?>
