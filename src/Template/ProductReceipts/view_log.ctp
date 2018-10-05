<div class="productReceipts index">
	<table cellpadding="0" cellspacing="0">
	<thead>
        <?php if(empty($log_table_data)){ 
        echo "No Data Found";
        }?>
        <?php if(!empty($log_table_data)){ ?>
	<tr>
			<th>Old Payment</br>Method</th>
			<th>New Payment</br>Method</th>
			<th>Action By</th>
			<th>Memo</th>
            <th>Action Time(created)</th>
            <th>Action Time(modified)</th>
	</tr>
	</thead>
	<tbody>
	<?php
		foreach($log_table_data as $key => $value){
	?>
	<tr>
        <td><?php echo $value->old_pmt_method; ?></td>
        <td><?php echo $value->pmt_method; ?></td>
        <td><?php echo $users[$value->user_id]; ?></td>
        <td><?php echo $value->memo; ?></td>
        <td><?php echo date("d-m-Y h:i",strtotime($value->created));?></td>
        <td><?php echo date("d-m-Y h:i",strtotime($value->modified));?></td>
    </tr>
<?php
        }}
	 ?>
	
	</tbody>
	</table>
	<p>
	
</div>

<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php #echo $this->Html->link(__('New Product Receipt'), array('action' => 'add')); ?></li>
		<li><?php //echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index')); ?> </li>
        <?php
        $loggedInUser = $this->request->session()->read('Auth.User.username');
        if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering?>
        <li><?php echo$this->element('tempered_side_menu')?></li>
		<li><?php //echo $this->Html->link(__('ManXX Special Invoice'), array('controller' => 'product_receipts', 'action' => 'dr_index',1)); ?> </li>
        <?php }?>
		<li><?php echo $this->Html->link(__('View Invoices'), array('controller' => 'product_receipts', 'action' => 'all_invoices')); ?> </li>
		<li><?php echo $this->Html->link(__('View Sales'), array('controller' => 'kiosk_product_sales', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('View Inv Pmt <br/>Logs'), array('controller' => 'product_receipts', 'action' => 'invoicePaymentClearness'),array('escape'=>false)); ?> </li>
		<li><?php echo $this->Html->link(__('View Quot Pmt <br/>Logs'), array('controller' => 'product_receipts', 'action' => 'quotationPaymentClearness'),array('escape'=>false)); ?> </li>
		
	</ul>
</div>
