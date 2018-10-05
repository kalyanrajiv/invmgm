<div class="functioncondition form">
  <h2><?php echo __('Function Tests'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<table cellpadding="0" cellspacing="0">
	 <thead>
		  <tr>
				  <th>Id</th>
				  <th>Function Test</th>
				  <th>Description</th>
				  <th>Status</th>
				  <th>Created</th>
				  <th>Modified</th>
					<th>Actions</th>
		  </tr>
	 </thead>
	<tbody>
	<?php foreach ($functionconditions as $functioncondition):
	 ?>
	<tr>
	 <td><?php echo h($functioncondition['id']); ?>&nbsp;</td>
		<td><?php echo h($functioncondition['function_condition']); ?>&nbsp;</td>
		<td><?php echo  $functioncondition['description'] ; ?>&nbsp;</td>
		<td><?php echo $statusOptions[$functioncondition['status']]; ?>&nbsp;</td>
		<td><?php echo date('jS M, Y g:i A',strtotime($functioncondition['created']));//$this->Time->format($functioncondition['created'] ,'','dd.mm.yy',null); ?>&nbsp;</td>
		<td><?php echo date('jS M, Y g:i A',strtotime($functioncondition['modified']));//$this->Time->format('jS M, Y g:i A',$functioncondition['modified'] ,null,null); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $functioncondition['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $functioncondition['id'])); ?>
			<?php //echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $functioncondition['FunctionCondition']['id']), array(), __('Are you sure you want to delete # %s?', $functioncondition['FunctionCondition']['id'])); ?>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
	</table>
	 
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Function Test'), array('action' => 'add')); ?></li>
	</ul>
</div>
 