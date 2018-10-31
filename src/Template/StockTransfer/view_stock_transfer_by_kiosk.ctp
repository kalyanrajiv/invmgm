<?php
		use Cake\Core\Configure;
		use Cake\Core\Configure\Engine\PhpConfig;
		$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
		$currency = Configure::read('CURRENCY_TYPE');
		$jQueryURL = "http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
		if(defined('URL_SCHEME')){
		$jQueryURL = URL_SCHEME."ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
		}
		$adminDomainURL = URL_SCHEME.ADMIN_DOMAIN;
?>
<script type="text/javascript" src="<?php echo $jQueryURL;?>"></script>
<?php
		echo $this->Html->script('jquery.blockUI');
		echo $this->Html->script('jquery.printElement');
		echo $this->Html->css('model/style.css');
		echo $this->Html->css('model/submodal.css');
		echo $this->Html->script('model/submodalsource.js');
		echo $this->Html->script('model/submodal.js');
		$url = $this->Url->build(['controller' => 'stockTransfer', 'action' => 'order_qty_update'],true);
?>
<input type="hidden" value="<?=$url;?>" name="url" id="update_qty_url" />
<div class="products index">
	<input type='button' id='printSelected' name='print' value='Print Receipt' style='width:200px;' align='center' />
	<?php
			if(count($products) >=0 ){
				$kioskNme = $kiosks[$products[0]['center_order']['kiosk_id']];
			}else{
				$kioskNme = 'Kiosk';
			}
			if($kioskOrderStatus == '1'){
				echo $this->Html->link(__('Kiosk to WH Trnsient'), array('controller' => 'kiosk_orders', 'action' => 'transient_kiosk_orders'));
			}else{
				echo $this->Html->link(__('Kiosk to WH Confirmed'), array('controller' => 'kiosk_orders', 'action' => 'confirmed_kiosk_orders'));
			}
			echo "<br/>";
   $dispatched_qty = $total_req = $count = 0;
			
			foreach($products as $key1 => $product1){
					//pr($product1);die;
					$count++;
					$dispatched_qty += $product1['quantity'];
					//if((int)$kiosk_placed_order_id){
					//if(array_key_exists($product1['Product']['id'],$quantityRequestedArr)){
					//	$total_req += $quantityRequestedArr[$product1['Product']['id']];
					//}	
					//}
			}
?>
	<div id='printDiv'>
		<strong><?php #pr($products);die;
    echo __('<span style="font-size: 20px;color: red;">Transferred Items</span> <span style="font-size: 17px;">('.ucfirst($kioskNme).' to Warehouse)</span>'); ?></strong>
			    
<?php 
			if(count($products) >=0 ){
					$kioskName = $kiosks[$products[0]['center_order']['kiosk_id']];
							$dispatchedOn = $this->Time->format('jS M, Y h:i A', $products[0]['center_order']['dispatched_on'],null,null); 
							echo "<h4>Order Details for order id: ".$products[0]['kiosk_order_id']." [Dispatch On: ".$dispatchedOn." by ".ucfirst($kioskName)."]</h4>";
			}
		echo "<h4>Total Product # <span style='color: blue;'>".$count."</span>  Total Dispatched Quantity # <span style='color: blue;'>".$dispatched_qty."</span>"; 
	if($products[0]['center_order']['status'] == 2){
	    //confirmed order
	    $userID = $products[0]['center_order']['received_by'];
        //pr($users);die;
	    if(!empty($userID)){
		$username = $users[$userID];
		$userLink = $this->Html->link($username, array('controller' => 'users', 'action' => 'view', $userID));
		$receivedOn =  date("d/m/y h:i A",strtotime($products[0]['center_order']['received_on'])); 
		echo "<h4>Received On: $receivedOn [Received By:{$userLink}]</h4>";
	    }
	}
    ?>
    
    <table cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th>Product Code</th>
            <th>Product</th>
            <th>Image</th>
            <th>Quantity</th>
            <th>Sale Price</th>
			<?php
				if($products[0]['center_order']['status'] == 1){
		?>
			<th>Actions</th>
			<?php }
			?>
	</tr>
    </thead>
    
    <tbody>        
	<?php
	$counter = 0;$groupStr = "";
	foreach ($products as $key => $product): 
		$currentPageNumber = $this->Paginator->current();
		$counter++;
		$groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
		
		$row_id = $product['id'];
		$truncatedProduct = \Cake\Utility\Text::truncate(
							 $product['product']['product'],
							 50,
							 [
								  'ellipsis' => '...',
								  'exact' => false
							 ]
				  );
	?>
	
	<tr>
            <td><?php echo $product['product']['product_code'];
			$p_id = $product['product']['id'];
			?>&nbsp;
			<input type="hidden" id="product_id_<?php echo $row_id; ?>" value="<?php echo $p_id; ?>" />
			
			</td>
            
            <td><?php 
                    echo $this->Html->link(
                                            $truncatedProduct,
                                            array('controller' => 'products', 'action' => 'view', $product['product']['id']),
                                            array('escapeTitle' => false, 'title' => $product['product']['product'])
                                            ); ?>&nbsp;
            </td>
                
            <td>
                <?php                    
							$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product['product']['id'].DS;
							$imageName = $product['product']['image'];
							$absoluteImagePath = $imageDir.$imageName;
							$largeimageURL = $imageURL = "/thumb_no-image.png";
							if(!empty($imageName)){
								$imageURL = "$adminDomainURL/files/Products/image/".$product['product']['id']."/thumb_".$imageName;
								$largeimageURL = "$adminDomainURL/files/Products/image/".$product['product']['id']."/vga_".$imageName;
							}
								
							echo $this->Html->link(
															$this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')),
															$largeimageURL,
															array('escapeTitle' => false, 'title' => $product['product']['product'],'class' => "group{$key}")
														);
                ?>
            </td>
            <td><?php
														$totalquantity = $product['quantity'];
														$options = array();
														for($i= 0; $i <= $totalquantity; $i++){
															$options[$i] = $i;
														}
						
														echo $this->Form->input("selected_qty_{$row_id}", array('options' => $options,
																	 'default' => $totalquantity,
																	 'label' => false,
																	 'name' => "selectedQty",
																	 'id' => "quantity_number_{$row_id}"
																	 ));
			
												?></td>
            <td><?php echo $currency.$product['sale_price'];
			 ?></td>
			<td>
				<?php
				if($products[0]['center_order']['status'] == 1){
		?>
				<input type="button" id="update_qty" onclick='update_qty(<?php echo $row_id; ?>);' value="Update Qty" />
				<?php } ?>
			</td>
	</tr>
        <?php endforeach; ?>
	</tbody>
	</table>
	 </div>
	 <table> 
	<tr>
	    <?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
		if($products[0]['center_order']['status'] == 1){
		?>
	    <td class="actions" style="float: left"><?php echo $this->Html->link(__('Receive Order'), array('controller' => 'kiosk_orders', 'action' => 'receive_kiosk_order', $products[0]['center_order']['id'])) ?></td>
	    <?php }
	    }?>
	</tr>
    
    </table>      
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
<script>
	//$("#update_qty").on("click",function(){
	//	alert("hi");
	//	});
	
	function update_qty(id) {
		if (confirm("Are You sure you want to update?")) {
            var qty = $("#quantity_number_"+id).val();
			var product_id = $("#product_id_"+id).val();
			var row_id = id;
			var target_url = $("#update_qty_url").val();
			
			target_url += "?qty="+qty;
			target_url += "&product_id="+product_id;
			target_url += "&row_id="+row_id;
			
			$.blockUI({ message: 'Updating Quantity...' });
			$.ajax({
					type: 'get',
					url: target_url,
					beforeSend: function(xhr) {
						xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
					},
					success: function(response) {
						var objArr = $.parseJSON(response);
						if (objArr.msg == "error") {
							alert("Error");
						//	$.unblockUI();
						}else{
							alert(objArr.msg);
							location.reload();
						//	$.unblockUI();
						}
					},
					error: function(e) {
					//	$.unblockUI();
						alert("An error occurred: " + e.responseText.message);
						console.log(e);
					}
		
			});
        }
    }
</script>
<?php echo '<script type="text/javascript" src="https://'.ADMIN_DOMAIN.'/js/jquery.colorbox.js"></script>';?>
<script>
		$(document).ready(function(){
		<?php echo $groupStr;?>
		});
</script>