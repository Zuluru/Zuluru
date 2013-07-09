<?php
// All of these can be changed through the various "Configuration -> Settings" pages of Zuluru.
// Easier to change them there than mess about with this file.
class SettingsData {

	public $table = 'settings';

	public $records = array(
		array(
			'category' => 'organization',
			'name' => 'name',
			'value' => 'Club',
		),
		array(
			'category' => 'organization',
			'name' => 'short_name',
			'value' => 'Club',
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
			'name' => 'affiliates',
			'value' => '0',
		),
		array(
			'category' => 'feature',
			'name' => 'multiple_affiliates',
			'value' => '0',
		),
		array(
			'category' => 'feature',
			'name' => 'auto_approve',
			'value' => '0',
		),
		array(
			'category' => 'feature',
			'name' => 'registration',
			'value' => '1',
		),
		array(
			'category' => 'feature',
			'name' => 'spirit',
			'value' => '1',
		),
		array(
			'category' => 'feature',
			'name' => 'urls',
			'value' => '1',
		),
		array(
			'category' => 'feature',
			'name' => 'flickr',
			'value' => '1',
		),
		array(
			'category' => 'feature',
			'name' => 'annotations',
			'value' => '1',
		),
		array(
			'category' => 'feature',
			'name' => 'shirt_colour',
			'value' => '1',
		),
		array(
			'category' => 'feature',
			'name' => 'attendance',
			'value' => '1',
		),
		array(
			'category' => 'feature',
			'name' => 'stat_tracking',
			'value' => '0',
		),
		array(
			'category' => 'feature',
			'name' => 'photos',
			'value' => '1',
		),
		array(
			'category' => 'feature',
			'name' => 'approve_photos',
			'value' => '1',
		),
		array(
			'category' => 'feature',
			'name' => 'documents',
			'value' => '0',
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
			'name' => 'home_field',
			'value' => '0',
		),
		array(
			'category' => 'feature',
			'name' => 'dog_questions',
			'value' => '0',
		),
		array(
			'category' => 'feature',
			'name' => 'franchises',
			'value' => '1',
		),
		array(
			'category' => 'feature',
			'name' => 'badges',
			'value' => '0',
		),
		array(
			'category' => 'feature',
			'name' => 'tasks',
			'value' => '0',
		),
		array(
			'category' => 'feature',
			'name' => 'tiny_mce',
			'value' => '0',
		),
		array(
			'category' => 'feature',
			'name' => 'pdfize',
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
			'name' => 'popup',
			'value' => '1',
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
			'category' => 'payment',
			'name' => 'options',
			'value' => 'your credit card',
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
			'name' => 'support_email',
			'value' => 'support@example.com',
		),
		array(
			'category' => 'email',
			'name' => 'emogrifier',
			'value' => '0',
		),
		array(
			'category' => 'registration',
			'name' => 'allow_tentative',
			'value' => '1',
		),
		array(
			'category' => 'registration',
			'name' => 'register_now',
			'value' => '0',
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
		array(
			'category' => 'profile',
			'name' => 'first_name',
			'value' => '2',
		),
		array(
			'category' => 'profile',
			'name' => 'last_name',
			'value' => '2',
		),
		array(
			'category' => 'profile',
			'name' => 'home_phone',
			'value' => '1',
		),
		array(
			'category' => 'profile',
			'name' => 'work_phone',
			'value' => '1',
		),
		array(
			'category' => 'profile',
			'name' => 'mobile_phone',
			'value' => '1',
		),
		array(
			'category' => 'profile',
			'name' => 'addr_street',
			'value' => '1',
		),
		array(
			'category' => 'profile',
			'name' => 'addr_city',
			'value' => '1',
		),
		array(
			'category' => 'profile',
			'name' => 'addr_prov',
			'value' => '1',
		),
		array(
			'category' => 'profile',
			'name' => 'addr_country',
			'value' => '1',
		),
		array(
			'category' => 'profile',
			'name' => 'addr_postalcode',
			'value' => '1',
		),
		array(
			'category' => 'profile',
			'name' => 'gender',
			'value' => '2',
		),
		array(
			'category' => 'profile',
			'name' => 'birthdate',
			'value' => '2',
		),
		array(
			'category' => 'profile',
			'name' => 'height',
			'value' => '1',
		),
		array(
			'category' => 'profile',
			'name' => 'skill_level',
			'value' => '1',
		),
		array(
			'category' => 'profile',
			'name' => 'year_started',
			'value' => '1',
		),
		array(
			'category' => 'profile',
			'name' => 'shirt_size',
			'value' => '1',
		),
		array(
			'category' => 'profile',
			'name' => 'willing_to_volunteer',
			'value' => '1',
		),
		array(
			'category' => 'profile',
			'name' => 'contact_for_feedback',
			'value' => '1',
		),
	);

}
?>
