<?php
$return = array();
foreach ($games as $game) {
	$data = array(
		'gameID' => $game['Game']['id'],
		'leagueID' => $game['Division']['League']['id'],
		'leagueName' => $game['Division']['League']['full_name'],
		'divisionID' => $game['Division']['id'],
		'divisionName' => $game['Division']['name'],
		'divisionLongName' => $game['Division']['full_league_name'],
		'gameDate' => $this->ZuluruTime->date ($game['GameSlot']['game_date']),
		'gameStartTime' => $this->ZuluruTime->time ($game['GameSlot']['game_start']),
		'gameStartTimestamp' => strtotime("{$game['GameSlot']['game_date']} {$game['GameSlot']['game_start']}"),
		'gameEndTime' => $this->ZuluruTime->time ($game['GameSlot']['display_game_end']),
		'gameEndTimestamp' => strtotime("{$game['GameSlot']['game_date']} {$game['GameSlot']['display_game_end']}"),
		'facilityID' => $game['GameSlot']['Field']['Facility']['id'],
		'facilityName' => $game['GameSlot']['Field']['Facility']['name'],
		'facilityCode' => $game['GameSlot']['Field']['Facility']['code'],
		'fieldID' => $game['GameSlot']['Field']['id'],
		'fieldNum' => $game['GameSlot']['Field']['num'],
	);
	foreach (array('home' => 'Home', 'away' => 'Away') as $type => $key) {
		if ($game['Game']["{$type}_team"] === null) {
			if (array_key_exists ("{$type}_dependency", $game['Game'])) {
				$data["{$type}TeamName"] = $game['Game']["{$type}_dependency"];
			} else {
				$data["{$type}TeamName"] = __('Unassigned', true);
			}
		} else {
			$team = $game["{$key}Team"];
			$data = array_merge($data, array(
				"{$type}TeamID" => $team['id'],
				"{$type}TeamName" => $team['name'],
				"{$type}TeamColour" => $team['shirt_colour'],
			));
			if (Configure::read('feature.shirt_colour') && array_key_exists ('shirt_colour', $team)) {
				$data["{$type}TeamShirtIcon"] = $this->element('shirt', array('colour' => $team['shirt_colour']));
			}
		}
	}
	$return[] = $data;
}
echo json_encode($return);
?>
