<div class="invoiceOrders view">
	<div id="error_div" tabindex='1'></div>
	<div id="error_pay_div"></div>
<?php
        if(!empty($this->request->pass)){
            $cust_id = $this->request->pass[0];
        }
        if(isset($cust_id) && !empty($cust_id)){  ?>
            <input type="hidden" id="customer_id" value='<?php echo $cust_id;?>' />
        <?php }
	$amount = 0;
	$finalAmount=$this->request->Session()->read('finalAmount');
	if($finalAmount > 0){
		$amount = number_format((float)$finalAmount, 2, '.', '');
	}
?>
<?php
		
    $cancel_url = $this->Url->build(["controller" => "product_receipts","action" => "cancel1"]);
    $after_cancel_url = $this->Url->build(["controller" => "customers","action" => "index"]);
    $submit_click = $this->Url->build(["controller" => "kiosk_product_sales","action" => "final_w_sale"]);
	$redirect_url = $this->Url->build(["controller" => "customers","action" => "index"]);
?>
<input type="hidden" id="cancel_url" value='<?php echo $cancel_url;?>' />
<input type="hidden" id="after_cancel_url" value='<?php echo $after_cancel_url;?>' />
<input type="hidden" id="submit_click" value='<?php echo $submit_click;?>' />
<input type="hidden" id="redirect_url" value='<?php echo $redirect_url;?>' />
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
		for($i = 0; $i < 3; $i++){ //count($paymentType)
			$style = "";
			if($i){$style='display:none;';}
	?>
	<td id="cell">
		<div id="divid_<?php echo $i;?>" style='<?php echo $style;?>'>
		<table>
			<tr>
				<td><?php
				$paymentType['Select Payment Method'] =   'Select Payment Method';
				 
				echo $this->Form->input('Payment Method',array(
																		 'options'=>$paymentType,
																		 'name'=>'Payment[Payment_Method][]',
                                                                         'id' => 'method_'.$i,
																		 'default' => 'Select Payment Method'))?></td>
			</tr>
			<tr>
				<td><?php echo $this->Form->input('Description',array('type'=>'hidden','style'=>'width: 136px;height: 15px;','name'=>"Payment[Description][$i]",'value' => '--'))?></td>
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
	Total : <input type="text" name="total" readonly id="total" value='0.00' style="width: 55px;height: 12px;margin-bottom: 10px;"/> Invoice Amount:<?php echo $amount;?> <strong>Due Amount:</strong><span id ='due_amount'><?php echo $amount;?></span>
	<input type="hidden" name="final_amount" id="final_amount" value="<?php echo $amount;?>"/>
	<?php
	echo "<table><tr><td style='width: 72px;'>";
	$options = array(
						'label' => 'submit',
						'name'  =>'submit',
						'id'    => 'submit',
						'div' => array(
							'class' => 'submit',
							'style' => 'width: 95px',
						)
					);
	  
	//echo $this->Form->end($options);
    ?>
    <input type="button" id="pay_submit" value="Submit" style="width: 63px;height: 40px;" />
    <?php 
	 echo "</td><td>" // style='padding-top: 20px;' ?>
     <input type="button" id="pay_cancel" value="cancel" style="width: 63px;height: 40px;" />
	  <?php 
	 //echo "<div style='height: 14px;background-color: rgb(98, 175, 86);  border-width: 1px; border-style: solid; width: 50px; font-size: 12px; padding: 9px; border-radius: 5px;color: white;'>".$this->Html->link(__('Cancel'), array('controller' => 'product_receipts', 'action' => 'cancel1'))."</div>";?>
	
	</td></tr>
	  </table>
	<div>*Clicking cancel will clear all the items saved in the cart</div>
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
		total = new Number(total+'').toFixed(parseInt(2));
			$('#total').val(total);
			if ($('#total').val() > dueAmount) {
				//alert("Amount is exceeding due amount");//code
				$('#error_div').html('Amount is exceeding due amount').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			}
			var balance = parseFloat(dueAmount) - parseFloat(total);		
			document.getElementById( 'due_amount' ).innerHTML =  balance.toFixed(2);
			return false;
	});
	$("input[id*='payment_method_']").keyup(function (event) {
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
		total = new Number(total+'').toFixed(parseInt(2));
		$('#total').val(total);
		if ($('#total').val() > dueAmount) {
			//alert("Amount is exceeding due amount");//code
			$('#error_div').html('Amount is exceeding due amount').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		}
		var balance = parseFloat(dueAmount) - parseFloat(total);		
		document.getElementById( 'due_amount' ).innerHTML =  balance.toFixed(2);
	});
	$("input[id*='1payment_method_']").keydown(function (event) {
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
		total = new Number(total+'').toFixed(parseInt(2));
		$('#total').val(total);
		if ($('#total').val() > dueAmount) {
			$('#error_div').html('Amount is exceeding due amount').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			//alert("Amount is exceeding due amount");//code
		}
		if($(this).val().indexOf('.') !== -1 && event.keyCode == 190){event.preventDefault();}
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
		if($(this).val().indexOf('.') !== -1 && event.keyCode == 190){event.preventDefault();}
	});
	//keyListener.onKeyDown = function() {
	//	trace("DOWN -> Code: " + Key.getCode() + "\tACSII: " + Key.getAscii() + "\tKey: " + chr(Key.getAscii()));
	//};
	//Key.addListener(keyListener);
	
	$("#full_or_part_2").click(function(){
		for(var i = 0; i < <?php echo 3;?>; i++){
			total += parseFloat($('#payment_method_'+i).val('0'));
		}
	});
	
	$("#full_or_part_1").click(function(){
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
	 $('#pay_submit').click(function(){
		if($('#full_or_part_1').is(':checked')){
			var select = $('#method_0').val();
			if (select== 'Select Payment Method') {
				//alert("Input amount should be more than zero");
				$('#error_div').html('Please select Payment method').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
				return false;   //code
			}
		}else{
			var select_1 = $('#method_0').val();
			var value_1 = $('#payment_method_0').val();
			if (select_1 == 'Select Payment Method') {
				//alert("Input amount should be more than zero");
				if (value_1 > 0) {
                    $('#error_div').html('Please select Payment method').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
					return false;   //code
                }
				
			}
			var select_2 = $('#method_1').val();
			var value_2 = $('#payment_method_1').val();
			if (select_2 == 'Select Payment Method') {
				//alert("Input amount should be more than zero");
				if (value_2 > 0) {
					$('#error_div').html('Please select Payment method').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
				   return false;   //code   
                }
			}
			var select_3 = $('#method_2').val();
			var value_3 = $('#payment_method_2').val();
			if (select_3 == 'Select Payment Method') {
				//alert("Input amount should be more than zero");
				if (value_3 > 0) {
                  $('#error_div').html('Please select Payment method').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
					return false;   //code  
                }
			}
		}
			 
	});
	
	$(document).ready(function(){
		//total = new Number(total+'').toFixed(parseInt(2));
		$('#total').val(dueAmount);
		$('#payment_method_0').val(dueAmount);
	});
</script>
<script>
    $(document).on('click','#pay_cancel',function(){
       var cancel_url = $('#cancel_url').val();
	  // alert(cancel_url);return false;
       var after_cancel_url = $('#after_cancel_url').val();
       
       $.blockUI({ message: 'Updating cart...' });
       $.ajax({
                type: 'get',
                url: cancel_url,
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                },
                success: function(response) {
                    var objArr = $.parseJSON(response);
                    if (objArr.status == "ok") {
                        $.unblockUI();
                        window.location.href = after_cancel_url;
                    }else{
                        window.location.href = after_cancel_url;
                        $.unblockUI();
                    }
                },
                error: function(e) {
                    $.unblockUI();
                    console.log(e);
                }
       });
    });
</script>
<script>
    $(document).on('click','#pay_submit',function(){
        var target_url = $('#submit_click').val();
        var payment_1 = $('#payment_method_0').val();
        var payment_2 = $('#payment_method_1').val();
        var payment_3 = $('#payment_method_2').val();
        
        var method_1 = $('#method_0').val();
        var method_2 = $('#method_1').val();
        var method_3 = $('#method_2').val();
        var customer_id = $('#customer_id').val();
        if (customer_id == "" || customer_id == "undefined" || customer_id == null) {
            alert("No customer Id Found");
            return false;
        }
        var final_amount = $('#final_amount').val();
		final_amount = new Number(final_amount+'').toFixed(parseInt(2));
        var part_time = 0;
        if($('#full_or_part_1').is(':checked')){
            if (parseFloat(payment_1) == parseFloat(final_amount)) {
               part_time = 0;
            }else{
				$('#error_div').html('Please add amount Equivalent to Invoice Amount').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
                return false;
            }
        }else if ($('#full_or_part_2').is(':checked')) {
			var new_pay = parseFloat(payment_1)+parseFloat(payment_2)+parseFloat(payment_3);
			new_pay = new Number(new_pay+'').toFixed(parseInt(2));
            if (new_pay == parseFloat(final_amount)) {
                part_time = 1;
            }else{
				$('#error_div').html('Please add amount Equivalent to Invoice Amount').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
                return false;
            }
        }
        
        target_url += "?part_time="+part_time;
        target_url += "&payment_1="+payment_1;
        target_url += "&payment_2="+payment_2;
        target_url += "&payment_3="+payment_3;
        
        target_url += "&method_1="+method_1;
        target_url += "&method_2="+method_2;
        target_url += "&method_3="+method_3;
        target_url += "&final_amount="+final_amount;
        target_url += "&customer_id="+customer_id;
        
        //alert(target_url);return false;
        
        $.blockUI({ message: 'Updating cart...' });
        $.ajax({
                type: 'get',
                url: target_url,
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                },
                success: function(response) {
					var objArr = $.parseJSON(response);
					if(objArr.hasOwnProperty('status')){
						document.getElementById('error_pay_div').innerHTML = objArr.status;
						$.unblockUI();
						$( "#error_pay_div" ).dialog({
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
																var redirect_url = $("#redirect_url").val();
																window.location.href = redirect_url;
															}
														}
													});
						
					}else if (objArr.hasOwnProperty('error')) {
						document.getElementById('error_pay_div').innerHTML = objArr.status;
                        $.unblockUI();
						$( "#error_pay_div" ).dialog({
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
																var redirect_url = $("#redirect_url").val();
																window.location.href = redirect_url;
															}
														}
													});
                    }
                },
                error: function(e) {
                    $.unblockUI();
                    console.log(e);
                }
        });
        
    });
</script>
