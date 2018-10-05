<div class="invoiceOrders view">
	<div id="error_div" tabindex='1'></div>
<?php
//if(!isset($paymentType)){
	$paymentType = array();
//}
if(empty($paymentType)){
	$paymentType['Cash'] = 'Cash';
	$paymentType['Card'] = 'Card';
}
?>
<?php $final_step_url = $this->Url->build(["controller" => "kiosk_product_sales","action" => "final_step_ajax"]); ?>
<?php $redirect_url = $this->Url->build(["controller" => "kiosk_product_sales","action" => "new_order"]); ?>
<?php $redirect_url_after_success = $this->Url->build(["controller" => "prints","action" => "generateReceipt"]); ?>
<?php
$print_setting = $setting['print_type'];
?>

<input type='hidden' name='final_step_url' id='final_step_url' value='<?=$final_step_url?>' />
<input type='hidden' name='redirect_url' id='redirect_url' value='<?=$redirect_url?>' />
<input type='hidden' name='redirect_url_after_success' id='redirect_url_after_success' value='<?=$redirect_url_after_success?>' />
<input type='hidden' name='print_setting' id='print_setting' value='<?=$print_setting?>' />
<div id="sale_done" title="Sale Done">Sale Done</div>


<table style='width:350px;'>
	<tr>
		<td><span style=''><h2><?php echo __('Payment'); ?></h2></span></td>
		<td><input type='radio' id = 'full_or_part_1' name='full_or_part' value='1' checked = 'checked' onClick='full_Part(1);'></td><td>Full Payment</td>
		<td><input type='radio' id = 'full_or_part_2' name='full_or_part' value='0' onClick='full_Part(0);'></td><td>Part Payment</td>
	</tr>
</table>
	<?php //echo $this->Form->create('Payment');?>
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
				<td><?php echo $this->Form->input('Payment Method',array('options'=>$paymentType,'id'=> 'method_'.$i,'name'=>'Payment[Payment_Method][]'))?></td>
			</tr>
			 <?php echo $this->Form->input('Description',array('type'=>'hidden','style'=>'width: 136px;height: 15px;','name'=>"Payment[Description][$i]"))?> 
			 <?php #echo $this->Form->input('product_receipt_id',array('type'=>'hidden','label' => 'product_receipt_id', 'value'=>$product_receipt_id));?>
			<tr>
				<td><?php echo $this->Form->input('Amount',array(
							'id' => "payment_method_$i",
							'type'=>'text',
							'style'=>'width: 55px;height: 17px;',
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
	Total : <input type="text" name="total" readonly id="total" value='0.00' style="width: 55px;height: 17px;margin-bottom: 10px;"/>
	Amount:<span id ='amount_pay'></span> <strong>Due Amount:</strong><span id ='due_amount'></span>
	<input type="hidden" name="final_amount" value='' id='final_amount'/>
	
		 
	<div class="submit">
		<table style="width: 20%;">
			<tr>
				<td><?php
				$options = array(
						'label' => 'submit',
						'name'  =>'submit1',
						'id'    => 'submit',
						'div' => array(
							'class' => 'submit',
						)
					);
//echo $this->Form->end($options);
				 ?></td>
				<td><input class = "final_step" type="submit" name='submit1' value="Submit" style="margin-top: 16px;"/></td>
				<td><input class = "cancel" type="submit" name='cancel' value="Cancel" style="margin-top: 16px;"/></td>
			</tr>
		</table>
	</div>
	
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index')); ?> </li>
	</ul>
</div>
<script>
	
	$("input[id*='payment_method_']").blur(function(){
		var dueAmount = document.getElementById('final_amount').value;
		total = 0;
		for(var i = 0; i < <?php echo 2;?>; i++){
			var amt = $('#payment_method_'+i).val();
			if (amt != "") {
				total += parseFloat($('#payment_method_'+i).val());
			}						
		}
		total = new Number(total+'').toFixed(parseInt(2));
		$('#total').val(total);
		if ($('#total').val() > dueAmount) {
			////alert("Amount is exceeding due amount");//code
		}
		var balance = dueAmount - total;		
		document.getElementById( 'due_amount' ).innerHTML =  balance.toFixed(2);
	});
	$("input[id*='payment_method_']").keyup(function (event) {
		var dueAmount = document.getElementById('final_amount').value;
		total = 0;
		for(var i = 0; i < <?php echo 2;?>; i++){
			var amt = $('#payment_method_'+i).val();
			if (amt != "") {
				//alert(parseFloat($('#payment_method_'+i).val()));
				total += parseFloat($('#payment_method_'+i).val());
			}						
		}
		//total = Math.round(total,2);
		total = new Number(total+'').toFixed(parseInt(2));
		//alert(total);
		$('#total').val(total);
		if ($('#total').val() > dueAmount) {
			////alert("Amount is exceeding due amount");//code
		}
		var balance = dueAmount - total;		
		document.getElementById( 'due_amount' ).innerHTML =  balance.toFixed(2);
	});
	$("input[id*='payment_method_']").keydown(function (event) {
		var dueAmount = document.getElementById('final_amount').value;
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
		total = new Number(total+'').toFixed(parseInt(2));
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
			//$('#divid_2').hide();
			//$('#payment_method_2').val("");
		}else{			
			$('#divid_1').show();
			$('#divid_2').show();
		}
	}
	
	$(document).ready(function(){
		//for showing the prefilled value in payment text box
		var dueAmnt = $('#final_amount').val();
		$('#payment_method_0').val(dueAmnt);
	})
	
	$('#full_or_part_1').click(function(){
		var dueAmnt = $('#final_amount').val();
		$('#payment_method_0').val(dueAmnt);
		$('#payment_method_1').val("");
	})
	
	$('#full_or_part_2').click(function(){
		$('#payment_method_0').val("");
	})
	
	$("#payment_method_0").focusout(function () {
		var dueAmount = document.getElementById('final_amount').value;
		var firstBox = $('#payment_method_0').val();
		if (firstBox > dueAmount) {
			$('#error_div').html('Entered Amount is more than Amount').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			//alert("Entered Amount is more than Amount");//code
			return false;   
		}
		if (firstBox<0) {
			//alert("Input amount should be more than zero");
			$('#error_div').html('Input amount should be more than zero').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			return false;   //code
		}
		//alert('hi');
		if($('#full_or_part_2').is(':checked')) {
			var blance = dueAmount - firstBox;
			document.getElementById( 'payment_method_1' ).value =  blance.toFixed(2);
		}
	});
	
	$("#payment_method_1").focusout(function () {
		var dueAmount = document.getElementById('final_amount').value;
		var secondBox = $('#payment_method_1').val();
		if (secondBox > dueAmount) {
				$('#error_div').html('Entered Amount is more than Amount').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
				//alert("Entered Amount is more than Amount");//code
				return false;   
			}
			if (secondBox<0) {
				//alert("Input amount should be more than zero");
				$('#error_div').html('Input amount should be more than zero').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
				return false;   //code
			}
		var blance = dueAmount - secondBox;
		document.getElementById( 'payment_method_0' ).value =  blance.toFixed(2);
	});
	// function validateForm(){
	 $('#submit').click(function(){
		var dueAmount = document.getElementById('final_amount').value;
		var firstBox = $('#payment_method_0').val();
		var secondBox = $('#payment_method_1').val();
		var totalamount = 0;
		if (isNaN($('#payment_method_0').val()) == false && $('#payment_method_0').val() != '' && isNaN($('#payment_method_1').val()) == false && $('#payment_method_1').val() != '') {
			totalamount =  parseFloat($('#payment_method_0').val())+parseFloat($('#payment_method_1').val());
		} else if ((isNaN($('#payment_method_0').val()) == false && $('#payment_method_0').val() != '') && (isNaN($('#payment_method_1').val()) == true || $('#payment_method_1').val() == '')) {
			totalamount =  parseFloat($('#payment_method_0').val());
		} else if ((isNaN($('#payment_method_0').val()) == true || $('#payment_method_0').val() == '') && (isNaN($('#payment_method_1').val()) == false && $('#payment_method_1').val() != '')) {
			totalamount =  parseFloat($('#payment_method_1').val());
		}
		dueAmount = $('#final_amount').val();
		//totalamount =  parseFloat($('#payment_method_0').val())+parseFloat($('#payment_method_1').val());
		 if (firstBox<0) {
				//alert("Input amount should be more than zero");
				$('#error_div').html('Input amount should be more than zero').css({"background-color": "pink", "color": "red", "font-size": "20px"}).focus();
				return false;   //code
			}
		if (secondBox<0) {
				//alert("Input amount should be more than zero");
				$('#error_div').html('Input amount should be more than zero').css({"background-color": "pink", "color": "red", "font-size": "20px"}).focus();
				return false;   //code
			}
		if(totalamount!= dueAmount) {
			$('#error_div').html('Please enter the amount equivalent to sale amount!').css({"background-color": "pink", "color": "red", "font-size": "20px"}).focus();
		 	//alert("Please enter the amount equivalent to sale amount!");//code
			return false;
		}
				 
	})
</script>
<script>
	$(document).on('click', '.cancel', function() {
		$('#payment_method_0').val("");
		$('#payment_method_1').val("");
		$('#total').val("");
		$('#error_div').html("");
		$('#divid_1').hide();
		$('#payment_method_1').val("");
		$('#full_or_part_1').attr("checked","checked");
		$('#full_or_part_2').removeAttr("checked");
		$('#product_div').show();
		$('#payment_div').hide();
	});
	
	$(document).on('click', '.final_step', function() {
		var final_amount = document.getElementById('final_amount').value;
		var cust_id = document.getElementById('CustomerId').value;
		var payment_1 = document.getElementById('payment_method_0').value;
		var payment_2 = document.getElementById('payment_method_1').value;
		var method_1 = document.getElementById('method_0').value;
		var method_2 = document.getElementById('method_1').value;
		var part_time = 0;
		if($('#full_or_part_2').is(':checked')) {
			
                if (method_1 == "Cash" && method_2 == "Cash") {
                    alert('Pls choose different method');
                    $('#error_div').html('Pls choose different method').css({"background-color": "pink", "color": "red", "font-size": "20px"}).focus();
                         return false; 
                 } 
                 if (method_1 == "Card" && method_2 == "Card") {
                     alert('Pls choose different method');
                    $('#error_div').html('Pls choose different method').css({"background-color": "pink", "color": "red", "font-size": "20px"}).focus();
                         return false; 
                 }
			
         }
		
		
		if($('#full_or_part_2').is(':checked')) {   // if part time payment
			var paymentTotal = parseFloat(payment_1)+parseFloat(payment_2);
			payment_total = new Number(paymentTotal+'').toFixed(parseInt(2));
			if(payment_total == parseFloat(final_amount)){  // check for part time payemnt to match with final payment
				part_time = 1;
			}else{
				return false;
			}
		}else{
			if(parseFloat(payment_1) == parseFloat(final_amount)){ // check for single payment to match with final payment
				part_time = 0;
			}else{
				return false;
			}
		}

		var print_setting = $("#print_setting").val();
		if (print_setting == 1) {
            var thermal = 1;
        }else{
			var thermal = 0;
		}
		
		var targeturl = $("#final_step_url").val();
		targeturl += '?final_amount='+final_amount;
		targeturl += '&cust_id='+cust_id;
		targeturl += '&payment_1='+payment_1;
		targeturl += '&payment_2='+payment_2;
		targeturl += '&method_1='+method_1;
		targeturl += '&method_2='+method_2;
		targeturl += '&part_time='+part_time;
		//alert(targeturl);
		$.blockUI({ message: 'Updating cart...' });
		
		$.ajax({
				type: 'get',
				url: targeturl,
				beforeSend: function(xhr) {
					xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				},
				success: function(response) {
					var objArr = $.parseJSON(response);
					if(objArr.hasOwnProperty('error')){
						document.getElementById('flash_msg').innerHTML = objArr.error;
						$('#payment_method_0').val("");
						$('#payment_method_1').val("");
						$('#total').val("");
						$('#divid_1').hide();
						$('#payment_method_1').val("");
						$('#full_or_part_1').attr("checked","checked");
						$('#full_or_part_2').removeAttr("checked");
						$('#payment_div').hide();
						$('#product_div').show();
					}else if (objArr.hasOwnProperty('status')) {
						if (objArr.status == 'ok') {
							var id = objArr.id;
							var kiosk_id = objArr.kiosk_id;
							$( "#sale_done" ).dialog({
														resizable: false,
														height:140,
														modal: true,
														closeText: "Close",
														width:300,
														maxWidth:300,
														title: '!!! Sale Done!!!',
														buttons: {
															"OK": function() {
																$( this ).dialog( "close" );
																if (thermal == 1) {
																	var redirect_url = $("#redirect_url_after_success").val();
																	redirect_url += "/"+id;
																	redirect_url += "/"+kiosk_id;
																}else{
																	var redirect_url = $("#redirect_url").val();	
																}
																
																window.location.href = redirect_url;
															}
														}
													});
							
							 //$(window).scrollTop(0);
                        }
                    }
					$.unblockUI();
				},
				error: function(e) {
					$.unblockUI();
					//alert("An error occurred: " + e.responseText.message);
					var msg = "An error occurred: " + e.responseText.message;
				 document.getElementById('error_for_alert').innerHTML = msg;
				 //alert("An error occurred: " + e.responseText.message);
				  $( "#error_for_alert" ).dialog({
						  resizable: false,
						  height:140,
						  modal: true,
						  closeText: "Close",
						  width:300,
						  maxWidth:300,
						  title: '!!! Error!!!',
						  buttons: {
							  "OK": function() {
								  $( this ).dialog( "close" );
							  }
						  }
					  });
					console.log(e);
				}
			});
	});
</script>