<?php
$this->Html->addCrumb (__('Groups', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="groups index">
	<h2><?php __('Permission Groups');?></h2>
	<table class="list">
	<tr>
		<th><?php __('Name'); ?></th>
		<th class="actions"><?php __('Actions'); ?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($groups as $group):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $group['Group']['name']; ?>&nbsp;</td>
		<td class="actions">
			<?php $id = 'span_' . mt_rand(); ?>
			<span id="<?php echo $id; ?>">
			<?php
			if ($group['Group']['name'] != 'Administrator') {
				if ($group['Group']['active']) {
					echo $this->Js->link(__('Deactivate', true),
							array('action' => 'deactivate', 'group' => $group['Group']['id'], 'id' => $id),
							array('update' => "#temp_update")
					);
				} else {
					echo $this->Js->link(__('Activate', true),
							array('action' => 'activate', 'group' => $group['Group']['id'], 'id' => $id),
							array('update' => "#temp_update")
					);
				}
			}
			?>
			</span>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<p><?php __('Active groups are available for people to select during account setup or edit, or be assigned to by an admin. The "Player" group will always be available for admins; deactivating this only means that it can\'t be used at the time of account creation (e.g. if you are running a youth league where most accounts will be parents). The "Administrator" group cannot be deactivated.'); ?></p>
</div>
