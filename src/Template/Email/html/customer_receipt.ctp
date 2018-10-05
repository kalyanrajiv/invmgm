<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');?>
<?php $vatPercentage = $productReceipt['vat'];
$address1 = $address2 = $city = $state = $postalCode = "";
//pr($customer_table);die;
if(!empty($customer_table) && array_key_exists(0,$customer_table)&&$customer_table[0]['id']>0){
	if($customer_table[0]['address_1']){
		$address1 = $customer_table[0]['address_1'].",";
	}
	if($customer_table[0]['address_2']){
		$address2 = $customer_table[0]['address_2'].",";
	}
	if($customer_table[0]['city']){
		$city = $customer_table[0]['city'].",";
	}
	if($customer_table[0]['state']){
		$state = $customer_table[0]['state'].",";
	}
	if($customer_table[0]['zip']){
		$postalCode = $customer_table[0]['zip'];
	}
	
}else{
	if($productReceipt['address_1']){
		$address1 = $productReceipt['address_1'].",";
	}
	if($productReceipt['address_2']){
		$address2 = $productReceipt['address_2'].",";
	}
	if($productReceipt['city']){
		$city = $productReceipt['city'].",";
	}
	if($productReceipt['state']){
		$state = $productReceipt['state'].",";
	}
	if($productReceipt['zip']){
		$postalCode = $productReceipt['zip'];
	}
}
//pr($sale_table);die;
?>
<table>
	<tr>
		<td>
			<b>Dear Customer</b>,
		</td>
	</tr>
	<tr><td style="height:10px;"></td></tr>
	<tr>
		<td></td>
		<td>Thank you for shopping with us. Please see below copy of your invoice.</td>
	</tr>
	<tr><td style="height:20px;"></td></tr>
</table>

<?php echo $html;?>