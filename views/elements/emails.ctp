<p>You can copy and paste the emails below into your addressbook, or <?php
echo $this->Html->link(__('send an email right away', true), 'mailto:' . implode (';', Set::extract ('/email_formatted', $people))); ?>.</p>
<?php
foreach ($people as $person) {
	echo htmlentities ($person['email_formatted']) . ';<br>';
}
?>
