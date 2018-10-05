<div class="productReceipts index">
        <?php
		$kskId = $this->request->Session()->read('kiosk_id');
		if($this->request->Session()->read('Auth.User.group_id') == 1){ //ADMINISTRATORS
			$kiosk_id = 0;
		}else{
			$kiosk_id = $this->request->Session()->read('kiosk_id');
		}
        //pr($this->request->params);die;
		if($this->request->params['action'] == 'drAllInvoices'){
				//echo'hi';die;
                //$processedInvoiceLink = $this->Html->link(' (view processed invoices)',array('action'=>'dr_index'));//for special invoices
				$processedInvoiceLink =  $this->Html->link(' (view processed invoices)',
																		['action' => 'dr_index']
																	   );
        }else{
				   $processedInvoiceLink =  $this->Html->link(' (view processed invoices)',
																		['action' => 'index']
																	   );
                //$processedInvoiceLink = $this->Html->link(' (view processed invoices)',array('action'=>'index'));
        }
        ?>
		<?php
		$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
      $updateUrl = "/img/16_edit_page.png";
		?>
	<strong><?php echo __('<span style="color:red; font-size:20px;">Invoices</span>'). $processedInvoiceLink?></strong>
	<?php echo "<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('created','Date'); ?></th>
			<th><?php echo $this->Paginator->sort('id',"#Invoice"); ?></th>
			<th><?php echo $this->Paginator->sort('fname',"Customer"); ?></th>
			<th>Business</th>
			<th><?php echo $this->Paginator->sort('customer_id',"#Cust"); ?></th>
			<?php if($this->request->Session()->read('Auth.User.group_id') == 1){ //ADMINISTRATORS?>
			<th><?php echo $this->Paginator->sort('bill_cost'); ?></th>
			<?php } ?>
			<th><?php echo $this->Paginator->sort('bill_amount'); ?></th>
			<th><?php #echo $this->Paginator->sort('processed_by'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($productReceipts as $productReceipt): //pr($productReceipt);die;
		if(!empty($productReceipt->bill_cost)){
				$billCost = "&#163;".$productReceipt->bill_cost;
		}else{
				$billCost = "--";
		}
		
	?>
	<tr>
		<?php //echo $productReceipt->created;die;?>
		<td><?php echo date('d-m-Y',strtotime($productReceipt->created));//$productReceipt->created;//$this->Time->format('d-m-Y',$productReceipt->created,null,null); ?>&nbsp;</td>
		<td><?php echo h($productReceipt->id); ?>&nbsp;</td>
		<td><?php echo $productReceipt->fname; ?>&nbsp;</td>
		<td><?php 
			if(array_key_exists($productReceipt->customer_id,$customerBusiness)){
				echo $customerBusiness[$productReceipt->customer_id];
			}else{
				echo "--";
			}
		 ?>&nbsp;</td>
		<td><?php echo h($productReceipt->customer_id); ?>&nbsp;</td>
		<?php if($this->request->Session()->read('Auth.User.group_id') == 1){  //ADMINISTRATORS?>
			<td><?php 
					echo $billCost;
			?>
			</td>
		<?php } ?>
		<td>&#163;<?php echo $productReceipt->bill_amount;?></td> 
		<td><?php #echo $productReceipt['ProductReceipt']['processed_by']?>&nbsp;</td>
		<td class="actions">
			<?php #echo $this->Html->link(__('View'), array('action' => 'generate_receipt', $productReceipt['ProductReceipt']['id'])); ?>
			<?php #echo $this->Html->link(__('Edit'), array('action' => 'edit', $productReceipt['ProductReceipt']['id'])); ?>
			<?php #echo $this->Html->link(__('Update Payment'), array('action' => 'update_payment', $productReceipt['PaymentDetail']['id'])); ?>
			<?php #echo $this->Html->link(__('Delivery Note'), array('action' => 'delivery_note', $productReceipt['ProductReceipt']['id'])); ?>
			<?php #echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $productReceipt['ProductReceipt']['id']), array(), __('Are you sure you want to delete # %s?', $productReceipt['ProductReceipt']['id'])); ?>
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
		<li><?php #echo $this->Html->link(__('New Product Receipt'), array('action' => 'add')); ?></li>
		<li><?php //echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index')); ?> </li>
        <?php $loggedInUser = $this->request->Session()->read('Auth.User.username');
        if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering
									?>
        <li><?=$this->element('tempered_side_menu')?></li>
		<li><?php //echo $this->Html->link(__('ManXX Special Invoice'), array('controller' => 'product_receipts', 'action' => 'dr_index',1)); ?> </li>
        <?php }?>
		<li><?php echo $this->Html->link(__('View Invoices'), array('controller' => 'product_receipts', 'action' => 'all_invoices')); ?> </li>
		<li><?php echo $this->Html->link(__('View Sales'), array('controller' => 'kiosk_product_sales', 'action' => 'index')); ?> </li>
		<li><?php //echo $this->Html->link(__('Manxx Invoice'), array('controller' => 'product_receipts', 'action' => 'index',1)); ?> </li>
		
	</ul>
</div>
<script>
	function reset_search(){
		jQuery( "#datepicker1" ).val("");
		jQuery( "#datepicker2" ).val("");
		jQuery("#search_kw").val("");
	}
	jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
	});
</script>
<script>
$(function() {
  $( document ).tooltip({
   content: function () {
    return $(this).prop('title');
   }
  });
 });
</script>