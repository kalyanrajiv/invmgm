<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
	$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<style>dt{width: 200px;}dd{padding-left: 20px;}</style>
<div class="products view">
<h2><?php echo __('Product'); ?></h2><?php //pr($product);die;  ?>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($product['id'] ); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Product'); ?></dt>
		<dd><?php  echo h($product['product'] ); ?>&nbsp;</dd>
		<dt><?php echo __('Category'); ?></dt>
		<dd>
			<?php  echo  $product['category']['category']  ; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Quantity'); ?></dt>
		<dd>
			<?php echo h($product['quantity'] ); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Description'); ?></dt>
		<dd>
			<?php echo h($product['description'] ); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Sale Price'); ?></dt>
		<dd>
		  <?php echo $currency.$product['selling_price']; ?>	 
		
			&nbsp;
		</dd>
		<dt><?php echo __('Brand'); ?></dt>
		<dd>
			<?php echo  $product['brand']['brand'] ; ?>
			&nbsp;
			 
		</dd>
		<dt><?php echo __('Manufacturing Date'); ?></dt>
		<dd>
			<?php if(!empty($product['manufacturing_date']))echo date('d-m-y g:i A', strtotime($product['manufacturing_date']) ); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Sku'); ?></dt>
		<dd>
			<?php echo h($product['sku']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Country Make'); ?></dt>
		<dd>
			<?php echo h($product['country_make'] ); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Product Code'); ?></dt>
		<dd>
			<?php echo h($product['product_code'] ); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Weight'); ?></dt>
		<dd>
			<?php echo h($product['weight'] ); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Color'); ?></dt>
		<dd>
			<?php echo h($product['color'] ); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Featured'); ?></dt>
		<dd>
			<?php echo h($product['featured']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Discount'); ?></dt>
		<dd>
			<?php echo h($product['discount'])."%"; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Image'); ?></dt>
		<dd>
			<?php
				#echo $this->Html->link($product['Image']['id'], array('controller' => 'images', 'action' => 'view', $product['Image']['id']));
				#echo h($product['Product']['image']);
				$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product['id'].DS;
				  $imageName = $product['image'];
				$absoluteImagePath = $imageDir.$imageName;
				$imageURL = "/thumb_no-image.png";
				if(@readlink($absoluteImagePath) || file_exists($absoluteImagePath)){
					//$applicationURL = $this->html->url('/', true);
					//$imageURL = $applicationURL."files/product/image/".$product['Product']['id']."/thumb_$imageName";
					$imageURL = "$siteBaseURL/files/Products/image/".$product['id']."/$imageName";
				}
					
				echo $this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px','escapeTitle' => false, 'title' => $product['product']));
			?>
			&nbsp;
		</dd>
		<dt><?php echo __('Manufacturer'); ?></dt>
		<dd>
			<?php echo h($product['manufacturer']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Stock Level'); ?></dt>
		<dd>
			<?php echo h($product['stock_level']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Dead Stock Level'); ?></dt>
		<dd>
			<?php echo h($product['dead_stock_level']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo $statusOptions[$product['status']]; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd><?php if(!empty($product['created']))echo date('d-m-y g:i A', strtotime($product['created'])); ?>&nbsp;</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd><?php echo date('d-m-y g:i A', strtotime($product['modified'])); ?>&nbsp;</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Order'), array('controller'=>'kiosk_product_sales', 'action' => 'new_order')); ?> </li>
	</ul>
</div>