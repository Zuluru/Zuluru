<?php
$emails = array();
foreach ($people as $person) {
	if (!empty($person['email_formatted'])) {
		$emails[$person['email']] = $person['email_formatted'];
	}
	if (!empty($person['alternate_email_formatted'])) {
		$emails[$person['alternate_email']] = $person['alternate_email_formatted'];
	}

	// Check for relatives, if this is a person record without a user record
	if (empty($person['user_id'])) {
		$relatives = $this->UserCache->read('RelatedTo', $person['id']);
		foreach ($relatives as $relative) {
			if (!empty($relative['Relative']['email_formatted'])) {
				$emails[$relative['Relative']['email']] = $relative['Relative']['email_formatted'];
			}
			if (!empty($relative['Relative']['alternate_email_formatted'])) {
				$emails[$relative['Relative']['alternate_email']] = $relative['Relative']['alternate_email_formatted'];
			}
		}
	}
}
?>
<p>You can copy and paste the emails below into your addressbook, or <?php
echo $this->Html->link(__('send an email right away', true), 'mailto:' . implode (',', $emails)); ?>.</p>
<?php
echo implode(',<br>', array_map('htmlentities', $emails));
?>
<p>Note that if you are using Microsoft Outlook, you may need to click in the To line of the message that pops up in order for the addresses to be recognized.</p>
