
<?php //echo $this->Html->script('jquery.printElement');
echo $this->Html->script('jquery.blockUI');
?>
 <?php
//echo'hello';die;
    use Cake\Core\Configure;
    use Cake\Core\Configure\Engine\PhpConfig;
	$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
     
?>
<div class="products index"> 
	 
	<?php
	 
        $path = dirname(__FILE__);
		$isboloRam = strpos($path,"boloram");
		$kiosk_ids = explode(",",$str_kiosk_id);
		$show_str = array();
		foreach($kiosk_ids as $k => $val){
		   $show_str[] = $kiosks[$val]; 
		}
		if(!empty($show_str)){
			$show = implode(",",$show_str);
		}
	?>
	
     <div id='printDiv'> 
    <strong><?php  //pr($kioskOrderStatus);
    echo __('<span style="font-size: 20px;color: red;">Transferred Items</span> <span style="font-size: 17px;">(Warehouse to '.$show.')</span>'); //ucfirst($kioskName) ?></strong>
    <?php  
       
    ?>
    <form action="<?php echo $this->request->webroot; ?>StockTransfer/place_order" method="post">
	<?php ?>
	<input type='submit' name="submit1" onclick="loading_msg()" value="Place Order" style="width: 113px;" />
	<?php 	 ?>
    <table cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th>Product Code</th>
            <th>Product</th>
            <th>Image</th>
			<th>Sale Price</th>
	    <th>Qty<br/>Requested</th>
		<th>Waheguru</br>Qty</th>
            <?php if($isboloRam != false){ ?>
			<th>Boloram </br> Qty</th>
			<?php  }?>
	</tr>
    </thead>
    
    <tbody>
		
	<?php    //pr($result);
	foreach ($result as $key => $product){?>
	<?php $currentPageNumber = $this->Paginator->current();
			$product_id = $product['product']['id'];
           	$product_code = $product['product']['product_code'];
              $imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product_id.DS;
			//$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product_id.DS;
			  $imageName =  $product['product']['image'];
			  $absoluteImagePath = $imageDir.$imageName;
			$imageURL = "/thumb_no-image.png";
			if(@readlink($absoluteImagePath) ||file_exists($absoluteImagePath)){
				$imageURL = "$siteBaseURL/files/Products/image/".$product['product']['id']."/$imageName";
			}
	?>
		<tr>
			<input type='hidden' name='order_id' value='<?=$order_id?>' />
			<input type='hidden' name='kiosk_id' value='<?=$str_kiosk_id?>' />
			<td><?=$product_code;?></td>
			<td><?=$product['product']['product'];?></td>
			<td><?=$image =  $this->Html->image($imageURL, array('fullBase' => true,'escapeTitle' => false, 'title' => $product_id,'height' => '100px','width' => '100px')
           //                                     array('height' => '100px','width' => '100px')
                                                );?></td>
			<td><?=$CURRENCY_TYPE.$product['product']['selling_price'];?></td>
			<td> <input type = 'text' name = 'placedorder[<?=$product_id;?>]' onchange="check_quantity(<?=$product_id;?>)" id ='pro_<?=$product_id;?>' value = '<?=$qantity_requested[$product_id];?>' style='width: 50px;'/></td>
			 
			<input type = 'hidden' name = 'hidden_quantity[<?php echo $product_id;?>]' id ='hidd_pro_<?php echo $product_id;?>' value = '<?php echo $avalable_qantity[$product_id];?>'/>
			<td><?=$avalable_qantity[$product_id];?></td>
			 <?php if($isboloRam != false){ ?>
			<td><?php echo $boloram_qantity[$product_id];?></td>
			<?php  }?>
		</tr>
        <?php }?>
		</form>
	</table>
	</div>
	
</div>
<div class="actions" align = 'left'>
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
	function check_quantity(product) {
        var actualQty = parseInt($("#hidd_pro_"+product).val());
		var req_quantity = parseInt($("#pro_"+product).val());
		if (actualQty < req_quantity) {
			//alert(org_qantity);
            $("#pro_"+product).val(actualQty);
			alert("requested_quantity ("+req_quantity+") is more then actual quantity ("+actualQty+")");
        }
    }
	function loading_msg(){
        $.blockUI({ message: 'Just a moment...' });
    }
</script>



