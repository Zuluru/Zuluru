<?php
$opts = $this->UserCache->allActAs();
if (!empty($opts)):
?>
<span class="profile-trigger"><?php
echo $this->Html->link($this->UserCache->read('Person.full_name') . $this->ZuluruHtml->icon('dropdown.png'), array('controller' => 'people', 'action' => 'act_as'), array('escape' => false));
?></span>
<div id="profile_options" style="display: none;">
<div><?php __('Switch to:'); ?></div>
<?php
foreach ($opts as $id => $name) {
	echo $this->Html->tag('div', $this->Html->link($name, array('controller' => 'people', 'action' => 'act_as', 'person' => $id, 'return' => true)));
}
?>
</div>
<?php
endif;
?>
