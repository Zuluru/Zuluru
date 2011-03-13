<?php
/**
 * Rule helper for returning a user's membership type.
 */

class RuleMemberTypeComponent extends RuleComponent
{
	function parse($config) {
		$this->config = trim ($config, '"\'');
		return true;
	}

	// Check if the user was a member on the configured date
	function evaluate($params) {
		$date = strtotime ($this->config);
		// TODO: A better way to rank membership types that handles more than just intro and full
		$intro = false;
		if (is_array($params) && array_key_exists ('Registration', $params)) {
			foreach ($params['Registration'] as $reg) {
				if (array_key_exists('membership_begins', $reg['Event']) &&
					strtotime ($reg['Event']['membership_begins']) <= $date &&
					$date <= strtotime ($reg['Event']['membership_ends']))
				{
					if ($reg['Event']['membership_type'] == 'full') {
						return 'full';
					} else if ($reg['Event']['membership_type'] == 'intro') {
						$intro = true;
					}
				}
			}
		}

		return ($intro ? 'intro' : 'none');

/* TODO Incorporate any of this?

AND(
	compare(member_type('2010-06-01') != 'none'),
	OR(
		compare(member_type('2010-06-01') = 'full'),
		compare(team_count('2010-06-01') = '0')
	)
)

		if (!$this->player->is_member() && !$this->is_admin) {
			if(!$this->player->is_player() ) {
				return error_exit('Only registered players can be added to a team.');
			} else {
				$he = ($this->player->gender == 'Male' ? 'he' : 'she');
				$his = ($this->player->gender == 'Male' ? 'his' : 'her');
				$him = ($this->player->gender == 'Male' ? 'him' : 'her');
				$mail = l(variable_get('app_admin_name', 'Leaguerunner Administrator'),
							'mailto:' . variable_get('app_admin_email','webmaster@localhost'));
				return error_exit("Only registered players can be added to a team. {$this->player->firstname} has yet to register and pay for this year's membership.  Please contact {$this->player->firstname} to remind $him to pay for $his membership.  If $he has registered and paid for $his membership please have $him contact $mail.");
			}
		}
*/

		return 'intro';
	}

	function desc() {
		App::import('helper', 'Time');
		App::import('helper', 'ZuluruTime');
		$ZuluruTime = new ZuluruTimeHelper();
		$date = $ZuluruTime->date ($this->config);
		return __('have a membership type', true);
	}
}

?>
