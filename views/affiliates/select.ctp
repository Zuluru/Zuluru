<?php
$this->Html->addCrumb (__('Affiliates', true));
$this->Html->addCrumb (__('Select', true));
?>

<div class="affiliates form">
<?php
echo $this->Form->create('Affiliate', array('url' => Router::normalize($this->here)));
echo $this->Html->para('warning-message', __('By selecting an affiliate below, you will only be shown that affiliate\'s details throughout the site. You will be able to remove this restriction or select another affiliate to browse, using links on your home page.', true));
echo $this->Html->para('warning-message', __('Note that, regardless of which affiliate you may select, your home page and menus will always show your teams and games.', true));
echo $this->Form->input('affiliate');
echo $this->Form->end(__('Submit', true));
?>
</div>
