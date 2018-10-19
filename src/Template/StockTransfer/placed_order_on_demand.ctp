<?php 
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$currency = Configure::read('CURRENCY_TYPE');
$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
$jQueryURL = "http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
if(defined('URL_SCHEME')){
	$jQueryURL = URL_SCHEME."ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
}
?>
<script type="text/javascript" src="<?php echo $jQueryURL;?>"></script>
<?php
	echo $this->Html->script('jquery.printElement');
	echo $this->Html->script('jquery.blockUI');
	
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
	$path = dirname(__FILE__);
	$isboloRam = strpos($path,"mbwaheguru");
	
	$kioskPlacedOrderId = $this->request->params['pass']['0'];
	if(array_key_exists($placedby,$users)){
		$placedbyName = $users[$placedby];
	}else{
		$placedbyName = '--';
	}
	$style = "'width: 136px;float: right;margin-right: 402px;'";
     if(!isset($forprint)){
		$forprint = "Yes";
	 }
	
?>
<div class="centralStocks index">	
    <?php $lock = $on_demand_orders_data['lock_status'];
	if($lock == 1){ ?>
		<input type='button' id='printSelected' name='print' value='Print Receipt' style='width:200px;' align='center' />	
	<?php }
	?>
    <h4 style="background-color: yellow;width: 522px;font-size: 16px;height: 29px;font-weight: bold;">(Print function would be available only for locked orders)</h4>
    <?php echo $this->Html->link(__(' Kiosk Placed Order'), array('controller' => 'kiosk_orders', 'action' => 'placed_orders'));?>
	
	<input type = "button" onclick="JavaScript: showHiddenElements();" value = "Reset" style=<?=$style;?>>
	<?php $kiosk_id = $kiosk['id'];
	$lock = $on_demand_orders_data['lock_status'];
	if($lock == 0 && $this->request->session()->read('Auth.User.group_id') != KIOSK_USERS){
	?>
	<input type = "button" onclick="JavaScript: updateStatus(<?php echo $kioskPlacedOrderId?>,<?php echo $kiosk_id?>);" value = "Lock" style=<?=$style;?>>
	<?php }?>
	
	
	<div id = 'printit'>
		<div id='printDiv'>
			<h2><?php echo __("Extra Stock Transfer to{$kiosk['name']}"); ?></h2>
	
			<form action='<?php echo $this->request->webroot;?>stock-transfer/onDemand_forPrint/<?php echo $kioskPlacedOrderId;?>' method = 'POST' id = "ProductPlacedOrderForm">Show Pictures?<br/>
				<input type = "radio" name = "forprint" value = 'Yes' <?php if($forprint=="Yes"){echo "checked";}?>/>&nbsp;Yes &nbsp; <br/><br/>
				<input type = "radio" name = "forprint" value = 'No' <?php if($forprint=="no"){echo "checked";}?>/> &nbsp;No &nbsp; 
				<button type="submit" form="ProductPlacedOrderForm" value="Submit">Submit</button>
			</form>
		<?php
			$count = 0 ;
			// pr($products);
			$sold_qty = 0;
			$quantity_dispatch = 0;
			$request_qty = 0;
			foreach ($product_new_arr as $key => $product){
				$count++;
				$product_id = $product['id'];
				$quantity_dispatch+= $productArr[$product_id];
				$request_qty+= $productArr[(int)$product['id']];
				
			}
		?>
		<?php
		
			echo "<h4>Order # <span style='color: blue;'>".$kioskPlacedOrderId."</span> placed on <span style='color: blue;'>".$modified."</span> Placed By:- <span style='color: blue;'>".$placedbyName."</span></h4>";
			echo "<br/>";
			echo "<h4>Total Product # <span style='color: blue;'>".$count."</span>  Total Sold Quantity # <span style='color: blue;'>".$quantity_dispatch."</span>  Total Requested Quantity # <span style='color: blue;'>".$request_qty."</h4> ";
			if($lock == 1){
				echo "<h4 style='color: red;background-color: yellow;width: 187px; font-weight: bold;'>This order is in locked state</h4>";
			}
			echo $this->Form->create(null,array(
									'url' => array('controller' => 'stock_transfer','action' => 'placed_order_on_demand',$kioskPlacedOrderId),
									'id' => 'ProductPlacedOrderForm',
									'onSubmit' => 'return validateForm();',
									)); ?>
			
			
			<table cellpadding="0" cellspacing="0">	
				<tr><td colspan='13'><hr></td></tr>
				<tr>
					
					<th>Sr.No</th>
					<th>Delete</th>
					<th>Prod<br/>Code</th>
					<th>Prod<br/>title</th>
					<th>Category</th>
					<th>colour</th>
					<?php if($forprint == 'Yes' ){echo "<th>Image</th>";}?>
					<th>Curr<br/>Stk</th>
					
					<th width='40' style='width: 40px;'>Qty</th>
					<th width='55' style='width: 55px;'>Qty<br/>Rqstd</th>
					<th width='65' style='width: 65px;'>Remarks</th>
					<th>Replace/Add</th>
				</tr>	
				<tbody>
	<?php $productRemarks = "";
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
			if($key == 4201){
				echo "1";
			}
			$quantity2dispatch = $product['quantity'];
			$productRemarks = "";
		}elseif($product['quantity'] == 0){
			if($key == 4201){
				echo "2";
			}
			$quantity2dispatch = 0;
			$productRemarks = "Out of stock";
		}elseif($product['quantity'] > $productArr[$productID]){
			if($key == 4201){
				echo "3";
			}
			$quantity2dispatch = $productArr[$productID];
			$productRemarks = "";
		}else{
			$quantity2dispatch = $product['quantity'];
		}
		
		$userRemarks = $remarksArr[$productID];
		$truncatedProduct =  \Cake\Utility\Text::truncate(
															$product['product'],
															30,
															[
																	'ellipsis' => '...',
																	'exact' => false
															]
													);
		
		$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product['id'].DS;
		$imageName = $product['image'];
		$absoluteImagePath = $imageDir.$imageName;
		$imageURL = "/thumb_no-image.png";
		if(@readlink($absoluteImagePath) ||file_exists($absoluteImagePath)){
			$imageURL = "{$siteBaseURL}/files/Products/image/".$product['id']."/thumb_".$imageName;
			$largeimageURL = "{$siteBaseURL}/files/Products/image/".$product['id']."/vga_"."$imageName"; //rasu
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
	
	<input type="hidden" name="KioskStock[kiosk_product_order_id][<?php echo $key;?>]" value="<?php echo $product_sr_no[$product['id']];?>" />
	<td><?php
	if($lock == 1){
		echo $product_sr_no[$product['id']];	
	}else{
			echo $counter;
	}
	?></td>
		<td>
	<?php  if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == inventory_manager){ ?>
		<input type='checkbox' name='cancelled_items[]' data="<?php echo $product['product_code'];?>" value='<?php echo $product['id'];?>' 
		<?php }else{
			if($lock == 0){
			?>
		<input type='checkbox' name='cancelled_items[]' data="<?php echo $product['product_code'];?>" value='<?php echo $product['id'];?>' />
		<?php }}?>
		</td>
		<td>
		<?php echo $product['product_code']; ?>
	    </td>
		<td style="width: 96px;">
		<?php
			echo $product['product'];
			//$this->Html->link($truncatedProduct,
				   //         array('controller' => 'products', 'action' => 'view', $product['Product']['id']),
					 //       array('escapeTitle' => false, 'title' => $product['Product']['product'])
					//);
			?>
		</td>
	    
		<td style="width: 82px;">
				<?=$categoryList[$product['category_id']];?>
		</td>
		<td style="width: 56px;">
			<?php echo $product["color"];?>
		</td>
		<?php if($forprint == "Yes"){?>
		<td><?php
			echo $this->Html->link(
							$this->Html->image($imageURL, array('fullBase' => true,'height'=>'64px','width'=>'64px')),
							$largeimageURL,
							array('escapeTitle' => false, 'title' => $product['product'], 'class' => "group{$key}")
					);
			?>
		</td>
		<?php } ?>
		<td><?php echo h($product['quantity']); ?>&nbsp;</td>
		
		
		<?php
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
				//if($isboloRam != false){
				//	if(isset($bolram_products) && !empty($bolram_products)){
				//		if(array_key_exists($product['Product']['id'],$bolram_products)){
				//			$conditional_s_price = $bolram_products[$product['Product']['id']];
				//		}else{
				//			$conditional_s_price = $productPrice;
				//		}
				//	}else{
				//		$conditional_s_price = $productPrice;
				//	}
				//	echo $this->Form->input(null,array(
				//				'type' => 'hidden',
				//				'name' => "data[KioskStock][price][$key]",
				//				'value' => $conditional_s_price,
				//				'label' => false,
				//				'style' => 'width:40px;'
				//				)
				//		);
				//}else{
					echo $this->Form->input(null,array(
								'type' => 'hidden',
								'name' => "KioskStock[price][$key]",
								'value' => $productPrice,
								'label' => false,
								'style' => 'width:40px;'
								)
						);
				//}
				
				echo $this->Form->input(null,array(
								'type' => 'hidden',
								'name' => "KioskStock[product_id][$key]",
								'value' => $product['id']
							 )
						);
				?>
		
		
        <td><?php  if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == inventory_manager){ ?>
		<?php
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
        ?>
		<?php }else{
			if($lock == 0){
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
		
		   }}?>
            </td>
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
					<?php  if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == inventory_manager){ ?>											
					<input type='radio' id= 'replace_product_<?php echo $product['id'];?>' name='replace_product' value = '<?php echo $product['id'];?>' onClick = 'updateHidden(<?php echo $product['id'];?>)' />
					<?php }else{
						if($lock == 0){
						?>
					<input type='radio' id= 'replace_product_<?php echo $product['id'];?>' name='replace_product' value = '<?php echo $product['id'];?>' onClick = 'updateHidden(<?php echo $product['id'];?>)' />
					<?php }}?>
			</td>
	</tr>
	<?php 
	echo $this->Form->input('null',array(
                                    'type' => 'hidden',
                                    'name' => "KioskStock[kiosk_user_remarks][$key]",
                                    'value' => $userRemarks
                                    )
                            );
	if(!empty($userRemarks)){?>
		<tr><td colspan='8'><strong>Kiosk user remarks:</strong><br/><span style="background-color: yellow"><?=$userRemarks;?></span></td></tr>
	<?php } ?>
	
        <?php endforeach; ?>
		<?php
		//pr($cancelProducts);
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
					$imageURL = "/files/Products/image/".$cancelProduct['id']."/$imageName";
				}
				?>
				<tr style="color: red;">
					<td><?php echo $canSrNoArr[$cancelProduct['id']]; ?></td>
					<td><?php echo $cancelProduct['product_code']; ?></td>
					<td>
						<?php  echo $cancelProduct['product'];  ?>
					</td>
					
					<td><?php echo $cancelCategoryList[$cancelProduct['category_id']]; ?></td>
					<td><?php echo $cancelProduct["color"];?></td>
					
						<?php
                      	if($forprint == "Yes"){
							echo "<td>";
                           
							echo $this->Html->link(
                                 $this->Html->image($imageURL, array('fullBase'=>false,'style'=>" width: 100px;height: 100px;")),
											array('controller' => 'products','action' => 'edit', $cancelProduct['id']),
											array('escapeTitle' => false, 'title' => $cancelProduct['product'])
									);
							echo "</td>";
						}
						?>
						
					
					<td><?php echo h($cancelProduct['quantity']); ?>&nbsp;</td>
					
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
		
		<table style="width: 20px;"><tr><td>
		<?php  if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == inventory_manager){ ?>
			<input type="submit" value="Cancel Item" id='cancel_item_1' style='width:100px;' onSubmit="return validateForm();">
		<?php }else{
			if($lock == 0){ ?>
			<input type="submit" value="Cancel Item" id='cancel_item_1' style='width:100px;' onSubmit="return validateForm();">
		<?php }
		}?>
		</td>
		
		<?php if( $this->request->session()->read('Auth.User.group_id') != MANAGERS || $this->request->session()->read('Auth.User.group_id') != ADMINISTRATORS|| $this->request->session()->read('Auth.User.group_id') != inventory_manager){
			echo $this->Form->end();
		} ?>
	<?php
		echo $this->Form->input(null,array(
							'type' => 'hidden',
							'name' => "KioskStock[kiosk_id]",
							'value' => $kiosk['id'],
							'label' => false,
							'style' => 'width:80px;'
					));
		$options1 = array('label' => 'Dispatch','div' => false,'id' => 'Dispatch','name' => 'Dispatch');
		$option2 = array('label' => 'Cancel','div' => false,'id' => 'Dispatch');
		if( $this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER|| $this->request->session()->read('Auth.User.group_id') == inventory_manager){ ?>
			<td>
			<?php echo $this->Form->submit("Dispatch",$options1); ?>
			
			</td>
			<?php 
			echo $this->Form->end();
		}else{?>
            <input type="image" src="http://hpwaheguru.co.uk/img/transparent.png" id='Dispatch' name="Dispatch" style="width: 1px;height: 1px;"/>
      <?php  }
	?>
	</tr>
		 </table>
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
	<?php  if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
					$this->request->session()->read('Auth.User.group_id') == MANAGERS ||
					$this->request->session()->read('Auth.User.group_id') == inventory_manager){
					echo $this->element('sidebar/order_menus');
				}else{
					echo $this->element('sidebar/kiosk_order_menus');
		}?>
	
</div>
<?php
	$update_type = $this->Url->build(['controller' => 'StockTransfer', 'action' => 'updatedOnDemandOrderQty'],true);
	echo "<input type='hidden' id='updateQtyURL' value='$update_type'/>";
	
	$update_type1 = $this->Url->build(['controller' => 'StockTransfer', 'action' => 'updateLock'],true);
	echo "<input type='hidden' id='updateLockURL' value='$update_type1'/>";
	
	$update_type2 = $this->Url->build(['controller' => 'StockTransfer', 'action' => 'placedOrderOnDemand',$kioskPlacedOrderId],true);
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
	
	function update_on_demand_quantities(product_id, kiosk_placed_order_id, quantity){
		//alert(product_id);
		//alert(kiosk_placed_order_id);
		//alert(quantity);
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
				if(objArr.status == "1"){
					;//alert("success");
				}else{
					;//alert(objArr.status);
				}
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
				}else{
					;//alert(objArr.status);
				}
				$.unblockUI();
			}
		});
	}
</script>
<?php echo '<script type="text/javascript" src="https://'.ADMIN_DOMAIN.'/js/jquery.colorbox.js"></script>';?>
<script>
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>