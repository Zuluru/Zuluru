<div id="document_comment_div" style="display: none;" title="<?php __('Document comment'); ?>">
<p><?php __('If you want to add a comment to the player, do so here.'); ?></p>
<br /><?php
echo $this->Form->input('comment', array(
		'label' => false,
		'size' => 70,
));
?>
</div>

<?php
$this->ZuluruHtml->script ('documents', array('inline' => false));
?>
