<?php
$field = __(Configure::read('ui.field'), true);
$fields = __(Configure::read('ui.fields'), true);
?>
<p><?php printf(__('Please respect the following rules for all %s. Note that some facilities also have additional restrictions listed that must also be followed.', true), $fields); ?></p>
<ul>
	<li><?php __('Garbage containers do not exist at most facilities. Do not leave any garbage behind when you leave -- even if it isn\'t yours. Take extra care to remove any hazardous items (i.e.: bottlecaps, glass) to avoid injury to others.'); ?></li>
<?php if (Configure::read('feature.dog_questions')): ?>
	<li><?php printf(__('If dogs are not allowed at a particular %s, you <strong>must</strong> respect this. If dogs are permitted at a %s, you must clean up after your pet and take the waste away with you.', true), $field, $field); ?></li>
<?php endif; ?>
	<li><?php printf(__('By law, alcohol is not permitted at any league %s, and can lose us our ability to play there.', true), $field); ?></li>
</ul>
<p><?php printf(__('If %s are lost due to the actions of a particular player or team, they will be <strong>removed from the league</strong>.', true), $field); ?></p>
