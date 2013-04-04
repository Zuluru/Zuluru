<h2><?php echo $league['League']['full_name']; ?></h2>
<dl>
	<dt><?php __('Season'); ?></dt>
	<dd><?php __($league['League']['season']); ?></dd>
<?php
if (count($league['Division']) == 1):
	if ($is_logged_in && !empty ($league['Division'][0]['Person'])):
		$links = array();
		foreach ($league['Division'][0]['Person'] as $coordinator) {
			$links[] = $this->Html->link($coordinator['full_name'], array('controller' => 'people', 'action' => 'view', 'person' => $coordinator['id']));
		}
?>
	<dt><?php __('Coordinators'); ?></dt>
	<dd><?php echo implode(', ', $links); ?></dd>
	<?php endif; ?>
	<dt><?php __('Teams'); ?></dt>
	<dd><?php echo count($league['Division'][0]['Team']); ?></dd>
<?php else: ?>
	<?php foreach ($league['Division'] as $division): ?>
	<dt><?php
	if (strlen($division['name']) > 12) {
		echo $this->Html->tag('span', $this->Text->truncate ($division['name'], 12), array('title' => $division['name']));
	} else {
		echo $division['name'];
	}
	?></dt>
	<dd><?php
	echo $this->Html->link(__('Details', true), array('controller' => 'divisions', 'action' => 'view', 'division' => $division['id'])) .
			' / ' .
			$this->Html->link(__('Schedule', true), array('controller' => 'divisions', 'action' => 'schedule', 'division' => $division['id'])) .
			' / ' .
			$this->Html->link(__('Standings', true), array('controller' => 'divisions', 'action' => 'standings', 'division' => $division['id']));
	?></dd>
	<?php endforeach; ?>
<?php endif; ?>

</dl>

<p><?php
echo $this->Html->link(__('Details', true), array('controller' => 'leagues', 'action' => 'view', 'league' => $league['League']['id']));
if (count($league['Division']) == 1) {
	echo ' / ' .
		$this->Html->link(__('Schedule', true), array('controller' => 'divisions', 'action' => 'schedule', 'division' => $league['Division'][0]['id'])) .
		' / ' .
		$this->Html->link(__('Standings', true), array('controller' => 'divisions', 'action' => 'standings', 'division' => $league['Division'][0]['id']));
}
?></p>
