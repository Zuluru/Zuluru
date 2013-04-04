<?php
$this->Html->addCrumb (__('Games', true));
$this->Html->addCrumb (__('Game', true) . ' ' . $this->data['Note']['game_id']);
$this->Html->addCrumb (__('Note', true));
if (empty($this->data['Note']['id'])) {
	$this->Html->addCrumb (__('Add', true));
} else {
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="games view">
<h2><?php __('Game Note'); ?></h2>
<dl><?php $i = 0; $class = ' class="altrow"';?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('League', true) . '/' . __('Division', true); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->element('divisions/block', array('division' => $game['Division'], 'field' => 'full_league_name')); ?>

	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Home Team'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		if ($game['Game']['home_team'] === null) {
			echo $game['Game']['home_dependency'];
			$game['HomeTeam']['Person'] = array();
		} else {
			echo $this->element('teams/block', array('team' => $game['HomeTeam']));
			if (array_key_exists ('home_dependency', $game['Game'])) {
				echo " ({$game['Game']['home_dependency']})";
			}
		}
		?>

	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Away Team'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		if ($game['Game']['away_team'] === null) {
			echo $game['Game']['away_dependency'];
			$game['AwayTeam']['Person'] = array();
		} else {
			echo $this->element('teams/block', array('team' => $game['AwayTeam']));
			if (array_key_exists ('away_dependency', $game['Game'])) {
				echo " ({$game['Game']['away_dependency']})";
			}
		}
		?>

	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Date and Time');?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		echo $this->ZuluruTime->date ($game['GameSlot']['game_date']) . ', ' .
			$this->ZuluruTime->time ($game['GameSlot']['game_start']) . '-' .
			$this->ZuluruTime->time ($game['GameSlot']['display_game_end']);
		?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Location');?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->element('fields/block', array('field' => $game['GameSlot']['Field'], 'display_field' => 'long_name')); ?>

	</dd>
</dl>
</div>

<div class="games form">
<?php
echo $this->Form->create('Note', array('url' => Router::normalize($this->here)));
if (!empty($this->data['Note']['id'])) {
	echo $this->Form->input('id');
}
echo $this->Form->hidden('Note.game_id');
echo $this->ZuluruForm->input('note', array('cols' => 70, 'class' => 'mceSimple'));
echo $this->ZuluruForm->input('visibility', array(
		'options' => array(
			VISIBILITY_PRIVATE => __('Only I will be able to see this', true),
			VISIBILITY_CAPTAINS => __('Only I and the captains of our team', true),
			VISIBILITY_TEAM => __('Everyone on my team', true),
		),
));
if (!empty($this->data['Note']['id'])) {
	echo $this->Html->para(null, __('Emails are NOT sent to others when you edit an existing note.', true));
} else {
	echo $this->Html->para(null, __('Everyone else that is allowed to see this note will be sent an email informing them. This is a good way to communicate with your teams.', true));
}
echo $this->Html->para(null, __('Under no circumstance will the players on the other team, or anyone else, be able to see this.', true));
echo $this->Form->end(__('Submit', true));
?>
</div>
<?php if (Configure::read('feature.tiny_mce')) $this->TinyMce->editor('simple'); ?>
