<?php $year = date('Y'); ?>
<h4>Type: Boolean</h4>
<p>The AND rule accepts a comma-separated list of two or more other rules, returning <em>true</em> if all of them are true, <em>false</em> otherwise.</p>
<p>Example:</p>
<pre>AND(
    COMPARE(ATTRIBUTE('gender') = 'Male'),
    COMPARE(ATTRIBUTE('birthdate') &lt;= '<?php echo $year - 33; ?>-12-31')
)</pre>
<p>will return <em>true</em> if the player is male and was born on or before Dec 31, <?php echo $year - 33; ?> (i.e. is a male masters player in <?php echo $year; ?>), <em>false</em> otherwise.</p>
<pre>AND(
    COMPARE(ATTRIBUTE('gender') = 'Female'),
    COMPARE(ATTRIBUTE('birthdate') &lt;= '<?php echo $year - 30; ?>-12-31')
)</pre>
<p>will return <em>true</em> if the player is female and was born on or before Dec 31, <?php echo $year - 30; ?> (i.e. is a female masters player in <?php echo $year; ?>), <em>false</em> otherwise.</p>
