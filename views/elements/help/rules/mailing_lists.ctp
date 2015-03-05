<p><?php __('There are two important points to note in regard to using the rules engine for mailing lists.'); ?></p>
<h4><?php __('Relative Dates'); ?></h4>
<?php $year = date('Y'); ?>
<p><?php printf(__('%s\'s mailing lists are unlike traditional mailing lists, in that membership is dynamic. Every time a newsletter is sent, the rule for inclusion on the mailing list is re-evaluated and a new list of people to send to is generated. When setting up mailing list rules that involve dates, then, it is usually preferable to use relative dates rather than absolute. For example:', true), ZULURU); ?></p>
<pre>AND(
    COMPARE(ATTRIBUTE('gender') = 'Male'),
    COMPARE(ATTRIBUTE('birthdate') &lt;= '<?php echo $year - 33; ?>-12-31')
)</pre>
<p><?php __('will find all men who are Masters age this year, but this would need to be updated annually. If you instead use:'); ?></p>
<pre>AND(
    COMPARE(ATTRIBUTE('gender') = 'Male'),
    COMPARE(ATTRIBUTE('birthdate') &lt;= FORMAT_DATE('Dec 31 - 33 years'))
)</pre>
<p><?php __('this will always find all men who are Masters age in whatever year the newsletter is being sent. This allows you to set up the mailing list once and re-use it for years without modification.'); ?></p>
<p><?php
printf(__('%s uses the PHP strtotime function. For more details of options you can use, see PHP\'s %s, in particular the Relative Formats page.', true),
	'FORMAT_DATE',
	$this->Html->link(__('Supported Date and Time Formats', true), 'http://php.net/manual/en/datetime.formats.php')
);
?></p>
<h4><?php __('Impossible Queries and Workarounds'); ?></h4>
<p><?php printf(__('For optimal performance, when used with mailing lists, the rules engine generates a database query for each rule. However, for technical reasons, it is not always possible to generate a query that will do what you want. In particular, the %s and %s rules cannot be used to find players who are not on any teams. For example, if you try to send a newsletter to a mailing list that has:', true), 'TEAM_COUNT', 'LEAGUE_TEAM_COUNT'); ?>
<pre>COMPARE(TEAM_COUNT('today') = '0')</pre>
<?php __('you will get the following error message:'); ?></p>
<div id="flashMessage" class="error"><?php __('The syntax of the mailing list rule is valid, but it is not possible to build a query which will return the expected results. See the "rules engine" help for suggestions.'); ?></div>
<p><?php __('Fortunately, there is a simple workaround. Simply negate the rule:'); ?>
<pre>NOT(COMPARE(TEAM_COUNT('today') > '0'))</pre>
</p>
