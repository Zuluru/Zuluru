<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb ($person['Person']['full_name']);
$this->Html->addCrumb (__('View', true));
?>

<div class="people view">
<h2><?php
if ($is_logged_in) {
	if (!empty($person['Upload'])) {
		foreach ($person['Upload'] as $key => $upload) {
			if ($upload['type_id'] === null) {
				if ($upload['approved']) {
					echo $this->element('people/player_photo', array('person' => $person['Person'], 'upload' => $upload));
				}
				// Remove photos from the list of documents we'll show later
				unset($person['Upload'][$key]);
			}
		}
	}
}
echo $person['Person']['full_name'];
?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<?php if ($is_me || $is_admin):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('System User Name'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $person['Person']['user_name']; ?>

			</dd>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Website User Id'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $person['Person']['id']; ?>

			</dd>
		<?php endif; ?>
		<?php if ($is_me || $is_admin || $is_coordinator || $is_captain || $is_my_captain || $is_division_captain ||
				($is_logged_in && $person['Person']['publish_email'])):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Email Address'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php
				echo $this->Html->link($person['Person']['email'], "mailto:{$person['Person']['email']}");
				echo ' (' . __($person['Person']['publish_email'] ? 'published' : 'private', true) . ')';
				?>

			</dd>
		<?php endif; ?>
		<?php if (Configure::read('profile.home_phone') && !empty($person['Person']['home_phone']) &&
					($is_me || $is_admin || $is_coordinator || $is_captain || $is_my_captain || $is_division_captain ||
						($is_logged_in && $person['Person']['publish_home_phone']))):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Phone (home)'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php
				echo $person['Person']['home_phone'];
				echo ' (' . __($person['Person']['publish_home_phone'] ? 'published' : 'private', true) . ')';
				?>

			</dd>
		<?php endif; ?>
		<?php if (Configure::read('profile.work_phone') && !empty($person['Person']['work_phone']) &&
					($is_me || $is_admin || $is_coordinator || $is_captain || $is_my_captain || $is_division_captain ||
						($is_logged_in && $person['Person']['publish_work_phone']))):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Phone (work)'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php
				echo $person['Person']['work_phone'];
				if (!empty($person['Person']['work_ext'])) {
					echo ' x' . $person['Person']['work_ext'];
				}
				echo ' (' . __($person['Person']['publish_work_phone'] ? 'published' : 'private', true) . ')';
				?>

			</dd>
		<?php endif; ?>
		<?php if (Configure::read('profile.mobile_phone') && !empty($person['Person']['mobile_phone']) &&
					($is_me || $is_admin || $is_coordinator || $is_captain || $is_my_captain || $is_division_captain ||
						($is_logged_in && $person['Person']['publish_mobile_phone']))):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Phone (mobile)'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php
				echo $person['Person']['mobile_phone'];
				echo ' (' . __($person['Person']['publish_mobile_phone'] ? 'published' : 'private', true) . ')';
				?>

			</dd>
		<?php endif; ?>
		<?php if ($is_me || $is_admin):?>
			<?php if (Configure::read('profile.addr_street')): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Address'); ?></dt>
			<dd<?php if ($i % 2 == 0) echo $class;?>>
				<?php echo $person['Person']['addr_street']; ?>

			</dd>
			<?php endif; ?>
			<?php if (Configure::read('profile.addr_city') || Configure::read('profile.addr_prov') || Configure::read('profile.addr_country')): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>>&nbsp;</dt>
			<dd<?php if ($i % 2 == 0) echo $class;?>>
				<?php
				$addr = array();
				if (Configure::read('profile.addr_city')) {
					$addr[] = $person['Person']['addr_city'];
				}
				if (Configure::read('profile.addr_city')) {
					$addr[] = __($person['Person']['addr_prov'], true);
				}
				if (Configure::read('profile.addr_city')) {
					$addr[] = __($person['Person']['addr_country'], true);
				}
				echo implode(', ', $addr);
				?>

			</dd>
			<?php endif; ?>
			<?php if (Configure::read('profile.addr_postalcode')): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>>&nbsp;</dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $person['Person']['addr_postalcode']; ?>

			</dd>
			<?php endif; ?>
			<?php if (Configure::read('profile.birthdate')): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Birthdate'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $this->ZuluruTime->date($person['Person']['birthdate']); ?>
				&nbsp;
			</dd>
			<?php endif; ?>
		<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Gender'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($person['Person']['gender']); ?>

		</dd>
		<?php if ($is_me || $is_admin || $is_coordinator || $is_captain):?>
			<?php if (Configure::read('profile.height')): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Height'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $person['Person']['height'] . ' ' . __('inches', true); ?>

			</dd>
			<?php endif; ?>
			<?php if (Configure::read('profile.shirt_size')): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Shirt Size'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php __($person['Person']['shirt_size']); ?>

			</dd>
			<?php endif; ?>
		<?php endif; ?>
		<?php if (Configure::read('profile.skill_level') && !empty ($person['Person']['skill_level'])):?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Skill Level'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __(Configure::read("options.skill.{$person['Person']['skill_level']}")) ; ?>

		</dd>
		<?php endif; ?>
		<?php if ($is_logged_in && !empty ($person['Person']['skill_level'])):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Year Started'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $person['Person']['year_started']; ?>

			</dd>
		<?php endif; ?>
		<?php if ($is_me || $is_admin):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Account Class'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php __($person['Group']['name']); ?>

			</dd>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Account Status'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php __($person['Person']['status']); ?>

			</dd>
			<?php if (Configure::read('feature.dog_questions')):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Has Dog'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php __($person['Person']['has_dog'] ? 'yes' : 'no'); ?>

			</dd>
			<?php endif; ?>
			<?php if (Configure::read('profile.willing_to_volunteer')): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Willing To Volunteer'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php __($person['Person']['willing_to_volunteer'] ? 'yes' : 'no'); ?>

			</dd>
			<?php endif; ?>
			<?php if (Configure::read('profile.contact_for_feedback')): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Contact For Feedback'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php __($person['Person']['contact_for_feedback'] ? 'yes' : 'no'); ?>

			</dd>
			<?php endif; ?>
		<?php endif; ?>
		<?php if (!empty($person['Note'])): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Private Note'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $person['Note'][0]['note']; ?>

			</dd>
		<?php endif; ?>
	</dl>
</div>
<div class="actions">
	<ul>
		<?php
		if ($is_logged_in && Configure::read('feature.annotations')) {
			if (!empty($person['Note'])) {
				echo $this->Html->tag ('li', $this->Html->link(__('Delete Note', true), array('action' => 'delete_note', 'person' => $person['Person']['id'])));
				$link = 'Edit Note';
			} else {
				$link = 'Add Note';
			}
			echo $this->Html->tag ('li', $this->Html->link(__($link, true), array('action' => 'note', 'person' => $person['Person']['id'])));
		}
		if ($is_me || $is_admin) {
			echo $this->Html->tag ('li', $this->Html->link(__('Edit Profile', true), array('action' => 'edit', 'person' => $person['Person']['id'], 'return' => true)));
			echo $this->Html->tag ('li', $this->Html->link(__('Edit Preferences', true), array('action' => 'preferences', 'person' => $person['Person']['id'])));
		}
		if ($is_admin) {
			echo $this->Html->tag ('li', $this->Html->link(__('Delete Player', true), array('action' => 'delete', 'person' => $person['Person']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $person['Person']['id'])));
		}
		?>
	</ul>
</div>

<?php if ($is_logged_in):?>
<div class="related">
	<h3><?php __('Teams');?></h3>
	<?php if (!empty($person['Team'])):?>
	<table class="list">
	<?php
		$i = 0;
		foreach ($person['Team'] as $team):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php
			echo $this->element('people/roster_role', array('roster' => $team['TeamsPerson'], 'division' => $team['Division'])) .
				' ' . __('on', true) . ' ' .
				$this->element('teams/block', array('team' => $team['Team'])) .
				' (' . $this->element('divisions/block', array('division' => $team['Division'], 'field' => 'long_league_name')) . ')';
			if (!empty($team['Team']['division_id'])) {
				Configure::load("sport/{$team['Division']['League']['sport']}");
				$positions = Configure::read('sport.positions');
				if (!empty($positions)) {
					echo ' (' . $this->element('people/roster_position', array('roster' => $team['TeamsPerson'], 'division' => $team['Division'])) . ')';
				}
			}
			?></td>
		</tr>
		<?php endforeach; ?>
	</table>
	<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('Show Team History', true), array('controller' => 'people', 'action' => 'teams', 'person' => $person['Person']['id'])); ?> </li>
		</ul>
	</div>
</div>

	<?php if (Configure::read('feature.badges') && !empty($person['Badge'])): ?>
<div class="related">
	<h3><?php __('Badges');?></h3>
	<p><?php
	foreach ($person['Badge'] as $badge) {
		echo $this->ZuluruHtml->iconLink("{$badge['icon']}_64.png", array('controller' => 'badges', 'action' => 'view', 'badge' => $badge['id']),
			array('alt' => $badge['name'], 'title' => $badge['description']));
	}
	?></p>
	<?php endif; ?>
<?php endif; ?>

<?php if (!empty($person['Division'])):?>
<div class="related">
	<h3><?php __('Divisions');?></h3>
	<table class="list">
	<?php
		$i = 0;
		foreach ($person['Division'] as $division):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo __(Configure::read("options.division_position.{$division['DivisionsPerson']['position']}"), true) . ' ' . __('of', true) . ' ' .
					$this->element('divisions/block', array('division' => $division['Division'], 'field' => 'long_league_name'));?></td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>
<?php endif; ?>

<?php if (Configure::read('scoring.allstars') && ($is_admin || $is_coordinator)):?>
<div class="related">
	<h3><?php __('Allstar Nominations');?></h3>
	<?php if (!empty($person['Allstar'])):?>
	<table class="list">
	<tr>
		<th><?php __('Date'); ?></th>
		<th><?php __('Home Team'); ?></th>
		<th><?php __('Away Team'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($person['Allstar'] as $allstar):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $this->Html->link($this->ZuluruTime->datetime("{$allstar['GameSlot']['game_date']} {$allstar['GameSlot']['game_start']}"), array('controller' => 'games', 'action' => 'view', 'game' => $allstar['Game']['id']));?></td>
			<td><?php echo $this->element('teams/block', array('team' => $allstar['HomeTeam'])); ?></td>
			<td><?php echo $this->element('teams/block', array('team' => $allstar['AwayTeam'])); ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('Delete', true), array('controller' => 'allstars', 'action' => 'delete', 'allstar' => $allstar['Allstar']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $allstar['Allstar']['id'])); ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
	<?php endif; ?>
</div>
<?php endif; ?>

<?php if (Configure::read('feature.registration')):?>
<?php if ($is_admin || ($is_me && !empty($person['Preregistration']))):?>
<div class="related">
	<h3><?php __('Preregistrations');?></h3>
	<?php if (!empty($person['Preregistration'])):?>
	<table class="list">
	<tr>
		<th><?php __('Event'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($person['Preregistration'] as $preregistration):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $this->Html->link($preregistration['Event']['name'], array('controller' => 'events', 'action' => 'view', 'event' => $preregistration['Event']['id']));?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('Delete', true), array('controller' => 'preregistrations', 'action' => 'delete', 'prereg' => $preregistration['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $preregistration['id'])); ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
	<?php endif; ?>

	<?php if ($is_admin):?>
	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('Add Preregistration', true), array('controller' => 'preregistrations', 'action' => 'add', 'person' => $person['Person']['id']));?> </li>
		</ul>
	</div>
	<?php endif; ?>
</div>
<?php endif; ?>

<?php if (($is_admin || $is_me) && !empty($person['Registration'])):?>
<div class="related">
	<h3><?php __('Recent Registrations');?></h3>
	<table class="list">
	<tr>
		<th><?php __('Event'); ?></th>
		<th><?php __('Date'); ?></th>
		<th><?php __('Payment'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($person['Registration'] as $registration):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $this->Html->link($registration['Event']['name'], array('controller' => 'events', 'action' => 'view', 'event' => $registration['Event']['id']));?></td>
			<td><?php echo $this->ZuluruTime->date($registration['created']);?></td>
			<td><?php echo $registration['payment'];?></td>
			<td class="actions">
			<?php if ($is_admin): ?>
				<?php echo $this->Html->link(__('View', true), array('controller' => 'registrations', 'action' => 'view', 'registration' => $registration['id']));?>
				<?php echo $this->Html->link(__('Edit', true), array('controller' => 'registrations', 'action' => 'edit', 'registration' => $registration['id'], 'return' => true)); ?>
			<?php endif; ?>
				<?php echo $this->Html->link(__('Unregister', true), array('controller' => 'registrations', 'action' => 'unregister', 'registration' => $registration['id'], 'return' => true), null, sprintf(__('Are you sure you want to delete # %s?', true), $registration['id'])); ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('Show Registration History', true), array('controller' => 'people', 'action' => 'registrations', 'person' => $person['Person']['id'])); ?> </li>
		</ul>
	</div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php if (($is_admin || $is_me) && !empty($person['Waiver'])):?>
<div class="related">
	<h3><?php __('Waivers');?></h3>
	<?php if (!empty($person['Waiver'])):?>
	<table class="list">
	<tr>
		<th><?php __('Waiver');?></th>
		<th><?php __('Signed');?></th>
		<th><?php __('Valid From');?></th>
		<th><?php __('Valid Until');?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($person['Waiver'] as $waiver):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $waiver['name']; ?></td>
			<td><?php echo $this->ZuluruTime->fulldate($waiver['WaiversPerson']['created']); ?></td>
			<td><?php echo $this->ZuluruTime->fulldate($waiver['WaiversPerson']['valid_from']); ?></td>
			<td><?php echo $this->ZuluruTime->fulldate($waiver['WaiversPerson']['valid_until']); ?></td>
			<td class="actions"><?php echo $this->ZuluruHtml->iconLink('view_24.png', array('controller' => 'waivers', 'action' => 'review', 'waiver' => $waiver['id'], 'date' => $waiver['WaiversPerson']['valid_from'])); ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
	<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('Show Waiver History', true), array('controller' => 'people', 'action' => 'waivers', 'person' => $person['Person']['id'])); ?> </li>
		</ul>
	</div>
</div>
<?php endif; ?>

<?php if (Configure::read('feature.documents') && ($is_admin || $is_me)):?>
<div class="related">
	<h3><?php __('Documents');?></h3>
<?php if (!empty($person['Upload'])): ?>
	<table class="list">
	<tr>
		<th><?php __('Document'); ?></th>
		<th><?php __('Valid From'); ?></th>
		<th><?php __('Valid Until'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($person['Upload'] as $document):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
			$rand = 'row_' . mt_rand();
		?>
		<tr<?php echo $class;?> id="<?php echo $rand; ?>">
			<td><?php echo $document['UploadType']['name'];?></td>
<?php if ($document['approved']): ?>
			<td><?php echo $this->ZuluruTime->date($document['valid_from']);?></td>
			<td><?php echo $this->ZuluruTime->date($document['valid_until']);?></td>
<?php else: ?>
			<td colspan="2" class="highlight"><?php __('Unapproved');?></td>
<?php endif; ?>
			<td class="actions">
				<?php echo $this->Html->link(__('View', true), array('action' => 'document', 'id' => $document['id']), array('target' => 'preview'));?>
<?php if ($is_admin):?>
<?php if ($document['approved']): ?>
				<?php echo $this->Html->link(__('Edit', true), array('action' => 'edit_document', 'id' => $document['id'], 'return' => true));?>
<?php else: ?>
				<?php echo $this->Html->link(__('Approve', true), array('action' => 'approve_document', 'id' => $document['id'], 'return' => true));?>
<?php endif; ?>
<?php endif; ?>
				<?php echo $this->Js->link (__('Delete', true),
					array('action' => 'delete_document', 'id' => $document['id'], 'row' => $rand),
					array('update' => "#temp_update", 'confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $document['id']))); ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
<?php endif; ?>
	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('Upload New Document', true), array('action' => 'document_upload', 'person' => $person['Person']['id'])); ?> </li>
		</ul>
	</div>
</div>
<?php endif; ?>

<?php if (Configure::read('feature.tasks') && $is_admin && !empty($person['TaskSlot'])):?>
	<div class="related">
<h3><?php __('Assigned Tasks'); ?></h3>
<table class="list">
<tr>
	<th><?php __('Task'); ?></th>
	<th><?php __('Time'); ?></th>
	<th><?php __('Report To'); ?></th>
</tr>
<?php
$i = 0;
foreach ($person['TaskSlot'] as $task):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td class="splash_item"><?php
			echo $this->Html->link($task['Task']['name'], array('controller' => 'tasks', 'action' => 'view', 'task' => $task['Task']['id']));
		?></td>
		<td class="splash_item"><?php
		echo $this->ZuluruTime->day($task['task_date']) . ', ' .
					$this->ZuluruTime->time($task['task_start']) . '-' .
					$this->ZuluruTime->time($task['task_end'])
		?></td>
		<td class="splash_item"><?php
		echo $this->element('people/block', array('person' => $task['Task']['Person']));
		?></td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<?php if (!empty($teams)) echo $this->element('people/roster_div'); ?>
