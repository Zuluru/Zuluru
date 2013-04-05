<?php if (isset ($error)): ?>
<p class="error-message"><?php __($error); ?></p>

<?php elseif (isset ($people) && empty ($people)): ?>
<p class="error-message"><?php __('No matches found!'); ?></p>

<?php elseif (isset ($people)): ?>

<div class="index">
<p>
<?php
// TODO: Test when JS is disabled
$this->Paginator->options(array(
	'update' => '#SearchResults',
	'evalScripts' => true,
	'url' => $url,
));
echo $this->Paginator->counter(array(
'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
));
?></p>
<table class="list">
<tr>
	<th><?php echo $this->Paginator->sort('first_name', null, array('buffer' => false));?></th>
	<th><?php echo $this->Paginator->sort('last_name', null, array('buffer' => false));?></th>
	<th class="actions"><?php __('Actions');?></th>
</tr>
<?php
$i = 0;
foreach ($people as $person):
	$affiliates = Set::extract('/Affiliate/id', $person);
	$mine = array_intersect($affiliates, $this->Session->read('Zuluru.ManagedAffiliateIDs'));
	$is_manager = !empty($mine);

	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $this->element('people/block', array('person' => $person, 'display_field' => 'first_name')); ?>
		</td>
		<td>
			<?php echo $this->element('people/block', array('person' => $person, 'display_field' => 'last_name')); ?>
		</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View', true), array('controller' => 'people', 'action' => 'view', 'person' => $person['Person']['id'])); ?>
			<?php
			if ($is_logged_in && Configure::read('feature.annotations')) {
				if (!empty($person['Note'])) {
					echo $this->Html->link(__('Delete Note', true), array('controller' => 'people', 'action' => 'delete_note', 'person' => $person['Person']['id'], 'return' => true));
					$link = 'Edit Note';
				} else {
					$link = 'Add Note';
				}
				echo $this->Html->link(__($link, true), array('controller' => 'people', 'action' => 'note', 'person' => $person['Person']['id'], 'return' => true));
			}
			?>
			<?php if ($is_admin || $is_manager): ?>
			<?php echo $this->Html->link(__('Edit', true), array('controller' => 'people', 'action' => 'edit', 'person' => $person['Person']['id'], 'return' => true)); ?>
			<?php echo $this->Html->link(__('Delete', true), array('controller' => 'people', 'action' => 'delete', 'person' => $person['Person']['id'], 'return' => true), null, sprintf(__('Are you sure you want to delete # %s?', true), $person['Person']['id'])); ?>
			<?php endif; ?>
			<?php
			if (!empty($extra_url)) {
				foreach ($extra_url as $title => $url) {
					$url = array_merge (array('person' => $person['Person']['id'], 'return' => true), $url);
					echo $this->Html->link(__($title, true), $url);
				}
			}
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<div class="paging">
	<?php echo $this->Paginator->prev('<< '.__('previous', true), array('buffer' => false), null, array('class'=>'disabled'));?>
 | 	<?php echo $this->Paginator->numbers(array('buffer' => false));?> | 
	<?php echo $this->Paginator->next(__('next', true).' >>', array('buffer' => false), null, array('class' => 'disabled'));?>
</div>

<?php endif; ?>
