<?php
class UserCacheHelper extends Helper {
	function read($key, $id = null) {
		return UserCacheComponent::getInstance()->read($key, $id);
	}
}
?>