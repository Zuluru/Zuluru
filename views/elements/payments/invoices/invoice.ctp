<?php
$reg_id_format = Configure::read('payment.reg_id_format');
?>
<table border=0 width=700>
	<tr><td colspan="4" class="center"><span class="warning-message"><?php __('Your Transaction has been Approved'); ?></span></td></tr>
	<tr><td colspan="4" class="center"><span class="warning-message"><?php __('Print this receipt for your records'); ?></span></td></tr>
	<tr><td colspan="4" bgcolor="#EEEEEE">&nbsp;</td></tr>
	<tr><td align="center" colspan="4"><h2 class="center"><?php echo Configure::read('organization.name'); ?></h2></td></tr>
	<tr><td align="center" colspan="4"><?php echo Configure::read('organization.address'); ?></td></tr>
	<tr><td align="center" colspan="4"><?php echo Configure::read('organization.address2'); ?></td></tr>
	<tr><td align="center" colspan="4"><?php echo Configure::read('organization.city'); ?>, <?php echo Configure::read('organization.province'); ?></td></tr>
	<tr><td align="center" colspan="4"><?php echo Configure::read('organization.postal'); ?></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="4"><?php echo Configure::read('organization.phone'); ?></td></tr>

	<tr><td align="center" colspan="4"><a href="<?php echo $_SERVER['REQUEST_SCHEME']; ?>://<?php echo $_SERVER["SERVER_NAME"]; ?>/"><?php echo $_SERVER["SERVER_NAME"]; ?></a></td></tr>
	<tr><td>&nbsp;</td></tr>

	<tr bgcolor="#EEEEEE"><td colspan="4"><b><?php __('Transaction Type'); ?>: <?php
		switch ($audit['transaction_name'])
		{
			case 'purchase':
			case 'cavv_purchase':
				__('Purchase');
				break;

			case 'idebit_purchase':
				__('Debit Purchase');
				break;

			case 'preauth':
			case 'cavv_preauth':
				__('Pre-authorization');
				break;

			default:
				__($audit['transaction_name']);
		}
	?></b></td></tr>
	<tr><td><?php __('Order ID'); ?>:</td><td><?php echo $audit['order_id']; ?></td></tr>
	<tr>
		<td><?php __('Date / Time'); ?>:</td><td><?php echo "{$audit['date']}  {$audit['time']}"; ?></td>
		<?php if (array_key_exists('approval_code', $audit)): ?>
		<td><?php __('Approval Code'); ?>:</td><td><?php echo $audit['approval_code']; ?></td>
		<?php else: ?>
		<td></td><td></td>
		<?php endif; ?>
	</tr>
	<tr>
		<td nowrap><?php __('Sequence Number'); ?>:</td><td><?php echo $audit['transaction_id']; ?></td>
		<?php if (array_key_exists('iso_code', $audit)): ?>
		<td><?php __('Response&nbsp;/&nbsp;ISO Code'); ?>:</td><td nowrap><?php echo "{$audit['response_code']}/{$audit['iso_code']}"; ?></td>
		<?php else: ?>
		<td><?php __('Response Code'); ?>:</td><td><?php echo $audit['response_code']; ?></td>
		<?php endif; ?>
	</tr>
	<tr>
		<td><?php printf(__('Amount (%s)', true), Configure::read('payment.currency')); ?>:</td><td>$<?php echo $audit['charge_total']; ?></td>
		<?php if (array_key_exists('f4l4', $audit)): ?>
		<td><?php __('Card #'); ?>:</td><td><?php echo $audit['f4l4']; ?></td>
		<?php else: ?>
		<td></td><td></td>
		<?php endif; ?>
	</tr>
	<?php if (array_key_exists('message', $audit)): ?>
	<tr><td colspan="4" nowrap><?php __('Message'); ?>: <?php echo $audit['message']; ?></td></tr>
	<?php endif; ?>
	<tr><td>&nbsp;</td></tr>

	<?php if ($audit['transaction_name'] == 'idebit_purchase'): ?>
	<tr bgcolor="#EEEEEE"><td colspan="4"><b><?php __('INTERAC&reg; Online Information'); ?></b></td></tr>
	<tr>
		<td><?php __('Issuer Name'); ?>:</td><td><?php echo $audit['issuer']; ?></td>
	</tr>
	<tr>
		<td><?php __('Issuer Confirmation'); ?>:</td><td><?php echo $audit['issuer_invoice']; ?></td>
	</tr>
	<tr>
		<td><?php __('Issuer Invoice #'); ?>:</td><td><?php echo $audit['issuer_confirmation']; ?></td>
	</tr>
	<tr><td>&nbsp;</td></tr>
	<?php endif; ?>

	</table>

	<table border="0" cellspacing="1" cellpadding="3" width="700">
	<tr><td colspan=5 bgcolor="#EEEEEE"><strong><?php __('Item Information'); ?></strong></td></tr>
	<tr>
		<td bgcolor="#DDDDDD" width=100><strong><?php __('ID'); ?></strong></td>
		<td bgcolor="#DDDDDD" width=350><strong><?php __('Description'); ?></strong></td>
		<td bgcolor="#DDDDDD" width=50 align="middle"><strong><?php __('Quantity'); ?></strong></td>
		<td bgcolor="#DDDDDD" width=100 align="right"><strong><?php __('Unit Cost'); ?></strong></td>
		<td bgcolor="#DDDDDD" width=100 align="right"><strong><?php __('Subtotal'); ?></strong></td>
	</tr>
<?php
foreach ($registrations as $registration):
	list ($cost, $tax1, $tax2) = Registration::paymentAmounts($registration);
?>
	<tr>
		<td valign="top"><?php echo sprintf($reg_id_format, $registration['Event']['id']); ?></td>
		<td valign="top"><?php echo Registration::longDescription($registration); ?></td>
		<td valign="top">1</td>
		<td valign="top" align="right">$<?php echo $cost; ?></td>
		<td valign="top" align="right">$<?php echo $cost; ?></td>
	</tr>

	<?php if ($tax1 > 0): ?>
	<tr>
		<td></td><td></td><td></td>
		<td align="right"><?php echo Configure::read('payment.tax1_name'); ?>:</td>
		<td align="right">$<?php echo $tax1; ?></td>
	</tr>
	<?php else: ?>
	<tr><td>&nbsp;</td></tr>
	<?php endif; ?>

	</tr>
	<?php if ($tax2 > 0): ?>
	<tr>
		<td></td><td></td><td></td>
		<td align="right"><?php echo Configure::read('payment.tax2_name'); ?>:</td>
		<td align="right">$<?php echo $tax2; ?></td>
	<?php else: ?>
	<tr><td>&nbsp;</td></tr>
	<?php endif; ?>
<?php endforeach; ?>

	<tr>
		<td></td><td></td><td></td><td align="right"><?php __('Total'); ?>:</td>
		<td align="right">$<?php echo $audit['charge_total']; ?>&nbsp;(<?php echo Configure::read('payment.currency'); ?>)</td>
	</tr>
	</table>

	<table width="700" cellspacing=3 cellpadding=3>
	<tr><td bgcolor="#EEEEEE"><strong><?php __('Customer Information'); ?></strong></td></tr>
	<tr>
		<td><?php echo $registration['Person']['full_name']; ?></td>
	</tr>
	<tr>
		<td><?php echo $registration['Person']['addr_street']; ?></td>
	</tr>
	<tr>
		<td><?php echo $registration['Person']['addr_city']; ?></td>
	</tr>
	<tr>
		<td><?php echo $registration['Person']['addr_prov']; ?></td>
	</tr>
	<tr>
		<td><?php echo $registration['Person']['addr_postalcode']; ?></td>
	</tr>
	<tr>
		<td><?php echo $registration['Person']['addr_country']; ?></td>
	</tr>
	<tr>
		<td><?php echo $registration['Person']['home_phone']; ?></td>
	</tr>
	<tr><td>&nbsp;</td></tr>

	<tr><td><?php echo $this->element('payments/refund'); ?></td></tr>

</table>