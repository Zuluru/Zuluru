<?php

$spirit = $this->element ("spirit/input/{$spirit_obj->render_element}",
	compact ('team_id', 'created_team_id', 'game', 'spirit_obj'));

if ($game['League']['numeric_sotg']) {
	if (!isset ($opts))
		$opts = array();
	else if  (!is_array($opts))
		$opts = array($opts);
	$opts = array_merge(array(
			'size' => 2,
			'label' => 'Spirit',
			'div' => false,
			'after' => '&nbsp;' . sprintf (__('(between 0 and %d)', true), $spirit_obj->max()),
	), $opts);

	$spirit .= $this->Form->input("SpiritEntry.$team_id.entered_sotg", $opts);
	// TODOCSS: Move the suggest box next to the input
	$spirit .= $this->Html->tag('div',
		$this->Html->tag('ul',
			$this->Html->tag('li',
				$this->Html->link('Suggest', '#', array(
						'onclick' => "suggestSpirit('$team_id'); return false;",
	)))), array('class' => 'actions'));
	$this->ZuluruHtml->script ('spirit', array('inline' => false));

	// Don't show this when submitting scores, just when editing. We don't need
	// to check admin/coordinator permissions, as that's already been done.
	if ($this->action == 'edit') {
		$checked = false;
		if (array_key_exists ($team_id, $game['SpiritEntry']) &&
			array_key_exists ('score_entry_penalty', $game['SpiritEntry'][$team_id]) &&
			$game['SpiritEntry'][$team_id]['score_entry_penalty'] != 0)
		{
			$checked = true;
		} else if (!Game::_is_finalized($game) &&
			!array_key_exists ($team_id, $game['ScoreEntry']))
		{
			$checked = true;
		}
		$spirit .= $this->Form->input("SpiritEntry.$team_id.score_entry_penalty", array(
				'type' => 'checkbox',
				'label' => __('Assign penalty for missing score entry?', true),
				'value' => -Configure::read('scoring.missing_score_spirit_penalty'),
				'checked' => $checked,
		));
	}
}

if ($spirit) {
	if ($game['HomeTeam']['id'] == $team_id) {
		$opponent_name = $game['HomeTeam']['name'];
	} else {
		$opponent_name = $game['AwayTeam']['name'];
	}
	echo $this->Html->tag ('fieldset',
		$this->Html->tag ('legend', __('Spirit assigned to', true) . ' ' . $opponent_name) . $spirit,
		array('class' => 'spirit'));
}

?>
