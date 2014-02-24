<?php
// Debug output messes up the CSV
Configure::write ('debug', 0);

// Start the output, let the browser know what type it is
header('Content-type: text/directory; charset=UTF-8; profile=vCard');
header("Content-Disposition: attachment; filename=\"$download_file_name.vcf\"");
// Prevent caching
header("Cache-Control: no-cache, must-revalidate");
?>
BEGIN:VCARD
VERSION:2.1
<?php echo $content_for_layout; ?>
REV:<?php echo date('Ymd') . 'T' . date('His') . 'Z'; ?>

END:VCARD
