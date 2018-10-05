<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Warehouse Vendor'), array('action' => 'add'),array('style'=>"width: 141px;") ); ?></li>
	</ul>
</div>
<div class="warehouseVendors index">
	<h2><?php echo __('Warehouse Vendors'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('vendor'); ?></th>
			<th><?php echo $this->Paginator->sort('vendor_email'); ?></th>
			<th><?php echo $this->Paginator->sort('vendor_address_1','Address'); ?></th>
			<th><?php echo $this->Paginator->sort('zip','Postal Code'); ?></th>
			<th><?php echo $this->Paginator->sort('vendor_contact','Contact'); ?></th>
			<th><?php echo $this->Paginator->sort('status'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($warehouseVendors as $warehouseVendor): ?>
	<tr><?php //pr($warehouseVendor);die; ?>
		<td><?php echo h($warehouseVendor['id']); ?>&nbsp;</td>
		<td><?php echo h($warehouseVendor['vendor']); ?>&nbsp;</td>
		<td><?php echo h($warehouseVendor['vendor_email']); ?>&nbsp;</td>
		<td><?php echo h($warehouseVendor['vendor_address_1']); ?>&nbsp;</td>
		<td><?php echo h($warehouseVendor['zip']); ?>&nbsp;</td>
		<td><?php echo h($warehouseVendor['vendor_contact']); ?>&nbsp;</td>
		<td><?php echo $active[$warehouseVendor['status']]; ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $warehouseVendor['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $warehouseVendor['id'])); ?>
			<?php //echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $warehouseVendor->id], ['confirm' =>  __('Are you sure you want to delete # %s?', $warehouseVendor->id)]); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
	<p>
	<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
	</div>
</div>

