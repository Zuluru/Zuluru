<?php $year = date('Y'); ?>
<h4>Type: Boolean</h4>
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
<p>Example:</p>
<pre>COMPARE(ATTRIBUTE('gender') = 'Male')</pre>
<p>will return <em>true</em> if the player is male, <em>false</em> otherwise.</p>
<pre>COMPARE(ATTRIBUTE('gender') = 'male')</pre>
<p>will <strong>always</strong> return <em>false</em>, because the "gender" attribute is always capitalized.</p>
<pre>COMPARE(ATTRIBUTE('birthdate') &gt;= '<?php echo $year - 18; ?>-01-01')</pre>
<p>will return <em>true</em> if the player was born on or after Jan 1, <?php echo $year - 18; ?> (i.e. is a junior player in <?php echo $year; ?>), <em>false</em> otherwise.</p>
<pre>COMPARE(MEMBER_TYPE('<?php echo $year; ?>-04-01') = 'none')</pre>
<p>will return <em>true</em> if the player does <strong>not</strong> have a paid membership that covers Apr 1, <?php echo $year; ?>, <em>false</em> otherwise.</p>
