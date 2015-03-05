<h2><?php __('Administrator Guide: Site Setup and Configuration'); ?></h2>
<p><?php printf(__('If you are reading this page, you have successfully installed %s. Congratulations!', true), ZULURU); ?></p>
<p><?php printf(__('%s is built to be highly flexible, which means that it can handle almost any situation you throw at it, but there\'s a number of things that need to be correctly configured to make it happen. %s configuration is split into three main areas.', true), ZULURU, ZULURU); ?></p>

<h3><?php __('Config Files'); ?></h3>
<p><?php printf(__('There are a number of things about the system which will never change under normal usage. These are stored in system configuration files, typically found under the %s folder.', true), 'zuluru/config'); ?></p>
<p><?php printf(__('First, there are a number of settings where defaults are either standard or easily calculated, which are saved to %s. This file includes comments describing the use of these settings, so you can generally edit it with confidence. Be sure to save a backup before starting, especially if you are unfamiliar with PHP syntax, as a missing quote or comma in this file can completely break your %s installation.', true), 'install.php', ZULURU); ?></p>
<p><?php printf(__('Second, you can control the options available in some other configuration pages, as well as certain aspects of system behaviour. %s defines most of the contents for drop-down selections throughout the system. You can alter these options (add new options, remove unwanted ones, or change wording) by creating a file called %s. (This is preferable to directly editing the %s file, as any such changes might be lost if you update your version of %s.) Similarly, you can alter some system behaviour by creating %s, containing overrides of selected values from %s. Note that, in both cases, you need only put the values you want to change in these files; they don\'t need (and shouldn\'t contain) copies of all settings.', true), 'options.php', 'options_custom.php', 'options.php', ZULURU, 'features_custom.php', 'features.php'); ?></p>

<h3><?php __('System Configuration'); ?></h3>
<p><?php __('You will have several options under the Configuration menu. At the very least, you will have a Settings sub-menu (with Organization, Features, Email, Team, User, Profile and Scoring) and Holidays. If you have enabled the registration feature, you will also have Settings -> Registration, and if you\'ve enabled online payments, you\'ll also see Settings -> Payment. If you have enabled the affiliate or document upload features, you will also have options for configuring these. When you first install your system, you should go through and set these all up according to your particular needs. Details about each option are included on the settings pages, and not reiterated here'); ?>.
<ul>
<li><?php __('The Settings -> Organization page is used to define such values as your organization\'s name, acronym, address, and some key dates.'); ?></li>
<li><?php __('Settings -> Features is used to turn various optional features (registration, franchises, annotations, how to handle roster requests, etc.) on and off.'); ?></li>
<li><?php __('The Settings -> Email page configures some important email addresses used by the system to communicate with users and administrators.'); ?></li>
<li><?php __('The Settings -> Team page allows you to turn various team-specific options on and off.'); ?></li>
<li><?php __('Settings -> User is used to change options related to how user accounts are handled, while Settings -> Profile is used to set which profile fields you want to track for your users (and can set some to be admin-only if there is no good reason for your users to be changing them once they are initially set).'); ?></li>
<li><?php __('Use the Settings -> Scoring page to configure how the system handles certain score submission situations, and to set up some default values for creating new leagues.'); ?></li>
<li><?php __('If you have enabled Registration on the Settings -> Features page, you then use the Settings -> Registration page to set up certain aspects of how the registration system will work.'); ?></li>
<li><?php __('If you have enabled Online Payments on the Settings -> Registration page, you then use the Settings -> Payments page to define any applicable taxes and configure login credentials for your selected third-party payment provider.'); ?></li>
<li><?php __('If you have enabled Affiliates on the Settings -> Features page, you then use the Affiliates area to set up your various affiliates, including optionally assigning managers to each.'); ?></li>
<li><?php __('If you have enabled Document Uploads on the Settings -> Features page, you then use the Upload types area to define which documents people are allowed to upload.'); ?></li>
<li><?php __('Finally, use the Holidays page to define the list of dates that will be skipped over when creating game slots and season attendance projections.'); ?></li>
</ul>
</p>
<p><?php __('Note that some settings (e.g. the TinyMCE WYSIWYG editor and the Emogrifier email formatter) have additional system requirements. These requirements are noted at these settings. Enabling them without first ensuring that the requirements are satisfied may cause system instability.'); ?></p>

<h3><?php __('Leagues and Registrations'); ?></h3>
<p><?php
printf(__('The settings above are all long-term settings; they primarily affect how the site presents itself as a whole. However, most organizations will have a variety of options for play. You might have some leagues that are co-ed and some single-gender. You might have leagues that happen on different nights or in different seasons or which cater to different skill levels. You might have small leagues and large leagues with different scheduling requirements. All of these variables are handled through the configuration of %s and, if enabled, %s.', true),
	$this->Html->link(__('leagues and divisions', true), array('action' => 'guide', 'administrator', 'leagues')),
	$this->Html->link(__('registration', true), array('action' => 'guide', 'administrator', 'registration'))
); ?></p>

<h3><?php __('Daily Maintenance'); ?></h3>
<p><?php __('There are a number of daily maintenance tasks which the system can take care of for you automatically. This includes things like reminding players of upcoming games or unanswered roster invitations, reminding coaches and captains of games they haven\'t submitted scores for, opening upcoming leagues, closing past leagues, etc.'); ?></p>
<p><?php printf(__('To handle all of this, you should set up an automated daily task, using the UNIX/Linux "cron" functionality, or the Windows Scheduler. Details of a sample cron task are in the main %s %s file, and something very similar can be used for Windows.', true), ZULURU, 'README'); ?></p>
