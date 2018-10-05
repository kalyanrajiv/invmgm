<?php
    use Cake\Core\Configure;
    use Cake\Core\Configure\Engine\PhpConfig;
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
	$siteBaseUrl = Configure::read('SITE_BASE_URL');
	$jQueryURL = "http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
	if(defined('URL_SCHEME')){
		$jQueryURL = URL_SCHEME."ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
	}
?>
<script type="text/javascript" src="<?php echo $jQueryURL;?>"></script>
<?php echo $this->Html->script('jquery.printElement');?>
<input type='button' id='printSelected' name='print' value='Print Receipt' style='width:200px;' align='center' />
<?php echo $this->Html->link("View Sales",array('controller'=>'kiosk_product_sales','action'=>'dr_index'))?>
<div id='printDiv' style="text-align: center;">
<?php //$vatPercentage = $productReceipt['ProductReceipt']['vat'];
//pr($settingArr['logo_image']);?>
<table border="1" cellspacing="0" style="width: 700px;">
	<tr>
		<td><?php #$imgUrl = $siteBaseUrl."/img/".$settingArr['logo_image'];
		//echo $this->Html->image($imgUrl, array('fullBase' => true));?>
			<table>
				<tr>
					<td style="font-size: 30px;"><strong>QUOTATION</strong></td>
					<td><table border="1" cellspacing="0" style="width: 50%;float: right;">
							<tr>
								<td><strong>Date</strong></td>
								<td><?=date('d-m-Y h:i:s a',strtotime($productReceipt['created']));//$this->Time->format('d-m-Y',$productReceipt['created'],null,null);?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table cellspacing="0">
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
							<tr>
								<th>Quotation</th>
							</tr>
							<tr>
								<td><?=strtoupper($customers['fname'])." ".strtoupper($customers['lname']);?></td>
							</tr>
							<tr>
								<td><?=strtoupper($customers['business']);?></td>
							</tr>
							<tr>
								<td><?=strtoupper($customers['address_1']);?></td>
							</tr>
							<tr>
								<td><?=strtoupper($customers['address_2']);?></td>
							</tr>
							<tr>
								<td><?=strtoupper($customers['city'])." ".strtoupper($customers['state']);?></td>
							</tr>
							<tr>
								<td><?=strtoupper($customers['zip']);?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
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
	$vatPercentage = $productReceipt['vat'];
	foreach($kioskProductSales as $key => $product){
		$show_price = 0;
		if($product['status']==1){
		$vatItem = $vat/100;
		$itemPrice = $product['sale_price'];     //     /(1+$vatItem);
		$discount = $itemPrice*$product['discount']/100;         //       *$product['quantity'];
		$discountAmount = ($itemPrice)-$discount;        //$product['quantity']*
		$show_price = $discountAmount * $product['quantity'];
		$amount+=$discountAmount * $product['quantity'];
		$totalDiscount+=$discount;
		$total_qty += $product['quantity'];
		?>
		<tr>
			<td><?= $productName[$product['product_id']];?></td>
			<?php if($product['discount'] < 0){ ?>
				<td> <?=   $CURRENCY_TYPE.number_format($show_price/$product['quantity'],2);?></td>
				<?php }else{ ?>
			<td> <?=   $CURRENCY_TYPE.number_format($itemPrice,2);?></td>
			<?php } ?>
			<td><?= $product['quantity'];?></td>
			<?php if($product['discount'] < 0){ ?>
			<td><?= 0;?></td>
			<?php }else{
				 $dis_amout = $itemPrice - $itemPrice*($product['discount']/100);
				?>
			<td><?= $dis_amout;?></td>
			<?php } ?>
			<td><?=$CURRENCY_TYPE;?><?= number_format($show_price,2);?></td>
		</tr>
	<?php 	}/*elseif($product['status']==1 && $product['quantity'] ==0){
			$vatAmount = 0;
		}*/
	}
		$amount = $amount;
		$bulkDiscount = $amount*$productReceipt['bulk_discount']/100;
		$netAmount = $amount - $bulkDiscount;
		$finalAmount = $productReceipt['bill_amount'];
		$finalVat = $finalAmount-$netAmount;
	?>
			
			</table>
		</td>
	</tr>
	<tr>
		<td>	
			<table border="1" cellspacing="0" style="font-size: 10px;">
				<tr>
					<th>
						Sub Total (total quantity = <?php echo $total_qty; ?>)
					</th>
					<td>
						<?php echo $CURRENCY_TYPE.number_format($amount,2);?>
					</td>
				</tr>
				<tr>
					<th>Bulk Discount (<?=$productReceipt['bulk_discount'];?>%)</th>
					<td><?=  $CURRENCY_TYPE.number_format($bulkDiscount,2);?></td>
				</tr>
				<tr>
					<th>Sub Total</th>
					<td><?=  $CURRENCY_TYPE.number_format($netAmount,2);?></td>
				</tr>
				<tr>
					<th>Total Amount</th>
					<td><?=  $CURRENCY_TYPE.$finalAmount; ?></td>
				</tr>
			</table>
		</td>
	</tr>

</table>

</div>
<div>**<b>Note : </b>This Is Not A Invoice.This Is Quotation Only.</div>