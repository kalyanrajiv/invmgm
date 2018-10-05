<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$repairStatusUserOptions = Configure::read('repair_statuses_user');
$repairStatusTechnicianOptions = Configure::read('repair_statuses_technician');

$repairStatus = $repairStatusUserOptions+$repairStatusTechnicianOptions;
?>
		 <tr>
                 <td style="font-size: 12px;" align="center"><b>Repair Details:</b></td>
                
		</tr>
		 <tr>
                 <td style="font-size: 12px;" align="left">IMEI:</td>
                 <td style="font-size: 12px;" align="right"><?=$repair_data['imei'];?></td>
		</tr>
         <tr>
			  <td style="font-size: 12px;" align="left">Brand:</td>
              <td style="font-size: 12px;" align="right"> <?php echo $repair_data['brand']['brand'];?></td>
	  	</tr>
         <tr>
              <td style="font-size: 12px;" align="left">Model Name:</td>
              <td style="font-size: 12px;" align="right"><?=$repair_data['mobile_model']['model'];?>
             </td>
	  	</tr>
		 <?php 
		//if($repair_data['status_rebooked'] == 1){?>
		<tr>
				<td style="font-size: 12px;" align="left"><b>Status:</b> </td>
				<td style="font-size: 12px;" align="right"><?php echo $repairStatus[$repair_data['status']];?> </td>
		</tr>
		<?php//	}?>
	 
        <tr>
					<td style="font-size: 12px;" align="left"><b>Problem</b> </td>
                     
					<td style="font-size: 12px;" align="right"><?php $statusarray = array(6,8);
					//echo $repair_data['status'];
					if(in_array($repair_data['status'],$statusarray)){
						if($repair_data['status_rebooked'] == 1){
								   echo "<b>Amount</b>";
						}else{
						      echo "<b>Amount</b>";
						 }	
                      }else{//echo $repair_data['status'];
								echo "<b>Estimated Amount</b>";
						 
					  }?></td>
		</tr>
				<?php
					$vat = $settingArr['vat'];
					$problemArr = explode("|",$repair_data['problem_type']);
					$estimatedCostArr = explode("|",$repair_data['estimated_cost']);
					$totalCost = 0;
					foreach($problemArr as $key=>$problemType){
						$estimatedCost = $estimatedCostArr[$key];
						$exceptVatCost = $estimatedCost/(1+$vat/100);
						$totalCost+=$estimatedCost;
			?>
			<tr>
				<td style="font-size: 12px;" align="left"><?php if(array_key_exists($problemType,$problemTypeOptions)){
				  echo $problemTypeOptions[$problemType];
				  }?></td>
				<td style="font-size: 12px;" align="right"><?php
				 //if($repair_data['status'] == 2){
				  if($repair_data['status_rebooked'] == 1){
						echo $CURRENCY_TYPE.number_format($estimatedCost,2);
						//echo $CURRENCY_TYPE.number_format(0,2);
				 }else{
                    echo $CURRENCY_TYPE.number_format($estimatedCost,2);
				 }
                 
				?></td>
			</tr>
				<?php }?>
			
			
				<?php if(!empty($paymentdata)){
						if($repair_data['status_rebooked'] == 1){
						//if($repair_data['status'] == 2){
						}else{
						?>
				<tr>
						<td style="font-size: 12px;" align="centre"><b>Payment method</b></td>
				</tr>
				<tr>
					<?php	  
										foreach($paymentdata as $sngpaymentdata){?>
												<tr>
													<td style="font-size: 12px;" align="left"><?php echo $sngpaymentdata['payment_method'];?></td>
													<td style="font-size: 12px;" align="right"><?php $amount = $sngpaymentdata['amount'];
													echo $CURRENCY_TYPE.number_format($amount,2);
													 ?></td>
												</tr>
				
								<?php }
								}?>
			</tr>
				<?php }?>
				<tr>
					<?php
				if(in_array($repair_data['status'],$statusarray)){
					 	if($repair_data['status_rebooked'] == 1){
						?>
						 <td style="font-size: 12px;" align="left">Total Amount</td>
						  <?php    }else{?>
					 <td style="font-size: 12px;" align="left">Total Amount</td>   
							
                   <?php  }  }else{
						if($repair_data['status'] ==2){
						?>
				      <td style="font-size: 12px;" align="left">Total Estimated Amount</td>
					   <?php   }else{?>
						 <td style="font-size: 12px;" align="left">Total  Amount</td>
				<?php	}  }?>	
				
				<?php
				if(in_array($repair_data['status'],$statusarray)){
						if($repair_data['status_rebooked'] == 1){
						?>
						 <td style="font-size: 12px;" align="right"><?php echo $CURRENCY_TYPE.number_format(0,2); ?></td>
						  <?php   }else{?>
                           <td style="font-size: 12px;" align="right"><?php echo $CURRENCY_TYPE.number_format($totalCost,2);
				 ?></td>
							
                   <?php  } }else{
						  if($repair_data['status'] == 2){
						//if($repair_data['status_rebooked'] == 1){
						?>
						 <td style="font-size: 12px;" align="right"><?php echo $CURRENCY_TYPE.number_format(0,2); ?></td>
						<?php  } else{ ?>
						   <td style="font-size: 12px;" align="right"><?php echo $CURRENCY_TYPE.number_format($totalCost,2);
				 ?></td>
				<?php	 } }?>
				 
			</tr>
				 </td>
			</tr>
			<?php
			
				if(!empty($repairRefundData)){?>
				
				<?php $totalRefundAmount = 0;
				foreach($repairRefundData as $key=>$refundData){
					//echo "totalRefundAmount".
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
				 
				
				<?php }?>
				<?php if(isset($totalRefundAmount) && $totalRefundAmount!=0){
					$grandTotal = $totalCost+$totalRefundAmount; // refund amount is already in negative
					?>
					<tr>
					<td style="font-size: 12px;" align="center" >After Refund Amt</td>
					<td style="font-size: 12px;" align="right"><?php echo   $CURRENCY_TYPE.number_format($grandTotal,2);//$settingArr['currency_symbol'].number_format($grandTotal,2);?></td>
				</tr>
			 
			
				<?php }?>