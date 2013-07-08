<?php

/**
 * Implementation of the game callback for the "league champion" badge.
 */
class BadgeLeagueChampionComponent extends Object
{
	function applicable($game, $team_id) {
		if (Game::_is_finalized($game) && $game['Game']['type'] == BRACKET_GAME && $game['Game']['name'] == '1st') {
			if ($game['Game']['home_team'] == $team_id && $game['Game']['home_score'] > $game['Game']['away_score']) {
				return true;
			}
			if ($game['Game']['away_team'] == $team_id && $game['Game']['away_score'] > $game['Game']['home_score']) {
				return true;
			}
		}
		return false;
	}
}

?>