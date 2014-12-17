<?php
$this->Html->addCrumb (__('Registration Events', true));
$this->Html->addCrumb (__('List', true));
?>

<?php
// Perhaps remove manager status, if we're looking at a different affiliate
if ($is_manager) {
	$affiliates = array_unique(Set::extract('/Event/affiliate_id', $events));
	$mine = array_intersect($affiliates, $this->UserCache->read('ManagedAffiliateIDs'));
	if (empty($mine)) {
		$is_manager = false;
	}
} else {
	$mine = array();
}
?>

<div class="events index">
<h2><?php __('Registration Events List');?></h2>
<?php if (empty($events)): ?>
<p class="warning-message">There are no events currently available for registration. Please check back periodically for updates.</p>
<?php else: ?>
<?php
echo $this->element('registrations/notice');
if (!$is_logged_in) {
	echo $this->element('events/not_logged_in');
}

$seasons = array_unique(Set::extract('/Division/League/season', $events));
echo $this->element('selector', array('title' => 'Season', 'options' => array_intersect(array_keys(Configure::read('options.season')), $seasons)));

$days = Set::extract('/Division/Day[id!=]', $events);
$days = Set::combine($days, '{n}.Day.id', '{n}.Day.name');
ksort($days);
echo $this->element('selector', array('title' => 'Day', 'options' => $days));

$play_types = array('team', 'individual');
?>

<table class="multi_row_list">
<tr>
	<th><?php __('Registration'); ?></th>
	<th><?php __('Cost'); ?></th>
	<th><?php __('Opens on'); ?></th>
	<th><?php __('Closes on'); ?></th>
	<th><?php __('Actions'); ?></th>
</tr>
<?php
$i = 0;
$now = date('Y-m-d H:i:s');

$last_name = $affiliate_id = null;
$cols = 4 + (!$is_admin);
foreach ($events as $event):
	if (count($affiliates) > 1 && $event['Event']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $event['Event']['affiliate_id'];
		$is_manager = in_array($affiliate_id, $mine);
?>
<tr>
	<th colspan="<?php echo $cols; ?>">
		<h3 class="affiliate"><?php echo $event['Affiliate']['name']; ?></h3>
	</th>
	<?php if ($is_admin): ?>
	<th class="actions">
		<?php
			echo $this->ZuluruHtml->iconLink('edit_24.png',
				array('controller' => 'affiliates', 'action' => 'edit', 'affiliate' => $event['Event']['affiliate_id'], 'return' => true),
				array('alt' => __('Edit', true), 'title' => __('Edit Affiliate', true)));
		?>
	</th>
	<?php endif; ?>
</tr>
<?php
	endif;

	if ($event['Event']['close'] < $now && !($is_admin || $is_manager)) {
		continue;
	}
	if ($event['EventType']['name'] != $last_name) {
		$classes = array();
		$i = 0;
		if (in_array($event['EventType']['type'], $play_types)) {
			$divisions = Set::extract("/Event[event_type_id={$event['Event']['event_type_id']}]/../Division", $events);

			$seasons = array_unique(Set::extract('/Division/League/season', $divisions));
			$classes[] = $this->element('selector_classes', array('title' => 'Season', 'options' => $seasons));

			$days = Set::extract('/Division/Day[id!=]', $divisions);
			$days = Set::combine($days, '{n}.Day.id', '{n}.Day.name');
			ksort($days);
			$classes[] = $this->element('selector_classes', array('title' => 'Day', 'options' => $days));
		}
		if (!empty($classes)) {
			$class = ' class="' . implode(' ', $classes) . '"';
		} else {
			$class = '';
		}
		echo "<tr><th colspan='5'><h4>{$event['EventType']['name']}</h4></th></tr>";
		$last_name = $event['EventType']['name'];
	}
	$classes = array();
	if ($i++ % 2 == 0) {
		$classes[] = 'altrow';
	}
	if (in_array($event['EventType']['type'], $play_types)) {
		if (!empty($event['Division']['id'])) {
			$classes[] = $this->element('selector_classes', array('title' => 'Season', 'options' => $event['Division']['League']['season']));
			$days = Set::combine($event, 'Division.Day.{n}.id', 'Division.Day.{n}.name');
			ksort($days);
			$classes[] = $this->element('selector_classes', array('title' => 'Day', 'options' => $days));
		} else {
			$classes[] = $this->element('selector_classes', array('title' => 'Season', 'options' => array()));
			$classes[] = $this->element('selector_classes', array('title' => 'Day', 'options' => array()));
		}
	}
	if (!empty($classes)) {
		$class = ' class="' . implode(' ', $classes) . '"';
	} else {
		$class = '';
	}

	if (count($event['Price']) == 1):
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $this->Html->link($event['Event']['name'], array('action' => 'view', 'event' => $event['Event']['id'])); ?>
		</td>
		<td>
			<?php
			$cost = $event['Price'][0]['cost'] + $event['Price'][0]['tax1'] + $event['Price'][0]['tax2'];
			if ($cost > 0) {
				echo '$' . $cost;
			} else {
				echo $this->Html->tag ('span', 'FREE', array('class' => 'free'));
			}
			?>
		</td>
		<td>
			<?php echo $this->ZuluruTime->datetime($event['Price'][0]['open']); ?>
		</td>
		<td>
			<?php echo $this->ZuluruTime->datetime($event['Price'][0]['close']); ?>
		</td>
		<td class="actions">
			<?php
			echo $this->ZuluruHtml->iconLink('view_24.png',
				array('action' => 'view', 'event' => $event['Event']['id']),
				array('alt' => __('View', true), 'title' => __('View', true)));
			if (Configure::read('registration.register_now')) {
				echo $this->Html->link(__('Register Now', true), array('controller' => 'registrations', 'action' => 'register', 'event' => $event['Event']['id']));
			}
			if ($is_admin || $is_manager) {
				echo $this->ZuluruHtml->iconLink('edit_24.png',
					array('action' => 'edit', 'event' => $event['Event']['id']),
					array('alt' => __('Edit', true), 'title' => __('Edit', true)));
				echo $this->ZuluruHtml->iconLink('add_24.png',
					array('controller' => 'prices', 'action' => 'add', 'event' => $event['Event']['id'], 'return' => true),
					array('alt' => __('Add price', true), 'title' => __('Add a new price point', true)));
				$alt = sprintf(__('Manage %s', true), __('Connections', true));
				echo $this->ZuluruHtml->iconLink('connections_24.png',
					array('action' => 'connections', 'event' => $event['Event']['id']),
					array('alt' => $alt, 'title' => $alt));
				echo $this->ZuluruHtml->iconLink('delete_24.png',
					array('action' => 'delete', 'event' => $event['Event']['id']),
					array('alt' => __('Delete', true), 'title' => __('Delete', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $event['Event']['id'])));
				if (Configure::read('feature.waiting_list')) {
					echo $this->Html->link(__('Waiting List', true),
						array('controller' => 'registrations', 'action' => 'waiting', 'event' => $event['Event']['id']));
				}
				echo $this->ZuluruHtml->iconLink('summary_24.png',
					array('controller' => 'registrations', 'action' => 'summary', 'event' => $event['Event']['id']),
					array('alt' => __('Summary', true), 'title' => __('Summary', true)));
				$alt = sprintf(__('Add %s', true), __('Preregistration', true));
				echo $this->ZuluruHtml->iconLink('preregistration_add_24.png',
					array('controller' => 'preregistrations', 'action' => 'add', 'event' => $event['Event']['id']),
					array('alt' => $alt, 'title' => $alt));
			}
			?>
		</td>
	</tr>
	<?php else: ?>
	<tr<?php echo $class;?>>
		<td colspan="4">
			<h4><?php echo $this->Html->link($event['Event']['name'], array('action' => 'view', 'event' => $event['Event']['id'])); ?></h4>
		</td>
		<td class="actions">
			<?php
			echo $this->ZuluruHtml->iconLink('view_24.png',
				array('action' => 'view', 'event' => $event['Event']['id']),
				array('alt' => __('View', true), 'title' => __('View', true)));
			if (Configure::read('registration.register_now')) {
				echo $this->Html->link(__('Register Now', true), array('controller' => 'registrations', 'action' => 'register', 'event' => $event['Event']['id']));
			}
			if ($is_admin || $is_manager) {
				echo $this->ZuluruHtml->iconLink('edit_24.png',
					array('action' => 'edit', 'event' => $event['Event']['id']),
					array('alt' => __('Edit', true), 'title' => __('Edit', true)));
				echo $this->ZuluruHtml->iconLink('add_24.png',
					array('controller' => 'prices', 'action' => 'add', 'event' => $event['Event']['id'], 'return' => true),
					array('alt' => __('Add price', true), 'title' => __('Add a new price point', true)));
				$alt = sprintf(__('Manage %s', true), __('Connections', true));
				echo $this->ZuluruHtml->iconLink('connections_24.png',
					array('action' => 'connections', 'event' => $event['Event']['id']),
					array('alt' => $alt, 'title' => $alt));
				echo $this->ZuluruHtml->iconLink('delete_24.png',
					array('action' => 'delete', 'event' => $event['Event']['id']),
					array('alt' => __('Delete', true), 'title' => __('Delete', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $event['Event']['id'])));
				if (Configure::read('feature.waiting_list')) {
					echo $this->Html->link(__('Waiting List', true),
						array('controller' => 'registrations', 'action' => 'waiting', 'event' => $event['Event']['id']));
				}
				echo $this->ZuluruHtml->iconLink('summary_24.png',
					array('controller' => 'registrations', 'action' => 'summary', 'event' => $event['Event']['id']),
					array('alt' => __('Summary', true), 'title' => __('Summary', true)));
				$alt = sprintf(__('Add %s', true), __('Preregistration', true));
				echo $this->ZuluruHtml->iconLink('preregistration_add_24.png',
					array('controller' => 'preregistrations', 'action' => 'add', 'event' => $event['Event']['id']),
					array('alt' => $alt, 'title' => $alt));
			}
			?>
		</td>
	</tr>
		<?php
		foreach ($event['Price'] as $price):
		?>
	<tr<?php echo $class;?>>
		<td>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $this->Html->link($price['name'], array('action' => 'view', 'event' => $event['Event']['id'])); ?>
		</td>
		<td>
			<?php
			$cost = $price['cost'] + $price['tax1'] + $price['tax2'];
			if ($cost > 0) {
				echo '$' . $cost;
			} else {
				echo $this->Html->tag ('span', 'FREE', array('class' => 'free'));
			}
			?>
		</td>
		<td>
			<?php echo $this->ZuluruTime->datetime($price['open']); ?>
		</td>
		<td>
			<?php echo $this->ZuluruTime->datetime($price['close']); ?>
		</td>
		<td class="actions">
			<?php
			echo $this->ZuluruHtml->iconLink('view_24.png',
				array('action' => 'view', 'event' => $event['Event']['id']),
				array('alt' => __('View', true), 'title' => __('View', true)));
			if (Configure::read('registration.register_now')) {
				echo $this->Html->link(__('Register Now', true), array('controller' => 'registrations', 'action' => 'register', 'event' => $event['Event']['id'], 'price' => $price['id']));
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
			?>
		</td>
	</tr>
		<?php endforeach; ?>
	<?php endif; ?>
<?php endforeach; ?>
</table>
<?php endif; ?>
</div>
<?php echo $this->element('people/confirmation', array('fields' => array('skill_level', 'height', 'shirt_size', 'year_started'))); ?>