<?php
$this->Html->addCrumb (__('Leagues', true));
$this->Html->addCrumb ($league['League']['long_name']);
$this->Html->addCrumb (__('View', true));
?>

<div class="leagues view">
<h2><?php  echo __('View League', true) . ': ' . $league['League']['long_name'];?></h2>
	<dl><?php $i = 1; $class = ' class="altrow"';?>
		<?php if (!empty($league['Person'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Coordinators'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		$coordinators = array();
		foreach ($league['Person'] as $person) {
			$coordinator = $this->element('people/block', compact('person'));
			if ($is_admin) {
				$coordinator .= '&nbsp;' .
					$this->Html->tag('span',
						$this->Html->link('Remove', array('action' => 'remove_coordinator', 'league' => $league['League']['id'], 'person' => $person['id'])),
						array('class' => 'actions'));
			}
			$coordinators[] = $coordinator;
		}
		echo implode ('<br />', $coordinators);
		?></dd>
		<?php endif; ?>
		<?php if (!empty ($league['League']['coord_list'])) : ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Coordinator Email List'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $this->Html->link ($league['League']['coord_list'], "mailto:{$league['League']['coord_list']}"); ?>

			</dd>
		<?php endif; ?>
		<?php if (!empty ($league['League']['capt_list'])) : ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Captain Email List'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $this->Html->link ($league['League']['capt_list'], "mailto:{$league['League']['capt_list']}"); ?>

			</dd>
		<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Status'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($league['League']['is_open'] ? 'Open' : 'Closed'); ?>

		</dd>
		<?php if ($league['League']['open'] != '0000-00-00'): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('First Game'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $this->ZuluruTime->date($league['League']['open']); ?>

			</dd>
		<?php endif; ?>
		<?php if ($league['League']['close'] != '0000-00-00'): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Last Game'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $this->ZuluruTime->date($league['League']['close']); ?>

			</dd>
		<?php endif; ?>
		<?php if ($league['League']['roster_deadline'] != '0000-00-00'): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Roster Deadline'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $this->ZuluruTime->date($league['League']['roster_deadline']); ?>

			</dd>
		<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __(count ($league['Day']) == 1 ? 'Day' : 'Days'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			$days = array();
			foreach ($league['Day'] as $day) {
				$days[] = __($day['name'], true);
			}
			echo implode (', ', $days);
			?>

		</dd>
		<?php if (!empty ($league['League']['tier'])): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Tier'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $league['League']['tier']; ?>

			</dd>
		<?php endif; ?>
		<?php if (!empty ($league['League']['ratio'])): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Gender Ratio'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php __(Inflector::Humanize ($league['League']['ratio'])); ?>

			</dd>
		<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Schedule Type'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __(Inflector::Humanize ($league['League']['schedule_type'])); ?>

		</dd>
		<?php
		$fields = $league_obj->schedulingFields($is_admin, $is_coordinator);
		foreach ($fields as $field => $options):
		?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __($options['label']); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $league['League'][$field]; ?>

			</dd>
		<?php endforeach; ?>
		<?php if ($is_admin || $is_coordinator): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Exclude Teams'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php __($league['League']['exclude_teams'] ? 'Yes' : 'No'); ?>

			</dd>
		<?php endif; ?>
		<?php if ($is_admin || $is_coordinator): ?>
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
			<?php if ($league['League']['email_after'] != 0): ?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Scoring reminder delay'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php echo $league['League']['email_after'] . ' ' . __('hours', true); ?>

				</dd>
			<?php endif; ?>
			<?php if ($league['League']['finalize_after'] != 0): ?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Game finalization delay'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php echo $league['League']['finalize_after'] . ' ' . __('hours', true); ?>

				</dd>
			<?php endif; ?>
		<?php endif; ?>
		<?php if (Configure::read('scoring.allstars')): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('All-star nominations'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php __(Inflector::Humanize ($league['League']['allstars'])); ?>

			</dd>
		<?php endif; ?>
	</dl>
</div>
<div class="actions">
	<ul>
		<?php
		echo $this->Html->tag ('li', $this->Html->link(__('Schedule', true), array('action' => 'schedule', 'league' => $league['League']['id'])));
		echo $this->Html->tag ('li', $this->Html->link(__('Standings', true), array('action' => 'standings', 'league' => $league['League']['id'])));
		if ($is_admin || $is_coordinator) {
			echo $this->Html->tag ('li', $this->Html->link(__('Edit League', true), array('action' => 'edit', 'league' => $league['League']['id'])));
			echo $this->Html->tag ('li', $this->Html->link(__('Captain Emails', true), array('action' => 'emails', 'league' => $league['League']['id'])));
			echo $this->Html->tag ('li', $this->Html->link(__('Approve scores', true), array('action' => 'approve_scores', 'league' => $league['League']['id'])));
			echo $this->Html->tag ('li', $this->Html->link(__('Add games', true), array('controller' => 'schedules', 'action' => 'add', 'league' => $league['League']['id'])));
			// TODO: More links to reports, etc.
		}
		if ($is_admin) {
			echo $this->Html->tag ('li', $this->Html->link(__('Add Coordinator', true), array('action' => 'add_coordinator', 'league' => $league['League']['id'])));
			echo $this->Html->tag ('li', $this->Html->link(__('Spirit', true), array('action' => 'spirit', 'league' => $league['League']['id'])));
			echo $this->Html->tag ('li', $this->Html->link(__('Delete League', true), array('action' => 'delete', 'league' => $league['League']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $league['League']['id'])));
		}
		?>
	</ul>
</div>

<div class="related">
	<?php if (!empty($league['Team'])):?>
	<table>
	<?php
	echo $this->element("league/view/{$league_obj->render_element}/heading",
			compact ('is_admin', 'is_coordinator'));
	$seed = $i = 0;
	foreach ($league['Team'] as $team) {
		$classes = array();
		if (floor ($seed++ / 8) % 2 == 1) {
			if (++$i % 2 == 1) {
				$classes[] = 'tier_alt_highlight';
			} else {
				$classes[] = 'tier_highlight';
			}
		} else {
			if (++$i % 2 == 1) {
				$classes[] = 'altrow';
			}
		}
		Team::consolidateRoster ($team);
		echo $this->element("league/view/{$league_obj->render_element}/team",
				compact ('is_admin', 'is_coordinator', 'league', 'team', 'seed', 'classes'));
	}
	?>
	</table>
	<?php endif; ?>
</div>
