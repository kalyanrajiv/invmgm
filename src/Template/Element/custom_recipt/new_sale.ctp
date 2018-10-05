<tr>
		 <td style="font-size: 12px;" align="left"><b>Items</b></td>
		 <td style="font-size: 12px;" align="right"><b>Amt</b></td>
</tr>
<?php
foreach ($kiosk_product_sale as $key => $value){
		 $total_price = $price = 0;
		 if($value['status']==1){
				  $product_id = $value['product_id'];
				  $receiptId = $value['product_receipt_id'];
				  $quantityKey = "$product_id|$receiptId";
				  if(array_key_exists($quantityKey, $qttyArr)){
						  if(!empty($value['discount'])){
						   $price = $value['sale_price'] - ( $value['sale_price'] * $value['discount']/100);
						   $total_price = $price * $qttyArr[$quantityKey];
						  }else{
									$price = $value['sale_price'];
									$total_price = $price * $qttyArr[$quantityKey];
						  }
				  }
				  
		 ?>
		 <tr>
              <td style="font-size: 12px; width: 173px;" align="left"><?=$productName[$value['product_id']];?> ( <?=$qttyArr[$quantityKey]." @ ".number_format($price,2);?> ) </td>
			  
              <td style="font-size: 12px;" align="right"><?php
			//   if(array_key_exists($quantityKey, $qttyArr)){
			//			   $discount = $price*$value['discount']/100*$qttyArr[$quantityKey];
			//			   //echo "</br>";
			//			   if($value['discount']<0){
			//					$discount_for_negitive = $price*$value['discount']/100;
			//					$discountAmount_for_negtive = ($price)-$discount_for_negitive;
			//					$discountAmount = $qttyArr[$quantityKey]*$discountAmount_for_negtive;	 
			//					//$discountAmount = -1*($itemPrice*$discount)/100;
			//			   }else{
			//					$discountAmount = ($qttyArr[$quantityKey]*$price)-$discount;	 
			//			   }
			//			  
			//		   }
			  echo $CURRENCY_TYPE.number_format($total_price,2);?></td>
	  	</tr>
<?php
		 }
       } ?>
         <tr>
                 <td style="font-size: 12px;" align="left">Total Amt : </td>
                 <td style="font-size: 12px;" align="right"><?php
				  echo $CURRENCY_TYPE.number_format($product_reciept['orig_bill_amount'],2);
				  ?></td>
		</tr>
		 
		
<?php

foreach($payment_method as $key1 => $val){?>
          <tr>
		<?php echo $val;?> 
          </tr> 
<?php } ?>
      
<?php 
$dataArr=array();
	foreach($kiosk_products_data as $k=>$data){
		$dataArr[$data['status']]=$data['status'];
	}
	if(array_key_exists(0,$dataArr)){ ?>
	<tr>
		 <td style="font-size: 12px;" align="left"><b>Refund Detail:</b></td>
		 <td style="font-size: 12px;" align="right"><?php echo date("d-m-y h:i:s");?></td>
		 </tr>
	<?php 
	 $refundedAmount =0;
		 //pr($kiosk_products_data);
		 foreach($kiosk_products_data as $key => $val1){
				  if($val1['status']==0){
						   $refundAmount = $val1['refund_price']*$val1['quantity'];
						   $refundedAmount+=$refundAmount; ?>
				  
				  <tr>
						   <td style="font-size: 12px;" align="left"><?php echo $productName[$val1['product_id']]; ?> ( <?php echo $val1['quantity']." @ ".number_format($val1['refund_price'],2)." ) ";?></td>
						   <td style="font-size: 12px;" align="right"><?php echo $CURRENCY_TYPE.number_format($refundAmount,2); ?></td>
				  </tr>
				  <?php } ?>
		 <?php }
?>

<tr>
		 <td style="font-size: 12px;" align="left">Refund Amt : </td>
		 <td style="font-size: 12px;" align="right"><?=$CURRENCY_TYPE.number_format($refundedAmount,2);?></td>
</tr>
<tr>
		 <td style="font-size: 12px;" align="left">After Refund Amt : </td>
		 <?php $after_refund_amt = $product_reciept['orig_bill_amount'] - $refundedAmount;?>
		 <td style="font-size: 12px;" align="right"><?=$CURRENCY_TYPE.number_format($after_refund_amt,2);?></td>
</tr>
<?php } ?>