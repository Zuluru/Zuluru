<?php
// Set up defaults
if (!isset ($url)) {
	$url = array();
}
?>

<div class="search form">
<?php echo $this->Form->create(false, array('url' => $url, 'id' => 'SearchForm'));?>
<p>Enter first and/or last name of person to search for and click 'submit'. You may use '*' as a wildcard.
<?php echo $this->ZuluruHtml->help(array('action' => 'people', 'searching')); ?>
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

echo $this->Form->input('first_name', array('size' => 40, 'maxlength' => 100));
echo $this->Form->input('last_name', array('size' => 40, 'maxlength' => 100));
echo $this->Form->hidden('sort', array('value' => 'last_name'));
echo $this->Form->hidden('direction', array('value' => 'asc'));

echo $this->Js->submit(__('Search', true), array('url'=> $url, 'update' => '#SearchResults', 'evalScripts' => true));
echo $this->Form->end();
?>
</div>

<div id="SearchResults">
</div>
