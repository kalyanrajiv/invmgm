<div class="invoiceOrders form">
	<div id="error_div"></div>
<?php echo $this->Form->create('UpdatePayment'); ?>
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
				<?php 
						foreach($payment_res as $key => $paymentData){ ?>
							<td><?php echo $paymentData['id'];?></td>
							<td><?php echo $paymentData['product_receipt_id'];?></td>
							<td><?php echo $paymentData['payment_method'];?></td>
							<td><?php echo $paymentData['description'];?></td>
							<td>
							<input type="text" id= "<?php echo "old_amt_box_".$paymentData['id']; ?>" name="<?php echo "payment[".$paymentData['id']."]"; ?>" style="width: 110px;" value="<?php echo $paymentData['amount'];?>"/>
							<?php //echo $this->Form->input('',array('value' => $paymentData['PaymentDetail']['amount']));?></td>
							<td><?php echo $paymentData['payment_status'];?></td>
							</tr>
					<?php }
				?>
			
		</table>
	</fieldset>
	<div class="submit">
	<?php
	//$options=array('div'=>false,'label'=>'Update Payment','id' => 'update_payment');
	echo $this->Form->submit('submit',array('name'=>'submit'));
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
		var givenamount = $('#sale_amount').val();
		<?php 
			foreach($payment_res as $key => $paymentData){
			?>
			totalamount+=parseFloat($('#old_amt_box_'+<?php echo $paymentData['id'];?>).val());
		<?php }
		 ?>
		 totalamount = roundNumber(totalamount,2);
		 givenamount = roundNumber(givenamount,2);
		if (totalamount != givenamount) {
			//alert(totalamount);
			alert("Total amount must be equivalent to the sale amount("+ givenamount +")!");
			$('#error_div').html("Total amount must be equivalent to the sale amount("+ givenamount +")!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			return false;
		} else {
			return true;
		}
	});
</script>
<script>
  $(function() {
    <?php
        foreach($payment_res as $key => $paymentData){ ?>
            $("#<?php echo "old_amt_box_".$paymentData['id']; ?>").keydown(function (event) {  
                if (event.shiftKey == true) {event.preventDefault();}
                if ((event.keyCode >= 48 && event.keyCode <= 57) ||
                   (event.keyCode >= 96 && event.keyCode <= 105) ||
                   event.keyCode == 8 || event.keyCode == 9 ||
                   event.keyCode == 37 || event.keyCode == 39 ||
                   event.keyCode == 46 || event.keyCode == 183 || event.keyCode == 190 || event.keyCode == 110) {
                     ;
                }else{
                  event.preventDefault();
                }
            });
  <?php }?>
  });
</script>

