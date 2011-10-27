<?php
if(!$is_logged_in) {
	return;
}
$notice = $this->requestAction ('notices/random');
if (!empty($notice)):
?>
<div id="SystemNotice">
<?php
	echo $this->ZuluruHtml->iconLink('close_24.png', '#', array('style' => 'float:right; margin-left:10px;'));
	echo $this->Html->tag('span', $this->Html->link(__('Remind me later', true), '#', array('id' => 'Remind', 'style' => 'float:right;')), array('class' => 'actions'));
	// TODO: Use current user record to do strtr replacements in notice text
	echo $notice['Notice']['notice'];
?></div>
<?php
	// TODO: Make all links in the Notice div do an Ajax call to mark the notice as viewed, and then hide the div
	$this->Js->buffer('$("#SystemNotice a").not("#Remind").each(function() { $(this).click(function() { notice_click(false); return false; }); });');
	$this->Js->buffer('$("#Remind").click(function() { notice_click(true); return false; });');
	echo $this->Html->scriptBlock("
function notice_click(remind) {
	$('#SystemNotice').hide('slow');
	var url = '/Zuluru/notices/viewed/{$notice['Notice']['id']}';
	if (remind) { url = url + '/1'; }
	$.ajax({
		dataType:'html',
		url:url
	});
}
	");
endif;
?>