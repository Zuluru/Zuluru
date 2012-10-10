<?php
class CropImageHelper extends Helper {
	var $helpers = array('Html', 'Javascript', 'Form');

	function createJavaScript($imgW, $imgH, $thumbW, $thumbH) {
		return $this->output("<script type=\"text/javascript\">
			function preview(img, selection) {
				var scaleX = $thumbW / selection.width;
				var scaleY = $thumbH / selection.height;

				jQuery('#thumbnail + div > img').css({
					width: Math.round(scaleX * $imgW) + 'px',
					height: Math.round(scaleY * $imgH) + 'px',
					marginLeft: '-' + Math.round(scaleX * selection.x1) + 'px',
					marginTop: '-' + Math.round(scaleY * selection.y1) + 'px'
				});
				jQuery('#x1').val(selection.x1);
				jQuery('#y1').val(selection.y1);
				jQuery('#x2').val(selection.x2);
				jQuery('#y2').val(selection.y2);
				jQuery('#w').val(selection.width);
				jQuery('#h').val(selection.height);
			}

			jQuery(document).ready(function ($) {
				$('#save_thumb').click(function() {
					var x1 = $('#x1').val();
					var y1 = $('#y1').val();
					var x2 = $('#x2').val();
					var y2 = $('#y2').val();
					var w = $('#w').val();
					var h = $('#h').val();
					if (x1==\"\" || y1==\"\" || x2==\"\" || y2==\"\" ||
						w==\"\" || h==\"\" || w < 50 || h < 50)
					{
						alert('Please choose an area to crop...');
						return false;
					} else {
						return true;
					}
				});
			});

			jQuery(window).load(function () {
				jQuery('#thumbnail').imgAreaSelect({ aspectRatio: '1:1', onSelectChange: preview });
			});
			</script>");
	}

	function createForm($tempDir, $imageName, $tH, $tW){
		$x1 = $this->Form->hidden('x1', array('value' => '', 'id' => 'x1'));
		$y1 = $this->Form->hidden('y1', array('value' => '', 'id' => 'y1'));
		$x2 = $this->Form->hidden('x2', array('value' => '', 'id' => 'x2',));
		$y2 = $this->Form->hidden('y2', array('value' => '', 'id' => 'y2'));
		$w = $this->Form->hidden('w', array('value' => '', 'id' => 'w'));
		$h = $this->Form->hidden('h', array('value' => '', 'id' => 'h'));
		$imgP = $this->Form->hidden('imageName', array('value' => $imageName));
		$imgTum = $this->Html->image($tempDir . $imageName, array('style' => 'float: left; margin: 10px;', 'id' => 'thumbnail', 'alt' => 'Create Thumbnail'));
		$imgTumPrev = $this->Html->image($tempDir . $imageName, array('style' => 'position:relative;', 'id' => 'thumbnail', 'alt' => 'Thumbnail Preview'));
		return $this->output($imgTum .
				$this->Html->tag('div', $imgTumPrev, array('style' => "position:relative; overflow:hidden; width:{$tW}px; height:{$tH}px;")) .
				"<br style=\"clear:both;\"/>$x1 $y1 $x2 $y2 $w $h $imgP");
	} 
}
?>
