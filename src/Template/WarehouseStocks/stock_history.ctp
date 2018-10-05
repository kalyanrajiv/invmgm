<?php
  use Cake\Core\Configure;
    use Cake\Core\Configure\Engine\PhpConfig;
	$currency = Configure::read('CURRENCY_TYPE');
	
?>
<div class="warehouseStocks index">
	<h2><?php echo __('Stock History'); ?></h2>
	<b>**Current Stock = Prev Qty + Newly entry</b>
	<?php if(!empty($historyData)){?>
	<table cellpadding="0" cellspacing="0">	
		<tr><td colspan='8'><hr></td></tr>
		<tr>
		  <th><?php echo "Code";?></th>
			<th><?php echo $this->Paginator->sort('product_id'); ?></th>
			<th><?php echo $this->Paginator->sort('user_id');?></th>
			<th><?php echo $this->Paginator->sort('warehouse_vendor_id');?></th>
			<th><?php echo $this->Paginator->sort('quantity');?></th>
			<th><?php echo $this->Paginator->sort('current_stock');?></th>
			<th><?php echo $this->Paginator->sort('price','Cost Price');?></th>
			<th><?php echo $this->Paginator->sort('in_out');?></th>
			<th><?php echo $this->Paginator->sort('reference_number');?></th>
			<th><?php echo $this->Paginator->sort('created', 'Date');?></th>
			 
		</tr>
		<?php
		//pr($userName);die;
			foreach($historyData as $key=>$dataInfo){?>
			<tr>
			  <td><?php echo $productCode[$dataInfo->product_id]; ?></td>
				<td><?php echo $productName[$dataInfo->product_id];?></td>
				<td><?php echo $userName[$dataInfo->user_id];?></td>
				<td><?php echo $warehouseVendors[$dataInfo->warehouse_vendor_id];?></td>
				<td><?php echo $dataInfo->quantity ;?></td>
				<td><?php echo $dataInfo->current_stock ;?></td>
				<td><?php echo $currency.$dataInfo->price; ?></td>
				<td><?php echo $inOut[$dataInfo->in_out];?></td>
				<td><?php echo $dataInfo->reference_number;?></td>
				<?php
				 $dataInfo->created->i18nFormat(
								[\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
							);
				$created =  $dataInfo->created->i18nFormat('dd-MM-yyyy HH:mm:ss');
				?>
				<td><?php echo date('d-m-Y h:i:s',strtotime($created));?></td>
			</tr>
		<?php }?>
		
		 
	</table>
	
	 <div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
	<?php }else{?>
	<h4>No data found!!</h4>
	<?php } ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div>
