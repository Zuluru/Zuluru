<?php
$this->Html->addCrumb (__('Leagues', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="leagues index">
<h2><?php __('Leagues');?></h2>
<table cellpadding="0" cellspacing="0">
<tr>
	<th><?php echo __('Name');?></th>
	<th class="actions"><?php __('Actions');?></th>
</tr>
<?php
$i = 0;
foreach ($leagues as $league):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $this->Html->link($league['League']['long_name'], array('action' => 'view', 'league' => $league['League']['id'])); ?>
		</td>
		<td class="actions">
			<?php
			echo $this->Html->link(__('Schedule', true), array('action' => 'schedule', 'league' => $league['League']['id']));
			echo $this->Html->link(__('Standings', true), array('action' => 'standings', 'league' => $league['League']['id']));
			if ($is_admin) {
				echo $this->Html->link(__('Allstars', true), array('action' => 'allstars', 'league' => $league['League']['id']));
				echo $this->Html->link(__('Edit', true), array('action' => 'edit', 'league' => $league['League']['id']));
				echo $this->Html->link(__('Delete', true), array('action' => 'delete', 'league' => $league['League']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $league['League']['id']));
			}
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<div class="actions">
	<ul>
<?php
foreach ($years as $year) {
	echo $this->Html->tag('li', $this->Html->link($year[0]['year'], array('year' => $year[0]['year'])));
}
?>

	</ul>
</div>
