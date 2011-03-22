<?php
echo $content_for_layout;
if (isset ($this->Js)) echo $this->Js->writeBuffer();
?>
