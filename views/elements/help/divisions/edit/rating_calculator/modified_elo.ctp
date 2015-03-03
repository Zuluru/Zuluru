<p><?php __('The Modified Elo calculator is an older variant, retained primarily for backward compatibility. It has been found to have shortcomings when used for Ultimate. It is not recommended for use in new leagues.'); ?></p>
<p><?php
printf(__('This uses a modified Elo system, similar to the one used for %s, with several modifications:', true),
	$this->Html->link(__('international soccer', true), 'http://www.eloratings.net/')
);
?></p>
<ul>
<li><?php __('all games are equally weighted'); ?></li>
<li><?php __('score differential bonus adjusted for Ultimate patterns (a 3 point win in soccer is a much bigger deal than in Ultimate)'); ?></li>
<li><?php printf(__('no bonus given for home-%s advantage', true), __(Configure::read('ui.field'), true)); ?></li>
</ul>
