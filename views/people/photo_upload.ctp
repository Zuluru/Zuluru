<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb ($person['full_name']);
$this->Html->addCrumb (__('Upload Photo', true));
?>

<div class="people view">
<h2><?php  echo __('Upload Photo', true) . ': ' . $person['full_name'];?></h2>

<p>By uploading a photo (only one photo per person allowed at the moment), you can further personalize your profile. In addition, others might use photos to figure out who they mean to nominate as an all-star, for the purposes of drafting hat teams, etc.</p>
<p>As with most information collected by this site, your photo will be available only to other members who are logged in.</p>
<p>Photos must be approved by an administrator before they will be visible by anyone, including you. To be approved, photos must be of you and only you (e.g. no logos or shots of groups or your pet or your car) and must clearly show your face. Photos may not include nudity or depiction of any activity that is illegal or otherwise contrary to the Spirit of Ultimate.</p>
<p>You will have the opportunity to crop the photo after uploading; crop areas must currently be square.</p>
<p>By uploading a photo you indicate that you are the legal copyright holder, or have obtained permission from the copyright holder to use it for this purpose.</p>
<p>Sorry, no support for uploading from mobile phones, yet.</p>
<?php
echo $this->Form->create(false, array('action' => 'photo', 'enctype' => 'multipart/form-data'));
echo $this->Form->input('image',array('type' => 'file')); 
echo $this->Form->end(__('Upload', true));
?>

</div>