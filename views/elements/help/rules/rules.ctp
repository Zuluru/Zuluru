<p>The following rules are available for use in the rules engine. Note that rule names are accepted in upper, lower or mixed case, but arguments to rules are generally case-specific.</p>
<?php
echo $this->element('help/topics', array(
		'section' => 'rules/rules',
		'topics' => array(
			'constant' => 'CONSTANT',
			'attribute' => 'ATTRIBUTE',
			'member_type' => 'MEMBER_TYPE',
			'format_date' => 'FORMAT_DATE',
			'team_count' => 'TEAM_COUNT',
			'registered' => 'REGISTERED',
			'compare' => 'COMPARE',
			'and' => 'AND',
			'or' => 'OR',
			'not' => 'NOT',
		),
));
?>
