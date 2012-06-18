<?php
echo $this->Html->para(null, __('If you prefer to pay offline via cheque, the online portion of your registration process is now complete, but you must do the following to make payment:', true));
echo Configure::read('registration.offline_payment_text');
?>
