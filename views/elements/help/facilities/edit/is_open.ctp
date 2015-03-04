<p><?php
printf(__('A facility should be marked as "open" as long as any %s at the facility is potentially in use. Only close a facility when it is no longer available.', true),
	__(Configure::read('ui.field'), true)
);
?></p>
