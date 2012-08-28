<?php
// Output a block listing teams that will be excluded from scheduling.
// This is used in all of the views that the SchedulesController may render.
?>
<?php if (isset ($this->data) && array_key_exists ('ExcludeTeams', $this->data)) : ?>
<p>You will be excluding the following teams from the schedule:
<ul>
<?php
foreach ($this->data['ExcludeTeams'] as $team_id => $one) {
	$team = array_pop (Set::extract ("/Team[id=$team_id]/name", $division));
	echo $this->Html->tag('li', $team);
}
?>
</ul></p>
<?php endif; ?>
