<h2><?php printf (__('Game %d', true), $game['Game']['id']); ?></h2>
<dl>
	<dt><?php __('Date'); ?></dt>
	<dd><?php echo $this->ZuluruTime->date($game['GameSlot']['game_date']); ?></dd>
	<dt><?php __('Time'); ?></dt>
	<dd><?php echo $this->ZuluruTime->time($game['GameSlot']['game_start']); ?> - <?php echo $this->ZuluruTime->time($game['GameSlot']['display_game_end']); ?></dd>
	<dt><?php __(Configure::read('ui.field_cap')); ?></dt>
	<dd><?php echo $this->Html->link($game['GameSlot']['Field']['long_name'],
			array('controller' => 'fields', 'action' => 'view', 'field' => $game['GameSlot']['Field']['id'])); ?></dd>
	<dt><?php __('Home Team'); ?></dt>
	<dd><?php
	echo $this->Html->link($game['HomeTeam']['name'],
			array('controller' => 'teams', 'action' => 'view', 'team' => $game['HomeTeam']['id']));
	if (Configure::read('feature.shirt_colour') && array_key_exists ('shirt_colour', $game['HomeTeam'])) {
		echo ' ' . $this->element('shirt', array('colour' => $game['HomeTeam']['shirt_colour']));
	}
	?></dd>
	<dt><?php __('Away Team'); ?></dt>
	<dd><?php
	echo $this->Html->link($game['AwayTeam']['name'],
			array('controller' => 'teams', 'action' => 'view', 'team' => $game['AwayTeam']['id']));
	if (Configure::read('feature.shirt_colour') && array_key_exists ('shirt_colour', $game['AwayTeam'])) {
		echo ' ' . $this->element('shirt', array('colour' => $game['AwayTeam']['shirt_colour']));
	}
	?></dd>

</dl>