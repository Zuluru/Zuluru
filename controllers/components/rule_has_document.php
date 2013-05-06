<?php
/**
 * Rule helper for checking whether the user has a required document.
 */

class RuleHasDocumentComponent extends RuleComponent
{
	var $reason = 'have uploaded the required document';

	function parse($config) {
		$this->config = array_map ('trim', explode (',', $config));
		foreach ($this->config as $key => $val) {
			$this->config[$key] = trim ($val, '"\'');
			$model = ClassRegistry::init('UploadType');
			$model->contain(array());
			$this->document = $model->field('name', array('id' => $this->config[0]));
		}
		return (count($this->config) == 2);
	}

	// Check if the user has uploaded the required document
	function evaluate($affiliate, $params, $team, $strict, $text_reason, $complete, $absolute_url) {
		$matches = Set::extract ("/Upload[type_id={$this->config[0]}]", $params);
		$unapproved = Set::extract ('/Upload[approved=0]', $matches);

		if (empty($unapproved)) {
			if ($text_reason) {
				$this->reason = "have uploaded the {$this->document}";
			} else {
				App::import('Helper', 'Html');
				$html = new HtmlHelper();
				$url = array('controller' => 'people', 'action' => 'document_upload', 'type' => $this->config[0]);
				if ($absolute_url) {
					$url = $html->url($url, true);
				} else {
					$url['return'] = true;
				}
				$this->reason = $html->link("have uploaded the {$this->document}", $url);
			}
		} else {
			$this->reason = "wait until your {$this->document} is approved";
		}

		if (!$strict) {
			return true;
		}

		if (is_array($params) && array_key_exists ('Upload', $params)) {
			$date = date('Y-m-d', strtotime ($this->config[1]));
			$matches = Set::extract ("/Upload[type_id={$this->config[0]}][valid_from<=$date][valid_until>=$date]", $params);
			if (!empty ($matches)) {
				return true;
			}
		}
		return false;
	}

	function query($affiliate) {
		$date = date('Y-m-d', strtotime ($this->config[1]));
		return $this->_execute_query(
			$affiliate,
			array(
				'Upload.type_id' => $this->config[0],
				'Upload.approved' => true,
				'Upload.valid_from <=' => $date,
				'Upload.valid_until >=' => $date,
			),
			array('Upload' => array(
				'table' => 'uploads',
				'alias' => 'Upload',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'Upload.person_id = Person.id',
			))
		);
	}

	function desc() {
		return __('have the document', true);
	}
}

?>
