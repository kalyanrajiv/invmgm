<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
        <li><?php echo $this->Html->link(__('Edit Kiosk'), array('action' => 'edit', $kiosk->id)); ?> </li>
        <li><?= $this->Form->postLink(__('Delete Kiosk'), ['action' => 'delete', $kiosk->id], ['confirm' => __('Are you sure you want to delete # {0}?', $kiosk->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Kiosks'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Kiosk'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Mobile Repairs'), ['controller' => 'MobileRepairs', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Mobile Repair'), ['controller' => 'MobileRepairs', 'action' => 'add']) ?> </li>
        <li><?php echo $this->Html->link(__('New Reorder Level'), array('controller' => 'reorder_levels', 'action' => 'add')); ?> </li>
        <li><?= $this->Html->link(__('List Reorder Levels'), ['controller' => 'ReorderLevels', 'action' => 'index']) ?> </li>
       
	</ul>
</div>


 <div class="kiosks view">
<h2><?php  echo __('Kiosk'); ?></h2>
	<dl>
		<dt>image</dt>
		<dd>
			<?php
									$imageDir = WWW_ROOT."logo".DS.$kiosk->id.DS.$kiosk->logo_image;
									if(!empty($kiosk->logo_image)){
										if(@readlink($imageDir) ||file_exists($imageDir)){
											$imageURL = "$siteBaseURL/logo/".$kiosk->id."/".$kiosk->logo_image;
										}else{
											$imageURL = "$siteBaseURL/thumb_no-image.png";
										}	
									}else{
										$imageURL = "$siteBaseURL/thumb_no-image.png";
									}
									
									
								echo $this->Html->image($imageURL, array('fullBase' => false,'width'=>'100px','height'=>'100px'));
							?>
		</dd>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo $this->Number->format($kiosk->id); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Code'); ?></dt>
		<dd>
			<?php echo h($kiosk->code); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($kiosk->name); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Email'); ?></dt>
		<dd>
			<?php echo h($kiosk->email); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Communication Password'); ?></dt>
		<dd>
			<?php echo h($kiosk->communication_password); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Address 1'); ?></dt>
		<dd>
			<?php echo h($kiosk->address_1); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Address 2'); ?></dt>
		<dd>
			<?php echo h($kiosk->address_2); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Postal Code'); ?></dt>
		<dd>
			<?php echo h($kiosk->zip); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Contact'); ?></dt>
		<dd>
			<?php echo h($kiosk->contact); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Rent'); ?></dt>
		<dd>
			<?php echo $CURRENCY_TYPE.$this->Number->format($kiosk->rent);   ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo $kiosk->status; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Kiosk Type'); ?></dt>
		<dd>
			<strong style='color:blue;'><?php echo $kiosk->kiosk_type ; ?></strong>
			&nbsp;
		</dd>
		<dt><?php echo __('Contract Type'); ?></dt>
		<dd>
			<?php echo $kiosk->contract_type; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Agreement From'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($kiosk->agreement_from));//$this->Time->format('jS M, Y g:i A',$kiosk->agreement_from,null,null);  ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Agreement From'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($kiosk->agreement_to));//$this->Time->format('jS M, Y g:i A',$kiosk->agreement_to,null,null);  ?>
			 
			&nbsp;
		</dd>
		<dt><?php echo __('Break Clause'); ?></dt>
		<dd>
			<?php echo $kiosk->break_clause; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Renewal Months'); ?></dt>
		<dd>
			<?php echo $kiosk->renewal_months ; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Renewal Weeks'); ?></dt>
		<dd>
			<?php echo $kiosk->renewal_weeks ; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($kiosk->created));//$this->Time->format('jS M, Y g:i A',$kiosk->created,null,null);
			 ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($kiosk->modified));//$this->Time->format('jS M, Y g:i A',$kiosk->modified,null,null);  ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="related">
	<h3><?php echo __('Related Mobile Repairs'); ?></h3>
	<?php if (!empty($kiosk->mobile_repairs)): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Repair Number'); ?></th>
		<th><?php echo __('Kiosk Id'); ?></th>
		<th><?php echo __('Customer Fname'); ?></th>
		<th><?php echo __('Customer Lname'); ?></th>
		<th><?php echo __('Customer Email'); ?></th>
		<th><?php echo __('Customer Address 1'); ?></th>
		<th><?php echo __('Customer Address 2'); ?></th>
		<th><?php echo __('Description'); ?></th>
		<th><?php echo __('Estimated Cost'); ?></th>
		<th><?php echo __('Actual Cost'); ?></th>
		<th><?php echo __('Zip'); ?></th>
		<th><?php echo __('Status'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($kiosk->mobile_repairs as $mobileRepair): ?>
		<tr>
            
			<td><?php echo h($mobileRepair->id); ?></td>
			<td><?php echo $mobileRepair->repair_number; ?></td>
			<td><?php echo $mobileRepair->kiosk_id; ?></td>
			<td><?php echo $mobileRepair->customer_fname; ?></td>
			<td><?php echo $mobileRepair->customer_lname; ?></td>
			<td><?php echo $mobileRepair->customer_email; ?></td>
			<td><?php echo $mobileRepair->customer_address_1; ?></td>
			<td><?php echo $mobileRepair->customer_address_2; ?></td>
			<td><?php echo $mobileRepair->description; ?></td>
			<td><?php echo $mobileRepair->estimated_cost;   ?></td>
			<td><?php echo $mobileRepair->actual_cost;   ?></td>
			<td><?php echo $mobileRepair->zip; ?></td>
			<td><?php echo $mobileRepair->status; ?></td>
			<td><?php echo date('jS M, Y g:i A',strtotime($mobileRepair->created));   ?></td>
			<td><?php echo date('jS M, Y g:i A',strtotime($mobileRepair->modified));   ?></td>
			 <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'MobileRepairs', 'action' => 'view', $mobileRepair->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'MobileRepairs', 'action' => 'edit', $mobileRepair->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'MobileRepairs', 'action' => 'delete', $mobileRepair->id], ['confirm' => __('Are you sure you want to delete # {0}?', $mobileRepair->id)]) ?>
                </td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Mobile Repair'), array('controller' => 'mobile_repairs', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
<div class="related">
	<h3><?php echo __('Related Reorder Levels'); ?></h3>
	<?php if (!empty($kiosk->reorder_levels)): ?>
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
	<?php foreach ($kiosk->reorder_levels as $reorderLevel): ?>
		<tr>
			<td><?php echo $reorderLevel->id; ?></td>
			<td><?php echo $reorderLevel->kiosk_id; ?></td>
			<td><?php echo $reorderLevel->product_id; ?></td>
			<td><?php echo $reorderLevel->reorder_level; ?></td>
			<td><?php echo $reorderLevel->status; ?></td>
			<td><?php echo $reorderLevel->created;   ?></td>
			<td><?php echo $reorderLevel->modified; ?></td>
			 <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'ReorderLevels', 'action' => 'view', $reorderLevel->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'ReorderLevels', 'action' => 'edit', $reorderLevel->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'ReorderLevels', 'action' => 'delete', $reorderLevel->id], ['confirm' => __('Are you sure you want to delete # {0}?', $reorderLevel->id)]) ?>
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