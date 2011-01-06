<?php
$this->Html->addCrumb (__('Players', true));
// TODO: simulate the name virtual field
$this->Html->addCrumb ("{$this->data['Person']['first_name']} {$this->data['Person']['last_name']}");
$this->Html->addCrumb (__('Edit', true));

$short = Configure::read('organization.short_name');
?>

<p>Note that email and phone publish settings below only apply to regular players. Captains will always have access to view the phone numbers and email addresses of their confirmed players. All Team Captains will also have their email address viewable by other players.</p>
<p>If you have concerns about the data <?php echo $short; ?> collects, please see our <strong><a href="<?php echo Configure::read('urls.privacy_policy'); ?>" target="_new">Privacy Policy</a>.</strong></p>

<div class="people form">
<?php echo $this->Form->create('Person', array('url' => $this->here));?>
	<fieldset>
 		<legend><?php __('Identity'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('first_name', array(
			'after' => $this->Html->para (null, __('First (and, if desired, middle) name.', true)),
		));
		echo $this->Form->input('last_name');
		if (!Configure::read('feature.manage_accounts')) {
			$admin = Configure::read('email.admin_email');
			echo $this->Form->input('user_name', array(
				'disabled' => 'true',
				'after' => $this->Html->para (null, __('To change this, please email your existing user name and preferred new user name to ', true) . $this->Html->link ($admin, "mailto:$admin") . '.'),
			));
		} else {
			echo $this->Form->input('user_name');
		}
		echo $this->Form->input('gender', array(
			'type' => 'select',
			'empty' => '---',
			'options' => Configure::read('options.gender'),
		));
	?>
	</fieldset>
	<fieldset>
 		<legend><?php __('Online Contact'); ?></legend>
	<?php
		if (!Configure::read('feature.manage_accounts')) {
			$profile_edit = sprintf (Configure::read('urls.profile_edit'), $id);
			$profile_link = $this->Html->link (Configure::read('feature.manage_name') . ' ' . __('profile', true), $profile_edit);
			echo $this->Form->input('email', array(
				'disabled' => 'true',
				'after' => $this->Html->para (null, __('To change this, edit your', true) . ' ' . $profile_link),
			));
		} else {
			echo $this->Form->input('email');
		}
		echo $this->Form->input('publish_email', array(
			'label' => __('Allow other players to view my email address', true),
		));
	?>
	</fieldset>
	<fieldset>
 		<legend><?php __('Street Address'); ?></legend>
	<?php
		echo $this->Form->input('addr_street', array(
			'label' => __('Street and Number', true),
			'after' => $this->Html->para (null, __('Number, street name, and apartment number if necessary.', true)),
		));
		echo $this->Form->input('addr_city', array(
			'label' => __('City', true),
			'after' => $this->Html->para (null, __('Name of city.', true)),
		));
		echo $this->Form->input('addr_prov', array(
			'label' => __('Province', true),
			'type' => 'select',
			'empty' => '---',
			'options' => $provinces,
			'after' => $this->Html->para (null, __('Select a province/state from the list', true)),
		));
		echo $this->Form->input('addr_country', array(
			'label' => __('Country', true),
			'type' => 'select',
			'empty' => '---',
			'options' => $countries,
			'after' => $this->Html->para (null, __('Select a country from the list.', true)),
		));
		echo $this->Form->input('addr_postalcode', array(
			'label' => __('Postal Code', true),
			'after' => $this->Html->para (null, __("Please enter a correct postal code matching the address above. $short uses this information to help locate new fields near its members.", true)),
		));
	?>
	</fieldset>
	<fieldset>
 		<legend><?php __('Telephone Numbers'); ?></legend>
	<?php
		echo $this->Form->input('home_phone', array(
			'after' => $this->Html->para (null, __('Enter your home telephone number. If you have only a mobile phone, enter that number both here and below.', true)),
		));
		echo $this->Form->input('publish_home_phone', array(
			'label' => __('Allow other players to view home number', true),
		));
		echo $this->Form->input('work_phone', array(
			'after' => $this->Html->para (null, __('Enter your work telephone number (optional).', true)),
		));
		echo $this->Form->input('work_ext', array(
			'label' => 'Work Extension',
			'after' => $this->Html->para (null, __('Enter your work extension (optional).', true)),
		));
		echo $this->Form->input('publish_work_phone', array(
			'label' => __('Allow other players to view work number', true),
		));
		echo $this->Form->input('mobile_phone', array(
			'after' => $this->Html->para (null, __('Enter your cell or pager number (optional).', true)),
		));
		echo $this->Form->input('publish_mobile_phone', array(
			'label' => __('Allow other players to view mobile number', true),
		));
	?>
	</fieldset>
	<?php if ($is_admin) : ?>
	<fieldset>
 		<legend><?php __('Account Information'); ?></legend>
	<?php
		echo $this->Form->input('group_id', array(
			'label' => __('Account Type', true),
			'type' => 'select',
			'empty' => '---',
			'options' => $groups,
		));
		echo $this->Form->input('status', array(
			'type' => 'select',
			'empty' => '---',
			'options' => Configure::read('options.record_status'),
		));
	?>
	</fieldset>
	<?php endif; ?>
	<fieldset>
 		<legend><?php __('Player and Skill Information'); ?></legend>
	<?php
		echo $this->Form->input('skill_level', array(
			'type' => 'select',
			'empty' => '---',
			'options' => Configure::read('options.skill'),
			'after' => $this->Html->para(null, __('Please use the questionnaire to ', true) . $this->Html->link (__('calculate your rating', true), '#', array('onclick' => 'dorating(); return false;')) . '.'),
		));
		echo $this->Form->input('year_started', array(
			'type' => 'select',
			'options' => $this->Form->__generateOptions('year', array(
					'min' => Configure::read('options.year.started.min'),
					'max' => Configure::read('options.year.started.max'),
					'order' => 'desc'
			)),
			'empty' => '---',
			'after' => $this->Html->para(null, 'The year you started playing Ultimate in <strong>this</strong> league.'),
		));
		echo $this->Form->input('birthdate', array(
			'minYear' => Configure::read('options.year.born.min'),
			'maxYear' => Configure::read('options.year.born.max'),
			'after' => $this->Html->para(null, __('Please enter a correct birthdate; having accurate information is important for insurance purposes.', true)),
		));
		echo $this->Form->input('height', array(
			'size' => 6,
			'after' => $this->Html->para(null, __('Please enter your height in inches (5 feet is 60 inches; 6 feet is 72 inches). This is used to help generate even teams for hat leagues.', true)),
		));
		echo $this->Form->input('shirt_size', array(
			'type' => 'select',
			'empty' => '---',
			'options' => Configure::read('options.shirt_size'),
		));
		if (Configure::read('feature.dog_questions')) {
			echo $this->Form->input('has_dog');
		}
		echo $this->Form->input('willing_to_volunteer', array(
			'label' => __("Can $short contact you about volunteering?", true),
		));
		echo $this->Form->input('contact_for_feedback', array(
			'label' => __("From time to time, $short would like to contact members with information on our programs and to solicit feedback. Can $short contact you in this regard?"),
		));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>

<?php // TODO Clean this up, the JS is very ugly ?>
<div id="rating" class="form" title="Zuluru Player Rating">
<script language='javascript'>

// function to do the ranking
function doranking() {
	// start with null answer values
	var q1 = null;
	var q2 = null;
	var q3 = null;
	var q4 = null;
	var q5 = null;
	var q6 = null;
	var q7 = null;
	var q8 = null;
	var q9 = null;
	var q10 = null;

	// these questions have 5 answers
	for (i = 0; i<5; i++) {
		if (document.ranking.q1[i].checked) {
			q1 = parseInt(document.ranking.q1[i].value, 10);
		}
		if (document.ranking.q3[i].checked) {
			q3 = parseInt(document.ranking.q3[i].value, 10);
		}
		if (document.ranking.q4[i].checked) {
			q4 = parseInt(document.ranking.q4[i].value, 10);
		}
		if (document.ranking.q5[i].checked) {
			q5 = parseInt(document.ranking.q5[i].value, 10);
		}
		if (document.ranking.q6[i].checked) {
			q6 = parseInt(document.ranking.q6[i].value, 10);
		}
		if (document.ranking.q7[i].checked) {
			q7 = parseInt(document.ranking.q7[i].value, 10);
		}
		if (document.ranking.q8[i].checked) {
			q8 = parseInt(document.ranking.q8[i].value, 10);
		}
		if (document.ranking.q9[i].checked) {
			q9 = parseInt(document.ranking.q9[i].value, 10);
		}
	}
	// this question has 7 answers
	for (i = 0; i<7; i++) {
		if (document.ranking.q2[i].checked) {
			q2 = parseInt(document.ranking.q2[i].value, 10);
		}
	}
	// this question has 4 answers
	for (i = 0; i<4; i++) {
		if (document.ranking.q10[i].checked) {
			q10 = parseInt(document.ranking.q10[i].value, 10);
		}
	}

	// check for skipped questions and show error:
	if (q1 == null) {
		alert("Please choose an answer for question number 1");
		exit();
	}
	if (q2 == null) {
		alert("Please choose an answer for question number 2");
		exit();
	}
	if (q3 == null) {
		alert("Please choose an answer for question number 3");
		exit();
	}
	if (q4 == null) {
		alert("Please choose an answer for question number 4");
		exit();
	}
	if (q5 == null) {
		alert("Please choose an answer for question number 5");
		exit();
	}
	if (q6 == null) {
		alert("Please choose an answer for question number 6");
		exit();
	}
	if (q7 == null) {
		alert("Please choose an answer for question number 7");
		exit();
	}
	if (q8 == null) {
		alert("Please choose an answer for question number 8");
		exit();
	}
	if (q9 == null) {
		alert("Please choose an answer for question number 9");
		exit();
	}
	if (q10 == null) {
		alert("Please choose an answer for question number 10");
		exit();
	}

	// CALCULATE THE RESULTS:

	// this equation (after rounding) gives ranking between 0.5 and 9.5
	var therank = 0.21429*(q1+q2+q3+q4+q5+q6+q7+q8+q9+q10) + 5;

	therank = therank*10;	// slide the decimal over
	therank = Math.round(therank);	// round it to nearest int
	therank = therank/10;	// slide the decimal back

	// now round to an integer (1 to 10):
	therank = Math.round(therank);

	// put the result into the text box
	$("#PersonSkillLevel").val(therank);
}

</script>

<p>Fill out this questionnaire and then click "Calculate" below to
figure out skill level you should use in Zuluru.</p>
<p>The questionnaire is divided into Skill and Experience questions.	Answer
each as honestly as possible, and the resulting Zuluru ranking should
be fairly accurate.</p>
<p>The calculated value will be entered on the Zuluru account editing form.</p>

<form name='ranking'>

<h2>Skill Questions:</h2>

<b>1) Compared to other players of the same sex as you, would you consider yourself:</b><br>
<input type="radio" name="q1" value="-2">One of the slowest</input><br>
<input type="radio" name="q1" value="-1">Slower than most</input><br>
<input type="radio" name="q1" value="0">Average speed</input><br>
<input type="radio" name="q1" value="1">Faster than most</input><br>
<input type="radio" name="q1" value="2">One of the fastest</input><br>

<br>

<b>2) How would you describe your throwing skills?</b><br>
<input type="radio" name="q2" value="-3">just learning, only backhand throw, no forehand</input><br>
<input type="radio" name="q2" value="-2">can make basic throws, perhaps weaker forehand, some distance and accuracy, nervous when handling the disc</input><br>
<input type="radio" name="q2" value="-1">basic throws (backhand and forehand), some distance and accuracy, not very consistent, somewhat intimidated when handling the disc, can handle on most lower-tier teams</input><br>
<input type="radio" name="q2" value="0">good basic throws (backhand and forehand), good distance and accuracy, fairly consistent, relatively comfortable when handling disc, can handle on most lower to mid-tier teams</input><br> 
<input type="radio" name="q2" value="1">very good basic throws, know some other kinds of throws, very good distance and accuracy, usually consistent quality throws, confident when handling the disc, can handle on most mid to upper-tier teams</input><br>
<input type="radio" name="q2" value="2">all kinds of throws, excellent distance and accuracy, not prone to errors of judgment, can handle on most top-tier and lower-competitive teams</input><br>
<input type="radio" name="q2" value="3">all kinds of throws, very rarely make a bad throw, excellent distance, near perfect accuracy, epitome of reliability, can handle on an elite team (mid-highly competitive team)</input><br>
 
<br>

<b>3) How would you rate your catching skills?</b><br>
<input type="radio" name="q3" value="-2">can make basic catches if they're straight to me, still learning to judge the flight path of the disc</input><br>
<input type="radio" name="q3" value="-1">can make basic catches, sometimes have difficulty judging the flight path of the disc</input><br>
<input type="radio" name="q3" value="0">can make most catches, good at judging the flight path of the disc, not likely to attempt a layout</input><br>
<input type="radio" name="q3" value="1">can catch almost everything (high, low, to the side), rarely misread the disc, will layout if necessary</input><br>
<input type="radio" name="q3" value="2">catch absolutely everything thrown towards me, and most of the swill that isn't</input><br>

<br>

<b>4) With respect to playing defense, you:</b><br>
<input type="radio" name="q4" value="-2">understand some basics, and are learning how to read the play, no/limited experience with defense strategies</input><br>
<input type="radio" name="q4" value="-1">know the basics, but you're sometimes behind the play, learned a bit about man defense strategies</input><br>
<input type="radio" name="q4" value="0">can stay with the play and sometimes make the D, understand the basics of man & zone style defense strategies </input><br>
<input type="radio" name="q4" value="1">can read and anticipate the play and get in position to increase the chances of make the D, comfortable with both man/zone style defense strategies</input><br>
<input type="radio" name="q4" value="2">always think ahead of the play and can often make the D, proficient at both man/zone style defense strategies and maybe know a few more</input><br>

<br>

<b>5) With respect to playing offense, you:</b><br>
<input type="radio" name="q5" value="-2">are still learning the basic strategy, not quite sure where to go or when to cut</input><br>
<input type="radio" name="q5" value="-1">have the basic idea of where/when/how cuts should be made, starting to be able to do it, basic knowledge of a stack</input><br>
<input type="radio" name="q5" value="0">can make decent cuts, understand the stack, can play at least one of handler/striker/popper/etc, understand the concept of the dump & swing</input><br>
<input type="radio" name="q5" value="1">can make good cuts, can play any of handler/striker/popper/etc, comfortable handling, rarely throw away the disc or get blocked</input><br>
<input type="radio" name="q5" value="2">proficient cutter, experienced handler, can play any position, understand many offensive strategies</input><br>

<br>


<h2>Experience Questions:</h2>

<b>6) For how many years have you been playing ultimate?</b><br>
<input type="radio" name="q6" value="-2">0 years</input><br>
<input type="radio" name="q6" value="-1">1-2 years</input><br>
<input type="radio" name="q6" value="0">3-5 years</input><br>
<input type="radio" name="q6" value="1">6-8 years</input><br>
<input type="radio" name="q6" value="2">9+ years</input><br>

<br>

<b>7) What is the highest level at which you regularly play at?</b><br>
<input type="radio" name="q7" value="-2">Mon/Wed Tiers 7-10 or Tue/Thu Tiers 6-10, Fridays, or Winter Open</input><br>
<input type="radio" name="q7" value="-1">Mon/Wed Tiers 4-6 or Tue/Thu Tiers 2-5, or Winter Open</input><br>
<input type="radio" name="q7" value="0">Mon/Wed Tiers 1-3, or Tue/Thu Tier 1, or Winter Intermediate</input><br>
<input type="radio" name="q7" value="1">Competitive Tournament play</input><br>
<input type="radio" name="q7" value="2">Competitive Nationals play</input><br>

<br>

<b>8) Over the past few summers, how many nights during the week did you play ultimate? (organized practices and regular pick-up count)</b><br>
<input type="radio" name="q8" value="-2">0 nights per week</input><br>
<input type="radio" name="q8" value="-1">1 night per week</input><br>
<input type="radio" name="q8" value="0">2 nights per week</input><br>
<input type="radio" name="q8" value="1">3 nights per week</input><br>
<input type="radio" name="q8" value="2">more than 3 nights per week</input><br>

<br>

<b>9) Over the past few years, when did you normally play ultimate?</b><br>
<input type="radio" name="q9" value="-2">The occasional pick-up game</input><br>
<input type="radio" name="q9" value="-1">The occasional tournament</input><br>
<input type="radio" name="q9" value="0">1 season (Summer only, Fall only, Winter only)</input><br>
<input type="radio" name="q9" value="1">2 seasons (Summer and Fall, or Summer and Winter, or Fall and Winter)</input><br>
<input type="radio" name="q9" value="2">3 seasons (Summer, Fall, and Winter)</input><br>

<br>

<b>10) If there was a disagreement on the field about a certain play, the majority of the time you would be able to:</b><br>
<input type="radio" name=q10 value="-2">not do much because you don't know all the rules yet </input><br>
<input type="radio" name=q10 value="-1">quote what you think is the rule, and agree with the other player/captain to go with that</input><br>
<input type="radio" name=q10 value="1">use a copy of the rules to find the exact rule that addresses the problem</input><br>
<input type="radio" name=q10 value="2">quote the exact rule from memory that addresses the problem</input><br>

<br>

</form>
</div>

<?php
echo $this->Html->scriptBlock ("
$('#rating').dialog({
	autoOpen: false,
	buttons: {
		'Cancel': function() { $('#rating').dialog('close'); },
		'Calculate': function() { doranking(); $('#rating').dialog('close'); }
	},
	modal: true,
	resizable: false,
	width: 640,
	height: 480
});

function dorating() {
	$('#rating').dialog('open');
}
");
?>
