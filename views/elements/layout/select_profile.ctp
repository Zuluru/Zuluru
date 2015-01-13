<?php
$opts = array();
if (!empty($is_logged_in)):
	// Current user comes first...
	$opts[$this->UserCache->currentId()] = $this->UserCache->read('Person.full_name');
	// ...then any relatives...
	$relatives = $this->UserCache->read('Relatives');
	foreach($relatives as $relative) {
		$opts[$relative['Relative']['id']] = $relative['Relative']['full_name'];
	}
	// ...then the real user. No harm if they're already in the list; this really just adds admins at the end, if applicable.
	$opts[$this->UserCache->realId()] = $this->UserCache->read('Person.full_name', $this->UserCache->realId());
	if (count($opts) > 1):
?>
<span class="profile-trigger"><?php
echo $this->Html->link(reset($opts) . $this->ZuluruHtml->icon('dropdown.png'), array('controller' => 'people', 'action' => 'act_as'), array('escape' => false));
unset($opts[key($opts)]);
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
endif;
?>
