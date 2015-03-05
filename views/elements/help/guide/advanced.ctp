<h2><?php __('Advanced User Guide'); ?></h2>
<h3><?php __('Pop-ups'); ?></h3>
<p><?php
printf(__('To make it easier and faster to find the details you are looking for, %s includes pop-ups in a number of areas. Currently, anywhere that you see a player, team or %s name, you can hover your mouse over it, and you will get a pop-up box with additional details and links about that person, team or %s. To make it disappear, just move your mouse away!', true),
	ZULURU, __(Configure::read('ui.field'), true), __(Configure::read('ui.field'), true)
); ?></p>
<p><?php
printf(__('Smart phones do not support the concept of "hovering", so if %s detects that you are running on a smart phone, a %s "pop-up" icon will be visible to the left of these items. Clicking that icon will bring up the pop-up, and clicking it again will hide it.', true),
	ZULURU, $this->ZuluruHtml->icon('popup_16.png')
); ?></p>
<p><?php __('Note that the content of all pop-ups is loaded from the server on demand, so it may take a second or two for the pop-up to appear.'); ?></p>
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
