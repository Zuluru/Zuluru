<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb ($person['full_name']);
$this->Html->addCrumb (__('Upload Photo', true));
?>

<?php
$short = Configure::read('organization.short_name');
$long = Configure::read('organization.name');
?>
<div class="people view">
<h2><?php  echo __('Upload Photo', true) . ': ' . $person['full_name'];?></h2>

<p>By uploading a photo (only one photo per person allowed at the moment), you can further personalize your profile. In addition, others might use photos to determine who they mean to nominate as an all-star, for the purposes of drafting hat teams, recruiting players, etc.</p>
<p>You will have the opportunity to crop the photo after uploading. Crop areas must currently be square. Sorry, no support for uploading from mobile phones, yet.</p>
<p>This is an OPTIONAL feature of the <?php echo $long; ?> (<?php echo $short; ?>) web site.  Your photo will be available only to other <?php echo $short; ?> members who are logged in to this site and will not otherwise by publicly visible.</p>
<p><strong>Photos must be approved by an administrator before they will be visible by anyone, including you. To be approved, a photo must be of you and only you (e.g. no logos or shots of groups or your pet or your car) and must clearly show your face. Photos may not include nudity or depiction of any activity that is illegal or otherwise contrary to the Spirit of Ultimate. Determination of whether a photo is suitable is within the sole discretion of the <?php echo $short; ?>.</strong></p>
<p><strong>By uploading a photo you confirm that you are the legal copyright holder, or have obtained permission from the copyright holder to use it for this purpose.</strong></p>
<p><strong>By uploading a photo you consent to allow the <?php echo $short; ?> to publish this photograph as your profile picture on the <?php echo $short; ?> web site, and hereby release, waive and forever discharge the <?php echo $short; ?>, its employees, volunteers, officers and directors, and contractors, of and from all liability, injury, loss, death, claims, demands, damages, costs, expenses, actions and causes of action, whether in law or in equity, howsoever caused, arising from any actions related to the publishing of this photograph.</strong></p>

<?php
echo $this->Form->create(false, array('action' => 'photo_upload', 'enctype' => 'multipart/form-data'));
echo $this->Form->input('image', array('type' => 'file')); 
echo $this->Form->end(__('Upload', true));
?>

</div>
