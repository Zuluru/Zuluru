<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb ($person['Person']['full_name']);
$this->Html->addCrumb (__('View', true));
?>

<div class="people view">
<h2><?php
echo __('View Player', true) . ': ' . $person['Person']['full_name'];
if ($is_logged_in && !empty ($person['Upload']) && $person['Upload'][0]['approved']) {
	echo $this->element('people/player_photo', array('person' => $person['Person'], 'upload' => $person['Upload'][0]));
}
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
		<?php if (!empty($person['Person']['home_phone']) &&
					($is_me || $is_admin || $is_coordinator || $is_captain ||
						($is_logged_in && $person['Person']['publish_home_phone']))):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Phone (home)'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php
				echo $person['Person']['home_phone'];
				echo ' (' . __($person['Person']['publish_home_phone'] ? 'published' : 'private', true) . ')';
				?>

			</dd>
		<?php endif; ?>
		<?php if (!empty($person['Person']['work_phone']) &&
					($is_me || $is_admin || $is_coordinator || $is_captain ||
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
		<?php if (!empty($person['Person']['mobile_phone']) &&
					($is_me || $is_admin || $is_coordinator || $is_captain ||
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
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Address'); ?></dt>
			<dd<?php if ($i % 2 == 0) echo $class;?>>
				<?php echo $person['Person']['addr_street']; ?>

			</dd>
			<dt<?php if ($i % 2 == 0) echo $class;?>>&nbsp;</dt>
			<dd<?php if ($i % 2 == 0) echo $class;?>>
				<?php echo $person['Person']['addr_city'] . ', ' .
							__($person['Person']['addr_prov'], true) . ', ' .
							__($person['Person']['addr_country'], true); ?>

			</dd>
			<dt<?php if ($i % 2 == 0) echo $class;?>>&nbsp;</dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $person['Person']['addr_postalcode']; ?>

			</dd>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Birthdate'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $this->ZuluruTime->date($person['Person']['birthdate']); ?>

			</dd>
		<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Gender'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($person['Person']['gender']); ?>

		</dd>
		<?php if ($is_me || $is_admin || $is_coordinator || $is_captain):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Height'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $person['Person']['height'] . ' ' . __('inches', true); ?>

			</dd>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Shirt Size'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php __($person['Person']['shirt_size']); ?>

			</dd>
		<?php endif; ?>
		<?php if (!empty ($person['Person']['skill_level'])):?>
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
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Willing To Volunteer'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php __($person['Person']['willing_to_volunteer'] ? 'yes' : 'no'); ?>

			</dd>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Contact For Feedback'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php __($person['Person']['contact_for_feedback'] ? 'yes' : 'no'); ?>

			</dd>
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
			echo $this->Html->tag ('li', $this->Html->link(__('Edit Profile', true), array('action' => 'edit', 'person' => $person['Person']['id'])));
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
			echo $this->element('people/roster', array('roster' => $team['TeamsPerson'], 'division' => $team['Division'])) .
				' ' . __('on', true) . ' ' .
				$this->element('teams/block', array('team' => $team['Team'])) .
				' (' . $this->Html->link($team['Division']['long_league_name'], array('controller' => 'divisions', 'action' => 'view', 'division' => $team['Division']['id'])) . ')';
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
					$this->Html->link($division['Division']['long_league_name'], array('controller' => 'divisions', 'action' => 'view', 'division' => $division['Division']['id']));?></td>
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
		<th><?php __('Field'); ?></th>
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
			<td><?php echo $this->Html->link("{$allstar['GameSlot']['game_date']} {$allstar['GameSlot']['game_start']}", array('controller' => 'games', 'action' => 'view', 'game' => $allstar['Game']['id']));?></td>
			<td><?php $this->element('teams/block', array('team' => $allstar['HomeTeam'])); ?></td>
			<td><?php $this->element('teams/block', array('team' => $allstar['AwayTeam'])); ?></td>
			<td><?php echo $this->element('fields/block', array('field' => $allstar['Field'])); ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('Delete', true), array('controller' => 'allstars', 'action' => 'delete', 'allstar' => $allstar['Allstar']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $allstar['Allstar']['id'])); ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
	<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Allstar Nomination', true), array('controller' => 'allstars', 'action' => 'add', 'person' => $person['Person']['id']));?> </li>
		</ul>
	</div>
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
				<?php echo $this->Html->link(__('View', true), array('controller' => 'registrations', 'action' => 'view', 'registration' => $registration['id']));?>
				<?php echo $this->Html->link(__('Edit', true), array('controller' => 'registrations', 'action' => 'edit', 'registration' => $registration['id'])); ?>
				<?php echo $this->Html->link(__('Unregister', true), array('controller' => 'registrations', 'action' => 'unregister', 'registration' => $registration['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $registration['id'])); ?>
			</td>
		</tr>
		<?php endforeach; ?>
</table>
<div class="actions">
<ul>
		<li><?php if ($is_me || $is_admin) echo $this->Html->link(__('Show Registration History', true), array('controller' => 'people', 'action' => 'registrations', 'person' => $person['Person']['id'])); ?> </li>
	</ul>
	</div>
</div>
<?php endif; ?>
<?php endif; ?>
