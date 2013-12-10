<?php
if (isset($error)) {
	echo $this->Html->scriptBlock ("alert('$error');");
} else {
	$team_names = array(
		$game['HomeTeam']['id'] => $game['HomeTeam']['name'],
		$game['AwayTeam']['id'] => $game['AwayTeam']['name']
	);

	if (!empty($game['Division']['League']['StatType'])) {
		// Build the roster options
		$roster = array();
		$has_numbers = false;
		foreach (array('HomeTeam', 'AwayTeam') as $key) {
			$team = $game[$key];
			$numbers = array_unique(Set::extract('/Person/TeamsPerson/number', $team));
			if (Configure::read('feature.shirt_numbers') && count($numbers) > 1 && $numbers[0] !== null) {
				$has_numbers = true;
			}
			foreach ($team['Person'] as $person) {
				$option = $person['full_name'];
				if ($has_numbers && $person['TeamsPerson']['number'] !== null && $person['TeamsPerson']['number'] !== '') {
					$option = "{$person['TeamsPerson']['number']} $option";
					if ($person['TeamsPerson']['number'] < 10) {
						$option = " $option";
					}
				}
				$roster[$team['id']][$person['id']] = $option;
			}
			asort($roster[$team['id']]);
		}
	}

	echo $this->Html->tag('table',
			$this->Html->tag('tbody',
				$this->element('games/edit_boxscore_line', array(
					'detail' => $saved['ScoreDetail'],
					'year' => $this->data['AddDetail']['created']['year'],
					'month' => $this->data['AddDetail']['created']['month'],
					'day' => $this->data['AddDetail']['created']['day'],
					'team_names' => $team_names,
					'roster' => $roster,
				))
			),
			array('id' => 'new_row')
	);
	echo $this->Html->scriptBlock ("jQuery('#add_row').before(jQuery('#new_row tbody').html());");
}

// Output the event handler code for the links
echo $this->Js->writeBuffer();
?>
