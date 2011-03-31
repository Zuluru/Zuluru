<?php $year = date('Y'); ?>
<p>The following rules are available for use in the rules engine. Note that rule names are accepted in upper, lower or mixed case, but arguments to rules are generally case-specific.</p>
<h2>CONSTANT</h2>
<h3>Type: Data</h3>
<p>The CONSTANT rule simply returns it's argument. It is most frequently invoked by simply specifying a quoted string.</p>
<p>Example:
<pre>CONSTANT("Male")
"Male"
'Male'</pre>
All of these will return the string <strong>Male</strong>.</p>
<h2>ATTRIBUTE</h2>
<h3>Type: Data</h3>
<p>The ATTRIBUTE rule extracts information from the player record and returns it. The name of the attribute to be returned must be in lower case and enclosed in quotes.</p>
<p>The most common attributes to use in comparisons are gender and birthdate, but any field name in the "people" table is an option.</p>
<p>Example:
<pre>ATTRIBUTE('gender')</pre>
will return either <strong>Male</strong> or <strong>Female</strong>. Note that both are capitalized.</p>
<h2>MEMBER_TYPE</h2>
<h3>Type: Data</h3>
<p>The MEMBER_TYPE rule accepts a YYYY-MM-DD formatted date and returns a string describing the highest membership in effect for the player on that date. The date must be enclosed in quotes.</p>
<p>Currently, the possible member types are "none" (they do not have a membership in effect on the given date), "intro" or "full". The membership type and valid dates are determined from the configuration of the membership events that the player has registered <strong>and paid</strong> for.</p>
<p>Example:
<pre>MEMBER_TYPE('<?php echo $year; ?>-06-01')</pre>
would return one of <strong>none</strong>, <strong>intro</strong> or <strong>full</strong>, depending on the player's registration history.</p>
<?php // TODO: Include suggestions for how to configure membership events and rules for consistency ?>
<h2>FORMAT_DATE</h2>
<h3>Type: Data</h3>
<p>The FORMAT_DATE rule accepts a date in an arbitrary format and reformats into a standard YYYY-MM-DD format useful for comparisons.</p>
<p>Example:
<pre>FORMAT_DATE('June 1, <?php echo $year; ?>')</pre>
would return <strong><?php echo $year; ?>-06-01</strong></p>
<h2>TEAM_COUNT</h2>
<h3>Type: Data</h3>
<p>The TEAM_COUNT rule accepts a YYYY-MM-DD formatted date and returns a count of how many teams the player is/was on that play/played in leagues that are/were open on this date. The date must be enclosed in quotes.</p>
<p>Only teams where the player is listed as a captain, assistant captain or regular player, and is accepted on the roster, are counted.</p>
<p>Example:
<pre>TEAM_COUNT('<?php echo $year; ?>-06-01')</pre>
would return the number of teams playing in the summer of <?php echo $year; ?> that the player is on.</p>
<h2>REGISTERED</h2>
<h3>Type: Boolean</h3>
<p>The REGISTERED rule accepts a comma-separated list of integers and returns true if the user has registered for at least one of them. Payment status is NOT checked, in order to allow people to register for multiple items and pay all at once.</p>
<p>Example:
<pre>REGISTERED(123)</pre>
will return <em>true</em> if the person has registered for event #123, <em>false</em> otherwise.
<pre>REGISTERED(1,12,123)</pre>
will return <em>true</em> if the person has registered for at least one of events #1, 12 or 123, <em>false</em> otherwise.</p>
<h2>COMPARE</h2>
<h3>Type: Boolean</h3>
<p>The COMPARE rule accepts two other rules, separated by a comparison operator, and returns the result of performing a boolean comparison of the results of executing the two rules.</p>
<p>The comparison operator must have whitespace on both sides of it.</p>
<p>Possible comparison operators are:
<ul>
<li>= (test for equality)</li>
<li>!= (test for inequality)</li>
<li>&lt; (less than)</li>
<li>&lt;= (less than or equal to)</li>
<li>&gt; (greater than)</li>
<li>&gt;= (greater than or equal to)</li>
</ul>
</p>
<p>Note that comparisons are done in a case-sensitive fashion.</p>
<p>Example:
<pre>COMPARE(ATTRIBUTE('gender') = 'Male')</pre>
will return <em>true</em> if the player is male, <em>false</em> otherwise.
<pre>COMPARE(ATTRIBUTE('gender') = 'male')</pre>
will <strong>always</strong> return <em>false</em>, because the "gender" attribute is always capitalized.
<pre>COMPARE(ATTRIBUTE('birthdate') &gt;= '<?php echo $year - 18; ?>-01-01')</pre>
will return <em>true</em> if the player was born on or after Jan 1, <?php echo $year - 18; ?> (i.e. is a junior player in <?php echo $year; ?>), <em>false</em> otherwise.
<pre>COMPARE(MEMBER_TYPE('<?php echo $year; ?>-04-01') = 'none')</pre>
will return <em>true</em> if the player does <strong>not</strong> have a paid membership that covers Apr 1, <?php echo $year; ?>, <em>false</em> otherwise.</p>
<h2>AND</h2>
<h3>Type: Boolean</h3>
<p>The AND rule accepts a comma-separated list of two or more other rules, returning <em>true</em> if all of them are true, <em>false</em> otherwise.</p>
<p>Example:
<pre>AND(
    COMPARE(ATTRIBUTE('gender') = 'Male'),
    COMPARE(ATTRIBUTE('birthdate') &lt;= '<?php echo $year - 33; ?>-12-31')
)</pre>
will return <em>true</em> if the player is male and was born on or before Dec 31, <?php echo $year - 33; ?> (i.e. is a male masters player in <?php echo $year; ?>), <em>false</em> otherwise.
<pre>AND(
    COMPARE(ATTRIBUTE('gender') = 'Female'),
    COMPARE(ATTRIBUTE('birthdate') &lt;= '<?php echo $year - 30; ?>-12-31')
)</pre>
will return <em>true</em> if the player is female and was born on or before Dec 31, <?php echo $year - 30; ?> (i.e. is a female masters player in <?php echo $year; ?>), <em>false</em> otherwise.</p>
<h2>OR</h2>
<h3>Type: Boolean</h3>
<p>The OR rule accepts a comma-separated list of two or more other rules, returning <em>true</em> if at least one of them is true, <em>false</em> otherwise.</p>
<p>Example:
<pre>OR(
    AND(
        COMPARE(ATTRIBUTE('gender') = 'Male'),
        COMPARE(ATTRIBUTE('birthdate') &lt;= '<?php echo $year - 33; ?>-12-31')
    ),
    AND(
        COMPARE(ATTRIBUTE('gender') = 'Female'),
        COMPARE(ATTRIBUTE('birthdate') &lt;= '<?php echo $year - 30; ?>-12-31')
    )
)</pre>
will return <em>true</em> if the player is male and was born on or before Dec 31, or is female and was born on or before Dec 31, <?php echo $year - 30; ?> (i.e. is a gender-specific masters player in <?php echo $year; ?>), <em>false</em> otherwise.</p>
<h2>NOT</h2>
<h3>Type: Boolean</h3>
<p>The NOT rule accepts one rule, returning <em>true</em> if that rule is false, <em>true</em> otherwise.</p>
<p>Note that this is infrequently used, as most rules are built using COMPARE, which supports negation via the != operator.</p>
<p>Example:
<pre>NOT(REGISTERED(123))</pre>
will return <em>false</em> if the person has registered for event #123, <em>true</em> otherwise.</p>
