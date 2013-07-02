<?php
$this->Html->addCrumb (__('Badges', true));
$this->Html->addCrumb ($badge['Badge']['name']);
$this->Html->addCrumb (__('View', true));
?>

<?php
// Perhaps remove manager status, if we're looking at a different affiliate
if ($is_manager && !in_array($badge['Badge']['affiliate_id'], $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
	$is_manager = false;
}
?>

<div class="badges view">
<h2><?php echo $this->ZuluruHtml->icon($badge['Badge']['icon'] . '_64.png') . ' ' . $badge['Badge']['name'];?></h2>
<p><?php echo $badge['Badge']['description']; ?></p>
<?php if ($is_admin || $is_manager): ?>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Category'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __(Configure::read("options.category.{$badge['Badge']['category']}")); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Active'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($badge['Badge']['active'] ? 'Yes' : 'No'); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Visibility'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __(Configure::read("options.visibility.{$badge['Badge']['visibility']}")); ?>
			&nbsp;
		</dd>
	</dl>
<?php endif; ?>
</div>

<?php if ($is_logged_in && !empty($badge['Person'])):?>
<div class="related">
	<h3><?php __('This badge has been awarded to:');?></h3>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
	));
	?></p>
	<table class="list">
	<tr>
		<th><?php __('Name'); ?></th>
		<th><?php __('For'); ?></th>
	</tr>
<?php
		$i = 0;
		foreach ($badge['Person'] as $person):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
			$rand = 'row_' . mt_rand();
?>
	<tr id="<?php echo $rand; ?>"<?php echo $class;?>>
		<td><?php echo $this->element('people/block', compact('person')); ?></td>
		<td class="reasons"><?php
		$reasons = array();
		foreach ($person['BadgesPerson'] as $record) {
			if (in_array($badge['Badge']['category'], array('nominated', 'assigned'))) {
				$reason = $record['reason'];
				if ($is_admin || $is_manager) {
					$url = array('controller' => 'people', 'action' => 'delete_badge', 'id' => $record['id'], 'row' => $rand);
					$url_string = Router::url($url);
					$reason = $this->Html->tag('span',
							$reason . ' (' . $this->Html->link(__('Delete', true), $url,
								array(
									'escape' => false,
									'onClick' => "badge_handle_comment('$url_string'); return false;",
								)
							) . ')',
							array('class' => "id_{$record['id']}"));
				}
				$reasons[] = $reason;
			} else if (!empty($record['game_id'])) {
				$reasons[] = $this->element('games/block', array('game' => $record['Game']));
			} else if (!empty($record['team_id'])) {
				$reasons[] = $this->element('teams/block', array('team' => $record['Team'], 'show_shirt' => false));
			} else if (!empty($record['registration_id'])) {
				$reasons[] = $this->ZuluruHtml->link($record['Registration']['Event']['name'], array('controller' => 'events', 'action' => 'view', 'event' => $record['Registration']['Event']['id']));
			}
		}
		echo implode(', ', $reasons);
		?></td>
	</tr>
<?php endforeach; ?>
	</table>
</div>
<div class="paging">
	<?php echo $this->Paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled'));?>
 | 	<?php echo $this->Paginator->numbers();?> | 
	<?php echo $this->Paginator->next(__('next', true).' >>', array(), null, array('class' => 'disabled'));?>
</div>
<?php endif; ?>

<div class="actions">
	<ul>
		<?php
		if ($badge['Badge']['category'] == 'nominated') {
			echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('nominate_32.png',
				array('controller' => 'people', 'action' => 'nominate_badge', 'badge' => $badge['Badge']['id']),
				array('alt' => __('Nominate', true), 'title' => __('Nominate for this Badge', true))));
		}
		if ($is_admin || $is_manager) {
			if ($badge['Badge']['category'] == 'assigned') {
				echo $this->ZuluruHtml->iconLink('nominate_32.png',
					array('controller' => 'people', 'action' => 'nominate_badge', 'badge' => $badge['Badge']['id']),
					array('alt' => __('Assign', true), 'title' => __('Assign this Badge', true)));
			} else if (!in_array($badge['Badge']['category'], array('nominated', 'runtime', 'aggregate'))) {
				echo $this->ZuluruHtml->iconLink('initialize_32.png',
					array('action' => 'initialize', 'badge' => $badge['Badge']['id']),
					array('alt' => __('Initialize', true), 'title' => __('Initialize', true)),
					array('confirm' => __('Are you sure you want to initialize? This should only ever need to be done once when the badge system is introduced.', true)));
			}
			echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('edit_32.png',
				array('action' => 'edit', 'badge' => $badge['Badge']['id']),
				array('alt' => __('Edit', true), 'title' => __('Edit Badge', true))));
			echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('delete_32.png',
				array('action' => 'delete', 'badge' => $badge['Badge']['id']),
				array('alt' => __('Delete', true), 'title' => __('Delete Badge', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $badge['Badge']['id']))));
			echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('add_32.png',
				array('action' => 'add'),
				array('alt' => __('Add', true), 'title' => __('Add Badge', true))));
		}
		?>
	</ul>
</div>

<?php echo $this->element('people/badge_div', array(
	'message' => 'If you want to add a comment to the badge holder about why the badge is being removed, do so here.',
)); ?>
