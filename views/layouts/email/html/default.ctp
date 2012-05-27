<?php
$email_css_file = $_SERVER['DOCUMENT_ROOT'] . DS . 'email.css';
if (file_exists($email_css_file)) {
	$style = file_get_contents($email_css_file);
} else {
	// Default style
	$style = '
body { color: black; background-color: white; }
p { margin: 1em 0; }
';
}

if (Configure::read('email.emogrifier')):
	App::import('Lib', 'emogrifier');
	$content_for_layout =
		$this->element('email/html/common_header') .
		$content_for_layout .
		$this->element('email/html/common_footer');
		// DOCTYPE, HTML, HEAD and BODY will all be added by this
	$emogrifier = new Emogrifier($content_for_layout, $style);
	echo $emogrifier->emogrify();
else:
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title><?php echo $title_for_layout;?></title>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
	<style type="text/css">
<?php echo $style; ?>
	</style>
</head>
<body>
<?php
echo $this->element('email/html/common_header');
echo $content_for_layout;
echo $this->element('email/html/common_footer');
?>
</body>
</html>
<?php endif; ?>