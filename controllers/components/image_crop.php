<?php
class ImageCropComponent extends Object {

	function uploadImage($upload, $temp_dir, $prefix) {
		if (empty($upload) || !array_key_exists('name', $upload)) {
			return false;
		}

		if (!is_dir ($temp_dir)) {
			mkdir ($temp_dir, 0777, true);
		}
		$file_ext = substr($upload['name'], strrpos($upload['name'], '.') + 1);
		$upload_target = $temp_dir . DS . $prefix . '.' . $file_ext;
		$max_width = 500;

		move_uploaded_file($upload['tmp_name'], $upload_target);
		chmod ($upload_target, 0777);
		$width = $this->getWidth($upload_target);
		$height = $this->getHeight($upload_target);
		// Scale the image if it is greater than the width set above
		if ($width > $max_width) {
			$scale = $max_width/$width;
			$uploaded = $this->resizeImage($upload_target, $width, $height, $scale);
		} else {
			$scale = 1;
			$uploaded = $this->resizeImage($upload_target, $width, $height, $scale);
		}

		return array('imageName' => "$prefix.$file_ext", 'imageWidth' => $this->getWidth($upload_target), 'imageHeight' => $this->getHeight($upload_target));
	}

	function getHeight($image) {
		$sizes = getimagesize($image);
		$height = $sizes[1];
		return $height;
	}

	function getWidth($image) {
		$sizes = getimagesize($image);
		$width = $sizes[0];
		return $width;
	}

	function resizeImage($image, $width, $height, $scale) {
		$newImageWidth = ceil($width * $scale);
		$newImageHeight = ceil($height * $scale);
		$newImage = imagecreatetruecolor($newImageWidth, $newImageHeight);
		$ext = strtolower(substr(basename($image), strrpos(basename($image), '.') + 1));
		$source = '';
		if($ext == 'png') {
			$source = imagecreatefrompng($image);
		} elseif($ext == 'jpg' || $ext == 'jpeg') {
			$source = imagecreatefromjpeg($image);
		} elseif($ext == 'gif') {
			$source = imagecreatefromgif($image);
		}
		imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newImageWidth, $newImageHeight, $width, $height);
		if($ext == 'png') {
			imagepng($newImage, $image, 9);
		} elseif($ext == 'jpg' || $ext == 'jpeg') {
			imagejpeg($newImage, $image, 90);
		} elseif($ext == 'gif') {
			imagegif($newImage, $image);
		}
		chmod($image, 0777);
		return $image;
	}

	function resizeThumbnailImage($thumb, $image, $width, $height, $start_width, $start_height, $scale) {
		$newImageWidth = ceil($width * $scale);
		$newImageHeight = ceil($height * $scale);
		$newImage = imagecreatetruecolor($newImageWidth, $newImageHeight);
		$ext = strtolower(substr(basename($image), strrpos(basename($image), '.') + 1));
		$source = '';
		if($ext == 'png') {
			$source = imagecreatefrompng($image);
		} elseif($ext == 'jpg' || $ext == 'jpeg') {
			$source = imagecreatefromjpeg($image);
		} elseif($ext == 'gif') {
			$source = imagecreatefromgif($image);
		}
		imagecopyresampled($newImage, $source, 0, 0, $start_width, $start_height, $newImageWidth, $newImageHeight, $width, $height);

		$ext = strtolower(substr(basename($thumb), strrpos(basename($thumb), '.') + 1));
		if($ext == 'png') {
			$result = imagepng($newImage, $thumb, 0);
		} elseif($ext == 'jpg' || $ext == 'jpeg') {
			$result = imagejpeg($newImage, $thumb, 90);
		} elseif($ext == 'gif') {
			$result = imagegif($newImage, $thumb);
		}
		if (!$result) {
			return false;
		}

		chmod($thumb, 0664);
		return $thumb;
	}

	function cropImage($thumb_width, $x1, $y1, $x2, $y2, $w, $h, $thumb, $image) {
		$scale = $thumb_width/$w;
		$cropped = $this->resizeThumbnailImage($thumb, $image, $w, $h, $x1, $y1, $scale);
		unlink ($image);
		if (!$cropped) {
			return false;
		}
		return $thumb;
	}
}
?>
