<div class="productReceipts index">
	
	<?php
	$agent_id = $date_type = $start_date = $end_date = $textKeyword  = "";
	$date_type = "invoice";
	$webroot = $this->request->webroot."product-receipts/dr_search";?>
	<form id ="search_form" action="<?php echo $webroot;?>" method = 'get'>
	<fieldset>
		<legend>Payment Type</legend>
		<table style="
    margin-bottom: -21px;
    margin-top: -18px;
    margin-left: -2px;">
			<tr>
				
				<td><input type="radio" name="date_type" value="invoice" <?php if($date_type == "invoice"){echo "checked";}?>>invoice date</td>	
				<td><input type="radio" name="date_type" value="payment" <?php if($date_type == "payment"){echo "checked";}?>>payment date</td>
				<td>
					<?php echo $this->Html->link(
									'Missing Payment',
									['controller' => 'missingpayment', 'action' => 'dr-kiosk-payment']
								);
				?>
				</td>
			</tr>
			<tr>
				<td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date" style = "width:100px;margin-top: 5px;" value='<?php echo $start_date;?>' /><br/><div style="padding-top: 4px; margin-top: 12px;margin-left: -6px;"><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date" style = "width:100px" value='<?php echo $end_date;?>' /></div></td>
				<?php
				$payment_type = '';
				$invoice_detail = '';
				if(array_key_exists('payment_type',$this->request->query)){
					$payment_type = $this->request->query['payment_type'];
				}
				if(array_key_exists('invoice_detail',$this->request->query)){
					$invoice_detail = $this->request->query['invoice_detail'];
				}else{
					$invoice_detail = "receipt_number";
				}
				if(empty($this->request->query)){
					$payment_type = "All";
				}
				?>
				<td>
					<table>
					<tr>
						<td><input type="radio" name="payment_type" value="On Credit" <?php if($payment_type == "On Credit"){echo "checked";}?>>On credit</td>
						<td><input type="radio" name="payment_type" value="Cash" <?php if($payment_type == "Cash"){echo "checked";}?>>Cash</td>
						<td><input type="radio" name="payment_type" value="Card" <?php if($payment_type == "Card"){echo "checked";}?>>Card</td>
					</tr>
					<tr>
						<td><input type="radio" name="payment_type" value="Bank Transfer" <?php if($payment_type == "Bank Transfer"){echo "checked";}?>>Bank Tx</td>
						<td><input type="radio" name="payment_type" value="Cheque" <?php if($payment_type == "Cheque"){echo "checked";}?>>Cheque</td>
						<td><input type="radio" name="payment_type" value="All" <?php if($payment_type == "All"){echo "checked";}?>>All</td>
					</tr>
					</table>
				</td>
				<td>
					<table>
					<tr>
						<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
						<td>
							 <select name = "kiosk_id" id="search_kiosks" >
								<?php
								//echo $kiosk;die;
								if($kiosk == 0){ //onchange="this.form.submit()"
									$kiosk = 10000;
								}
								foreach($kiosks as $skey => $svalue){?>
									<option  value="<?php echo $skey;?>"<?php if($skey == $kiosk){echo "selected=selected";}?>><?php echo $svalue;?></option>
									<?php } ?>
							</select> 
							
						</td>
						<?php } ?>
						<td><input type="radio" name="invoice_detail" class = "radio1" value="receipt_number" <?php if($invoice_detail == "receipt_number"){echo "checked";}?>>Receipt number</td>
						<td><input type="radio" name="invoice_detail" class = "radio1" value="business" <?php if($invoice_detail == "business"){echo "checked";}?>>Business</td>
						<td><input type="radio" name="invoice_detail" class = "radio1" value="customer_id" <?php if($invoice_detail == "customer_id"){echo "checked";}?>>Customer number</td>
					</tr>
					<tr>
						<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
						<td>
							<select id="agent_id" name="agent_id">
								<?php foreach($agents as $ag_key => $ag_value){//onchange="this.form.submit()"?>
								
									<option  value="<?php echo $ag_key;?>"<?php if($ag_key == $agent_id){echo "selected=selected";}?>><?php echo $ag_value;?></option>
									<?php } ?>
							</select>
						</td>
						<?php } ?>
						<td><input type="text" name="search_kw" style="width: 100px;margin-right: -30px;" id = "search_kw" placeholder = "business,cst id or inv#" value="<?php echo $textKeyword;?>"></td>
						
						<td colspan="2" ><input type="submit" name="submit1" id="submit1" value="Search Payment" style="margin-left: 3px;">
						<input type='submit' name='reset' id="reset" value='Reset Search' style='padding:4px 8px;background:#dcdcdc;color:#333;border:1px solid #bbb;border-radius:4px;width: 120px;height: 30px;' onClick='reset_search();'/></td>
						
					</tr>
					</table>
				</td>
			</tr>
		</table>
	</fieldset>
	</form>
	
	
	<?php
    echo $this->Form->create('KioskTotalSale',array('id'=>'missingpaymentKioskPaymentForm','url'=>array('controller'=>'missingpayment','action'=>'dr_kiosk_payment')));
if(!empty($this->request->params['pass']) && array_key_exists(0,$this->request->params['pass'])){
    $selectedKiosk = $this->request->params['pass'][0];
}else{
    $selectedKiosk = 10000;
}
?>
<table width='100%'>
	<strong><?php echo __('<span style="color:red; font-size:20px;">'.$kiosks[$selectedKiosk].' Quotation (Missing Payment)</span>') ; ?></strong>
    <tr>
        <td><?php echo $this->Form->input('kiosk',array('options'=>$kiosks,'default'=>$selectedKiosk,'id' => 'kiosk_1'))?></td>
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
					echo $this->Html->link($viewImgHTML, array('controller' => 'product_receipts','action' => 'dr_generate_receipt', $productReceipt['id']), array('escapeTitle' => false, 'title' => 'View Invoice', 'alt' => 'View Invoice'));
				}else{
					echo $this->Html->link($viewImgHTML, array('controller' => 'product_receipts','action' => 'dr_generate_receipt', $productReceipt['id'],$kid), array('escapeTitle' => false, 'title' => 'View Invoice', 'alt' => 'View Invoice'));
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
					echo $this->Html->link($viewImgHTML, array('controller' => 'product_receipts','action' => 'dr_generate_receipt', $productReceipt['id']), array('escapeTitle' => false, 'title' => 'View Invoice', 'alt' => 'View Invoice'));
				}else{
					//echo'hi';
					//echo $kid;die;
					echo $this->Html->link($viewImgHTML, array('controller' => 'product_receipts','action' => 'dr_generate_receipt', $productReceipt['id'],$kid), array('escapeTitle' => false, 'title' => 'View Invoice', 'alt' => 'View Invoice'));
				}
			}elseif(!empty($sessKioskID)){
				if($sessKioskID == 10000){
					echo $this->Html->link($viewImgHTML, array('controller' => 'product_receipts','action' => 'dr_generate_receipt', $productReceipt['id']), array('escapeTitle' => false, 'title' => 'View Invoice', 'alt' => 'View Invoice'));
				}else{
					echo $this->Html->link($viewImgHTML, array('action' => 'generate_receipt', $productReceipt['id'],$sessKioskID), array('escapeTitle' => false, 'title' => 'View Invoice', 'alt' => 'View Invoice'));
				}
			}else{
				echo $this->Html->link($viewImgHTML, array('controller' => 'product_receipts','action' => 'dr_generate_receipt', $productReceipt['id']), array('escapeTitle' => false, 'title' => 'View Invoice', 'alt' => 'View Invoice'));
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
			echo "<li>".$this->Html->link(__('View Invoices'), array('controller' => 'product_receipts', 'action' => 'all_invoices'))."</li>";
		}elseif(array_key_exists('kiosk_id',$this->request->query)){
			$kid = $this->request->query['kiosk_id'];
			echo "<li>".$this->Html->link(__('View Invoices'), array('controller' => 'product_receipts','action' => 'all_invoices'))."</li>";
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
    $('#kiosk_1').change(function(){
        var kiskId = $('#kiosk_1').val();
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
  <script>
	function reset_search(){
		jQuery( "#datepicker1" ).val("");
		jQuery( "#datepicker2" ).val("");
		jQuery("#search_kw").val("");
	}
	jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy" });
	});
	
	$('#submit1').click(function(){
		var cnt = $("input[class = 'radio1']:checked").length;
			if(cnt<1)
			{
			    alert("Please check at least one Receipt number/Business/Customer number");
			    return false;
			}
	});
	$('#search_kiosks').change(function(){
		$.blockUI({ message: 'Loading ...' });
		document.getElementById("search_form").submit();
	});
	$('#agent_id').change(function(){
		$.blockUI({ message: 'Loading ...' });
		document.getElementById("search_form").submit();
	});
	$('#reset').change(function(){
		$.blockUI({ message: 'Loading ...' });
		document.getElementById("search_form").submit();
	});
	
</script>