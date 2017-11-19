<?php
$this->Html->addCrumb (__('Newsletter', true));
$this->Html->addCrumb ($newsletter['Newsletter']['name']);
$this->Html->addCrumb (__('Delivery Report', true));
?>

<div class="newsletters view">
<h2><?php  echo __('Delivery Report', true) . ': ' . $newsletter['Newsletter']['name'];?></h2>
<p><?php printf(__('This newsletter has been delivered to %d people. Click letters below to see recipients whose last name start with that letter.', true), count($newsletter['Delivery'])); ?></p>
<?php
AppModel::_reindexOuter($people, 'Person', 'id');
AppModel::_reindexOuter($newsletter['Delivery'], null, 'person_id');

$letters = array();
foreach ($people as $person) {
	$letters[up($person['Person']['last_name'][0])] = true;
}
?>

<p><?php foreach (array_keys($letters) as $letter): ?>
	<a href="#" class="letter_link" id="letter_<?php echo $letter; ?>"><?php echo $letter; ?></a>
<?php endforeach; ?>
</p>
<table class="list">
<thead>
	<tr>
		<th><?php __('Recipient'); ?></th>
		<th><?php __('Date Sent'); ?></th>
	</tr>
</thead>
<tbody>
<?php foreach ($people as $person): ?>
	<tr class="letter letter_<?php echo up($person['Person']['last_name'][0]); ?>">
		<td><?php echo $this->Html->link($person['Person']['full_name'], array('controller' => 'people', 'action' => 'view', 'person' => $person['Person']['id'])); ?></td>
		<td><?php echo $this->ZuluruTime->date($newsletter['Delivery'][$person['Person']['id']]['created']); ?></td>
	</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->ZuluruHtml->iconLink('newsletter_send_32.png',
					array('action' => 'send', 'newsletter' => $newsletter['Newsletter']['id']),
					array('alt' => __('Send', true), 'title' => __('Send', true))); ?></li>
		<li><?php echo $this->ZuluruHtml->iconLink('edit_32.png',
					array('action' => 'edit', 'newsletter' => $newsletter['Newsletter']['id'], 'return' => true),
					array('alt' => __('Edit', true), 'title' => __('Edit', true))); ?></li>
		<li><?php echo $this->ZuluruHtml->iconLink('delete_32.png',
					array('action' => 'delete', 'newsletter' => $newsletter['Newsletter']['id']),
					array('alt' => __('Delete', true), 'title' => __('Delete Newsletter', true)),
					array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $newsletter['Newsletter']['id']))); ?></li>
		<li><?php echo $this->Html->link(sprintf(__('List %s', true), __('Newsletters', true)), array('action' => 'index'));?></li>
		<li><?php echo $this->ZuluruHtml->iconLink('newsletter_add_32.png',
					array('action' => 'add'),
					array('alt' => __('New', true), 'title' => __('New', true))); ?></li>
	</ul>
</div>

<?php
echo $this->Html->scriptBlock('
function display_letter(id) {
	jQuery(".letter").css("display", "none");
	jQuery("." + id).css("display", "");
}
');

$this->Js->buffer('
display_letter("letter_A");
jQuery(".letter_link").bind("click", function(){display_letter(jQuery(this).attr("id")); return false;});
');
?>