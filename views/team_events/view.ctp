<?php
$this->Html->addCrumb (__('Team Events', true));
$this->Html->addCrumb ($event['Team']['name']);
$this->Html->addCrumb ($event['TeamEvent']['name']);
$this->Html->addCrumb (__('View', true));
?>

<div class="teamEvents view">
<h2><?php  echo $event['Team']['name'] . ': ' . $event['TeamEvent']['name'];?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Team'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->element('teams/block', array('team' => $event['Team'], 'show_shirt' => false)); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Event'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $event['TeamEvent']['name']; ?>
			&nbsp;
		</dd>
		<?php if (!empty($event['TeamEvent']['description'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Description'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $event['TeamEvent']['description']; ?>
			&nbsp;
		</dd>
		<?php endif; ?>
		<?php if (Configure::read('feature.urls') && !empty($event['TeamEvent']['website'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Website'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link ($event['TeamEvent']['website'], $event['TeamEvent']['website']); ?>
			&nbsp;
		</dd>
		<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Date'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->date ($event['TeamEvent']['date']); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Start'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->time ($event['TeamEvent']['start']); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('End'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->time ($event['TeamEvent']['end']); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Location'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $event['TeamEvent']['location_name']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Address'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			$address = "{$event['TeamEvent']['location_street']}, {$event['TeamEvent']['location_city']}, {$event['TeamEvent']['location_province']}";
			$link_address = strtr ($address, ' ', '+');
			echo $this->Html->link($address, "http://maps.google.com/maps?q=$link_address");
			?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Totals'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>><?php
		// Build the totals
		$statuses = Configure::read('attendance');
		$alt = Configure::read('attendance_alt');
		$count = array_fill_keys(array_keys($statuses), array('Male' => 0, 'Female' => 0));
		foreach ($attendance['Attendance'] as $record) {
			$person = array_shift (Set::extract("/Team/Person[id={$record['person_id']}]/.", $event));
			$status = $record['status'];
			++$count[$status][$person['gender']];
		}

		foreach ($statuses as $status => $description) {
			$counts = array();
			foreach (array('Male', 'Female') as $gender) {
				if ($count[$status][$gender]) {
					$counts[] = $count[$status][$gender] . substr (__($gender, true), 0, 1);
				}
			}
			if (!empty ($counts)) {
				$low = low($statuses[$status]);
				$short = $this->ZuluruHtml->icon("attendance_{$low}_dedicated_24.png", array(
						'title' => sprintf (__('Attendance: %s', true), __($statuses[$status], true)),
						'alt' => $alt[$status],
				));
				echo $short . ': ' . implode(' / ', $counts) . '&nbsp;';
			}
		}
		?></dd>
	</dl>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->ZuluruHtml->iconLink('edit_32.png',
					array('action' => 'edit', 'event' => $event['TeamEvent']['id'], 'return' => true),
					array('alt' => __('Edit Event', true), 'title' => __('Edit Event', true))); ?></li>
		<li><?php echo $this->ZuluruHtml->iconLink('delete_32.png',
					array('action' => 'delete', 'event' => $event['TeamEvent']['id']),
					array('alt' => __('Delete Event', true), 'title' => __('Delete Event', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $event['TeamEvent']['id']))); ?></li>
		<li><?php echo $this->ZuluruHtml->iconLink('team_event_add_32.png',
					array('action' => 'add', 'team' => $event['TeamEvent']['team_id']),
					array('alt' => __('New Event', true), 'title' => __('Create a New Event', true))); ?></li>
	</ul>
</div>
<div class="related">
	<table class="list">
	<thead>
	<tr>
		<th><?php __('Name'); ?></th>
		<th><?php __('Role'); ?></th>
		<th><?php __('Gender'); ?></th>
		<th><?php __('Rating'); ?></th>
		<th><?php __('Attendance'); ?></th>
		<th><?php __('Updated'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	$i = 1;
	foreach ($event['Team']['Person'] as $person):
		$record = array_shift (Set::extract("/Attendance[person_id={$person['id']}]/.", $attendance));
		$status = $record['status'];
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $this->element('people/block', compact('person')); ?></td>
		<td><?php __(Configure::read("options.roster_role.{$person['TeamsPerson']['role']}")); ?></td>
		<td><?php __($person['gender']);?></td>
		<td><?php echo $person['skill_level'];?></td>
		<td class="<?php echo low($statuses[$status]);?>"><?php
			echo $this->element('team_events/attendance_change', array(
				'team' => $event['Team'],
				'event_id' => $event['TeamEvent']['id'],
				'date' => $event['TeamEvent']['date'],
				'time' => $event['TeamEvent']['start'],
				'person_id' => $person['id'],
				'role' => $person['TeamsPerson']['role'],
				'status' => $status,
				'comment' => $record['comment'],
				'dedicated' => true,
			));
		?></td>
		<td><?php
		if ($record['created'] != $record['updated']) {
			echo $this->ZuluruTime->datetime($record['updated']);
		}
		?></td>
	</tr>
	<?php endforeach; ?>

	</tbody>
	</table>
</div>

<?php echo $this->element('games/attendance_div'); ?>
