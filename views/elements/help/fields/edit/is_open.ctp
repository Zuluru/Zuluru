<p><?php
printf(__('Typically, a %s should be marked as "open" if it is in use by a current or upcoming league. By "closing" %s not currently in use, the %s list will only display those facilities that players might need to travel to, making it easier for them to find relevant information.', true),
	__(Configure::read('ui.field'), true), __(Configure::read('ui.fields'), true), __(Configure::read('ui.field'), true)
);
?></p>
