<?php
$elements = array('help');
$heading = 'Zuluru';
if (isset ($controller)) {
	$elements[] = $controller;
	$heading = $controller;
	if (isset ($topic)) {
		$elements[] = $topic;
		$heading = null;
		if (isset ($item)) {
			$elements[] = $item;
		}
	}
}

foreach ($elements as $element) {
	$this->Html->addCrumb (__(Inflector::humanize($element), true));
}

if ($heading !== null) {
	echo $this->Html->tag ('h2', __(Inflector::humanize($heading), true) . ' ' . __('Help', true));
	$elements[] = 'index';
}
echo $this->element(implode('/', $elements));

if (isset ($controller) && !isset ($topic)) {
	echo $this->Html->para (null, $this->Html->link (__('Return to main help page', true), array('controller' => 'help')));
}

$body = htmlspecialchars ('I have a suggestion for the Zuluru online help page at ' . implode(' : ', $elements));
?>

<p>If you have suggestions for additions, changes or other improvements to this online help, please send them to <?php
echo $this->Html->link ('admin@zuluru.org', "mailto:admin@zuluru.org?subject=Zuluru%20Online%20Help%20Suggestion&body=$body");
?>.</p>
