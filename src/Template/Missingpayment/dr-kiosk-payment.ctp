<div class="productReceipts index">
	<?php
    echo $this->Form->create('KioskTotalSale',array('id'=>'missingpaymentKioskPaymentForm','url'=>array('controller'=>'tests','action'=>'dr_kiosk_payment')));
if(!empty($this->request->params['pass']) && array_key_exists(0,$this->request->params['pass'])){
    $selectedKiosk = $this->request->params['pass'][0];
}else{
    $selectedKiosk = 10000;
}
//	 echo $this->Form->create('missingpayment',array('url'=>array('controller'=>'tests','action'=>'kiosk_payment','id'=>'missingpaymentKioskPaymentForm')));
//if(!empty($this->request->params['pass']) && array_key_exists(0,$this->request->params['pass'])){
//    $selectedKiosk = $this->request->params['pass'][0];
//}else{
//    $selectedKiosk = 10000;
//}
?>
<table width='100%'>
	<strong><?php echo __('<span style="color:red; font-size:20px;">'.$kiosks[$selectedKiosk].' (Missing Payment)</span>') ; ?></strong>
    <tr>
        <td><?php echo $this->Form->input('kiosk',array('options'=>$kiosks,'default'=>$selectedKiosk))?></td>
    </tr>
	
	 <?php echo $this->Form->end();?>
  
	 
	<tr>
		<th>Invoice Date</th>
			<th><?php echo $this->Paginator->sort('created','Payment Date'); ?></th>
			<th><?php echo $this->Paginator->sort('product_receipt_id',"#Invoice"); ?></th>
			<th>Customer</th>
			<th>Business</th>
			<th>#Cust</th>
			 
			<th><b>Payment</b></th>
		 
			<th><b>Total</b></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
		$tolalBillCosting = 0.0;
	//	 pr($productReceipts);
		foreach ($productReceipts as $key=>$productReceipt):
		 
	?>
	<tr>
		<td><?php 
				echo  date("d-m-Y",strtotime($productReceipt['created']));
		?></td>
		<td><?php
				echo date("d-m-Y",strtotime($productReceipt['created']));?>&nbsp;</td>
		<td><?php echo h($productReceipt['id']); ?>&nbsp;</td>
		<td><?php echo $productReceipt['fname']; ?>&nbsp;</td>
		<?php if(array_key_exists($productReceipt['customer_id'],$customerBusiness)){ ?>
		<td><?php echo $customerBusiness[$productReceipt['customer_id']]; ?>&nbsp;</td>
		<?php }else{
			echo "<td>"."--"."</td>";
		}
			?>
		
		<td><?php echo h($productReceipt['customer_id']); ?>&nbsp;</td>
       
		<td >&#163;<?php echo $totalpayment1 = $pramount[$productReceipt['id']] ;
				
				?></td>
		 
		<td><span ><?php echo "&#163;".number_format($productReceipt['bill_amount'],2); ?>&nbsp;</span></td>
		<td class="actions">
				<?php $viewImgHTML = $this->Html->image('view20X20.png', array('fullBase' => true, 'alt' => 'View Invoice', 'title' => 'View Invoice', 'border' => '0'));
			//pr($this->request);
			$sessKioskID = $this->request->Session()->read('kiosk_id');
			//echo $sessKioskID.'111';
			if(array_key_exists('0',$this->request->params['pass'])){
				$kid = $this->request->params['pass'][0];
				if($kid == 10000){
					echo $this->Html->link($viewImgHTML, array('controller' => 'product_receipts','action' => 'generate_receipt', $productReceipt['id']), array('escapeTitle' => false, 'title' => 'View Invoice', 'alt' => 'View Invoice'));
				}else{
					echo $this->Html->link($viewImgHTML, array('controller' => 'product_receipts','action' => 'generate_receipt', $productReceipt['id'],$kid), array('escapeTitle' => false, 'title' => 'View Invoice', 'alt' => 'View Invoice'));
				}	
			}elseif(array_key_exists('kiosk_id',$this->request->query) || array_key_exists('kiosk-id',$this->request->query )){
				//pr($this->request);die;
				if(array_key_exists('kiosk_id',$this->request->query)){
					$kid = $this->request->query['kiosk_id'];
				}
				if(array_key_exists('kiosk-id',$this->request->query)){
					$kid = $this->request->query['kiosk-id'];
				}
				if($kid == 10000){
					echo $this->Html->link($viewImgHTML, array('controller' => 'product_receipts','action' => 'generate_receipt', $productReceipt['id']), array('escapeTitle' => false, 'title' => 'View Invoice', 'alt' => 'View Invoice'));
				}else{
					//echo'hi';
					//echo $kid;die;
					echo $this->Html->link($viewImgHTML, array('controller' => 'product_receipts','action' => 'generate_receipt', $productReceipt['id'],$kid), array('escapeTitle' => false, 'title' => 'View Invoice', 'alt' => 'View Invoice'));
				}
			}elseif(!empty($sessKioskID)){
				if($sessKioskID == 10000){
					echo $this->Html->link($viewImgHTML, array('controller' => 'product_receipts','action' => 'generate_receipt', $productReceipt['id']), array('escapeTitle' => false, 'title' => 'View Invoice', 'alt' => 'View Invoice'));
				}else{
					echo $this->Html->link($viewImgHTML, array('action' => 'generate_receipt', $productReceipt['id'],$sessKioskID), array('escapeTitle' => false, 'title' => 'View Invoice', 'alt' => 'View Invoice'));
				}
			}else{
				echo $this->Html->link($viewImgHTML, array('controller' => 'product_receipts','action' => 'generate_receipt', $productReceipt['id']), array('escapeTitle' => false, 'title' => 'View Invoice', 'alt' => 'View Invoice'));
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
		<!--<li><?php echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index')); ?> </li>-->
        <?php
        $loggedInUser = $this->request->session()->read('Auth.User.username');
        if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering
									?>
        <li><?=$this->element('tempered_side_menu')?></li>
		<!--<li><?php echo $this->Html->link(__('ManXX Quotation'), array('controller' => 'product_receipts', 'action' => 'dr_index',1)); ?> </li>-->
        <?php }
		
		if(array_key_exists('0',$this->request->params['pass'])){
			$kid = $this->request->params['pass'][0];
			echo "<li>".$this->Html->link(__('View Invoices'), array('controller' => 'product_receipts', 'action' => 'all_invoices',$kid))."</li>";
		}elseif(array_key_exists('kiosk_id',$this->request->query)){
			$kid = $this->request->query['kiosk_id'];
			echo "<li>".$this->Html->link(__('View Invoices'), array('controller' => 'product_receipts','action' => 'all_invoices',$kid))."</li>";
		}else{ 
			echo "<li>".$this->Html->link(__('View Invoices'), array('controller' => 'product_receipts','action' => 'all_invoices'))."</li>";
		}
		?>
		
		<li><?php echo $this->Html->link(__('View Sales'), array('controller' => 'kiosk_product_sales', 'action' => 'index')); ?> </li>
		<!--<li><?php echo $this->Html->link(__('Manxx Invoice'), array('controller' => 'product_receipts', 'action' => 'index',1)); ?> </li>-->
		<!--<li><?php echo $this->Html->link(__('New Customer'), array('controller' => 'customers', 'action' => 'add')); ?> </li>-->
		<!--<li><?php echo $this->Html->link(__('List Kiosk Product Sales'), array('controller' => 'kiosk_product_sales', 'action' => 'index')); ?> </li>-->
		<!--<li><?php echo $this->Html->link(__('New Kiosk Product Sale'), array('controller' => 'kiosk_product_sales', 'action' => 'add')); ?> </li>-->
	</ul>
</div>
 <script>
    $('#kiosk').change(function(){
        var kiskId = $('#kiosk').val();
        // alert(kiskId);
        if (document.getElementById('missingpaymentKioskPaymentForm')) {
            var action = $('#missingpaymentKioskPaymentForm').attr('action');
            var formid = '#missingpaymentKioskPaymentForm';
        } else {
            var action = $('#missingpaymentKioskPaymentForm').attr('action');
            var formid = '#missingpaymentKioskPaymentForm';
        }
            var newAction = action + '/' + kiskId;
            $(formid).attr('action',newAction);
        this.form.submit();
    });
</script>
  