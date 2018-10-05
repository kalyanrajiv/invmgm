<div class="invoiceOrders form">
<?php

echo $this->Form->create('UpdatePayment'); ?>
	<fieldset>
		<legend>Update Payment(For Invoice)</legend>
		<table><?php //pr($paymentData); ?>
			<tr><td><b>Total Amt: </b><?php echo $paymentData['amount'];?></td>
			<td><b>Invoice Date :</b> <?php echo date("d-m-Y",strtotime($recit_created));?>
			<input type="hidden" id="min_val_for_date"  value="<?php echo $recit_created; ?>" />
			</td>
			</tr>
			<input type="hidden" name="sale_amount" id='sale_amount' value='<?=$paymentData['amount'];?>'>
			
			<tr>
				<th>Id</th>
				<th>Receipt Id</th>
				<th>Payment Method</th>
				<th>Amount</th>
				<th>Description</th>
				<th>Payment Status</th>
				<th>Update Mode</th>
			</tr>
			
			<tr>
				<td><?php echo $paymentData['id'];?></td>
				<td><?php echo $paymentData['product_receipt_id'];?></td>
				<td><?php echo $paymentData['payment_method'];?></td>
				<?php $method = $paymentData['payment_method'];?>
				<?php //echo $paymentData['description'];?>
				<td>
				<input type="text" id= "old_amt_box" name="old_amt" style="width: 130px;" value="<?php echo $paymentData['amount'];?>"/>
				<?php //echo $this->Form->input('',array('value' => $paymentData['PaymentDetail']['amount']));?></td>
				<td><input type="text"  name="desc" style="width: 130px;" value="<?php echo $paymentData['description'];?>"/></td>
				<td><?php echo $paymentData['payment_status'];?></td>
				<td><?php echo $this->Form->input('change_mode',array('options'=>$paymentType,'label'=>false,'value' => $method))?></td>
			</tr>
			
			<tr style="display: none;" id="hidden_row">
				<td colspan = 5>
					<table>
						<tr>
							<td>
								<input type="text" name="added_amount" id='added_amount' value='0'/>
							</td>
							<td>
								<td><input type="text" title = "description" placeholder="description"  name="new_box_desc" value=""/></td>
							</td>	
							<td>
								<?php echo $this->Form->input('new_change_mode',array('options'=>$paymentType,'label'=>false))?>
							</td>
									
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					<span><a href="javascript:void(0)" id="open_new">+</a></span>
				</td>
			</tr>

			<tr>
				<td colspan='5'>
					<input type="checkbox" name="ticked" id="ticked" value="1"> select to mark as paid<br>
					
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
	$options=array('div'=>false,'label'=>'Update Payment','id' => 'update_payment','name'=>'submit1');
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
	
	
	$('#open_new').click(function(){
		$('#hidden_row').css("display", "block");
		$('#added_amount').prop('disabled', false);
	});
</script>
<script>
  $(function() {
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
  <script>
	 $( document ).ready(function() {
		$("#date_box").hide();
});
</script>