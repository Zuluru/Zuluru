<?php
class UserCacheHelper extends Helper {
	function read($key, $id = null) {
		return UserCacheComponent::getInstance()->read($key, $id);
	}

	function currentId() {
		return UserCacheComponent::getInstance()->currentId();
	}

	function realId() {
		return UserCacheComponent::getInstance()->realId();
	}
}
?>