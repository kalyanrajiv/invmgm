<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=<charset>" />
    <title>Receipt</title>
  </head>

  <style type="text/css">

    BODY, TD
    {
      background-color: #ffffff;
      color: #000000;
      font-family: Arial;
      font-size: 8px;
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
<input type='button' id='printSelected' name='print' value='Print Receipt' style='width:220px;' align='center' />

<div id='printDiv' style="margin-right: 0px;">
    <table  cellspacing="0" style="margin-top: 0; margin-bottom: 0; width: 150px;" >
      <tr style="font-size: 8px;">
        <td>

          <table  style="width:70px;">
            <tr> 
              <td align="center" style="font-size: 17pt"><IMG SRC="/img/hp logo.jpg" width="250" height="250"></td>
            </tr>
			 </table>
			 <table  style="width:250px;">
			  <tr>
			  <td colspan=2>
				<?php
				$kiosk_show_info = array();
				$kiosk_id = $mobilePurchase[0]['kiosk_id'];
				foreach($kiosk_info as $key => $info){
				  if($info->id == $kiosk_id){
					$kiosk_show_info['address1'] = $info->address_1;
					$kiosk_show_info['address_2'] = $info->address_2;
					$kiosk_show_info['city'] = $info->city;
					$kiosk_show_info['state'] = $info->state;
					$kiosk_show_info['zip'] = $info->zip;
					$kiosk_show_info['email'] = $info->email;
					$kiosk_show_info['contact'] = $info->contact;
				  }
				}
				if(!empty($kiosk_show_info)){ ?>
				  <table>
					<?php if(!empty($kiosk_show_info['address1'])){ ?>
					<tr>
					  <td><span style="font-size: 12px;">
						<?=$kiosk_show_info['address1'];?>
					  
					
					<?php } ?>
					<?php if(!empty($kiosk_show_info['address_2'])){ ?>
					
					  
						<?=" , ".$kiosk_show_info['address_2'];?>
					  
					
					<?php } ?>
					<?php if(!empty($kiosk_show_info['city'])){ ?>
					
					  
						<?=" , ".$kiosk_show_info['city'];?>
					  
					
					<?php } ?>
					<?php if(!empty($kiosk_show_info['zip'])){ ?>
					
					 
						<?=" , ".$kiosk_show_info['zip'];?>
						</span>
					  </td>
					</tr>
					<tr>
					  <td>
						<span style="font-size: 12px;">
						 Tel :  
						  <?php if(!empty($kiosk_show_info['contact'])){?>
					  <?=$kiosk_show_info['contact'];?>
					  <?php }?>
					  
					  <?php if(!empty($kiosk_show_info['email'])){?>
					  <?=" , ".$kiosk_show_info['email'];?>
					  <?php }?>
					  
					   </br>
					  
					  <?php if(!empty($settingArr['website'])){?>
					  <?=" ".$settingArr['website'];?>
					  <?php }?>
					  </span>
					  </td>
					</tr>
					<?php } ?>
				  </table>
				<?php }
				
				?>
			  </td>
			 </tr>
			  
			  
            <tr>
			  <td style="font-size: 12px;" colspan=2><?=date('d-m-Y h:i:s',strtotime($mobilePurchase[0]['created']));?>  &nbsp; &nbsp; &nbsp; VAT : <?=$settingArr['vat_number'];?>     </td>
              
			</tr>
            <tr>
			  <td style="font-size: 12px;" align="left">Salesman:</td>
              <td style="font-size: 12px;" align="right"><?=$user_info[$mobilePurchase[0]['user_id']];?></td>
			</tr>
             <tr>
			  <td colspan=2 style="font-size: 12px;" align="left">Receipt No.: &nbsp; &nbsp; &nbsp;  <?=$mobilePurchase[0]['id'];?></td>
              
			</tr>
			 
           
            <tr>
              <td align="center" style="font-size: 12px;" colspan=2><b>Customer Detail :</b></td>
            </tr>
           
              <td style="font-size: 12px;" align="center" colspan=2 ><?=strtoupper($mobilePurchase[0]['customer_fname']) ;
								echo "\t".strtoupper($mobilePurchase[0]['customer_lname']);
								?></td>
              <tr>
                
                 <td style="font-size: 12px;" align="center" colspan=2><?=strtoupper($mobilePurchase[0]['customer_address_1']);?> <br>
								 <?=strtoupper($mobilePurchase[0]['customer_address_2']);?><br>
								 <?=strtoupper($mobilePurchase[0]['city'])." ".strtoupper($mobilePurchase[0]['state']);?>
								 <br>
								 <?=strtoupper($mobilePurchase[0]['zip']);?></td>
								 
							</tr>
                <?php
				//pr($mobilePurchase);
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
             <td style="font-size: 12px;" align="left">Cost Price:</td>
              <td style="font-size: 12px;" align="right"><?=$CURRENCY_TYPE.$cost_price;?></td>
		</tr>
        
			 
			 
	
	<?php 	}?>
  
  
           </table>

          

        </td>
      </tr>
      
     
     <tr style="font-size: 6px;">
      <td>
        <table  cellspacing="0" width='100%'>  
            <tr> <td align="left" wrap="wrap"><span style="font-size: 10px;"><?=$settingArr['invoice_terms_conditions'];?></span></td></tr>
         </tr>
    </table>
    </td>
		</tr>
	 
    </table>

</div>
  </body>
  
</html>
