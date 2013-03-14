<?php
$this->Html->addCrumb (__('Mailing Lists', true));
$this->Html->addCrumb ($mailingList['MailingList']['name']);
$this->Html->addCrumb (__('Preview', true));
?>

<div class="mailingLists preview">
	<h2><?php echo $mailingList['MailingList']['name'];?></h2>
	<p>This mailing list currently matches the following people. Keep in mind that mailing lists are dynamic, so the list may change from day to day as people register, join teams, etc.</p>
	<p><?php
	$out = array();
	foreach ($people as $person) {
		$out[] = $this->element('people/block', compact('person'));
	}
	echo implode(', ', $out);
	?></p>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
	));
	?>	</p>

	<div class="paging">
		<?php echo $this->Paginator->prev('<< ' . __('previous', true), array(), null, array('class'=>'disabled'));?>
	 | 	<?php echo $this->Paginator->numbers();?>
 |
		<?php echo $this->Paginator->next(__('next', true) . ' >>', array(), null, array('class' => 'disabled'));?>
	</div>
</div>