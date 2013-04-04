<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb (__('Statistics', true));
?>

<div class="teams statistics">
<h2><?php __('Team Statistics');?></h2>

<h3><?php __('Teams by Division'); ?></h3>
<table class="list">
	<thead>
		<tr>
			<th><?php __('Season');?></th>
			<th><?php __('League');?></th>
			<th><?php __('Division'); ?></th>
			<th><?php __('Teams'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$total = 0;
$league = $season = $affiliate_id = null;
foreach ($counts as $division):
	if (count($affiliates) > 1 && $division['League']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $division['League']['affiliate_id'];
		if ($total):
?>
		<tr>
			<td colspan="3"><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
<?php
		endif;

		$total = 0;
		$league = $season = null;
?>
		<tr>
			<th colspan="4">
				<h4 class="affiliate"><?php echo $division['League']['Affiliate']['name']; ?></h4>
			</th>
		</tr>
<?php
	endif;

	$total += $division[0]['count'];
?>
		<tr>
			<td><?php
			if ($division['League']['season'] != $season) {
				__($division['League']['season']);
				$season = $division['League']['season'];
			}
			?></td>
			<td><?php
			if ($division['League']['id'] != $league) {
				echo $this->Html->link($division['League']['name'], array('action' => 'edit', 'league' => $division['League']['id'], 'return' => true));
				$league = $division['League']['id'];
			}
			?>
			</td>
			<td><?php echo $this->element('divisions/block', array('division' => $division['Division'])); ?></td>
			<td><?php echo $division[0]['count']; ?></td>
		</tr>
<?php endforeach; ?>

		<tr>
			<td colspan="3"><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
	</tbody>
</table>

<h3><?php __('Teams with too few players'); ?></h3>
<table class="list">
	<thead>
		<tr>
			<th><?php __('Team'); ?></th>
			<th><?php __('Division'); ?></th>
			<th><?php __('Players'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$affiliate_id = null;
foreach ($shorts as $team):
	if (count($affiliates) > 1 && $team['League']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $team['League']['affiliate_id'];
?>
		<tr>
			<th colspan="3">
				<h4 class="affiliate"><?php echo $team['League']['Affiliate']['name']; ?></h4>
			</th>
		</tr>
<?php endif; ?>
		<tr>
			<td><?php echo $this->element('teams/block', compact('team')); ?></td>
			<td><?php echo $this->element('divisions/block', array('division' => $team['Division'], 'field' => 'full_league_name')); ?></td>
			<td><?php
			echo $team[0]['count'];
			if ($team[0]['subs'] > 0) {
				echo " ({$team[0]['subs']} subs)";
			}
			?></td>
		</tr>
<?php endforeach; ?>

	</tbody>
</table>

<h3><?php __('Top-rated Teams'); ?></h3>
<table class="list">
	<thead>
		<tr>
			<th><?php __('Team'); ?></th>
			<th><?php __('Division'); ?></th>
			<th><?php __('Rating'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$affiliate_id = null;
foreach ($top_rating as $team):
	if (count($affiliates) > 1 && $team['League']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $team['League']['affiliate_id'];
?>
		<tr>
			<th colspan="3">
				<h4 class="affiliate"><?php echo $team['League']['Affiliate']['name']; ?></h4>
			</th>
		</tr>
<?php endif; ?>
		<tr>
			<td><?php echo $this->element('teams/block', compact('team')); ?></td>
			<td><?php echo $this->element('divisions/block', array('division' => $team['Division'], 'field' => 'full_league_name')); ?></td>
			<td><?php echo $team['Team']['rating']; ?></td>
		</tr>
<?php endforeach; ?>

	</tbody>
</table>

<h3><?php __('Lowest-rated Teams'); ?></h3>
<table class="list">
	<thead>
		<tr>
			<th><?php __('Team'); ?></th>
			<th><?php __('Division'); ?></th>
			<th><?php __('Rating'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$affiliate_id = null;
foreach ($lowest_rating as $team):
	if (count($affiliates) > 1 && $team['League']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $team['League']['affiliate_id'];
?>
		<tr>
			<th colspan="3">
				<h4 class="affiliate"><?php echo $team['League']['Affiliate']['name']; ?></h4>
			</th>
		</tr>
<?php endif; ?>
		<tr>
			<td><?php echo $this->element('teams/block', compact('team')); ?></td>
			<td><?php echo $this->element('divisions/block', array('division' => $team['Division'], 'field' => 'full_league_name')); ?></td>
			<td><?php echo $team['Team']['rating']; ?></td>
		</tr>
<?php endforeach; ?>

	</tbody>
</table>

<h3><?php __('Top Defaulting Teams'); ?></h3>
<table class="list">
	<thead>
		<tr>
			<th><?php __('Team'); ?></th>
			<th><?php __('Division'); ?></th>
			<th><?php __('Defaults'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$affiliate_id = null;
foreach ($defaulting as $team):
	$team['Team'] = $team[0];
	if (count($affiliates) > 1 && $team['League']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $team['League']['affiliate_id'];
?>
		<tr>
			<th colspan="3">
				<h4 class="affiliate"><?php echo $team['League']['Affiliate']['name']; ?></h4>
			</th>
		</tr>
<?php endif; ?>
		<tr>
			<td><?php echo $this->element('teams/block', compact('team')); ?></td>
			<td><?php echo $this->element('divisions/block', array('division' => $team['Division'], 'field' => 'full_league_name')); ?></td>
			<td><?php echo $team['Team']['count']; ?></td>
		</tr>
<?php endforeach; ?>

	</tbody>
</table>

<h3><?php __('Top Non-score-submitting Teams'); ?></h3>
<table class="list">
	<thead>
		<tr>
			<th><?php __('Team'); ?></th>
			<th><?php __('Division'); ?></th>
			<th><?php __('Games'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$affiliate_id = null;
foreach ($no_scores as $team):
	$team['Team'] = $team[0];
	if (count($affiliates) > 1 && $team['League']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $team['League']['affiliate_id'];
?>
		<tr>
			<th colspan="3">
				<h4 class="affiliate"><?php echo $team['League']['Affiliate']['name']; ?></h4>
			</th>
		</tr>
<?php endif; ?>
		<tr>
			<td><?php echo $this->element('teams/block', compact('team')); ?></td>
			<td><?php echo $this->element('divisions/block', array('division' => $team['Division'], 'field' => 'full_league_name')); ?></td>
			<td><?php echo $team[0]['count']; ?></td>
		</tr>
<?php endforeach; ?>

	</tbody>
</table>

<?php if (Configure::read('feature.spirit')): ?>

<h3><?php __('Top Spirited Teams'); ?></h3>
<table class="list">
	<thead>
		<tr>
			<th><?php __('Team'); ?></th>
			<th><?php __('Division'); ?></th>
			<th><?php __('Average Spirit'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$affiliate_id = null;
foreach ($top_spirit as $team):
	if (count($affiliates) > 1 && $team['League']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $team['League']['affiliate_id'];
?>
		<tr>
			<th colspan="3">
				<h4 class="affiliate"><?php echo $team['League']['Affiliate']['name']; ?></h4>
			</th>
		</tr>
<?php endif; ?>
		<tr>
			<td><?php echo $this->element('teams/block', compact('team')); ?></td>
			<td><?php echo $this->element('divisions/block', array('division' => $team['Division'], 'field' => 'full_league_name')); ?></td>
			<td><?php echo $team[0]['avgspirit']; ?></td>
		</tr>
<?php endforeach; ?>

	</tbody>
</table>

<h3><?php __('Lowest Spirited Teams'); ?></h3>
<table class="list">
	<thead>
		<tr>
			<th><?php __('Team'); ?></th>
			<th><?php __('Division'); ?></th>
			<th><?php __('Average Spirit'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$affiliate_id = null;
foreach ($lowest_spirit as $team):
	if (count($affiliates) > 1 && $team['League']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $team['League']['affiliate_id'];
?>
		<tr>
			<th colspan="3">
				<h4 class="affiliate"><?php echo $team['League']['Affiliate']['name']; ?></h4>
			</th>
		</tr>
<?php endif; ?>
		<tr>
			<td><?php echo $this->element('teams/block', compact('team')); ?></td>
			<td><?php echo $this->element('divisions/block', array('division' => $team['Division'], 'field' => 'full_league_name')); ?></td>
			<td><?php echo $team[0]['avgspirit']; ?></td>
		</tr>
<?php endforeach; ?>

	</tbody>
</table>

<?php endif; ?>

</div>
<div class="actions">
	<p><?php __('Other years'); ?>:</p>
	<ul>
<?php
foreach ($years as $y) {
	echo $this->Html->tag('li', $this->Html->link($y[0]['year'], array('year' => $y[0]['year'])));
}
?>

	</ul>
</div>

<?php if (isset($leagues)): ?>
<div class="actions" style="clear:both;">
	<p><?php __('Other leagues'); ?>:</p>
	<ul>
<?php
foreach ($leagues as $league_id => $league) {
	echo $this->Html->tag('li', $this->Html->link($league, array('year' => $year, 'league' => $league_id)));
}
?>

	</ul>
</div>
<?php endif; ?>
