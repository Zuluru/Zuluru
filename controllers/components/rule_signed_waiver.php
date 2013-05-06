<?php
/**
 * Rule helper for checking whether the user has signed a required waiver.
 */

class RuleSignedWaiverComponent extends RuleComponent
{
	var $reason = 'have signed the required waiver';

	function parse($config) {
		$this->config = array_map ('trim', explode (',', $config));
		foreach ($this->config as $key => $val) {
			$this->config[$key] = trim ($val, '"\'');
		}
		if (count($this->config) >= 2) {
			$this->date = date('Y-m-d', strtotime (array_pop($this->config)));
			$model = ClassRegistry::init('Waiver');
			$model->contain(array());
			$this->waiver = $model->field('name', array('id' => $this->config[0]));
			return true;
		} else {
			return false;
		}
	}

	// Check if the user has signed the required waiver
	function evaluate($affiliate, $params, $team, $strict, $text_reason, $complete, $absolute_url) {
		if ($text_reason) {
			$this->reason = "have signed the {$this->waiver} waiver";
		} else {
			App::import('Helper', 'Html');
			$html = new HtmlHelper();
			$url = array('controller' => 'waivers', 'action' => 'sign', 'waiver' => $this->config[0], 'date' => $this->date);
			if ($absolute_url) {
				$url = $html->url($url, true);
			} else {
				$url['return'] = true;
			}
			$this->reason = $html->link("have signed the {$this->waiver} waiver", $url);
		}
		$this->redirect = array('controller' => 'waivers', 'action' => 'sign', 'waiver' => $this->config[0], 'date' => $this->date);

		if (!$strict) {
			return true;
		}

		if (is_array($params) && array_key_exists ('Waiver', $params)) {
			$matches = array_intersect($this->config, Set::extract ("/Waiver/WaiversPerson[valid_from<={$this->date}][valid_until>={$this->date}]/waiver_id", $params));
			if (!empty ($matches)) {
				return true;
			}
		}
		return false;
	}

	function query($affiliate) {
		return $this->_execute_query(
			$affiliate,
			array(
				'WaiversPerson.waiver_id' => $this->config,
				'WaiversPerson.valid_from <=' => $this->date,
				'WaiversPerson.valid_until >=' => $this->date,
			),
			array('WaiversPerson' => array(
				'table' => 'waivers_people',
				'alias' => 'WaiversPerson',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'WaiversPerson.person_id = Person.id',
			))
		);
	}

	function desc() {
		return __('have signed the waiver', true);
	}
}

?>
