<?php
$replacements = array(
	' And ' => ' and ',
	' Or ' => ' or ',
);

foreach ($topics as $topic => $title)
{
	// TODO: Add a name anchor here, and a list of topics at the top
	// TODO: Handle one more level of nesting in topics

	if (is_numeric ($topic)) {
		$topic = $title;
		$title = strtr (Inflector::humanize($topic), $replacements);
	}

	echo $this->Html->tag ('h3', __($title, true));
	echo $this->element("help/$section/$topic");
}
?>