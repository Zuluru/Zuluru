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
			echo $this->Html->link($division['League']['full_name'], array('controller' => 'leagues', 'action' => 'view', 'league' => $division['League']['id']));
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
				<dt<?php if ($j % 2 == 0) echo $class;?>><?php __('Expected Max Score'); ?></dt>
				<dd<?php if ($j++ % 2 == 0) echo $class;?>>
					<?php echo $division['League']['expected_max_score']; ?>

				</dd>
			<?php endif; ?>
		</dl>
		</fieldset>

		<?php if (!empty($division['Person'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Coordinators'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		$coordinators = array();
		foreach ($division['Person'] as $person) {
			$coordinator = $this->element('people/block', compact('person'));
			if ($is_admin || $is_manager) {
				$coordinator .= '&nbsp;' .
					$this->Html->tag('span',
						$this->ZuluruHtml->iconLink('coordinator_delete_24.png',
							array('action' => 'remove_coordinator', 'division' => $division['Division']['id'], 'person' => $person['id']),
							array('alt' => __('Remove', true), 'title' => __('Remove', true))),
						array('class' => 'actions'));
			}
			$coordinators[] = $coordinator;
		}
		echo implode ('<br />', $coordinators);
		?></dd>
		<?php endif; ?>
		<?php if (!empty ($division['Division']['coord_list'])) : ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Coordinator Email List'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $this->Html->link ($division['Division']['coord_list'], "mailto:{$division['Division']['coord_list']}"); ?>

			</dd>
		<?php endif; ?>
		<?php if (!empty ($division['Division']['capt_list'])) : ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Captain Email List'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $this->Html->link ($division['Division']['capt_list'], "mailto:{$division['Division']['capt_list']}"); ?>

			</dd>
		<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Status'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($division['Division']['is_open'] ? 'Open' : 'Closed'); ?>

		</dd>
		<?php if ($division['Division']['open'] != '0000-00-00'): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('First Game'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $this->ZuluruTime->date($division['Division']['open']); ?>

			</dd>
		<?php endif; ?>
		<?php if ($division['Division']['close'] != '0000-00-00'): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Last Game'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $this->ZuluruTime->date($division['Division']['close']); ?>

			</dd>
		<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Roster Deadline'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->date(Division::rosterDeadline($division['Division'])); ?>

		</dd>
		<?php if (!empty ($division['Day'])): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __(count ($division['Day']) == 1 ? 'Day' : 'Days'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php
				$days = array();
				foreach ($division['Day'] as $day) {
					$days[] = __($day['name'], true);
				}
				echo implode (', ', $days);
				?>

			</dd>
		<?php endif; ?>
		<?php if (!empty ($division['Division']['ratio'])): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Gender Ratio'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php __(Inflector::Humanize ($division['Division']['ratio'])); ?>

			</dd>
		<?php endif; ?>
		<?php if ($is_admin || $is_manager): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Roster Rule'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $this->Html->tag('pre', $division['Division']['roster_rule'] . '&nbsp;'); ?>

			</dd>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Roster Method'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo Configure::read("options.roster_methods.{$division['Division']['roster_method']}"); ?>

			</dd>
		<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Schedule Type'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			__(Inflector::Humanize ($division['Division']['schedule_type']));
			echo '&nbsp;' . $this->ZuluruHtml->help(array('action' => 'divisions', 'edit', 'schedule_type', $division['Division']['schedule_type']));
			?>

		</dd>
		<?php
		$fields = $league_obj->schedulingFields($is_admin || $is_manager, $is_coordinator);
		foreach ($fields as $field => $options):
		?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __($options['label']); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php
				echo $division['Division'][$field];
				echo '&nbsp;' . $this->ZuluruHtml->help(array('action' => 'divisions', 'edit', $field));
				?>

			</dd>
		<?php endforeach; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Rating Calculator'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			__(Inflector::Humanize ($division['Division']['rating_calculator']));
			echo '&nbsp;' . $this->ZuluruHtml->help(array('action' => 'divisions', 'edit', 'rating_calculator', $division['Division']['rating_calculator']));
			?>

		</dd>
		<?php if ($is_admin || $is_manager || $is_coordinator): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Exclude Teams'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php
				__($division['Division']['exclude_teams'] ? 'Yes' : 'No');
				echo '&nbsp;' . $this->ZuluruHtml->help(array('action' => 'divisions', 'edit', 'exclude_teams'));
				?>

			</dd>
			<?php if ($division['Division']['email_after'] != 0): ?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Scoring reminder delay'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php echo $division['Division']['email_after'] . ' ' . __('hours', true); ?>

				</dd>
			<?php endif; ?>
			<?php if ($division['Division']['finalize_after'] != 0): ?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Game finalization delay'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php echo $division['Division']['finalize_after'] . ' ' . __('hours', true); ?>

				</dd>
			<?php endif; ?>
		<?php endif; ?>
		<?php if (Configure::read('scoring.allstars')): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('All-star nominations'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php __(Inflector::Humanize ($division['Division']['allstars'])); ?>

			</dd>
		<?php endif; ?>
	</dl>
</div>
<div class="actions">
	<ul>
		<?php
		echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('schedule_32.png',
			array('action' => 'schedule', 'division' => $division['Division']['id']),
			array('alt' => __('Schedule', true), 'title' => __('Schedule', true))));
		echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('standings_32.png',
			array('action' => 'standings', 'division' => $division['Division']['id']),
			array('alt' => __('Standings', true), 'title' => __('Standings', true))));
		if ($is_admin || $is_manager || $is_coordinator) {
			if ($division['Division']['is_playoff']) {
				echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('initialize_32.png',
					array('action' => 'initialize_ratings', 'division' => $division['Division']['id']),
					array('alt' => __('Initialize', true), 'title' => __('Initialize Ratings', true))));
			}
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('edit_32.png',
				array('action' => 'edit', 'division' => $division['Division']['id']),
				array('alt' => __('Edit', true), 'title' => __('Edit Division', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('email_32.png',
				array('action' => 'emails', 'division' => $division['Division']['id']),
				array('alt' => __('Captain Emails', true), 'title' => __('Captain Emails', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('score_approve_32.png',
				array('action' => 'approve_scores', 'division' => $division['Division']['id']),
				array('alt' => __('Approve scores', true), 'title' => __('Approve scores', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('schedule_add_32.png',
				array('controller' => 'schedules', 'action' => 'add', 'division' => $division['Division']['id']),
				array('alt' => __('Add Games', true), 'title' => __('Add Games', true))));
			if (League::hasSpirit($division)) {
				echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('spirit_32.png',
					array('action' => 'spirit', 'division' => $division['Division']['id']),
					array('alt' => __('Spirit', true), 'title' => __('See Division Spirit Report', true))));
			}
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('field_report_32.png',
				array('action' => 'fields', 'division' => $division['Division']['id']),
				array('alt' => sprintf(__('%s Distribution', true), Configure::read('sport.field_cap')), 'title' => sprintf(__('%s Distribution Report', true), Configure::read('sport.field_cap')))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('team_add_32.png',
				array('action' => 'add_teams', 'division' => $division['Division']['id']),
				array('alt' => __('Add Teams', true), 'title' => __('Add Teams', true))));
			// TODO: More links to reports, etc.
		}
		if ($is_admin || $is_manager) {
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('coordinator_add_32.png',
				array('action' => 'add_coordinator', 'division' => $division['Division']['id']),
				array('alt' => __('Add Coordinator', true), 'title' => __('Add Coordinator', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('division_clone_32.png',
				array('action' => 'add', 'league' => $division['League']['id'], 'division' => $division['Division']['id']),
				array('alt' => __('Clone Division', true), 'title' => __('Clone Division', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('delete_32.png',
				array('action' => 'delete', 'division' => $division['Division']['id']),
				array('alt' => __('Delete', true), 'title' => __('Delete Division', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $division['Division']['id']))));
		}
		?>
	</ul>
</div>

<div class="related">
	<?php if (!empty($division['Team'])):?>
	<table class="list">
	<?php
	echo $this->element("leagues/view/{$league_obj->render_element}/heading",
			compact ('is_admin', 'is_manager', 'is_coordinator'));
	$seed = $i = 0;
	foreach ($division['Team'] as $team) {
		$is_captain = in_array($team['id'], $this->Session->read('Zuluru.OwnedTeamIDs'));
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
		echo $this->element("leagues/view/{$league_obj->render_element}/team",
				compact ('is_admin', 'is_manager', 'is_coordinator', 'is_captain', 'division', 'team', 'seed', 'classes'));
	}
	?>
	</table>
	<?php endif; ?>
</div>

<?php if (!empty($division['Event'])): ?>
<div class="related">
	<h3><?php __('Register to play in this division:');?></h3>
	<table class="list">
	<tr>
		<th><?php __('Registration'); ?></th>
		<th><?php __('Type');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($division['Event'] as $related):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $this->Html->link($related['name'], array('controller' => 'events', 'action' => 'view', 'event' => $related['id']));?></td>
			<td><?php __($related['EventType']['name']);?></td>
		</tr>
	<?php endforeach; ?>
	</table>
</div>
<?php endif; ?>
