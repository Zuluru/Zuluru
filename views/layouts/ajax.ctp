<?php
// Clear flash message without showing it
$this->Session->flash();

echo $content_for_layout;
echo implode("\n", $this->ZuluruHtml->getBuffer());
if (isset ($this->Js)) echo $this->Js->writeBuffer();
?>
