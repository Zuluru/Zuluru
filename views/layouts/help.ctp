<?php
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
?>
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
		echo $this->Html->meta('icon');
		echo $this->Html->meta(array('name' => 'no_cms_wrapper'));

		echo $this->ZuluruHtml->css(array (
				'http://code.jquery.com/ui/1.10.3/themes/redmond/jquery-ui.css',
				'zuluru/layout.css',
				'zuluru/look.css',
		));
		if (Configure::read('debug')) {
			echo $this->ZuluruHtml->css(array ('zuluru/debug.css'));
		}
	?>
<!--[if lt IE 8]>
<?php echo $this->ZuluruHtml->css('zuluru/ie_fixes.css'); ?>
<![endif]-->
	<?php
		$css = Configure::read('additional_css');
		if (!empty ($css)) {
			// These files are assumed to come from the normal location, not the Zuluru location.
			// A complete path can always be given, if required.
			echo $this->Html->css($css);
		}

		if (isset ($this->Js)) {
			echo $this->ZuluruHtml->script(array(
					'http://code.jquery.com/jquery-1.10.2.js',
					'http://code.jquery.com/ui/1.10.3/jquery-ui.js',
			));
			echo $this->Html->scriptBlock('jQuery.noConflict();');
		}
		echo $scripts_for_layout;
	?>
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;" />
</head>
<body>
	<div id="zuluru">
		<div class="crumbs">
			<?php echo $this->Html->getCrumbs(' &raquo; '); ?>

		</div>

		<div id="content">
			<?php
				echo $content_for_layout;
			?>

		</div>
	</div>
	<?php if (isset ($this->Js)) echo $this->Js->writeBuffer(); ?>
	<?php echo $this->element('sql_dump'); ?>
</body>
</html>
