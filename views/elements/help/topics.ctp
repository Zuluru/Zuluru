<?php
foreach ($topics as $topic => $title)
{
	// TODO: Add a name anchor here, and a list of topics at the top
	// TODO: Handle one more level of nesting in topics
	echo $this->Html->tag ('h3', __($title, true));
	echo $this->element("help/$section/$topic");
}
?>