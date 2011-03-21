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
			<?php __($team['Team']['open_roster'] ? 'Open' : 'Closed'); ?>

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
			echo $this->Html->tag ('li', $this->Html->link(__('Schedule', true), array('action' => 'schedule', 'team' => $team['Team']['id'])));
			echo $this->Html->tag ('li', $this->Html->link(__('Standings', true), array('controller' => 'leagues', 'action' => 'standings', 'league' => $team['League']['id'], 'team' => $team['Team']['id'])));
		}
		if ($is_logged_in && $team['Team']['open_roster']) {
			echo $this->Html->tag ('li', $this->Html->link(__('Join Team', true), array('controller' => 'teams', 'action' => 'roster_status', 'team' => $team['Team']['id'])));
		}
		if ($is_admin || $is_captain) {
			echo $this->Html->tag ('li', $this->Html->link(__('Edit Team', true), array('action' => 'edit', 'team' => $team['Team']['id'])));
			echo $this->Html->tag ('li', $this->Html->link(__('Player Emails', true), array('action' => 'emails', 'team' => $team['Team']['id'])));
		}
		if ($is_admin || ($is_captain && $team['League']['roster_deadline'] >= date('Y-m-d'))) {
			echo $this->Html->tag ('li', $this->Html->link(__('Add player', true), array('action' => 'add_player', 'team' => $team['Team']['id'])));
		}
		if ($is_admin) {
			echo $this->Html->tag ('li', $this->Html->link(__('Delete Team', true), array('action' => 'delete', 'team' => $team['Team']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $team['Team']['id'])));
			echo $this->Html->tag ('li', $this->Html->link(__('Move Team', true), array('action' => 'move', 'team' => $team['Team']['id'])));
			echo $this->Html->tag ('li', $this->Html->link(__('Spirit', true), array('action' => 'spirit', 'team' => $team['Team']['id'])));
		}
		?>
	</ul>
</div>

<?php if ($is_logged_in):?>
<div class="related">
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php __('Name'); ?></th>
		<th><?php __('Position'); ?></th>
		<th><?php __('Gender'); ?></th>
		<th><?php __('Rating'); ?></th>
		<?php if ($is_admin || $is_coordinator) : ?>
		<th><?php __('Shirt Size'); ?></th>
		<?php endif; ?>
		<th><?php __('Date Joined'); ?></th>
	</tr>
	<?php
		$roster_descriptions = Configure::read('options.roster_position');

		$i = $roster_count = $skill_count = $skill_total = 0;
		$roster_required = Configure::read("roster_requirements.{$team['League']['ratio']}");
		foreach ($team['Person'] as $person):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
			if (in_array ($person['TeamsPerson']['status'], Configure::read('playing_roster_positions'))) {
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
		<td><?php
		if ($is_admin || $is_coordinator ||
			(($is_captain || $person['id'] == $my_id) && $team['League']['roster_deadline'] >= date('Y-m-d'))
		)
			echo $this->Html->link(__($roster_descriptions[$person['TeamsPerson']['status']], true),
					array('controller' => 'teams', 'action' => 'roster_status', 'team' => $team['Team']['id'], 'person' => $person['id']));
		else
			__($roster_descriptions[$person['TeamsPerson']['status']]);
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
