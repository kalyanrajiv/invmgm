<div class="customers form">
<?php
	$url = $this->Url->build(array('controller' => 'customers', 'action' => 'get_address'));
	echo $this->Form->create('retailCustomer',array('onSubmit' => 'return validateForm();'));
?>
<div id="error_div" tabindex='1'></div>
	<fieldset>
		<legend><?php echo __('Add Customer'); ?></legend>
		
	<?php
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		echo $this->Form->input('kiosk_id',array('type'=>'hidden','value'=>$kiosk_id));
		echo "<h4>Customer Details</h4>";
		echo "<table>";
			echo "<tr>";
				echo "<td>".$this->Form->input('fname',array('label'=>'First Name'))."</td>";
				echo "<td>".$this->Form->input('lname',array('label'=>'Last Name'))."</td>";
				echo "<td>".$this->Form->input('mobile',array('maxLength'=>11,'id' => 'retailCustomerMobile'))."</td>";
				
			echo "</tr>";
			echo "<tr>";
			echo "<td>";
				echo "<table>";
				echo "<tr>";
				echo "<td>".$this->Form->input('email')."</td>";
				echo "<td>";
						echo $this->Form->input('zip',array(
															'placeholder' => 'Postcode',
															'label'=>false ,
															//'size'=>'10px',
															'rel' => $url,
															'style'=>'margin-top: 15px;margin-left: 0px;width: 137px;height: 15px;'
															));
					echo "</td>";
					echo "<td>";
						echo "<button type='button' id='find_address' class='btn' style='margin-top: 21px;margin-left: -5px;width: 130px;height: 29px;'>Find my address</button>";
					echo "</td>";
					
					echo "</tr>";
				echo "</table>";
				echo "</td>";
				echo "<td>".$this->Form->input('address_1', array('placeholder' => 'property name/no. and street name'));
	?>
		<select name='street_address' id='street_address'><option>--postcode--</option></select>
		<span id='address_missing'><br/><a href='#-1'>Address is not on the list</a></span>
				
					</td>
					<?php
						echo "<td>".$this->Form->input('address_2', array('placeholder' => "further address details (optional)"))."</td></tr>";
			echo "</tr>";
			echo "<tr>";			
				echo "<td>".$this->Form->input('city',array('label' => 'Town/City','placeholder' => "name of town or city"))."</td>";
				echo "<td>".$this->Form->input('state',array('label'=>'County', 'placeholder' => "name of county (optional)"))."</td>";
				//'options'=>$countiesUkOptions,
				echo "<td>".$this->Form->input('country',array('options'=>$countryOptions))."</td>";
			echo "</tr>";
		 
		?>
			 
			 
			</tr></table>
		 
	</fieldset>
<?php
echo $this->Form->Submit(__('Save'),array('name'=>'submit'));
echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Customers'), array('action' => 'index')); ?></li>
	</ul>
</div>
 
<script>
$(function() {
	$('#address_missing').click(function(){
		$('#street_address').hide();
		$('#retailCustomerAddress1').show("");
		$('#retailCustomerAddress1').val("");
		$('#retailCustomerAddress2').val("");
		$('#retailCustomerCity').val("");
		$('#retailCustomerState').val("");		
		$(this).hide();
	});
	$( "#street_address" ).select(function() {
		alert($( "#street_address" ).val());
		$('#retailCustomerAddress1').val($( "#street_address" ).val());
		$('#retailCustomerAddress1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$( "#street_address" ).change(function() {
		$('#retailCustomerAddress1').val($( "#street_address" ).val());
		$('#retailCustomerAddress1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$("#find_address").click(function() {
		var zipCode = $("#retailCustomerZip").val();
		//$.blockUI({ message: 'Just a moment...' });
		//focus++;
		//alert("focusout:"+zipCode);
		//$( "#focus-count" ).text( "focusout fired: " + focus + "x" );	
		var zipCode = $("#retailCustomerZip").val();
		var targeturl = $("#retailCustomerZip").attr('rel') + '?zip=' + escape(zipCode);		
		$.blockUI({ message: 'Just a moment...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
			    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				console.log(response);
				$.unblockUI();
				var obj = jQuery.parseJSON( response);			
				if (response) {
					if (obj.ErrorNumber == 0) {
						$('#street_address').show();
						$('#retailCustomerAddress1').hide("");
						$('#address_missing').show();
						var toAppend = '';
						$('#street_address').find('option').remove().end();
						$.each(obj.Street, function( index, value ) {
							//alert( index + ": " + value );
							toAppend += '<option value="'+value+'">'+value+'</option>';
						});
						$('#street_address').append(toAppend);
						$('#retailCustomerAddress2').val(obj.Address2);
						$('#retailCustomerCity').val(obj.Town);
						$('#retailCustomerState').val(obj.County);
					}else{
						alert("Error Code: "+obj.ErrorNumber+ ", Error Message: "+ obj.ErrorMessage);
					}					
				}
			},
			error: function(e) {
			    $.unblockUI();
			    alert("Error:"+response);
			    alert("An error occurred: " + e.responseText.message);
			    console.log(e);
			}
		});
	});
	$('#retailCustomerAddress1').show();
	$('#street_address').hide();
	$('#address_missing').hide();
	
});
</script>
<script>
$(function() {
	 $("#retailCustomerMobile").keydown(function (event) {		
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||
		event.keyCode == 46 || event.keyCode == 183) {
			;
			
		} else {
			event.preventDefault();
		}
		
        });
	 function isValidEmailAddress(emailAddress) {
		var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
		return pattern.test(emailAddress);
	}
	
});
</script>
<script>
	function validateForm(){
	if($('#RetailCustomerFname').val() == '') {
		$('#error_div').html("Please input the first name").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert("Please input the first name");
		return false;
	}
	
	if ($('#RetailCustomerLname').val() == '') {
		$('#error_div').html("Please input the last name").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert("Please input the last name");
		return false;
	}
	
	if ($('#RetailCustomerMobile').val() == '') {
		$('#error_div').html("Please input the phone number").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert("Please input the phone number");
		return false;
	}else if ($('#RetailCustomerMobile').val().length < 11) {
		$('#error_div').html("Phone number should be minimum 11 characters long!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert('Phone number should be minimum 11 characters long!');
		return false;
	}
	if ($('#email').val() == '') {
		$('#error_div').html("Please input the Retail customer's email").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert("Please input the customer's email");
		return false;
	}else if (!isValidEmailAddress($('#MobileRepairCustomerEmail').val())) {
		$('#error_div').html("Please input valid email address!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert('Please input valid email address!');
		return false;
	}
	
}
</script>