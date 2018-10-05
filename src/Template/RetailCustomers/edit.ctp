 
<div class="customers form">
<?php
    $customer_id = $this->request['data']['id'];
	$url = $this->Url->build(array('controller' => 'customers', 'action' => 'get_address'));
	echo $this->Form->create($RetailCustomersEntity);
?>
	<fieldset>
		<legend><?php echo __('Edit Customer('.$customer_id.')'); ?></legend>
	<?php //pr($this->request);
		echo $this->Form->input('id',array('type'=>'hidden'));
		echo "<h4>Retail Customer Details</h4>";
		echo "<table>";
		echo "<tr>";
            echo "<td>".$this->Form->input('fname',array('label'=>'First Name'))."</td>";
            echo "<td>".$this->Form->input('lname',array('label'=>'Last Name'))."</td>";
            echo "<td>".$this->Form->input('mobile',array('maxLength'=>11))."</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>";
				echo "<table>";
					echo "<tr>";
                         echo "<td>".$this->Form->input('email')."</td>";
						echo "<td>";
                            echo $this->Form->input('zip',array('placeholder' => 'Postcode', 'label'=>false, 'rel' => $url,'size'=>'10px','style'=>'margin-top: 13px;margin-left: -8px;width: 112px;height: 18px;'));
						echo "</td>";
						echo "<td>";
						echo "<button type='button' id='find_address' class='btn' style='margin-top: 19px;margin-left: -8px;width: 130px;height: 29px;'>Find my address</button>";
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
			
			echo "<tr>";			
			
			echo "<td>".$this->Form->input('city',array('label' => 'Town/City','placeholder' => "name of town or city"))."</td>";
			echo "<td>".$this->Form->input('state',array('label'=>'County', 'placeholder' => "name of county (optional)"))."</td>";
			//'options'=>$countiesUkOptions,
			echo "<td>".$this->Form->input('country',array('options'=>$countryOptions))."</td>";
			echo "</tr>";
			
			echo "<tr>";		
			 
			//print_r($this->request['data']);
			//echo $this->request['data']['Customer']['same_delivery_address'];
			
?>
		<td>
				 
			</td>
			</tr>
	<?php	echo "</table>";
	echo $this->Form->submit("Save");
	echo $this->Form->end();
	echo $table;?>
	</fieldset>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		
		<li><?php echo $this->Html->link(__('New Customer'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Customers'), array('action' => 'index')); ?></li>
		<li><?php //echo $this->Html->link(__('Sell'), array('controller'=>'kiosk_product_sales','action' => 'new_sale', $customer_id));
		echo $this->Html->link(__('New Sale'), array(
                                                'controller' => 'kiosk_product_sales',
                                                'action' => 'new_order',
                                                 '?' => array('customerId' => $customer_id))
            );?> </li>
		<li><?php echo $this->Html->link(__('Repair'), array(
                                                'controller' => 'mobile_repairs',
                                                'action' => 'add',
                                                 '?' => array('customerId' => $customer_id))
            ); ?></li>
		<li><?php //echo $this->Html->link(__('Create Performa'), array('controller'=>'invoice_order_details','action' => 'create_invoice', $customer_id));
		
			echo $this->Html->link(__('Mobile Purchase'), array(
                                                'controller' => 'mobile_purchases',
                                                'action' => 'add',
                                                 '?' => array('customerId' =>$customer_id ))
            ); 
		?>
		<?php  ?></li>
		<li><?php  echo $this->Html->link(__('Unlock'), array(
                                                'controller' => 'mobile_unlocks',
                                                'action' => 'add',
                                                 '?' => array('customerId' => $customer_id))
            ); ?> </li>
		
	</ul>
</div>

<script>
$(function() {
	$('#address_missing').click(function(){
		$('#street_address').hide();
		$('#RetailCustomerAddress1').show("");
		$('#RetailCustomerAddress1').val("");
		$('#RetailCustomerAddress2').val("");
		$('#CustomerCity').val("");
		$('#CustomerState').val("");		
		$(this).hide();
	});
	$( "#street_address" ).select(function() {
		alert($( "#street_address" ).val());
		$('#RetailCustomerAddress1').val($( "#street_address" ).val());
		$('#RetailCustomerAddress1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$( "#street_address" ).change(function() {
		$('#CustomerAddress1').val($( "#street_address" ).val());
		$('#CustomerAddress1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$( "#find_address" ).click(function() {
		var zipCode = $("#RetailCustomerZip").val();
		//$.blockUI({ message: 'Just a moment...' });
		//focus++;
		//alert("focusout:"+zipCode);
		//$( "#focus-count" ).text( "focusout fired: " + focus + "x" );
		var zipCode = $("#RetailCustomerZip").val();
		var targeturl = $("#RetailCustomerZip").attr('rel') + '?zip=' + escape(zipCode);		
		$.blockUI({ message: 'Just a moment...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
			    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				$.unblockUI();
				var obj = jQuery.parseJSON( response);			
				if (response) {
					if (obj.ErrorNumber == 0) {
						$('#street_address').show();
						$('#RetailCustomerAddress1').hide("");
						$('#address_missing').show();
						var toAppend = '';
						$('#street_address').find('option').remove().end();
						$.each(obj.Street, function( index, value ) {
							//alert( index + ": " + value );
							toAppend += '<option value="'+value+'">'+value+'</option>';
						});
						$('#street_address').append(toAppend);
						$('#RetailCustomerAddress2').val(obj.Address2);
						$('#CustomerCity').val(obj.Town);
						$('#CustomerState').val(obj.County);
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
	$('#RetailCustomerAddress1').show();
	$('#street_address').hide();
	$('#address_missing').hide();
	
});
</script>
<script>
$(function() {
	 
	//---------------
	$("#RetailCustomerMobile").keydown(function (event) {  
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
	//---------------
});
</script>