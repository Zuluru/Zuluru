<?php
class Zuluru71Schema extends CakeSchema {
	var $name = 'Zuluru71';

	// Add new database records for all the settings ever added,
	// to make sure everyone has at least up-to-date defaults.
	function new_data() {
		return array(
			'settings' => array(
				'key' => array('category', 'name'),
				'records' => array(
					array(
						'category' => 'organization',
						'name' => 'first_day',
						'value' => '1',
					),
					array(
						'category' => 'organization',
						'name' => 'spring_start',
						'value' => '0-04-01',
					),
					array(
						'category' => 'organization',
						'name' => 'spring_indoor_start',
						'value' => '0-04-01',
					),
					array(
						'category' => 'organization',
						'name' => 'summer_start',
						'value' => '0-06-01',
					),
					array(
						'category' => 'organization',
						'name' => 'summer_indoor_start',
						'value' => '0-06-01',
					),
					array(
						'category' => 'organization',
						'name' => 'fall_start',
						'value' => '0-09-01',
					),
					array(
						'category' => 'organization',
						'name' => 'fall_indoor_start',
						'value' => '0-09-01',
					),
					array(
						'category' => 'organization',
						'name' => 'winter_start',
						'value' => '0-01-01',
					),
					array(
						'category' => 'organization',
						'name' => 'winter_indoor_start',
						'value' => '0-01-01',
					),
					array(
						'category' => 'site',
						'name' => 'default_language',
						'value' => 'en',
					),
					array(
						'category' => 'feature',
						'name' => 'language',
						'value' => '0',
					),
					array(
						'category' => 'feature',
						'name' => 'uls',
						'value' => '0',
					),
					array(
						'category' => 'feature',
						'name' => 'public',
						'value' => '0',
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
						'name' => 'antispam',
						'value' => '0',
					),
					array(
						'category' => 'feature',
						'name' => 'birth_year_only',
						'value' => '0',
					),
					array(
						'category' => 'feature',
						'name' => 'units',
						'value' => 'Metric',
					),
					array(
						'category' => 'feature',
						'name' => 'waiting_list',
						'value' => '1',
					),
					array(
						'category' => 'feature',
						'name' => 'spirit',
						'value' => '1',
					),
					array(
						'category' => 'feature',
						'name' => 'allow_past_games',
						'value' => '0',
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
						'name' => 'twitter',
						'value' => '0',
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
						'name' => 'shirt_numbers',
						'value' => '0',
					),
					array(
						'category' => 'feature',
						'name' => 'attendance',
						'value' => '1',
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
						'name' => 'gravatar',
						'value' => '0',
					),
					array(
						'category' => 'feature',
						'name' => 'documents',
						'value' => '0',
					),
					array(
						'category' => 'feature',
						'name' => 'contacts',
						'value' => '0',
					),
					array(
						'category' => 'feature',
						'name' => 'facility_preference',
						'value' => '0',
					),
					array(
						'category' => 'feature',
						'name' => 'home_field',
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
						'name' => 'spirit_max',
						'value' => '5',
					),
					array(
						'category' => 'scoring',
						'name' => 'spirit_default',
						'value' => '1',
					),
					array(
						'category' => 'scoring',
						'name' => 'carbon_flip',
						'value' => '0',
					),
					array(
						'category' => 'scoring',
						'name' => 'most_spirited',
						'value' => '0',
					),
					array(
						'category' => 'scoring',
						'name' => 'stat_tracking',
						'value' => '0',
					),
					array(
						'category' => 'payment',
						'name' => 'popup',
						'value' => '1',
					),
					array(
						'category' => 'payment',
						'name' => 'options',
						'value' => 'your credit card',
					),
					array(
						'category' => 'payment',
						'name' => 'offline_options',
						'value' => 'cheque, cash or email transfer',
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
						'name' => 'register_now',
						'value' => '0',
					),
					array(
						'category' => 'registration',
						'name' => 'reservation_time',
						'value' => '24',
					),
					array(
						'category' => 'registration',
						'name' => 'delete_unpaid',
						'value' => '0',
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
				),
			),
		);
	}
}
?>
