<p>There are two important points to note in regard to using the rules engine for mailing lists.</p>
<h4>Relative Dates</h4>
<?php $year = date('Y'); ?>
<p><?php echo ZULURU; ?>'s mailing lists are unlike traditional mailing lists, in that membership is dynamic.
Every time a newsletter is sent, the rule for inclusion on the mailing list is re-evaluated and a new list of people to send to is generated.
When setting up mailing list rules that involve dates, then, it is usually preferable to use relative dates rather than absolute.
For example:</p>
<pre>AND(
    COMPARE(ATTRIBUTE('gender') = 'Male'),
    COMPARE(ATTRIBUTE('birthdate') &lt;= '<?php echo $year - 33; ?>-12-31')
)</pre>
<p>will find all men who are Masters age this year, but this would need to be updated annually.
If you instead use:</p>
<pre>AND(
    COMPARE(ATTRIBUTE('gender') = 'Male'),
    COMPARE(ATTRIBUTE('birthdate') &lt;= FORMAT_DATE('Dec 31 - 33 years'))
)</pre>
<p>this will always find all men who are Masters age in whatever year the newsletter is being sent.
This allows you to set up the mailing list once and re-use it for years without modification.</p>
<p>FORMAT_DATE uses the PHP strtotime function.
For more details of options you can use, see PHP's <a href="http://php.net/manual/en/datetime.formats.php">Supported Date and Time Formats</a>, in particular the Relative Formats page.</p>
<h4>Impossible Queries and Workarounds</h4>
<p>For optimal performance, when used with mailing lists, the rules engine generates a database query for each rule.
However, for technical reasons, it is not always possible to generate a query that will do what you want.
In particular, the TEAM_COUNT and LEAGUE_TEAM_COUNT rules cannot be used to find players who are not on any teams.
For example, if you try to send a newsletter to a mailing list that has:
<pre>COMPARE(TEAM_COUNT(FORMAT_DATE('today')) = '0')</pre>
you will get the following error message:</p>
<div id="flashMessage" class="error">The syntax of the mailing list rule is valid, but it is not possible to build a query which will return the expected results. See the "rules engine" help for suggestions.</div>
<p>Fortunately, there is a simple workaround. Simply negate the rule:
<pre>NOT(COMPARE(TEAM_COUNT(FORMAT_DATE('today')) > '0'))</pre>
</p>
