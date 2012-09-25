<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb ($person['full_name']);
$this->Html->addCrumb (__('Upload Document', true));
?>

<?php
$short = Configure::read('organization.short_name');
$long = Configure::read('organization.name');
$max = ini_get('upload_max_filesize');
$unit = substr($max,-1);
if ($unit == 'M' || $unit == 'K') {
	$max .= 'b';
}
?>
<div class="people view">
<h2><?php  echo __('Upload Document', true) . ': ' . $person['full_name'];?></h2>

<p>Some site functionality may require that you upload a document to prove a claim. For example, junior players might require a waiver signed by a parent or guardian, or students might need to submit proof of enrolment to qualify for a discount.</p>
<p><strong>Documents must be approved by an administrator before the related function will be allowed. This may take up to two business days to process.</strong></p>

<?php
echo $this->Form->create(false, array('url' => Router::normalize($this->here), 'enctype' => 'multipart/form-data'));
echo $this->Form->input('document_type', array(
		'options' => $types,
		'empty' => 'Select one:',
		'default' => $type,
));
echo $this->Form->input('document', array('type' => 'file'));
echo $this->Form->end(__('Upload', true));
?>

</div>
