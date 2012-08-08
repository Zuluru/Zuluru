<p>Dear <?php echo $document['Person']['first_name']; ?>,</p>
<p>Your <?php echo $document['UploadType']['name']; ?> document<?php
if ($document['Upload']['approved']): ?>
, valid from <?php
echo $this->ZuluruTime->date($document['Upload']['valid_from']); ?> to <?php
echo $this->ZuluruTime->date($document['Upload']['valid_until']); ?>, has been removed by an administrator.</p>
<?php if ($document['Upload']['valid_until'] < date('Y-m-d')): ?>
<p>As the validity date has passed, this is most likely simply a housekeeping matter and can be safely ignored.</p>
<?php endif; ?>
<?php else: ?>
 has been reviewed by an administrator and rejected as unsuitable for the desired purpose. Please review your upload to ensure that it is the correct document and easily legible, and try again.</p><?php
endif; ?>
<?php if (isset($comment)): ?>
<p><?php echo $comment; ?></p>
<?php endif; ?>
<p>If you have any questions or concerns about this, please contact <?php echo $this->Html->link(Configure::read('email.admin_name'), 'mailto:' . Configure::read('email.admin_email')); ?>.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
