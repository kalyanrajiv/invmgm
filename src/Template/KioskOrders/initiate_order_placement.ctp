<div class="brands index">
<?php
	
	//$www_root = '/var/www/vhosts/'.ADMIN_DOMAIN.'/httpdocs/app/webroot/';
?>
<div style="margin-right: 72px;">
	<h2> <?php echo __('Phones Sold Today'); ?></h2>
	<table cellpadding="0" cellspacing="0">
		<tr><td colspan='8'><hr></td></tr>
        <tr>
			<th>Brand</th>
			<th>Model</th>
			<th>Placed Qty</th>
        </tr>
		<?php
 // pr($soldPhones);//die;
			if(!empty($soldPhones)){
				foreach($soldPhones as $p_key => $p_value){
					$brandid = $p_value['brand_id'];
					$modelid = $p_value['mobile_model_id'];
					$qntity_sold = $p_value['total'];?>
					<tr>
						<td><?=$brand[$brandid]?></td>
						<td><?=$models[$modelid]?></td>
						<td><?=$qntity_sold?></td>
					</tr>
				<?php }
				
			}else{
				echo "No Phone sold today";
			}
		?>
	</table>
	
</div>
	<h2> <?php echo __('Place Order'); ?></h2>
	<?php
		   $webRoot = $this->request->webroot."KioskOrders/place_order"; 
        echo $this->Form->create('placeorder',['type' => 'post']);
		//echo $this->Form->create('placeorder',array( 'type'=>'post'));
	?> 	 
	 
	<table cellpadding="0" cellspacing="0">
		<tr><td colspan='8'><hr></td></tr>
        <tr>
			<th>Product Code</th>
			<th>Product Title</th>
            <th>Image</th>
			<th>Placed Qty</th>
			<th>Current Qty</th>	
            <th>Qty Req</th>			
			<th>Remarks</th>
        </tr>
        
		<?php
			$itemSold = 0;
			
			//   pr($products); 
			 //pr($product_quantities);
			foreach($products as $key =>  $product){
				$product_id = $product['id'];
				$product_title = $product['product'];
				$product_code = $product['product_code'];
				$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product_id.DS;
				$imageName = $product['image'];
				$absoluteImagePath = $imageDir.$imageName;
				$imageURL = "/thumb_no-image.png";
				if(@readlink($absoluteImagePath) || file_exists($absoluteImagePath)){
					$imageURL = "{$siteBaseURL}/files/Products/image/".$product['id']."/$imageName";
				}
				$product_currentquantity = $product['quantity']; 
				$product_soldquantity = $product_quantities[$product_id];
				$placedQty = 0;$placedRemarks = "";
				if(count($todaysPlaced) >= 1){
					if(array_key_exists($product_id, $todaysPlaced))
						list($placedQty,$placedRemarks) = $todaysPlaced[$product_id];
				}
				$originalSold = $origQtys[$product_id];
				//---------------------------------------------
				//$product_soldquantity;
				//pr($product);die;
				//echo $product['Product']['id'];
				// pr($returnedProductsArr);
                
                
				if(count($returnedProductsArr) && array_key_exists($product['id'],$returnedProductsArr)){
					//echo $product['Product']['id'];
					$product_soldquantity = $product_soldquantity - $returnedProductsArr[$product['id']]['total_returned_quantity'];
					if($product_soldquantity >0){
						echo "<input type = 'hidden' name = 'placedorder_hidden[$product_id]' id ='data[placedorder][$product_id]' value = '$product_soldquantity'/>";
					}
					if($product_soldquantity <= 0){
						
						$product_soldquantity = 0;
					}
				  }
                
				//echo $product_soldquantity;die;
				//---------------------------------------------
				if($product_soldquantity <= 0)
					continue;
				else
					$itemSold++;
				
        ?>
		<tr>
			<td><?php echo $product_code;?></td>
			<td><?php echo $product_title;?></td>
			<td><?php echo $image =  $this->Html->image($imageURL, array('fullBase' => true,'escapeTitle' => false,'style' => 'width:80px;height:80px;', 'title' => $product_id));?></td>
			<td><?=$placedQty?></td>
			<td><?php echo $product_currentquantity;?></td>
		 
		 
			  <?php
			  //added on 07.03.2016 for subtracting returned quantity for the products sold other than today
			  //till here
			  //*/
			  
			 if(is_array($placedproduct_quantities) && array_key_exists($product_id,$placedproduct_quantities)){
				if($placedproduct_quantities[$product_id] != 0){
					$productQuantity = $placedproduct_quantities[$product_id] + $product_soldquantity;
				}else{
					$productQuantity = $product_soldquantity;
				}
				$difference = $placedproduct_quantities[$product_id];
			  }else{
				$productQuantity = $product_soldquantity;
				$difference = 0;
			  }
				echo "<td><input type = 'text' name = 'placedorder[$product_id]' id ='data[placedorder][$product_id]' value = '$productQuantity' style='width: 50px;'/></td> ";
			  ?>
			 
			<td><textarea name = "remarks[<?=$product_id?>]" placeholder = "remarks" rows=='5' cols='30'><?php echo $placedRemarks;?></textarea></td>
			
	    </tr>
		<tr><td colspan='7'><span style='color: blue'>Total Items sold since morning: <?=$originalSold?>, Difference: <?=$difference;?></span> </td></tr>
        <?php }?>
		<?php
		//pr($products);
		if($itemSold == 0){?>
		<tr><td colspan='7'><span style='color: blue'>No items sold after your last placed order</span></td></tr>
		<?php }else {?>
        <tr><td><input type = "submit" name = "update_quantity" value = "Place Order"/></td></tr>
		<?php }?>
	</table>
	<?php echo $this->Form->end();	?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
		<?php echo $this->element('sidebar/kiosk_order_menus');?>
	 
</div>
 

<script>
	$("input[id*='[placedorder]']").keydown(function (event) {
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||
		event.keyCode == 46  || event.keyCode == 183 ) {
			;
			//48-57 => 0..9
			//8 => Backspace; 9 => tab 
		} else {
			event.preventDefault();
		}
		
        });
</script>
<script>
	$('input[name = "update_quantity"]').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	});
</script>