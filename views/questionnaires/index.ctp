<?php
$this->Html->addCrumb (__('Questionnaires', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="questionnaires index">
	<h2><?php __($active ? 'Questionnaires List' : 'Deactivated Questionnaires List');?></h2>
	<table class="list">
	<tr>
		<th><?php echo $this->Paginator->sort('name');?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
	$i = 0;
	$affiliate_id = null;
	foreach ($questionnaires as $questionnaire):
		if (count($affiliates) > 1 && $questionnaire['Questionnaire']['affiliate_id'] != $affiliate_id):
			$affiliate_id = $questionnaire['Questionnaire']['affiliate_id'];
	?>
	<tr>
		<th colspan="2">
			<h3 class="affiliate"><?php echo $questionnaire['Affiliate']['name']; ?></h3>
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
		<td><?php echo $questionnaire['Questionnaire']['name']; ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Preview', true), array('action' => 'view', 'questionnaire' => $questionnaire['Questionnaire']['id'])); ?>
			<?php echo $this->Html->link(__('Edit', true), array('action' => 'edit', 'questionnaire' => $questionnaire['Questionnaire']['id'])); ?>
			<?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', 'questionnaire' => $questionnaire['Questionnaire']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $questionnaire['Questionnaire']['id'])); ?>
			<?php $id = 'span_' . mt_rand(); ?>
			<span id="<?php echo $id; ?>">
			<?php
			if ($questionnaire['Questionnaire']['active']) {
				echo $this->Js->link(__('Deactivate', true),
						array('action' => 'deactivate', 'questionnaire' => $questionnaire['Questionnaire']['id'], 'id' => $id),
						array('update' => "#temp_update")
				);
			} else {
				echo $this->Js->link(__('Activate', true),
						array('action' => 'activate', 'questionnaire' => $questionnaire['Questionnaire']['id'], 'id' => $id),
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
		<li><?php echo $this->Html->link(sprintf(__('New %s', true), __('Questionnaire', true)), array('action' => 'add')); ?></li>
	</ul>
</div>
