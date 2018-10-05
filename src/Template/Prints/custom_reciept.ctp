<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
echo $this->element('custom_recipt/header');
$siteBaseUrl = Configure::read('SITE_BASE_URL');
$self_kiosk_info = array();
			if(!empty($kiosk_info)){
				foreach($kiosk_info as $key => $value){
					if($kiosk_id == $value->id){
						$self_kiosk_info = $value;
					}
				}
			}
			
?>
<tr>
    <td>
         <table  style="width:70px;">
            <tr>
				<?php if(empty(trim($self_kiosk_info->terms))){?>
				 <td align="center" style="font-size: 17pt"><IMG SRC="<?php echo "/img/".$settingArr['logo_image'];?>" width="250" height="125"></td>
				<?php }else{
					if(!empty(trim($self_kiosk_info->logo_image))){
						$imgUrl = $siteBaseUrl."/logo/".$self_kiosk_info->id."/".$self_kiosk_info->logo_image;?>
						<td align="center" style="font-size: 17pt"><IMG SRC="<?php echo $imgUrl;?>" width="250" height="125"></td>
				<?php 	}else{
						 $imgUrl = $siteBaseUrl."/img/".$settingArr['logo_image']; ?>
						 <td align="center" style="font-size: 17pt"><IMG SRC="<?php echo $imgUrl;?>" width="250" height="125"></td>
					<?php }
				 } ?>
             
            </tr>
			 </table>
         <table  style="width:250px;">
            <?php
			
			$common_data['vat_number'] = $settingArr['vat_number'];
			echo $this->element('custom_recipt/kiosk_info',array(
                                                                       'kiosk_info' => $kiosk_info,// all kiosk detail
                                                                       'kiosk_id' => $kiosk_id,
                                                                       )); ?>
            <?php echo $this->element('custom_recipt/common_part',array(
                                                                       'common_data' => $common_data,
                                                                       'user_info' => $user_info, //// all user detail
																	   'kiosk_info' => $kiosk_info,
																	   'kiosk_id' => $kiosk_id,
                                                                       )); ?>
																	   
            <?php
			if($recipt_for == "mobile_purchase"){
			echo $this->element('custom_recipt/mobile_purchase',array(
                                                                       'mobilePurchase' => $mobilePurchase,
                                                                       'brands' => $brands,
                                                                       'mobileModels' => $mobileModels,
                                                                       'CURRENCY_TYPE' => $CURRENCY_TYPE,
                                                                       ));
			}elseif($recipt_for == "repair"){
				echo $this->element('custom_recipt/repair',array(
                                                                       'repair' => $repair_data,
                                                                       'brands' => $brands,
                                                                       'mobileModels' => $mobileModels,
                                                                       'CURRENCY_TYPE' => $CURRENCY_TYPE,
                                                                       ));
			}elseif($recipt_for == "mobile_sale"){
                echo $this->element('custom_recipt/mobile_sale',array(
                                                                       'sale_data' => $mobileResaleData,
                                                                       'brands' => $brandName,
                                                                       'mobileModels' => $modelName,
                                                                       'CURRENCY_TYPE' => $CURRENCY_TYPE,
                                                                       'refund_data' => $mobileReturnData,
                                                                       'pay_terms' => $pay_arr,
                                                                       ));
            }elseif($recipt_for == "mobile_bulk_sale"){
                echo $this->element('custom_recipt/mobile_bulk_sale',array(
                                                                       'sale_data' => $mobileResaleData,
                                                                       'brands' => $brandName,
                                                                       'mobileModels' => $modelName,
                                                                       'CURRENCY_TYPE' => $CURRENCY_TYPE,
                                                                       'refund_data' => $mobileReturnData,
                                                                       'pay_terms' => $pay_arr,
                                                                       ));
            }elseif($recipt_for == "new_sale"){
				
				echo $this->element('custom_recipt/new_sale',array(
                                                                       'kiosk_product_sale' => $kiosk_products_data,
																	   'product_reciept' => $productReceipt,
																	   'productName' => $productName,
																	   'payment_method' => $payment_method,
																	   'CURRENCY_TYPE' => $CURRENCY_TYPE,
																	   'qttyArr' => $qttyArr,
                                                                       ));
			}elseif($recipt_for == "unlock"){
				 
                echo $this->element('custom_recipt/unlock',array(
                                                                       'umlock' => $mobileUnlockData,
																		'brands' => $brands,
																		'mobileModels' => $mobileModels,
																		'CURRENCY_TYPE' => $CURRENCY_TYPE,
																		 
                                                                       ));
            }
			?>
         </table>
    </td>
</tr>


<?php

		
		if($recipt_for == "mobile_sale"){
			if(!empty($self_kiosk_info->terms)){
				echo $this->element('custom_recipt/footer',array(
													 'term_and_conditions' => $self_kiosk_info->terms,
													 ));    
			}else{
				echo $this->element('custom_recipt/footer',array(
													 'term_and_conditions' => $settingArr['terms_resale'],
													 ));    	
			}
			
		}elseif($recipt_for == "repair"){
			if(!empty($self_kiosk_info->terms)){
				echo $this->element('custom_recipt/footer',array(
													 'term_and_conditions' => $self_kiosk_info->terms,
													 ));    
			}else{
				echo $this->element('custom_recipt/footer',array(
													 'term_and_conditions' => $settingArr['terms_repair'],
													 ));   	
			}
			
		}elseif($recipt_for == "unlock"){
			if(!empty($self_kiosk_info->terms)){
				echo $this->element('custom_recipt/footer',array(
													 'term_and_conditions' => $self_kiosk_info->terms,
													 ));    
			}else{
				echo $this->element('custom_recipt/footer',array(
													 'term_and_conditions' => $settingArr['terms_unlock'],
													 ));   
			}
				
		}elseif($recipt_for == "mobile_purchase"){
			if(!empty($self_kiosk_info->terms)){
				echo $this->element('custom_recipt/footer',array(
													 'term_and_conditions' => $self_kiosk_info->terms,
													 ));    
			}else{
				echo $this->element('custom_recipt/footer',array(
													 'term_and_conditions' => $settingArr['mobile_purchase_terms'],
													 ));   
			}
				
		}elseif($recipt_for == "mobile_bulk_sale"){
			if(!empty($self_kiosk_info->terms)){
				echo $this->element('custom_recipt/footer',array(
													 'term_and_conditions' => $self_kiosk_info->terms,
													 ));    
			}else{
				echo $this->element('custom_recipt/footer',array(
													 'term_and_conditions' => $settingArr['terms_bulk_resale'],
													 ));   
			}
				
		}elseif($recipt_for == "new_sale"){
			if(!empty($self_kiosk_info->terms)){
				echo $this->element('custom_recipt/footer',array(
													 'term_and_conditions' => $self_kiosk_info->terms,
													 ));    
			}else{
					echo $this->element('custom_recipt/footer',array(
													 'term_and_conditions' => $settingArr['invoice_terms_conditions'],
													 ));   
			}
				
		}else{
			if(!empty($self_kiosk_info->terms)){
				echo $this->element('custom_recipt/footer',array(
													 'term_and_conditions' => $self_kiosk_info->terms,
													 ));    
			}else{
				echo $this->element('custom_recipt/footer',array(
													 'term_and_conditions' => $settingArr['invoice_terms_conditions'],
													 ));	
			}
			
		}
		//if($recipt_for == "mobile_sale"){
		//	echo $this->element('custom_recipt/footer',array(
		//												 'term_and_conditions' => $settingArr['invoice_terms_conditions'],
		//												 ));    
		//}else{
		//	echo $this->element('custom_recipt/footer',array(
		//												 'term_and_conditions' => $settingArr['invoice_terms_conditions'],
		//												 ));
		//}

?>
<script>
 $(document).ready(function() {
		$("#printSelected").click(function() {
			$('#ProductPlacedOrderForm').hide();
			$('#heighlighted_block').hide();
			$('#cancel_item_1').hide();
			$('#Dispatch').hide();
		    printElem({
				printMode:'popup',
				leaveOpen:true,
				/*overrideElementCSS:[
							'print.css',
							{ href:'http://<?php echo ADMIN_DOMAIN;?>/css/print.css',media:'print'}
						]*/
				overrideElementCSS:['http://<?php echo ADMIN_DOMAIN;?>/css/print.css']
				});
		});
	 });
	function printElem(options){
		$('#printDiv').printElement(options);
	}	
</script>