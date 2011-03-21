<?php

class ZuluruHtmlHelper extends HtmlHelper {
	var $helpers = array('Text');

	/**
	 * Extend the default link function by allowing for shortening link titles.
	 */
	function link($title, $url = null, $options = array(), $confirmMessage = false) {
		if (is_array ($options) && array_key_exists ('max_length', $options)) {
			$max = $options['max_length'];
			unset ($options['max_length']);
			if (strlen ($title) > $max) {
				$options['title'] = $title;
				$title = $this->Text->truncate ($title, $max);
			}
		}
		return parent::link ($title, $url, $options, $confirmMessage);
	}

	/**
	 * Include Zuluru-specific CSS files from the configured location.
	 * This replicates a bare minimum of the main HtmlHelper's css function,
	 * to avoid prefixing absolute paths. Everything else is passed to the
	 * parent for processing.
	 */
	function css($path, $rel = null, $options = array()) {
		$base = Configure::read('urls.zuluru_css');

		if (is_array($path)) {
			foreach ($path as $i) {
				echo $this->css($i, $rel, $options);
			}
			return;
		}

		if (strpos($path, '://') !== false) {
			$url = $path;
		} else {
			if ($path[0] !== '/') {
				$url = $base . $path;
			}
		}
		echo parent::css($url, $rel, $options);
	}

	/**
	 * Include Zuluru-specific JS files from the configured location.
	 * This replicates a bare minimum of the main HtmlHelper's js function,
	 * to avoid prefixing absolute paths. Everything else is passed to the
	 * parent for processing.
	 */
	function script($path, $options = array()) {
		$base = Configure::read('urls.zuluru_js');

		if (is_array($path)) {
			foreach ($path as $i) {
				echo $this->script($i, $options);
			}
			return;
		}

		if (strpos($path, '://') !== false) {
			$url = $path;
		} else {
			if ($path[0] !== '/') {
				$url = $base . $path;
			}
		}
		echo parent::script($url, $options);
	}

	/**
	 * Create links from images.
	 */
	function imageLink($img, $url, $imgOptions = array(), $urlOptions = array()) {
		echo parent::link (parent::image ($img, $imgOptions),
							$url, array_merge (array('escape' => false), $urlOptions));
	}

	/**
	 * Create pop-up help links.
	 */
	function help($url) {
		// Add "/help" to the beginning of whatever URL is provided
		$url = array_merge (array('controller' => 'help'), $url);

		// Add the help image, with a link to a pop-up with the help
		$id = implode ('_', array_values ($url));
		echo $this->imageLink('help.png', $url, array(
			'id' => $id,
			'alt' => __('[Help]', true),
			'title' => __('Additional help', true),
		), array('target' => '_blank'));

		// Add an invisible div with the help text in it, and attach an event to the image
		$view =& ClassRegistry::getObject('view');
		$element = implode ('/', array_values ($url));
		$title = array_map (array('Inflector', 'humanize'), array_values ($url));
		echo $this->tag ('div', $view->element ($element), array(
				'id' => "{$id}_div",
				'class' => 'help',
				'title' => implode (' &raquo; ', $title),
		));
		$view->Js->get("#$id")->event('click', "show_help('$id');");

		if (!isset ($this->dialogHandlerOutput)) {
			echo $view->Html->scriptBlock ("
function show_help(id) {
	$('#' + id + '_div').dialog({
		buttons: {
			'Close': function() { $('#' + id + '_div').dialog('close'); },
		},
		modal: true,
		resizable: false,
		width: 480,
		height: 250
	});
}
", array('inline' => false));
			$this->dialogHandlerOutput = true;
		}

	}
}

?>
