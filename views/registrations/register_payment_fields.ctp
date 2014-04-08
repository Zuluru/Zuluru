<?php
echo $this->element('registrations/register_payment_fields', array(
		'price' => $price['Price'],
		'registration' => (!empty($price['Registration']) ? $price['Registration'] : null),
));
?>