<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb (sprintf(__('Starting with %', true), $letter));
?>

<div class="teams index">
<h2><?php __('List Teams');?></h2>
<p><?php
__('Locate by letter: ');
$links = array();
foreach ($letters as $l) {
	$l = up($l[0]['letter']);
	if ($l != $letter) {
		$links[] = $this->Html->link($l, array('action' => 'letter', 'letter' => $l));
	} else {
		$links[] = $letter;
	}
}
echo implode ('&nbsp;&nbsp;', $links);
?></p>
<p>
<table cellpadding="0" cellspacing="0">
<tr>
	<th><?php __('Name');?></th>
	<th><?php __('League');?></th>
	<th class="actions"><?php __('Actions');?></th>
</tr>
<?php
$i = 0;
foreach ($teams as $team):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $team['Team']['name']; ?>
		</td>
		<td>
			<?php echo $this->Html->link($team['League']['long_name'], array('controller' => 'leagues', 'action' => 'view', 'league' => $team['League']['id'])); ?>
		</td>
		<td class="actions">
			<?php
			echo $this->Html->link(__('View', true), array('action' => 'view', 'team' => $team['Team']['id']));
			echo $this->Html->link(__('Schedule', true), array('action' => 'schedule', 'team' => $team['Team']['id']));
			echo $this->Html->link(__('Standings', true), array('controller' => 'leagues', 'action' => 'standings', 'league' => $team['League']['id'], 'team' => $team['Team']['id']));
			if ($is_admin) {
				echo $this->Html->link(__('Edit', true), array('action' => 'edit', 'team' => $team['Team']['id']));
				echo $this->Html->link(__('Delete', true), array('action' => 'delete', 'team' => $team['Team']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $team['Team']['id']));
			}
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
</div>
