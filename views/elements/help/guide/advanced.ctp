<h2>Advanced User Guide</h2>
<h3>Pop-ups</h3>
<p>To make it easier and faster to find the details you are looking for, <?php echo ZULURU; ?> includes pop-ups in a number of areas.
Currently, anywhere that you see a player, team or <?php __(Configure::read('ui.field')); ?> name, you can hover your mouse over it, and you will get a pop-up box with additional details and links about that person, team or <?php __(Configure::read('ui.field')); ?>.
To make it disappear, just move your mouse away!</p>
<p>Smart phones do not support the concept of "hovering", so if <?php echo ZULURU; ?> detects that you are running on a smart phone, a <?php echo $this->ZuluruHtml->icon('popup_16.png'); ?> "pop-up" icon will be visible to the left of these items.
Clicking that icon will bring up the pop-up, and clicking it again will hide it.</p>
<p>Note that the content of all pop-ups is loaded from the server on demand, so it may take a second or two for the pop-up to appear.</p>
<?php
	echo $this->element('help/topics', array(
			'section' => 'people',
			'topics' => array(
				'preferences',
				'photo_upload' => 'Player Photos',
				'skill_level',
			),
			'compact' => true,
	));
?>
