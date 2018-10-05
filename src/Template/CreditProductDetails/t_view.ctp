<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$jQueryURL = "http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
if(defined('URL_SCHEME')){
	$jQueryURL = URL_SCHEME."ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
}
?>
<script type="text/javascript" src="<?php echo $jQueryURL;?>"></script>
<?php echo $this->Html->script('jquery.printElement');
$siteBaseUrl = Configure::read('SITE_BASE_URL');
//pr($user_name);?>
<input type='button' id='printSelected' name='print' value='Print Receipt' style='width:200px;' align='center' />
<div id='printDiv'>
	<table border="1" cellspacing="0" style="width: 700px;">
	<tr>
		<td><?php ?>
			<table style="text-align: center;float: right; width: 450px;">
				<tr>
					<td style="font-size: 30px;"><strong>QUOTATION CREDIT</strong></td>
				</tr>
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
						<tr>
							<th>Date.</th>
						</tr>
						<tr>
							<td><?=date('d-m-Y h:i:s a',strtotime($creditReceiptDetail['created']));//$this->Time->format('d-m-Y',$creditReceiptDetail['CreditReceipt']['created'],null,null);?></td>
						</tr>
						<?php if(!empty($kioskTable)){?>
						<tr>
							<td colspan="3">
								<?=$kioskTable;?>
							</td>
						</tr>
						<?php } ?>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>
			<table cellspacing="0">
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
							<tr>
								<th>Invoice To</th>
								
							</tr>
							<tr>
								<td><?=strtoupper($customer['fname'])." ".strtoupper($customer['lname']);?></td>
								
							</tr>
							<tr>
								<td><?=strtoupper($customer['business']);?></td>
								
							</tr>
							<tr>
								<td><?=strtoupper($customer['address_1']);?></td>
								
							</tr>
							<tr>
								<td><?=strtoupper($customer['address_2']);?></td>
								
							</tr>
							<tr>
								<td><?=strtoupper($customer['city'])." ".strtoupper($customer['state']);?></td>
								
							</tr>
							<tr>
								<td><?=strtoupper($customer['zip']);?></td>
								
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0" style="font-size: 10px;">
			<tr>
				
				<th>Description</th>
				<th>Sale Price</th>
				<th>Quantity</th>
				<th>Discount Price</th>
				<th>Amount</th>
			</tr>
	<?php
	$amount = 0;
	$totalVat = 0;
	$total_qty = $totalDiscount = 0;
	//pr($creditProductDetails);
	foreach($creditProductDetails as $key => $product){
		$vatItem = $vat/100;
		$itemPrice = round($product['sale_price']/(1+$vatItem),2);
		$discount = round($itemPrice*$product['discount']/100*$product['quantity'],2);
		$discountAmount = round(($product['quantity']*$itemPrice)-$discount,2);
		$amount+=$discountAmount;
		$totalDiscount+=$discount;
		$total_qty += $product['quantity'];
		?>
		<tr>
			<td><?= $productName[$product['product_id']];?></td>
			<?php
				if($product['discount'] > 0){
					$show_amount = $discountAmount/$product['quantity'];
					$itemPrice = $show_amount;
				}
				if($product['discount'] < 0){
					$itemPrice = $itemPrice;
				}
			?>
			
			<td><?=$CURRENCY_TYPE;?><?= number_format($itemPrice,2);?></td>
			<td><?= $product['quantity'];?></td>
			<?php if($product['discount'] < 0){?>
					<td><?php echo $discountAmount;?></td>
			<?php }else{
					$show_amount = $discountAmount/$product['quantity'];?>
					<td><?= number_format($show_amount,2);?></td>
			<?php } ?>
			
			<td><?=$CURRENCY_TYPE;?><?= number_format($discountAmount,2);?></td>
		</tr>
	<?php 	
	}
		$amount = round($amount,2);
		$bulkDiscount = round($amount*$creditReceiptDetail['bulk_discount']/100,2);
		$netAmount = round($amount - $bulkDiscount,2);
		$finalAmount = $creditReceiptDetail['credit_amount'];
		$finalVat = round($finalAmount-$netAmount,2);
	?>
			</table>
		</td>
	</tr>
	
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0" style="font-size: 10px;">
				<tr>
					<th>Sub Total(total qty =<?php echo $total_qty;?>)</th>
					<td><?php echo $CURRENCY_TYPE.round($amount,2);?></td>
				</tr>
				<tr>
					<th>Bulk Discount (<?=number_format($creditReceiptDetail['bulk_discount'],2);?>%)</th>
					<td><?=$CURRENCY_TYPE." ".number_format(round($bulkDiscount,2),2);?></td>
				</tr>
				<tr>
					<th>Sub Total</th>
					<td><?=$CURRENCY_TYPE." ".number_format(round($netAmount,2),2);?></td>
				</tr>			
				<tr>
					<th>Total Amount</th>
					<td><?=$CURRENCY_TYPE." ".number_format($finalAmount,2);?></td>
				</tr>
			</table>
		</td>
	</tr>
	
	</table>
</div>
	<table>
	<?php
		if(isset($paymentDetails)){
			$rowStr = "";
			foreach($paymentDetails as $key => $sngPmtDet){
				$pmtMethod = $sngPmtDet['payment_method'];
				$pmtDesc = $sngPmtDet['description'];
				$amt = $sngPmtDet['amount'];
				$srNo = $key + 1;
				$rowStr.="<tr><td>$srNo</td><td>$pmtMethod</td><td>$pmtDesc</td><td>$amt</td></tr>";
			}
			if(!empty($rowStr)){
				echo $pmtStr = <<<PMT_STR
				<br/><h3>Payment Details:</h3>
				<table>
				<tr><th>Sr No.</th><th>Payment Method</th><th>Description</th><th>Amount</th></tr>
				$rowStr
				</table>
PMT_STR;
			}
		}
	?>
	</table>
	<?php //echo $this->Form->create();?>
	<?php
		//if(!empty($customerEmail)){
		//	echo $this->Form->input(null,array(
		//			'type' => 'text',
		//			'label' => 'Enter customer email',
		//			'name' => 'customer_email',
		//			'value' => $customerEmail
		//				)
		//			);
		//}else{
		//	echo $this->Form->input(null,array(
		//			'type' => 'text',
		//			'label' => 'Enter customer email',
		//			'name' => 'customer_email'
		//				)
		//			);
		//}
	?>
	<?php
	//$option = array('name'=>'send_receipt', 'value'=>'Submit');
	//echo $this->Form->Submit('Submit',$option);
    //echo $this->Form->end();?>
