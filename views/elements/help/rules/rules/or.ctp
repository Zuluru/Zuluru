<?php $year = date('Y'); ?>
<h4>Type: Boolean</h4>
<p>The OR rule accepts a comma-separated list of two or more other rules, returning <em>true</em> if at least one of them is true, <em>false</em> otherwise.</p>
<p>Example:</p>
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
<p>will return <em>true</em> if the player is male and was born on or before Dec 31, or is female and was born on or before Dec 31, <?php echo $year - 30; ?> (i.e. is a gender-specific masters player in <?php echo $year; ?>), <em>false</em> otherwise.</p>
