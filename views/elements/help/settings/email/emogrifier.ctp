<p><?php __('Emogrifier is a pre-processor for converting CSS definitions into inline styles, useful for improving the rendering results of email in various email clients.'); ?></p>
<p><?php printf(__('To use this, download emogrifier.php from %s and place it in %s.', true),
	$this->Html->link('http://www.pelagodesign.com/sidecar/emogrifier/', 'http://www.pelagodesign.com/sidecar/emogrifier/'),
	'zuluru/libs'
); ?></p>
<p><?php printf(__('The Emogrifier library also requires that the DOM and MBSTRING extensions be enabled in your PHP installation. You do%s have the DOM extension, and do%s have the MBSTRING extension.', true),
	(extension_loaded('dom') ? '' : ' ' . __('NOT', true)),
	(extension_loaded('mbstring') ? '' : ' ' . __('NOT', true))
); ?></p>
<p><?php __('Place your email styles in email.css in your web root folder.'); ?></p>
