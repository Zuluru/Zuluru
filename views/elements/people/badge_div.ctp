<div id="badge_comment_div" style="display: none;" title="<?php __('Badge comment'); ?>">
<p><?php echo $message; ?></p>
<br /><?php
echo $this->Form->input('comment', array(
		'label' => false,
		'size' => 50,
));
?>
</div>

<?php
$this->ZuluruHtml->script ('badges', array('inline' => false));
?>
