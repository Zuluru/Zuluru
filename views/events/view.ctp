<?php
$this->Html->addCrumb (__('Registration', true));
$this->Html->addCrumb ($event['Event']['name']);
$this->Html->addCrumb (__('View', true));
?>

<?php
// Perhaps remove manager status, if we're looking at a different affiliate
if ($is_manager && !in_array($event['Event']['affiliate_id'], $this->UserCache->read('ManagedAffiliateIDs'))) {
	$is_manager = false;
}

$deposit = Set::extract('/Price[allow_deposit=1]', $event);
$deposit = !empty($deposit);
$admin_register = false;
?>

<div class="events view">
	<h2><?php echo $event['Event']['name'];?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $event['Event']['name']; ?>

		</dd>
		<?php if (count($affiliates) > 1): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Affiliate'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($event['Affiliate']['name'], array('controller' => 'affiliates', 'action' => 'view', 'affiliate' => $event['Affiliate']['id'])); ?>

		</dd>
		<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Description'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $event['Event']['description']; ?>
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Event Type'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($event['EventType']['name']); ?>

		</dd>
<?php if (!empty ($event['Event']['level_of_play'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Level of Play'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $event['Event']['level_of_play']; ?>

		</dd>
<?php endif; ?>

<?php if (!empty($event['Event']['division_id'])): ?>
<?php if (count($facilities) > 0 && count($facilities) < 6):
		$facility_links = array();
		foreach ($facilities as $facility_id => $facility_name) {
			$facility_links[] = $this->Html->link ($facility_name, array('controller' => 'facilities', 'action' => 'view', 'facility' => $facility_id));
		}
?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __n('Location', 'Locations', count($facilities)); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo implode (', ', $facility_links); ?>

		</dd>
<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('First Game'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->Date ($event['Division']['open']); ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Last Game'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->Date ($event['Division']['close']); ?>

		</dd>
		<?php if (!empty ($event['Division']['Day'])): ?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __n('Day', 'Days', count($event['Division']['Day'])); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php
				$days = array();
				foreach ($event['Division']['Day'] as $day) {
					$days[] = __($day['name'], true);
				}
				echo implode (', ', $days);
				?>

			</dd>
		<?php endif; ?>
<?php if (!empty($times) && count($times) < 5):
		$time_list = array();
		foreach ($times as $start => $end) {
			$time_list[] = $this->ZuluruTime->Time ($start) . '-' . $this->ZuluruTime->Time ($end);
		}
?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __n('Game Time', 'Game Times', count($times)); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo implode (', ', $time_list); ?>

		</dd>
<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Gender Ratio'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __(Inflector::Humanize ($event['Division']['ratio'])); ?>

		</dd>
<?php endif; ?>

<?php if (!empty ($event['Event']['membership_begins'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Membership Begins'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->date($event['Event']['membership_begins']); ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Membership Ends'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->date($event['Event']['membership_ends']); ?>

		</dd>
<?php endif; ?>

<?php if ($event['Event']['cap_female'] == -2): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Registration Cap'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $event['Event']['cap_male']; ?>

		</dd>
<?php else: ?>
<?php if ($event['Event']['cap_male'] > 0): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Male Cap'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $event['Event']['cap_male']; ?>

		</dd>
<?php endif; ?>
<?php if ($event['Event']['cap_female'] > 0): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Female Cap'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $event['Event']['cap_female']; ?>

		</dd>
<?php endif; ?>
<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Multiples'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php $event['Event']['multiple'] ? __('Allowed') : __('Not allowed'); ?>

		</dd>

<?php if (count($event['Price']) == 1):
			if (!empty($price_allowed[$event['Price'][0]['id']]['allowed']) && strtotime($event['Price'][0]['open']) > time() && ($is_admin || $is_manager)) {
				$admin_register = true;
			}
?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Registration Opens'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->DateTime ($event['Price'][0]['open']); ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Registration  Closes'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->DateTime ($event['Price'][0]['close']); ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Reservations'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo ($event['Price'][0]['allow_reservations'] ? Price::duration($event['Price'][0]['reservation_duration']) : __('No', true)); ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Cost'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			$cost = $event['Price'][0]['cost'] + $event['Price'][0]['tax1'] + $event['Price'][0]['tax2'];
			if ($cost > 0) {
				echo '$' . $cost;
			} else {
				echo $this->Html->tag ('span', 'FREE', array('class' => 'free'));
			}
			?>

		</dd>
		<?php if ($deposit): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Deposit'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			echo '$' . $event['Price'][0]['minimum_deposit'];
			if (!$event['Price'][0]['fixed_deposit']) {
				echo '+';
			}
			?>

		</dd>
		<?php endif; ?>

<?php endif; ?>
	</dl>

<?php if (count($event['Price']) > 1): ?>
	<div class="related">
	<h3><?php __('Registration Options');?></h3>
	<table class="multi_row_list">
	<tr>
		<th><?php __('Option'); ?></th>
		<th><?php __('Registration Opens'); ?></th>
		<th><?php __('Registration Closes'); ?></th>
		<th><?php __('Reservations?'); ?></th>
		<th><?php __('Cost'); ?></th>
		<?php if ($deposit): ?>
		<th><?php __('Deposit'); ?></th>
		<?php endif; ?>
		<th><?php __('Actions'); ?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($event['Price'] as $price):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
	<tr<?php echo $class;?>>
		<td><?php echo $price['name'];?></td>
		<td><?php echo $this->ZuluruTime->DateTime ($price['open']); ?></td>
		<td><?php echo $this->ZuluruTime->DateTime ($price['close']); ?></td>
		<td><?php echo ($price['allow_reservations'] ? Price::duration($price['reservation_duration']) : __('No', true)); ?></td>
		<td><?php
		$cost = $price['cost'] + $price['tax1'] + $price['tax2'];
		if ($cost > 0) {
			echo '$' . $cost;
		} else {
			echo $this->Html->tag ('span', 'FREE', array('class' => 'free'));
		}
		?></td>
		<?php if ($deposit): ?>
		<td><?php
		if ($price['allow_deposit']) {
			echo '$' . $price['minimum_deposit'];
			if (!$price['fixed_deposit']) {
				echo '+';
			}
		} else {
			__('N/A');
		}
		?></td>
		<?php endif; ?>
		<td class="actions"><?php
		if (!empty($price_allowed[$price['id']]['allowed'])) {
			echo $this->Html->link(__('Register now!', true),
					array('controller' => 'registrations', 'action' => 'register', 'event' => $id, 'option' => $price['id']),
					array('title' => __('Register for ', true) . $event['Event']['name'] . ' ' . $price['name'])
			);
			if (strtotime($price['open']) > time() && ($is_admin || $is_manager)) {
				$admin_register = true;
			}
		}
		if ($is_admin || $is_manager) {
			echo $this->ZuluruHtml->iconLink('edit_24.png',
					array('controller' => 'prices', 'action' => 'edit', 'price' => $price['id']),
					array('alt' => __('Edit', true), 'title' => __('Edit', true)));
			echo $this->ZuluruHtml->iconLink('delete_24.png',
					array('controller' => 'prices', 'action' => 'delete', 'price' => $price['id']),
					array('alt' => __('Delete', true), 'title' => __('Delete', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $price['id'])));
		}
		?></td>
	</tr>
	<?php if (!empty($price['description'])): ?>
	<tr<?php echo $class;?>>
		<td colspan="<?php echo 6 + $deposit; ?>"><?php echo $price['description']; ?></td>
	</tr>
	<?php endif; ?>
	<?php if (isset($price_allowed) && !empty($price_allowed[$price['id']]['messages'])): ?>
	<tr<?php echo $class;?>>
		<td colspan="<?php echo 6 + $deposit; ?>"><?php echo $price_allowed[$price['id']]['messages']; ?></td>
	</tr>
	<?php endif; ?>
	<?php endforeach; ?>
	</table>
</div>
<?php endif; ?>

<?php
if (!$is_logged_in):
	echo $this->element('events/not_logged_in');
else:
	echo $this->element('messages');
	if ($allowed) {
		echo $this->Html->tag ('h2', $this->Html->link(__('Register now!', true),
				array('controller' => 'registrations', 'action' => 'register', 'event' => $id),
				array('title' => __('Register for ', true) . $event['Event']['name'], 'style' => 'text-decoration: underline;')
		));
	}
	if ($admin_register) {
		echo $this->Html->para('warning-message', __('Note that you have been given the option to register before the specified opening date due to your status as system administrator.', true));
	}
?>

</div>

<?php if (!empty($event['Division']['Event']) || !empty($event['Alternate'])): ?>
<div class="related">
	<h3><?php __('You might alternately be interested in the following registrations:');?></h3>
	<table class="list">
	<tr>
		<th><?php __('Registration'); ?></th>
		<th><?php __('Type');?></th>
	</tr>
	<?php
		$i = 0;
		if (!empty($event['Division']['Event'])) :
			foreach ($event['Division']['Event'] as $related):
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
	<?php endif; ?>
	<?php
		foreach ($event['Alternate'] as $related):
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

<div class="actions">
	<ul>
	<?php
		if (!empty($event['Event']['division_id'])) {
		echo $this->Html->tag ('li', $this->element('divisions/block', array('division' => $event['Division'], 'link_text' => sprintf(__('View %s', true), __('Division', true)))));
		}
		if ($is_admin || $is_manager) {
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('edit_32.png',
				array('action' => 'edit', 'event' => $event['Event']['id'], 'return' => true),
				array('alt' => __('Edit', true), 'title' => __('Edit', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('add_32.png',
				array('controller' => 'prices', 'action' => 'add', 'event' => $event['Event']['id'], 'return' => true),
				array('alt' => __('Add price', true), 'title' => __('Add a new price point', true))));
			$alt = sprintf(__('Manage %s', true), __('Connections', true));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('connections_32.png',
				array('action' => 'connections', 'event' => $event['Event']['id']),
				array('alt' => $alt, 'title' => $alt)));
			echo $this->Html->tag ('li', $this->Html->link(sprintf(__('Edit %s', true), __('Questionnaire', true)),
				array('controller' => 'questionnaires', 'action' => 'edit', 'questionnaire' => $event['Event']['questionnaire_id'], 'return' => true)));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('delete_32.png',
				array('action' => 'delete', 'event' => $event['Event']['id']),
				array('alt' => __('Delete', true), 'title' => __('Delete', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $event['Event']['id']))));
			if (Configure::read('feature.waiting_list')) {
				echo $this->Html->tag ('li', $this->Html->link(__('Waiting List', true),
					array('controller' => 'registrations', 'action' => 'waiting', 'event' => $event['Event']['id'])));
			}
			$alt = sprintf(__('%s Summary', true), __('Registration', true));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('summary_32.png',
				array('controller' => 'registrations', 'action' => 'summary', 'event' => $event['Event']['id']),
				array('alt' => $alt, 'title' => $alt)));
			echo $this->Html->tag ('li', $this->Html->link(sprintf(__('Detailed %s List', true), __('Registration', true)),
				array('controller' => 'registrations', 'action' => 'full_list', 'event' => $event['Event']['id'])));
			echo $this->Html->tag ('li', $this->Html->link(sprintf(__('Download %s List', true), __('Registration', true)),
				array('controller' => 'registrations', 'action' => 'full_list', 'event' => $event['Event']['id'], 'ext' => 'csv')));
			$alt = sprintf(__('Add %s', true), __('Preregistration', true));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('preregistration_add_32.png',
				array('controller' => 'preregistrations', 'action' => 'add', 'event' => $event['Event']['id']),
				array('alt' => $alt, 'title' => $alt)));
			echo $this->Html->tag ('li', $this->Html->link(sprintf(__('List %s', true), __('Preregistrations', true)), array('controller' => 'preregistrations', 'action' => 'index', 'event' => $event['Event']['id'])));
		}
	?>
	</ul>
</div>

<?php if (!empty($event['Preregistration']) && ($is_admin || $is_manager)):?>
<div class="related">
	<h3><?php printf(__('Related %s', true), __('Preregistrations', true));?></h3>
	<table class="list">
	<tr>
		<th><?php __('Person Id'); ?></th>
		<th><?php __('Event Id'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($event['Preregistration'] as $preregistration):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $preregistration['person_id'];?></td>
			<td><?php echo $preregistration['event_id'];?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('Delete', true), array('controller' => 'preregistrations', 'action' => 'delete', 'prereg' => $preregistration['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $preregistration['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
</div>
<?php endif; ?>

<?php endif; ?>
