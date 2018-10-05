<div class="invoiceOrders view">
	<div id="error_div" tabindex='1'></div>
	<div id="error_for_alert"></div>
<?php
	if(is_array($this->request->Session()->read('resale_data_session'))){
		$resaleData = $this->request->Session()->read('resale_data_session.MobileBlkReSale');
		if(array_key_exists('discounted_price',$resaleData) && $resaleData['discounted_price'] > 0){
			$finalAmount = $resaleData['discounted_price'];
		}else{
			$finalAmount = $resaleData['selling_price'];
		}
		if(array_key_exists('prchseId',$resaleData)){
			$purchase_id = $resaleData['prchseId'];
		}
		$amount = number_format((float)$finalAmount, 2, '.', '');
	}
	$print_setting = $setting['print_type'];
?>

<?php $after_payment_redirect = $this->Url->build(array('controller'=> 'mobile_purchases', 'action' => 'index'));?>
<input type="hidden" id="after_payment_redirect" name="after_payment_redirect" value = '<?php echo $after_payment_redirect ?>' />


<?php $after_payment_redirect_print = $this->Url->build(array('controller'=> 'prints', 'action' => 'mobile_bulk_sale'));?>
<input type="hidden" id="after_payment_redirect_print" name="after_payment_redirect" value = '<?php echo $after_payment_redirect_print ?>' />

<input type="hidden" id="print_setting" name="print_setting" value = '<?php echo $print_setting ?>' />

<input type="hidden" id="purchase_id" name="purchase_id" value='<?php echo $purchase_id;?>' />
<?php $cancel_redirect = $this->Url->build(array('controller'=> 'mobile_blk_re_sales', 'action' => 'add',$purchase_id));?>
<input type="hidden" id="cancel_redirect" name="cancel_redirect" value = '<?php echo $cancel_redirect ?>' />
<?php $cancel_ajax = $this->Url->build(array('controller' => 'mobile_re_sales','action' => 'cancel_ajax'));?>
<input type="hidden" id="cancel_ajax" name='cancel_ajax' value='<?php echo $cancel_ajax;?>' />
<?php $do_payment = $this->Url->build(array('controller' => 'mobile_re_sales', 'action' => 'do_payment'));?>
<input type="hidden" id="do_payment" name="do_payment" value='<?php echo $do_payment;?>' />



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
				<td><?php echo $this->Form->input('Payment Method',array('options'=>$paymentType,'id' => "method_".$i,'name'=>'data[Payment][Payment_Method][]'))?></td>
			</tr>
			
				 <?php echo $this->Form->input('Description',array('type'=>'hidden','id' => 'method_'.$i,'style'=>'width: 136px;height: 15px;','name'=>"data[Payment][Description][$i]"))?> 
			
			<tr>
				<td><?php echo $this->Form->input('Amount',array(
							'id' => "payment_method_$i",
							'type'=>'text',
							'style'=>'width: 55px;height: 17px;',
							'label'=>'Amount',
							'name'=>"data[Payment][Amount][$i]",								)
								  )?></td>
			</tr>
		</table></div>
		
	</td>
	<?php
		}
	?>		
	</tr>
</table>
	Total : <input type="text" name="total" readonly id="total" value='0.00' style="width: 55px;height: 17px;margin-bottom: 10px;"/> Invoice Amount:<?php echo $amount;?> <strong>Due Amount:</strong><span id ='due_amount'><?php echo $amount;?></span>
	<input type="hidden" name="final_amount" value="<?php echo $amount;?>" id='final_amount'/>
	<div class="submit">
		<table style="width: 20%;">
			<tr>
				<td><?php $options = array(
						'label' => 'submit',
						'name'  =>'submit',
						'id'    => 'submit',
						'div' => array(
							'class' => 'submit',
						)
					);
//echo $this->Form->end($options);
				?>
				<input type="submit" name='submit1' value="Submit" id='submit_payment' style="margin-top: 16px;"/>
				</td>
				<td><input type="submit" name='cancel' value="Cancel" id='cancel_payment' style="margin-top: 16px;"/></td>
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
			//$('#divid_2').hide();
			//$('#payment_method_2').val("");
		}else{			
			$('#divid_1').show();
			//$('#divid_2').show();
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
		if($('#full_or_part_2').is(':checked')) {
			var blance = dueAmount - firstBox;		
			document.getElementById( 'payment_method_1' ).value =  blance.toFixed(2);
		}
	});
	
	$("#payment_method_1").focusout(function () {
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
	 $('#submit').click(function(){
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
		if (totalamount != dueAmount) {
			$('#error_div').html('Please enter the amount equivalent to amount!').css({"background-color": "pink", "color": "red", "font-size": "20px"}).focus();
		 	//alert("Please enter the amount equivalent to amount!");//code
			return false;
		}
	})
</script>
<script>
	$(document).on('click','#cancel_payment',function(){
		var target_url = $('#cancel_ajax').val();
		var cancel_redirect = $('#cancel_redirect').val();
		$.blockUI({message:'updating cart....'});
		$.ajax({
			type: 'get',
			url: target_url,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response){
				var objArr = $.parseJSON(response);
				if (objArr.status == 'ok') {
					window.location.href = cancel_redirect;
                }
				$.unblockUI();
			},
			error: function(e){
				$.unblockUI();
				alert("An Error Occured:" + e.responseText.message);
				console.log(e);
			}
		});
	});
</script>
<script>
	$(document).on('click','#submit_payment',function(){
		var after_payment_redirect = $('#after_payment_redirect').val();
		var purchase_id = $('#purchase_id').val();
		var final_amount = $('#final_amount').val();
		var payment_1 =$('#payment_method_0').val();
		var payment_2 =$('#payment_method_1').val();
		var method_1 =$('#method_0').val();
		var method_2 =$('#method_1').val();
		var part_time = 0;
        if ($('#full_or_part_2').is(':checked')) {
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
		if ($('#full_or_part_1').is(':checked')) {
            if (parseFloat(payment_1) == parseFloat(final_amount)) {
					part_time = 0;    
            }else{
				return false;
			}
        }else{
            if (parseFloat(payment_1) + parseFloat(payment_2) == parseFloat(final_amount)) {
                part_time = 1;
            }else{
				return false;
			}
        }
		
		var print_setting = $('#print_setting').val();
		if (print_setting == 1) {
			var thermal = 1;
        }else{
			var thermal = 0;
		}
		
		
		var target_url = $('#do_payment').val();
		target_url += "?final_amount="+final_amount;
		target_url += "&payment_1="+payment_1;
		target_url += "&payment_2="+payment_2;
		target_url += "&method_1="+method_1;
		target_url += "&method_2="+method_2;
		target_url += "&part_time="+part_time;
		target_url += "&purchase_id="+purchase_id;
		//alert(target_url);return false;
		
		$.blockUI({message:'updating cart....'});
		
		$.ajax({
			type: 'get',
			url: target_url,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response){
				var objArr = $.parseJSON(response);
				if (objArr.hasOwnProperty('status')) {
					document.getElementById('error_for_alert').innerHTML = objArr.status;
					var id = objArr.id;
                    $.unblockUI();
					$( "#error_for_alert" ).dialog({
					  
					  resizable: false,
					  height:140,
					  modal: true,
					  closeText: "Close",
					  width:300,
					  maxWidth:300,
					  title: '!!! Status!!!',
					  buttons: {
						  "OK": function() {
							  $( this ).dialog( "close" );
								if (thermal == 1) {
									var after_payment_redirect = $("#after_payment_redirect_print").val();
									after_payment_redirect +="/"+id;
								}else{
									var after_payment_redirect = $("#after_payment_redirect").val();
								}
							  window.location.href = after_payment_redirect;
						  }
					  }
				  }); 
                } else if (objArr.hasOwnProperty('error')) {
					document.getElementById('error_for_alert').innerHTML = objArr.error;
					$.unblockUI();
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
							  window.location.href = after_payment_redirect;
						  }
					  }
				  }); 
                    
                }
				
			},
			error: function(e){
				$.unblockUI();
				alert("An Error Occured:" + e.responseText.message);
				console.log(e);
			}
		});
		
		
		
	});
</script>