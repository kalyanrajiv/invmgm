<div class="mobilePurchases index">
	<?php if(empty(trim($reference))){
		$reference1 = "_";
		}else{
			$reference1 = $reference;
			}?>
	<h2><?php echo 'Mobile Reference: '.$this->Html->link($reference1,array('action'=>'edit_mobile_reference_list',$reference,$randNum),array('alt'=>'Edit','title'=>'Edit')); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id','Purchase id'); ?></th>
			<th><?php echo $this->Paginator->sort('kiosk_id','Kiosk'); ?></th>
			<th><?php echo $this->Paginator->sort('brand_id'); ?></th>
			<th><?php echo $this->Paginator->sort('mobile_model_id'); ?></th>
			<th><?php echo $this->Paginator->sort('imei'); ?></th>
			<th><?php echo $this->Paginator->sort('color'); ?></th>
			<th><?php echo $this->Paginator->sort('type'); ?></th>
			<th><?php echo $this->Paginator->sort('network_id'); ?></th>
			<th><?php echo $this->Paginator->sort('grade'); ?></th>
			<th>Current Status</th>
            
	</tr>
	</thead>
	<tbody>
	
	<?php
	foreach ($mobileListing as $key=>$mobileList):
		if($mobileList['receiving_status'] == 1){
			$currentStatus = "Transient";
		}else{
			$currentStatus = $status[$mobileList['status']];
		}
	?>
	<tr>
		<td><?php echo h($mobileList->id); ?>&nbsp;</td>
		<td><?php
			if($mobileList->kiosk_id == 10000){
				echo "Warehouse";
			}else{
				if(array_key_exists($mobileList->kiosk_id,$kiosks)){
					echo $kiosks[$mobileList->kiosk_id];
				}else{
					echo "--";
				}
				//echo $mobilePurchase['purchased_by_kiosk'];
				
			}
		 
				    ?>&nbsp;</td>
		<td><?php echo h($brandName[$mobileList->brand_id]); ?>&nbsp;</td>
		<td><?php if(array_key_exists($mobileList->mobile_model_id,$modelName)){
            echo h($modelName[$mobileList->mobile_model_id]);
        }?>&nbsp;</td>
		<td><?php echo h($mobileList->imei); ?>&nbsp;</td>
		<td>
			<?php echo h($color[$mobileList->color]); ?>&nbsp;
		</td>
		<td><?php echo h($type[$mobileList->type]); ?>&nbsp;</td>
		<td><?php
		if(array_key_exists($mobileList->network_id,$networks)){
			echo h($networks[$mobileList->network_id]);
		}
		?>&nbsp;</td>
		<td><?php echo h($mobileList->grade); ?>&nbsp;</td>
		<td><?php echo $currentStatus; ?></td>
		<td class="actions">
			<?php 
				#echo $this->Html->link(__('View Detail'), array('action' => 'mobile_listing_per_reference', $referenceNumber['mobile_purchase_reference']));
				?>
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
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Stock by Reference'), array('action' => 'reference_number_listing')); ?></li>
		<li><?php # echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php # echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
	</ul>
</div>
