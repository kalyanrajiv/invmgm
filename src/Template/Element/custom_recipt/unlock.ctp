         <tr>
                 <td style="font-size: 12px;" align="center"><b>Unlock Details:</b></td>
                
		</tr>
		  <tr>
                 <td style="font-size: 12px;" align="left">IMEI</td>
                 <td style="font-size: 12px;" align="right"><?=$mobileUnlockData['imei'];?></td>
		</tr>
         <tr>
			  <td style="font-size: 12px;" align="left">Brand</td>
              <td style="font-size: 12px;" align="right"> <?php echo $brands =  $mobileUnlockData['brand']['brand'];?></td>
	  	</tr>
         <tr>
              <td style="font-size: 12px;" align="left">Model Name</td>
              <td style="font-size: 12px;" align="right"><?=$mobileUnlockData['mobile_model']['model'];?>
             </td>
			  
	  	</tr>
		 <tr>
              <td style="font-size: 12px;" align="left">Network</td>
              <td style="font-size: 12px;" align="right"><?php  echo $mobileUnlockData['network']['name'];?>
             </td>
			  
	  	</tr>
		 <tr>
              <td style="font-size: 12px;" align="left">Est Time</td>
              <td style="font-size: 12px;" align="right"><?php  echo $mobileUnlockData['unlocking_days']." day(s)&nbsp;";
			 echo  $mobileUnlockData['estimated_minutes']." min(s)";?>
             </td>
			  
	  	</tr>
		 <?php
				$vat = $settingArr['vat'];
				$subTotal = $mobileUnlockData['estimated_cost']/(1+$vat/100);
				$vatAmount = $mobileUnlockData['estimated_cost'] - $subTotal;
		?>
		  <tr>
				<td style="font-size: 12px;" align="left"><?php $statusarray = array(0,9,11);
					if(in_array($mobileUnlockData['status'],$statusarray)){
                             echo "Amount";
							
                      }else{//echo $repair_data['status'];
						     echo "Est Amt";
					  }?></td>
             
              <td style="font-size: 12px;" align="right"><?php  echo $CURRENCY_TYPE.number_format($mobileUnlockData['estimated_cost'],2);?>
             </td>
			  
	  	</tr>
		<?php if(!empty($paymentdata)){?>
				<tr>
						<td style="font-size: 12px;" align="centre"><b>Payment method</b></td>
				</tr>
				<tr>
					<?php	 foreach($paymentdata as $sngpaymentdata){?>
					<tr>
						<td style="font-size: 12px;" align="left"><?php echo $sngpaymentdata['payment_method'];?></td>
						<td style="font-size: 12px;" align="right"><?php $amount = $sngpaymentdata['amount'];
						echo $CURRENCY_TYPE.number_format($amount,2);
						 ?></td>
					</tr>
				
				<?php }?>
			</tr>
				<?php }?>
			 
				 
			</tr>
			<?php if(!empty($unlockRefundData)){
					 $totalRefundAmount = 0;?>
			 
					<?php //pr($unlockRefundData);
					foreach($unlockRefundData as $key=>$refundData){
					//	pr($refundData);
						$totalRefundAmount+=$refundData['refund_amount'];
						?>
							<tr>
					<td style="font-size: 12px;" align="left">
						<strong>Refund Details</strong>
					</td>
					<td style="font-size: 12px;" align="right"><?php
						 $refundData['refund_on']->i18nFormat(
                                                [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                        );
								$refundDate =  $refundData['refund_on']->i18nFormat('dd-MM-yyyy HH:mm:ss');
								echo $refundDate = date("d-m-y h:i a",strtotime($refundDate)); 
					 
						 ?></td>
				</tr>
					<tr>
						
						<td style="font-size: 12px;" align="left">
						 Amount
					</td>
						<td style="font-size: 12px;" align="right"><?php echo $CURRENCY_TYPE.-$refundData['refund_amount'];
					 //$settingArr['currency_symbol'].-$refundData['refund_amount'];?></td>
					</tr>
					<?php } ?>
					 
					<?php if(isset($totalRefundAmount) && $totalRefundAmount!=0){
						$grandTotal = $mobileUnlockData['estimated_cost']+$totalRefundAmount; // refund amount is already in negative
						?>
						
					<tr>
					<td style="font-size: 12px;" align="left">After Refund Amt</th>
					<td style="font-size: 12px;" align="right"><?php echo   $CURRENCY_TYPE.number_format($grandTotal,2);
					//$settingArr['currency_symbol'].number_format($grandTotal,2);?></td>
				</tr>
				<?php }
				}?>