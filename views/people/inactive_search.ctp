<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb (__('Inactive Search', true));
?>

<div class="people search">
<h2><?php printf(__('Search %s', true), __('Players', true)); ?></h2>

<div id="SearchResults">
<?php endif; ?>

<?php echo $this->element('people/search_results'); ?>

<?php if (!empty($params['rule'])): ?>
<p class="clear">To create a mailing list for this search, use this rule: <code><?php echo $params['rule']; ?></code></p>
<?php endif; ?>

<?php if (!$this->params['isAjax']): ?>
</div>
</div>
<?php endif; ?>
