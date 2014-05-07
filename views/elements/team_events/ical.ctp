<?php
$created = strtotime ($event['TeamEvent']['created']);
$created = date('Ymd', $created) . 'T' . date('His', $created) . 'Z';

// Get domain URL for signing events
$domain = Configure::read('urls.domain');

$location_name = strtr($event['TeamEvent']['location_name'], '()', '[]');
$location_address = ical_encode("{$event['TeamEvent']['location_street']}, {$event['TeamEvent']['location_city']}, {$event['TeamEvent']['location_province']} ($location_name)");

// encode event start and end times
$event_date = "TZID=$timezone:" . strftime('%Y%m%d', strtotime($event['TeamEvent']['date'])); // from date type
if ($event['TeamEvent']['start'] > $event['TeamEvent']['end']) {
	$event_end_date = "TZID=$timezone:" . strftime('%Y%m%d', strtotime($event['TeamEvent']['date']) + DAY);
} else {
	$event_end_date = $event_date;
}
$event_start = $event_date . 'T'
		. implode('', explode(':', $event['TeamEvent']['start'])); // from 'hh:mm:ss' string
$event_end = $event_end_date . 'T'
		. implode('', explode(':', $event['TeamEvent']['end']));  // from 'hh:mm:ss' string
$event_stamp = strftime('%a %b %d %Y', strtotime ($event['TeamEvent']['date'])) .
		" {$event['TeamEvent']['start']} to {$event['TeamEvent']['end']}";

// date stamp this file
$now = gmstrftime('%Y%m%dT%H%M%SZ'); // MUST be in UTC

// output event
?>
BEGIN:VEVENT
UID:<?php echo "$uid_prefix$event_id@$domain"; ?>

DTSTAMP:<?php echo $now; ?>

CREATED:<?php echo $created; ?>

LAST-MODIFIED:<?php echo $created; ?>

DTSTART;<?php echo $event_start; ?>

DTEND;<?php echo $event_end; ?>

LOCATION:<?php echo $location_address; ?>

SUMMARY:<?php echo ical_encode($event['TeamEvent']['name']); ?>

DESCRIPTION:<?php echo ical_encode($event['TeamEvent']['name']); ?>, at <?php echo ical_encode($event['TeamEvent']['location_name']); ?>, on <?php echo $event_stamp; ?>

STATUS:CONFIRMED
TRANSP:OPAQUE
END:VEVENT
