<div class="mobilecondition form">
  <h2><?php echo __('Mobile Conditions'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<table cellpadding="0" cellspacing="0">
	 <thead>
		  <tr>
				  <th>Id</th>
				  <th>Mobile Conditions</th>
				  <th>Description</th>
				  <th>Status</th>
				  <th>Created</th>
				  <th>Modified</th>
					<th>Actions</th>
		  </tr>
	 </thead>
	<tbody>
        <?php //pr($mobileconditions);die;?>
	<?php foreach ($mobileconditions as $mobilecondition):
    //pr($mobilecondition);die;
    //pr($statusOptions);die;
	 ?>
	<tr>
	 <td><?php echo h($mobilecondition['id']); ?>&nbsp;</td>
		<td><?php echo h($mobilecondition['mobile_condition']); ?>&nbsp;</td>
		<td><?php echo  $mobilecondition['description'] ; ?>&nbsp;</td>
		<td><?php echo $statusOptions[$mobilecondition['status']]; ?>&nbsp;</td>
		 <td><?php echo date('jS M, Y g:i A',strtotime($mobilecondition['created']));//$this->Time->format('jS M, Y g:i A',$mobilecondition['created'] ,null,null); ?>&nbsp;</td>
		<td><?php echo date('jS M, Y g:i A',strtotime($mobilecondition['modified']));//$this->Time->format('jS M, Y g:i A',$mobilecondition['modified'] ,null,null); ?>&nbsp;</td>
        
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $mobilecondition['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $mobilecondition['id'])); ?>
			<?php //echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $mobilecondition['MobileCondition']['id']), array(), __('Are you sure you want to delete # %s?', $mobilecondition['MobileCondition']['id'])); ?>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
	</table>
	 
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Mobile Condition'), array('action' => 'add')); ?></li>
	</ul>
</div>
 