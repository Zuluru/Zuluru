<?php
$published = array_unique (Set::extract ("/GameSlot[game_date=$date]/../published", $league['Game']));
if (count ($published) != 1 || $published[0] == 0) {
	$published = false;
} else {
	$published = true;
}

$teams = Set::combine ($league['Team'], '{n}.id', '{n}.name');
natcasesort ($teams);
?>

<tr>
	<th colspan="5"><a name="<?php echo $date; ?>"><?php echo $this->ZuluruTime->fulldate($date); ?></a></th>
</tr>
<tr>
	<th><?php __('Time/Field'); ?></th>
	<th><?php __('Home'); ?></th>
	<th><?php __('Away'); ?></th>
	<th></th>
</tr>

<?php
foreach ($league['Game'] as $game):
	if ($date != $game['GameSlot']['game_date']) {
		continue;
	}
?>

<tr<?php if (!$game['published']) echo ' class="unpublished"'; ?>>
	<td><?php
	echo $this->Form->hidden ("Game.{$game['id']}.id", array('value' => $game['id']));
	echo $this->Form->hidden ("Game.{$game['id']}.GameSlot.game_id", array('value' => $game['id']));
	echo $this->Form->input ("Game.{$game['id']}.GameSlot.id", array(
			'div' => false,
			'label' => false,
			'options' => $slots,
			'empty' => '---',
			'selected' => $game_slot[$game['id']],
	));
	?></td>
	<td><?php
	echo $this->Form->input ("Game.{$game['id']}.home_team", array(
			'div' => false,
			'label' => false,
			'options' => $teams,
			'empty' => '---',
			'selected' => $game['home_team'],
	));
	?></td>
	<td><?php
	echo $this->Form->input ("Game.{$game['id']}.away_team", array(
			'div' => false,
			'label' => false,
			'options' => $teams,
			'empty' => '---',
			'selected' => $game['away_team'],
	));
	?></td>
	<td></td>
</tr>

<?php
endforeach;
?>

<tr>
	<td colspan="3"><?php
	echo $this->Form->input ('publish', array(
			'label' => __('Set as published for player viewing?', true),
			'type' => 'checkbox',
			'checked' => $published,
	));
	echo $this->Form->input ('double_header', array(
			'label' => __('Allow double-headers?', true),
			'type' => 'checkbox',
			'checked' => false,
	));
	?></td>
	<td class="actions splash_action">
		<?php echo $this->Form->hidden ('edit_date', array('value' => $date)); ?>
		<?php echo $this->Form->submit (__('Reset', true), array('type' => 'reset', 'div' => false)); ?>
		<?php echo $this->Form->submit (__('Submit', true), array('div' => false)); ?>
	</td>
</tr>
