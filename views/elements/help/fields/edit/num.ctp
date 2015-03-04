<p><?php
printf(__('When there are multiple %s at a single facility, the individual %s are distinguished by "number". This might be as simple as "1", "2" and "3", it might be "East" and "West", or (particularly at large multi-use facilities) it might be something more like "Soccer 1 North".', true),
	__(Configure::read('ui.fields'), true), __(Configure::read('ui.fields'), true)
);
?></p>
<p><?php
printf(__('As this is used in a number of displays, it should be kept as succinct as possible, without loss of specificity. For example, "Soccer1N" might be sufficient instead of "Soccer 1 North". One common scheme is to number %s starting with the %s closest to the parking lot or entrance. Remember that players can always refer to the %s layout diagram if they are in doubt.', true),
	__(Configure::read('ui.fields'), true), __(Configure::read('ui.field'), true), __(Configure::read('ui.field'), true)
);
?></p>
