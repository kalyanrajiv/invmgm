<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
	$siteBaseUrl = Configure::read('SITE_BASE_URL');
	//pr($mobilePurchase);die;
	$jQueryURL = "http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
	if(defined('URL_SCHEME')){
		$jQueryURL = URL_SCHEME."ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
	}
?>
<script type="text/javascript" src="<?php echo $jQueryURL;?>"></script>
<?php echo $this->Html->script('jquery.printElement');?>
<input type='button' id='printSelected' name='print' value='Print Receipt' style='width:200px;' align='center' />
<?php echo $this->Html->link(__('Go to Mobile Purchases List'), array('action' => 'index')); ?>&nbsp;
<?php echo $this->Html->link(__('View'), array('action' => 'view',$mobilePurchase[0]['id'])); ?>
<div id='printDiv' style="text-align: center;">
	
<table border="1" cellspacing="0" style="width: 700px;">
	<tr>
		<td><?php
		
		$imgUrl = $siteBaseUrl."/img/".$settingArr['logo_image'];
		echo $this->Html->image($imgUrl, array('fullBase' => true));?>
			<table style="text-align: center;float: right; width:450px;">
				<tr>
					<td style="font-size: 30px;"><strong>Purchase Receipt</strong></td>
				</tr>
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
						<tr>
							<?php if(!empty($kiosk_data) && $kiosk_data[0]->vat_applied == 1){ ?>
									<th>VAT Reg No.</th>
							<?php } ?>
							<th>Date.</th>
							<th>Purchase No.</th>
						</tr>
						<tr>
							<?php if(!empty($kiosk_data) && $kiosk_data[0]->vat_applied == 1){
								if(!empty(trim($kiosk_data[0]->vat_no))){ ?>
									<td><?=$kiosk_data[0]->vat_no;?></td>
								<?php }else{ ?>
										<td><?=$settingArr['vat_number'];?></td>		
								<?php }
								?>
							
							<?php } ?>
							<td><?=date('d-m-Y',strtotime($mobilePurchase[0]['created']));//$this->Time->format('d-m-Y',$mobilePurchase[0]['created'],null,null);?></td>
							<td><?=$mobilePurchase[0]['id'];?></td>
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
								<th>Purchase From</th>
							</tr>
							<tr>
								<td><?=strtoupper($mobilePurchase[0]['customer_fname']) ;
								echo "\t".strtoupper($mobilePurchase[0]['customer_lname']);
								?></td>
							</tr>
							<tr>
								<td><?=strtoupper($mobilePurchase[0]['customer_address_1']);?> <br>
								 <?=strtoupper($mobilePurchase[0]['customer_address_2']);?><br>
								 <?=strtoupper($mobilePurchase[0]['city'])." ".strtoupper($mobilePurchase[0]['state']);?>
								 <br>
								 <?=strtoupper($mobilePurchase[0]['zip']);?>
								</td>
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
				<th>Id</th>
				<th>Brand</th>
				<th>Model</th>
				<th>Network</th>
				<th>IMEI</th>
				<th>Cost Price</th>
				<th>Quantity</th>
				 
			</tr>
	<?php

	 // pr($mobilePurchase);
	foreach($mobilePurchase as $key => $sngmobilePurchase){
		$id = $sngmobilePurchase['id'];
		$brandname = $sngmobilePurchase['brand_id'];
		$Modelname = $sngmobilePurchase['mobile_model_id'];
		$network_id = $sngmobilePurchase['network_id'];
		if(array_key_exists($network_id,$networks)){
			$network = $networks[$network_id];
		}else{
			$network ="--";
		}
		
		$imei = $sngmobilePurchase['imei'];
		$cost_price = $sngmobilePurchase['topedup_price'];
		if(!empty($cost_price) && $cost_price>0){
			 $cost_price = $sngmobilePurchase['topedup_price'];
		}else{
			$cost_price = $sngmobilePurchase['cost_price'];
		}
		
		?>
		<tr>
			<td><?=$id; ?></td>
			<td><?= $brands[$brandname];?></td>
			<td><?= $mobileModels[$Modelname];?></td>
			<td><?= $network;?></td>
			<td><?= $imei;?></td>
			<td> <?=$CURRENCY_TYPE.$cost_price;?></td>
			<td><?= "1";?></td>
			 
			 
		</tr>
	<?php 	}/*elseif($product['status']==1 && $product['quantity'] ==0){
			$vatAmount = 0;
		}*/
	 
		 
	?>
			
			</table>
		</td>
	</tr>
	 
	<tr>
		<td>
			<table border="1" cellspacing="0">
				<tr>
					<td>Tel(Sales) <?php echo $settingArr['tele_sales'];?></td>
					<td>Fax(Sales) <?=$settingArr['fax_number'];?></td>
					<td>Email <?=$settingArr['email'];?></td>
					<td>Website <?=$settingArr['website'];?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
	<p style="text-align: center;">
		<?=$settingArr['headoffice_address'];?>
	</p>
		
	<p style="text-align: center; font-size: 10px;">
		<?php if(!empty($kiosk_data) && !empty($kiosk_data[0]->terms)){?>
		<?=$kiosk_data[0]->terms;?>
		<?php }else{?>
		<?=$settingArr['invoice_terms_conditions'];?>
		<?php }?>
	</p>
</div>