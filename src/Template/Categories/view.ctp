 
<div class="categories view">
	<?php
	$i =  0;
	$groupStr = "";
	?>
<h2><?php echo __('Category'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($category->id); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Category'); ?></dt>
		<dd>
			<?php echo h($category->category); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Id Name Path'); ?></dt>
		<dd>
			<?php echo h($category->id_name_path); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Description'); ?></dt>
		<dd>
			<?php echo h($category->description); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Image'); ?></dt>
		<dd>
			<?php # echo h($category->image);
			$imageDir = WWW_ROOT."files".DS.'Categories'.DS.'image'.DS.$category->id.DS;
			$imageName = $category->image;
			$largeImageName = 'vga_'.$category->image;
			$absoluteImagePath = $imageDir.$imageName;
			$LargeimageURL = $imageURL = "/thumb_no-image.png";
				if(@readlink($absoluteImagePath) || file_exists($absoluteImagePath)){
					//$applicationURL = $this->html->url('/', true);
					//$imageURL = $applicationURL."files/product/image/".$product['Product['id."/thumb_$imageName";
					$imageURL = "$siteBaseURL/files/Categories/image/".$category->id."/$imageName";
					$LargeimageURL = "$siteBaseURL/files/Categories/image/".$category->id."/"."$largeImageName";
				}
						$groupStr.="\n$(\".group{$i}\").colorbox({rel:'group{$i}'});";
					echo $this->Html->link(
							  $this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')),
							  $LargeimageURL,
							  array('escapeTitle' => false, 'title' => $category->category,'class' => "group{$i}")
							 );	
			
			?>
			&nbsp;
		</dd>
		<dt><?php echo __('Parent Category'); ?></dt>
		<dd>
			<?php echo $category->has('parent_category') ? $this->Html->link($category->parent_category->id, ['controller' => 'Categories', 'action' => 'view', $category->parent_category->id]) : ''; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Sort Order'); ?></dt>
		<dd>
			<?php echo h($category->sort_order); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo $activeOptions[$category->status]; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo date('d-m-Y H:i:s',strtotime($category->modified)) ; ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Category'), ['action' => 'edit', $category->id]); ?> </li>
		 <li><?= $this->Form->postLink(__('Delete Category'), ['action' => 'delete', $category->id], ['confirm' => __('Are you sure you want to delete # {0}?', $category->id)]) ?> </li>
		<li><?php echo $this->Html->link(__('New Category'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Categories'), array('controller' => 'categories', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Parent <br/>Category'), ['controller' => 'categories', 'action' => 'add'],['escape' => false]); ?> </li>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php  echo __('Related Categories'); ?></h3>
	<?php if (!empty($category->ChildCategory)): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Category'); ?></th>
		<th><?php echo __('Id Name Path'); ?></th>
		<th><?php echo __('Description'); ?></th>
		<th><?php echo __('Image'); ?></th>
		<th><?php echo __('Parent Id'); ?></th>
		<th><?php echo __('Top'); ?></th>
		<th><?php echo __('Column'); ?></th>
		<th><?php echo __('Sort Order'); ?></th>
		<th><?php echo __('Status'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($category->ChildCategory as $childCategory): ?>
		<tr>
			<td><?php echo $childCategory->id; ?></td>
			<td><?php echo $childCategory->category; ?></td>
			<td><?php echo $childCategory->id_name_path; ?></td>
			<td><?php echo $childCategory->description; ?></td>
			<td><?php echo $childCategory->image; ?></td>
			<td><?php echo $childCategory->parent_id; ?></td>
			<td><?php echo $childCategory->top; ?></td>
			<td><?php echo $childCategory->column; ?></td>
			<td><?php echo $childCategory->sort_order; ?></td>
			<td><?php echo $childCategory->status; ?></td>
			<td><?php echo $childCategory->created; ?></td>
			<td><?php echo $childCategory->modified; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'categories', 'action' => 'view', $childCategory->id)); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'categories', 'action' => 'edit', $childCategory->id)); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'categories', 'action' => 'delete', $childCategory->id), array(), __('Are you sure you want to delete # %s?', $childCategory->id)); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Child Category'), array('controller' => 'categories', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
<div class="related">
	<h3><?php echo __('Related Products'); ?></h3>
	<?php if (!empty($category->products)): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Category Id'); ?></th>
		<th><?php echo __('Cost Price'); ?></th>
		<th><?php echo __('Brand Id'); ?></th>
		<th><?php echo __('Manufacturing Date'); ?></th>
		<th><?php echo __('Sku'); ?></th>
		<th><?php echo __('Country Make'); ?></th>
		<th><?php echo __('Product Code'); ?></th>
		<th><?php echo __('Weight'); ?></th>
		<th><?php echo __('Color'); ?></th>
		<th><?php echo __('Featured'); ?></th>
		<th><?php echo __('Discount'); ?></th>
		<th><?php echo __('Image Id'); ?></th>
		<th><?php echo __('Manufacturer'); ?></th>
		<th><?php echo __('Stock Level'); ?></th>
		<th><?php echo __('Dead Stock Level'); ?></th>
		<th><?php echo __('Status'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($category->products as $product): ?>
		<tr>
			<td><?php echo $product->id; ?></td>
			<td><?php echo $product->category_id; ?></td>
			<td><?php $cost_price =  $product->cost_price;
			echo  $CURRENCY_TYPE.$cost_price ;
			?></td>
			<td><?php echo $product->brand_id; ?></td>
			<td><?php echo $this->Time->format('jS M, Y g:i A',$product->manufacturing_date,null,null); ?></td>
			<td><?php echo $product->sku; ?></td>
			<td><?php echo $product->country_make; ?></td>
			<td><?php echo $product->product_code; ?></td>
			<td><?php echo $product->weight; ?></td>
			<td><?php echo $product->color; ?></td>
			<td><?php echo $product->featured; ?></td>
			<td><?php echo $product->discount."%"; ?></td>
			<td><?php echo $product->image_id; ?></td>
			<td><?php echo $product->manufacturer; ?></td>
			<td><?php echo $product->stock_level; ?></td>
			<td><?php echo $product->dead_stock_level; ?></td>
			<td><?php echo $product->status; ?></td>
			<td><?php echo $this->Time->format('jS M, Y g:i A', $product->created,null,null); ?></td>
			<td><?php echo $this->Time->format('jS M, Y g:i A',$product->modified,null,null); ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'products', 'action' => 'view', $product->id)); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'products', 'action' => 'edit', $product->id)); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'products', 'action' => 'delete', $product->id), array(), __('Are you sure you want to delete # %s?', $product->id)); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
<script>
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>