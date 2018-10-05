<div class="productReceipts index">
<?php
//pr($main_kiosk_product_recipt);
$date_type = $agent_id = $start_date = $end_date  = $textKeyword = "";
$date_type = "invoice";

$webroot = $this->request->webroot."product-receipts/search"; //FULL_BASE_URL.?>
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
				<td><?php echo $this->Html->link(
									'Missing Payment',
									['controller' => 'missingpayment', 'action' => 'kiosk-payment']
								);
				?>
</td>
			</tr>
			<tr>
				<td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date" style = "width:100px;margin-top: 5px;" value='<?php echo $start_date;?>' /><br/><div style="padding-top: 4px; margin-top: 12px;margin-left: -6px;"><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date" style = "width:100px" value='<?php echo $end_date;?>' /></div></td>
				<?php
				$payment_type = '';
				//$invoice_detail = '';
				if(array_key_exists('payment_type',$this->request->query)){
					$payment_type = $this->request->query['payment_type'];
				}else{
                    $payment_type = "All";
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
							 <?php //echo $kiosk_id;die;  ?>
							 <select id="search_kiosks" name = "kiosk_id"  >
								<?php foreach($kiosks as $skey => $svalue){//onchange="this.form.submit()"?>
								
									<option  value="<?php echo $skey;?>"<?php if($skey == $kiosk_id){echo "selected=selected";}?>><?php echo $svalue;?></option>
									<?php } ?>
							</select> 
							
						</td>
						<?php }?>
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
						
						<td colspan="2" ><input type="submit" id="submit1" value="Search Payment" style="margin-left: 3px;">
						<input type='submit' name='reset' id="reset" value='Reset' style='padding:4px 8px;background:#dcdcdc;color:#333;border:1px solid #bbb;border-radius:4px;width: 120px;height: 30px;'/></td>
						
					</tr>
					</table>
				</td>
			</tr>
		</table>
	</fieldset>
	</form>

	<?php
    echo $this->Form->create('KioskTotalSale',array('id'=>'missingpaymentKioskPaymentForm','url'=>array('controller'=>'missingpayment','action'=>'kiosk_payment')));
if(!empty($this->request->params['pass']) && array_key_exists(0,$this->request->params['pass'])){
    $selectedKiosk = $this->request->params['pass'][0];
}else{
    $selectedKiosk = 10000;
}
?>

<div>
<table width='100%'>
	<strong><?php echo __('<span style="color:red; font-size:20px;">'.$kiosks[$selectedKiosk].' Invoice (Missing Payment)</span>') ; ?></strong>
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
</div>
<div>
<table>
	<strong><?php echo __('<span style="color:red; font-size:20px;">'.$kiosks[$selectedKiosk].' Invoice (Missing Recipt table Entry)</span>') ; ?></strong>
	<th>Invoice Date</th>
			<th><?php echo 'created'; ?></th>
			<th><?php echo 'product_receipt_id'; ?></th>
			<th>Customer</th>
			<th>Business</th>
			<th>#Cust</th>
			 
			<th><b>Payment</b></th>
		 
			<th><b>Total</b></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	<?php foreach($main_kiosk_product_recipt as $k_key => $k_value){?>
				<tr>
					<td><a href="##" >##</a></td>
					<td>--</td>
					<td><?php echo $k_value['product_receipt_id']; ?></td>
					<td>--</td>
					<td>--</td>
					<td>--</td>
					<td><?php echo $totalpayment1 = $pramount[$k_value['product_receipt_id']] ;
				?></td>
					<td><?php echo $totalpayment1 = $pramount[$k_value['product_receipt_id']] ;
				?></td>
					<td>
					<?php
					$editImgHTML = $this->Html->image('16_edit_page.png', array('fullBase' => true, 'alt' => 'Edit', 'title' => 'Edit', 'border' => '0'));
					echo $this->Html->link($editImgHTML, array('action' => 'edit', $k_value['product_receipt_id']), array('escapeTitle' => false, 'title' => 'Edit', 'alt' => 'Edit')); ?>
					</td>
				</tr>
	<?php } ?>
</table>
</div>	
</div>

 
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php #echo $this->Html->link(__('New Product Receipt'), array('action' => 'add')); ?></li>
		
        <?php
        $loggedInUser = $this->request->session()->read('Auth.User.username');
        if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right 
		?>
        <li><?=$this->element('tempered_side_menu')?></li>
		
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
		
		
	</ul>
</div>
 <script>
    $('#kiosk_1').change(function(){
		$.blockUI({ message: 'Loading ...' });
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
	
	$('#submit1').click(function(){
		var cnt = $("input[class = 'radio1']:checked").length;
			if(cnt<1)
			{
			    alert("Please check at least one Receipt number/Business/Customer number");
			    return false;
			}
	});
</script>