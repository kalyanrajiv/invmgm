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