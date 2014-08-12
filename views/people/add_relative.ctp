<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('People', true));
$this->Html->addCrumb ($person['full_name']);
$this->Html->addCrumb (__('Add Relative', true));
?>

<div class="people add_relative">
<h2><?php echo sprintf(__('Add %s', true), __('Relative', true)) . ': ' . $person['full_name'];?></h2>

<p>By adding someone as a relative, you will be able to see their schedule and perform certain actions in the system on their behalf.
Adding someone as a relative does <strong>not</strong> give them any control over your information; to allow this, they need to add you as a relative.</p>
<p>After adding them, they still need to accept you as a relative before you can manage their account for them.</p>
<?php echo $this->element('people/search_form', array('affiliates' => Set::combine($this->UserCache->read('Affiliates'), '{n}.Affiliate.id', '{n}.Affiliate.name'))); ?>

<div id="SearchResults">
<?php endif; ?>

<?php echo $this->element('people/search_results', array('extra_url' => array(__('Add as relative', true) => array('controller' => 'people', 'action' => 'add_relative', 'person' => $person['id'], 'return' => false)), 'extra_url_parameter' => 'relative')); ?>

<?php if (!$this->params['isAjax']): ?>

</div>
</div>
<?php endif; ?>
