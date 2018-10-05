<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php $siteBaseUrl = Configure::read('SITE_BASE_URL');
$currency = Configure::read('CURRENCY_TYPE');
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));?>
<table border="1" cellspacing="0" style="width: 700px;">
	<tr>
		<td><?php $imgUrl = "{$siteBaseUrl}/img/".$settingArr['logo_image'];
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
			<table style="text-align: center; width:100%;">
				<tr>
					<td style="font-size: 30px;"><strong>UNLOCK RECEIPT</strong></td>
				</tr>
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
						<tr>
							<th>VAT Reg No.</th>
							<th>Date.</th>
							<th>Unlock No.</th>
							<th>Rep</th>
						</tr>
						<tr>
							<td><?php echo $settingArr['vat_number'];?></td>
							<td><?php echo date('d-m-Y',strtotime($mobileUnlockData['created']));//$this->Time->format('d-m-Y',$mobileUnlockData['created'],null,null);?></td>
							<td><?php echo $mobileUnlockData['id'];?></td>
							<td><?php echo $userName[$mobileUnlockData['booked_by']];?></td>
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
								<td><?php echo strtoupper($mobileUnlockData['customer_fname'])." ".strtoupper($mobileUnlockData['customer_lname']);?></td>
							</tr>
							<tr>
								<td><?php echo strtoupper($mobileUnlockData['customer_address_1']);?></td>
							</tr>
							<?php if(!empty($mobileUnlockData['customer_address_2'])){?>
								<tr>
									<td><?php echo strtoupper($mobileUnlockData['customer_address_2']);?></td>
								</tr>
							<?php } ?>
							<?php if(!empty($mobileUnlockData['city'])){?>
								<tr>
									<td><?php echo strtoupper($mobileUnlockData['city']);?></td>
								</tr>
							<?php } ?>
							<?php if(!empty($mobileUnlockData['state'])){?>
								<tr>
									<td><?php echo strtoupper($mobileUnlockData['state']);?></td>
								</tr>
							<?php } ?>
							<tr>
								<td><?php echo strtoupper($mobileUnlockData['zip']);?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<?php
		$vat = $settingArr['vat'];
		$subTotal = $mobileUnlockData['estimated_cost']/(1+$vat/100);
		$vatAmount = $mobileUnlockData['estimated_cost'] - $subTotal;
	?>
	<tr>
		<td>
			<table border="1" cellspacing="0" style="font-size: 10px;width: 100%;">
				<tr>
					<th>Imei</th>
					<th>Brand</th>
					<th>Model</th>
					<th>Network</th>
					<th>Amount</th>
				</tr>
				<tr>
					<td><?php echo $mobileUnlockData['imei'];?></td>
					<td><?php echo $mobileUnlockData['brand']['brand'];?></td>
					<td><?php echo $mobileUnlockData['mobile_model']['model'];?></td>
					<td><?php echo $mobileUnlockData['network']['name'];?></td>
					<td><?php echo $settingArr['currency_symbol'].number_format($subTotal,2);?></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0" style="font-size: 10px;width: 100%;">
				<tr>
					<th colspan='2'>Sub Total</th>
					<td><?php echo $settingArr['currency_symbol'].number_format($subTotal,2);?></td>
				</tr>
				<tr>
					<th colspan='2'>VAT</th>
					<td><?php echo $settingArr['currency_symbol'].number_format($vatAmount,2);?></td>
				</tr>				
				<tr>
					<th colspan='2'>Total Amount</th>
					<td><?php echo $settingArr['currency_symbol'].number_format($mobileUnlockData['estimated_cost'],2);?></td>
				</tr>
				<?php if(!empty($unlockRefundData)){
					 $totalRefundAmount = 0;?>
					<tr>
						<td colspan='2' style='text-align: center;'>
							<strong>Refund Details</strong>
						</td>
					</tr>
					<?php foreach($unlockRefundData as $key=>$refundData){
						//echo "totalRefundAmount".
						$totalRefundAmount+=$refundData['refund_amount'];
						?>
					<tr>
						
						<td colspan='2'><strong>Refund on(<?php echo $this->Time->format('jS M, Y ', $refundData['refund_on'],null,null); ?>)</strong></td>
						<td><?php echo $this->Number->currency(-$refundData['refund_amount'],'BRL');
					 //$settingArr['currency_symbol'].-$refundData['refund_amount'];?></td>
					</tr>
					<?php } ?>
					<tr>
						<th colspan='2'>Total Refund Amount</th>
						<td><?php /*if(array_key_exists('refund_amount',$totalRefundAmount)){*/
						echo $this->Number->currency(-$totalRefundAmount,'BRL');
						//}?></td>
					</tr>
					<?php if(isset($totalRefundAmount) && $totalRefundAmount!=0){
						$grandTotal = $mobileUnlockData['estimated_cost']+$totalRefundAmount; // refund amount is already in negative
						?>
						
					<tr>
					<th colspan='2' style='text-align: center;'>Grand Total (after refund)</th>
					<td><?php echo   $this->Number->currency(number_format($grandTotal,2),'BRL');//$settingArr['currency_symbol'].number_format($grandTotal,2);?></td>
				</tr>
				<?php }
				}?>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0" style="width: 100%;">
				<tr>
					<td colspan='4'>Website <?=$settingArr['website'];?></td>
				</tr>
				<tr>
						<td colspan='4'><?=$settingArr['headoffice_address'];?></td>
				</tr>
				<tr>
						<td colspan='4'><?=$settingArr['invoice_terms_conditions'];?></td>
				</tr>
			</table>
		</td>
	</tr>