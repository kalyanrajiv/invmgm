<?php
$jQueryURL = "http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
if(defined('URL_SCHEME')){
	$jQueryURL = URL_SCHEME."ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
}
?>
<script type="text/javascript" src="<?php echo $jQueryURL;?>"></script>
<?php echo $this->Html->script('jquery.printElement');
echo $this->Html->css('model/style.css');
echo $this->Html->css('model/submodal.css');
 echo $this->Html->script('model/submodalsource.js');
 echo $this->Html->script('model/submodal.js');

?>
<input type='button' id='printSelected' name='print' value='Print Receipt' style='width:200px;' align='center' />
<?= $this->Html->link('Stock Below Level', array('action' => 'stock_level'));?>

	<?php echo $this->Form->create('Stock'); ?>
	<?php
	 
	$quantityArr = array();
	$productids = array();
	 
	foreach($data as $k => $understockLevelOrder){
		$product_id = $understockLevelOrder['product_id'];
		$quantity = $understockLevelOrder['quantity'];
		$quantityArr[$product_id] = $quantity;
	}
 ?>
	<?php
		$queryStr = "";
		$rootURL = $this->request->webroot;//$this->html->url('/', true);
		if(!empty($rawDate) ){
			 $queryStr.="date=".$rawDate;
		}
	 //echo $queryStr;
			
	?>

	<strong style="font-size: 20px;color: red;"><?php echo __('Saved Products'); ?>&nbsp;<a href="<?php echo $rootURL;?>Stock/exportproducts/?<?php echo $queryStr;?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png', array('fullBase' => true));?></a></strong><div id='printDiv' style="text-align: center;">
		<?php if(array_key_exists(0,$data)){?>
			<span style="float: left;"><h3>Order #: <?=$data[0]['order_id'];?>(created on: <?=date("jS M, Y g:i A",strtotime($data[0]['created']))?>)</h3></span>
			<?php }
		?>
		<table cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<th>Product Code</th>
					<th>Product</th>
					<th>Category</th>
					<th>Image</th>
					<th>Color</th>
					<th>Quantity</th>
				</tr>
			</thead>
			<tbody>
				<?php $currentPageNumber = $this->Paginator->current();?>
				<?php //pr($product);
				foreach ($product as $key => $productData):
				if($productData){
				$truncatedProduct = \Cake\Utility\Text::truncate(
                                                                $productData['product'],
                                                                50,
                                                                [
                                                                    'ellipsis' => '...',
                                                                    'exact' => false
                                                                ]
                                                            );
				}
				?>
				<tr>
					<td>
						<?php
						if($productData){
						if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
						<?php echo $this->Html->link(
									     $productData['product_code'],
									     array('controller' => 'products', 'action' => 'edit', $productData['id']),
									     array('escapeTitle' => false, 'title' => $productData['product'])
									     ); ?>
						<?php }else{?>
						<?php echo $productData['product_code']; ?>
						<?php }
						}else{
							echo "<strong>--</strong>";	
						}?>
					</td>
					<td>
						<?php if($productData){
							echo $this->Html->link(
									     $truncatedProduct,
									     array('controller' => 'products', 'action' => 'view', $productData['id']),
									     array('escapeTitle' => false, 'title' => $productData['product'])
									     );
						}else{
							echo "<strong>--</strong>";
						}
							?>
					</td>
					<td><?php if(array_key_exists($productData['category_id'],$categoryNames)){
						echo $categoryNames[$productData['category_id']];
						}else{
							echo "--";
						}?></td>
					<td>
			<?php
			if($productData){
				$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$productData['id'].DS;
				$imageName =  $productData['image'];
				$absoluteImagePath = $imageDir.$imageName;
				$imageURL = "/thumb_no-image.png";
                //echo $absoluteImagePath;die;
				if(@readlink($absoluteImagePath) || file_exists($absoluteImagePath)){
					//$applicationURL = $this->html->url('/', true);
					//$imageURL = $applicationURL."files/product/image/".$product['Product']['id']."/thumb_$imageName";
					$imageURL = "/files/Products/image/".$productData['id']."/$imageName";
				}
			
					echo $this->Html->link(
							  $this->Html->image($imageURL, array('fullBase' => true, 'style' => 'width: 40px; height: 40px;')),
							  $imageURL,
							  array('escapeTitle' => false, 'title' => $productData['product'],'class' => "submodal")
							 );}else{
						echo "<strong>--</strong>";
					}

?>&nbsp;</td>
					<td><?php if($productData){echo $productData['color'];}else{ echo "<strong>--</strong>";}?></td>
					<td><?php if($productData){echo $this->Form->input(null,array(
											'type' => 'text',
											'name' => "data[Stock][quantity][$key]",
											'value' => $quantityArr[$productData['id']],
											'label' => false,
											'style' => 'width:80px;',
											'readonly' => false
									  )
							      ); ?>
							      <?php echo $this->Form->input(null,array(
										     'type' => 'hidden',
											'name' => "data[Stock][product_id][$key]",
											'value' => $productData['id']
									  )
							      );
					}else{
						echo "<strong>--</strong>";}?>&nbsp;</td>
				</tr>
				<?php 
				endforeach;?>
				<?php echo $this->Form->input(null,array('type' => 'hidden','value' => $currentPageNumber,'name' => 'current_page'));?>
			</tbody>
		</table>
</div>