
<?php
if(!isset($paymentType)){
	$paymentType = array();
}
if(empty($paymentType)){
	$paymentType['Select Payment Method'] =   'Select Payment Method';
	$paymentType['Cheque'] = 'Cheque';
	$paymentType['Cash'] = 'Cash';
	$paymentType['Bank Transfer'] = 'Bank Transfer';
	$paymentType['Card'] = 'Card';
	$paymentType['On Credit'] = 'On Credit';
}

?>
<div id='paymentDiv' style="overflow: scroll; width: 580px; height: 700px; font-size: 9px;">
	<fieldset>
		<table style='width:350px;'>
			<tr>
				<td><span style=''><h2><?php echo __('Performa'); ?></h2></span></td>
				<td>Please Press Submit to Create Performa</td>
			</tr>
		</table>
		<form action="/home/update_performa" method="post">
			<input type="submit" name="submit" value="submit" />
		</form>
		<div style="margin-top: -21px;margin-left: 91px;"><input type="submit" name="cancel" value="cancel"  class = "pay_cancel_button"/></div>
	</fieldset>
</div>
        
