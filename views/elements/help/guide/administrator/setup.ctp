<h2>Administrator Guide: Site Setup and Configuration</h2>
<p>If you are reading this page, you have successfully installed <?php echo ZULURU; ?>. Congratulations!</p>
<p><?php echo ZULURU; ?> is built to be highly flexible, which means that it can handle almost any situation you throw at it, but there's a number of things that need to be correctly configured to make it happen.
<?php echo ZULURU; ?> configuration is split into three main areas.</p>

<h3>Config Files</h3>
<p>There are a number of things about the system which will never change under normal usage.
These are stored in system configuration files, typically found under the zuluru/config folder.</p>
<p>First, there are a number of settings where defaults are either standard or easily calculated, which are saved to install.php.
This file includes comments describing the use of these settings, so you can generally edit it with confidence.
Be sure to save a backup before starting, especially if you are unfamiliar with PHP syntax, as a missing quote or comma in this file can completely break your <?php echo ZULURU; ?> installation.</p>
<p>Second, you can control the options available in some other configuration pages, as well as certain aspects of system behaviour.
options.php defines most of the contents for drop-down selections throughout the system.
You can alter these options (add new options, remove unwanted ones, or change wording) by creating a file called options_custom.php.
(This is preferable to directly editing the options.php file, as any such changes might be lost if you update your version of <?php echo ZULURU; ?>.)
Similarly, you can alter some system behaviour by creating features_custom.php, containing overrides of selected values from features.php.
Note that, in both cases, you need only put the values you want to change in these files;
they don't need (and shouldn't contain) copies of all settings.</p>

<h3>System Configuration</h3>
<p>You will have several options under the Configuration menu.
At the very least, you will have a Settings sub-menu (with Organization, Features, Email and Scoring) and Holidays.
If you have enabled the registration feature, you will also have Settings -> Registration, and if you've enabled online payments, you'll also see Settings -> Payment.
If you have enabled the affiliate or document upload features, you will also have options for configuring these.
When you first install your system, you should go through and set these all up according to your particular needs.
Details about each option are included on the settings pages, and not reiterated here.
<ul>
<li>The Settings -> Organization page is used to define such values as your organization's name, acronym, address, and some key dates.</li>
<li>Settings -> Features is used to turn various optional features (registration, franchises, annotations, how to handle roster requests, etc.) on and off.</li>
<li>The Settings -> Email page configures some important email addresses used by the system to communicate with users and administrators.</li>
<li>Use the Settings -> Scoring page to configure how the system handles certain score submission situations, and to set up some default values for creating new leagues.</li>
<li>If you have enabled Registration on the Settings -> Features page, you then use the Settings -> Registration page to set up certain aspects of how the registration system will work.</li>
<li>If you have enabled Online Payments on the Settings -> Registration page, you then use the Settings -> Payments page to define any applicable taxes and configure login credentials for your selected third-party payment provider.</li>
<li>If you have enabled Affiliates on the Settings -> Features page, you then use the Affiliates area to set up your various affiliates, including optionally assigning managers to each.</li>
<li>If you have enabled Document Uploads on the Settings -> Features page, you then use the Upload types area to define which documents people are allowed to upload.</li>
<li>Finally, use the Holidays page to define the list of dates that will be skipped over when creating game slots and season attendance projections.</li>
</ul>
</p>
<p>Note that some settings (e.g. the TinyMCE WYSIWYG editor and the Emogrifier email formatter) have additional system requirements.
These requirements are noted at these settings.
Enabling them without first ensuring that the requirements are satisfied may cause system instability.</p>

<h3>Leagues and Registrations</h3>
<p>The settings above are all long-term settings; they primarily affect how the site presents itself as a whole.
However, most organizations will have a variety of options for play.
You might have some leagues that are co-ed and some single-gender.
You might have leagues that happen on different nights or in different seasons or which cater to different skill levels.
You might have small leagues and large leagues with different scheduling requirements.
All of these variables are handled through the configuration of <?php echo $this->Html->link('leagues and divisions', array('action' => 'guide', 'administrator', 'leagues'));
?> and, if enabled, <?php echo $this->Html->link('registration', array('action' => 'guide', 'administrator', 'registration')); ?>.</p>

<h3>Daily Maintenance</h3>
<p>There are a number of daily maintenance tasks which the system can take care of for you automatically.
This includes things like reminding players of upcoming games or unanswered roster invitations, reminding captains of games they haven't submitted scores for, opening upcoming leagues, closing past leagues, etc.</p>
<p>To handle all of this, you should set up an automated daily task, using the UNIX/Linux "cron" functionality, or the Windows Scheduler.
Details of a sample cron task are in the main <?php echo ZULURU; ?> README file, and something very similar can be used for Windows.</p>
