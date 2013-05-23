<?php
$this->Html->addCrumb (__('Leagues', true));
$this->Html->addCrumb (__('Summary', true));
?>

<div class="leagues summary">
<h2><?php __('League Summary');?></h2>
<table class="list">
<tr>
	<th><?php __('Season');?></th>
	<th><?php __('Name');?></th>
	<?php if (Configure::read('feature.spirit')): ?>
	<th><?php __('Spirit Display');?></th>
	<th><?php __('Spirit Questionnaire');?></th>
	<th><?php __('Numeric Spirit?');?></th>
	<?php endif; ?>
	<th><?php __('Max Score');?></th>
	<th><?php __('Schedule Attempts');?></th>
	<th><?php __('Tie Breaker');?></th>
</tr>
<?php
$i = 0;
$league = $season = $affiliate_id = null;
foreach ($divisions as $division):
	if ($division['League']['id'] == $league) {
		continue;
	}
	if (count($affiliates) > 1 && $division['League']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $division['League']['affiliate_id'];
?>
<tr>
	<th colspan="<?php echo 4 + (Configure::read('feature.spirit') * 3); ?>">
		<h3 class="affiliate"><?php echo $division['League']['Affiliate']['name']; ?></h3>
	</th>
</tr>
<?php
	endif;

	$league = $division['League']['id'];
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td><?php
		if ($division['League']['season'] != $season) {
			__($division['League']['season']);
			$season = $division['League']['season'];
		}
		?></td>
		<td><?php
		echo $this->Html->link($division['League']['name'], array('action' => 'edit', 'league' => $division['League']['id'], 'return' => true));
		?></td>
		<?php if (Configure::read('feature.spirit')): ?>
		<td><?php __(Inflector::humanize($division['League']['display_sotg'])); ?></td>
		<td><?php echo $division['League']['sotg_questions']; ?></td>
		<td><?php __($division['League']['numeric_sotg'] ? 'Yes' : 'No'); ?></td>
		<?php endif; ?>
		<td><?php echo $division['League']['expected_max_score']; ?></td>
		<td><?php echo $division['League']['schedule_attempts']; ?></td>
		<td><?php echo Configure::read("options.tie_breaker_spirit.{$division['League']['tie_breaker']}"); ?></td>
	</tr>
<?php endforeach; ?>
</table>

<h2><?php __('Division Summary');?></h2>
<table class="list">
<tr>
	<th><?php __('Season');?></th>
	<th><?php __('League');?></th>
	<th><?php __('Division');?></th>
	<th><?php __('Schedule Type');?></th>
	<th><?php __('Games Before Repeat');?></th>
	<th><?php __('First Game');?></th>
	<th><?php __('Last Game');?></th>
	<th><?php __('Roster Deadline');?></th>
	<th><?php __('Allstars');?></th>
	<th><?php __('Rating Calculator');?></th>
	<th><?php __('Remind After');?></th>
	<th><?php __('Finalize After');?></th>
	<th><?php __('Roster Rule');?></th>
</tr>
<?php
$i = 0;
$league = $season = $affiliate_id = null;
foreach ($divisions as $division):
	if (count($affiliates) > 1 && $division['League']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $division['League']['affiliate_id'];
?>
<tr>
	<th colspan="13">
		<h3 class="affiliate"><?php echo $division['League']['Affiliate']['name']; ?></h3>
	</th>
</tr>
<?php
	endif;

	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
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
		<td><?php echo $this->Html->link($division['Division']['name'], array('controller' => 'divisions', 'action' => 'edit', 'division' => $division['Division']['id'], 'return' => true)); ?></td>
		<td><?php __(Inflector::humanize($division['Division']['schedule_type'])); ?></td>
		<td><?php echo $division['Division']['games_before_repeat']; ?></td>
		<td><?php echo $this->ZuluruTime->date($division['Division']['open']); ?></td>
		<td><?php echo $this->ZuluruTime->date($division['Division']['close']); ?></td>
		<td><?php echo $this->ZuluruTime->date(Division::rosterDeadline($division['Division'])); ?></td>
		<td><?php
		__(Inflector::humanize($division['Division']['allstars']));
		if ($division['Division']['allstars'] != 'never') {
			__(' from ');
			__(Inflector::humanize($division['Division']['allstars_from']));
		}
		?></td>
		<td><?php __(Inflector::humanize($division['Division']['rating_calculator'])); ?></td>
		<td><?php echo $division['Division']['email_after']; ?></td>
		<td><?php echo $division['Division']['finalize_after']; ?></td>
		<td><?php echo $division['Division']['roster_rule']; ?></td>
	</tr>
<?php endforeach; ?>
</table>

</div>
