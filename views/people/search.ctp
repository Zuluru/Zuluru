<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('People', true));
$this->Html->addCrumb (__('Search', true));
?>

<div class="people search">
<h2><?php printf(__('Search %s', true), __('People', true)); ?></h2>

<?php echo $this->element('people/search_form'); ?>

<?php if ($is_admin || $is_manager): ?>
<p>Alternately, you may <?php echo $this->Html->link(__('enter a rule and find people who match', true), array('action' => 'rule_search')); ?>,
<?php echo $this->Html->link(__('find everyone participating in a particular league', true), array('action' => 'league_search')); ?> or
<?php echo $this->Html->link(__('find all inactive users (not currently on any team)', true), array('action' => 'inactive_search')); ?>.
<?php endif; ?>

<div id="SearchResults">
<?php endif; ?>

<?php
if ($is_admin || $is_manager) {
	echo $this->element('people/search_results', array(
			'extra_url' => array(
				__('Change password', true) => array('controller' => 'users', 'action' => 'change_password', 'url_parameter' => 'user', 'url_field' => 'user_id'),
				__('Act As', true) => array('controller' => 'people', 'action' => 'act_as'),
			),
	));
} else {
	echo $this->element('people/search_results');
}
?>

<?php if (!$this->params['isAjax']): ?>

</div>
</div>
<?php endif; ?>
