<?php
$this->Html->addCrumb (__('Divisions', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('View', true));
?>

<?php
// Perhaps remove manager status, if we're looking at a different affiliate
if ($is_manager && !in_array($division['League']['affiliate_id'], $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
	$is_manager = false;
}
?>

<div class="divisions view">
<h2><?php echo $division['Division']['name'];?></h2>
	<dl><?php $i = 1; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('League'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			echo $this->element('leagues/block', array('league' => $division['League']));
			echo $this->ZuluruHtml->iconLink('view_24.png', array('controller' => 'leagues', 'action' => 'view', 'league' => $division['League']['id']), array('id' => 'LeagueDetailsIcon'));
			$this->Js->get('#LeagueDetailsIcon')->event('click', 'jQuery("#LeagueDetails").toggle();');
			?>

		</dd>
		<fieldset id="LeagueDetails" style="display:none;">
		<legend><?php __('League Details'); ?></legend>
		<dl><?php $j = 1; ?>
			<dt<?php if ($j % 2 == 0) echo $class;?>><?php __('Season'); ?></dt>
			<dd<?php if ($j++ % 2 == 0) echo $class;?>>
				<?php __($division['League']['season']); ?>

			</dd>
			<?php if ($is_admin || $is_manager || $is_coordinator): ?>
				<?php if (League::hasSpirit($division)): ?>
				<dt<?php if ($j % 2 == 0) echo $class;?>><?php __('Spirit Questionnaire'); ?></dt>
				<dd<?php if ($j++ % 2 == 0) echo $class;?>>
					<?php __(Configure::read("options.spirit_questions.{$division['League']['sotg_questions']}")); ?>

				</dd>
				<dt<?php if ($j % 2 == 0) echo $class;?>><?php __('Spirit Numeric Entry'); ?></dt>
				<dd<?php if ($j++ % 2 == 0) echo $class;?>>
					<?php __($division['League']['numeric_sotg'] ? 'Yes' : 'No'); ?>

				</dd>
				<dt<?php if ($j % 2 == 0) echo $class;?>><?php __('Spirit Display'); ?></dt>
				<dd<?php if ($j++ % 2 == 0) echo $class;?>>
					<?php __(Inflector::Humanize ($division['League']['display_sotg'])); ?>

				</dd>
				<?php endif; ?>
				<dt<?php if ($j % 2 == 0) echo $class;?>><?php __('Expected Max Score'); ?></dt>
				<dd<?php if ($j++ % 2 == 0) echo $class;?>>
					<?php echo $division['League']['expected_max_score']; ?>

				</dd>
			<?php endif; ?>
		</dl>
		</fieldset>
		<?php
		echo $this->element('divisions/details', array_merge(array(
				'division' => $division['Division'],
				'people' => $division['Person'],
			), compact('is_manager', 'i', 'class')));
		?>
	</dl>
</div>
<div class="actions"><?php echo $this->element('divisions/actions', array(
	'league' => $division['League'],
	'division' => $division['Division'],
	'format' => 'list',
)); ?></div>
<?php
echo $this->element('divisions/teams', array_merge(array(
		'league' => $division['League'],
		'division' => $division['Division'],
		'teams' => $division['Team'],
	), compact('is_manager')));
echo $this->element('divisions/register', array('events' => $division['Event']));
?>
