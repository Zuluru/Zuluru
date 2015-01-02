<?php
$this->Html->addCrumb (__('People', true));
$this->Html->addCrumb ($person['Person']['full_name']);
$this->Html->addCrumb (__('Team History', true));
?>

<div class="teams index">
<h2><?php echo __('Team History', true) . ': ' . $person['Person']['full_name'];?></h2>

<?php
$years = array();
foreach ($teams as $team) {
	if (substr($team['Division']['open'], 0, 4) != '0000') {
		$years[] = date ('Y', strtotime ($team['Division']['open']));
		$seasons[] = $team['Division']['League']['season'];
	}
}
echo $this->element('selector', array('title' => 'Year', 'options' => array_unique($years)));

$seasons = array_unique(Set::extract('/Division/League/season', $teams));
echo $this->element('selector', array('title' => 'Season', 'options' => array_intersect(array_keys(Configure::read('options.season')), $seasons)));

$days = Set::extract('/Division/Day[id!=]', $teams);
$days = Set::combine($days, '{n}.Day.id', '{n}.Day.name');
ksort($days);
echo $this->element('selector', array('title' => 'Day', 'options' => $days));

$roles = array_unique(Set::extract('/TeamsPerson/role', $teams));
echo $this->element('selector', array('title' => 'Role', 'options' => array_intersect(array_keys(Configure::read('options.roster_role')), $roles)));
?>
<table class="list">
<?php
$last_year = null;
foreach ($teams as $team):
	if (substr($team['Division']['open'], 0, 4) != '0000') {
		$year = $year_text = date ('Y', strtotime ($team['Division']['open']));
	} else {
		$year = '0000';
		$year_text = __('N/A', true);
	}
	if ($last_year != $year):
		$last_year = $year;
		$seasons = array_unique(Set::extract("/Division[open>=$year-01-01][open<=$year-12-31]/League/season", $teams));
		$days = array_unique(Set::extract("/Division[open>=$year-01-01][open<=$year-12-31]/Day/name", $teams));
		$roles = array_unique(Set::extract("/Division[open>=$year-01-01][open<=$year-12-31]/../TeamsPerson/role", $teams));
?>
<tr class="<?php echo $this->element('selector_classes', array('title' => 'Year', 'options' => $year)); ?> <?php echo $this->element('selector_classes', array('title' => 'Season', 'options' => $seasons)); ?> <?php echo $this->element('selector_classes', array('title' => 'Day', 'options' => $days)); ?> <?php echo $this->element('selector_classes', array('title' => 'Role', 'options' => $roles)); ?>">
	<th colspan="3"><?php echo $year_text; ?></th>
</tr>
<?php endif; ?>
<tr class="<?php echo $this->element('selector_classes', array('title' => 'Year', 'options' => $year)); ?> <?php echo $this->element('selector_classes', array('title' => 'Season', 'options' => $team['Division']['League']['season'])); ?> <?php echo $this->element('selector_classes', array('title' => 'Day', 'options' => array_unique(Set::extract('/Division/Day/name', $team)))); ?> <?php echo $this->element('selector_classes', array('title' => 'Role', 'options' => $team['TeamsPerson']['role'])); ?>">
	<td><?php echo $this->element('teams/block', compact('team')); ?></td>
	<td><?php echo $team['TeamsPerson']['role']; ?></td>
	<td><?php echo $this->element('divisions/block', array('division' => $team['Division'], 'field' => 'full_league_name')); ?></td>
<?php endforeach; ?>
</tr>

</table>
</div>
