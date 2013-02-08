<?php echo $this->Html->doctype('xhtml-trans'); ?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php
		$crumbs = $this->Html->getCrumbs(' &raquo; ');
		if (!empty ($crumbs))
			echo $crumbs . ' : ';
		echo Configure::read('site.name') . ' : ' .
			Configure::read('organization.name');
		?>
	</title>
	<?php
	// Attempt to figure out where the CSS file is located, so we can give dompdf an absolute path.
	// TODO: The name of the style sheet will need to be generalized if we use PDFize for anything else.
	$css_included = false;
	foreach (array(
			APP . 'webroot',
			$_SERVER['DOCUMENT_ROOT'],
	) as $path)
	{
		$file = $path . DS . 'css' . DS . 'zuluru' . DS . 'stat_sheet.css';
		if (file_exists($file)) {
			echo $this->Html->css($file);
			$css_included = true;
			break;
		}
	}
	if (!$css_included) {
		// Use a URL and hope for the best...
		echo $this->Html->css('http://' . $_SERVER['HTTP_HOST'] . '/css/zuluru/stat_sheet.css');
	}
	?>
</head>
<body>
<?php
echo $content_for_layout;
echo implode("\n", $this->ZuluruHtml->getBuffer());
?>

</body>
</html>
