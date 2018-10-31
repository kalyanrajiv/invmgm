<?php
$jQueryURL = "https://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
if(defined('URL_SCHEME')){
	$jQueryURL = URL_SCHEME."ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
}
$adminDomainURL = URL_SCHEME.ADMIN_DOMAIN;
?>
<script type="text/javascript" src="<?php echo $jQueryURL;?>"></script>
<?php echo $this->Html->script('jquery.printElement');
echo $this->Html->css('model/style.css');
echo $this->Html->css('model/submodal.css');
 echo $this->Html->script('model/submodalsource.js');
 echo $this->Html->script('model/submodal.js');
 
 
 ?>
<?php //echo $this->Html->link(__('WH 2 Kiosk Trnsient Ordrs'), array('controller' => 'kiosk_orders', 'action' => 'transient_orders')); ?>
 
 <?php
 
use Cake\Utility\Text;
use Cake\Routing\Router;
use Cake\I18n\Time;
echo $this->Html->script('jquery.blockUI');
  
?> 
<div style="margin-left: 287px;">
	<span id='kiosk_name'></span>
</div>
<div class="products index"> 
	<input type='button' id='printSelected' name='print' value='Print Receipt' style='width:200px;' align='center' />
	<?php
	if(!isset($clone_order)){$clone_order=0;}
	if(count($products) > 0 ){
      //pr($products);
	  if(array_key_exists($products[0]['kiosk_order']['kiosk_id'],$kiosks)){
		$kioskName = $kiosks[$products[0]['kiosk_order']['kiosk_id']];
	  }else{
		$kioskName = "--";
	  }
	}else{
		$kioskName = 'Kiosk';
	}
	// pr($kioskOrderStatus);
	if(!empty($allKiosks)){
		$json_kiosk = json_encode($allKiosks);
	}else{
		$json_kiosk = "";
	}
	 if($kioskOrderStatus == '1'){
		
		echo $this->Html->link(__('WH 2 Kiosk Trnsient Ordrs'), array('controller' => 'kiosk_orders', 'action' => 'transient_orders'));
	 }else{
		  echo $this->Html->link(__('WH 2 Kiosk Confmd Orders'), array('controller' => 'kiosk_orders', 'action' => 'confirmed_orders'));
	 }
	 
	?>
	<?php
	 if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
		$this->request->session()->read('Auth.User.group_id') == MANAGERS ||
		$this->request->session()->read('Auth.User.group_id') == inventory_manager
		){
		if($clone_order == 1 && $kioskOrderStatus == 1){ ?>
		<form method='post' action="<?php echo $this->request->webroot; ?>StockTransfer/clone_order" style="display: initial;padding-left: 187px;">
		<?php echo $this->Form->input('kiosk',array('options'=>$allKiosks, 'onchange' => "show_kiosk($json_kiosk)",'multiple'=>true,'label' => false,'div'=>false,'style' => 'width:180px','name' => 'data[kiosk_id]','id' => 'box_element')); //'default'=>$selectedKiosks, ?>
			<?php //echo $this->Form->input(null,array('options' => $kiosks,'label' => false,'div'=>false, 'empty' => 'Select Kiosk', 'style' => 'width:180px', 'id'=> 'kioskid', 'name' => 'data[MobilePurchase][kiosk_id]'));?>
			<input type='hidden' name=order_id value='<?=$products[0]['kiosk_order_id'];?>'>
			&nbsp;<input type='submit' name='clone' value='clone'>
		</form>
		<?php }
	} ?>
     <div id='printDiv'> 
    <strong><?php    
    echo __('<span style="font-size: 20px;color: red;">Transferred Items</span> <span style="font-size: 17px;">(Warehouse to '.ucfirst($kioskName).')</span>'); ?></strong>
	<?php
	$dispatched_qty = $total_req = $count = 0;
	foreach($products as $key1 => $product1){
		$count++;
		$dispatched_qty += $product1['quantity'];
		if((int)$kiosk_placed_order_id){
			if(array_key_exists($product1['product']['id'],$quantityRequestedArr)){
				$total_req += $quantityRequestedArr[$product1['product']['id']];
			}	
		}
	}
	?>
    <?php
        if(count($products) > 0 ){
           
		 if((int)$products[0]['kiosk_order']['kiosk_placed_order_id'] && isset($kiosk_placed_user_id) && array_key_exists($kiosk_placed_user_id[$products[0]['kiosk_order']['kiosk_placed_order_id']],$users)){
			$placedBy =   $users[$kiosk_placed_user_id[$products[0]['kiosk_order']['kiosk_placed_order_id']]];
			$placedOrderId = "(Placed under order id: ".$products[0]['kiosk_order']['kiosk_placed_order_id'].")";
		}else{
			 $placedBy = "--";
			 $placedOrderId = '';
		}
		if((int)$products[0]['kiosk_order']['user_id']){
			if(array_key_exists($products[0]['kiosk_order']['user_id'],$users)){
				$dispatchedBy = $users[$products[0]['kiosk_order']['user_id']];
			}else{
				$dispatchedBy = '--';
			}
		}else{
			$dispatchedBy = '--';
		}
		 $placedOn =  $products[0]['kiosk_order']['created'];
         $dispatchedOn_arr =  $products[0]['kiosk_order']['dispatched_on'];
            if(!empty($dispatchedOn_arr)){
                //$dispatchedOn = $dispatchedOn_arr->i18nFormat(
                //                                    [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                //                            );
				
                $dispatchedOn = date("d-m-y h:i a",strtotime($dispatchedOn_arr)); 
            }else{
                $dispatchedOn = "--";
            }
        
		 if(!empty($merge_data)){
			if($merge_data['merged'] == 1){
						$request_on_txt = " Merged On: ";
						$place_by_txt = "Merged By: ";
			}else{
				$request_on_txt = " Requested On: ";
				$place_by_txt = "Placed By:";
			}
		}else{
			$request_on_txt = " Requested On: ";
				$place_by_txt = "Placed By:";
		}
		
	    if(!empty($requestedTime)){
		$requestedOn = "$request_on_txt <span style='color: blue;'>". $requestedTime."</span>, ";
	    }else{
		$requestedOn = "";
	    }
	    
            echo "<h4>Order Details for dispatch order id: ".$products[0]['kiosk_order_id'].$placedOrderId." [".$requestedOn." $place_by_txt <span style='color: blue;'>".$placedBy."</span>, Dispatched On: <span style='color: blue;'>".$dispatchedOn."</span>, Dispatched By: <span style='color: blue;'>{$dispatchedBy}</span>]</h4>";
	    echo "<h4>Total Product # <span style='color: blue;'>".$count."</span>  Total Dispatched Quantity # <span style='color: blue;'>".$dispatched_qty."</span>  Total Requested Quantity # <span style='color: blue;'>".$total_req."</h4> ";
		
		
		
	    if($products[0]['kiosk_order']['status'] == 2){
		//confirmed order
		$userID = $products[0]['kiosk_order']['received_by'];
		if(!empty($userID)){
		    $username = $users[$userID];
		    $userLink = $this->Html->link($username, array('controller' => 'users', 'action' => 'view', $userID));
		   // $receivedOn = $products[0]['kiosk_order']['received_on'];
			 $receivedOn =  date("d/m/y h:i A",strtotime($products[0]['kiosk_order']['received_on'])); 

		    echo "<h4>Received On: $receivedOn [Received By:{$userLink}]</h4>";
		}
	    }
        }
		if(!empty($merge_data)){
			if($merge_data['merged'] == 1){
				echo "<h4 style='color: red;'>Merged order details:</h4>";
				$data_to_show = unserialize($merge_data['merge_data']);
				echo "<h4 style='color: red;'>";
				$sr_no = 0;
				foreach($data_to_show as $merg_key => $merg_val){
					$sr_no++;
					$text = "";
					$text .= $sr_no." ";
					$text .= " Orig Order ID: ".$merg_val['id']." , ";
					$created_date  = $merg_val['created'];
//					$created = $created_date->i18nFormat(
//                                                    [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
//                                            );
					$created = date("d-m-y h:i a",strtotime($created_date)); 
					$text .= "Placed On: ".$created." , ";
					$text .= "Placed By: ".$users[$merg_val['user_id']];
					if(count($data_to_show) != $sr_no){
						$text .= " , ";
					}
					echo $text;
					echo "</br>";
				}
				echo "</h4>";
			}
		}
		
		echo "<b>Serial number will only be generated for cases : which are in locked state</b>";
    ?>
	
    <table cellpadding="0" cellspacing="0">
    <thead>
        <tr>
			<th>Sr.</br>No.</th>
            <th>Product Code</th>
            <th>Product</th>
            <th>Image</th>
	    <th>Quantity<br/>Requested</th>
	    <?php 
	    if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
	    ?>
            <th>Quantity</br>Dispatched</th>
	    <?php }else{ ?>
	    <th>Quantity</br>Received</th>
	    <?php } ?>
            <th>Sale Price(Inc Vat)</th>
			<th> Kiosk Remarks</th>
	    <th>Remarks</th>
	</tr>
    </thead>
    
    <tbody>        
	<?php
	//pr($products);die;
	$groupStr = "";
	$counter = 0;
	foreach ($products as $key => $product):
	$counter++;
	$groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
	?>
	<?php $currentPageNumber = $this->Paginator->current();?>
	<?php
   
            $truncatedProduct = $product['product']['product'];
                                $truncatedCategory =  Text::truncate(
                                     $truncatedProduct,
                                     50,
                                     [
                                         'ellipsis' => '...',
                                         'exact' => false
                                     ]
                                 );
                
        ?>
	<?php
			//echo $product['Product']['id'];
			
		if(!isset($statusArr))$statusArr = array();
	
		if(array_key_exists($product['product']['id'],$statusArr)){
			if($statusArr[$product['product']['id']] == 1){//replaced
				echo "<tr style='color: darkgreen;'>";
			}elseif($statusArr[$product['product']['id']] == 2){
				echo "<tr style='color: blue;'>";
			}else{
					echo "<tr>";
			}
		}else{
			echo "<tr>";
		}
	?>
	<td><?php if(array_key_exists('sr_no', $product)){echo $product['sr_no'];} ?></td>
	
            <td><?php echo $product['product']['product_code']; ?>&nbsp;</td>
            
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
                    //$imageName =  $product['product']['image'];
																				$imageName = 'thumb_'.$product['product']['image'];
																				$largeImageName = 'vga_'.$product['product']['image'];
                    $absoluteImagePath = $imageDir.$imageName;
																				$LargeimageURL =  $imageURL = "/thumb_no-image.png";
                    if(!empty($imageName)){
                        $imageURL = "{$adminDomainURL}/files/Products/image/".$product['product']['id']."/$imageName"; //rasu
																								$LargeimageURL = "{$siteBaseURL}/files/Products/image/".$product['product']['id']."/"."$largeImageName"; //rasu
                    }
                            
                    echo $this->Html->link(
                                $this->Html->image($imageURL, array('fullBase' =>true,'width' => '100px','height' => '100px')), //rasu
                                $LargeimageURL,
                                array('escapeTitle' => false, 'title' => $product['product']['product'],'class' => "group{$key}")
                               );
                ?>
            </td>
	    <?php if((int)$kiosk_placed_order_id){?>
	    <td><?php if(!empty($product['product']['id']) && array_key_exists($product['product']['id'],$quantityRequestedArr))
	    {
		echo $quantityRequestedArr[$product['product']['id']];
	    }else{
		echo "--";
	    }?>
	    </td>
	    <?php }else{ ?>
	    <td>--</td>
	    <?php } ?>
	    <td><?php echo $product['quantity'];?></td>
            <td><?php echo $CURRENCY_TYPE.$product['sale_price'];   ?></td>
	    <td><?php // pr($kioskOrderProductremarks);
		//echo $kioskOrderProductremarks[$product['Product']['id']];
		
		if(is_array($kioskOrderProductremarks) && array_key_exists($product['product']['id'],$kioskOrderProductremarks) && !empty($kioskOrderProductremarks[$product['product']['id']])){
		  	echo $kioskOrderProductremarks[$product['product']['id']];
		}else{
			echo "--";
		}
			?></td>
		
	    <td><?php echo $product['remarks'];?></td>
		<td>
			<form target="_blank" method="post" action="/products/print_label">
				<input name="_csrfToken" autocomplete="off" value="<?php echo $token = $this->request->getParam('_csrfToken');?>" type="hidden">
						<input type="text" name="print_label_price" value="<?php echo $product['sale_price'];?>" style="width: 29px;" />
						<input type="submit" name="print" value="Print Label" />
						<input type="hidden" name="id" value="<?php echo $product['product']['id'];?>" />
						<input type="hidden" name="selling_price_for_label" value="<?php echo $product['sale_price'];?>" />
					</form>
			</td>
	</tr>
        <?php endforeach;?>
	<?php
		 
		 //$groupStr = "";
		foreach($cancelledProds as $cancelledProd){
			
			// pr($cancelledProd);
			$prID = $cancelledProd['product_id'];
			list($product_code, $productTitle, $productImage) = $infoCancelProds[$prID];
			$imageDir = WWW_ROOT."files".DS.'Product'.DS.'image'.DS.$prID.DS;
			$imageName = 'thumb_'.$productImage;
			$absoluteImagePath = $imageDir.$imageName;
			$LargeimageURL = $imageURL = "/thumb_no-image.png";
			
			if(!empty($imageName)){
				$imageURL = "{$adminDomainURL}/files/product/image/".$prID."/$imageName"; //rasu
				$LargeimageURL = "{$adminDomainURL}/files/product/image/".$prID."/vga_"."$imageName"; //rasu
			}
					$sr_no = $cancelledProd['sr_no'];
			echo "<tr style='color: red;'><td>$sr_no</td><td>$product_code</td><td>$productTitle</td><td>";
			echo $this->Html->link(
						$this->Html->image($imageURL, array('fullBase' => false)), //rasu
						$LargeimageURL,
						array('escapeTitle' => false, 'title' => $productTitle,'class' => "group{$key}")
					   );
			echo "</td><td>".$cancelledProd['quantity']."</td>";
			echo "<td>--</td>";
			echo "<td>--</td>";
			echo "<td>";
			if(empty($cancelledProd['remarks'])){
				echo "--";
			}else{
				echo $cancelledProd['remarks'];
			}
			echo "</td>";
			echo "<td>";
			if(empty($cancelledProd['remarks'])){
				echo "--";
			}else{
				echo $cancelledProd['remarks'];
			}
			echo "</td>"; ?>
			
			<?php echo "</tr>";
		}
	?>
	</table>
	</div>
	 <table> 
	<?php if($kioskOrderStatus==1 && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS ||
	   $kioskOrderStatus==1 && $this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
	?>
	<tr>
	    <td class="actions" style="float: left"><?php echo $this->Html->link(__('Receive Order'), array('controller' => 'kiosk_orders', 'action' => 'receive_order', $products[0]['kiosk_order']['id']),array('id'=>'receive_loading')) ?></td>
	</tr>
	<?php }
	if($kioskOrderStatus==2 && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS ||
	   $kioskOrderStatus==2 && $this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
	?>
	<tr>
	    <td class="actions" style="float: left"><?php echo $this->Html->link(__('Create Dispute'), array('controller' => 'stock_transfer', 'action' => 'create_dispute', $products[0]['kiosk_order']['id']),array('id'=>'dispute_loading')) ?></td>
	</tr>
	<?php } ?>
    
    </table>      
 
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
	$('#receive_loading').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	});
	
	$('#dispute_loading').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	});
</script>
<script>
	function show_kiosk(kiosks) {
					$('#kiosk_name').html("<span style='color:red'>No Kiosk Choosen</span>");
					var box_val = $('#box_element').val();
					var show  = [];
					for (var i = 0; i < box_val.length;i++) {
							show.push(toTitleCase(kiosks[box_val[i]]));
					}
					var final = show.join(", ");
					if ($.trim(final) != "") {
								$('#kiosk_name').html("<b>Kiosk choosen are:</b><br/>"+final);
					}else{
							$('#kiosk_name').html("<span style='color:red'>No Kiosk Choosen</span>");
					}
  }
	function toTitleCase(str) {
		return str.replace(/(?:^|\s)\w/g, function(match) {
			return match.toUpperCase();
		});
	}
</script>
<?php echo '<script type="text/javascript" src="https://'.ADMIN_DOMAIN.'/js/jquery.colorbox.js"></script>';?>
<script>
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>