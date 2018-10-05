<div class="faultycondition form">
  <h2><?php echo __('Faulty Conditions'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<table cellpadding="0" cellspacing="0">
	 <thead>
		  <tr>
				  <th>Id</th>
				  <th>Faulty Conditions</th>
				  <th>Description</th>
				  <th>Status</th>
				  <th>Created</th>
				  <th>Modified</th
		  </tr>
	 </thead>
	<tbody>
	<?php foreach ($faultyconditions as $faultycondition):
	 ?>
	<tr>
	 <td><?php echo h($faultycondition['id']); ?>&nbsp;</td>
		<td><?php echo h($faultycondition['faulty_condition']); ?>&nbsp;</td>
		<td><?php echo  $faultycondition['description'] ; ?>&nbsp;</td>
		<td><?php echo $statusOptions[$faultycondition['status']]; ?>&nbsp;</td>
		<td><?php echo date('jS M, Y g:i A',strtotime($faultycondition['created'])); ?>&nbsp;</td>
		<td><?php echo date('jS M, Y g:i A',strtotime($faultycondition['modified'])); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $faultycondition['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $faultycondition['id'])); ?>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
	</table>
	 
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Faulty <br/>Condition'), array('action' => 'add'),array('escape'=>false)); ?></li>
	</ul>
</div>