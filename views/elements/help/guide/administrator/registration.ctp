<h2><?php __('Administrator Guide: Registration'); ?></h2>
<p><?php
printf(__('Registration is an optional feature of %s. It can be disabled completely through the %s page. If it is disabled, you will be responsible for manually managing and tracking all registrations and payments, so even if you are not doing online payments, using the registration system will typically save you time and reduce problems. This guide will help you understand how to use the registration system to best effect.', true),
	ZULURU,
	$this->Html->link(sprintf('%s -> %s -> %s', __('Configuration', true), __('Settings', true), __('Features', true)), array('controller' => 'settings', 'action' => 'feature'))
); ?></p>

<h3><?php __('Events'); ?></h3>
<p><?php __('The registration system is based around a set of "registration events" that you define. An event is anything that a user might register for. Common events include annual memberships, spots for teams in leagues or tournaments, roster spots on "hat" teams, training clinics, and social events.'); ?>
 <?php __('(Developer\'s note: I\'ve never really liked the term "event" for this. If you can think of a better one, please let me know!)'); ?>'); ?></p>

<h4><?php __('Rules'); ?></h4>
<p><?php __('If desired, you can add rules that must be met before a user can register for a particular event. Most commonly, this is used to enforce age limits or ensure that pre-requisites are met, but there are other uses as well. See the help for the "register rule" field for more information; setting up rules is currently a little bit complex, but there are examples provided that will cover most normal situations.'); ?></p>
<p><?php __('There are also "rule-like" settings which allow you to put a limit on how many people can register for it (e.g. if there are limited team spots available), enforce gender limits, and indicate whether an individual may register for an event more than once (e.g. purchasing multiple tickets to a social event).'); ?></p>

<h4><?php __('Waivers'); ?></h4>
<p><?php printf(__('You will typically want everyone to accept a waiver of liability before participating in games that you offer. You can optionally require that the user accept a click-through waiver before registering or joining a team by using the %s rule.', true), 'SIGNED_WAIVER'); ?></p>

<h4><?php __('Questionnaires'); ?></h4>
<p><?php __('If desired, you can define a questionnaire that will be used during registration. Questionnaires are built from a set of questions that you also define. Questions can be multiple-choice, groups of checkboxes, or allow text entry. Each question can require a response or be optional. Many questionnaires might use the same question, and many events might use the same questionnaire; you do not need to create a new copy of an existing questionnaire for every new event.'); ?></p>
<p><?php __('Some event types (e.g. anything for team registration) will have some questions automatically added to the questionnaire, for team name, etc., so you don\'t need to add those manually.'); ?></p>

<h3><?php __('Registrations'); ?></h3>
<p><?php
printf(__('Once a registration event has opened, people will (hopefully!) start registering for it. You can access summaries and details of these registrations, including questionnaire answers, through the %s page, or the %s.', true),
	$this->Html->link(sprintf('%s -> %s', __('Registration', true), __('Statistics', true)), array('controller' => 'registrations', 'action' => 'statistics')),
	$this->Html->link(__('events list', true), array('controller' => 'events'))
); ?></p>
<p><?php __('If the event has a cost associated with it, people may pay either online or offline.'); ?></p>

<h4><?php __('Online Payments'); ?></h4>
<p><?php
printf(__('%s supports multiple online payment providers, so if you choose to accept online payments, you have some options in who you want to deal with. Currently, you can enable only a single provider; there is no way to give the user a choice of who they want to pay you through. If you enable online payments in the %s page, you will also need to enter the account credentials provided to you by the provider.', true),
	ZULURU,
	$this->Html->link(sprintf('%s -> %s -> %s', __('Configuration', true), __('Settings', true), __('Registration', true)), array('controller' => 'settings', 'action' => 'registration'))
); ?></p>

<h4><?php __('Offline Payments'); ?></h4>
<p><?php __('Online payment providers do take a piece of every payment for their trouble, so some organizations opt to only collect money manually. Even if you have online payments enabled, there will always be some percentage who prefer to pay by cheque. You may also have some events where cash is collected "at the door". In any of these situations, you will need to manually edit the individual registrations to mark them as paid, along with any notes you care to add (e.g. cheque number).'); ?></p>

<h4><?php __('Pre-registrations'); ?></h4>
<p><?php __('In certain situations, you may want to allow certain individuals to register for an event before the general public is able to, or after the event has officially closed. For example, someone who will be away for an entire registration period might request permission to register before they leave, and if someone drops out of a full registration after the closing date, you might have a waiting list and want to ensure that the first person on the list gets in. You don\'t want to change the open or close date, because this allows anyone to register, instead of just the people you want. To allow this, you can add a "pre-registration" for a user, allowing them to register normally outside of the public date range; the process for them is exactly the same as it would be otherwise, including any required questionnaire and/or waiver, and acceptance of online payments. Note that adding a pre-registration does <strong>not</strong> allow someone to violate the register rule or caps.'); ?></p>
