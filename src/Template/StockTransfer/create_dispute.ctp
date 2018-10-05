<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
	//$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
	//pr($products);
?>
<div class="products index">	
    <strong><?php #pr($products);die;
    echo __('<span style="font-size: 20px;color: red;">Transferred Items</span> <span style="font-size: 17px;">(Warehouse to Kiosk)</span>'); ?></strong>
    <?php
		$count = 0 ;
		  //pr($disputed_data);
		$quantity = 0;
		//pr($products);
		foreach ($products as $key => $product){
			$count++;
			$product_id = $product['product']['id'];
			$quantity+=  $product['quantity'];
					
		}
        if(count($products) >=0 ){
           // pr($products);
            $dispatchedOn = $products[0]['kiosk_order']['dispatched_on'];
             if(!empty($dispatchedOn)){
                  $dispatchedOn->i18nFormat(
                                                    [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                            );
							$dispatchedOn_date =  $dispatchedOn->i18nFormat('dd-MM-yyyy HH:mm:ss');
                $dispatchedOn_date = date("d-m-y h:i a",strtotime($dispatchedOn_date)); 
            }else{
                $receivedOn_date = "--";
            }
            echo "<h4>Order Details for order id: ".$products[0]['kiosk_order_id']." [Dispatch On: ".$dispatchedOn_date."]</h4>";
        }
	if($products[0]['kiosk_order']['status'] == 2){
	    //confirmed order
	    $userID = $products[0]['kiosk_order']['received_by'];
	    if(!empty($userID)){
			$username = $users[$userID];
			$userLink = $this->Html->link($username, array('controller' => 'users', 'action' => 'view', $userID));
			$receivedOn = $products[0]['kiosk_order']['received_on'];
             if(!empty($receivedOn)){
                  $receivedOn->i18nFormat(
                                                    [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                            );
				$receivedOn_date =  $receivedOn->i18nFormat('dd-MM-yyyy HH:mm:ss');
                $receivedOn_date = date("d-m-y h:i a",strtotime($receivedOn_date)); 
            }else{
                $receivedOn_date = "--";
            }
            
			echo "<h4>Received On: $receivedOn_date [Received By:{$userLink}]</h4>";
	    }
	}
	echo "<h4>Total Product # <span style='color: blue;'>".$count."</span>  Total  Quantity # <span style='color: blue;'>".$quantity."</span></h4> ";
	
    ?>
    <?php echo $this->Form->create()?>
    <table cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th>id</th>
			<th>Product code</th>
            <th>Product</th>
            <th>Image</th>
            <th>Quantity</th>
            <th>Sale Price</th>
	    <th>Received More/Less</th>
		<th>Quantity</br>Received</th>
	    <th>Difference<sup style="color: red;">*</sup></th>
	    <th>Remarks<sup style="color: red;">*</sup></th>
        </tr>
    </thead>
    
    <tbody>        
	<?php
   // pr($disputed_data);
    foreach ($products as $key => $product):?>
	<?php $currentPageNumber = $this->Paginator->current();?>
	<?php
                $truncatedProduct =
				\Cake\Utility\Text::truncate(
                                                                        $product['product']['product'],
                                                                        22,
                                                                        [
                                                                                'ellipsis' => '...',
                                                                                'exact' => false
                                                                        ]
                                                                );
				
		if(in_array($product['product_id'],$disputedProductIds)){
        ?>
	
		<tr style="background: yellow;">
	    <?php }else{?>
	    <tr>
	    <?php } ?>
            <td><?php echo $product['id']; ?>&nbsp;</td>
              <td><?php echo $Productscode[$product['product']['id']]; ?>&nbsp;</td>
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
                    $imageURL = "/thumb_no-image.png";
                    if(@readlink($absoluteImagePath) ||file_exists($absoluteImagePath)){
                        $imageURL = "$siteBaseURL/files/Products/image/".$product['product']['id']."/$imageName";
                    }
                            
                    echo $this->Html->link(
                                $this->Html->image($imageURL, array('fullBase' => false,'width' => '100px','height' => '100px')),
                                array('controller' => 'products','action' => 'edit', $product['product']['id']),
                                array('escapeTitle' => false, 'title' => $product['product']['product'])
                               );
                ?>
            </td>
            <td><?php echo $product['product']['quantity'];?></td>
	    <?php   echo $this->Form->input(null,array(
                                    'name' => "StockTransfer[actual_quantity][$key]",
                                    'value' => $product['product']['quantity'],
                                    'type' => 'hidden'
                                    )
                            ); ?>
            <td><?php echo $product['sale_price'];?></td>
	    <td><?php
	    if(in_array($product['product_id'],$disputedProductIds)){
	    echo $this->Form->input(null,array(
                                    'name' => "StockTransfer[receiving_status][$key]",
                                    'value' => $disputed_data[$product['product_id']]['receiving_status'],
                                    'label' => false,
                                    'style' => 'width:110px;',
				    'options' => $disputeOptions,
                                    'disabled' => 'disabled'
                                    )
                            );
	    }else{
		echo $this->Form->input(null,array(
                                    'name' => "StockTransfer[receiving_status][$key]",
                                    //'value' => $productRemarks,
                                    'label' => false,
                                    //'style' => 'width:110px;',
				    'options' => $disputeOptions,
                                    'readonly' => false
                                    )
                            );
	    }
	    ?>
            </td>
	    <td><?php echo $product['quantity'];?></td>
		<td><?php
        //pr($disputedProductIds);
        //pr($product['product_id']);die;
	    if(in_array($product['product_id'],$disputedProductIds)){
            echo $this->Form->input('null',array(
                                    'type' => 'text',
                                    'name' => "StockTransfer[quantity][$key]",
                                      'value' => $disputed_data[$product['product_id']]['quantity'],
                                    'label' => false,
                                    'style' => 'width:80px;',
                                    'readonly' => 'readonly'
                                    )
                            );
	    }else{
            echo $this->Form->input('null',array(
                                    'type' => 'text',
                                    'name' => "StockTransfer[quantity][$key]",
                                    //'value' => $productRemarks,
                                    'label' => false,
                                    'style' => 'width:80px;',
                                    'readonly' => false
                                    )
                            );
	    }
	    ?>
			    <?php echo $this->Form->input(null,array(
                                    'type' => 'hidden',
                                    'name' => "StockTransfer[product_id][$key]",
                                    'value' => $product['product']['id'],
                                    'label' => $product['product']['id'],
                                    'style' => 'width:80px;'
                                    )
                            ); ?>
			    <?php echo $this->Form->input(null,array(
                                    'type' => 'hidden',
                                    'name' => "StockTransfer[kiosk_id][$key]",
                                    'value' => $products[0]['kiosk_order']['kiosk_id'],
                                    'label' => $products[0]['kiosk_order']['kiosk_id'],
                                    'style' => 'width:80px;'
                                    )
                            ); ?>
			    <?php echo $this->Form->input(null,array(
                                    'type' => 'hidden',
                                    'name' => "StockTransfer[kiosk_order_id][$key]",
                                    'value' => $products[0]['kiosk_order']['id'],
                                    'label' => $products[0]['kiosk_order']['id'],
                                    'style' => 'width:80px;'
                                    )
                            ); ?>
            </td>

	    <td><?php
	    if(in_array($product['product_id'],$disputedProductIds)){
	    echo $this->Form->input('null',array(
						'type' => 'text',
						'name' => "StockTransfer[kiosk_user_remarks][$key]",
						'label' => false,
                        'value' => $disputed_data[$product['product_id']]['kiosk_user_remarks'],
						//'style' => 'width:80px;',
						'readonly' => 'readonly'
						)
						  );
	    }else{
		echo $this->Form->input('null',array(
						'type' => 'text',
						'name' => "StockTransfer[kiosk_user_remarks][$key]",
						'label' => false,
						//'style' => 'width:80px;',
						'readonly' => false
						)
						  );
	    }
	    ?>
	    </td>
	</tr>
        <?php endforeach; ?>
    </tbody>
    </table>   
   <?php
   echo $this->Form->submit("Submit",['name'=>'submit1']);
   echo $this->Form->end();?>
   <span><i>**highlited rows are already disputed**</i></span>
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
	$('input[name = "submit1"]').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	});
</script>