<div class="productReceipts index">
 
      <strong><?php echo __('<span style="color:red; font-size:20px;">'.$kiosks[$kiosk_id].' Invoices(missing payment)</span>') ; ?></strong>
	<table cellpadding="0" cellspacing="0">
	<thead>
		 
          
			 
            
		 
	<tr>
		<th>Invoice Date</th>
			<th><?php echo $this->Paginator->sort('created','Payment Date'); ?></th>
			<th><?php echo $this->Paginator->sort('product_receipt_id',"#Invoice"); ?></th>
			<th>Customer</th>
			<th>Business</th>
			<th>#Cust</th>
            
			<th><span style='float: right;'>Payment</span></th>
			 
			<th><span style='float: left;'>Total</span></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
		$tolalBillCosting = 0.0;
		// pr($productReceipts);die;
		foreach ($productReceipts as $key=>$productReceipt):
		 
	?>
	<tr>
		<td><?php 
				echo  date("d-m-Y",strtotime($createdArr[$productReceipt['id']]));
		?></td>
		<td><?php
				echo date("d-m-Y",strtotime($productReceipt['created']));?>&nbsp;</td>
		<td><?php echo h($productReceipt['id']); ?>&nbsp;</td>
		<td><?php if(!empty($productReceipt['fname'])){
            echo $productReceipt['fname'];
        }else{
            echo "--";
            }?>&nbsp;</td>
		<?php if(array_key_exists($productReceipt['customer_id'],$customerBusiness)){ ?>
		<td><?php echo $customerBusiness[$productReceipt['customer_id']]; ?>&nbsp;</td>
		<?php }else{
			echo "<td>"."--"."</td>";
		}
			?>
		
		<td><?php echo h($productReceipt['customer_id']); ?>&nbsp;</td>
       
		<td style="padding-left: 24px;">&#163;<?php   echo $totalpayment1 = array_sum($pramount[$productReceipt['id']])  ;
				
				?></td>
		 
		<td><span style="padding-left: 24px;"><?php echo "&#163;".number_format($productReceipt['bill_amount'],2); ?>&nbsp;</span></td>
		<td class="actions">
			<?php if(array_key_exists('0',$this->request->params['pass'])){
						$kid = $this->request->params['pass'][0];
						$viewImgHTML = $this->Html->image('view20X20.png', array('fullBase' => true, 'alt' => 'View Invoice', 'title' => 'View Invoice', 'border' => '0'));
						echo $this->Html->link($viewImgHTML, array('controller' => 'product_receipts','action' => 'dr_generate_receipt', $productReceipt['id'],$kid), array('escapeTitle' => false, 'title' => 'View Invoice', 'alt' => 'View Invoice','target' => '_blank'));
						//echo $this->Html->link(__('View'), array('action' => 'dr_generate_receipt', $productReceipt['ProductReceipt']['id'],$kid));
				}elseif(array_key_exists('kiosk_id',$this->request->query)){
					$kid = $this->request->query['kiosk_id'];
					$viewImgHTML = $this->Html->image('view20X20.png', array('fullBase' => true, 'alt' => 'View Invoice', 'title' => 'View Invoice', 'border' => '0'));
					echo $this->Html->link($viewImgHTML, array('controller' => 'product_receipts','action' => 'dr_generate_receipt', $productReceipt['id'],$kid), array('escapeTitle' => false, 'title' => 'View Invoice', 'alt' => 'View Invoice','target' => '_blank'));
					//echo $this->Html->link(__('View'), array('action' => 'dr_generate_receipt', $productReceipt['ProductReceipt']['id'],$kid));
				}else{
					$viewImgHTML = $this->Html->image('view20X20.png', array('fullBase' => true, 'alt' => 'View Invoice', 'title' => 'View Invoice', 'border' => '0'));
					echo $this->Html->link($viewImgHTML, array('controller' => 'product_receipts','action' => 'dr_generate_receipt', $productReceipt['id']), array('escapeTitle' => false, 'title' => 'View Invoice', 'alt' => 'View Invoice','target' => '_blank'));
					//echo $this->Html->link(__('View'), array('action' => 'dr_generate_receipt', $productReceipt['ProductReceipt']['id']));
				}
				?>
		 </td>
	</tr>
<?php 		 
	endforeach; ?>
 
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
        <?php
        $loggedInUser = $this->request->session()->read('Auth.User.username');
        if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering
		?>
        <li><?php echo$this->element('tempered_side_menu')?></li>
		<li><?php //echo $this->Html->link(__('ManXX Special Invoice'), array('controller' => 'product_receipts', 'action' => 'dr_index',1)); ?> </li>
        <?php }?>
		<li><?php echo $this->Html->link(__('View Invoices'), array('controller' => 'product_receipts', 'action' => 'all_invoices')); ?> </li>
		<li><?php echo $this->Html->link(__('View Sales'), array('controller' => 'kiosk_product_sales', 'action' => 'index')); ?> </li>
		<li><?php //echo $this->Html->link(__('Manxx Invoice'), array('controller' => 'product_receipts', 'action' => 'index',1)); ?> </li>
		
	</ul>
</div>
 