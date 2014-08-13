<?php
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Clear flash message without showing it
$this->Session->flash();

echo $content_for_layout;
echo implode("\n", $this->ZuluruHtml->getBuffer());
if (isset ($this->Js)) echo $this->Js->writeBuffer();
?>
