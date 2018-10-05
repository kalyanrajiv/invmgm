<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));?>
<table border="1" cellspacing="0" style="width: 700px;">
	<tr>
		<td><?php
		$siteBaseUrl = Configure::read('SITE_BASE_URL');
		$imgUrl = "/img/".$settingArr['logo_image'];
		echo $this->Html->image($imgUrl, array('fullBase' => true));?>
			<table border = '1' style="text-align: center;float: right; width: 320px;margin-top: 27px;margin-right: 8px;">
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
							<td><?php
							$created = date('d-m-Y',strtotime($date));
							echo $created;?></td>
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
						<table border="1" style="width:693px" cellspacing="0">
							<tr>
								<td style="text-align: left;"><strong>Name:</strong></td>
								<td style="text-align: left;"><?php echo strtoupper($mobileRepairData['customer_fname'])." ".strtoupper($mobileRepairData['customer_lname']);?></td>
							</tr>
							<tr>
								<td style="text-align: left;"><strong>Address1:</strong></td>
								<td style="text-align: left;"><?php echo strtoupper($mobileRepairData['customer_address_1']);?></td>
							</tr>
							<?php if(!empty($mobileRepairData['customer_address_2'])){?>
								<tr>
									<td style="text-align: left;"><strong>Address2:</strong></td>
									<td style="text-align: left;"><?php echo strtoupper($mobileRepairData['customer_address_2']);?></td>
								</tr>
							<?php } ?>
							<?php if(!empty($mobileRepairData['city'])){?>
								<tr>
									<td style="text-align: left;"><strong>City:</strong></td>
									<td style="text-align: left;"><?php echo strtoupper($mobileRepairData['city']);?></td>
								</tr>
							<?php } ?>
							<?php if(!empty($mobileRepairData['state'])){?>
								<tr>
									<td style="text-align: left;"><strong>State:</strong></td>
									<td style="text-align: left;"><?php echo strtoupper($mobileRepairData['state']);?></td>
								</tr>
							<?php } ?>
							<tr>
								<td style="text-align: left;"><strong>Zip:</strong></td>
								<td style="text-align: left;"><?php echo strtoupper($mobileRepairData['zip']);?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0" style="font-size: 10px;width: 100%;">
				<tr>
					<td><strong>Imei: </strong></td>
					<td><strong>Brand: </strong></td>
					<td><strong>Model: </strong></td>
					<td colspan='2'><strong>Problem: </strong>
					<td style="text-align: right;"><strong>Amount: </strong>
				</tr>
				<!--<tr>
					<th colspan='2'>Problem</th>
					<th>Amount</th>
				</tr>-->
				<tr>
					<td><?php echo $mobileRepairData['imei'];?></td>
					<td><?php echo $mobileRepairData['brand']['brand'];?></td>
					<td><?php echo $mobileRepairData['mobile_model']['model'];?></td>
				
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
				
					<td colspan='2'><?php echo $problemTypeOptions[$problemType];?></td>
					<td style="text-align: right;"><?php echo $currency.number_format($exceptVatCost,2);?></td>
				</tr>
				<?php }
					$subTotal = $totalCost/(1+$vat/100);
					$vatAmount = $totalCost - $subTotal;
				?>
				<tr>
					<td colspan='6'>&nbsp;</td>
				</tr>
				<tr>
					<th colspan='3' style="text-align: right;">Sub Total</th>
					<td colspan='3' style="text-align: right;"><?php echo $currency.number_format($subTotal,2);?></td>
				</tr>
				<tr>
					<th colspan='3' style="text-align: right;">VAT</th>
					<td colspan='3' style="text-align: right;"><?php echo $currency.number_format($vatAmount,2);?></td>
				</tr>				
				<tr>
					<th colspan='3' style="text-align: right;">Total Amount</th>
					<td colspan='3' style="text-align: right;"><?php echo $currency.number_format($totalCost,2);?></td>
				</tr>
				<?php if(!empty($repairRefundData)){?>
				<tr>
					<td colspan='6' style="text-align: center;font-size: 13px;">
						<strong>Refund Details</strong>
					</td>
				</tr>
				<?php $totalRefundAmount = 0;
				foreach($repairRefundData as $key=>$refundData){
					//echo "totalRefundAmount".
					$totalRefundAmount+=$refundData['refund_amount'];
					$refundOn = date('d-m-Y',strtotime($refundData['refund_on']));
					?>
				<tr>
					<th style="text-align: right;" colspan='3'>Refund on (<?php echo $refundOn; ?>)</th>
					<td colspan='3' style="text-align: right;"><?php echo $currency.-$refundData['refund_amount'];
				 //$settingArr['currency_symbol'].-$refundData['refund_amount'];?></td>
				</tr>
				<?php } ?>
				<tr>
					<th colspan='3' style="text-align: right;">Total Refund Amount</th>
					<td colspan='3' style="text-align: right;"><?php /*if(array_key_exists('refund_amount',$totalRefundAmount)){*/
					echo $currency.-$totalRefundAmount;
					//}?></td>
				</tr>
				
				<?php }?>
				<?php if(isset($totalRefundAmount) && $totalRefundAmount!=0){
					$grandTotal = $totalCost+$totalRefundAmount; // refund amount is already in negative
					?>
					<tr>
					<th colspan='3' style="text-align: right;">Grand Total (after refund)</th>
					<td colspan='3' style="text-align: right;"><?php echo   $currency.number_format($grandTotal,2);//$settingArr['currency_symbol'].number_format($grandTotal,2);?></td>
				</tr>
				<?php }?>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0" style="width: 100%;">
				<tr>
					<!--<td>Tel(Sales) <?php
					//if(!empty($kioskContact)){
					//	echo $kioskContact;
					//}else{
					//	echo $settingArr['tele_sales'];
					//}
					?></td>-->
					<!--<td>Fax(Sales) <?php #$settingArr['fax_number'];?></td>
					<td>Email <?php #$settingArr['email'];?></td>-->
					<td colspan='4'>Website <?=$settingArr['website'];?></td>
				</tr>
				<tr>
						<td colspan='4'><?=$settingArr['headoffice_address'];?></td>
				</tr>
				<tr>
						<td colspan='4'>
						<?php if(!empty(trim($kioskDetails['terms']))){
										echo $kioskDetails['terms'];
								}else{
										echo $settingArr['invoice_terms_conditions'];
								} ?>
						</td>
				</tr>
			</table>
		</td>
	</tr>
</table>