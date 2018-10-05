<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$jQueryURL = "http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
if(defined('URL_SCHEME')){
	$jQueryURL = URL_SCHEME."ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
}
?>
<script type="text/javascript" src="<?php echo $jQueryURL;?>"></script>
<?php
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
	$siteBaseUrl = Configure::read('SITE_BASE_URL');
?>
<?php /*pr($mobileRepairData);*/
echo $this->Html->script('jquery.printElement');?>
<input type='button' id='printSelected' name='print' value='Print Receipt' style='width:200px;' align='center' />
<div id='printDiv' style="text-align: center;">
<?php $customerEmail = $mobileRepairData['customer_email'];
//pr($mobileRepairData);
?>
<table border="1" cellspacing="0" style="width: 700px;">
	<tr>
		<td><?php
		if(empty($kioskDetails['terms'])){
			$imgUrl = "{$siteBaseUrl}/img/".$settingArr['logo_image'];
			echo $this->Html->image($imgUrl, array('fullBase' => true));	
		}else{
			if(!empty(trim($kioskDetails['terms']))){
				$imgUrl = $siteBaseUrl."/logo/".$kioskDetails['id']."/".$kioskDetails['logo_image'];
				echo $this->Html->image($imgUrl, array('fullBase' => true,'style'=> "width: 332px;height: 102px;"));	
			}else{
				 $imgUrl = $siteBaseUrl."/img/".$settingArr['logo_image'];
				 echo $this->Html->image($imgUrl, array('fullBase' => true));
			}
		}
		
		
		?>
		
		<table style="text-align: center;float: right; width: 320px;margin-top: 27px;margin-right: 8px;">
					 <?php
					 
					 if(empty(trim($kioskDetails['terms']))){ ?>
					 <tr>
							<td> <?=$settingArr['headoffice_address'];?> </td> 
					</tr>
					 <?php } ?>
			</table>
			<table border = '1' style="text-align: center;float: right; width: 320px;margin-top: -60px;margin-right: 8px;">
				<tr>
					<td><strong><?=$kioskDetails['name'];?></strong></td>
				</tr>
				<tr>
					<td><?=$kioskDetails['address_1'];?>
					<?=($kioskDetails['address_2'] != '') ? "<br/>".$kioskDetails['address_2'] : "";?></td>
				</tr>
				<tr>
					<td><?=$kioskDetails['city'];?>, <?=$kioskDetails['state'];?></td>
				</tr>
				<tr>
					<td><?=$kioskDetails['zip'];?>, UK. <?=($kioskDetails['contact'] != '') ? ", Contact:".$kioskDetails['contact'] : "";?></td>
				</tr>
			</table>
			<table style="text-align: center;float: right; width:100%;">
				<tr>
					<td style="font-size: 20px;"><strong>REPAIR RECEIPT</strong></td>
				</tr>
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
						<tr>
							<?php if(!empty($kioskDetails) && $kioskDetails['vat_applied'] == 1){ ?>
								<th>VAT Reg No.</th>
							<?php } ?>
							<th>Date.</th>
							<th>Repair No.</th>
							<th>Rep</th>
						</tr>
						<tr>
							
							<?php if(!empty($kioskDetails) && $kioskDetails['vat_applied'] == 1){
									if(!empty(trim($kioskDetails['vat_no']))){ ?>
										<td><?php echo $kioskDetails['vat_no'];?></td>
							  <?php }else{
										if(!empty($settingArr['vat_number'])){ ?>
											<td><?php echo $settingArr['vat_number'];?></td>	
									<?php }
									}
								?>
										
							<?php } ?>
							
							<td><?php echo date('d-m-Y',strtotime($date)); //$mobileRepairData['created']; ?></td>
							<td><?php echo $mobileRepairData['id'];?></td>
							<td><?php echo $userName[$mobileRepairData['booked_by']];?></td>
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
						<table width="100%" cellspacing="0">
							<tr>
								<td><?php echo strtoupper($mobileRepairData['customer_fname'])." ".strtoupper($mobileRepairData['customer_lname']);?></td>
							</tr>
							<tr>
								<td><?php echo strtoupper($mobileRepairData['customer_address_1']);?></td>
							</tr>
							<?php if(!empty($mobileRepairData['customer_address_2'])){?>
								<tr>
									<td><?php echo strtoupper($mobileRepairData['customer_address_2']);?></td>
								</tr>
							<?php } ?>
							<?php if(!empty($mobileRepairData['city'])){?>
								<tr>
									<td><?php echo strtoupper($mobileRepairData['city']);?></td>
								</tr>
							<?php } ?>
							<?php if(!empty($mobileRepairData['state'])){?>
								<tr>
									<td><?php echo strtoupper($mobileRepairData['state']);?></td>
								</tr>
							<?php } ?>
							<tr>
								<td><?php echo strtoupper($mobileRepairData['zip']);?></td>
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
					<td><strong>Imei: </strong><?php echo $mobileRepairData['imei'];?></td>
					<td><strong>Brand: </strong><?php echo $mobileRepairData['brand']['brand'];?></td>
					<td><strong>Model: </strong><?php echo $mobileRepairData['mobile_model']['model'];?></td>
				</tr>
				<tr>
					<th colspan='2'>Problem</th>
					<th>Amount</th>
				</tr>
				<?php
					$vat = $settingArr['vat'];
					$problemArr = explode("|",$mobileRepairData['problem_type']);
					$estimatedCostArr = explode("|",$mobileRepairData['estimated_cost']);
					$totalCost = 0;
					foreach($problemArr as $key=>$problemType){
						$estimatedCost = $estimatedCostArr[$key];
						$exceptVatCost = $estimatedCost/(1+$vat/100);
						$totalCost+=$estimatedCost;
				?>
				<tr>
					<td colspan='2'><?php echo $problemTypeOptions[$problemType];?></td>
					<td><?php 
                    echo $CURRENCY_TYPE.number_format($exceptVatCost,2);
					//$settingArr['currency_symbol'].number_format($exceptVatCost,2);?></td>
				</tr>
				<?php }
					$subTotal = $totalCost/(1+$vat/100);
					$vatAmount = $totalCost - $subTotal;
				?>
				<tr>
					<td colspan='3'>&nbsp;</td>
				</tr>
				<tr>
					<th colspan='2'>Sub Total</th>
					<td><?php echo $CURRENCY_TYPE.number_format($subTotal,2);
					//$settingArr['currency_symbol'].number_format($subTotal,2);?></td>
				</tr>
				<tr>
					<th colspan='2'>VAT</th>
					<td><?php   echo $CURRENCY_TYPE.number_format($vatAmount,2);
					//$settingArr['currency_symbol'].number_format($vatAmount,2);?></td>
				</tr>				
				<tr>
					<th colspan='2'>Total Amount</th>
					<td><?php echo $CURRENCY_TYPE.number_format($totalCost,2);//echo $settingArr['currency_symbol'].number_format($totalCost,2);?></td>
				</tr>
				<?php if(!empty($repairRefundData)){?>
				<tr>
					<td colspan='2' style='text-align: center;'>
						<strong>Refund Details</strong>
					</td>
				</tr>
				<?php $totalRefundAmount = 0;
				foreach($repairRefundData as $key=>$refundData){
					//echo "totalRefundAmount".
					$totalRefundAmount+=$refundData['refund_amount'];
					?>
				<tr>
					<th colspan='2'>Refund on (<?php echo date('d-m-y',strtotime($refundData['refund_on']));
                     //  $this->Time->format('jS M, Y ', $refundData['refund_on'],null,null); 
                    ?>) </th>
                 
					<td><?php echo $CURRENCY_TYPE.-$refundData['refund_amount'];
				 //$settingArr['currency_symbol'].-$refundData['refund_amount'];?></td>
				</tr>
				<?php } ?>
				<tr>
					<th colspan='2'>Total Refund Amount</th>
					<td><?php /*if(array_key_exists('refund_amount',$totalRefundAmount)){*/
					echo $CURRENCY_TYPE.-$totalRefundAmount;
					//}?></td>
				</tr>
				
				<?php }?>
				<?php if(isset($totalRefundAmount) && $totalRefundAmount!=0){
					$grandTotal = $totalCost+$totalRefundAmount; // refund amount is already in negative
					?>
					<tr>
					<th colspan='2' style='text-align: center;'>Grand Total (after refund)</th>
					<td><?php echo   $CURRENCY_TYPE.number_format($grandTotal,2);//$settingArr['currency_symbol'].number_format($grandTotal,2);?></td>
				</tr>
				<?php }?>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0">
				<tr>
					
					<td>Fax(Sales) <?=$settingArr['fax_number'];?></td>
					<?php if(!empty(trim($kioskDetails['terms']))){ ?>
						<td>Email <?=$kioskDetails['email'];?></td>
					<?php }else{?>
						<td>Email <?=$settingArr['email'];?></td>
					<?php } ?>
					
					
					<?php if(empty(trim($kioskDetails['terms']))){ ?>
					<td>Website <?=$settingArr['website'];?></td>
					<?php } ?>
				</tr>
			</table>
		</td>
	</tr>
</table>
	
		
	<p style="text-align: left;">
		<div style="text-align: left;">
		<?php if(!empty(trim($kioskDetails['terms']))){
						echo $kioskDetails['terms'];
				}else{
						echo $settingArr['invoice_terms_conditions'];
				} ?>
		</div>
	</p>
	
</div>
<?php
echo $this->Form->create('RepairReceipt');
echo $this->Form->Input('email',array('type'=>'text','value'=>$customerEmail,'style'=>"width: 291px;"));
echo $this->Form->submit("Send Receipt");
echo $this->Form->end();
?>