<div class="invoiceOrders form">
<?php echo $this->Form->create('UpdatePayment');
?>
	<fieldset>
		<legend>Update Payment</legend>
		<table>
			<tr>Total Amt: <?php echo $final_amt;?></tr>
			<input type="hidden" name="sale_amount" id='sale_amount' value='<?=$final_amt;?>'>
			<tr>
				<th>Id</th>
				<th>Receipt Id</th>
				<th>Payment Method</th>
				<th>Description</th>
				<th>Amount</th>
				<th>Payment Status</th>
			</tr>
			
			<tr>
				<input type="hidden" name="selected_customer" value="<?php echo $selected_cutomer_id;?>" />
				<?php  //pr($res);  //die;
                foreach($res as $s => $paymentData){
                    //pr($k);die;
                        //foreach($res as $paymentData){ //pr($paymentData);die;?>
						 	<td><?php echo $paymentData['id'];//echo $paymentData['id'];?></td>
							<td><?php echo $paymentData['credit_receipt_id'];?></td>
							<td><?php echo $paymentData['payment_method'];?></td>
							<td><?php echo $paymentData['description'];?></td>
							<td>
							<input type="text" id= "<?php echo "old_amt_box_".$paymentData['id']; ?>" name="<?php echo "data[payment][".$paymentData['id']."]"; ?>" value="<?php echo $paymentData['amount'];?>"/>
							<?php //echo $this->Form->input('',array('value' => $paymentData['PaymentDetail']['amount']));?></td>
							<td><?php echo $paymentData['payment_status'];?></td>
							</tr>
					<?php //}
			 } ?>
			
		</table>
	</fieldset>
	<div class="submit">
        <?php
       // $options=array('div'=>false,'label'=>'Update Payment','id' => 'update_payment');
       echo $this->Form->button('Update Payment', ['id' => 'update_payment','name'=>'submit']);
         ?>
    <?= $this->Form->end() ?>
	<?php
	 //$options=array('div'=>false,'label'=>'Update Payment','id' => 'update_payment');
	//echo $this->Form->end($options); ?>
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
		var givenamount = $('#sale_amount').val();
		<?php //foreach($res as $s => $k){
			foreach($res as $paymentData){
			?>
			totalamount+=parseFloat($('#old_amt_box_'+<?php echo $paymentData['id'];?>).val());
		<?php }
		//} ?>
		totalamount = Math.round(totalamount*100)/100;
		givenamount = Math.round(givenamount*100)/100;
		if (totalamount != givenamount) {
			alert("Total amount must be equivalent to the sale amount("+ givenamount +")!");
			$('#error_div').html("Total amount must be equivalent to the sale amount("+ givenamount +")!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			return false;
		} else {
			$.blockUI({ message: 'Just a moment...' });
			return true;
		}
	});
</script>