Dear <?php echo $document['Person']['first_name']; ?>,

An administrator has updated the valid dates for your <?php echo $document['UploadType']['name']; ?> document. It is now listed as being valid from <?php
echo $this->ZuluruTime->date($document['Upload']['valid_from']); ?> until <?php
echo $this->ZuluruTime->date($document['Upload']['valid_until']); ?>.

If you have any questions or concerns about this, please contact <?php echo $this->Html->link(Configure::read('email.admin_name'), 'mailto:' . Configure::read('email.admin_email')); ?>.

Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
