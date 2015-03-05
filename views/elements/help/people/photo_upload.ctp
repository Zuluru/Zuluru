<p><?php
printf(__('The %s page will allow you to upload a photo of yourself, further personalizing your profile, and making it easier for coaches and captains to recognize you for all-star nominations and roster recruiting. Note that your photo is never visible to anyone outside the site, only to logged-in members of the club.', true),
	$this->Html->link(__('My Profile', true) . ' -> ' . __('Upload Photo', true), array('controller' => 'people', 'action' => 'photo_upload'))
);
?></p>
