<?php
$prefix = "SpiritEntry.$team_id";
if (array_key_exists ($team_id, $game['SpiritEntry'])) {
	echo $this->Form->hidden ("$prefix.id", array ('value' => $game['SpiritEntry'][$team_id]['id']));
}
echo $this->element('formbuilder/input', array('prefix' => $prefix, 'questions' => $spirit_obj->questions));

if (Configure::read('scoring.most_spirited') && $game['Division']['most_spirited'] != 'never'):
	if ($this->action != 'edit'):
?>
<div id="MostSpiritedWrapper">
<?php
		if ($this->action != 'edit' && $game['Division']['most_spirited'] == 'optional') {
			echo $this->Form->input("$prefix.has_most_spirited", array(
					'type' => 'checkbox',
					'value' => '1',
					'label' => 'I want to nominate a most spirited player',
			));
		}
?>
<div class="MostSpiritedDetails">
<p>You may select one person from the list below<?php
		if ($game['Division']['most_spirited'] == 'always') {
			echo ', if you think they deserve to be nominated as most spirited player';
		}
?>.</p>

<?php
	endif;

	// Build list of most spirited options
	$players = array();
	$player_roles = Configure::read('playing_roster_roles');

	foreach ($team['Person'] as $person) {
		$block = $this->element('people/block', array('person' => $person, 'link' => false));
		if (!in_array($person['TeamsPerson']['role'], $player_roles)) {
			$block .= ' (' . __('substitute', true) . ')';
		}
		$players[$person['id']] = $block;
	}

	echo $this->Form->input("$prefix.most_spirited", array(
			'type' => 'radio',
			'options' => $players,
	));

	if ($this->action != 'edit'):
?>
</div>
</div>
<?php
	endif;
endif;
?>
