<?php
$this->Html->addCrumb (__('Badges', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="badges index">
<h2><?php __($active ? 'Badges' : 'Deactivated Badges');?></h2>
<p>
<?php
echo $this->Paginator->counter(array(
'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
));
?></p>
<table class="list">
	<tr>
		<th><?php echo $this->Paginator->sort('name');?></th>
		<th><?php echo $this->Paginator->sort('category');?></th>
		<th><?php echo $this->Paginator->sort('visibility');?></th>
		<th><?php __('Icon'); ?></th>
		<?php if ($active): ?>
		<th><?php __('Awarded'); ?></th>
		<?php endif; ?>
		<th class="actions"><?php __('Actions'); ?></th>
	</tr>
	</tr>
	<?php
	$i = 0;
	$affiliate_id = null;
	foreach ($badges as $badge):
		$is_manager = $is_logged_in && in_array($badge['Badge']['affiliate_id'], $this->Session->read('Zuluru.ManagedAffiliateIDs'));

		if (count($affiliates) > 1 && $badge['Badge']['affiliate_id'] != $affiliate_id):
			$affiliate_id = $badge['Badge']['affiliate_id'];
	?>
	<tr>
		<th colspan="6">
			<h3 class="affiliate"><?php echo $badge['Affiliate']['name']; ?></h3>
		</th>
	</tr>
	<?php
		endif;

		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $badge['Badge']['name']; ?>&nbsp;</td>
		<td><?php __(Configure::read("options.category.{$badge['Badge']['category']}")); ?>&nbsp;</td>
		<td><?php __(Configure::read("options.visibility.{$badge['Badge']['visibility']}")); ?>&nbsp;</td>
		<td><?php echo $this->ZuluruHtml->icon($badge['Badge']['icon'] . '_64.png'); ?>&nbsp;</td>
		<?php if ($active): ?>
		<td><?php
		if (in_array($badge['Badge']['category'], array('runtime', 'aggregate'))) {
			echo $this->Html->tag('abbr', __('N/A', true),
					array('title' => __('This badge is determined on-the-fly so there is no count of how many people have it', true)));
		} else {
			echo $badge['count'];
		}
		?></td>
		<?php endif; ?>
		<td class="actions">
			<?php
			echo $this->ZuluruHtml->iconLink('view_24.png',
				array('action' => 'view', 'badge' => $badge['Badge']['id']),
				array('alt' => __('View', true), 'title' => __('View', true)));
			if ($is_admin || $is_manager) {
				echo $this->ZuluruHtml->iconLink('edit_24.png',
					array('action' => 'edit', 'badge' => $badge['Badge']['id']),
					array('alt' => __('Edit', true), 'title' => __('Edit', true)));
				echo $this->ZuluruHtml->iconLink('delete_24.png',
					array('action' => 'delete', 'badge' => $badge['Badge']['id']),
					array('alt' => __('Delete', true), 'title' => __('Delete', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $badge['Badge']['id'])));

				$id = 'span_' . mt_rand(); ?>
				<span id="<?php echo $id; ?>">
				<?php
				if ($badge['Badge']['active']) {
					echo $this->Js->link(__('Deactivate', true),
							array('action' => 'deactivate', 'badge' => $badge['Badge']['id'], 'id' => $id),
							array('update' => "#temp_update")
					);
				} else {
					echo $this->Js->link(__('Activate', true),
							array('action' => 'activate', 'badge' => $badge['Badge']['id'], 'id' => $id),
							array('update' => "#temp_update")
					);
				}
				?>
				</span>

				<?php
				if ($active) {
					if ($badge['Badge']['category'] == 'assigned') {
						echo $this->ZuluruHtml->iconLink('nominate_24.png',
							array('controller' => 'people', 'action' => 'nominate_badge', 'badge' => $badge['Badge']['id']),
							array('alt' => __('Assign', true), 'title' => __('Assign this Badge', true)));
					} else if (!in_array($badge['Badge']['category'], array('nominated', 'runtime', 'aggregate'))) {
						echo $this->ZuluruHtml->iconLink('initialize_24.png',
							array('action' => 'initialize', 'badge' => $badge['Badge']['id']),
							array('alt' => __('Initialize', true), 'title' => __('Initialize', true)),
							array('confirm' => __('Are you sure you want to initialize? This should only ever need to be done once when the badge system is introduced.', true)));
					}
				}
			}
			if ($badge['Badge']['category'] == 'nominated' && $active) {
				echo $this->ZuluruHtml->iconLink('nominate_24.png',
					array('controller' => 'people', 'action' => 'nominate_badge', 'badge' => $badge['Badge']['id']),
					array('alt' => __('Nominate', true), 'title' => __('Nominate for this Badge', true)));
			}
			?>
			</span>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
</div>
<div class="actions">
	<ul>
		<?php
		if ($is_admin || $is_manager) {
			echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('add_32.png',
				array('action' => 'add'),
				array('alt' => __('Add', true), 'title' => __('Add Badge', true))));
		}
		?>
	</ul>
</div>
<div class="paging">
	<?php echo $this->Paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled'));?>
 | 	<?php echo $this->Paginator->numbers();?> | 
	<?php echo $this->Paginator->next(__('next', true).' >>', array(), null, array('class' => 'disabled'));?>
</div>
