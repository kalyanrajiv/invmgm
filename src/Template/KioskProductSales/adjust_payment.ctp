<div class="invoiceOrders form">
<?php echo $this->Form->create('UpdatePayment'); ?>
	<fieldset>
		<legend>Prev Amount Modified to  <?=$final_price;?></legend>
		<table>
			<tr>Total Amt: <?php echo $final_price;?></tr>
			<input type="hidden" name="sale_amount" id='sale_amount' value='<?=$final_price;?>'>
			<tr>
				<th>Id</th>
				<th>Receipt Id</th>
				<th>Payment Method</th>
				<th>Description</th>
				<th>Amount</th>
				<th>Payment Status</th>
			</tr>
			
			<?php foreach($paymentTableData as $key => $paymentData){?>
			<tr>
				<td><?php echo $paymentData['id'];?></td>
				<td><?php echo $paymentData['product_receipt_id'];?></td>
				<td><?php echo $paymentData['payment_method'];?></td>
				<td><?php echo $paymentData['description'];?></td>
				<td>
				<input type="text" id= "old_amt_box_<?php echo $key;?>" name="old_amt[]" value="<?php echo $paymentData['amount'];?>"/>
				<?php //echo $this->Form->input('',array('value' => $paymentData['PaymentDetail']['amount']));?></td>
				<td><?php echo $paymentData['payment_status'];?></td>
				
			</tr>
			<?php } ?>
			
	
		</table>
	</fieldset>
	<div class="submit">
	<?php
	$options=array('div'=>false,'label'=>'Update Payment','id' => 'update_payment');
    echo $this->Form->Submit('Update Payment',$options);
	echo $this->Form->end(); ?>
	</div>

</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index')); ?> </li>
	</ul>
</div>
<script>
	$('#update_payment').click(function(){
		var totalamount = 0;
		var givenamount = parseFloat($('#sale_amount').val());
		<?php foreach($paymentTableData as $key => $paymentData){?>
			totalamount+=parseFloat($('#old_amt_box_'+<?php echo $key?>).val());
		<?php } ?>
		var isDisabled = $('#added_amount').prop('disabled');
		
		if (isDisabled == false) {
			//alert('enabled');
			totalamount =  parseFloat($('#added_amount').val()) + totalamount;
		}
		totalamount = parseFloat(totalamount);
        totalamount = totalamount.toFixed(2);
		if (totalamount != givenamount) {
            
			alert("Total amount must be equivalent to the sale amount("+ givenamount +")!");
			$('#error_div').html("Total amount must be equivalent to the sale amount("+ givenamount +")!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			return false;
		} else {
			return true;
		}
	});
	
	
	$('#open_new').click(function(){
		$('#hidden_row').css("display", "block");
		$('#added_amount').prop('disabled', false);
	});
</script>
<script>
	<?php foreach($paymentTableData as $key => $paymentData){?>
	$("#old_amt_box_"+<?php echo $key?>).keydown(function (event) {		
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||
		event.keyCode == 46 || event.keyCode == 183 ||
		event.keyCode == 190 || event.keyCode == 110
		) {
			;
			//event.keyCode == 190 || event.keyCode == 110 for dots
			//48-57 => 0..9
			//8 => Backspace; 9 => tab 
		} else {
			event.preventDefault();
		}
    });
	<?php } ?>
</script>