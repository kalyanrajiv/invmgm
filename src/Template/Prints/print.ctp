 
  <style type="text/css">

    BODY, TD
    {
      background-color: #ffffff;
      color: #000000;
      font-family: Arial;
      font-size: 8pt;
	  margin-bottom:20px;
	  margin-top:0px;
	  
    }

  </style>
  <?php
  $jQueryURL = "http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
  if(defined('URL_SCHEME')){
  	$jQueryURL = URL_SCHEME."ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
  }
  ?>
  <script type="text/javascript" src="<?php echo $jQueryURL;?>"></script>
<?php echo $this->Html->script('jquery.printElement');?>
<input type='button' id='printSelected' name='print' value='Print Receipt' style='width:200px;' align='center' />

<div id='printDiv'>
    <table border="1" cellspacing="0" style="margin-top: 0; margin-bottom: 0; width: 100px;" >
	  <tr>
		<td>
          <table  style="width:100px;">
			  <tr> 
				  <td align="center" style="font-size: 17pt"><IMG SRC="/img/hp logo.jpg" width="200" height="200"></td>
			  </tr>
			  <tr>
			   <td align="center"><strong><?=$kioskDetails['name'];?></td>
			  </tr>
			  <tr>
			   <td align="center"><?=$kioskDetails['address_1'];?>
					 <?=($kioskDetails['address_2'] != '') ? "<br/>".$kioskDetails['address_2'] : "";?></td>
			  </tr>
			  <tr>
			   <td align="center"><?=$kioskDetails['city'];?>, <?=$kioskDetails['state'];?></td>
			  </tr>
			  <tr>
			   <td align="center"><?=$kioskDetails['zip'];?>, UK. <?=($kioskDetails['contact'] != '') ? ", Contact:".$kioskDetails['contact'] : "";?></td>
			  </tr>
			  <tr>
			   <td align="center"><strong>REPAIR RECEIPT</strong></td>
			  </tr>
			  <tr>
				  <td><?php echo date('d-m-Y',strtotime($mobileRepairData['created'])); //$mobileRepairData['created']; ?></td>
			  </tr>
          </table>
          <table   style="width: 100px;">
			  <tr>
				<td>VAT Reg No.:</td>
				<td><?=$settingArr['vat_number'];?></td>
			  </tr>
			  <tr>
				<td>Repair No.</td>
			   <td><?php echo $mobileRepairData['id'];?></td>
			  </tr>
			  <tr>
			  <td>Rep</td>
              <td><?php if(array_key_exists($mobileRepairData['booked_by'],$userName)){
				echo $userName[$mobileRepairData['booked_by']];
			  }else{
				echo"--";
			  }?></td>
            </tr>
		  </table>
	    </td>
	  </tr>
	  <tr>
		<td>
		  <table style="width:100px;">
			  <tr> 
				 <td align="center">Customer</td>
			  </tr>
			  <tr>
				  <td align="center"><strong><?= strtoupper($mobileRepairData['customer_fname'])." ".strtoupper($mobileRepairData['customer_lname']);?></td>
			  </tr>
			  <tr>
				  <td align="center"><?=strtoupper($mobileRepairData['customer_address_1']);?>
					<?=($kioskDetails['address_2'] != '') ? "<br/>".$kioskDetails['address_2'] : "";?></td>
			  </tr>
			  <?php if(!empty($mobileRepairData['customer_address_2'])){?>
			  <tr>
				  <td align="center"><?=strtoupper($mobileRepairData['customer_address_2']);?></td>
			  </tr>
			  <?php } ?>
			  <?php if(!empty($mobileRepairData['city'])){?>
			  <tr>
				   <td align="center"><?=strtoupper($mobileRepairData['city']);?></td>
			  </tr>
			<?php } ?>
			  <?php if(!empty($mobileRepairData['state'])){?>
			  <tr>
					<td align="center"><?=strtoupper($mobileRepairData['state']);?></td>
			  </tr>
			  <?php } ?>
			  <tr>
				  <td align="center"><?=strtoupper($mobileRepairData['zip']);?></td>
			  </tr>
             
           </table>
		</td>
		 </tr>
	  <tr>
		<td>
		  <table>
			<tr>
				<td>Imei: </td><td><?php echo $mobileRepairData['imei'];?></td>
			</tr>
			<tr>
				<td>Brand: </td><td><?php echo $mobileRepairData['brand']['brand'];?></td>
			</tr>
			<tr>
				<td>Model: </td><td><?php echo $mobileRepairData['mobile_model']['model'];?></td>
			</tr>
			<tr>
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
				<td colspan='2'><?php if(array_key_exists($problemType,$problemTypeOptions)){
				  echo $problemTypeOptions[$problemType];
				  }?></td>
				<td><?php 
					  echo $CURRENCY_TYPE.number_format($exceptVatCost,2);
				?></td>
			</tr>
				<?php }?>
			<tr>
				<th colspan='2'>Total Amount</th>
				<td><?php echo $CURRENCY_TYPE.number_format($totalCost,2);
				 ?></td>
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
		   <tr>
		<td>
			<table border="1" cellspacing="0" style="width:70px;">
				 <tr>
                      <td align="left">Tel(Sales):</td>
                      <td align="right"><?=$settingArr['tele_sales'];?></td>
                  </tr>
                   <tr>
                        <td align="left">Fax(Sales):</td>
                        <td align="right"><?=$settingArr['fax_number'];?></td>
                  </tr>
					 
                  <tr>
                        <td align="left">Email:</td>
                        <td align="right"><?=$settingArr['email'];?></td>
                  </tr>
				  <tr>
                      <td align="left">Website:</td>
                      <td align="right"><?=$settingArr['website'];?></td>
                  </tr>
				  
			</table>
		</td>
        
	</tr>
     
     <tr>
      <td>
        <table border="0" cellspacing="0" width='100%'>  
            <tr>
               <td align="left" style ="font-size: 6pt;" wrap='wrap'><?php  
				echo $settingArr['headoffice_address'];
			    ?></td>
            </tr>
            <tr> <td align="left" style ="font-size: 6pt;" wrap="wrap"><?=$settingArr['invoice_terms_conditions'];?></td></tr>
         </tr>
    </table>
		</td>
	  </tr>
    </table>

</div>
  