<?php

class ZuluruHtmlHelper extends HtmlHelper {
	var $helpers = array('Text');

/**
 * HTML that is queued for output
 *
 * @var array
 * @see HtmlPlusHelper::buffer()
 * @access private
 */
	var $__bufferedHtml = array();

/**
 * List of help IDs that have already been included on the page.
 */
	var $__helpShown = array();

	/**
	 * Extend the default tag function by allowing for arrays of text
	 */
	function tag($name, $text = null, $options = array()) {
		if (is_array($text)) {
			$output = '';
			foreach ($text as $t) {
				$output .= parent::tag($name, $t, $options);
			}
			return $output;
		} else {
			return parent::tag($name, $text, $options);
		}
	}

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
			$paths = array();
			foreach ($path as $i) {
				$paths[] = $this->css($i, $rel, $options);
			}
			return implode("\n", $paths);
		}

		if (strpos($path, '://') !== false) {
			$url = $path;
		} else {
			if ($path[0] !== '/') {
				$url = $base . $path;
			}
		}
		return parent::css($url, $rel, $options);
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
			$scripts = array();
			foreach ($path as $i) {
				$scripts[] = $this->script($i, $options);
			}
			return implode("\n", $scripts);
		}

		if (strpos($path, '://') !== false) {
			$url = $path;
		} else {
			if ($path[0] !== '/') {
				$url = $base . $path;
			}
		}
		return parent::script($url, $options);
	}

	/**
	 * Create links from images.
	 */
	function imageLink($img, $url, $imgOptions = array(), $urlOptions = array(), $confirmMessage = false) {
		return $this->link (parent::image ($img, $imgOptions),
							$url, array_merge (array('escape' => false), $urlOptions), $confirmMessage);
	}

	/**
	 * Use local settings to select an icon.
	 */
	function icon($img, $imgOptions = array()) {
		$base_folder = Configure::read('folders.icon_base');
		$base_url = Configure::read('urls.zuluru_img');

		$icon_pack = Configure::read('icon_pack');
		if ($icon_pack == 'default') {
			$icon_pack = '';
		} else {
			$icon_pack = "/$icon_pack";
		}

		if (file_exists("$base_folder$icon_pack/$img")) {
			return parent::image ("$base_url$icon_pack/$img", $imgOptions);
		}
		if (file_exists("$base_folder/$img")) {
			return parent::image ("$base_url/$img", $imgOptions);
		}
		return parent::image($img, $imgOptions);
	}

	/**
	 * Create links from icons.
	 */
	function iconLink($img, $url, $imgOptions = array(), $urlOptions = array(), $confirmMessage = false) {
		return $this->link ($this->icon ($img, $imgOptions),
							$url, array_merge (array('escape' => false), $urlOptions), $confirmMessage);
	}

	/**
	 * Create pop-up help links.
	 */
	function help($url) {
		$help = '';

		// Add "/help" to the beginning of whatever URL is provided
		$url = array_merge (array('controller' => 'help'), $url);

		// Prevent any given help from being added more than once
		$id = implode ('_', array_values ($url));
		if (in_array($id, $this->__helpShown)) {
			return;
		}

		// Add the help image, with a link to a pop-up with the help
		$help .= $this->iconLink('help_16.png', $url, array(
			'id' => $id,
			'alt' => __('[Help]', true),
			'title' => __('Additional help', true),
		), array('target' => 'help'));

		// Build the link for suggestions
		$body = htmlspecialchars ('I have a suggestion for the Zuluru online help page at ' . implode(' : ', $url));
		$add = $this->tag('hr') .
				$this->para(null, sprintf(__('If you have suggestions for additions, changes or other improvements to this online help, please send them to %s.', true),
					$this->link (Configure::read('email.support_email'), 'mailto:' . Configure::read('email.support_email') . '?subject=' . ZULURU . "%20Online%20Help%20Suggestion&body=$body")
				));

		// Add an invisible div with the help text in it, and attach an event to the image
		$view =& ClassRegistry::getObject('view');
		$element = implode ('/', array_values ($url));
		$title = array_map (array('Inflector', 'humanize'), array_values ($url));
		$title = array_map ('__', array_values($title), array_fill(0, count($title), true));
		$help .= $this->tag ('div', $view->element ($element) . $add, array(
				'id' => "{$id}_div",
				'class' => 'help_dialog',
				'title' => implode (' :: ', $title),
		));

		$link = Router::url ($url);
		$view->Js->get("#$id")->event('click', "show_help('$id', '$link');");

		if (empty($this->__helpShown)) {
			// The first little bit here is from http://jsbin.com/icuguz/12/edit,
			// referenced by http://bugs.jqueryui.com/ticket/4731, to keep the
			// focus from jumping to the first tabbable element in the dialog
			$open = __('Open this help page in a new window', true);
			$close = __('Close', true);
			$view->Html->scriptBlock ("
jQuery.ui.dialog.prototype._focusTabbable = function() {
	this.uiDialogTitlebarClose.focus();
};

function show_help(id, link) {
	jQuery('#' + id + '_div').dialog({
		buttons: {
			'$open': function() {
				jQuery('#' + id + '_div').dialog('close');
				window.open(link, '_blank');
			},
			'$close': function() {
				jQuery('#' + id + '_div').dialog('close');
			}
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

		$this->__helpShown[] = $id;

		return $help;
	}

	function buffer($html) {
		$this->__bufferedHtml[] = $html;
	}

/**
 * Get all the buffered HTML
 *
 * @param boolean $clear Whether or not to clear the HTML caches (default true)
 * @return array Array of HTMLs added to the request.
 * @access public
 */
	function getBuffer($clear = true) {
		$buffer = $this->__bufferedHtml;
		if ($clear) {
			$this->__bufferedHtml = array();
		}
		return $buffer;
	}
}

?>
