<?php
//echo'hello';die;
    use Cake\Core\Configure;
    use Cake\Core\Configure\Engine\PhpConfig;
	$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
    $currency = Configure::read('CURRENCY_TYPE');
	//pr($product);
?>
<style>dt{width: 200px;}dd{padding-left: 20px;}</style>
<div class="products view">
<?php if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
<strong><?php echo '<span style="font-size:20px;color:red;">Product</span> &nbsp;&nbsp;&nbsp;&nbsp;'.$this->Html->link('View stock history',array('controller'=>'warehouse_stocks','action'=>'stock_history',$product['id'] )) ;?></strong>
<?php }else{?>
<h2><?php echo __('Product');?></h2>
<?php }?>

	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd><?php  echo h($product['id'] ); ?>&nbsp;</dd>
		
		<dt><?php echo __('Product'); ?></dt>
		<dd><?php  echo h($product['product'] ); ?>&nbsp;</dd>
		
		<dt><?php echo __('Product Code'); ?></dt>
		<dd>
			<?php  echo h($product['product_code'] ); ?>&nbsp;
		</dd>
		<dt>
			<?php echo __("Barcode"); ?>
		</dt>
		<dd>
			<?php echo $barcode;?>
		</dd>
		<dt><?php echo __('Category');?></dt>
		<dd>
			<?php  echo $this->Html->link($product['category']['category'], array('controller' => 'categories', 'action' => 'view', $product['category']['id'])); ?>&nbsp;
		</dd>
		
		<dt><?php echo __('Quantity'); ?></dt>
		<dd>
			<?php echo $product['quantity']; ?>&nbsp;
		</dd>
		
		<dt><?php echo __('Description'); ?></dt>
		<dd>
			<?php echo h($product['description'] ); ?>
			&nbsp;
		</dd>
		
		<?php
			if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
			<dt><?php echo __('Cost Price'); ?></dt>
			<dd><?php echo $currency.$product['cost_price'] ;?>&nbsp;</dd>
			<dt><?php echo __('Last Updated CP'); ?></dt>
			<dd><?php if(!empty($product['lu_cp']))echo date('d-m-y g:i A', strtotime($product['lu_cp'])); ?>&nbsp;</dd>
			<dt style='color:blue;'><?php echo __('Retail Cost Price'); ?></dt>
			<dd><?php echo $currency.$product['retail_cost_price'] ;?>&nbsp;</dd>
			<dt style='color:blue;'><?php echo ('Last Updated Rtl CP'); ?></dt>
			<dd><?php if(!empty($product['lu_rcp']))echo date('d-m-y g:i A', strtotime($product['lu_rcp'])); ?>&nbsp;</dd>
		<?php } ?>
		
		<dt><?php echo __('Sale Price'); ?></dt>
		<dd><?php echo $currency.$product['selling_price']; ?>&nbsp;</dd>
		<dt><?php echo __('Last Updated SP'); ?></dt>
		<dd><?php if(!empty($product['lu_sp']))echo date('d-m-y g:i A', strtotime($product['lu_sp'])); ?>&nbsp;</dd>
		<?php
			if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
			<dt style='color:blue;'><?php echo __('Retail Selling Price'); ?></dt>
			<dd><?php echo $currency.$product['retail_selling_price'] ;?>&nbsp;</dd>
			<dt style='color:blue'><?php echo 'Last Updated Rtl SP'; ?></dt>
			<dd><?php if(!empty($product['lu_rsp']))echo date('d-m-y g:i A', strtotime($product['lu_rsp'])); ?>&nbsp;</dd>
		<?php } ?>
		<dt><?php echo __('Brand'); ?></dt>
		<dd>
			<?php echo $this->Html->link($product['brand']['brand'], array('controller' => 'brands', 'action' => 'view', $product['brand']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Model'); ?></dt>
		<dd>
			<?php echo $product['model']; ?>
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
			<?php echo $featured = ($product['featured'] ) ? 'Yes':'No'; ?>&nbsp;
		</dd>
		
		<dt><?php echo __('Discount'); ?></dt>
		<dd>
			<?php if($product['discount_status']  == 1){
				echo h($product['discount'] )."%";
				}else{
					echo "NA";
				} ?>
			&nbsp;
		</dd>
		<?php
			if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
				<dt style='color:blue;'><?php echo __('Retail Discount'); ?></dt>
				<dd><?php echo $rtDis = ($product['rt_discount_status']  == 1) ? h($product['retail_discount'] )."%": "NA";?>&nbsp;</dd>
		<?php
			}
		?>
		<dt><?php echo __('Special Offer'); ?></dt>
		<dd>
			<?php echo $spOffer = ($product['special_offer'] == 1) ? 'Yes' : 'No'; ?>
		</dd>
		
		<dt style='color:blue;'><?php echo __('Retail Special Offer'); ?></dt>
		<dd>&nbsp;<?php echo $spOffer = ($product['retail_special_offer'] == 1) ? 'Yes' : 'No'; ?></dd>
		
		<dt><?php echo __('Festival Offer'); ?></dt>
		<dd>&nbsp;<?php echo $spOffer = ($product['festival_offer'] == 1) ? 'Yes' : 'No'; ?></dd>
		
		<dt><?php echo __('Image'); ?></dt>
		<dd>
			<?php
				#echo $this->Html->link($product['Image']['id'], array('controller' => 'images', 'action' => 'view', $product['Image']['id']));
				#echo h($product['image']);
				$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product['id'] .DS;
				$imageName = $product['image'] ;
				$absoluteImagePath = $imageDir.$imageName;
				$imageURL = "/thumb_no-image.png";
				if(@readlink($absoluteImagePath) || file_exists($absoluteImagePath)){
					//$applicationURL = $this->html->url('/', true);
					//$imageURL = $applicationURL."files/product/image/".$product['id']."/thumb_$imageName";
					$imageURL = "$siteBaseURL/files/Products/image/".$product['id'] ."/$imageName";
				}
					
					echo $this->Html->link(
							  $this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')), //rasu
							  array('controller' => 'products','action' => 'edit', $product['id'] ),
							  array('escapeTitle' => false, 'title' => $product['product'] )
							 );
			?>
			&nbsp;
		</dd>
		<dt><?php echo __('Manufacturer'); ?></dt>
		<dd>
			<?php echo h($product['manufacturer'] ); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Stock Level'); ?></dt>
		<dd>
			<?php echo h($product['stock_level'] ); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Dead Stock Level'); ?></dt>
		<dd>
			<?php echo h($product['dead_stock_level'] ); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo $statusOptions[$product['status'] ]; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created by'); ?></dt>
		<dd>
			<?php echo $userName; ?>
			&nbsp;&nbsp;&nbsp;&nbsp;**Created by is applicable only for hpwaheguru
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd><?php
		if(!empty($product['created'])){
			
				$product['created']->i18nFormat(
										[\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
								);
				$show_date =  $product['created']->i18nFormat('dd-MM-yyyy HH:mm:ss');
				
				echo $show_date = date("d-m-y h:i a",strtotime($show_date));
				
				
				//echo date('d-m-y g:i A', strtotime($product['created']));
		}else{
			echo "--";
			}	
		?>&nbsp;</dd>
		<dt><?php echo __('Quantity Modified'); ?></dt>
		<dd><?php
		if(!empty($product['qty_modified'])){
			echo date('d-m-y g:i A', strtotime($product['qty_modified']));
		}
		?>&nbsp;</dd>
		<?php $path = dirname(__FILE__);
		$isboloRam = strpos($path,"mbwaheguru");
		if($isboloRam != false){
			 ?>
				<dt><?php echo __('last Modified by');  ?></dt>
		<dd style="width: 451px;">
			<?php
			if(array_key_exists($product['modified_by'],$Users)){
				echo $Users[$product['modified_by']];	
			}else{
				echo "--";
			}
			 ?>
			&nbsp;&nbsp;&nbsp;&nbsp;**Modified by is applicable only for hpwaheguru
		</dd>
		<?php 
		}else{ ?>
				<dt><?php echo __('last Modified by');  ?></dt>
		<dd>
			<?php
			if(array_key_exists($product['modified_by'],$Users)){
				echo $Users[$product['modified_by']];	
			}else{
				echo "--";
			}
			 ?>
			&nbsp;&nbsp;&nbsp;&nbsp;**Modified by is applicable only for hpwaheguru
		</dd>
		<?php }
		?>
		
		<dt><?php echo __('Product Modified On'); ?></dt>
		<dd>
			<?php   if(!empty($product['last_updated'])){
                 echo date('d-m-y g:i A', strtotime($product['last_updated']));
            }else{
                echo "--";
            }
           
             ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Last Import'); ?></dt>
		<dd style='background-color: yellow'><?php  if(!empty($product['last_import']))echo date('d-m-y g:i A', strtotime($product['last_import']));else echo "Never Imported";?>&nbsp;</dd>
		<?php
			if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
			<dt><?php echo __('Cost Price'); ?></dt>
			<dd><?php echo $currency.$product['cost_price'] ;?>&nbsp;</dd>
			<dt><?php echo __('Last Updated CP'); ?></dt>
			<dd><?php if(!empty($product['lu_cp']))echo date('d-m-y g:i A', strtotime($product['lu_cp'])); ?>&nbsp;</dd>
		<?php } ?>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Product'), array('action' => 'edit', $product['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Product'), array('action' => 'delete', $product['id']),
                                             array(), __('Are you sure you want to delete # %s?', $product['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Products'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Categories'), array('controller' => 'categories', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Category'), array('controller' => 'categories', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
		
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Reorder Levels'); ?></h3>
	<?php if (!empty($product['reorder_levels'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Kiosk Id'); ?></th>
		<th><?php echo __('Product Id'); ?></th>
		<th><?php echo __('Reorder Level'); ?></th>
		<th><?php echo __('Status'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($product['reorder_levels'] as $reorderLevel): ?>
		<tr>
			<td><?php echo $reorderLevel['id'] ; ?></td>
			<td><?php echo $reorderLevel['kiosk_id'] ; ?></td>
			<td><?php echo $reorderLevel['product_id'] ; ?></td>
			<td><?php echo $reorderLevel['reorder_level'] ; ?></td>
			<td><?php echo $reorderLevel['status'] ; ?></td>
			<td><?php echo date('d-m-y g:i A', strtotime($reorderLevel['created']));   ?></td>
			<td><?php echo date('d-m-y g:i A',strtotime($reorderLevel['modified']));   ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'reorder_levels', 'action' => 'view', $reorderLevel['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'reorder_levels', 'action' => 'edit', $reorderLevel['id'] )); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'reorder_levels', 'action' => 'delete', $reorderLevel['id'] ), array(), __('Are you sure you want to delete # %s?', $reorderLevel['id'] )); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Reorder Level'), array('controller' => 'reorder_levels', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
