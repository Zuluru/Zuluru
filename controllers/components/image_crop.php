<?php
class ImageCropComponent extends Object {

	function uploadImage($upload, $webpath, $prefix) {
		if (empty($upload) || !array_key_exists('name', $upload)) {
			return false;
		}

		$upload_dir = WWW_ROOT . str_replace('/', DS, trim ($webpath, '/'));
		if (!is_dir ($upload_dir)) {
			mkdir ($upload_dir, 0777, true);
		}
		$file_ext = substr($upload['name'], strrpos($upload['name'], '.') + 1);
		$upload_target = $upload_dir . DS . $prefix . '.' . $file_ext;
		$max_width = 800;

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

		return array('imagePath' => "$webpath/$prefix.$file_ext", 'imageName' => $prefix.$file_ext, 'imageWidth' => $this->getWidth($upload_target), 'imageHeight' => $this->getHeight($upload_target));
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
		if($ext == 'png' || $ext == 'PNG') {
			imagepng($newImage, $image, 9);
		} elseif($ext == 'jpg' || $ext == 'jpeg' || $ext == 'JPG' || $ext == 'JPEG') {
			imagejpeg($newImage, $image, 90);
		} elseif($ext == 'gif' || $ext == 'GIF') {
			imagegif($newImage, $image);
		}
		chmod($image, 0777);
		return $image;
	}

	function resizeThumbnailImage($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale) {
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

		if($ext == 'png' || $ext == 'PNG') {
			imagepng($newImage, $thumb_image_name, 0);
		} elseif($ext == 'jpg' || $ext == 'jpeg' || $ext == 'JPG' || $ext == 'JPEG') {
			imagejpeg($newImage, $thumb_image_name, 90);
		} elseif($ext == 'gif' || $ext == 'GIF') {
			imagegif($newImage, $thumb_image_name);
		}

		chmod($thumb_image_name, 0777);
		return $thumb_image_name;
	}

	function cropImage($thumb_width, $x1, $y1, $x2, $y2, $w, $h, $thumb, $image) {
		$scale = $thumb_width/$w;
		$image_path = WWW_ROOT . str_replace('/', DS, $image);
		$ext = strtolower(substr(basename($image), strrpos(basename($image), '.') + 1));
		$thumb_path = WWW_ROOT . str_replace('/', DS, $thumb) . '.' . $ext;
		$cropped = $this->resizeThumbnailImage($thumb_path, $image_path, $w, $h, $x1, $y1, $scale);
		unlink ($image_path);
		return $thumb . '.' . $ext;
	}
}
?>
