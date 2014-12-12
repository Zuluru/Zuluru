<?php
$view_contact = $is_admin || $is_manager || $is_coordinator || $is_captain || $is_my_captain || $is_my_coordinator || $is_division_captain;
?>
N:<?php echo "{$person['last_name']};{$person['first_name']}"; ?>

FN:<?php echo $person['full_name']; ?>

<?php if (Configure::read('profile.home_phone') && !empty($person['home_phone']) &&
			($view_contact || ($is_logged_in && $person['publish_home_phone']))):?>
TEL;HOME;VOICE:<?php echo $person['home_phone']; ?>
<?php endif; ?>

<?php if (Configure::read('profile.work_phone') && !empty($person['work_phone']) &&
			($view_contact || ($is_logged_in && $person['publish_work_phone']))):?>
TEL;WORK;VOICE:<?php echo $person['work_phone']; ?>
<?php endif; ?>

<?php if (Configure::read('profile.mobile_phone') && !empty($person['mobile_phone']) &&
			($view_contact || ($is_logged_in && $person['publish_mobile_phone']))):?>
TEL;CELL;VOICE:<?php echo $person['mobile_phone']; ?>
<?php endif; ?>

<?php if (!empty($person['email']) && ($view_contact || ($is_logged_in && $person['publish_email']))):?>
EMAIL;PREF;INTERNET:<?php echo $person['email']; ?>

<?php endif; ?>

<?php if (!empty($person['alternate_email']) && ($view_contact || ($is_logged_in && $person['publish_alternate_email']))):?>
EMAIL;INTERNET:<?php echo $person['alternate_email']; ?>

<?php endif; ?>
