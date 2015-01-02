<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb ($person['full_name']);
$this->Html->addCrumb (__('View', true));
?>

<div class="people view">
<h2><?php
echo $this->element('people/player_photo', array('person' => $person, 'photo' => $photo));
echo $person['full_name'];
$view_contact = $is_me || $is_admin || $is_manager || $is_coordinator || $is_captain || $is_my_captain || $is_my_coordinator || $is_division_captain;
$has_visible_contact = false;

$this_is_parent = Set::extract('/GroupsPerson[group_id=1]', $groups);
$this_is_parent = (!empty($this_is_parent));
$this_is_player = Set::extract('/GroupsPerson[group_id=2]', $groups);
$this_is_player = (!empty($this_is_player));
?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<?php if ($is_me || $is_admin || $is_manager):?>
			<?php if ($person['user_id']):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('User Name'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $person['user_name']; ?>
			</dd>
				<?php if (!Configure::read('feature.manage_accounts')): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php printf(__('%s User Id', true), Configure::read('feature.manage_name')); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $person['user_id']; ?>
			</dd>
				<?php endif; ?>
			<?php endif; ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Zuluru Id'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $person['id']; ?>
			</dd>
		<?php endif; ?>
		<?php if ($is_admin || $is_manager):?>
			<?php if (!empty($person['last_login'])):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Last Login'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $this->ZuluruTime->datetime($person['last_login']); ?>
			</dd>
			<?php endif; ?>
			<?php if (!empty($person['client_ip'])):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('IP Address'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $person['client_ip']; ?>
			</dd>
			<?php endif; ?>
		<?php endif; ?>
		<?php if (($view_contact || ($is_logged_in && $person['publish_email'])) && !empty($person['email'])):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Email Address'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php
				$has_visible_contact = true;
				echo $this->Html->link($person['email'], "mailto:{$person['email']}");
				echo ' (' . ($person['publish_email'] ? __('published', true) : __('private', true)) . ')';
				?>
			</dd>
		<?php endif; ?>
		<?php if (($view_contact || ($is_logged_in && $person['publish_alternate_email'])) && !empty($person['alternate_email'])):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Alternate Email Address'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php
				$has_visible_contact = true;
				echo $this->Html->link($person['alternate_email'], "mailto:{$person['alternate_email']}");
				echo ' (' . ($person['publish_alternate_email'] ? __('published', true) : __('private', true)) . ')';
				?>
			</dd>
		<?php endif; ?>
		<?php if (Configure::read('profile.home_phone') && !empty($person['home_phone']) &&
					($view_contact || ($is_logged_in && $person['publish_home_phone']))):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Phone (home)'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php
				$has_visible_contact = true;
				echo $person['home_phone'];
				echo ' (' . ($person['publish_home_phone'] ? __('published', true) : __('private', true)) . ')';
				?>
			</dd>
		<?php endif; ?>
		<?php if (Configure::read('profile.work_phone') && !empty($person['work_phone']) &&
					($view_contact || ($is_logged_in && $person['publish_work_phone']))):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Phone (work)'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php
				$has_visible_contact = true;
				echo $person['work_phone'];
				if (!empty($person['work_ext'])) {
					echo ' x' . $person['work_ext'];
				}
				echo ' (' . ($person['publish_work_phone'] ? __('published', true) : __('private', true)) . ')';
				?>

			</dd>
		<?php endif; ?>
		<?php if (Configure::read('profile.mobile_phone') && !empty($person['mobile_phone']) &&
					($view_contact || ($is_logged_in && $person['publish_mobile_phone']))):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Phone (mobile)'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php
				$has_visible_contact = true;
				echo $person['mobile_phone'];
				echo ' (' . ($person['publish_mobile_phone'] ? __('published', true) : __('private', true)) . ')';
				?>

			</dd>
		<?php endif; ?>
		<?php if ($this_is_parent): ?>
			<?php if (!empty($person['alternate_full_name'])): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Alternate Contact'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $person['alternate_full_name']; ?>
				&nbsp;
			</dd>
			<?php endif; ?>
			<?php if (Configure::read('profile.work_phone') && !empty($person['alternate_work_phone']) &&
					($view_contact || ($is_logged_in && $person['publish_alternate_work_phone']))):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Phone (work)'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php
				$has_visible_contact = true;
				echo $person['alternate_work_phone'];
				if (!empty($person['alternate_work_ext'])) {
					echo ' x' . $person['alternate_work_ext'];
				}
				echo ' (' . ($person['publish_alternate_work_phone'] ? __('published', true) : __('private', true)) . ')';
				?>

			</dd>
			<?php endif; ?>
			<?php if (Configure::read('profile.mobile_phone') && !empty($person['alternate_mobile_phone']) &&
					($view_contact || ($is_logged_in && $person['publish_alternate_mobile_phone']))):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Phone (mobile)'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php
				$has_visible_contact = true;
				echo $person['alternate_mobile_phone'];
				echo ' (' . ($person['publish_alternate_mobile_phone'] ? __('published', true) : __('private', true)) . ')';
				?>

			</dd>
			<?php endif; ?>
		<?php endif; ?>
		<?php if ($is_me || $is_admin || $is_manager):?>
			<?php
			$has_address = false;
			$label = __('Address', true);
			if (Configure::read('profile.addr_street') && !empty($person['addr_street'])):
			?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo $label; ?></dt>
			<dd<?php if ($i % 2 == 0) echo $class;?>>
				<?php
				echo $person['addr_street'];
				$has_address = true;
				$label = '&nbsp;';
				?>
			</dd>
			<?php endif; ?>
			<?php
			$addr = array();
			if (Configure::read('profile.addr_city') && !empty($person['addr_city'])) {
				$addr[] = $person['addr_city'];
			}
			if (Configure::read('profile.addr_prov') && !empty($person['addr_prov'])) {
				$addr[] = __($person['addr_prov'], true);
			}
			if (Configure::read('profile.addr_country') && !empty($person['addr_country'])) {
				$addr[] = __($person['addr_country'], true);
			}
			if (!empty($addr)):
			?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo $label; ?></dt>
			<dd<?php if ($i % 2 == 0) echo $class;?>><?php echo implode(', ', $addr); ?></dd>
			<?php
				$has_address = true;
				$label = '&nbsp;';
			endif;
			?>
			<?php if (Configure::read('profile.addr_postalcode') && !empty($person['addr_postalcode'])): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo $label; ?></dt>
			<dd<?php if ($i % 2 == 0) echo $class;?>>
				<?php echo $person['addr_postalcode']; ?>
			</dd>
			<?php
				$has_address = true;
			endif;
			?>
			<?php
			if ($has_address) {
				++ $i;
			}
			?>
		<?php endif; ?>
		<?php if ($this_is_player): ?>
			<?php if ($is_me || $is_admin || $is_manager):?>
				<?php if (Configure::read('profile.birthdate')): ?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Birthdate'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php
					if (Configure::read('feature.birth_year_only')) {
						$birth_year = substr($person['birthdate'], 0, 4);
						if (empty($person['birthdate']) || $birth_year == '0000') {
							__('unknown');
						} else {
							echo $birth_year;
						}
					} else {
						echo $this->ZuluruTime->date($person['birthdate']);
					}
					?>
				</dd>
				<?php endif; ?>
			<?php endif; ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Gender'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php __($person['gender']); ?>&nbsp;

			</dd>
			<?php if ($is_me || $is_admin || $is_manager || $is_coordinator || $is_captain):?>
				<?php if (Configure::read('profile.height') && !empty($person['height'])): ?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Height'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php echo $person['height'] . ' ' . (Configure::read('feature.units') == 'Metric' ? __('cm', true) : __('inches', true)); ?>

				</dd>
				<?php endif; ?>
				<?php if (Configure::read('profile.shirt_size') && !empty($person['shirt_size'])): ?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Shirt Size'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php __($person['shirt_size']); ?>

				</dd>
				<?php endif; ?>
			<?php endif; ?>
			<?php if (Configure::read('profile.skill_level') && !empty ($person['skill_level'])):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Skill Level'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php __(Configure::read("options.skill.{$person['skill_level']}")) ; ?>

			</dd>
			<?php endif; ?>
			<?php if ($is_logged_in && !empty ($person['year_started'])):?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Year Started'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php echo $person['year_started']; ?>

				</dd>
			<?php endif; ?>
		<?php endif; ?>
		<?php if ($is_me || $is_admin || $is_manager):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __n('Account Class', 'Account Classes', count($groups)); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php
				$names = array();
				foreach ($groups as $group) {
					$names[] = __($group['name'], true);
				}
				if (empty($names)) {
					__('None');
				} else {
					echo implode(', ', $names);
				}
				?>

			</dd>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Account Status'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php __($person['status']); ?>

			</dd>
			<?php if (Configure::read('feature.dog_questions')):?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Has Dog'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php $person['has_dog'] ? __('Yes') : __('No'); ?>

			</dd>
			<?php endif; ?>
			<?php if (Configure::read('profile.willing_to_volunteer')): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Willing To Volunteer'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php $person['willing_to_volunteer'] ? __('Yes') : __('No'); ?>

			</dd>
			<?php endif; ?>
			<?php if (Configure::read('profile.contact_for_feedback')): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Contact For Feedback'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php $person['contact_for_feedback'] ? __('Yes') : __('No'); ?>

			</dd>
			<?php endif; ?>
		<?php endif; ?>
		<?php if (!empty($note)): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Private Note'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $note['Note']['note']; ?>

			</dd>
		<?php endif; ?>
	</dl>
</div>
<div class="actions">
	<ul>
		<?php
		if ($is_logged_in && $has_visible_contact) {
			echo $this->Html->tag ('li', $this->Html->link(__('VCF', true), array('action' => 'vcf', 'person' => $person['id'])));
		}
		if ($is_logged_in && Configure::read('feature.annotations')) {
			if (!empty($note)) {
				echo $this->Html->tag ('li', $this->Html->link(__('Delete Note', true), array('action' => 'delete_note', 'person' => $person['id'])));
				$link = __('Edit Note', true);
			} else {
				$link = __('Add Note', true);
			}
			echo $this->Html->tag ('li', $this->Html->link($link, array('action' => 'note', 'person' => $person['id'])));
		}
		if ($is_me || $is_admin || $is_manager) {
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('edit_24.png', array('action' => 'edit', 'person' => $person['id'], 'return' => true), array('alt' => __('Edit Profile', true), 'title' => __('Edit Profile', true))));
			echo $this->Html->tag ('li', $this->Html->link(__('Edit Preferences', true), array('action' => 'preferences', 'person' => $person['id'])));
			if (!empty($person['user_id'])) {
				echo $this->Html->tag ('li', $this->Html->link(__('Change Password', true), array('controller' => 'users', 'action' => 'change_password', 'user' => $person['user_id'])));
			}
		}
		if ($is_admin || $is_manager) {
			echo $this->Html->tag ('li', $this->Html->link(__('Act As', true), array('controller' => 'people', 'action' => 'act_as', 'person' => $person['id'])));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('delete_24.png', array('action' => 'delete', 'person' => $person['id']), array('alt' => __('Delete Player', true), 'title' => __('Delete Player', true)), array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $person['id']))));
		}
		?>
	</ul>
</div>

<?php if ($view_contact && AppController::_isChild($person['birthdate']) && !empty($related_to)): ?>
<div class="related">
	<h3><?php __('Contacts');?></h3>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
	<?php
	foreach ($related_to as $relative):
		if ($relative['PeoplePerson']['approved']):
	?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Name'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $relative['Relative']['full_name']; ?>
			</dd>
			<?php if (!empty($relative['Relative']['email'])):?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Email Address'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php
					echo $this->Html->link($relative['Relative']['email'], "mailto:{$relative['Relative']['email']}");
					echo ' (' . ($relative['Relative']['publish_email'] ? __('published', true) : __('private', true)) . ')';
					?>
				</dd>
			<?php endif; ?>
			<?php if (!empty($relative['Relative']['alternate_email'])):?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Alternate Email Address'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php
					echo $this->Html->link($relative['Relative']['alternate_email'], "mailto:{$relative['Relative']['alternate_email']}");
					echo ' (' . ($relative['Relative']['publish_alternate_email'] ? __('published', true) : __('private', true)) . ')';
					?>
				</dd>
			<?php endif; ?>
			<?php if (Configure::read('profile.home_phone') && !empty($relative['Relative']['home_phone'])):?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Phone (home)'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php
					echo $relative['Relative']['home_phone'];
					echo ' (' . ($relative['Relative']['publish_home_phone'] ? __('published', true) : __('private', true)) . ')';
					?>
				</dd>
			<?php endif; ?>
			<?php if (Configure::read('profile.work_phone') && !empty($relative['Relative']['work_phone'])):?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Phone (work)'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php
					echo $relative['Relative']['work_phone'];
					if (!empty($relative['Relative']['work_ext'])) {
						echo ' x' . $relative['Relative']['work_ext'];
					}
					echo ' (' . ($relative['Relative']['publish_work_phone'] ? __('published', true) : __('private', true)) . ')';
					?>

				</dd>
			<?php endif; ?>
			<?php if (Configure::read('profile.mobile_phone') && !empty($relative['Relative']['mobile_phone'])):?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Phone (mobile)'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php
					echo $relative['Relative']['mobile_phone'];
					echo ' (' . ($relative['Relative']['publish_mobile_phone'] ? __('published', true) : __('private', true)) . ')';
					?>

				</dd>
			<?php
			endif;
		endif;
	endforeach;
	?>
	</dl>
</div>
<?php endif; ?>

<?php
$all_teams = $this->UserCache->read('AllTeamIDs', $person['id']);
if ($is_logged_in && ($this_is_player || !empty($all_teams))): ?>
<div class="related">
	<h3><?php __('Teams');?></h3>
	<?php if (!empty($teams)):?>
	<table class="list">
	<?php
		$i = 0;
		foreach ($teams as $team):
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
			<li><?php echo $this->Html->link(__('Show Team History', true), array('controller' => 'people', 'action' => 'teams', 'person' => $person['id'])); ?> </li>
		</ul>
	</div>
</div>
<?php endif; ?>

<?php if (($is_admin || $is_manager || $is_me) && (!empty($relatives) || !empty($related_to))):?>
<div class="related">
	<h3><?php __('Relatives');?></h3>
	<table class="list">
	<tr>
		<th><?php __('Relative');?></th>
		<th><?php __('Approved');?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		$you = ($is_me ? __('You', true) : $person['first_name']) . ' ' . __('can control', true);
		foreach ($relatives as $relative):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $you . ' ' . $this->element('people/block', array('person' => $relative['Relative'])); ?></td>
			<td><?php $relative['PeoplePerson']['approved'] ? __('Yes') : __('No'); ?></td>
			<td class="actions"><?php
				echo $this->ZuluruHtml->iconLink('view_24.png', array('controller' => 'people', 'action' => 'view', 'person' => $relative['Relative']['id']));
				echo $this->ZuluruHtml->iconLink('delete_24.png', array('controller' => 'people', 'action' => 'remove_relative', 'person' => $person['id'], 'relative' => $relative['Relative']['id']));
			?></td>
		</tr>
		<?php
		endforeach;

		$you = ($is_me ? __('you', true) : $person['first_name']);
		foreach ($related_to as $relative):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $this->element('people/block', array('person' => $relative['Relative'])) . ' ' . __('can control', true) . ' ' . $you; ?></td>
			<td><?php $relative['PeoplePerson']['approved'] ? __('Yes') : __('No'); ?></td>
			<td class="actions"><?php
				echo $this->ZuluruHtml->iconLink('view_24.png', array('controller' => 'people', 'action' => 'view', 'person' => $relative['Relative']['id']));
				// Only full users can remove relations
				if (!empty($person['user_id'])) {
					echo $this->ZuluruHtml->iconLink('delete_24.png', array('controller' => 'people', 'action' => 'remove_relative', 'person' => $person['id'], 'relative' => $relative['Relative']['id']));
				}
				if (!$relative['PeoplePerson']['approved']) {
					echo $this->ZuluruHtml->iconLink('approve_24.png', array('controller' => 'people', 'action' => 'approve_relative', 'person' => $person['id'], 'relative' => $relative['Relative']['id']));
				}
			?></td>
		</tr>
		<?php endforeach; ?>
	</table>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('Link a relative', true), array('controller' => 'people', 'action' => 'link_relative')); ?> </li>
		</ul>
	</div>
</div>
<?php endif; ?>

<?php if ($is_logged_in && Configure::read('feature.badges') && !empty($badges['Badge'])): ?>
<div class="related">
	<h3><?php __('Badges');?></h3>
	<p><?php
	foreach ($badges['Badge'] as $badge) {
		echo $this->ZuluruHtml->iconLink("{$badge['icon']}_64.png", array('controller' => 'badges', 'action' => 'view', 'badge' => $badge['id']),
			array('alt' => $badge['name'], 'title' => $badge['description']));
	}
	?></p>
</div>
<?php endif; ?>

<?php if (!empty($divisions)):?>
<div class="related">
	<h3><?php __('Divisions');?></h3>
	<table class="list">
	<?php
		$i = 0;
		foreach ($divisions as $division):
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

<?php if (Configure::read('scoring.allstars') && ($is_admin || $is_manager || $is_coordinator) && ($this_is_player || !empty($allstars))):?>
<div class="related">
	<h3><?php __('Allstar Nominations');?></h3>
	<?php if (!empty($allstars)):?>
	<table class="list">
	<tr>
		<th><?php __('Date'); ?></th>
		<th><?php __('Home Team'); ?></th>
		<th><?php __('Away Team'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($allstars as $allstar):
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
<?php if ($is_admin || $is_manager || ($is_me && !empty($preregistrations))):?>
<div class="related">
	<h3><?php __('Preregistrations');?></h3>
	<?php if (!empty($preregistrations)):?>
	<table class="list">
	<tr>
		<th><?php __('Event'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($preregistrations as $preregistration):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $this->Html->link($preregistration['Event']['name'], array('controller' => 'events', 'action' => 'view', 'event' => $preregistration['Event']['id']));?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('Delete', true), array('controller' => 'preregistrations', 'action' => 'delete', 'prereg' => $preregistration['Preregistration']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $preregistration['Preregistration']['id'])); ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
	<?php endif; ?>

	<?php if ($is_admin || $is_manager):?>
	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('Add Preregistration', true), array('controller' => 'preregistrations', 'action' => 'add', 'person' => $person['id']));?> </li>
		</ul>
	</div>
	<?php endif; ?>
</div>
<?php endif; ?>

<?php if (($is_admin || $is_manager || $is_me) && !empty($registrations)):?>
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
		foreach ($registrations as $registration):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $this->Html->link($registration['Event']['name'], array('controller' => 'events', 'action' => 'view', 'event' => $registration['Event']['id']));?></td>
			<td><?php echo $this->ZuluruTime->date($registration['Registration']['created']);?></td>
			<td><?php __($registration['Registration']['payment']);?></td>
			<td class="actions">
			<?php if ($is_admin || $is_manager): ?>
				<?php echo $this->Html->link(__('View', true), array('controller' => 'registrations', 'action' => 'view', 'registration' => $registration['Registration']['id']));?>
				<?php echo $this->Html->link(__('Edit', true), array('controller' => 'registrations', 'action' => 'edit', 'registration' => $registration['Registration']['id'], 'return' => true)); ?>
			<?php endif; ?>
			<?php if (in_array($registration['Registration']['payment'], Configure::read('registration_none_paid')) || $registration['Registration']['total_amount'] == 0): ?>
				<?php if (!$is_admin && !$is_manager): ?>
					<?php echo $this->Html->link(__('Edit', true), array('controller' => 'registrations', 'action' => 'edit', 'registration' => $registration['Registration']['id'], 'return' => true)); ?>
				<?php endif; ?>
				<?php echo $this->Html->link(__('Unregister', true), array('controller' => 'registrations', 'action' => 'unregister', 'registration' => $registration['Registration']['id'], 'return' => true), null, sprintf(__('Are you sure you want to delete # %s?', true), $registration['Registration']['id'])); ?>
			<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('Show Registration History', true), array('controller' => 'people', 'action' => 'registrations', 'person' => $person['id'])); ?> </li>
		</ul>
	</div>
</div>
<?php endif; ?>

<?php if (($is_admin || $is_manager || $is_me) && !empty($credits)):?>
	<div class="related">
	<h3><?php __('Available Credits');?></h3>
	<p>These credits can be applied to future registrations.</p>
	<table class="list">
	<tr>
		<th><?php __('Date'); ?></th>
		<th><?php __('Initial Amount'); ?></th>
		<th><?php __('Amount Used'); ?></th>
		<th><?php __('Notes'); ?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($credits as $credit):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $this->ZuluruTime->date($credit['Credit']['created']);?></td>
			<td><?php echo $credit['Credit']['amount'];?></td>
			<td><?php echo $credit['Credit']['amount_used'];?></td>
			<td><?php echo $credit['Credit']['notes'];?></td>
		</tr>
		<?php endforeach; ?>
	</table>
	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('Show Credit History', true), array('controller' => 'people', 'action' => 'credits', 'person' => $person['id'])); ?> </li>
		</ul>
	</div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php if ($is_admin || $is_manager || $is_me): ?>
<div class="related">
	<h3><?php __('Waivers');?></h3>
<?php if(!empty($waivers)): ?>
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
		foreach ($waivers as $waiver):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $waiver['Waiver']['name']; ?></td>
			<td><?php echo $this->ZuluruTime->fulldate($waiver['WaiversPerson']['created']); ?></td>
			<td><?php echo $this->ZuluruTime->fulldate($waiver['WaiversPerson']['valid_from']); ?></td>
			<td><?php echo $this->ZuluruTime->fulldate($waiver['WaiversPerson']['valid_until']); ?></td>
			<td class="actions"><?php echo $this->ZuluruHtml->iconLink('view_24.png', array('controller' => 'waivers', 'action' => 'review', 'waiver' => $waiver['Waiver']['id'], 'date' => $waiver['WaiversPerson']['valid_from'])); ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
<?php else: ?>
	<p>No current waiver is in effect.</p>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('Show Waiver History', true), array('controller' => 'people', 'action' => 'waivers', 'person' => $person['id'])); ?> </li>
		</ul>
	</div>
</div>
<?php endif; ?>

<?php if (Configure::read('feature.documents') && ($is_admin || $is_manager || $is_me)):?>
<div class="related">
	<h3><?php __('Documents');?></h3>
<?php if (!empty($documents)): ?>
	<table class="list">
	<tr>
		<th><?php __('Document'); ?></th>
		<th><?php __('Valid From'); ?></th>
		<th><?php __('Valid Until'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($documents as $document):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
			$rand = 'row_' . mt_rand();
		?>
		<tr<?php echo $class;?> id="<?php echo $rand; ?>">
			<td><?php echo $document['UploadType']['name'];?></td>
<?php if ($document['Upload']['approved']): ?>
			<td><?php echo $this->ZuluruTime->date($document['Upload']['valid_from']);?></td>
			<td><?php echo $this->ZuluruTime->date($document['Upload']['valid_until']);?></td>
<?php else: ?>
			<td colspan="2" class="highlight-message"><?php __('Unapproved');?></td>
<?php endif; ?>
			<td class="actions">
				<?php echo $this->Html->link(__('View', true), array('action' => 'document', 'id' => $document['Upload']['id']), array('target' => 'preview'));?>
<?php if ($is_admin || $is_manager):?>
<?php if ($document['Upload']['approved']): ?>
				<?php echo $this->Html->link(__('Edit', true), array('action' => 'edit_document', 'id' => $document['Upload']['id'], 'return' => true));?>
<?php else: ?>
				<?php echo $this->Html->link(__('Approve', true), array('action' => 'approve_document', 'id' => $document['Upload']['id'], 'return' => true));?>
<?php endif; ?>
<?php endif; ?>
				<?php echo $this->Js->link (__('Delete', true),
					array('action' => 'delete_document', 'id' => $document['Upload']['id'], 'row' => $rand),
					array('update' => "#temp_update", 'confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $document['Upload']['id']))); ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
<?php endif; ?>
	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('Upload New Document', true), array('action' => 'document_upload', 'person' => $person['id'])); ?> </li>
		</ul>
	</div>
</div>
<?php endif; ?>

<?php if (($is_admin || $is_manager || $is_me) && !empty($tasks)):?>
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
foreach ($tasks as $task):
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
		echo $this->ZuluruTime->day($task['TaskSlot']['task_date']) . ', ' .
					$this->ZuluruTime->time($task['TaskSlot']['task_start']) . '-' .
					$this->ZuluruTime->time($task['TaskSlot']['task_end'])
		?></td>
		<td class="splash_item"><?php
		echo $this->element('people/block', array('person' => $task['Task']));
		?></td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<?php if ($is_logged_in && !empty($teams)) echo $this->element('people/roster_div'); ?>
