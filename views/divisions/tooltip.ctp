<h2><?php echo $division['Division']['full_league_name']; ?></h2>
<dl>
<?php if ($is_logged_in && !empty ($division['Person'])):
	$links = array();
	foreach ($division['Person'] as $coordinator) {
		$links[] = $this->Html->link($coordinator['full_name'], array('controller' => 'people', 'action' => 'view', 'person' => $coordinator['id']));
	}
?>
<?php if (!empty ($division['Day'])): ?>
	<dt><?php __(count ($division['Day']) == 1 ? 'Day' : 'Days'); ?></dt>
	<dd>
		<?php
		$days = array();
		foreach ($division['Day'] as $day) {
			$days[] = __($day['name'], true);
		}
		echo implode (', ', $days);
		?>

	</dd>
<?php endif; ?>
	<dt><?php __('Coordinators'); ?></dt>
	<dd><?php echo implode(', ', $links); ?></dd>
<?php endif; ?>
	<dt><?php __('Teams'); ?></dt>
	<dd><?php echo count($division['Team']); ?></dd>
</dl>

<p><?php
$division_count = $this->requestAction(array('controller' => 'leagues', 'action' => 'division_count'),
		array('named' => array('league' => $division['Division']['league_id'])));
if ($division_count == 1) {
	echo $this->Html->link(__('Details', true), array('controller' => 'leagues', 'action' => 'view', 'league' => $division['Division']['league_id']));
} else {
	echo $this->Html->link(__('Details', true), array('controller' => 'divisions', 'action' => 'view', 'division' => $division['Division']['id']));
}
echo ' / ' .
	$this->Html->link(__('Schedule', true), array('controller' => 'divisions', 'action' => 'schedule', 'division' => $division['Division']['id'])) .
	' / ' .
	$this->Html->link(__('Standings', true), array('controller' => 'divisions', 'action' => 'standings', 'division' => $division['Division']['id']));
?></p>
