<p>Emogrifier is a pre-processor for converting CSS definitions into inline styles, useful for improving the rendering results of email in various email clients.</p>
<p>To use this, download emogrifier.php from <a href="http://www.pelagodesign.com/sidecar/emogrifier/">http://www.pelagodesign.com/sidecar/emogrifier/</a> and place it in /zuluru/libs.</p>
<p>The Emogrifier library also requires that the DOM and MBSTRING extensions be enabled in your PHP installation. You do <?php
if (!extension_loaded('dom')) echo 'NOT '; ?>have the DOM extension, and do <?php
if (!extension_loaded('mbstring')) echo 'NOT '; ?>have the MBSTRING extension.</p>
<p>Place your email styles in email.css in your web root folder.</p>
