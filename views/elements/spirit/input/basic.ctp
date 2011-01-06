<?php
$prefix = "SpiritEntry.$team_id";
if (array_key_exists ($team_id, $game['SpiritEntry'])) {
	echo $this->Form->hidden ("$prefix.id", array ('value' => $game['SpiritEntry'][$team_id]['id']));
}
echo $this->element('formbuilder/input', array('prefix' => $prefix, 'questions' => $spirit_obj->questions));
?>
