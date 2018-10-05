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
$currency = Configure::read('CURRENCY_TYPE');
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
$siteBaseUrl = Configure::read('SITE_BASE_URL');?>
<input type='button' id='printSelected' name='print' value='Print Receipt' style='width:200px;' align='center' />

<?php 
echo $this->Html->link('View Sales', array('controller' => 'mobile_unlock_sales', 'action' => 'view_unlock_sales')); ?>
<div id='printDiv' style="text-align: center;">
<?php $customerEmail = $mobileUnlockData['customer_email'];?>
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
			<table style="text-align: center; width:100%;">
				<tr>
					<td style="font-size: 20px;"><strong>UNLOCK RECEIPT</strong></td>
				</tr>
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
						<tr>
							<?php if(!empty($kioskDetails) && $kioskDetails['vat_applied'] == 1){
								?>
								<th>VAT Reg No.</th>
							<?php } ?>
							<th>Date.</th>
							<th>Unlock No.</th>
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
							<?php
							if($mobileStatus == 1){
							?>
							<td><?php echo date('d-m-Y',strtotime($date));//$this->Time->format('d-m-Y',$mobileUnlockData['created'],null,null);?></td>
							<?php }else{ ?>
							<td><?php echo date('d-m-Y',strtotime($mobileUnlockData['created']));//$this->Time->format('d-m-Y',$mobileUnlockData['created'],null,null);?></td>
							<?php } ?>
							<td><?php echo $mobileUnlockData['id'];?></td>
							<td><?php
							if(array_key_exists($mobileUnlockData['booked_by'],$userName)){
								echo $userName[$mobileUnlockData['booked_by']];
							}
							?></td>
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
			<table border="1" cellspacing="0" style="font-size: 10px;">
				<tr>
					<th>Imei</th>
					<th>Brand</th>
					<th>Model</th>
					<th>Network</th>
					<th>Amount</th>
				</tr>
				<tr><?php //pr($mobileUnlockData);die;  ?>
					<td><?php echo $mobileUnlockData['imei'];?></td>
					<td><?php echo $mobileUnlockData['brand']['brand'];?></td>
					<td><?php echo $mobileUnlockData['mobile_model']['model'];?></td>
					<td><?php echo $mobileUnlockData['network']['name'];?></td>
					<td><?php echo $CURRENCY_TYPE.number_format($subTotal,2);?></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0" style="font-size: 10px;">
				<tr>
					<th colspan='2'>Sub Total</th>
					<td><?php echo $CURRENCY_TYPE.number_format($subTotal,2);?></td>
				</tr>
				<tr>
					<th colspan='2'>VAT</th>
					<td><?php echo $CURRENCY_TYPE.number_format($vatAmount,2);?></td>
				</tr>				
				<tr>
					<th colspan='2'>Total Amount</th>
					<td><?php echo $CURRENCY_TYPE.number_format($mobileUnlockData['estimated_cost'],2);?></td>
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
						
						<td colspan='2'><strong>Refund on(<?php echo date('jS M, Y ',strtotime($refundData['refund_on']));//$this->Time->format('jS M, Y ', $refundData['refund_on'],null,null); ?>)</strong></td>
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
					<?php if(isset($totalRefundAmount) && $totalRefundAmount!=0){
						$grandTotal = $mobileUnlockData['estimated_cost']+$totalRefundAmount; // refund amount is already in negative
						?>
						
					<tr>
					<th colspan='2' style='text-align: center;'>Grand Total (after refund)</th>
					<td><?php echo   $CURRENCY_TYPE.number_format($grandTotal,2);//$settingArr['currency_symbol'].number_format($grandTotal,2);?></td>
				</tr>
				<?php }
				}?>
			</table>
		</td>
	</tr>
	<?php if(empty(trim($kioskDetails['terms']))){ ?>
	<tr>
		<td>
			<table border="1" cellspacing="0">
				<tr>
					<td colspan='4'>Website <?=$settingArr['website'];?></td>
				</tr>
			</table>
		</td>
	</tr>
	<?php } ?>
</table>
		
	<p style="text-align: left; font-size: 10px;">
		<div style="text-align: left; font-size: 10px;">
			<?php if(!empty(trim($kioskDetails['terms']))){
						echo $kioskDetails['terms'];
				}else{
						echo $settingArr['invoice_terms_conditions'];
				} ?>
		
		</div>
	</p>
</div>
<?php
echo $this->Form->create('UnlockReceipt');
echo $this->Form->Input('email',array('type'=>'text','value'=>$customerEmail,'style'=>"width: 291px;"));
echo $this->Form->submit('Send Receipt',array('name'=>'submit'));
echo $this->Form->end();
?>