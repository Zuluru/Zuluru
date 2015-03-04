<p><?php
printf(__('Use this area for any general instructions that don\'t fall under other categories. Exmples might include %s setup notes, presence of playgrounds for those with children, etc.', true),
	__(Configure::read('ui.field'), true)
);
?></p>
<p><?php __('If you leave this field blank, it will not be shown on the facility view page.'); ?></p>
