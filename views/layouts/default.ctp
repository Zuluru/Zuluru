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

		echo $this->ZuluruHtml->css(array (
				'https://code.jquery.com/ui/1.10.3/themes/redmond/jquery-ui.css',
				'zuluru/layout.css',
				'zuluru/look.css',
				'cssplay_flyout_ltr.css',
		));
		if (Configure::read('debug')) {
			echo $this->ZuluruHtml->css(array ('zuluru/debug.css'));
		}
		$language = Configure::read('personal.language');
		if (Configure::read('feature.uls') && empty($language)) {
			echo $this->ZuluruHtml->css(array (
					'uls/jquery.uls.css',
					'uls/jquery.uls.grid.css',
					'uls/jquery.uls.lcd.css',
			));
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
					'https://code.jquery.com/jquery-1.10.2.js',
					'https://code.jquery.com/ui/1.10.3/jquery-ui.js',
					'tooltip.js',
					'placeholder.js',
			));
			echo $this->Html->scriptBlock('
				jQuery.noConflict();
				jQuery.widget.bridge("uitooltip", jQuery.ui.tooltip);
				jQuery.widget.bridge("uibutton", jQuery.ui.button);
			');

			echo $this->element('layout/select_profile_js');
			echo $this->element('layout/select_language_js', compact('language'));
		}

		echo $scripts_for_layout;
	?>
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;" />
</head>
<body>
<?php echo $this->element('layout/header'); ?>
	<div id="zuluru">
		<div class="crumbs">
			<?php echo $this->Html->getCrumbs(' &raquo; '); ?>

			<span class="session_options" style="float: right;">
			<?php
			echo $this->element('layout/select_profile');
			echo $this->element('layout/select_language');
			?>
			</span>
		</div>
		<table class="container"><tr>
		<td class="sidebar-left">
			<?php echo $this->element("menus/$menu_element", array('menu_items' => $menu_items)); ?>

		</td>
		<td>
		<div id="content">
			<?php
				echo $this->Session->flash('auth');
				echo $this->Session->flash('email');
				echo $this->Session->flash();
				echo $this->element('notice');
				echo $content_for_layout;
				echo implode("\n", $this->ZuluruHtml->getBuffer());
			?>

		</div>
		</td>
		</tr></table>
<?php echo $this->element('layout/footer'); ?>
		<hr noshade="noshade" />
		<p><i>Powered by <a href="https://zuluru.org/"><?php echo ZULURU; ?></a>, version <?php echo ZULURU_MAJOR . '.' . ZULURU_MINOR . '.' . ZULURU_REVISION; ?> | <?php
		$body = htmlspecialchars ("I found a bug in {$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}{$this->here}");
		echo $this->Html->link('Report a bug', 'mailto:' . Configure::read('email.support_email') . '?subject=' . ZULURU . "%20Bug&body=$body") . ' on this page'; ?> | <?php
		echo $this->ZuluruHtml->iconLink('facebook.png', 'https://facebook.com/Zuluru', array(), array('target' => 'facebook')) . ' ' .
			$this->Html->link('Follow Zuluru on Facebook', 'https://facebook.com/Zuluru', array('target' => 'facebook'));
		?></i></p>
	</div>
	<?php // Various Ajax bits throughout the system target this ?>
	<div id="temp_update" style="display: none;"></div>

	<?php if (isset ($this->Js)) echo $this->Js->writeBuffer(); ?>
	<?php echo $this->element('sql_dump'); ?>
</body>
</html>
