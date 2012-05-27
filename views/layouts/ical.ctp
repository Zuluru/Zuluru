<?php
// This can happen when an invalid game or team is requested
if (!isset($calendar_type)) {
	return;
}

$short = Configure::read('organization.short_name');

header('Content-type: text/calendar; charset=UTF-8');
// Prevent caching
header("Cache-Control: no-cache, must-revalidate");

// ical header
// TODO: Handle other time zones, hopefully there's an easy way to do this dynamically
?>
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Zuluru//<?php echo $calendar_type; ?>//EN
CALSCALE:GREGORIAN
METHOD:PUBLISH
X-WR-CALNAME:<?php echo ical_encode($calendar_name); ?> from <?php echo $short; ?>

BEGIN:VTIMEZONE
TZID:US/Eastern
LAST-MODIFIED:20070101T000000Z
BEGIN:DAYLIGHT
DTSTART:20070301T020000
RRULE:FREQ=YEARLY;BYDAY=2SU;BYMONTH=3
TZOFFSETFROM:-0500
TZOFFSETTO:-0400
TZNAME:EDT
END:DAYLIGHT
BEGIN:STANDARD
DTSTART:20071101T020000
RRULE:FREQ=YEARLY;BYDAY=1SU;BYMONTH=11
TZOFFSETFROM:-0400
TZOFFSETTO:-0500
TZNAME:EST
END:STANDARD
END:VTIMEZONE
<?php echo $content_for_layout; ?>
END:VCALENDAR
