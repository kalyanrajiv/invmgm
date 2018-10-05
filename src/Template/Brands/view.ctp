<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$active = Configure::read('active');
$currency = Configure::read('CURRENCY_TYPE'); 

?>
<div class="brands view">
    
<h2><?php echo __('Brand'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($brand->id); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Brand'); ?></dt>
		<dd>
			<?php echo h($brand->brand); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Company'); ?></dt>
		<dd>
			<?php echo h($brand->company); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo $active[$brand->status]; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($brand->created) ); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($brand->modified)); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Brand'), array('action' => 'edit', $brand->id )); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Brand'), array('action' => 'delete', $brand->id ), array(), __('Are you sure you want to delete # %s?', $brand->id )); ?> </li>
		<li><?php echo $this->Html->link(__('List Brands'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Brand'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Products'); ?></h3>
	<?php   if (!empty($brand->products)): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Category Id'); ?></th>
		<th><?php echo __('Price'); ?></th>
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
	<?php foreach ($brand['products'] as $product): ?>
		<tr>
			<td><?php echo $product->id; ?></td>
			<td><?php echo $product->category_id; ?></td>
			<td><?php $cost_price = $product->cost_price ;
			 echo $currency.$cost_price;
			?></td>
			<td><?php echo $product->brand_id ; ?></td>
			<td><?php echo $product->manufacturing_date ; ?></td>
			<td><?php echo $product->sku ; ?></td>
			<td><?php echo $product->country_make ; ?></td>
			<td><?php echo $product->product_code ; ?></td>
			<td><?php echo $product->weight ; ?></td>
			<td><?php echo $product->color ; ?></td>
			<td><?php echo $featuredOptions[$product->featured ]; ?></td>
			<td><?php echo $product->discount ."%"; ?></td>
			<td><?php echo $product->image ; ?></td>
			<td><?php echo $product->manufacturer ; ?></td>
			<td><?php echo $product->stock_level ; ?></td>
			<td><?php echo $product->dead_stock_level ; ?></td>
			<td><?php echo $active[$product->status ]; ?></td>
			<td><?php echo  date('jS M, Y g:i A',strtotime($product->created));  ?></td>
			<td><?php echo date('jS M, Y g:i A',strtotime($product->modified));  ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'products', 'action' => 'view', $product->id )); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'products', 'action' => 'edit', $product->id )); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'products', 'action' => 'delete', $product->id ), array(), __('Are you sure you want to delete # %s?', $product->id )); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php  endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>