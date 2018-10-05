<div class="invoiceOrders view">
	<div id="error_div" tabindex='1'></div>
	 <?php $redirect_url = $this->Url->build(array('controller' => 'mobile_repairs', 'action' => 'index')); ?>
   <input type='hidden' name='redirect_url' id='redirect_url' value='<?php echo $redirect_url; ?>'/>
   <div id="error_for_alert" title="error_for_alert">Error</div>
   <?php $final_step_url = $this->Url->build(array('controller' => 'mobile_repairs', 'action' => 'final_step_ajax')); ?>
<input type='hidden' name='final_step_url' id='final_step_url' value='<?=$final_step_url?>' />
<?php
//pr($this->Session->read());
//pr($this->request);
$paymentType = array('Cash' => 'Cash', 'Card' => 'Card');
$redirect_url_after_success = $this->Url->build(["controller" => "prints","action" => "repair"]);
$print_setting = $setting['print_type'];
$repair_id = "";
	if(is_array($this->request->Session()->read('final_parts_basket'))){
		$finalAmount = $this->request->Session()->read('final_parts_basket.total_cost');
	}elseif(is_array($this->request->Session()->read('manager_edit_repair_data'))){
		$finalAmount = 0;
		$dataArray = $this->request->Session()->read('manager_edit_repair_data');
		$costArr = explode('|',$dataArray['MobileRepair']['estimated_cost']);
		foreach($costArr as $key => $cost){
			$finalAmount+=$cost;
		}
	}else{
		$finalAmount = 0;
		$dataArray = $this->request->Session()->read('received_reprd_from_tech_data');
		$repair_id = $dataArray['MobileRepair']['id'];
		$costArr = explode('|',$dataArray['MobileRepair']['estimated_cost']);
		foreach($costArr as $key => $cost){
			$finalAmount+=$cost;
		}
	}
	if(!empty($this->request->data)){
		$finalAmount = 0;
		if(!empty($this->request->data)){
			$cost_arr = explode('|',$this->request->data['estimated_cost']);
			foreach($cost_arr as $key => $cost){
				$finalAmount+=$cost;
			}
		}
	}
	
	 $amount = number_format((float)$finalAmount, 4, '.', '');
?>
<input type='hidden' name='redirect_url_after_success' id='redirect_url_after_success' value='<?=$redirect_url_after_success?>' />
<input type='hidden' name='print_setting' id='print_setting' value='<?=$print_setting?>' />

<input type='hidden' id="repiar_id" value= '<?php echo $repair_id; ?>'/>
<table style='width:350px;'>
	<tr>
		<td><span style=''><h2><?php echo __('Payment1'); ?></h2></span></td>
		<td><input type='radio' id = 'full_or_part_1' name='full_or_part' value='1' checked = 'checked' onClick='full_Part(1);'></td><td>Full Payment</td>
		<td><input type='radio' id = 'full_or_part_2' name='full_or_part' value='0' onClick='full_Part(0);'></td><td>Part Payment</td>
	</tr>
</table>
	<?php
	if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
		
	}else{
			echo $this->Form->create('Payment');
	}?>
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
				<td><?php echo $this->Form->input('Payment Method',array('options'=>$paymentType,'name'=>'data[Payment][Payment_Method][]','id' => 'method_'.$i))?></td>
			</tr>
			
				 <?php echo $this->Form->input('Description',array('type'=>'hidden','style'=>'width: 136px;height: 15px;','name'=>"data[Payment][Description][$i]"))?> 
			
			<tr>
				<td><?php echo $this->Form->input('Amount',array(
							'id' => "payment_method_$i",
							'type'=>'text',
							'style'=>'width: 55px;height: 25px;',
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
	Total : <input type="text" name="total" readonly id="total" value='0.00' style="width: 55px;height: 25px;margin-bottom: 10px;" disabled='disabled'/> Invoice Amount:<?php echo $amount;?> <strong>Due Amount:</strong><span id ='due_amount'><?php echo $amount;?></span>
	<input type="hidden" name="final_amount" value="<?php echo $amount;?>" id="final_amount"/>
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
				if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){ ?>
					<input type="submit" id="submit" name='submit1' value="submit" class = "final_step_pay"style="margin-top: 16px;"/>
		<?php	}else{
					echo $this->Form->end($options);
				}
				?>
</td>
				<td><input type="submit" id="s_cancel_payment" name='cancel' value="Cancel" style="margin-top: 16px;"/></td>
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
		$("#payment_method_0").prop("readonly", true);
		$('#payment_method_0').val(dueAmnt);
		$('#payment_method_1').val("");
	})
	
	$('#full_or_part_2').click(function(){
		$("#payment_method_0").prop("readonly", false);
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
		if(totalamount!= dueAmount) {
			$('#error_div').html('Please enter the amount equivalent to amount!').css({"background-color": "pink", "color": "red", "font-size": "20px"}).focus();
		 	//alert("Please enter the amount equivalent to amount!");//code
			return false;
		}
				 
	})
</script>
<script>
	$(document).on('click','#s_cancel_payment',function(){
		  $('#payment_div').hide();
			$('#repair_form').show();
			return false;
    });
	$(document).on('click','.final_step_pay',function(){
		var final_amount = document.getElementById('final_amount').value;
		var repair_id = document.getElementById('repair_id').value;
		if (repair_id == "" || repair_id == "undefined") {
			 document.getElementById('error_for_alert').innerHTML = "Repair Id Is Missing";
						$( "#error_for_alert" ).dialog({
									  resizable: false,
									  height:140,
									  modal: true,
									  closeText: "Close",
									  width:300,
									  maxWidth:300,
									  title: '!!! Error !!!',
									  buttons: {
										  "OK": function() {
											  $( this ).dialog( "close" );
											  return false;
											//   var redirect_url = $("#redirect_url").val();
											//	window.location.href = redirect_url;
										  }
									  }
								  });
            return false;
        }
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
			if(parseFloat(payment_1)+parseFloat(payment_2) == parseFloat(final_amount)){  // check for part time payemnt to match with final payment
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
		targeturl += '&repair_id='+repair_id;
		targeturl += '&payment_1='+payment_1;
		targeturl += '&payment_2='+payment_2;
		targeturl += '&method_1='+method_1;
		targeturl += '&method_2='+method_2;
		targeturl += '&part_time='+part_time;
		targeturl += '&received_reprd_from_tech_data=1';
		
		//alert(targeturl);return false;
		$.blockUI({ message: 'Updating cart...' });
		
		$.ajax({
				type: 'get',
				url: targeturl,
				beforeSend: function(xhr) {
					xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				},
				success: function(response) {
					var objArr = $.parseJSON(response);
					if(objArr.hasOwnProperty('status')){
						 document.getElementById('error_for_alert').innerHTML = objArr.status;
						 var id = objArr.id;
						$( "#error_for_alert" ).dialog({
									  resizable: false,
									  height:140,
									  modal: true,
									  closeText: "Close",
									  width:300,
									  maxWidth:300,
									  title: '!!! Done !!!',
									  buttons: {
										  "OK": function() {
											  $( this ).dialog( "close" );
											   if (thermal == 1) {
													  var redirect_url = $("#redirect_url_after_success").val();
													  redirect_url += "/"+id;
												}else{
													var redirect_url = $("#redirect_url").val();
												}
												window.location.href = redirect_url;
										  }
									  }
								  });
					} else if (objArr.hasOwnProperty('error')) {
                         document.getElementById('error_for_alert').innerHTML = objArr.error;
						$( "#error_for_alert" ).dialog({
									  resizable: false,
									  height:140,
									  modal: true,
									  closeText: "Close",
									  width:300,
									  maxWidth:300,
									  title: '!!! Error !!!',
									  buttons: {
										  "OK": function() {
											  $( this ).dialog( "close" );
											  return false;
											//   var redirect_url = $("#redirect_url").val();
											//	window.location.href = redirect_url;
										  }
									  }
								  });
                    }
					$.unblockUI();
				},
				error: function(e) {
					$.unblockUI();
					var msg = "An error occurred: " + e.responseText.message;
					//alert(msg);
					document.getElementById('error_for_alert').innerHTML = msg;
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
								  return false;
							  }
						  }
					  });
					console.log(e);
				}
				
		});
		return false;
	});
</script>