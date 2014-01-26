<?php echo $this->Html->doctype('xhtml-trans'); ?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php echo $this->Html->charset(); ?>
	<?php
		echo $this->ZuluruHtml->css(array (
				'zuluru/iframe',
		));
		if (Configure::read('debug')) {
			echo $this->ZuluruHtml->css(array ('zuluru/debug'));
		}
	?>
	<?php
		$css = Configure::read('additional_css');
		if (!empty ($css)) {
			// These files are assumed to come from the normal location, not the Zuluru location.
			// A complete path can always be given, if required.
			echo $this->Html->css($css);
		}
	?>
</head>
<body>
	<div id="iframe">
		<?php
			echo $content_for_layout;
		?>

	</div>
</body>
</html>

