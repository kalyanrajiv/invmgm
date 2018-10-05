<div class="products index">	
    <strong><?php #pr($products);die;
    
    ?>
    
    <table cellpadding="0" cellspacing="0">
    <thead>
        <tr>
	    <th>Kiosk</th>
            <th>Code</th>
            <th>Product</th>
            <th>Image</th>
            <th>Quantity</th>
	</tr>
    </thead>
    
    <tbody>
	<?php //pr($productArr);
	foreach($productArr as $kioskName=>$productInfo){?>
	<?php if(!empty($productArr[$kioskName])){
	    $truncatedName =
						
						\Cake\Utility\Text::truncate( $productInfo['product'],
													 30,
													 [ 'ellipsis' => '...', '
													  exact' => false ] );		
	    ?>	
	    <tr>
		<td><?php echo $kioskName;?></td>
		<td><?php echo $productInfo['product_code'];?></td>
		<td><?php echo $truncatedName;?></td>
		<td><?php $imageDir = WWW_ROOT."files".DS.'product'.DS.'image'.DS.$productInfo['id'].DS;
			$imageName = 'thumb_'.$productInfo['image'];
			$absoluteImagePath = $imageDir.$imageName;
			$imageURL = "/thumb_no-image.png";
			if(@readlink($absoluteImagePath) ||file_exists($absoluteImagePath)){
			    $imageURL = "/files/product/image/".$productInfo['id']."/$imageName";
			}
				
			echo $this->Html->link(
				    $this->Html->image($imageURL, array('fullBase' => true)),
				    array('controller' => 'products','action' => 'edit', $productInfo['id']),
				    array('escapeTitle' => false, 'title' => $productInfo['product'])
				   );?>
		</td>
		<td><?php echo $productInfo['quantity'];?></td>
	    </tr>    
	<?php }
	}
	?>
    </tbody>
    </table>      
</div>

<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('Kiosk 2 WH Trnsient Ordrs'), array('controller' => 'kiosk_orders','action' => 'transient_kiosk_orders')); ?></li>
        <li><?php echo $this->Html->link(__('Kiosk 2 WH Confrmd Ordrs'), array('controller' => 'kiosk_orders', 'action' => 'confirmed_kiosk_orders')); ?> </li>
        <li><?php echo $this->Html->link(__('Stock Transfer'), array('controller' => 'stock_transfer', 'action' => 'index')); ?> </li>        
    </ul>
</div>
