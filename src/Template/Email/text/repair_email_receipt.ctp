<table border="1" cellspacing="0" style="width: 700px;">
	<tr>
		<td><?php $imgUrl = "/img/".$settingArr['logo_image'];
		echo $this->Html->image($imgUrl, array('fullBase' => true));?>
			<table style="text-align: center;float: right; width:450px;">
				<tr>
					<td style="font-size: 30px;"><strong>REPAIR RECEIPT</strong></td>
				</tr>
				<tr>
					<td>
						<table border="1" width="100%" cellspacing="0">
						<tr>
							<th>VAT Reg No.</th>
							<th>Date.</th>
							<th>Repair No.</th>
							<th>Rep</th>
						</tr>
						<tr>
							<td><?php echo $settingArr['vat_number'];?></td>
							<td><?php echo $this->Time->format('d-m-Y',$mobileRepairData['MobileRepair']['created'],null,null);?></td>
							<td><?php echo $mobileRepairData['MobileRepair']['id'];?></td>
							<td><?php echo $userName[$mobileRepairData['MobileRepair']['booked_by']];?></td>
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
								<td><?php echo strtoupper($mobileRepairData['MobileRepair']['customer_fname'])." ".strtoupper($mobileRepairData['MobileRepair']['customer_lname']);?></td>
							</tr>
							<tr>
								<td><?php echo strtoupper($mobileRepairData['MobileRepair']['customer_address_1']);?></td>
							</tr>
							<?php if(!empty($mobileRepairData['MobileRepair']['customer_address_2'])){?>
								<tr>
									<td><?php echo strtoupper($mobileRepairData['MobileRepair']['customer_address_2']);?></td>
								</tr>
							<?php } ?>
							<?php if(!empty($mobileRepairData['MobileRepair']['city'])){?>
								<tr>
									<td><?php echo strtoupper($mobileRepairData['MobileRepair']['city']);?></td>
								</tr>
							<?php } ?>
							<?php if(!empty($mobileRepairData['MobileRepair']['state'])){?>
								<tr>
									<td><?php echo strtoupper($mobileRepairData['MobileRepair']['state']);?></td>
								</tr>
							<?php } ?>
							<tr>
								<td><?php echo strtoupper($mobileRepairData['MobileRepair']['zip']);?></td>
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
					<td><strong>Imei: </strong><?php echo $mobileRepairData['MobileRepair']['imei'];?></td>
					<td><strong>Brand: </strong><?php echo $mobileRepairData['Brand']['brand'];?></td>
					<td><strong>Model: </strong><?php echo $mobileRepairData['MobileModel']['model'];?></td>
				</tr>
				<tr>
					<th colspan='2'>Problem</th>
					<th>Amount</th>
				</tr>
				<?php
					$vat = $settingArr['vat'];
					$problemArr = explode("|",$mobileRepairData['MobileRepair']['problem_type']);
					$estimatedCostArr = explode("|",$mobileRepairData['MobileRepair']['estimated_cost']);
					$totalCost = 0;
					foreach($problemArr as $key=>$problemType){
						$estimatedCost = $estimatedCostArr[$key];
						$exceptVatCost = $estimatedCost/(1+$vat/100);
						$totalCost+=$estimatedCost;
				?>
				<tr>
					<td colspan='2'><?php echo $problemTypeOptions[$problemType];?></td>
					<td><?php echo $settingArr['currency_symbol'].number_format($exceptVatCost,2);?></td>
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
					<td><?php echo $settingArr['currency_symbol'].number_format($subTotal,2);?></td>
				</tr>
				<tr>
					<th colspan='2'>VAT</th>
					<td><?php echo $settingArr['currency_symbol'].number_format($vatAmount,2);?></td>
				</tr>				
				<tr>
					<th colspan='2'>Total Amount</th>
					<td><?php echo $settingArr['currency_symbol'].number_format($totalCost,2);?></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0" style="width: 100%;">
				<tr>
					<td>Tel(Sales) <?php
					if(!empty($kioskContact)){
						echo $kioskContact;
					}else{
						echo $settingArr['tele_sales'];
					}
					?></td>
					<td>Fax(Sales) <?=$settingArr['fax_number'];?></td>
					<td>Email <?=$settingArr['email'];?></td>
					<td>Website <?=$settingArr['website'];?></td>
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
</table>