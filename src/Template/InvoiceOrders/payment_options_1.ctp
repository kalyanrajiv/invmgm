<?php
	$mainDomain = ADMIN_DOMAIN;
	$receiptHTML = <<<RECEIPT_HTML
	<table border="1" style="">
		<tr>
			<th colspan="7" align="center">$mainDomain</th>
		</tr>
		<tr>
			<th colspan="2">Performa # {$invoiceOrder['id']}</th>
			<th colspan="3">Customer # {$invoiceOrder['customer_id']}</th>
			<th>Date:</th>
			<td><?= date("d-m-Y");?></td>
		</tr>
		<tr>
			<td><strong>Name:</strong></td>
			<td colspan="2">{$invoiceOrder['fname']} {$invoiceOrder['lname']}</td>
			<td><strong>Email</strong></td>
			<td>{$invoiceOrder['email']}</td>
			<td><strong>Mobile:</strong></td>
			<td>{$invoiceOrder['mobile']}</td>
		</tr>
		<tr>
			<td><strong>Delivery Address:</strong></td>
			<td colspan="6">{$invoiceOrder['del_address_1']} {$invoiceOrder['del_address_2']}</td>
		</tr>
		<tr>
			<td><strong>City:</strong></td>
			<td>{$invoiceOrder['del_city']}</td>
			<td><strong>State:</strong></td>
			<td colspan="2">{$invoiceOrder['del_state']}</td>
			<td><strong>Postal Code:</strong></td>
			<td>{$invoiceOrder['del_zip']}</td>
		</tr>
		<tr>
			<th colspan="7">Purchase Details</th>
		</tr>
	
		<tr>
			<th>Created On</th>
			<th colspan="2">Product</th>
			<th>Price</th>
			<th>Quantity</th>
			<th>Discount Price</th>
			<th>Amount</th>
		</tr>
RECEIPT_HTML;
?>
		<?php
		$amount = 0;
		$totalVat = 0;
		$totalDiscount = 0;
		$bulkDiscountValue = 0;
		$netAmount = $invoiceOrder['amount'];
		$netAmount = number_format((float)$netAmount, 2, '.', '');
		$bulkDiscountPercentage = $invoiceOrder['bulk_discount'];
        $country = $cus_result['country'];
        if($special == 1){
			if($country == "OTH"){
				$netAmount = $netAmount;
			}else{
				$vatItem = $vat/100;
				$netAmount =  round(($netAmount/(1+$vatItem)),2);
			}
		}
        
		//pr($invoice_detail_data);
		foreach($invoice_detail_data as $key => $orderDetail){
			$vatItem = $vat/100;
			$itemPrice = round($orderDetail['price']/(1+$vatItem),2);	
			//if($orderDetail['discount'] < 0){
			//	$itemPrice = round($orderDetail['price'],2);
			//}else{
			//	$itemPrice = round($orderDetail['price']/(1+$vatItem),2);	
			//}
			
			$discount = $itemPrice*$orderDetail['discount']/100*$orderDetail['quantity']; //$orderDetail['price']
			$discountAmount = ($orderDetail['quantity']*$itemPrice)-$discount; //$orderDetail['price']
			$amt_to_show = $itemPrice - $itemPrice*$orderDetail['discount']/100;
			
			//$amount+=$discountAmount;
			$bulkDiscountValue = $amount - $netAmount;
			$vatAmount = $discountAmount-($discountAmount/(1+$vatItem));
			$totalVat+=$vatAmount;
			$totalVat = number_format($totalVat,2);
			$totalDiscount+=$discount;
			$dis = round($orderDetail['discount'],2);
			if($dis < 0){
				$itemPrice = $discount_colum = $amt_to_show;
				$amt_colum = $amt_to_show*$orderDetail['quantity'];
			}else{
				$discount_colum = $amt_to_show;
				$amt_colum =  $amt_to_show*$orderDetail['quantity'];
			}
			$amount += $amt_colum;
			if($country=='OTH'){ $vatValue = "0";}else{ $vatValue = $vat;}
            if($special == 1){
				$vatValue = 0;
			}
			$created = date("d/m/y",strtotime($orderDetail['created']));
			$receiptHTML.= <<<RECEIPT_HTML
			<tr>
			
				<td>{$created}</td>
				<td colspan="2">{$productName[$orderDetail['product_id']]}</td>
				<td>{$itemPrice}</td> 
				<td>{$orderDetail['quantity']}</td>
				<td>{$discount_colum}</td>
				
				<td>{$amt_colum}</td>
			</tr>
RECEIPT_HTML;
//$orderDetail['price']
//$orderDetail['discount']
		}
        if($special == 1){
			$vat = 0;
		}
		$vatItem = $vat/100;
		$bulkDiscountValue = $amount*$bulkDiscountPercentage/100;
		$subTotal = $amount-$bulkDiscountValue;
		$subTotal = number_format($subTotal,2);
		$finalVat = $subTotal-$subTotal/(1+$vatItem);
		$finalVat = number_format($finalVat,2);
		$nAmount = number_format($subTotal/(1+$vatItem),2);
        if($country == "OTH"){
			$finalVat = 0;
			$nAmount = number_format($subTotal,2);
		}else{
			$finalVat = $subTotal*$vatItem;
			$nAmount = number_format($subTotal+$finalVat,2);
		}
        
        $finalVat = number_format($finalVat,2);
        
		$receiptHTML.= <<<RECEIPT_HTML
		<tr>
			<th colspan="6">Sub Total</th>
			<td>{$amount}</td>
		</tr>
		<tr>
			<th colspan="6">Bulk Discount ({$bulkDiscountPercentage} %)</th>
			<td>{$bulkDiscountValue}</td>
		</tr>
		
		<tr>
			<th colspan="6">Vat</th>
			<td>{$finalVat}</td>
		</tr>
		<tr>
			<th colspan="6">Net Amount</th>
			<td>{$nAmount}</td>
		</tr>
		<tr>
			<th colspan="6">Total Amount</th>
			<td>{$netAmount}</td>
		</tr>
	</table>
RECEIPT_HTML;
?>
<div class="invoiceOrders view">
<table style='width:350px;'>
	<tr>
		<td><span style=''><h2><?php echo __('Payment'); ?></h2></span></td>
		<td><input type='radio' id = 'full_or_part_1' name='full_or_part' value='1' checked = 'checked' onClick='full_Part(1);'></td><td>Full Payment</td>
		<td><input type='radio' id = 'full_or_part_2' name='full_or_part' value='0' onClick='full_Part(0);'></td><td>Part Payment</td>
	</tr>
</table>
<?php echo $this->Form->create('Payment');?>
<td><input type="hidden" name = "special" value="<?php echo $special;?>"</td>
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
			
				<?php echo $this->Form->input('Description',array('type'=>'hidden','style'=>'width: 136px;height: 15px;','value' => "not needed",'name'=>"Payment[Description][$i]"))?>
			
			<tr>
				<td><?php echo $this->Form->input('Amount',array(
							'id' => "payment_method_$i",
							'type'=>'text',
							'style'=>'width: 55px;height: 15px;',
							'label'=>'Amount',
							'name'=>"Payment[Amount][$i]",								)
								  )?></td>
			</tr>
		</table></div>
		
	</td>
	<?php
		}
	?>		
	</tr>
</table>
	Total : <input type="text" name="total" readonly id="total" value='0.00' style="width: 55px;height: 12px;margin-bottom: 10px;"/> Invoice Amount:<?php echo $netAmount;?> <strong>Due Amount:</strong><span id ='due_amount'><?php echo $netAmount;?></span>

	<?php
    echo $this->Form->Submit('submit',['name'=>'submit1']);
    echo $this->Form->end();?>
	<?php echo $receiptHTML;?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index')); ?> </li>
	</ul>
</div>
<script>
	var dueAmount = <?php echo $netAmount;?>;
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
			alert("Amount is exceeding due amount(#225)");//code - bug
			$('payment_method_0').val(dueAmount);
			$('payment_method_1').val(0);
			$('payment_method_2').val(0);
			return;
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
			$("#payment_method_0").val(dueAmount);
			$("#payment_method_0").focus();
			$("#payment_method_0").select();
			$("#payment_method_1").val(0);
			$("#payment_method_2").val(0);
			return false;   
		}
		if (firstBox < 0) {
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
<script>
	$('input[name="submit1"]').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	})
</script>