 <tr>
			  <td style="font-size: 12px;" align="left"><b>Sale Detail</b></td>
			  <td style="font-size: 12px;" align="left"></td>
 </tr>
 <?php
               if(!empty($sale_data)){
                  $id = $sale_data['id'];
                  $brandname = $sale_data['brand_id'];
                  $Modelname = $sale_data['mobile_model_id'];
                  $network_id = $sale_data['network_id'];
                  if(array_key_exists($network_id,$networks)){
                      $network = $networks[$network_id];
                  }else{
                      $network ="--";
                  }
		
                $imei = $sale_data['imei'];
                $cost_price = $sale_data['discounted_price'];
                
		
          ?>
		 <tr>
			  <td style="font-size: 12px;" align="left">Brand:</td>
              <td style="font-size: 12px;" align="right"><?=$brands[$brandname];?></td>
	  	</tr>
         <tr>
              <td style="font-size: 12px;" align="left">Model Name:</td>
              <td style="font-size: 12px;" align="right"><?=$mobileModels[$Modelname];?></td>
	  	</tr>
         <tr>
             <td style="font-size: 12px;" align="left">Network:</td>
              <td style="font-size: 12px;" align="right"><?=$network;?></td>
	  	 </tr>
         <tr>
                 <td style="font-size: 12px;" align="left">IMEI:</td>
                 <td style="font-size: 12px;" align="right"><?=$imei;?></td>
		</tr>
		  <tr>
             <td style="font-size: 12px;" align="left">Quantity:</td>
              <td style="font-size: 12px;" align="right"><?="1";?></td>
			 </tr>
         <tr>
             <td style="font-size: 12px;" align="left">Amount:</td>
              <td style="font-size: 12px;" align="right"><?=$CURRENCY_TYPE.number_format($cost_price,2);?></td>
		</tr>
         
            <?php
            if(!empty($pay_terms)){
                foreach($pay_terms as $pay_method => $value){ ?>
                   
                        <tr><td style="font-size: 12px;" align="left"><?php echo $pay_method." :" ?></td><td style="font-size: 12px;" align="right"><?php echo $CURRENCY_TYPE.number_format($value,2); ?></td></tr>
                    <?php }
            }
            ?>
	<?php 	}?>
<?php if(!empty($refund_data)){?>
<tr>
			  <td style="font-size: 12px;" align="left"><b>Refund Details</b></td>
			  <td style="font-size: 12px;" align="right"><?php echo date("d-m-y h:i:s");?></td>
</tr>
<tr>
			  <td style="font-size: 12px;" align="left">Net Refund :</td>
			  <td style="font-size: 12px;" align="right"><?=$CURRENCY_TYPE.number_format($mobileReturnData['refund_price'],2);?></td>
</tr>
<?php
$after_refund_amt = $cost_price - $mobileReturnData['refund_price'];
?>
<tr>
			  <td nowrap='nowrap' style="font-size: 12px;" align="left">After Refund Amt:</td>
			  <td style="font-size: 12px;" align="right"><?=$CURRENCY_TYPE.number_format($after_refund_amt,2);?></td>
</tr>
<?php } ?>