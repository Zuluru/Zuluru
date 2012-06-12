<p>Dear <?php echo $document['Person']['first_name']; ?>,</p>
<p>Your <?php echo $document['UploadType']['name']; ?> document has been approved by an administrator as being valid from <?php
echo $this->ZuluruTime->date($document['Upload']['valid_from']); ?> until <?php
echo $this->ZuluruTime->date($document['Upload']['valid_until']); ?>.</p>
<p>If you have any questions or concerns about this, please contact <?php echo $this->Html->link(Configure::read('email.admin_name'), 'mailto:' . Configure::read('email.admin_email')); ?>.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
