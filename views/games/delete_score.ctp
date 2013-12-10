<?php
if (isset($error)) {
	echo $this->Html->scriptBlock ("alert('$error');");
} else {
	echo $this->Html->scriptBlock ("jQuery('#detail_$id').remove();");
}
?>
