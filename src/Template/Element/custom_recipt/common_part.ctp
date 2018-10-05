<tr>
	<?php
	if(array_key_exists('mobile_status', $common_data) && $common_data['mobile_status'] == 1){
	?>
			  <td style="font-size: 12px;" colspan=2><?=date('d-m-Y h:i:s',strtotime($common_data['payment_date'])); }else{?>
			  <td style="font-size: 12px;" colspan=2><?=date('d-m-Y h:i:s',strtotime($common_data['created']));}?>
			  <?php
			  foreach($kiosk_info as $key => $info){
							if($info->id == $kiosk_id){
										  if($info->vat_applied == 1){
														if(!empty($info->vat_no)){
														?>
														&nbsp; &nbsp; &nbsp; VAT : <?=$info->vat_no;
														}else{ 
																	  if(!empty(trim($common_data['vat_number']))){
																					
																					echo "&nbsp; &nbsp; &nbsp; VAT : ".$common_data['vat_number'];
																	  }
														}
														?>
										  <?php }
							}
			  }
			  ?>
			  </td>
              
			</tr>
            <tr>
			  <td style="font-size: 12px;" align="left">Salesman:</td>
              <td style="font-size: 12px;" align="right"><?php if(array_key_exists($common_data['user_id'],$user_info)){
                                                                                                echo $user_info[$common_data['user_id']];
                                                                                              }else{
                                                                                                echo "--";
                                                                                              }
                                                                                                ?></td>
			</tr>
             <tr>
			  <td  style="font-size: 12px;">Receipt No.: &nbsp; &nbsp; &nbsp;  </td>
              <td style="font-size: 12px;" align="right"><?=$common_data['id'];?></td>
			</tr>
			 
           <?php
           if(!empty($common_data['cust'])){?>
            <tr>
              <td  style="font-size: 12px;" colspan=2><b>Customer Detail :</b></td>
            </tr>
           <?php if(!empty($common_data['cust']['customer_fname'])){?>
              <td style="font-size: 12px;"  colspan=2 ><?=strtoupper($common_data['cust']['customer_fname']) ;
								echo "\t".strtoupper($common_data['cust']['customer_lname']);
								?></td>
              <tr>
                <?php } ?>
                <?php if(!empty($common_data['cust']['customer_address_1'])){?>
                 <td style="font-size: 12px;"  colspan=2><?=strtoupper($common_data['cust']['customer_address_1']);?> <br>
                 <?php }?>
                 <?php if(!empty($common_data['cust']['customer_address_2'])){?>
								 <?=strtoupper($common_data['cust']['customer_address_2']);?><br>
                                 <?php } ?>
                                 <?php if(!empty($common_data['cust']['city']) && !empty($common_data['cust']['state'])){?>
								 <?=strtoupper($common_data['cust']['city'])." ".strtoupper($common_data['cust']['state']);?>
								 <br>
								 <?php } ?>
                                 <?php if(!empty($common_data['cust']['zip'])){ ?>
								 <?=strtoupper($common_data['cust']['zip']);?></td>
								 <?php } ?>
							</tr>
              <?php } ?>