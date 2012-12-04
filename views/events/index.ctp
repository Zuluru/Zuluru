<?php
$this->Html->addCrumb (__('Registration Events', true));
$this->Html->addCrumb (__('List', true));
?>

<?php
// Perhaps remove manager status, if we're looking at a different affiliate
if ($is_manager) {
	$affiliates = array_unique(Set::extract('/Event/affiliate_id', $events));
	$mine = array_intersect($affiliates, $this->Session->read('Zuluru.ManagedAffiliateIDs'));
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
?>

<table class="list">
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
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
		echo "<tr$class><td colspan='5'><h4>{$event['EventType']['name']}</h4></td></tr>";
		$last_name = $event['EventType']['name'];
	}
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $this->Html->link(__($event['Event']['name'], true), array('action' => 'view', 'event' => $event['Event']['id'])); ?>
		</td>
		<td>
			<?php
			$cost = $event['Event']['cost'] + $event['Event']['tax1'] + $event['Event']['tax2'];
			if ($cost > 0) {
				echo '$' . $cost;
			} else {
				echo $this->Html->tag ('span', 'FREE', array('class' => 'free'));
			}
			?>
		</td>
		<td>
			<?php echo $this->ZuluruTime->datetime($event['Event']['open']); ?>
		</td>
		<td>
			<?php echo $this->ZuluruTime->datetime($event['Event']['close']); ?>
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
				$alt = sprintf(__('Manage %s', true), __('Connections', true));
				echo $this->ZuluruHtml->iconLink('connections_24.png',
					array('action' => 'connections', 'event' => $event['Event']['id']),
					array('alt' => $alt, 'title' => $alt));
				echo $this->ZuluruHtml->iconLink('delete_24.png',
					array('action' => 'delete', 'event' => $event['Event']['id']),
					array('alt' => __('Delete', true), 'title' => __('Delete', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $event['Event']['id'])));
				echo $this->Html->link(__('Waiting List', true),
					array('controller' => 'registrations', 'action' => 'waiting', 'event' => $event['Event']['id']));
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
<?php endforeach; ?>
</table>
<?php endif; ?>
</div>
