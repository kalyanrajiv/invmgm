 <div class="users view">
<h2><?php echo __('User'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($user->id); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('F Name'); ?></dt>
		<dd>
			<?php echo h($user->f_name); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('L Name'); ?></dt>
		<dd>
			<?php echo h($user->l_name); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Email'); ?></dt>
		<dd>
			<?php echo h($user->email); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Username'); ?></dt>
		<dd>
			<?php echo h($user->username); ?>
			&nbsp;
		</dd>		
		<dt><?php echo __('Group'); ?></dt>
		<dd>
             <?= $user->has('group') ? $this->Html->link($user->group->name, ['controller' => 'Groups', 'action' => 'view', $user->group->id]) : '' ?> 
			
			&nbsp;
		</dd>
		<dt><?php echo __('Mobile'); ?></dt>
		<dd>
			<?php echo h($user->mobile); ?>
			&nbsp;
		</dd>		
		<dt><?php echo __('Address 1'); ?></dt>
		<dd>
			<?php echo h($user->address_1); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Address 2'); ?></dt>
		<dd>
			<?php echo h($user->address_2); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Date Of Birth'); ?></dt>
		<dd>
			<?php if(array_key_exists('profiles[0]',$user)){
                echo $user->profiles[0]->date_of_birth;
            }
              ?>
			&nbsp;
		</dd>
		</dd>
		<dt><?php echo __('National Insurance'); ?></dt>
		<dd>
			<?php   if(array_key_exists('profiles[0]',$user)){
                echo  $user->profiles[0]->national_insurance;
            }
           //pr($user1);//echo h($profiles->national_insurance); ?>
			&nbsp;
		</dd>
		</dd>
		<dt><?php echo __('Visa Type'); ?></dt>
		<dd>
			<?php if(array_key_exists('profiles[0]',$user)){
                echo  $user->profiles[0]->visa_type;
            }?>
			&nbsp;
		</dd>
		</dd>
		<dt><?php echo __('Visa Expiry Date'); ?></dt>
		<dd>
			<?php if(array_key_exists('profiles[0]',$user)){
                echo $user->profiles[0]->visa_expiry_date;
            }?>
			&nbsp;
		</dd>
		</dd>
		<dt><?php echo __('Memo'); ?></dt>
		<dd>
			<?php
            if(array_key_exists('profiles[0]',$user)){
                echo $user->profiles[0]->memo;
                }
                ?>
			&nbsp;
		</dd>
		<?php //code added by inder, starts from here ?>
		<dt><?php echo __('Document 1'); ?></dt>
		<dd>
			 
			&nbsp;
		</dd>
		<dt><?php echo __('Document 2'); ?></dt>
		<dd>
			 
			&nbsp;
		</dd>
		<dt><?php echo __('Document 3'); ?></dt>
		<dd>
			 
			&nbsp;
		</dd>
		<dt><?php echo __('Document 4'); ?></dt>
		<dd>
		 
			 
			&nbsp;
		</dd>
		<dt><?php echo __('Document 5'); ?></dt>
		<dd>
			 
			&nbsp;
		</dd>
		<dt><?php echo __('Active'); ?></dt>
		<dd>
			<?php echo $user->active; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo $user->created;   ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo $user->modified; ?>
			&nbsp;
		</dd>
	</dl>
</div>
 <div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit User'), array('action' => 'edit', $user->id)); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete User'), array('action' => 'delete', $user->id), array(), __('Are you sure you want to delete # %s?', $user->id)); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Groups'), array('controller' => 'groups', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Group'), array('controller' => 'groups', 'action' => 'add')); ?> </li>
	</ul>
</div>
 