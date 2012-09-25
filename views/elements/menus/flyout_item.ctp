<?php
if (array_key_exists ('opts', $item)) {
	$opts = $item['opts'];
} else {
	$opts = array();
}

$name = __($item['name'], true);
$short_name = $text->truncate ($name, 18);
if ($short_name != $name) {
	$opts['title'] = $name;
}

if (array_key_exists ('url', $item)) {
	$url = $this->Html->url($item['url']);
	$content = $this->Html->link($short_name, $item['url'], $opts);
} else {
	$url = false;
	// TODOCSS: fix formatting so we don't need to have a # link like this
	$content = $this->Html->link($short_name, '#', $opts);
}

$classes = array();
if ($this->here == $url)
	$classes[] = 'selected';

if (array_key_exists ('items', $item) && !empty ($item['items'])) {
	$subs = '';
	foreach ($item['items'] as $sub_item)
	{
		$subs .= $this->element('menus/flyout_item', array('item' => $sub_item));
	}
	$content .= $this->Html->tag ('ul', $subs);
	$classes[] = 'sub';
}

$options = array();
if (!empty ($classes))
	$options['class'] = implode (' ', $classes);

echo $this->Html->tag ('li', $content, $options);
?>
