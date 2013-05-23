<?php
$this->Html->addCrumb (__('Leagues', true));
$this->Html->addCrumb ($league['League']['full_name']);
$this->Html->addCrumb (__('View', true));
?>

<?php
// Perhaps remove manager status, if we're looking at a different affiliate
if ($is_manager && !in_array($league['League']['affiliate_id'], $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
	$is_manager = false;
}

$collapse = (count($league['Division']) == 1);
?>
<div class="leagues view">
<h2><?php echo $league['League']['full_name'];?></h2>
	<dl><?php $i = 1; $class = ' class="altrow"';?>
		<?php if (count($affiliates) > 1): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Affiliate'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($league['Affiliate']['name'], array('controller' => 'affiliates', 'action' => 'view', 'affiliate' => $league['Affiliate']['id'])); ?>

		</dd>
		<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Season'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($league['League']['season']); ?>

		</dd>
		<?php if ($is_admin || $is_manager || $is_coordinator): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Schedule Attempts'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $league['League']['schedule_attempts']; ?>

			</dd>
		<?php if (Configure::read('feature.spirit')): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Spirit Questionnaire'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php __(Configure::read("options.spirit_questions.{$league['League']['sotg_questions']}")); ?>

			</dd>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Spirit Numeric Entry'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php __($league['League']['numeric_sotg'] ? 'Yes' : 'No'); ?>

			</dd>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Spirit Display'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php __(Inflector::Humanize ($league['League']['display_sotg'])); ?>

			</dd>
		<?php endif; ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Expected Max Score'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $league['League']['expected_max_score']; ?>

			</dd>
		<?php endif; ?>
		<?php if (Configure::read('scoring.stat_tracking')): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Stat Tracking'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php __(Inflector::Humanize ($league['League']['stat_tracking'])); ?>

			</dd>
		<?php endif; ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Tie Breaker'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo Configure::read("options.tie_breaker_spirit.{$league['League']['tie_breaker']}"); ?>

			</dd>
		<?php
		if ($collapse) {
			echo $this->element('divisions/details', array_merge(array(
					'division' => $league['Division'][0],
					'people' => $league['Division'][0]['Person'],
				), compact('is_manager', 'i', 'class')));
		}
		?>
	</dl>
</div>

<?php
if (!$collapse):
?>
<div class="related">
<h2><?php __('Divisions');?></h2>
<table class="list">
<?php foreach ($league['Division'] as $division): ?>
	<tr>
		<td>
			<?php echo $this->element('divisions/block', array('division' => $division)); ?>
		</td>
		<td class="actions"><?php echo $this->element('divisions/actions', compact('league', 'division', 'is_manager', 'collapse')); ?></td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<?php else: ?>
<div class="actions">
<?php echo $this->element('leagues/actions', array_merge(
	compact('league', 'collapse'),
	array('format' => 'list')
)); ?>
</div>
<?php endif; ?>
<?php
if ($collapse) {
	echo $this->element('divisions/teams', array_merge(array(
			'league' => $league['League'],
			'division' => $league['Division'][0],
			'teams' => $league['Division'][0]['Team'],
		), compact('is_manager')));
	echo $this->element('divisions/register', array('events' => $league['Division'][0]['Event']));
}
?>
