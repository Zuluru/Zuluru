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
				'cake.generic',
				'zuluru',
		));
		$css = Configure::read('additional_css');
		if (!empty ($css)) {
			// These files are assumed to come from the normal location, not the Zuluru location.
			// A complete path can always be given, if required.
			echo $this->Html->css($css);
		}

		if (isset ($this->Js)) {
			echo $this->ZuluruHtml->script(array(
					'jquery-1.4.2.min.js',
			));
		}
		echo $scripts_for_layout;
	?>

</head>
<body onresize="resizeMap()" onunload="GUnload()" style="padding: 0;">
	<div id="map" style="margin: 0; padding: 0; width: 70%; height: 400px; float: left;"></div>
	<div style="margin: 0; padding-left: 1em; width: 27%; float: left;">
		<?php
			echo $this->Session->flash();
			echo $content_for_layout;
		?>

	</div>
	<?php if (isset ($this->Js)) echo $this->Js->writeBuffer(); ?>
</body>
</html>
