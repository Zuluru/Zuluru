<?php
$this->Html->addCrumb (__('People', true));
$this->Html->addCrumb (__('List', true));
if (isset($group)) {
	$this->Html->addCrumb (__(Inflector::pluralize($group), true));
}
?>

<div class="people index">
<h2><?php
__('People');
if (isset($group)) {
	echo ': ' . __(Inflector::pluralize($group), true);
}

$user_names = array_unique(Set::extract('/Person/user_name', $people));
$hide_user_name = (empty($user_names) || (count($user_names) == 1 && empty($user_names[0])));
$emails = array_unique(Set::extract('/Person/email', $people));
$hide_email = (empty($emails) || (count($emails) == 1 && empty($emails[0])));
$genders = array_unique(Set::extract('/Person/gender', $people));
$hide_gender = (empty($genders) || (count($genders) == 1 && empty($genders[0])));
?></h2>
<table class="list">
	<tr>
		<th><?php echo $this->Paginator->sort('first_name'); ?></th>
		<th><?php echo $this->Paginator->sort('last_name'); ?></th>
		<?php if (!$hide_user_name): ?>
		<th><?php __('User Name'); ?></th>
		<?php endif; ?>
		<?php if (!$hide_email): ?>
		<th><?php __('Email'); ?></th>
		<?php endif; ?>
		<?php if (!$hide_gender): ?>
		<th><?php echo $this->Paginator->sort('gender'); ?></th>
		<?php endif; ?>
		<th><?php echo $this->Paginator->sort('status'); ?></th>
		<th class="actions"><?php __('Actions'); ?></th>
	</tr>
	<?php
	$i = 0;
	$affiliate_id = null;
	foreach ($people as $person):
		if (count($affiliates) > 1 && $person['Affiliate']['id'] != $affiliate_id):
			$affiliate_id = $person['Affiliate']['id'];
	?>
	<tr>
		<th colspan="7">
			<h3 class="affiliate"><?php echo $person['Affiliate']['name']; ?></h3>
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
		<td><?php echo $this->element('people/block', array('person' => $person, 'display_field' => 'first_name')); ?>&nbsp;</td>
		<td><?php echo $this->element('people/block', array('person' => $person, 'display_field' => 'last_name')); ?>&nbsp;</td>
		<?php if (!$hide_user_name): ?>
		<td><?php if (!empty($person['Person']['user_name'])) echo $person['Person']['user_name']; ?>&nbsp;</td>
		<?php endif; ?>
		<?php if (!$hide_email): ?>
		<td><?php echo $person['Person']['email']; ?>&nbsp;</td>
		<?php endif; ?>
		<?php if (!$hide_gender): ?>
		<td><?php echo $person['Person']['gender']; ?>&nbsp;</td>
		<?php endif; ?>
		<td><?php echo $person['Person']['status']; ?>&nbsp;</td>
		<td class="actions">
		<?php
		echo $this->ZuluruHtml->iconLink('view_24.png',
			array('action' => 'view', 'person' => $person['Person']['id']),
			array('alt' => __('View', true), 'title' => __('View', true)));
		echo $this->ZuluruHtml->iconLink('edit_24.png',
			array('action' => 'edit', 'person' => $person['Person']['id']),
			array('alt' => __('Edit', true), 'title' => __('Edit', true)));
		echo $this->ZuluruHtml->link(__('Act As', true), array('action' => 'act_as', 'person' => $person['Person']['id']));
		echo $this->ZuluruHtml->iconLink('delete_24.png',
			array('action' => 'delete', 'person' => $person['Person']['id']),
			array('alt' => __('Delete', true), 'title' => __('Delete', true)),
			array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $person['Person']['id'])));
		?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
	));
	?>	</p>

	<div class="paging">
		<?php echo $this->Paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled'));?>
	 | 	<?php echo $this->Paginator->numbers();?>
 |
		<?php echo $this->Paginator->next(__('next', true).' >>', array(), null, array('class' => 'disabled'));?>
	</div>
</div>
<div class="actions">
	<ul>
		<?php echo $this->Html->tag ('li', $this->Html->link(__('Download', true), array_merge($this->params['named'], array('ext' => 'csv')))); ?>
	</ul>
</div>
