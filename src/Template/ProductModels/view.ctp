 <?php  //pr($mobileModel);die;
 use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
 ?>
<div class="mobileModels view">
<h2><?php
        $statusOptions = Configure::read('active');
        $this->set(compact('statusOptions'));
        echo __('Product Model'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($mobileModel->id); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Brand'); ?></dt>
		<dd>
			<?php
            echo $this->Html->link($mobileModel['brand']['brand'], array('controller' => 'Brands', 'action' => 'view', $mobileModel['brand']['id']));
           // echo $mobileModel->has('brand') ? $this->Html->link($mobileModel->brand->id, ['controller' => 'Brands', 'action' => 'view', $mobileModel->brand->id]) : ''; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Model'); ?></dt>
		<dd>
			<?php echo h($mobileModel->model); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Brief Description'); ?></dt>
		<dd>
			<?php echo h($mobileModel->brief_description); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo $statusOptions[$mobileModel->status] ; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo date('d-m-y g:i A',strtotime($mobileModel->created));  ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo date('d-m-y g:i A',strtotime($mobileModel->modified));  ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?= $this->Html->link(__('Edit product Model'), ['action' => 'edit', $mobileModel->id],['style'=>'width: 122px;']) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Product Model'), ['action' => 'delete', $mobileModel->id], ['confirm' => __('Are you sure you want to delete # {0}?', $mobileModel->id),'style'=>'width: 122px;']) ?> </li>
		  <li><?= $this->Html->link(__('List Product Models'), ['action' => 'index'],['style'=>'width: 122px;']) ?> </li>
		 <li><?= $this->Html->link(__('New Mobile Model'), ['action' => 'add'],['style'=>'width: 122px;']) ?> </li>
        <li><?= $this->Html->link(__('List Brands'), ['controller' => 'Brands', 'action' => 'index'],['style'=>'width: 122px;']) ?> </li>
        <li><?= $this->Html->link(__('New Brand'), ['controller' => 'Brands', 'action' => 'add'],['style'=>'width: 122px;']) ?> </li>
		 
	</ul>
</div>
 
