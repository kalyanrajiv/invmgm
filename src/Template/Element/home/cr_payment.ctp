 
<?php
if(!isset($paymentType)){
	$paymentType = array();
}

if(empty($paymentType)){
	$paymentType['Select Payment Method'] =   'Select Payment Method';
	//$paymentType['Cheque'] = 'Cheque';
	//$paymentType['Cash'] = 'Cash';
	//$paymentType['Bank Transfer'] = 'Bank Transfer';
	//$paymentType['Card'] = 'Card';
	$paymentType['On Credit'] = 'On Credit';
}

?>
<div id='paymentDiv' style="overflow: scroll; width: 580px; height: 700px; font-size: 9px;">
	<fieldset>
		<table style='width:350px;'>
			<tr>
				<td><span style=''><h2><?php echo __('Payment'); ?></h2></span></td>
				<td><input type='radio' id = 'full_or_part_1' name='full_or_part' value='1' checked = 'checked' onClick='full_Part(1);'></td><td>Full Payment</td>
				<td><input type='radio' id = 'full_or_part_2' name='full_or_part' value='0' onClick='full_Part(0);'></td><td>Part Payment</td>
			</tr>
		</table>
		<form action="/home/save_credit" method="post">
			<input name="_csrfToken" autocomplete="off" value="<?php echo $token = $this->request->getParam('_csrfToken');?>" type="hidden">
			<table>
				<tr>
			<?php
				for($i = 0; $i < 3; $i++){ //count($paymentType)
				$style = "";
				if($i){$style='display:none;';}
			?>
					<td>
						<div id="divid_<?php echo $i;?>" style='<?php echo $style;?>'>
							<table>
								<tr>
									<td><?php
										
											echo $this->Form->input('Payment Method',array(
															 'options'=>$paymentType,
															 'name'=>"Payment[Payment_Method][]",
															 'id'=>"payment_mode_$i",
															 'default' => 'Select Payment Method'))?></td>
								</tr>
								<tr>
									<td><?php echo $this->Form->input('Amount',array(
											'id' => "payment_method_$i",
											'type'=>'text',
											'style'=>'width: 55px;height: 15px;',
											'label'=>'Amount',
											'name'=>"Payment[Amount][$i]",
											'onchange'=>"validateFloatKeyPress(this);"
											//'value' => $amount
											)
									  )?>
									  <input type="hidden" id="total_hidden_amount" value = ""/></td>
								</tr>
							</table>
						</div>
							<?php
								}
							?>
					</td>
				</tr>
			</table>
			Total : <input type="text" name="total" readonly id="total" value='0.00' style="width: 55px;height: 12px;margin-bottom: 10px;"/> Invoice Amount:<span id = "invoice_amt"></span><strong>Due Amount:</strong><span id ='due_amount'><?php //echo $amount;?></span>
			<input type="hidden" name="final_amount" id = "final_amount" value=""/>
			<input type="hidden" name="bulk_discount_input" id = "bulk_discount_input" value=""/>
			<input type="hidden" name="recipt_required" id = "recipt_required" value=""/>
			</br>
			<input type="submit" id = "pay_submit" name="submit1" value="submit"/>
			<input type="button" class = "pay_cancel_button" name="cancel" value="Cancel" style="width: 80px;"/>
		</form>
	</fieldset>
</div>
        
<script>
	function validateFloatKeyPress(el) {
			var v = parseFloat(el.value);
			el.value = (isNaN(v)) ? '' : v.toFixed(2);
		}
		
	$("#full_or_part_2").click(function(){
		for(var i = 0; i < <?php echo 3;?>; i++){
			total += parseFloat($('#payment_method_'+i).val('0'));
		}
	});
	
	$("#full_or_part_1").click(function(){
		dueAmount = parseFloat($('#total_hidden_amount').val());
		parseFloat($('#payment_method_0').val(dueAmount));
	});
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
		//dueAmount = parseFloat($('#total_hidden_amount').val());
		$('#paymentDiv').hide();
		$('#total').val(dueAmount);
		$('#payment_method_0').val(dueAmount);
	});
	
	
	var dueAmount = parseFloat($('#total_hidden_amount').val());
	$("input[id*='payment_method_']").blur(function(){
		dueAmount = parseFloat($('#total_hidden_amount').val());
       // alert(dueAmount);
		if ($('#full_or_part_2').is(':checked')) {
			total = 0;
			for(var i = 0; i < <?php echo 3;?>; i++){
				var amt = $('#payment_method_'+i).val();
				if (amt != "") {
					total += parseFloat($('#payment_method_'+i).val());
				}						
			}
		} else {
			var total = parseFloat($('#payment_method_0').val());
		}
		if (isNaN(total) == true) {
            total = 0
        }
		total = roundNumber(total,2);
		$('#total').val(total);
		if ($('#total').val() > dueAmount) {
			$( "#dialog-pmt-exceeding" ).dialog({
				resizable: false,
				height:140,
				modal: true,
				closeText: "Close",
				width:300,
				maxWidth:300,
				title: '!!! Missing payment method alert!!!',
				buttons: {
					"OK": function() {
						$( this ).dialog( "close" );
					}
				}
			});
			for(var i = 0; i < <?php echo 3;?>; i++){
				$('#payment_method_'+i).val(0);					
			}
			return false;
			//alert("Amount is exceeding due amount");//code
		}
		var balance = parseFloat(dueAmount) - parseFloat(total);		
		document.getElementById( 'due_amount' ).innerHTML =  balance.toFixed(2);
	});
	$("input[id*='payment_method_']").keyup(function (event) {
		dueAmount = parseFloat($('#total_hidden_amount').val());
		if ($('#full_or_part_2').is(':checked')) {
			total = 0;
			for(var i = 0; i < <?php echo 3;?>; i++){
				var amt = $('#payment_method_'+i).val();
				if (amt != "") {
					total += parseFloat($('#payment_method_'+i).val());
				}						
			}
		} else {
			var total = parseFloat($('#payment_method_0').val());
		}
		if (isNaN(total) == true) {
            total = 0
        }
		total = roundNumber(total,2);
		$('#total').val(total);
		if ($('#total').val() > dueAmount) {
			$( "#dialog-pmt-exceeding" ).dialog({
				resizable: false,
				height:140,
				modal: true,
				closeText: "Close",
				width:300,
				maxWidth:300,
				title: '!!! Missing payment method alert!!!',
				buttons: {
					"OK": function() {
						$( this ).dialog( "close" );
					}
				}
			});
			
			for(var i = 0; i < <?php echo 3;?>; i++){
				$('#payment_method_'+i).val(0);					
			}
			return false;
			//alert("Amount is exceeding due amount");//code
		}
		var balance = parseFloat(dueAmount) - parseFloat(total);		
		document.getElementById( 'due_amount' ).innerHTML =  balance.toFixed(2);
	});
	$("input[id*='payment_method_']").keydown(function (event) {
		dueAmount = parseFloat($('#total_hidden_amount').val());
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
		
		if ($('#full_or_part_2').is(':checked')) {
			total = 0;
			for(var i = 0; i < <?php echo 3;?>; i++){
				var amt = $('#payment_method_'+i).val();
				if (amt != "") {
					total += parseFloat($('#payment_method_'+i).val());
				}						
			}
		} else {
			var total = parseFloat($('#payment_method_0').val());
		}
		
		if (isNaN(total) == true) {
            total = 0
        }
		total = roundNumber(total,2);
		$('#total').val(total);
		if ($('#total').val() > dueAmount) {
			for(var i = 0; i < <?php echo 3;?>; i++){
				$('#payment_method_'+i).val(0);					
			}
			//alert("Amount is exceeding due amount");//code
			$( "#dialog-pmt-exceeding" ).dialog({
				resizable: false,
				height:140,
				modal: true,
				closeText: "Close",
				width:300,
				maxWidth:300,
				title: '!!! Missing payment method alert!!!',
				buttons: {
					"OK": function() {
						$( this ).dialog( "close" );
					}
				}
			});
			return false;
		}
		if($(this).val().indexOf('.') !== -1 && event.keyCode == 190){event.preventDefault();}
    });
	
	$('#pay_submit').click(function(){
		var dueAmount = parseFloat($('#total_hidden_amount').val());
		var method = $('#payment_mode_0').val();
		if (method  == 'Select Payment Method') {
            //alert('please select payment method');
			$( "#dialog-pmt" ).dialog({
				resizable: false,
				height:140,
				modal: true,
				closeText: "Close",
				width:300,
				maxWidth:300,
				title: '!!! Missing payment method alert!!!',
				buttons: {
					"OK": function() {
						$( this ).dialog( "close" );
					}
				}
			});
			return false;
        }
		
		if ($('#full_or_part_2').is(':checked')) {
			total = 0;
			for(var i = 0; i < <?php echo 3;?>; i++){
				var amt = $('#payment_method_'+i).val();
				var mode = $('#payment_mode_'+i).val();
				if (amt != "") {
					if (mode != "Select Payment Method") {
                     total += parseFloat($('#payment_method_'+i).val());   
                    }
				}						
			}
			 total = roundNumber(total,2);
			if (total == dueAmount) {
				$.blockUI({ message: 'Just a moment...' });
			}else{
				//alert("either amount or mode is not selected correctly");
				$( "#combination" ).dialog({
				resizable: false,
				height:140,
				modal: true,
				closeText: "Close",
				width:300,
				maxWidth:300,
				title: '!!! Missing payment method alert!!!',
				buttons: {
					"OK": function() {
						$( this ).dialog( "close" );
					}
				}
			});
				return false;
			}
		}else{
			var total = parseFloat($('#payment_method_0').val());
			total = roundNumber(total,2);
			if (total == dueAmount) {
				$.blockUI({ message: 'Just a moment...' });
            }else{
				$( "#dialog-pmt-not-equal" ).dialog({
				resizable: false,
				height:140,
				modal: true,
				closeText: "Close",
				width:300,
				maxWidth:300,
				title: '!!! Missing payment method alert!!!',
				buttons: {
					"OK": function() {
						$( this ).dialog( "close" );
					}
				}
			});
				//alert("total is not equal to amount");
				return false;
			}
		}
	});
	
	
</script>