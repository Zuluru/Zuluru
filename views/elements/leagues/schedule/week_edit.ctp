<?php
$published = array_unique (Set::extract ("/GameSlot[game_date=$date]/../published", $division['Game']));
if (count ($published) != 1 || $published[0] == 0) {
	$published = false;
} else {
	$published = true;
}

$teams = Set::combine ($division['Team'], '{n}.id', '{n}.name');
natcasesort ($teams);
?>

<tr>
	<th colspan="4"><a name="<?php echo $date; ?>"><?php echo $this->ZuluruTime->fulldate($date); ?></a></th>
	<th colspan="2" class="actions splash_action">
	<?php echo $this->ZuluruHtml->iconLink('field_24.png',
			array('action' => 'slots', 'division' => $division['Division']['id'], 'date' => $date),
			array('alt' => __(Configure::read('sport.fields_cap'), true), 'title' => sprintf(__('Available %s', true), __(Configure::read('sport.fields_cap'), true)))); ?>
	</th>
</tr>
<tr>
	<th><?php if ($is_tournament): ?><?php __('Game'); ?><?php endif; ?></th>
	<th colspan="2"><?php printf(__('Time/%s', true), __(Configure::read('sport.field_cap'), true)); ?></th>
	<th><?php __('Home'); ?></th>
	<th><?php __('Away'); ?></th>
	<th></th>
</tr>

<?php
foreach ($division['Game'] as $game):
	if ($date != $game['GameSlot']['game_date']) {
		continue;
	}
	Game::_readDependencies($game);

	if (empty ($this->data)) {
		$data = $game;
	} else {
		$data = array_shift (Set::extract("/Game[id={$game['id']}]/.", $this->data));
	}
?>

<tr<?php if (!$game['published']) echo ' class="unpublished"'; ?>>
	<td><?php if ($is_tournament): ?><?php
	if (!empty($data['name'])) {
		echo $data['name'];
	}
	?><?php endif; ?></td>
	<td colspan="2"><?php
	echo $this->Form->hidden ("Game.{$game['id']}.id", array('value' => $game['id']));
	echo $this->Form->hidden ("Game.{$game['id']}.GameSlot.game_id", array('value' => $game['id']));
	echo $this->Form->input ("Game.{$game['id']}.GameSlot.id", array(
			'div' => false,
			'label' => false,
			'options' => $slots,
			'empty' => '---',
			'selected' => $data['GameSlot']['id'],
	));
	?></td>
	<td><?php
	if ($is_tournament) {
		if (empty ($game['HomeTeam'])) {
			if (array_key_exists ('home_dependency', $game)) {
				echo $game['home_dependency'];
			} else {
				__('Unassigned');
			}
		} else {
			echo $this->element('teams/block', array('team' => $game['HomeTeam'], 'options' => array('max_length' => 16)));
		}
	} else {
		echo $this->Form->input ("Game.{$game['id']}.home_team", array(
				'div' => false,
				'label' => false,
				'options' => $teams,
				'empty' => '---',
				'selected' => $data['home_team'],
		));
	}
	?></td>
	<td><?php
	if ($is_tournament) {
		if (empty ($game['AwayTeam'])) {
			if (array_key_exists ('away_dependency', $game)) {
				echo $game['away_dependency'];
			} else {
				__('Unassigned');
			}
		} else {
			echo $this->element('teams/block', array('team' => $game['AwayTeam'], 'options' => array('max_length' => 16)));
		}
	} else {
		echo $this->Form->input ("Game.{$game['id']}.away_team", array(
				'div' => false,
				'label' => false,
				'options' => $teams,
				'empty' => '---',
				'selected' => $data['away_team'],
		));
	}
	?></td>
	<td></td>
</tr>

<?php
endforeach;
?>

<tr>
	<td colspan="4"><?php
	echo $this->Form->input ('publish', array(
			'label' => __('Set as published for player viewing?', true),
			'type' => 'checkbox',
			'checked' => $published,
	));
	if (!$is_tournament) {
		echo $this->Form->input ('double_header', array(
				'label' => __('Allow double-headers?', true),
				'type' => 'checkbox',
				'checked' => false,
		));
	}
	?></td>
	<td class="actions splash_action">
		<?php echo $this->Form->hidden ('edit_date', array('value' => $date)); ?>
		<?php echo $this->Form->submit (__('Reset', true), array('type' => 'reset', 'div' => false)); ?>
		<?php echo $this->Form->submit (__('Submit', true), array('div' => false)); ?>
	</td>
</tr>
