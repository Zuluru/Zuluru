<?php
// Clear flash message without showing it
$this->Session->flash();

echo $content_for_layout;
if (isset ($this->Js)) echo $this->Js->writeBuffer();
?>
