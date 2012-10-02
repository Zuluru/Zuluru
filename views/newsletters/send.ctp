<?php
$this->Html->addCrumb (__('Newsletter', true));
$this->Html->addCrumb ($newsletter['Newsletter']['name']);
$this->Html->addCrumb (__('Sending', true));
?>

<div class="newsletters sent">
<h2><?php  echo __('Sending', true) . ': ' . $newsletter['Newsletter']['name'];?></h2>
<?php
if ($execute) {
	echo $this->Html->para(null, 'Batch sent at ' . $this->ZuluruTime->time(time() - Configure::read('timezone.adjust') * 60));

	$emails = Set::extract ('/Person/email', $people);
	echo $this->Html->para(null, __('Sent email to', true) . ' ' . implode (', ', $emails));

	// Wait for a bit, then redirect to the next group
	$next = $this->Html->url (array('action' => 'send', 'newsletter' => $newsletter['Newsletter']['id'], 'execute' => true), true);
	$this->addScript($this->Html->meta (array('http-equiv' => 'refresh', 'content' => "$delay;url=$next")));
} else {
	if ($test) {
		$emails = Set::extract ('/Person/email', $people);
		echo $this->Html->para(null, __('Test email sent to', true) . ' ' . implode (', ', $emails));
	}
	echo $this->Html->para(null, sprintf(__('To send yourself a test copy of this newsletter, %s.', true), $this->Html->link(__('click here', true), array('action' => 'send', 'newsletter' => $newsletter['Newsletter']['id'], 'test' => true))));
	echo $this->Html->para(null, sprintf(__('To initiate delivery of this newsletter, %s.', true), $this->Html->link(__('click here', true), array('action' => 'send', 'newsletter' => $newsletter['Newsletter']['id'], 'execute' => true))));
}
?>
</div>
