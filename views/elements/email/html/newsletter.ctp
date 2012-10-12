<?php
$text = $newsletter['Newsletter']['text'];
if ($newsletter['Newsletter']['personalize']) {
	$text = strtr ($text, array(
			'first_name' => $person['Person']['first_name'],
			'last_name' => $person['Person']['last_name'],
			'full_name' => $person['Person']['full_name'],
	));
}
echo $text;
?>

<?php if ($newsletter['Newsletter']['personalize']): ?>
<p>This message was sent to <?php echo $person['Person']['email']; ?>.</p>
<?php endif; ?>

<p>You have received this message because you are on the <?php
if (Configure::read('feature.affiliates')) {
	echo $newsletter['MailingList']['Affiliate']['name'];
} else {
	echo Configure::read('organization.name');
}
?>'s <?php
echo $newsletter['MailingList']['name']; ?> mailing list. To learn more about how we use your information, please read our privacy policy or contact <?php
echo $this->Html->link(Configure::read('email.admin_name'), 'mailto:' . Configure::read('email.admin_email')); ?>.</p>

<?php if ($newsletter['MailingList']['opt_out']): ?>
<p>To unsubscribe from this mailing list,
<?php
if ($newsletter['Newsletter']['personalize']):
	$url = Router::url(array('controller' => 'mailing_lists', 'action' => 'unsubscribe', 'list' => $newsletter['MailingList']['id'], 'person' => $person['Person']['id'], 'code' => $code), true);
	echo $this->Html->link('click here', $url);
else:
	$url = Router::url(array('controller' => 'mailing_lists', 'action' => 'unsubscribe', 'list' => $newsletter['MailingList']['id']), true);
	echo $this->Html->link('click here', $url) . '. You must be logged in to the web site for this to work';
endif;
?>
.</p>
<?php endif; ?>
