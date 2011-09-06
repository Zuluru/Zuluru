<?php
// All of these can be changed through the various "Settings" pages of Zuluru.
// Easier to change them there than mess about with this file.
class SettingsData {

	public $table = 'settings';

	public $records = array(
		array(
			'category' => 'organization',
			'name' => 'name',
			'value' => '',
		),
		array(
			'category' => 'organization',
			'name' => 'short_name',
			'value' => '',
		),
		array(
			'category' => 'organization',
			'name' => 'address',
			'value' => '',
		),
		array(
			'category' => 'organization',
			'name' => 'address2',
			'value' => '',
		),
		array(
			'category' => 'organization',
			'name' => 'city',
			'value' => '',
		),
		array(
			'category' => 'organization',
			'name' => 'province',
			'value' => '',
		),
		array(
			'category' => 'organization',
			'name' => 'country',
			'value' => '',
		),
		array(
			'category' => 'organization',
			'name' => 'postal',
			'value' => '',
		),
		array(
			'category' => 'organization',
			'name' => 'phone',
			'value' => '',
		),
		array(
			'category' => 'organization',
			'name' => 'latitude',
			'value' => '',
		),
		array(
			'category' => 'organization',
			'name' => 'longitude',
			'value' => '',
		),
		array(
			'category' => 'organization',
			'name' => 'year_end',
			'value' => '03',
		),
		array(
			'category' => 'site',
			'name' => 'gmaps_key',
			'value' => '',
		),
		array(
			'category' => 'site',
			'name' => 'name',
			'value' => 'Zuluru',
		),
		array(
			'category' => 'feature',
			'name' => 'items_per_page',
			'value' => '25',
		),
		array(
			'category' => 'feature',
			'name' => 'registration',
			'value' => '1',
		),
		array(
			'category' => 'feature',
			'name' => 'generate_roster_email',
			'value' => '1',
		),
		array(
			'category' => 'feature',
			'name' => 'force_roster_request',
			'value' => '0',
		),
		array(
			'category' => 'feature',
			'name' => 'region_preference',
			'value' => '0',
		),
		array(
			'category' => 'feature',
			'name' => 'dog_questions',
			'value' => '0',
		),
		array(
			'category' => 'scoring',
			'name' => 'default_winning_score',
			'value' => '6',
		),
		array(
			'category' => 'scoring',
			'name' => 'default_losing_score',
			'value' => '0',
		),
		array(
			'category' => 'scoring',
			'name' => 'default_transfer_ratings',
			'value' => '1',
		),
		array(
			'category' => 'scoring',
			'name' => 'spirit_questions',
			'value' => 'team',
		),
		array(
			'category' => 'scoring',
			'name' => 'spirit_numeric',
			'value' => '1',
		),
		array(
			'category' => 'scoring',
			'name' => 'spirit_max',
			'value' => '5',
		),
		array(
			'category' => 'scoring',
			'name' => 'missing_score_spirit_penalty',
			'value' => '3',
		),
		array(
			'category' => 'scoring',
			'name' => 'incident_reports',
			'value' => '1',
		),
		array(
			'category' => 'scoring',
			'name' => 'allstars',
			'value' => '1',
		),
		array(
			'category' => 'payment',
			'name' => 'currency',
			'value' => 'CAD',
		),
		array(
			'category' => 'payment',
			'name' => 'invoice_implementation',
			'value' => 'invoice',
		),
		array(
			'category' => 'payment',
			'name' => 'payment_implementation',
			'value' => 'paypal',
		),
		array(
			'category' => 'payment',
			'name' => 'reg_id_format',
			'value' => 'Reg%05d',
		),
		array(
			'category' => 'payment',
			'name' => 'tax1_enable',
			'value' => '1',
		),
		array(
			'category' => 'payment',
			'name' => 'tax1_name',
			'value' => 'HST',
		),
		array(
			'category' => 'payment',
			'name' => 'tax2_enable',
			'value' => '0',
		),
		array(
			'category' => 'payment',
			'name' => 'tax2_name',
			'value' => '',
		),
		array(
			'category' => 'payment',
			'name' => 'test_payments',
			'value' => '0',
		),
		array(
			'category' => 'email',
			'name' => 'admin_email',
			'value' => 'admin@example.com',
		),
		array(
			'category' => 'email',
			'name' => 'admin_name',
			'value' => 'Zuluru Administrator',
		),
		array(
			'category' => 'email',
			'name' => 'incident_report_email',
			'value' => 'incidents@example.com',
		),
		array(
			'category' => 'email',
			'name' => 'approval_notice_subject',
			'value' => '%site Notification of Score Approval',
		),
		array(
			'category' => 'email',
			'name' => 'approval_notice_body',
			'value' => 'Dear %fullname,\r\n\r\nYou have not submitted a score for the game between your team %team and %opponent, which was scheduled for %gamedate in %league. Scores need to be submitted in a timely fashion by both captains to substantiate results and for optimal scheduling of future games.  Your opponent\'s submission for this game has now been accepted and they have been given a perfect spirit score as a result of their timely submission. Your team spirit score has been penalized due to your lack of submission - your opponent\'s Spirit score for your team minus 3 points.  Overall team spirit can impact participation in future events.\r\n\r\nIf there is an exceptional reason why you were unable to submit your score in time, you may contact your coordinator who will consider reversing the penalty. To avoid such penalties in the future, please be sure to submit your scores promptly.\r\n\r\nThanks,\r\n%adminname\r\n',
		),
		array(
			'category' => 'email',
			'name' => 'approved_subject',
			'value' => '%site Account Activation for %username',
		),
		array(
			'category' => 'email',
			'name' => 'approved_player_body',
			'value' => 'Dear %fullname,\r\n\r\nYour %site account has been approved.\r\n\r\nYour new permanent member number is\r\n     %memberid\r\nThis number will identify you for member services, discounts, etc, so please write it down in a safe place so you\'ll remember it.\r\n\r\nYou may now log in to the system at\r\n  %url\r\nwith the username\r\n   %username\r\nand the password you specified when you created your account.  You will be asked to confirm your account information and sign a waiver form before your account will be activated.\r\n\r\nThanks,\r\n%adminname\r\n',
		),
		array(
			'category' => 'email',
			'name' => 'approved_visitor_body',
			'value' => 'Dear %fullname,\r\n\r\nYour %site account has been approved.\r\n\r\nYou may now log in to the system at\r\n     %url\r\nwith the username\r\n   %username\r\nand the password you specified when you created your account.  You will be asked to confirm your account information and sign a waiver form before your account will be activated.\r\n\r\nThanks,\r\n%adminname\r\n',
		),
		array(
			'category' => 'email',
			'name' => 'delete_duplicate_subject',
			'value' => '%site Account Update ',
		),
		array(
			'category' => 'email',
			'name' => 'delete_duplicate_body',
			'value' => 'Dear %fullname,\r\n\r\nYou seem to have created a duplicate %site account.  You already have an account with the username\r\n   %existingusername\r\ncreated using the email address\r\n    %existingemail\r\nYour second account has been deleted.  If you cannot remember your password for the existing account, please use the \'Forgot your password?\' feature at\r\n     %passwordurl\r\nand a new password will be emailed to you.\r\n\r\nIf the above email address is no longer correct, please reply to this message and request an address change.\r\n\r\nThanks,\r\n%adminname\r\n',
		),
		array(
			'category' => 'email',
			'name' => 'merge_duplicate_subject',
			'value' => '%site Account Update  ',
		),
		array(
			'category' => 'email',
			'name' => 'merge_duplicate_body',
			'value' => 'Dear %fullname,\r\n\r\nYou seem to have created a duplicate %site account.  You already had an account with the username\r\n    %existingusername\r\ncreated using the email address\r\n    %existingemail\r\nTo preserve historical information (registrations, team records, etc.) this old account has been merged with your new information.  You will be able to access this account with your newly chosen user name and password.\r\n\r\nThanks,\r\n%adminname\r\n',
		),
		array(
			'category' => 'email',
			'name' => 'password_reset_subject',
			'value' => '%site Password Reset',
		),
		array(
			'category' => 'email',
			'name' => 'password_reset_body',
			'value' => 'Dear %fullname,\r\n\r\nSomeone, probably you, just requested that your password for the account\r\n     %username\r\nbe reset.  Your new password is\r\n        %password\r\nSince this password has been sent via unencrypted email, you should change it as soon as possible.\r\n\r\nIf you didn\'t request this change, don\'t worry.  Your account password can only ever be mailed to the email address specified in your %site system account.  However, if you think someone may be attempting to gain unauthorized access to your account, please contact the system administrator.\r\n\r\nThanks,\r\n%adminname\r\n',
		),
		array(
			'category' => 'email',
			'name' => 'member_letter_subject',
			'value' => '%site %year Membership',
		),
		array(
			'category' => 'email',
			'name' => 'member_letter_body',
			'value' => 'Dear %fullname,\r\n\r\nWelcome to %site!\r\n\r\nThanks,\r\n%adminname',
		),
		array(
			'category' => 'email',
			'name' => 'photo_approved_subject',
			'value' => '%site Notification of Photo Approval',
		),
		array(
			'category' => 'email',
			'name' => 'photo_approved_body',
			'value' => 'Dear %fullname,\r\n\r\nYour photo has been approved and is now visible to other members who are logged in to this site.\r\n\r\nThanks,\r\n%adminname',
		),
		array(
			'category' => 'email',
			'name' => 'photo_deleted_subject',
			'value' => '%site Notification of Photo Deletion',
		),
		array(
			'category' => 'email',
			'name' => 'photo_deleted_body',
			'value' => 'Dear %fullname,\r\n\r\nYour photo has been reviewed by an administrator and rejected as unsuitable. To be approved, photos must be of you and only you (e.g. no logos or shots of groups or your pet or your car) and must clearly show your face. Photos may not include nudity or depiction of any activity that is illegal or otherwise contrary to the Spirit of Ultimate.\r\n\r\nThanks,\r\n%adminname',
		),
		array(
			'category' => 'email',
			'name' => 'score_reminder_subject',
			'value' => '%site Reminder to Submit Score',
		),
		array(
			'category' => 'email',
			'name' => 'score_reminder_body',
			'value' => 'Dear %fullname,\r\n\r\nYou have not yet submitted a score for the game between your team %team and %opponent, which was scheduled for %gamedate in %league. Scores need to be submitted in a timely fashion by BOTH captains to substantiate results and for optimal scheduling of future games.  We ask you to please update the score as soon as possible.  You can submit the score for this game at %scoreurl\r\n\r\nNote that failure to report your score within 5 days of your game will result in automatic score approval and a loss of 3 Spirit points (not including Spirit points deducted by your opponent).\r\n\r\nThanks,\r\n%adminname\r\n',
		),
		array(
			'category' => 'registration',
			'name' => 'allow_tentative',
			'value' => '1',
		),
		array(
			'category' => 'registration',
			'name' => 'online_payments',
			'value' => '1',
		),
		array(
			'category' => 'registration',
			'name' => 'order_id_format',
			'value' => 'R%09d',
		),
		array(
			'category' => 'registration',
			'name' => 'offline_payment_text',
			'value' => '<ul>\r\n<li>Mail (or personally deliver) a cheque for the appropriate amount to the head office.</li>\r\n<li>Ensure that you quote your order number on the cheque in order for your payment to be properly credited.</li>\r\n<li>Also include a note indicating which registration the cheque is for, along with your full name.</li>\r\n<li>If you are paying for multiple registrations with a single cheque, be sure to list all applicable order numbers, registrations and member names.</li>\r\n</ul>\r\n<p>Please note that online payment registrations are \'live\' while offline payments are not.  You will not be registered to the appropriate category that you are paying for until the cheque is received and processed (usually within 1-2 business days of receipt).</p>\r\n<p><b>Insufficient Funds (NSF) - Refused Credit Cards</b></p>\r\n<p>A $15 surcharge will be added when a payment cheque cannot be cashed due to insufficient funds or when a manual credit card transaction slip is refused. (Note: the on-line payments system validates credit cards in real time. There is no surcharge if an on-line transaction is refused.) </p>\r\n',
		),
		array(
			'category' => 'registration',
			'name' => 'refund_policy_text',
			'value' => '<p>Refunds are granted under the following conditions: </p>\r\n<ul>\r\n    <li>All refunds less than $50.00 will be charged a minimum $5.00 administration fee. </li>\r\n    <li>If a member (individual registration) decides to quit after playing less than 25% of their games, a refund will be granted, less a 10% administration fee or minimum $5.00 administration fee. </li>\r\n    <li>If a team (league team registration) decides to quit before the season begins a refund will be granted, less a 10% administration fee or minimum $5.00 administration fee, provided that the cancellation occurs more than five business days prior to the start date of the associated league. Team refunds are not issued once a season begins or within 5 business days of the league start date. </li>\r\n    <li>If a team or member (tournament/event registration) decides to quit before a special event begins they should refer to the refund policy that is stipulated for that specific event (in the event registration details). In the absence of special event refund details the above stipulation of two business days notice will apply (to both teams and individuals), and a refund will be granted less a 10% administration fee or minimum $5.00 administration fee. </li>\r\n    <li>If a member or team is incorrectly charged or charged more than once for their registration a refund will be granted for the incorrect difference </li>\r\n    <li>Pro-rated refunds can be requested in extraordinary circumstances (e.g. if a player becomes seriously injured after playing more than 25% of their games) and are at the discretion of the GM </li>\r\n    <li>Other situations may warrant a refund, at the discretion of the GM </li>\r\n</ul>\r\n<p>Please note that we do not issue individual player refunds for \'team\' registrations; meaning if your captain registers a team and you pay the captain your portion of the team fee, it is between you and the captain to determine any individual refunds for the team contribution.</p>\r\n',
		),
	);

}
?>
