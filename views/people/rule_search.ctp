<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb (__('Rule-based Search', true));
?>

<div class="people search">
<h2><?php printf(__('Search %s', true), __('Players', true)); ?></h2>

<div class="search form">
<?php echo $this->Form->create(false, array('url' => $url, 'id' => 'RuleSearchForm'));?>
<p>Enter a rule to find people who match.
<?php echo $this->ZuluruHtml->help(array('action' => 'rules', 'rules')); ?>
</p>
<?php
if (isset($affiliate_id)) {
	echo $this->Form->hidden('affiliate_id', array('value' => $affiliate_id));
} else if (isset($affiliates)) {
	echo $this->ZuluruForm->input('affiliate_id', array(
			'options' => $affiliates,
			'hide_single' => true,
	));
}

echo $this->Form->input('rule', array('cols' => 60, 'rows' => 5));
echo $this->Form->hidden('sort', array('value' => 'last_name'));
echo $this->Form->hidden('direction', array('value' => 'asc'));

echo $this->Js->submit(__('Search', true), array('url'=> $url, 'update' => '#SearchResults', 'evalScripts' => true));
echo $this->Form->end();
?>
</div>

<div id="SearchResults">
</div>

<?php endif; ?>

<?php echo $this->element('people/search_results'); ?>

<?php if (!$this->params['isAjax']): ?>

</div>
<?php endif; ?>
