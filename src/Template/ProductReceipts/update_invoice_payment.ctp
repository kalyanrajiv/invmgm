<div class="invoiceOrders view">
<h2><?php echo __('Update Payment');
if($newInvoiceOrderAmount==0 && array_key_exists('final_amount',$this->request['data'])){
	$newInvoiceOrderAmount = round($this->request['data']['final_amount'],2);
}
if((int)$bulkDiscount){
	echo " (Bulk Discount ".$bulkDiscount."%)";
}
?></h2>
	<table style='width:350px;'>
	<tr>
		<td><input type='radio' id = 'full_or_part_1' name='full_or_part' value='1' checked = 'checked' onClick='full_Part(1);'></td><td>Full Payment</td>
		<td><input type='radio' id = 'full_or_part_2' name='full_or_part' value='0' onClick='full_Part(0);'></td><td>Part Payment</td>
	</tr>
</table>
	<?php echo $this->Form->create('Payment');?>
<table id="main_table">
	<tr id="main_row">
	<?php		
		for($i = 0; $i < 3; $i++){ //count($paymentType)
			$style = "";
			if($i){$style='display:none;';}
	?>
	<td id="cell">
		<div id="divid_<?php echo $i;?>" style='<?php echo $style;?>'>
		<table>
			<tr>
				<td><?php echo $this->Form->input('Payment Method',array('options'=>$paymentType,'name'=>'Payment[Payment_Method][]'))?></td>
			</tr>
			<?php echo $this->Form->input('Description',array('type'=>'hidden','style'=>'width: 136px;height: 15px;','name'=>"Payment[Description][$i]", 'value'=>'no neeed of it'))?>
			<tr>
				<td><?php echo $this->Form->input('Amount',array(
							'id' => "payment_method_$i",
							'type'=>'text',
							'style'=>'width: 55px;height: 15px;',
							'label'=>'Amount',
							'name'=>"Payment[Amount][$i]",
							'value'=>''
							)
								  )?></td>
			</tr>
		</table></div>
		
	</td>
	<?php
		}
	?>		
	</tr>
</table>
<?php $options = array('label' => 'Submit', 'value' => 'submit', 'div' => false, 'class' => 'submit'); ?>
<div class="submit">
<table style="width:400px">
	<tr><td colspan="5">Total : <input type="text" name="total" readonly id="total" value='0.00' style="width: 55px;height: 12px;margin-bottom: 10px;"/> Invoice Amount:<?php echo number_format($newInvoiceOrderAmount,2);?> <strong>Due Amount:</strong><span id ='due_amount'><?php echo round($newInvoiceOrderAmount,2);?></span>
	<input type="hidden" name="final_amount" value="<?php echo $newInvoiceOrderAmount;?>"/>
	<input type="hidden" name="update_invoice_pmt" id="update_invoice_pmt"/>
	<?php echo $hiddenFields;?></td></tr>
	<tr>
		<td style="width:90px;"><?php echo $this->Form->submit('Cancel', array('div' => false,'name' => 'cancel','class' => 'submit')); ?></td>
		<td style="width:10px;">&nbsp;</td>
		<td style="width:90px;"><?php
		echo $this->Form->submit('Submit',array('value' => 'submit','div' => false));
		echo $this->Form->end();?></td>
	</tr>
</table>
</div>
</form>
		

	
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index')); ?> </li>
	</ul>
</div>
<script>
	var dueAmount = <?php echo round($newInvoiceOrderAmount,2);?>;
	$("input[id*='payment_method_']").blur(function(){
		total = 0;
		for(var i = 0; i < <?php echo 3;?>; i++){
			var amt = $('#payment_method_'+i).val();
			if (amt != "") {
				total += parseFloat($('#payment_method_'+i).val());
			}						
		}
		$('#total').val(total);
		if ($('#total').val() > dueAmount) {
			alert("Amount is exceeding due amount");//code
		}
		var balance = dueAmount - total;		
		document.getElementById( 'due_amount' ).innerHTML =  balance.toFixed(2);
	});
	$("input[id*='payment_method_']").keyup(function (event) {
		total = 0;
		for(var i = 0; i < <?php echo 3;?>; i++){
			var amt = $('#payment_method_'+i).val();
			if (amt != "") {
				total += parseFloat($('#payment_method_'+i).val());
			}						
		}
		$('#total').val(total);
		if ($('#total').val() > dueAmount) {
			alert("Amount is exceeding due amount");//code
		}
		var balance = dueAmount - total;		
		document.getElementById( 'due_amount' ).innerHTML =  balance.toFixed(2);
	});
	$("input[id*='payment_method_']").keydown(function (event) {		
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||
		event.keyCode == 46 || event.keyCode == 190 || event.keyCode == 183 ||
		event.keyCode == 110) {
			;
			//48-57 => 0..9
			//8 => Backspace; 9 => tab 
		} else {
			event.preventDefault();
		}
		total = 0;
		for(var i = 0; i < <?php echo count($paymentType);?>; i++){
			var amt = $('#payment_method_'+i).val();
			if (amt != "") {
				total += parseFloat($('#payment_method_'+i).val());
			}						
		}
		$('#total').val(total);
		if ($('#total').val() > dueAmount) {
			alert("Amount is exceeding due amount");//code
		}
		if($(this).val().indexOf('.') !== -1 && event.keyCode == 190){event.preventDefault();}
        });
	//keyListener.onKeyDown = function() {
	//	trace("DOWN -> Code: " + Key.getCode() + "\tACSII: " + Key.getAscii() + "\tKey: " + chr(Key.getAscii()));
	//};
	//Key.addListener(keyListener);
	
	function full_Part(fullPart) {
		if (fullPart == 1) {
			$('#divid_1').hide();
			$('#payment_method_1').val("");
			$('#divid_2').hide();
			$('#payment_method_2').val("");
		}else{			
			$('#divid_1').show();
			$('#divid_2').show();
		}
	}
	
	$(document).ready(function(){
		//for showing the prefilled value in payment text box
		var dueAmnt = $('#final_amount').val();
		$('#payment_method_0').val(dueAmount);
	})
	
	$('#full_or_part_1').click(function(){
		var dueAmnt = $('#final_amount').val();
		$('#payment_method_0').val(dueAmount);
		$('#payment_method_1').val("");
	})
	
	$('#full_or_part_2').click(function(){
		$('#payment_method_0').val("");
	})
	
	$("#payment_method_0").focusout(function () {
		var firstBox = $('#payment_method_0').val();
		if (firstBox > dueAmount) {
			//$('#error_div').html('Entered Amount is more than Amount').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert("Entered Amount is more than Amount");//code
			return false;   
		}
		if (firstBox<0) {
			alert("Input amount should be more than zero");
			//$('#error_div').html('Input amount should be more than zero').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			return false;   //code
		}
		if($('#full_or_part_2').is(':checked')) {
			var blance = dueAmount - firstBox;
			document.getElementById( 'payment_method_1' ).value =  blance.toFixed(2);
		}
	});
	
	$("#payment_method_1").focusout(function () {
		var secondBox = $('#payment_method_1').val();
		if (secondBox > dueAmount) {
				//$('#error_div').html('Entered Amount is more than Amount').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
				alert("Entered Amount is more than Amount");//code
				return false;   
			}
			if (secondBox<0) {
				alert("Input amount should be more than zero");
				//$('#error_div').html('Input amount should be more than zero').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
				return false;   //code
			}
		var blance = dueAmount - secondBox;
		document.getElementById( 'payment_method_0' ).value =  blance.toFixed(2);
	});
</script>