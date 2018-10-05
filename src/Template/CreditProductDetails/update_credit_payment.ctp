<div class="invoiceOrders form">
<?php echo $this->Form->create('UpdatePayment'); ?>
	<fieldset>
		<?php if($note_or_quotation == 1){?>
			<legend>Update Payment(For Credit Note)</legend>
		<?php }else{?>
			<legend>Update Payment(For Credit Quotation)</legend>
		<?php }?>
		<table>
			<tr><td>Total Amt: <?php echo $paymentData['amount'];?></td>
			<td>Date : <?php echo date("d-m-Y",strtotime($recit_created));?>
			<input type="hidden" id="min_val_for_date"  value="<?php echo $recit_created; ?>" />
			</td>
			</tr>
			
			<tr>
				<th>Id</th>
				<th>Receipt Id</th>
				<th>Payment Method</th>
				<th>Amount</th>
				<th>Description</th>
				<th>Payment Status</th>
				<th>Update Mode</th>
			</tr>
			<?php //pr($paymentData); ?>
			<tr>
				<input type="hidden" name="sale_amount" id='sale_amount' value='<?=$paymentData['amount'];?>'>
				<td><?php echo $paymentData['id'];?></td>
				<td><?php echo $paymentData['credit_receipt_id'];?></td>
				<td><?php echo $paymentData['payment_method'];?></td>
				<?php $method = $paymentData['payment_method']; ?>
				
				
				<td><input type="text" id= "old_amt_box" style="width: 120px;" name="old_amt" value="<?php echo $paymentData['amount'];?>"/></td>
				<td><input type="text"  name="desc" style="width: 120px;" value="<?php echo $paymentData['description'];?>"/></td>
				<td><?php echo $paymentData['payment_status'];?></td>
				<td><?php echo $this->Form->input('change_mode',array('options'=>$paymentType,'label'=>false,'value' => $method))?></td>
			</tr>
			<tr style="display: none;" id="hidden_row">
				<td>
					<input type="text" name="added_amount" id='added_amount' value='0'/>
				</td>
				<td>
					<input type="text" title = "description" placeholder="description"  name="new_box_desc" value=""/>
				</td>
				<td>
					<?php echo $this->Form->input('new_change_mode',array('options'=>$paymentType,'label'=>false))?>
				</td>
				
			</tr>
			<tr>
				<td>
					<span><a href="javascript:void(0)" id="open_new">+</a></span>
				</td>
			</tr>
			<tr>
				<td colspan='3' >
					<input type="checkbox" id="ticked" name="ticked" value="1"> select to mark as paid<br>
				</td>
				<td id="date_box">
					<input type="text" id="datepicker" name="date_box_date" />
				</td>
			</tr>
			<tr><td colspan='7'>**Checkbox not applicable for on credit options</td></tr>
		</table>
	</fieldset>
	<div class="submit">
	<?php
	//$options=array('div'=>false,'label'=>'Update Payment','id' => 'update_payment');
    echo $this->Form->submit('Update Payment',array('name'=>'submit1','id' => 'update_payment'));
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
	 $( document ).ready(function() {
		$("#date_box").hide();
});
</script>
<script>
    
 	$("#old_amt_box").keydown(function (event) {  
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
  
   $("#added_amount").keydown(function (event) {  
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
  
   
	$('#open_new').click(function(){
		$('#hidden_row').css("display", "block");
		$('#added_amount').prop('disabled', false);
	});
	$('#update_payment').click(function(){
		var totalamount = 0;
		var givenamount = parseFloat($('#sale_amount').val());
		<?php //foreach($paymentData as $key => $paymentInfo){?>
			totalamount+=parseFloat($('#old_amt_box').val());
		<?php //} ?>
		var isDisabled = $('#added_amount').prop('disabled');
		
		if (isDisabled == false) {
			//alert('enabled');
			totalamount =  parseFloat($('#added_amount').val()) + totalamount;
		}
		totalamount = totalamount.toFixed(2);
		givenamount = givenamount.toFixed(2);
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
<script>
	$("#ticked").click(function(){
		if($('#ticked').is(":checked")) {
			$("#date_box").show();
				jQuery(function() {
					jQuery( "#datepicker" ).datepicker({ dateFormat: "d M yy" });
					$("#datepicker").val($.datepicker.formatDate("d M yy", new Date()));
					var min_date = $("#min_val_for_date").val();
					
					min_date = $.datepicker.formatDate('yy,mm,dd', new Date(min_date));
					
					$( "#datepicker" ).datepicker( "option", "minDate", new Date(min_date) );
					$( "#datepicker" ).datepicker( "option", "maxDate", new Date() );
				});
		}else{
			$("#date_box").hide();
		}
		
		})
</script>