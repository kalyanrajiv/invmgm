 <?php  //pr($mobileModel);die;
 use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
 ?>
<div class="mobileModels view">
<h2><?php
        $statusOptions = Configure::read('active');
        $this->set(compact('statusOptions'));
        echo __('Mobile Model'); ?></h2>
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
		<li><?= $this->Html->link(__('Edit Mobile Model'), ['action' => 'edit', $mobileModel->id],['style'=>'width: 122px;']) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Mobile Model'), ['action' => 'delete', $mobileModel->id], ['confirm' => __('Are you sure you want to delete # {0}?', $mobileModel->id),'style'=>'width: 122px;']) ?> </li>
		  <li><?= $this->Html->link(__('List Mobile Models'), ['action' => 'index'],['style'=>'width: 122px;']) ?> </li>
		 <li><?= $this->Html->link(__('New Mobile Model'), ['action' => 'add'],['style'=>'width: 122px;']) ?> </li>
        <li><?= $this->Html->link(__('List Brands'), ['controller' => 'Brands', 'action' => 'index'],['style'=>'width: 122px;']) ?> </li>
        <li><?= $this->Html->link(__('New Brand'), ['controller' => 'Brands', 'action' => 'add'],['style'=>'width: 122px;']) ?> </li>
		<li><?php echo $this->Html->link(__('List Mobile <br/>Unlock Prices'), ['controller' => 'mobile-unlock-prices', 'action' => 'index'],['escape' => false,'style'=>'width: 122px;']); ?> </li>
		<li><?php echo $this->Html->link(__('New Mobile <br/>Unlock Price'), ['controller' => 'mobile-unlock-prices', 'action' => 'add'],['escape' => false,'style'=>'width: 122px;']); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Mobile Unlock Prices');?></h3>
	<?php if (!empty($mobileModel->mobile_unlock_prices)): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Brand Id'); ?></th>
		<th><?php echo __('Mobile Model Id'); ?></th>
		<th><?php echo __('Network Id'); ?></th>
		<th><?php echo __('Unlocking Price'); ?></th>
		<th><?php echo __('Unlocking Days'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	 <?php foreach ($mobileModel->mobile_unlock_prices as $mobileUnlockPrices): ?>
            <tr>
                <td><?= h($mobileUnlockPrices->id) ?></td>
                <td><?= h($mobileUnlockPrices->brand_id) ?></td>
                <td><?= h($mobileUnlockPrices->mobile_model_id) ?></td>
                <td><?= h($mobileUnlockPrices->network_id) ?></td>
              
                <td><?= h($mobileUnlockPrices->unlocking_price) ?></td>
                <td><?= h($mobileUnlockPrices->unlocking_days) ?></td>
               
                 
                <td><?= date('d-m-y g:i A',strtotime($mobileUnlockPrices->created));  ?></td>
                <td><?= date('d-m-y g:i A',strtotime($mobileUnlockPrices->modified));  ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'MobileUnlockPrices', 'action' => 'view', $mobileUnlockPrices->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'MobileUnlockPrices', 'action' => 'edit', $mobileUnlockPrices->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'MobileUnlockPrices', 'action' => 'delete', $mobileUnlockPrices->id], ['confirm' => __('Are you sure you want to delete # {0}?', $mobileUnlockPrices->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
	 
	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Mobile <br/>Unlock Price'), ['controller' => 'mobile-unlock-prices', 'action' => 'add'],['escape' => false,'style'=>'width: 122px;']); ?> </li>
		</ul>
	</div>
</div>
