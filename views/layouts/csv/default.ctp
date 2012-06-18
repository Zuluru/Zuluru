<?php
// Debug output messes up the CSV
Configure::write ('debug', 0);

// Start the output, let the browser know what type it is
header('Content-type: text/x-csv');
header("Content-Disposition: attachment; filename=\"$download_file_name.csv\"");

echo $content_for_layout;
?>
