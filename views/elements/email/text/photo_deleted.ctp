Dear <?php echo $person['Person']['first_name']; ?>,

Your photo has been reviewed by an administrator and rejected as unsuitable. To be approved, photos must be of you and only you (e.g. no logos or shots of groups or your pet or your car) and must clearly show your face. Photos may not include nudity or depiction of any activity that is illegal or otherwise contrary to the Spirit of Ultimate.

Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
