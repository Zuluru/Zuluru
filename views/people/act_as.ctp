<?php
$this->Html->addCrumb (__('People', true));
$this->Html->addCrumb (__('Act As', true));
?>

<div class="people act_as">
<h2><?php __('Act As');?></h2>

<?php
echo $this->Html->para(null, sprintf(__('You are currently using the site as %s. This gives you the option to change to one of the following people.', true), $this->UserCache->read('Person.full_name')));
echo $this->Html->para(null, __('Switch to:'));
?>
<ul>
<?php
foreach ($opts as $id => $name) {
	echo $this->Html->tag('li', $this->Html->link($name, array('controller' => 'people', 'action' => 'act_as', 'person' => $id)));
}
?>
</ul>
</div>