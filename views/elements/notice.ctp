<?php
if (!$is_logged_in) {
	return;
}
$notice = $this->requestAction ('notices/next');
if (!empty($notice)):
?>
<div id="SystemNotice">
<?php
	echo $this->ZuluruHtml->iconLink('close_24.png', '#', array('id' => 'Close', 'style' => 'float:right; margin-left:10px;'));
	echo $this->Html->tag('span', $this->Html->link(__('Remind me later', true), '#', array('id' => 'Remind', 'style' => 'float:right;')), array('class' => 'actions'));

	// Use system configuration and current user record to do replacements in notice text
	$text = $notice['Notice']['notice'];
	while (preg_match('#(.*)<%icon (.*?) %>(.*)#', $text, $matches)) {
		$text = $matches[1] . $this->ZuluruHtml->icon($matches[2]) . $matches[3];
	}

	while (preg_match('#(.*)<%link (.*?) (.*?) (.*?) %>(.*)#', $text, $matches)) {
		$text = $matches[1] . $this->Html->link($matches[4], array('controller' => $matches[2], 'action' => $matches[3])) . $matches[5];
	}

	while (preg_match('#(.*)<%setting (.*?) %>(.*)#', $text, $matches)) {
		$text = $matches[1] . Configure::read($matches[2]) . $matches[3];
	}

	echo $text;
?></div>
<?php
	// Make the remind and close links in the Notice div do their jobs
	$url = $this->Html->url(array('controller' => 'notices', 'action' => 'viewed', $notice['Notice']['id']));
	$this->Js->buffer('jQuery("#Close").click(function() { notice_click(false); return false; });');
	$this->Js->buffer('jQuery("#Remind").click(function() { notice_click(true); return false; });');
	echo $this->Html->scriptBlock("
function notice_click(remind) {
	jQuery('#SystemNotice').hide('slow');
	var url = '$url';
	if (remind) { url = url + '/1'; }
	jQuery.ajax({
		dataType:'html',
		url:url
	});
}
	");
endif;
?>
