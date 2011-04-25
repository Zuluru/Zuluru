<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb ($team['Team']['name']);
$this->Html->addCrumb (__('View', true));
?>

<div class="teams view">
<h2><?php  echo __('View Team', true) . ': ' . $team['Team']['name'];?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<?php if (!empty ($team['Team']['website'])):?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Website'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($team['Team']['website'], $team['Team']['website']); ?>

		</dd>
		<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Shirt Colour'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($team['Team']['shirt_colour']); ?>
			&nbsp;
		</dd>
		<?php if ($team['League']['id']): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('League'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($team['League']['long_name'], array('controller' => 'leagues', 'action' => 'view', 'league' => $team['League']['id'])); ?>

		</dd>
		<?php endif; ?>
		<?php if (!empty ($team['Team']['home_field'])):?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Home Field'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php // TODO: home field name ?>
			<?php echo $this->Html->link($team['Team']['home_field'], array('controller' => 'fields', 'action' => 'view', 'field' => $team['Team']['home_field'])); ?>

		</dd>
		<?php endif; ?>
		<?php if (Configure::read('feature.region_preference') && !empty ($team['Team']['region_preference'])):?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Region Preference'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($team['Team']['region_preference']); ?>

		</dd>
		<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Roster Status'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			__($team['Team']['open_roster'] ? 'Open' : 'Closed');
			echo ' ' . $this->ZuluruHtml->help(array('action' => 'teams', 'roster_status'));
			?>

		</dd>
		<?php // TODO: SBF ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Rating'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $team['Team']['rating']; ?>

		</dd>
	</dl>
</div>
<div class="actions">
	<ul>
		<?php
		if ($team['League']['id']) {
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('schedule_32.png',
				array('action' => 'schedule', 'team' => $team['Team']['id']),
				array('alt' => __('Schedule', true), 'title' => __('View Team Schedule', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('standings_32.png',
				array('controller' => 'leagues', 'action' => 'standings', 'league' => $team['League']['id'], 'team' => $team['Team']['id']),
				array('alt' => __('Standings', true), 'title' => __('View Team Standings', true))));

		}
		if ($is_logged_in && $team['Team']['open_roster'] && $team['League']['roster_deadline'] >= date('Y-m-d') &&
			!in_array($team['Team']['id'], $this->Session->read('Zuluru.TeamIDs')))
		{
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('roster_add_32.png',
				array('action' => 'roster_request', 'team' => $team['Team']['id']),
				array('alt' => __('Join Team', true), 'title' => __('Join Team', true))));
		}
		if ($is_admin || $is_captain) {
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('edit_32.png',
				array('action' => 'edit', 'team' => $team['Team']['id']),
				array('alt' => __('Edit Team', true), 'title' => __('Edit Team', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('email_32.png',
				array('action' => 'emails', 'team' => $team['Team']['id']),
				array('alt' => __('Player Emails', true), 'title' => __('Player Emails', true))));
		}
		if ($is_admin || ($is_captain && $team['League']['roster_deadline'] >= date('Y-m-d'))) {
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('roster_add_32.png',
				array('action' => 'add_player', 'team' => $team['Team']['id']),
				array('alt' => __('Add Player', true), 'title' => __('Add Player', true))));
		}
		if ($is_admin) {
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('spirit_32.png',
				array('action' => 'spirit', 'team' => $team['Team']['id']),
				array('alt' => __('Spirit', true), 'title' => __('See Team Spirit Report', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('move_32.png',
				array('action' => 'move', 'team' => $team['Team']['id']),
				array('alt' => __('Move Team', true), 'title' => __('Move Team', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('delete_32.png',
				array('action' => 'delete', 'team' => $team['Team']['id']),
				array('alt' => __('Delete', true), 'title' => __('Delete Team', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $team['Team']['id']))));
		}
		?>
	</ul>
</div>

<?php if ($is_logged_in):?>
<div class="related">
	<?php
	$cols = 5;
	$warning = false;
	?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php __('Name'); ?></th>
		<th><?php __('Position'); ?></th>
		<th><?php __('Gender'); ?></th>
		<th><?php __('Rating'); ?></th>
		<?php if ($is_admin || $is_coordinator) : ?>
		<th><?php __('Shirt Size'); ?></th>
		<?php
			++$cols;
		endif;
		?>
		<th><?php __('Date Joined'); ?></th>
	</tr>
	<?php
		$i = $roster_count = $skill_count = $skill_total = 0;
		$roster_required = Configure::read("roster_requirements.{$team['League']['ratio']}");
		foreach ($team['Person'] as $person):
			// Maybe add a warning
			if ($person['can_add'] !== true && !$warning):
				$warning = true;
				$class = ' class="error-message"';
				if ($i++ % 2 == 0) {
					$class = ' class="altrow error-message"';
				}
	?>
	<tr<?php echo $class;?>>
		<td colspan="<?php echo $cols; ?>"><strong>
			<?php echo sprintf(__('Notice: The following players are currently INELIGIBLE to participate on this roster. This is typically because they do not have a current membership. They are not allowed to play with this team until this is corrected. Hover your mouse over the %s to see the specific reason why.', true),
				$this->ZuluruHtml->icon('help_16.png', array('alt' => '?'))); ?>
		</strong></td>
	</tr>
	<?php
			endif;

			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
			if (in_array ($person['TeamsPerson']['position'], Configure::read('playing_roster_positions')) &&
				$person['TeamsPerson']['status'] == ROSTER_APPROVED)
			{
				++ $roster_count;
				if ($person['skill_level']) {
					++ $skill_count;
					$skill_total += $person['skill_level'];
				}
			}

			$conflicts = array();
			// TODO: Check for roster conflicts
			if ($person['status'] == 'inactive') {
				$conflicts[] = '(' . __('account inactive', true) . ')';
			}
	?>
	<tr<?php echo $class;?>>
		<td><?php
		echo $this->element('people/block', compact('person'));
		if (!empty ($conflicts)) {
			echo '<div class="roster_conflict">' . implode ('<br />', $conflicts) . '</div>';
		}
		?></td>
		<td<?php if ($warning) echo ' class="error-message"';?>><?php
		echo $this->element('people/roster', array('roster' => $person['TeamsPerson'], 'league' => $team['League']));
		if ($person['can_add'] !== true) {
			echo ' ' . $this->ZuluruHtml->icon('help_16.png', array('title' => $person['can_add'], 'alt' => '?'));
		}
		?></td>
		<td><?php __($person['gender']);?></td>
		<td><?php echo $person['skill_level'];?></td>
		<?php
		if ($is_admin || $is_coordinator) {
			echo $this->Html->tag('td', __($person['shirt_size'], true));
		}
		?>
		<td><?php echo $this->ZuluruTime->date($person['TeamsPerson']['created']);?></td>
	</tr>
	<?php endforeach; ?>
	<?php
	if ($skill_count) :
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td colspan="3"><?php __('Average Skill Rating') ?></td>
		<td><?php printf("%.2f", $skill_total / $skill_count) ?></td>
		<?php if ($is_admin || $is_coordinator) echo '<td></td>'; ?>
		<td></td>
	</tr>
	<?php endif; ?>
	</table>

	<?php if (($is_admin || $is_coordinator || $is_captain) && $roster_count < $roster_required && $team['League']['roster_deadline'] != '0000-00-00' && $team['League']['roster_deadline'] >= date('Y-m-d')):?>
	<p class="error-message">This team currently has only <?php echo $roster_count ?> full-time players listed. Your team roster must have a minimum of <?php echo $roster_required ?> rostered 'regular' players by the start of your league. For playoffs, your roster must be finalized by the team roster deadline (<?php
	echo $this->ZuluruTime->date($team['League']['roster_deadline']); ?>), and all team members must be listed as a 'regular player'.  If an individual has not replied promptly to your request to join, we suggest that you contact them to remind them to respond.</p>
	<?php endif; ?>

</div>
<?php endif; ?>
