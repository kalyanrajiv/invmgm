<div class="groups view">
<h2><?php echo __('Group'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?= $this->Number->format($group->id) ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($group->name); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
            <?php echo date('jS M, Y h:i A',strtotime($group->created));//$this->Time->format($group->created,'jS M, Y h:i A',  null,null);	 ?>
			 
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo date('jS M, Y h:i A',strtotime($group->modified));//$this->Time->format($group->modified,'jS M, Y h:i A',  null,null);	 ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Group'), array('action' => 'edit', $group->id)); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Group'), array('action' => 'delete', $group->id), array(), __('Are you sure you want to delete # %s?', $group['Group']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Groups'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Group'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Users'); ?></h3>
	<?php  if (!empty($group->users)): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('F Name'); ?></th>
		 
		<th><?php echo __('Email'); ?></th>
		<th><?php echo __('Username'); ?></th>
		 
		<th><?php echo __('Mobile'); ?></th>
		<th><?php echo __('Role'); ?></th>
		<th><?php echo __('Address 1'); ?></th>
		 
		<th><?php echo __('Active'); ?></th>
	 
		<th><?php echo __('Modified'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($group->users as $users): ?>
		<tr>
			<td><?= h($users->id) ?></td>
            <td><?= h($users->f_name) ?></td>
            <td><?= h($users->email) ?></td>
            <td><?= h($users->username) ?></td>
			 
			<td><?= h($users->mobile) ?></td>
            <td><?= h($users->role) ?></td>
			 <td><?= h($users->address_1) ?></td>
			 <td><?=  $activeOptions[$users->active] ?></td>
			 <td><?= h($users->modified) ?></td>
			 
			 
			 <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'Users', 'action' => 'view', $users->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'Users', 'action' => 'edit', $users->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Users', 'action' => 'delete', $users->id], ['confirm' => __('Are you sure you want to delete # {0}?', $users->id)]) ?>
                </td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
