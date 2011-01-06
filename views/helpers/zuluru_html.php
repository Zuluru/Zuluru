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
}

?>
