<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<?php
	$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
      $updateUrl = "/img/16_edit_page.png";
?>
<div class="invoiceOrders index">
	<h2><?php echo __('Performas')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>&nbsp;<a href='<?php echo $this->request->webroot;?>InvoiceOrders/export/' target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png',array('fullBase' => true));?></a>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	</h2> 
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id','Performa Id'); ?></th>
			<th><?php echo $this->Paginator->sort('created','Date'); ?></th>
			<th><?php echo $this->Paginator->sort('customer_id','Business'); ?></th>
			<th><?php echo $this->Paginator->sort('fname','Name'); ?></th>
			<th><?php echo $this->Paginator->sort('user_id','Created by'); ?></th>
			<th><?php echo $this->Paginator->sort('amount'); ?></th>
			<th>Action</th>
	</tr>
	</thead>
	<tbody><?php //pr($invoiceOrders);?>
	<?php foreach ($invoiceOrders as $invoiceOrder):?>
	<tr>
		<td><?php echo h($invoiceOrder['id']); ?>&nbsp;</td>
		<td><?php echo date('jS M, Y h:i A',strtotime($invoiceOrder['created']));//$this->Time->format('jS M, Y h:i A', $invoiceOrder['created'],null,null); ?>&nbsp;</td>
		<td><?php
			if(array_key_exists($invoiceOrder['customer_id'],$bussArr)){
				if($bussArr[$invoiceOrder['customer_id']] == ""){
					if(array_key_exists($invoiceOrder['customer_id'],$nameArr)){
						echo $nameArr[$invoiceOrder['customer_id']];
					}else{
						echo "";
					}
				}else{
					echo $bussArr[$invoiceOrder['customer_id']];
				}
			}else{
				if(array_key_exists($invoiceOrder['customer_id'],$nameArr)){
						echo $nameArr[$invoiceOrder['customer_id']];
					}else{
						echo "";
					}
			}
			//echo h($invoiceOrder['InvoiceOrder']['customer_id']);
			
		?>&nbsp;</td>
		<td><?php echo h($invoiceOrder['fname']); ?>&nbsp;</td>
		<td><?php echo $users[$invoiceOrder['user_id']]; ?></td>
		<td><?php echo $CURRENCY_TYPE.$invoiceOrder['amount']; ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $invoiceOrder['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('controller' => "home",'action' => 'edit-bulk-performa', $invoiceOrder['id'],$invoiceOrder['customer_id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $invoiceOrder['id']),
							 array('escapeTitle' => false, 'title' => 'Delete'), __('Are you sure you want to delete # %s?', $invoiceOrder['InvoiceOrder']['id'])); ?>
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
		<li><?php echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index')); ?> </li>
	</ul>
</div>
<script>
$(function() {
  $( document ).tooltip({
   content: function () {
    return $(this).prop('title');
   }
  });
 });
</script>