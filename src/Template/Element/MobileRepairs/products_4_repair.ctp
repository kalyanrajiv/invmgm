<?php	    
	$currentPageNumber = $this->Paginator->current();
?>
<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th>Product ID</th>
			<th>Product Code</th>
			<th><?php echo $this->Paginator->sort('product_id'); ?></th>
			<th><?php echo $this->Paginator->sort('color');?></th>
			<th>Image</th>
			<th><?php echo $this->Paginator->sort('quantity','Current Stock'); ?></th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody>
	
	<?php	   
	$sessionBaket = $this->request->Session()->read("parts_basket");
	
	foreach ($products as $key => $product){
		$truncatedProduct = \Cake\Utility\Text::truncate(
                                                                        $product->product,
                                                                        22,
                                                                        [
                                                                                'ellipsis' => '...',
                                                                                'exact' => false
                                                                        ]
                                                                );
		
		$imageDir = WWW_ROOT."files".DS.'product'.DS.'image'.DS.$product->id.DS;
		$imageName = 'thumb_'.$product->image;
		$absoluteImagePath = $imageDir.$imageName;
		$imageURL = "/thumb_no-image.png";
		
		if(@readlink($absoluteImagePath) || file_exists($absoluteImagePath)){
			$imageURL = $siteBaseURL."/files/product/image/".$product->id."/$imageName";
		}
			
		$productQuantity = null;
		$sellingPrice = $product->selling_price;
		$productRemarks = "";
		
		$checked = false;
		if( count($sessionBaket) > 1){
			if(array_key_exists($product->id,$sessionBaket)){$checked = true;}
		}
?>
		<tr>
			<td><?=$product->id?></td>  
			<td><?=$product->product_code?></td>
			<td>
			<?php
			echo $this->Html->link($truncatedProduct,
						array('controller' => 'products', 'action' => 'view', $product->id),
						array('escapeTitle' => false, 'title' => $product->product)
				);
			?>
			</td>
			<td><?php echo $product->color; ?></td>
			<td><?php
				echo $this->Html->link(
						$this->Html->image($imageURL, array('fullBase' => true)),
						array('controller' => 'products','action' => 'edit', $product->id),
						array('escapeTitle' => false, 'title' => $product->product)
					);
				?>
			</td>
			<td><?php echo h($product->quantity); ?>&nbsp;</td>
			<td>
				<?php
				$replacementPrdct = $product->id;
					if($product->quantity){
					    echo $this->Form->input('Replace',array(
							'type' => 'submit',
							'name' => "data[PartsRepaired][replacement][$replacementPrdct]",
							'value' => $product->id,
							'label' => false,
							'style' => 'width:80px;',
							'readonly' => false,
							'onClick' => "updateKiosk($ksk_Id);"
							)
						);
					}
				?>
			</td>
		</tr>
<?php
	}
	echo $this->Form->input(null,array('type' => 'hidden','value' => $currentPageNumber,'name' => 'current_page'));
?>
</tbody>
</table>		    
<p>
		<span style="float: right;"><i style="color: blue;">**Internal Booking</i> <i style="color: red;">**Rebooked</i></span>
<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
