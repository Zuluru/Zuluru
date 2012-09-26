<?php
$this->Html->addCrumb (__('Leagues', true));
$this->Html->addCrumb ($league['League']['full_name']);
$this->Html->addCrumb (__('View', true));
?>

<div class="leagues view">
<h2><?php echo $league['League']['full_name'];?></h2>
	<dl><?php $i = 1; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Season'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($league['League']['season']); ?>

		</dd>
		<?php if ($is_admin || $is_coordinator): ?>
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
	</dl>
</div>

<div class="related">
<h2><?php __('Divisions');?></h2>
<table class="list">
<?php foreach ($league['Division'] as $division): ?>
	<tr>
		<td>
			<?php echo $this->Html->link($division['name'], array('controller' => 'divisions', 'action' => 'view', 'division' => $division['id'])); ?>
		</td>
		<td class="actions">
			<?php
			echo $this->ZuluruHtml->iconLink('view_32.png',
				array('controller' => 'divisions', 'action' => 'view', 'division' => $division['id']),
				array('alt' => __('Details', true), 'title' => __('View Division Details', true)));
			echo $this->ZuluruHtml->iconLink('schedule_32.png',
				array('controller' => 'divisions', 'action' => 'schedule', 'division' => $division['id']),
				array('alt' => __('Schedule', true), 'title' => __('Schedule', true)));
			echo $this->ZuluruHtml->iconLink('standings_32.png',
				array('controller' => 'divisions', 'action' => 'standings', 'division' => $division['id']),
				array('alt' => __('Standings', true), 'title' => __('Standings', true)));
			if ($is_admin || in_array($division['id'], $this->Session->read('Zuluru.DivisionIDs'))) {
				echo $this->ZuluruHtml->iconLink('edit_32.png',
					array('controller' => 'divisions', 'action' => 'edit', 'division' => $division['id']),
					array('alt' => __('Edit', true), 'title' => __('Edit Division', true)));
				echo $this->ZuluruHtml->iconLink('email_32.png',
					array('controller' => 'divisions', 'action' => 'emails', 'division' => $division['id']),
					array('alt' => __('Captain Emails', true), 'title' => __('Captain Emails', true)));
				echo $this->ZuluruHtml->iconLink('score_approve_32.png',
					array('controller' => 'divisions', 'action' => 'approve_scores', 'division' => $division['id']),
					array('alt' => __('Approve scores', true), 'title' => __('Approve scores', true)));
				echo $this->ZuluruHtml->iconLink('schedule_add_32.png',
					array('controller' => 'schedules', 'action' => 'add', 'division' => $division['id']),
					array('alt' => __('Add Games', true), 'title' => __('Add Games', true)));
				if (League::hasSpirit($league)) {
					echo $this->ZuluruHtml->iconLink('spirit_32.png',
						array('controller' => 'divisions', 'action' => 'spirit', 'division' => $division['id']),
						array('alt' => __('Spirit', true), 'title' => __('See Division Spirit Report', true)));
				}
				echo $this->ZuluruHtml->iconLink('field_report_32.png',
					array('controller' => 'divisions', 'action' => 'fields', 'division' => $division['id']),
					array('alt' => sprintf(__('%s Distribution', true), Configure::read('sport.field_cap')), 'title' => sprintf(__('%s Distribution Report', true), Configure::read('sport.field_cap'))));
			}
			if ($is_admin) {
				echo $this->ZuluruHtml->iconLink('coordinator_add_32.png',
					array('controller' => 'divisions', 'action' => 'add_coordinator', 'division' => $division['id']),
					array('alt' => __('Add Coordinator', true), 'title' => __('Add Coordinator', true)));
				if ($division['allstars'] != 'never') {
					echo $this->Html->link(__('Allstars', true), array('controller' => 'divisions', 'action' => 'allstars', 'division' => $division['id']));
				}
				echo $this->ZuluruHtml->iconLink('delete_32.png',
					array('controller' => 'divisions', 'action' => 'delete', 'division' => $division['id']),
					array('alt' => __('Delete', true), 'title' => __('Delete Division', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $division['id'])));
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
		if ($is_admin) {
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('edit_32.png',
				array('action' => 'edit', 'league' => $league['League']['id']),
				array('alt' => __('Edit', true), 'title' => __('Edit League', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('division_add_32.png',
				array('controller' => 'divisions', 'action' => 'add', 'league' => $league['League']['id']),
				array('alt' => __('Add Division', true), 'title' => __('Add Division', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('delete_32.png',
				array('action' => 'delete', 'league' => $league['League']['id']),
				array('alt' => __('Delete', true), 'title' => __('Delete League', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $league['League']['id']))));
		}
		?>
	</ul>
</div>
