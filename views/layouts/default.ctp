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
				'http://code.jquery.com/ui/1.10.3/themes/redmond/jquery-ui.css',
				'zuluru/layout',
				'zuluru/look',
				'cssplay_flyout_ltr',
		));
		if (Configure::read('debug')) {
			echo $this->ZuluruHtml->css(array ('zuluru/debug'));
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
<?php echo $this->ZuluruHtml->css('zuluru/ie_fixes'); ?>
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
					'tooltip.js',
					'placeholder.js',
			));
			echo $this->Html->scriptBlock('jQuery.noConflict();');
		}
		if (Configure::read('feature.uls') && empty($language)) {
			echo $this->ZuluruHtml->script(array(
					'jquery.uls/src/jquery.uls.data.js',
					'jquery.uls/src/jquery.uls.data.utils.js',
					'jquery.uls/src/jquery.uls.lcd.js',
					'jquery.uls/src/jquery.uls.languagefilter.js',
					'jquery.uls/src/jquery.uls.regionfilter.js',
					'jquery.uls/src/jquery.uls.core.js',
			));
			echo $this->Js->buffer('
				jQuery(".uls-trigger").uls({
					onSelect : function(language) {
						window.location = "' . $this->Html->url(array('controller' => 'all', 'action' => 'language'), true) . '/lang:" + language + "/return:1";
					},
					languages: {' . Configure::read('available_translation_strings') . '}
				});
			');
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

			<?php if (Configure::read('feature.uls') && empty($language)): ?>
			<span style="float: right;" class="uls-trigger"><?php echo Configure::read('Config.language_name'); ?></span>
			<?php endif; ?>
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
		<p><i>Powered by <a href="http://zuluru.org/"><?php echo ZULURU; ?></a>, version <?php echo ZULURU_MAJOR . '.' . ZULURU_MINOR . '.' . ZULURU_REVISION; ?> | <?php
		$body = htmlspecialchars ("I found a bug in http://{$_SERVER['HTTP_HOST']}{$this->here}");
		echo $this->Html->link('Report a bug', 'mailto:' . Configure::read('email.support_email') . '?subject=' . ZULURU . "%20Bug&body=$body") . ' on this page'; ?> | <?php
		echo $this->ZuluruHtml->iconLink('facebook.png', 'http://facebook.com/Zuluru', array(), array('target' => 'facebook')) . ' ' .
			$this->Html->link('Follow Zuluru on Facebook', 'http://facebook.com/Zuluru', array('target' => 'facebook'));
		?></i></p>
	</div>
	<?php // Various Ajax bits throughout the system target this ?>
	<div id="temp_update" style="display: none;"></div>

	<?php if (isset ($this->Js)) echo $this->Js->writeBuffer(); ?>
	<?php echo $this->element('sql_dump'); ?>
</body>
</html>
