<?php
$this->Html->addCrumb (__('Contacts', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="contacts index">
<h2><?php __('List Contacts');?></h2>
<?php if (empty($contacts)): ?>
<p class="warning-message"><?php __('There are no contacts in the system.'); ?></p>
<?php else: ?>
<p>
<?php
echo $this->Paginator->counter(array(
'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
));
?></p>
<table class="list">
<tr>
	<th><?php echo $this->Paginator->sort('name');?></th>
	<th><?php __('Email');?></th>
	<th class="actions"><?php __('Actions');?></th>
</tr>
<?php
$i = 0;
$affiliate_id = null;
foreach ($contacts as $contact):
	$is_manager = $is_logged_in && in_array($contact['Contact']['affiliate_id'], $this->UserCache->read('ManagedAffiliateIDs'));

	if (count($affiliates) > 1 && $contact['Contact']['affiliate_id'] != $affiliate_id):
		$affiliate_id = $contact['Contact']['affiliate_id'];
?>
<tr>
	<th colspan="3">
		<h3 class="affiliate"><?php echo $contact['Affiliate']['name']; ?></h3>
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
		<td><?php echo $contact['Contact']['name']; ?></td>
		<td><?php echo $contact['Contact']['email']; ?></td>
		<td class="actions">
			<?php
			echo $this->ZuluruHtml->iconLink('edit_24.png',
				array('action' => 'edit', 'contact' => $contact['Contact']['id']),
				array('alt' => __('Edit Contact', true), 'title' => __('Edit Contact', true)));
			echo $this->ZuluruHtml->iconLink('delete_24.png',
				array('action' => 'delete', 'contact' => $contact['Contact']['id']),
				array('alt' => __('Delete', true), 'title' => __('Delete Contact', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $contact['Contact']['id'])));
			echo $this->ZuluruHtml->iconLink('email_24.png',
				array('action' => 'message', 'contact' => $contact['Contact']['id']),
				array('alt' => __('Message Contact', true), 'title' => __('Message Contact', true)));
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<div class="paging">
	<?php echo $this->Paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled'));?>
 | 	<?php echo $this->Paginator->numbers();?> | 
	<?php echo $this->Paginator->next(__('next', true).' >>', array(), null, array('class' => 'disabled'));?>
<?php endif; ?>
</div>
<div class="actions">
	<ul>
		<li><?php
		echo $this->ZuluruHtml->iconLink('add_24.png',
				array('action' => 'add'),
				array('alt' => __('Add Contact', true), 'title' => __('Add Contact', true)));
		?></li>
	</ul>
</div>
