<?php
// There may be nothing to output, in which case we don't even want the wrapper div
if (!$is_admin && !$is_manager && !($empty && $is_player)) {
	return;
}
$act_as = ($id == $my_id ? null : $id);
?>
<div id="kick_start">
<?php
if ($is_admin) {
	if (Configure::read('feature.affiliates')) {
		if (empty($affiliates)) {
			echo $this->Html->para('warning-message', __('You have enabled the affiliate option, but have not yet created any affiliates. ', true) .
				$this->Html->link(__('Create one now!', true), array('controller' => 'affiliates', 'action' => 'add', 'return' => true)));
		} else {
			$unmanaged = $this->requestAction(array('controller' => 'affiliates', 'action' => 'unmanaged'));
			if (!empty($unmanaged)):
?>
<p class="warning-message">The following affiliates do not yet have managers assigned to them:</p>
<table class="list">
<tr>
	<th><?php __('Affiliate'); ?></th>
	<th><?php __('Actions'); ?></th>
</tr>
<?php
				$i = 0;
				foreach ($unmanaged as $affiliate):
					$class = null;
					if ($i++ % 2 == 0) {
						$class = ' class="altrow"';
					}
?>
	<tr<?php echo $class;?>>
		<td class="splash_item"><?php echo $affiliate['Affiliate']['name']; ?></td>
		<td class="actions">
			<?php
					echo $this->ZuluruHtml->iconLink('edit_24.png',
						array('controller' => 'affiliates', 'action' => 'edit', 'affiliate' => $affiliate['Affiliate']['id'], 'return' => true),
						array('alt' => __('Edit', true), 'title' => __('Edit', true)));
					echo $this->ZuluruHtml->iconLink('coordinator_add_24.png',
						array('controller' => 'affiliates', 'action' => 'add_manager', 'affiliate' => $affiliate['Affiliate']['id'], 'return' => true),
						array('alt' => __('Add Manager', true), 'title' => __('Add Manager', true)));
					echo $this->ZuluruHtml->iconLink('delete_24.png',
						array('controller' => 'affiliates', 'action' => 'delete', 'affiliate' => $affiliate['Affiliate']['id'], 'return' => true),
						array('alt' => __('Delete', true), 'title' => __('Delete', true)),
						array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $affiliate['Affiliate']['id'])));
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
<?php
			endif;
		}
	}
}

if ($is_manager) {
	$my_affiliates = $this->UserCache->read('ManagedAffiliates');
	if ($is_admin || !empty($my_affiliates)) {
		$facilities = $this->requestAction(array('controller' => 'facilities', 'action' => 'index'));
		$facilities = Set::extract('/Facility[id>0]', $facilities);
		if (empty($facilities)) {
			echo $this->Html->para('warning-message', __('You have no open facilities. ', true) .
				$this->Html->link(__('Create one now!', true), array('controller' => 'facilities', 'action' => 'add', 'return' => true)));
		} else {
			// Eliminate any open facilities that have fields, and check if there's anything left that we need to warn about
			foreach ($facilities as $key => $facility) {
				if (!empty($facility['Facility']['Field'])) {
					unset($facilities[$key]);
				}
			}
			if (!empty($facilities)):
?>
<p class="warning-message">The following facilities are open but do not have any open <?php echo Configure::read('ui.fields'); ?>:</p>
<table class="list">
<tr>
	<th><?php __('Facility'); ?></th>
	<th><?php __('Actions'); ?></th>
</tr>
<?php
				$i = 0;
				foreach ($facilities as $facility):
					$class = null;
					if ($i++ % 2 == 0) {
						$class = ' class="altrow"';
					}
?>
	<tr<?php echo $class;?>>
		<td class="splash_item"><?php echo $facility['Facility']['name']; ?></td>
		<td class="actions">
			<?php
					echo $this->ZuluruHtml->iconLink('view_24.png',
						array('controller' => 'facilities', 'action' => 'view', 'facility' => $facility['Facility']['id']),
						array('alt' => __('View', true), 'title' => __('View Facility', true)));
					echo $this->ZuluruHtml->iconLink('edit_24.png',
						array('controller' => 'facilities', 'action' => 'edit', 'facility' => $facility['Facility']['id'], 'return' => true),
						array('alt' => __('Edit', true), 'title' => __('Edit Facility', true)));
					echo $this->ZuluruHtml->iconLink('add_24.png',
						array('controller' => 'fields', 'action' => 'add', 'facility' => $facility['Facility']['id'], 'return' => true),
						array('alt' => sprintf(__('Add %s', true), __(Configure::read('ui.field'), true)), 'title' => sprintf(__('Add %s', true), __(Configure::read('ui.field'), true))));
					echo $this->ZuluruHtml->iconLink('delete_24.png',
						array('controller' => 'facilities', 'action' => 'delete', 'facility' => $facility['Facility']['id'], 'return' => true),
						array('alt' => __('Delete', true), 'title' => __('Delete Facility', true)),
						array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $facility['Facility']['id'])));
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
<?php
			endif;
		}

		$leagues = $this->requestAction(array('controller' => 'leagues', 'action' => 'index'));
		if (empty($leagues)) {
			echo $this->Html->para('warning-message', __('You have no current or upcoming leagues. ', true) .
				$this->Html->link(__('Create one now!', true), array('controller' => 'leagues', 'action' => 'add', 'return' => true)));
		} else {
			// Eliminate any open leagues that have divisions, and check if there's anything left that we need to warn about
			foreach ($leagues as $key => $league) {
				if (!empty($league['Division'])) {
					unset($leagues[$key]);
				}
			}
			if (!empty($leagues)):
?>
<p class="warning-message">The following leagues do not yet have divisions:</p>
<table class="list">
<tr>
	<th><?php __('League'); ?></th>
	<th><?php __('Actions'); ?></th>
</tr>
<?php
				$i = 0;
				foreach ($leagues as $league):
					$class = null;
					if ($i++ % 2 == 0) {
						$class = ' class="altrow"';
					}
?>
	<tr<?php echo $class;?>>
		<td class="splash_item"><?php echo $league['League']['full_name']; ?></td>
		<td class="actions"><?php echo $this->element('leagues/actions', array('league' => $league, 'return' => true)); ?></td>
	</tr>
<?php endforeach; ?>
</table>
<?php
			endif;
		}

		$events = $this->requestAction(array('controller' => 'events', 'action' => 'index'));
		if (empty($events)) {
			echo $this->Html->para('warning-message', __('You have no current or upcoming registration events. ', true) .
				$this->Html->link(__('Create one now!', true), array('controller' => 'events', 'action' => 'add', 'return' => true)));
		}
	}
} else if ($empty && $is_player) {
	// If the user has nothing going on, pull some more details to allow us to help them get started
	$membership_events = $this->requestAction(array('controller' => 'events', 'action' => 'count'), array('pass' => array(true)));
	$non_membership_events = $this->requestAction(array('controller' => 'events', 'action' => 'count'));
	$open_teams = $this->requestAction(array('controller' => 'teams', 'action' => 'open_count'));
	$leagues = $this->requestAction(array('controller' => 'leagues', 'action' => 'index'));

?>
<h3><?php __('You are not yet on any teams.'); ?></h3>
<?php
	$options = array();
	if ($membership_events) {
		$options[] = __('membership', true);
	}
	if ($non_membership_events) {
		$options[] = __('an event', true);
	}

	$actions = array();
	if (!empty($options)) {
		$actions[] = $this->Html->link (__('Register for', true) . ' ' . implode(' ' . __('or', true) . ' ', $options), array('controller' => 'events', 'action' => 'wizard', 'act_as' => $act_as));
	}

	if ($open_teams) {
		$actions[] = $this->Html->link ('Join an existing team', array('controller' => 'teams', 'action' => 'join', 'act_as' => $act_as));
	}

	if (!empty($leagues)) {
		$actions[] = $this->Html->link ('Check out the leagues we are currently offering', array('controller' => 'leagues'));
	}

	if (!empty($actions)) {
		echo $this->Html->tag('div', $this->Html->nestedList($actions), array('class' => 'actions'));
	}
}
?>
</div>
