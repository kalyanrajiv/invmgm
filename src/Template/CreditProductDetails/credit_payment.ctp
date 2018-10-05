<div class="invoiceOrders view">
	<div id="error_div" tabindex='1'></div>
<?php
$newBasket = $this->request->Session()->read('Basket');
if(!is_array($newBasket) || count($newBasket) == 0){
	$redirURL = $this->Html->url(array('controller'=>'credit_product_details','action'=>'view_credit_note'));
	echo "<script>window.location = '$redirURL';</script>";
}
$country = $this->request->Session()->read('country');
//pr($_SESSION);
$bulkDiscount = $this->request->Session()->read('bulk_discount');
$amount = 0;
$finalAmount = 0;
foreach($newBasket as $finalProductId => $finalDetail){
	 $finalDiscount = $finalDetail['discount'];
	if($country == "OTH"){
		$selling_price = $finalDetail['price_without_vat'];
	}else{
		 $selling_price = $finalDetail['selling_price'];
	}
	 $amountToPay = $finalDetail['quantity']*($selling_price-$selling_price*$finalDiscount/100);
	$netAmount = (float)$amountToPay-(float)$amountToPay*(float)$bulkDiscount/100;
	$finalAmount+=$netAmount;
}//die;
	 $amount = number_format((float)$finalAmount, 2, '.', '');
	$finalAmount=$this->request->Session()->read('finalAmount');
	if($finalAmount > 0){
		$amount = number_format((float)$finalAmount, 2, '.', '');
	}
?>
<table style='width:350px;'>
	<tr>
		<td><span style=''><h2><?php echo __('Payment'); ?></h2></span></td>
		<td><input type='radio' id = 'full_or_part_1' name='full_or_part' value='1' checked = 'checked' onClick='full_Part(1);'></td><td>Full Payment</td>
		<td><input type='radio' id = 'full_or_part_2' name='full_or_part' value='0' onClick='full_Part(0);'></td><td>Part Payment</td>
	</tr>
</table>
	<?php echo $this->Form->create('Payment');?>
<table id="main_table">
	<tr id="main_row">
	<?php		
		for($i = 0; $i < 2; $i++){ //count($paymentType)
			$style = "";
			if($i){$style='display:none;';}
	?>
	<td id="cell">
		<div id="divid_<?php echo $i;?>" style='<?php echo $style;?>'>
		<table>
			<tr>
				<?php $paymentType['Select Payment Method'] =   'Select Payment Method'; ?>
				<td><?php echo $this->Form->input('Payment Method',array(
																		 'options'=>$paymentType,
																		 'name'=>'Payment[Payment_Method][]',
																		 //'default' => 'Select Payment Method'
																		 )
												  );
				echo $this->Form->input('Description',array(
																  'type'=>'hidden',
																  'value' => 'we are not using','style'=>'width: 136px;height: 15px;','name'=>"Payment[Description][$i]"));
				?>
			</td>
			</tr>
			<tr>
				<td><?php echo $this->Form->input('Amount',array(
							'id' => "payment_method_$i",
							'type'=>'text',
							'style'=>'width: 55px;height: 15px;',
							'label'=>'Amount',
							'name'=>"Payment[Amount][$i]",
							//'value' => $amount
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
	Total : <input type="text" name="total" readonly id="total" value='0.00' style="width: 55px;height: 12px;margin-bottom: 10px;"/> Credit Amount:<?php echo $amount;?> <strong>Due Amount:</strong><span id ='due_amount'><?php echo $amount;?></span>
	<input type="hidden" name="final_amount" value="<?php echo $amount;?>"/>
	<?php
	$options = array(
						'label' => 'submit',
						'name'  =>'submit1',
						'id'    => 'submit',
						'div' => array(
						'class' => 'submit',
						)
					);
	echo $this->Form->submit("submit",$options);
echo $this->Form->end();
	?>
	<?php //echo $this->Form->end('submit');
	 echo "</td><td style='padding-top: 20px;'>"?>
	<?php 
	 echo "<div style='height: 14px;background-color: rgb(98, 175, 86);  border-width: 1px; border-style: solid; width: 50px; font-size: 12px; padding: 9px; border-radius: 5px;color: white;'>".$this->Html->link(__('Cancel'), array('controller' => 'product_receipts', 'action' => 'cancel'))."</div>";?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index')); ?> </li>
	</ul>
</div>
<script>
	var dueAmount = <?php echo $amount;?>;
	$("input[id*='payment_method_']").blur(function(){
		total = 0;
		for(var i = 0; i < <?php echo 2;?>; i++){
			var amt = $('#payment_method_'+i).val();
			if (amt != "") {
				total += parseFloat($('#payment_method_'+i).val());
			}						
		}
		$('#total').val(total);
		if ($('#total').val() > dueAmount) {
			//alert("Amount is exceeding due amount");//code
		}
		var balance = dueAmount - total;		
		document.getElementById( 'due_amount' ).innerHTML =  balance.toFixed(2);
	});
	$("input[id*='payment_method_']").keyup(function (event) {
		total = 0;
		for(var i = 0; i < <?php echo 2;?>; i++){
			var amt = $('#payment_method_'+i).val();
			if (amt != "") {
				total += parseFloat($('#payment_method_'+i).val());
			}						
		}
		$('#total').val(total);
		if ($('#total').val() > dueAmount) {
			//alert("Amount is exceeding due amount");//code
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
			//alert("Amount is exceeding due amount");//code
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
	$('#submit').click(function(){
		var select = $('#PaymentPaymentMethod').val();
		 if (select == 'Select Payment Method') {
				alert("Please select Payment method");
				$('#error_div').html('Please select Payment method').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
				return false;   //code
			}
		 
				 
	})
	
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
	
	$('input[name = "submit1"]').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	});
</script>