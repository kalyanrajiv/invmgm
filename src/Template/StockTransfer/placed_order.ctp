<?php
		use Cake\Core\Configure;
		use Cake\Core\Configure\Engine\PhpConfig;
		$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
		$jQueryURL = "http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
		if(defined('URL_SCHEME')){
			$jQueryURL = URL_SCHEME."ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
		}
?>
<script type="text/javascript" src="<?php echo $jQueryURL;?>"></script>
<?php
	use Cake\Utility\Text; 
	echo $this->Html->script('jquery.printElement');
	echo $this->Html->script('jquery.blockUI');
	echo $this->Html->css('model/style.css');
	echo $this->Html->css('model/submodal.css');
 echo $this->Html->script('model/submodalsource.js');
 echo $this->Html->script('model/submodal.js');
 $check_price = $this->Url->build(['controller' => 'stock-transfer', 'action' => 'check_price'],true);
	
	$kioskPlacedOrderId = $this->request->params['pass']['0'];
	if(array_key_exists($placedby,$users)){
		$placedbyName = $users[$placedby];
	}else{
		$placedbyName = '--';
	}
	$style = "'width: 136px;float: right;margin-right: 402px;'";
?>
<input type='hidden' name='check_price' id='check_price' value='<?=$check_price?>' />

<div class="centralStocks index">
	<?php
	
		$lock = $KioskPlacedOrders_data['lock_status'];
		if($lock == 1){
	?>
	
    <input type='button' id='printSelected' name='print' value='Print Receipt' style='width:200px;' align='center' />
	<?php } ?><h4 style="background-color: yellow;width: 522px;font-size: 16px;height: 29px;font-weight: bold;">(Print function would be available only for locked orders)</h4>
    <?php echo $this->Html->link(__(' Kiosk Placed Order'), array('controller' => 'kiosk_orders', 'action' => 'placed_orders'));?>
	<?php
	
	$merged = $KioskPlacedOrders_data['merged'];
  if(!empty($KioskPlacedOrders_data['merge_data'])){
	
		$merged_data = unserialize($KioskPlacedOrders_data['merge_data']);
  }else{
	$merged_data = "";
  }
  $kiosk_id = $kiosk["id"];
  
	$kiosk_id = $kiosk["id"];
	if($lock == 0 && $this->request->session()->read('Auth.User.group_id') != KIOSK_USERS){
		?>
	<input type = "button" onclick="JavaScript: updateStatus(<?php echo $kioskPlacedOrderId?>,<?php  echo $kiosk_id?>);" value = "Lock" style='width:200px;' />
	<?php }elseif($lock == 0 && $merged == 1 && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){ ?>
	<input type = "button" onclick="JavaScript: updateStatus(<?php echo $kioskPlacedOrderId?>,<?php  echo $kiosk_id?>);" value = "Finalize" style='width:200px;' />
	<?php }?>
	<div style="margin-left: -2px;">

	</div>
	
	<div id = 'printit'>
		<div id='printDiv'>
				<h2> <?php echo __('Phones Sold'); ?></h2>
	<table cellpadding="0" cellspacing="0">
		<tr><td colspan='8'><hr></td></tr>
        <tr>
			<th>Brand</th>
			<th>Model</th>
			<th>Placed Qty</th>
			<th></th>
        </tr>
		<?php
		//pr($res_data);die;
			foreach($res_data as $key => $value){
				$qantity = $model  = $qantity = "";
				$model = $value['model'];
				$brand_id = $value['brand'];
				$qantity = $value['quantity'];?>
				<tr style="background-color: yellow;">
					<td><?php echo $models[$model];?></td>
					<td><?php echo $brand[$brand_id];?></td>
					<td><?php echo $qantity;?></td>
					<td><?php echo $this->Html->link(__('Transfer Mobile'), array('controller' => 'mobile_purchases', 'action' => 'global_search'),array('target' => '_blank'));?></td>
				</tr>
			<?php }
		?>
	</table>	
			<h2><?php echo __("Transfer Stock to {$kiosk['name']}"); ?></h2>
	
			<form action='<?php echo $this->request->webroot;?>stock-transfer/forPrint/<?php echo $kioskPlacedOrderId;?>' method = 'POST' id = "ProductPlacedOrderForm">
				<table style="width: 400px;border: 1px; border-bottom-color: blue;">
					<tr>
						<td style='width:100px;'>Show Pictures?&nbsp;</td>
						<td><input type = "radio" name = "forprint" value = 'Yes' <?php if($forprint=='yes'){echo "checked";}?> /></td>
						<td>&nbsp;Yes &nbsp;</td>
						<td><input type = "radio" name = "forprint" value = 'No' <?php if($forprint=='no'){echo "checked";}?> /></td>
						<td>&nbsp;No</td>
						<td style='width:90px;'><button type="submit" form="ProductPlacedOrderForm" value="Submit">Submit</button></td>
					</tr>
				</table>
			</form>
	
		<?php
        // code for counting product rows and total sold qty  
		$count = 0 ;
		// pr($products);
		$sold_qty = 0;
		$quantity_dispatch = 0;
		$request_qty = 0;
		foreach ($products as $key => $product){
			$count++;
			$product_id = $product['id'];
			$quantity_dispatch+= $productArr[$product_id];
			$request_qty+= $productArr[(int)$product['id']];
			
		}
		?>
		 
		<?php
       // pr($kiosk);
	   if(!empty($merged_data)){
		$place_by_txt = " Merged By";
	   }else{
		$place_by_txt = " Placed By";
	   }
			echo "<h4>Order # <span style='color: blue;'>".$kioskPlacedOrderId."</span> placed on <span style='color: blue;'>".$modified."</span>".$place_by_txt.":- <span style='color: blue;'>".$placedbyName."</span></h4>";
			echo "<h4>Total Product # <span style='color: blue;'>".$count."</span>  Total Sold Quantity # <span style='color: blue;'>".$quantity_dispatch."</span>  Total Requested Quantity # <span style='color: blue;'>".$request_qty."</h4> ";
			if(strtotime(date("y-m-d")) > strtotime($modified)){
				$lock == 1;
			}
			
			
			if($lock == 1){
				echo "<h4 style='color: red;background-color: yellow;width: 187px; font-weight: bold;'>This order is in locked state</h4>";
			}
			
			if($merged == 1){
				echo "<h4 style='color: red;'>Merged order details:</h4>";
			}
			if(!empty($merged_data)){
				echo "<h4 style='color: red;'>";
				$sr_no = 0;
				foreach($merged_data as $merg_key => $merg_val){
					$sr_no++;
					$text = "";
					$text .= $sr_no." ";
					$text .= " Orig Order ID: ".$merg_val['id']." , ";
					
					$merged_dummy_created = $merg_val['created'];
					$merged_dummy_created->i18nFormat(
                                                [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                        );
						$merged_created =  $merged_dummy_created->i18nFormat('dd-MM-yyyy HH:mm:ss');
						
						$merged_created = date("d-m-y h:i a",strtotime($merged_created));
					
					if(array_key_exists($merg_val['user_id'],$users)){
						$username_new = $users[$merg_val['user_id']];	
					}else{
						$username_new = "";
					}
					$text .= "Placed On: ".$merged_created." , ";
					$text .= "Placed By: ".$username_new;
					if(count($merged_data) != $sr_no){
						$text .= " , ";
					}
					echo $text;
					echo "</br>";
				}
				echo "</h4>";
			}
			
			echo $this->Form->create('null',array(
									'url' => array('controller' => 'stock_transfer','action' => 'placed_order',$kioskPlacedOrderId),
									'id' => 'ProductPlacedOrderForm',
									'onSubmit' => 'return validateForm();',
									)); 		?>
			
	
			<table cellpadding="0" cellspacing="0">	
				<tr><td colspan='13'><hr></td></tr>
				<tr>
			
					<th>Sr.No</th>
					<?php
						if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
							if($lock == 1){
								
							}else{
								if($merged == 1 && $user_group_data[$placedby] == KIOSK_USERS){
									echo "<th>Delete</th>";
								}elseif($merged == 0){
									echo "<th>Delete</th>";
								}
							}
						}else{
							echo "<th>Delete</th>";
						}
						
						
						?>
					
					<th>Prod<br/>Code</th>
					<th>Prod<br/>title</th>
					<th>Category</th>
					<th>colour</th>
					<?php if($forprint == "yes"){echo "<th>Image</th>";}?>
					<th>Curr<br/>Stk</th>
					<th style="width: 50px;">Curr<br/>Price</th>
					<th>New<br/>Price(Inc Vat)</th>
					<th>Sold<br/>Qty</th>
					
						<?php
						if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
							if($lock == 1 ){
								
							}else{
								if($merged == 1 && $user_group_data[$placedby] == KIOSK_USERS){
									echo "<th width='40' style='width: 40px;'>Qty</th>";
								}elseif($merged == 0){
									echo "<th width='40' style='width: 40px;'>Qty</th>";
								}
							}
						}else{
							echo "<th width='40' style='width: 40px;'>Qty</th>";
						}
						
						
						?>
						
						
					
					<th width='55' style='width: 55px;'>Qty<br/>Rqstd</th>
					<th width='65' style='width: 65px;'>Remarks</th>
					
					<?php
						if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
							if($lock == 1 ){
								
							}else{
								if($merged == 1 && $user_group_data[$placedby] == KIOSK_USERS){
									echo "<th>Replace/Add</th>";
								}elseif($merged == 0){
									echo "<th>Replace/Add</th>";
								}
							}
						}else{
							echo "<th>Replace/Add</th>";
						}
						
						
						?>
					
					
					
					
				</tr>	
				<tbody>
	<?php  $productRemarks = "";
	//pr($products);
	$counter = 0;
	$groupStr = "";
	foreach ($product_new_arr as $key => $product):
	$groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
	$counter++;
		$productID = (int)$product['id'];
		$quantity2dispatch = 0;
		if(
		   $product['quantity'] < $productArr[$productID] &&
		   $product['quantity'] != 0
		){
			
			 $quantity2dispatch = $product['quantity'];
			$productRemarks = "";
		}elseif($product['quantity'] == 0){
			
			$quantity2dispatch = 0;
			$productRemarks = "Out of stock";
		}elseif($product['quantity'] > $productArr[$productID]){
			
			$quantity2dispatch = $productArr[$productID];
			$productRemarks = "";
		}else{
			$quantity2dispatch = $product['quantity'];
		}
        //pr($remarksArr);die;
		$userRemarks = $remarksArr[$productID];
       // pr($product);die;
          $text = $product['product'];
               $truncatedProduct =  Text::truncate(
                    $text,
                    40,
                    [
                        'ellipsis' => '...',
                        'exact' => false
                    ]
                );
		 
		
		$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product['id'].DS;
		$imageName = $product['image'];
		$absoluteImagePath = $imageDir.$imageName;
		$largeImageURL = $imageURL = "/thumb_no-image.png";
		if(@readlink($absoluteImagePath) ||file_exists($absoluteImagePath)){
			$imageURL = "{$siteBaseURL}/files/Products/image/".$product['id']."/thumb_".$imageName;
			$largeImageURL = "{$siteBaseURL}/files/Products/image/".$product['id']."/vga_".$imageName;
		}
		$productQuantity = "";
		$productPrice = $product['selling_price'];
	?>
	<?php
		if(array($product['id'],$statusArr)){
			if($statusArr[$product['id']] == 1){//replaced
				echo "<tr style='color: darkgreen;'>";
			}elseif($statusArr[$product['id']] == 2){
				echo "<tr style='color: blue;'>";
			}else{
					echo "<tr>";
			}
		}else{
			echo "<tr>";
		}
	?>
	
	<input type="hidden" name="KioskStock[kiosk_product_order_id][<?php echo $key; ?>]" value="<?php echo $product_sr_no[$product['id']];?>" />
	</td>
	<td><?php
	if($lock == 1){
		echo $product_sr_no[$product['id']];	
	}else{
			echo $counter;
	}?></td>
	<?php
						if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
							if($lock == 1){
								
							}else{ 
								if($merged == 1 && $user_group_data[$placedby] == KIOSK_USERS){ ?>
									<td><input type='checkbox' name='cancelled_items[]' data="<?php echo $product['product_code'];?>" value='<?php echo $product['id'];?>' /></td>
								<?php }elseif($merged == 0){ ?>
								<td><input type='checkbox' name='cancelled_items[]' data="<?php echo $product['product_code'];?>" value='<?php echo $product['id'];?>' /></td>
						<?php } }
						}else{ ?>
							<td><input type='checkbox' name='cancelled_items[]' data="<?php echo $product['product_code'];?>" value='<?php echo $product['id'];?>' /></td>
					<?php } 
						
	?>
		<td>
		<?php echo $product['product_code']; ?>
	    </td>
		<td style="width: 96px;">
		<?php
			echo $product['product'];
			//$this->Html->link($truncatedProduct,
				   //         array('controller' => 'products', 'action' => 'view', $product['id']),
					 //       array('escapeTitle' => false, 'title' => $product['product'])
					//);
			?>
		</td>
	    
		<td style="width: 82px;">
				<?=$categoryList[$product['category_id']]; ?>
		</td>
		<td style="width: 56px;">
			<?php echo $product["color"];?>
		</td>
		<?php if($forprint == "yes"){?>
		<td><?php
			echo $this->Html->link(
							$this->Html->image($imageURL, array('fullBase' => true,'width' => '95px','height' => '90px')),
							$largeImageURL,
							array('escapeTitle' => false, 'title' => $product['product'],'class' => "group{$key}")
					);
		
			?>
		</td>
		<?php } ?>
		<td><?php echo h($product['quantity']); ?>&nbsp;</td>
		<td><?php echo $CURRENCY_TYPE.$product['selling_price'] ;
			//echo h($product['selling_price']); ?>&nbsp;</td>
		
		<td><?php  
				echo $this->Form->input(null,array(
								'type' => 'hidden',
								'name' => "KioskStock[p_quantity][$key]",
								'value' => $product['quantity'],
								'label' => false,
								'style' => 'width:80px;'
						)
				   );
				echo $this->Form->input(null,array(
								'type' => 'hidden',
								'name' => "KioskStock[placed_order_id]",
								'value' => $kioskPlacedOrderId,
								'label' => false,
								'style' => 'width:80px;'
						)
									   );
				echo $this->Form->input(null,array(
								'type' => 'text',
								'name' => "KioskStock[price][$key]",
								'value' => $productPrice,
								'label' => false,
								'style' => 'width:40px;',
								'id' => "price_$key",
								'old_price' => $productPrice,
								)
						);
				echo $this->Form->input(null,array(
								'type' => 'hidden',
								'name' => "KioskStock[product_id][$key]",
								'value' => $product['id']
							 )
						);
				?>
		</td>
		<td><?php echo $org_product_qty[(int)$product['id']]-$diferenceArr[(int)$product['id']];?></td>
        
		<?php
						if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
							if($lock == 1 ){ ?>
								
							<?php }else{
								if($merged == 1 && $user_group_data[$placedby] == KIOSK_USERS){ 
									echo "<td>";
									if($quantity2dispatch==0){
										
										echo $this->Form->input(null,array(
																	'id' => 'quantity_check',
																	'type' => 'text',
																	'name' => "KioskStock[quantity][$key]",
																	'value' => $quantity2dispatch,
																	'label' => false,
																	'style' => 'width:40px;',
																	//'readonly' => 'readonly'   sourabh
																	'onChange' => "update_on_demand_quantities($productID, $kioskPlacedOrderId, this.value)", 
																	)
															);
									}else{
										echo $this->Form->input(null,array(
																'id' => 'quantity_check',
																'type' => 'text',
																'name' => "KioskStock[quantity][$key]",
																'value' => $quantity2dispatch,
																'label' => false,
																'style' => 'width:40px;',
																'readonly' => false,
																'onChange' => "update_on_demand_quantities($productID, $kioskPlacedOrderId, this.value)", 
																)
														);
									}
									echo "</td>";
						}elseif($merged == 0){
							echo "<td>";
									if($quantity2dispatch==0){
										
										echo $this->Form->input(null,array(
																	'id' => 'quantity_check',
																	'type' => 'text',
																	'name' => "KioskStock[quantity][$key]",
																	'value' => $quantity2dispatch,
																	'label' => false,
																	'style' => 'width:40px;',
																	//'readonly' => 'readonly'   sourabh
																	'onChange' => "update_on_demand_quantities($productID, $kioskPlacedOrderId, this.value)", 
																	)
															);
									}else{
										echo $this->Form->input(null,array(
																'id' => 'quantity_check',
																'type' => 'text',
																'name' => "KioskStock[quantity][$key]",
																'value' => $quantity2dispatch,
																'label' => false,
																'style' => 'width:40px;',
																'readonly' => false,
																'onChange' => "update_on_demand_quantities($productID, $kioskPlacedOrderId, this.value)", 
																)
														);
									}
									echo "</td>";
						}
							}
						}else{ 
							echo "<td>";
									if($quantity2dispatch==0){
										
										echo $this->Form->input(null,array(
																	'id' => 'quantity_check',
																	'type' => 'text',
																	'name' => "KioskStock[quantity][$key]",
																	'value' => $quantity2dispatch,
																	'label' => false,
																	'style' => 'width:40px;',
																	//'readonly' => 'readonly'   sourabh
																	'onChange' => "update_on_demand_quantities($productID, $kioskPlacedOrderId, this.value)", 
																	)
															);
									}else{
										echo $this->Form->input(null,array(
																'id' => 'quantity_check',
																'type' => 'text',
																'name' => "KioskStock[quantity][$key]",
																'value' => $quantity2dispatch,
																'label' => false,
																'style' => 'width:40px;',
																'readonly' => false,
																'onChange' => "update_on_demand_quantities($productID, $kioskPlacedOrderId, this.value)", 
																)
														);
									}
									echo "</td>";
						 }
						?>
            
			<td>
				<?php echo $org_product_qty[(int)$product['id']];?>
			</td>
			
            <td><?php
					if(array($product['id'],$statusArr)){
						if($statusArr[$product['id']] == 1){//replaced
								$productRemarks = $userRemarks;
						}elseif($statusArr[$product['id']] == 2){
							$productRemarks = $userRemarks;
						}else{
								$productRemarks = $productRemarks;
						}
					}else{
						echo "<tr>";
					}
			
			echo $this->Form->input(null,array(
                                    'type' => 'text',
                                    'name' => "KioskStock[remarks][$key]",
                                    'value' => $productRemarks,
                                    'label' => false,
                                    'style' => 'width:180px;',
                                    'readonly' => false
                                    )
                            ); ?>
            </td>
			<td style="width: 88px;"><?php
				/*echo $this->Html->link('cancel', array(
																	'controller' => 'stock_transfer',
																	'action' => 'placed_order',
																	$kioskPlacedOrderId, // id
																	'?' => array('cancel' => 'cancel',
																				 'productId' => $productID,
																				 'kioskId' => $kiosk['id'],
																				 ))
																);*/?>
			<?php
						if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
							if($lock == 1 ){
								
							}else{
								if($merged == 1 && $user_group_data[$placedby] == KIOSK_USERS){ ?>
									<input type='radio' id= 'replace_product_<?php echo $product['id'];?>' name='replace_product' value = '<?php echo $product['id'];?>' onClick = 'updateHidden(<?php echo $product['id'];?>)' />
						<?php }elseif($merged == 0){ ?>
							<input type='radio' id= 'replace_product_<?php echo $product['id'];?>' name='replace_product' value = '<?php echo $product['id'];?>' onClick = 'updateHidden(<?php echo $product['id'];?>)' />
							<?php } 
							}
						}else{ ?>
							<input type='radio' id= 'replace_product_<?php echo $product['id'];?>' name='replace_product' value = '<?php echo $product['id'];?>' onClick = 'updateHidden(<?php echo $product['id'];?>)' />
						<?php }						
						?>													
																
			
			</td>
	</tr>
	<?php
   // pr($userRemarks);continue;
	echo $this->Form->input('null',array(
                                    'type' => 'hidden',
                                    'name' => "KioskStock[kiosk_user_remarks][$key]",
                                    'value' => $userRemarks
                                    )
                            );
	if(!empty($userRemarks)){?>
		<tr><td colspan='8'><strong>Kiosk user remarks:</strong><br/><span style="background-color: yellow"><?=$userRemarks;?></span></td></tr>
	<?php } ?>
	
        <?php  endforeach; ?>
		<?php
     
		if(count($cancelProducts) > 0){
			foreach($cancelProducts as $key1 => $cancelProduct){
				$quantity2dispatch = 0;
				if(
				   $cancelProduct['quantity'] < $canProductArr[$cancelProduct['id']] &&
				   $cancelProduct['quantity'] != 0
				){
					$quantity2dispatch = $cancelProduct['quantity'];
				}elseif($cancelProduct['quantity'] == 0){
					$quantity2dispatch = 0;
				}elseif($cancelProduct['quantity'] > $canProductArr[$cancelProduct['id']]){
					if(!empty($productID)){
						$quantity2dispatch = $productArr[$productID];
					}
				}
				
				
				$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$cancelProduct['id'].DS;
				$imageName =  $cancelProduct['image'];
				$absoluteImagePath = $imageDir.$imageName;
				$imageURL = "/thumb_no-image.png";
				if(@readlink($absoluteImagePath) ||file_exists($absoluteImagePath)){
					$imageURL = "$siteBaseURL/files/Products/image/".$cancelProduct['id']."/thumb_".$imageName;
					$largeImageURL = "$siteBaseURL/files/Products/image/".$cancelProduct['id']."/$largeImageName"; //rasu
				}
				?>
				<tr style="color: red;">
					<td><?php echo $canSrNoArr[$cancelProduct['id']]; ?></td>
					<td></td>
					<td><?php echo $cancelProduct['product_code']; ?></td>
					<td>
						<?php  echo $cancelProduct['product'];  ?>
					</td>
					
					<td><?php echo $cancelCategoryList[$cancelProduct['category_id']]; ?></td>
					<td><?php echo $cancelProduct["color"];?></td>
					
						<?php
						//echo $this->Html->image($imageURL,array("width"=>"64px"));die;
						if($forprint == "yes"){
							echo "<td>";
							echo $this->Html->link(
											$this->Html->image($imageURL, array('fullBase' => true,'width' => '95px','height' => '90px')),
											$largeImageURL,
											array('escapeTitle' => false, 'title' => $cancelProduct['product'],'class' => "submodal")
									);
							echo "</td>";
						}
						?>
						
					
					<td><?php echo h($cancelProduct['quantity']); ?>&nbsp;</td>
					<td><?php if(!empty($product)) echo $CURRENCY_TYPE.$product['selling_price'];?></td>
					<td><?php $cancelProductPrice = $cancelProduct['selling_price'];
							echo $this->Form->input(null,array(
								'type' => 'text',
								'name' => "cancel_product",
								'value' => $cancelProductPrice,
								'label' => false,
								'style' => 'width:40px;'
								)
						);
					
					?></td>
					<td><?php echo $canProductArr[(int)$cancelProduct['id']]-$cnaceldiferenceArr[(int)$cancelProduct['id']];?></td>
					<td><?php if($quantity2dispatch==0){
						echo $this->Form->input(null,array(
													'id' => 'quantity_check',
													'type' => 'text',
													'name' => "quantity",
													'value' => $quantity2dispatch,
													'label' => false,
													'style' => 'width:40px;',
													 'disabled' => TRUE,
													)
											);
						}else{
						echo $this->Form->input(null,array(
													'id' => 'quantity_check',
													'type' => 'text',
													'name' => "quantity",
													'value' => $quantity2dispatch,
													'label' => false,
													'style' => 'width:40px;',
													 'disabled' => TRUE,
													)
											);
						}
					?>
					</td>
					<td><?php echo $canProductArr[$cancelProduct['id']];?></td>
					<td>
						<?php
						$remarks = $cancelRemarksArr[$cancelProduct['id']];
						$remarks = 'Cancelled';
								echo $this->Form->input(null,array(
                                    'type' => 'text',
                                    'name' => "remarks",
                                    'value' => $remarks,
                                    'label' => false,
                                    'style' => 'width:180px;',
                                    'readonly' => false
                                    )
								); ?>						
					</td>
					<td></td>
				</tr>
			<?php }
		}
			
		?>
	</tbody>
	</table>
	
	<input type="hidden" name='CancelButton' id="CancelButton" value='0'/>
	<input type="hidden" name='ReplaceButton' id="ReplaceButton" value='0'/>
	<div class="submit">
		<?php
						if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
							if($lock == 1 ){
								
							}else{
								if($merged == 1 && $user_group_data[$placedby] == KIOSK_USERS){
									echo "<input type='submit' value='Delete Item' id='cancel_item_1' style='width:100px;' onSubmit='return validateForm();'>";
								}elseif($merged == 0){
									echo "<input type='submit' value='Delete Item' id='cancel_item_1' style='width:100px;' onSubmit='return validateForm();'>";
								}
							}
						}else{
							echo "<input type='submit' value='Delete Item' id='cancel_item_1' style='width:100px;' onSubmit='return validateForm();'>";
						}
						
						
						?>
		
		
		<?php
    
		echo $this->Form->input('null',array(
							'type' => 'hidden',
							'name' => "KioskStock[kiosk_id]",
							'value' => $kiosk->id,
							'label' => false,
							'style' => 'width:80px;'
					));
		$options1 = array('label' => 'Dispatch','div' => false,'id' => 'Dispatch','name'=>'Dispatch');
		$option2 = array('label' => 'Cancel','div' => false,'id' => 'Dispatch');
		if($this->request->session()->read('Auth.User.group_id') == MANAGERS ||
         $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER||
         $this->request->session()->read('Auth.User.group_id') == inventory_manager||
		 $this->request->session()->read('Auth.User.group_id') == SALESMAN
		 ){
			echo $this->Form->submit("Dispatch",$options1);
			echo $this->Form->end();
		}else{
			$options2 = array('label' => 'Dispatch','div' => false,'id' => 'Dispatch','style' => "display:none"); ?>
			<input type="image" src="http://hpwaheguru.co.uk/img/transparent.png" id='Dispatch' name="Dispatch" style="width: 1px;height: 1px;"/>
			<?php
			if($lock == 1 ){
								
							}else{
								if($merged == 1 && $user_group_data[$placedby] == KIOSK_USERS){ 
									echo $this->Form->submit("Dispatch",$options2);
								}elseif($merged == 0){
									echo $this->Form->submit("Dispatch",$options2);
								}
							}
            echo $this->Form->end();
		}
	?>
	</div> 
		 </div> 
	</div > 
	<div id = "heighlighted_block">
	 
	* Blue color heighlighted rows are newly added</br>
	** Dark green color rows are replaced</br>
	***Red colored rows are canceled
	</div>
	<?php
		if(isset($repProducts)){
            echo $this->element('product', array('products' => $repProducts));
		}
	?>
	 
</div>



<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<?php  if($this->request->session()->read('Auth.User.group_id')  == ADMINISTRATORS ||
					$this->request->session()->read('Auth.User.group_id')  == MANAGERS ||
					$this->request->session()->read('Auth.User.group_id')  == inventory_manager){
					echo $this->element('sidebar/order_menus');
				}else{
					echo $this->element('sidebar/kiosk_order_menus');
		}?>
</div>
<?php
	$update_type = $this->Url->build(['controller' => 'StockTransfer', 'action' => 'updatedOrderQty'],true);
	echo "<input type='hidden' id='updateQtyURL' value='$update_type'/>";
	
	$update_type1 = $this->Url->build(['controller' => 'StockTransfer', 'action' => 'updateLockNormal'],true);
	echo "<input type='hidden' id='updateLockURL' value='$update_type1'/>";
	
	$update_type2 = $this->Url->build(['controller' => 'StockTransfer', 'action' => 'placedOrder',$kioskPlacedOrderId],true);
	echo "<input type='hidden' id='redirect_url' value='$update_type2'/>";
?>
<script>
	function printDiv() {
		var printContents = document.getElementById("printit").innerHTML;
		var originalContents = document.body.innerHTML;
		document.body.innerHTML = printContents;
		$('#ProductPlacedOrderForm').hide();
		$('#heighlighted_block').hide();
		$('#cancel_item_1').hide();
		$('#Dispatch').hide();
		window.print();
		document.body.innerHTML = originalContents;
		location.reload();
	}
</script>

<script>
	$("input[id*='quantity_check']").keydown(function (event) {
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||
		event.keyCode == 46  || event.keyCode == 183
		) {
			;
			//48-57 => 0..9
			//8 => Backspace; 9 => tab 
		} else {
			event.preventDefault();
		}
		
 });
</script>
<script type="text/javascript">
	function updateHidden(productID){
		$('#ReplaceButton').val(1);
		$( "#Dispatch" ).trigger( "click" );
	}
	
	function validateForm(cancel = 0) {
				var cancelled = $('#CancelButton').val();
				var replaced = $('#ReplaceButton').val();
				if (replaced == 1) {
														
				}else if (cancelled == 1) {
					var favorite = [];
					$.each($("input[name='cancelled_items[]']:checked"), function(){
						var productCode = $(this).attr( "data" );
						favorite.push(productCode);//$(this).val();
					});
					
					if (favorite.length == 0) {
							alert("You have not selected any item for cancellation! If you want to replace product or want to add new product, please uncheck all checkboxes!");
							return false;
					}else{
							var r = confirm("Are you sure you want to cancel product(s) with product code(s):"+favorite.join(", "));
							if (r == true) {
								$('#CancelButton').val('1');
								return true;
							} else {
								$('#CancelButton').val('0');
								return false;
							}
					}
    }
 }
	
 $(document).ready(function() {
				//var delete
				$('#Dispatch').click(function() {
					$('#CancelButton').val('0');
					$.blockUI({ message: 'Just a moment...' });
				});
				$('#cancel_item_1').click(function() {
					$('#CancelButton').val('1');
				});
		
				$("#cancel_item").click(function(){
					var favorite = [];
					$.each($("input[name='cancelled_items[]']:checked"), function(){            
									favorite.push($(this).val());
					});
					var r = confirm("Are you sure you want to cancel product(s) with product code(s):"+favorite.join(", "));
					
						if (r == true) {
								$('#CancelButton').val('1');
								$('#ProductPlacedOrderForm').trigger('submit');
						//$('#ProductPlacedOrderForm').submit();
						} else {
							$('#CancelButton').val('0');
							txt = "You pressed Cancel!";
						}
				});
		
				/*$("#cancel_item").confirm({
					text: "Are you sure you want to delete checked rows?",
					title: "Confirmation required",
					confirm: function(button) {
						
					},
					cancel: function(button) {
						// nothing to do
					},
					confirmButton: "Yes I am",
					cancelButton: "No",
					post: true,
					confirmButtonClass: "btn-danger",
					cancelButtonClass: "btn-default",
					dialogClass: "modal-dialog modal-lg" // Bootstrap classes for large modal
				});*/
  });
	
	function showHiddenElements() {
		$('#ProductPlacedOrderForm').show();
		$('#heighlighted_block').show();
		$('#cancel_item_1').show();
		$('#Dispatch').show();
	}
</script>
<script>
	$(window).focus(function() {
		$('#cancel_item_1').show();
		$('#Dispatch').show();
	});
</script>
<script>
	function update_on_demand_quantities(product_id, kiosk_placed_order_id, quantity){
		targeturl = $("#updateQtyURL").val();
		targeturl += "?product_id="+product_id+"&kiosk_placed_order_id="+kiosk_placed_order_id+"&quantity="+quantity+"&kiosk_id="+<?php echo $kiosk['id'];?>;
		//alert(targeturl);
		$.blockUI({ message: 'Updating Quantity...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				var objArr = $.parseJSON(response);
				$.unblockUI();
			}
		});
	}
</script>
<script>
	function updateStatus(orderId,kioskId){
				targeturl = $("#updateLockURL").val();
				targeturl += "?id="+orderId;
				targeturl += "&kiosk_id="+kioskId;
				$.blockUI({ message: 'Updating Quantity...' });
				$.ajax({
							type: 'get',
							url: targeturl,
							beforeSend: function(xhr) {
								xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
							},
							success: function(response) {
								var objArr = $.parseJSON(response);
								if(objArr.status == "1"){
									var redirect_url = $("#redirect_url").val();
									window.location.href = redirect_url;
								}
								$.unblockUI();
							}
				});
	}
</script>


<script>
<?php
foreach ($product_new_arr as $key => $product){
?>
$("#price_<?php echo $key;?>").blur(function(){
	 var price = $("#price_<?php echo $key;?>").val();
	 var old_price = $("#price_<?php echo $key;?>").attr("old_price");
	 var product_id = <?php echo $product['id'];?>;
	 var targeturl = $("#check_price").val();
	 targeturl += "?id="+product_id;
	 targeturl += "&price="+price;
	 $.blockUI({ message: 'Just a moment...' });
		
	 $.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
			 $.unblockUI();
			  var objArr = $.parseJSON(response);
			  if (objArr.msg == "ok") {
     }else if(objArr.msg == "error"){
						alert("price is less then cost price");
						$("#price_<?php echo $key;?>").val(old_price);
			  }
			},
			error: function(e) {
			 $.unblockUI();
				$.unblockUI();
				alert("An error occurred.");
				console.log(e);
			}
	 });
 });

<?php
}
?> 
</script>
<?php echo '<script type="text/javascript" src="https://'.ADMIN_DOMAIN.'/js/jquery.colorbox.js"></script>';?>
<script>
		$(document).ready(function(){
		<?php echo $groupStr;?>
		});
</script>