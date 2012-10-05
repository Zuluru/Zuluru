<?php
$this->Html->addCrumb (__('Questions', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="questions index">
	<h2><?php __($active ? 'Questions List' : 'Deactivated Questions List');?></h2>
	<table class="list">
	<tr>
		<th><?php echo $this->Paginator->sort('question');?></th>
		<th><?php echo $this->Paginator->sort('type');?></th>
		<th><?php echo $this->Paginator->sort('anonymous');?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
	$i = 0;
	$affiliate_id = null;
	foreach ($questions as $question):
		if (count($affiliates) > 1 && $question['Question']['affiliate_id'] != $affiliate_id):
			$affiliate_id = $question['Question']['affiliate_id'];
	?>
	<tr>
		<th colspan="4">
			<h3 class="affiliate"><?php echo $question['Affiliate']['name']; ?></h3>
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
		<td><?php echo $question['Question']['question']; ?>&nbsp;</td>
		<td><?php echo $question['Question']['type']; ?>&nbsp;</td>
		<td><?php __($question['Question']['anonymous'] ? 'yes' : 'no'); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Preview', true), array('action' => 'view', 'question' => $question['Question']['id'])); ?>
			<?php echo $this->Html->link(__('Edit', true), array('action' => 'edit', 'question' => $question['Question']['id'])); ?>
			<?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', 'question' => $question['Question']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $question['Question']['id'])); ?>
			<?php $id = 'span_' . mt_rand(); ?>
			<span id="<?php echo $id; ?>">
			<?php
			if ($question['Question']['active']) {
				echo $this->Js->link(__('Deactivate', true),
						array('action' => 'deactivate', 'question' => $question['Question']['id'], 'id' => $id),
						array('update' => "#temp_update")
				);
			} else {
				echo $this->Js->link(__('Activate', true),
						array('action' => 'activate', 'question' => $question['Question']['id'], 'id' => $id),
						array('update' => "#temp_update")
				);
			}
			?>
			</span>
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
		<li><?php echo $this->Html->link(sprintf(__('New %s', true), __('Question', true)), array('action' => 'add')); ?></li>
	</ul>
</div>
