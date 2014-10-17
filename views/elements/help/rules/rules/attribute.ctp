<h4>Type: Data</h4>
<p>The ATTRIBUTE rule extracts information from the player record and returns it. The name of the attribute to be returned must be in lower case and enclosed in quotes.</p>
<p>The most common attributes to use in comparisons are gender and birthdate, but any field name in the "people" table is an option.</p>
<p>The complete list is as follows. Where there is a limited list of options, they are given in parentheses; note that these are case-sensitive.</p>
<p><?php
$fields = array();
$person = ClassRegistry::init('person');
foreach (array_keys($person->_schema) as $key) {
	$include = false;

	// Check for entirely disabled features
	$feature_lookup = array(
		'has_dog' => 'dog_questions',
		'twitter_token' => 'twitter',
		'twitter_secret' => 'twitter',
		'show_gravatar' => 'gravatar',
	);
	if (!array_key_exists($key, $feature_lookup) || Configure::read("feature.{$feature_lookup[$key]}")) {
		// Deal with special cases
		if (in_array($key, array('id', 'group_id', 'status', 'publish_email'))) {
			$include = true;
		} else if ($key == 'work_ext') {
			$include = Configure::read('profile.work_phone');
		} else if (strpos($key, 'publish_') !== false) {
			$related = substr($key, 8);
			$include = Configure::read("profile.$related");
		} else {
			$include = Configure::read("profile.$key");
		}
	}

	if ($include) {
		if (strpos($key, '_id') !== false) {
			$model = Inflector::classify(substr($key, 0, strlen($key) - 3));
			$list = $person->$model->find('list');
			$options = array();
			foreach ($list as $list_key => $list_value) {
				$options[] = "'$list_key' " . __('for', true) . ' ' . __($list_value, true);
			}
		} else {
			$options = Configure::read("options.$key");
		}
		if (!empty($options)) {
			$fields[] = $key . ' (' . implode(', ', $options) . ')';
		} else {
			$fields[] = $key;
		}
	}
}
echo implode(', ', $fields);
?></p>
<p>Example:</p>
<pre>ATTRIBUTE('gender')</pre>
<p>will return either <strong>Male</strong> or <strong>Female</strong>.</p>