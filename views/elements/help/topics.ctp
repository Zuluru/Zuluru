<?php
$replacements = array(
	' And ' => ' and ',
	' Or ' => ' or ',
);

foreach ($topics as $topic => $title)
{
	// TODO: Add a name anchor here, and a list of topics at the top

	if (is_array ($title)) {
		$opts = $title;
		if (array_key_exists ('title', $opts)) {
			$title = $opts['title'];
		} else {
			$title = strtr (Inflector::humanize($topic), $replacements);
		}
	} else {
		$opts = array();
		if (is_numeric ($topic)) {
			$topic = $title;
			$title = strtr (Inflector::humanize($topic), $replacements);
		}
	}
	$title = __($title, true);

	if (array_key_exists ('image', $opts)) {
		$title = $this->ZuluruHtml->icon($opts['image']) . ' ' . $title;
	}

	if (!isset ($compact) || $compact === false) {
		echo $this->Html->tag ('hr');
	}

	echo $this->Html->tag ('h3', $title);
	echo $this->Html->tag ('div', $this->element("help/$section/$topic"), array('class' => 'help_block'));
}
?>