<?php
$this->Html->addCrumb (__('People', true));
$this->Html->addCrumb (__('New Accounts', true));
?>

<div class="people list_new">
<h2><?php __('New Accounts');?></h2>

<?php
$rows = array();
foreach ($new as $person) {
	$links = array(
		$this->ZuluruHtml->iconLink('view_24.png',
			array('action' => 'view', 'person' => $person['Person']['id']),
			array('alt' => __('View', true), 'title' => __('View', true))),
		$this->ZuluruHtml->iconLink('edit_24.png',
			array('action' => 'edit', 'person' => $person['Person']['id'], 'return' => true),
			array('alt' => __('Edit', true), 'title' => __('Edit', true))),
		$this->Html->link (__('Approve', true), array('action' => 'approve', 'person' => $person['Person']['id'])),
	);
	if (Configure::read('feature.manage_accounts')) {
		$links[] = $this->ZuluruHtml->iconLink('delete_24.png',
			array('action' => 'delete', 'person' => $person['Person']['id'], 'return' => true),
			array('alt' => __('Delete', true), 'title' => __('Delete', true)),
			array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $person['Person']['id'])));
	}

	$class = ($person['Person']['duplicate'] ? 'warning-message' : '');
	$rows[] = array(
		array($person['Person']['full_name'], array('class' => $class)),
		array(implode ('', $links), array('class' => 'actions'))
	);
}
if (empty($rows)) {
	echo $this->Html->para(null, __('No accounts to approve.', true));
} else {
	echo $this->Html->tag('table', $this->Html->tableCells ($rows, array(), array('class' => 'altrow')), array('class' => 'list'));
}
?>

</div>
