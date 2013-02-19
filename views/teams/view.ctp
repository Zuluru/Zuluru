<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb ($team['Team']['name']);
$this->Html->addCrumb (__('View', true));
?>

<div class="teams view">
<h2><?php echo $team['Team']['name'];?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<?php if (Configure::read('feature.urls') && !empty ($team['Team']['website'])):?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Website'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($team['Team']['website'], $team['Team']['website']); ?>

		</dd>
		<?php endif; ?>
		<?php if (Configure::read('feature.shirt_colour')): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Shirt Colour'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			__($team['Team']['shirt_colour']);
			echo ' ' . $this->ZuluruHtml->help(array('action' => 'teams', 'edit', 'shirt_colour'));
			?>
			&nbsp;
		</dd>
		<?php endif; ?>
		<?php if ($team['Division']['id']): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Division'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($team['Division']['full_league_name'], array('controller' => 'divisions', 'action' => 'view', 'division' => $team['Division']['id'])); ?>

		</dd>
		<?php endif; ?>
		<?php if (Configure::read('feature.home_field') && !empty ($team['Team']['home_field'])):?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Home Field'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->element('fields/block', array('field' => $team['Field'], 'display_field' => 'long_name')); ?>

		</dd>
		<?php endif; ?>
		<?php if (Configure::read('feature.region_preference') && !empty ($team['Team']['region_preference'])):?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Region Preference'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($team['Region']['name']); ?>

		</dd>
		<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Roster Status'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			__($team['Team']['open_roster'] ? 'Open' : 'Closed');
			echo ' ' . $this->ZuluruHtml->help(array('action' => 'teams', 'edit', 'open_roster'));
			?>

		</dd>
		<?php if (Configure::read('feature.attendance')): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Track Attendance'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			__($team['Team']['track_attendance'] ? 'Yes' : 'No');
			echo ' ' . $this->ZuluruHtml->help(array('action' => 'teams', 'edit', 'track_attendance'));
			?>

		</dd>
		<?php if ($team['Team']['track_attendance']): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Attendance Reminder'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			switch ($team['Team']['attendance_reminder']) {
				case -1:
					__('disabled');
					break;

				case 0:
					__('day of game');
					break;

				case 1:
					__('day before game');
					break;

				default:
					printf(__('%d days before game', true), $team['Team']['attendance_reminder']);
					break;
			}
			?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Attendance Summary'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			switch ($team['Team']['attendance_summary']) {
				case -1:
					__('disabled');
					break;

				case 0:
					__('day of game');
					break;

				case 1:
					__('day before game');
					break;

				default:
					printf(__('%d days before game', true), $team['Team']['attendance_summary']);
					break;
			}
			?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Attendance Notification'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			switch ($team['Team']['attendance_notification']) {
				case -1:
					__('disabled');
					break;

				case 0:
					__('day of game');
					break;

				case 1:
					__('day before game');
					break;

				default:
					printf(__('%d days before game', true), $team['Team']['attendance_notification']);
					break;
			}
			?>

		</dd>
		<?php endif; ?>
		<?php endif; ?>
		<?php // TODO: SBF ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Rating'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $team['Team']['rating']; ?>

		</dd>
		<?php if (Configure::read('feature.franchises') && !empty ($team['Franchise'])):?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Franchises'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			$franchises = array();
			foreach ($team['Franchise'] as $franchise) {
				$franchises[] = $this->Html->link($franchise['name'], array('controller' => 'franchises', 'action' => 'view', 'franchise' => $franchise['id']));
			}
			echo implode (', ', $franchises);
			?>

		</dd>
		<?php endif; ?>
		<?php if (isset ($affiliate)): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Affiliated Team'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			echo $this->Html->link($affiliate['Team']['name'], array('action' => 'view', 'team' => $affiliate['Team']['id'])) .
				' (' .
				$this->Html->link($affiliate['Division']['full_league_name'], array('controller' => 'divisions', 'action' => 'view', 'division' => $affiliate['Division']['id'])) .
				')';
			?>

		</dd>
		<?php endif; ?>
		<?php if (!empty($team['Note'])): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Private Note'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $team['Note'][0]['note']; ?>

			</dd>
		<?php endif; ?>
	</dl>
</div>
<div class="actions">
	<?php
	$extra = array();
	if ($is_captain && Configure::read('scoring.stat_tracking') && League::hasStats($team['Division']['League'])) {
		$extra[] = $this->ZuluruHtml->iconLink('pdf_32.png',
				array('action' => 'stat_sheet', 'team' => $team['Team']['id']),
				array('alt' => __('Stat Sheet', true), 'title' => __('Stat Sheet', true)));
	}

	$has_numbers = false;
	$numbers = array_unique(Set::extract('/Person/TeamsPerson/number', $team));
	if (Configure::read('feature.shirt_numbers') && count($numbers) > 1 && $numbers[0] !== null) {
		$has_numbers = true;
	}
	if (Configure::read('feature.shirt_numbers') && !$has_numbers && ($is_effective_admin || $is_effective_coordinator || $is_captain)) {
		$extra[] = $this->ZuluruHtml->link(__('Jersey Numbers', true), array('action' => 'numbers', 'team' => $team['Team']['id']));
	}
	echo $this->element('teams/actions', array('team' => $team['Team'], 'division' => $team['Division'], 'league' => $team['Division']['League'], 'format' => 'list', 'extra' => $extra));
	?>
</div>

<?php if ($is_logged_in):?>
<div class="related">
	<?php
	$cols = 4;
	$warning = false;
	?>
	<table class="list">
	<tr>
		<?php if ($has_numbers): ?>
		<th><?php __('Number'); ?></th>
		<?php
			++$cols;
		endif;
		?>
		<th><?php __('Name'); ?></th>
		<th><?php __('Role'); ?></th>
		<th><?php __('Gender'); ?></th>
		<?php if (Configure::read('profile.skill_level')): ?>
		<th><?php __('Rating'); ?></th>
		<?php
			++$cols;
		endif;
		?>
		<?php if (Configure::read('profile.shirt_size') && ($is_admin || $is_coordinator)): ?>
		<th><?php __('Shirt Size'); ?></th>
		<?php
			++$cols;
		endif;
		?>
		<th><?php __('Date Joined'); ?></th>
	</tr>
	<?php
		$i = $roster_count = $skill_count = $skill_total = 0;
		$roster_required = Configure::read("sport.roster_requirements.{$team['Division']['ratio']}");
		foreach ($team['Person'] as $person):
			// Maybe add a warning
			if ($person['can_add'] !== true && !$warning):
				$warning = true;
				$class = ' class="warning-message"';
				if ($i++ % 2 == 0) {
					$class = ' class="altrow warning-message"';
				}
	?>
	<tr<?php echo $class;?>>
		<td colspan="<?php echo $cols; ?>"><strong>
			<?php
			if ($team['Division']['is_playoff']) {
				$typical_reason = 'the current roster does not meet the playoff roster rules';
			} else if (Configure::read('feature.registration') && $team['Division']['flag_membership']) {
				$typical_reason = 'they do not have a current membership';
			} else {
				$typical_reason = 'there is something wrong with their account';
			}
			echo sprintf(__('Notice: The following players are currently INELIGIBLE to participate on this roster. This is typically because %s. They are not allowed to play with this team until this is corrected. Hover your mouse over the %s to see the specific reason why.', true),
				__($typical_reason, true),
				$this->ZuluruHtml->icon('help_16.png', array('alt' => '?'))); ?>
		</strong></td>
	</tr>
	<?php
			endif;

			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
			if (in_array ($person['TeamsPerson']['role'], Configure::read('playing_roster_roles')) &&
				$person['TeamsPerson']['status'] == ROSTER_APPROVED)
			{
				++ $roster_count;
				if ($person['skill_level']) {
					++ $skill_count;
					$skill_total += $person['skill_level'];
				}
			}

			$conflicts = array();
			if ($person['status'] == 'inactive') {
				$conflicts[] = __('account inactive', true);
			}
			if (Configure::read('feature.registration') && $team['Division']['flag_membership']  && !$person['is_a_member']) {
				$conflicts[] = __('not a member', true);
			}
			if ($team['Division']['flag_roster_conflict'] && $person['roster_conflict']) {
				$conflicts[] = __('roster conflict', true);
			}
			if ($team['Division']['flag_schedule_conflict'] && $person['schedule_conflict']) {
				$conflicts[] = __('schedule conflict', true);
			}
	?>
	<tr<?php echo $class;?>>
		<?php if ($has_numbers): ?>
		<td><?php echo $this->element('people/number', compact('person')); ?></td>
		<?php endif; ?>
		<td><?php
		echo $this->element('people/block', compact('person'));
		if (!empty ($conflicts)) {
			echo $this->Html->tag('div',
				'(' . implode (', ', $conflicts) . ')',
				array('class' => 'warning-message'));
		}
		?></td>
		<td<?php if ($warning) echo ' class="warning-message"';?>><?php
		echo $this->element('people/roster_role', array('roster' => $person['TeamsPerson'], 'division' => $team['Division']));
		if ($person['can_add'] !== true) {
			echo ' ' . $this->ZuluruHtml->icon('help_16.png', array('title' => $person['can_add'], 'alt' => '?'));
		}
		?></td>
		<td><?php __($person['gender']);?></td>
		<?php if (Configure::read('profile.skill_level')): ?>
		<td><?php echo $person['skill_level'];?></td>
		<?php endif; ?>
		<?php
		if (Configure::read('profile.shirt_size') && ($is_admin || $is_coordinator)) {
			echo $this->Html->tag('td', __($person['shirt_size'], true));
		}
		?>
		<td><?php echo $this->ZuluruTime->date($person['TeamsPerson']['created']);?></td>
	</tr>
	<?php endforeach; ?>
	<?php
	if (Configure::read('profile.skill_level') && $skill_count):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<?php if ($has_numbers): ?>
		<td></td>
		<?php endif; ?>
		<td colspan="3"><?php __('Average Skill Rating') ?></td>
		<td><?php printf("%.2f", $skill_total / $skill_count) ?></td>
		<?php if ($is_admin || $is_coordinator) echo '<td></td>'; ?>
		<td></td>
	</tr>
	<?php endif; ?>
	</table>

	<?php if (($is_admin || $is_coordinator || $is_captain) && $roster_count < $roster_required && !Division::rosterDeadlinePassed($team['Division'])):?>
	<p class="warning-message">
		<?php if (!$team['Division']['is_playoff']): ?>
		This team currently has only <?php echo $roster_count ?> full-time players listed. Your team roster must have a minimum of <?php echo $roster_required ?> rostered 'regular' players by the start of your division. For playoffs, your roster must be finalized by the team roster deadline (<?php
		echo $this->ZuluruTime->date(Division::rosterDeadline($team['Division'])); ?>), and all team members must be listed as a 'regular player'.
		<?php endif; ?>
		If an individual has not replied promptly to your request to join, we suggest that you contact them to remind them to respond.</p>
	<?php endif; ?>

</div>
<?php endif; ?>

<?php echo $this->element('people/number_div'); ?>
<?php echo $this->element('people/roster_div'); ?>
