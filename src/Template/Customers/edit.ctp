<div class="customers form">
<?php
	$url = $this->Url->build(array('controller' => 'customers', 'action' => 'get_address'));
	echo $this->Form->create($customer);
	
?>
	<fieldset>
		<legend><?php echo __('Edit Customer'); ?></legend>
	<?php //pr($this->request);
		echo $this->Form->input('id',array('type'=>'hidden'));
		//$sameDeliveryAddress = $this->request['data']['Customer']['same_delivery_address'];
		echo "<h4>Customer Details</h4>";
		echo "<table>";
		
		echo "<tr>";
		echo "<td>".$this->Form->input('business')."</td>";
		echo "<td>".$this->Form->input('vat_number',array('maxLength'=>20))."</td>";
		echo "<td>".$this->Form->input('email')."</td>";
		echo "</tr>";
		
		echo "<tr>";
		echo "<td>".$this->Form->input('fname',array('label'=>'First Name'))."</td>";
		echo "<td>".$this->Form->input('lname',array('label'=>'Last Name'))."</td>";
		echo "<td>".$this->Form->input('mobile',array('maxLength'=>11))."</td>";
		echo "</tr>";
		
		echo "<tr>";
			echo "<td>";
				echo "<table>";
					echo "<tr>";
						echo "<td>";
						echo $this->Form->input('zip',array('placeholder' => 'Postcode', 'label'=>false, 'rel' => $url,'size'=>'10px','style'=>'width: 120px;'));
						echo "</td>";
						echo "<td>";
						echo "<button type='button' id='find_address' class='btn' style='margin-top: 6px;margin-left: -8px;width: 130px;height: 29px;'>Find my address</button>";
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
            // $countryOptions ='';
			echo "<td>".$this->Form->input('country',array('options'=>$countryOptions))."</td>";
			echo "</tr>";
			
			echo "<tr>";
           
			echo "<td>".$this->Form->input('landline',array('maxLength'=>11))."</td>";
			echo "<td>".$this->Form->input('memo',array('id' => 'Memo'))."</td>";
			echo "<td>".$this->Form->input('agent_id',array('options'=>$agents,"style"=>"width: 163px;",'label'=>'Select Acc manager'));
			echo "<span style='float: right;text-align: right;width: 305px;'><b>**changing agent_id will trasnfer all previous sale of this customer to new agent in all kiosks including wholesale store</b></span>";
			//print_r($this->request['data']);
			//echo $this->request['data']['Customer']['same_delivery_address'];
			
?>
		<br>
				<table cellspacing='1' cellpadding='1'>
					<tr>
						<td>Same delivery address?</td>
						<td>Yes <input type='radio' name='same_delivery_address' value='1' onClick='showhide_info(1);' <?php echo
                        $customer->same_delivery_address == 1 ? "checked":"";?>/></td>
						<td>No<input type='radio' name='same_delivery_address' value='0' onClick='showhide_info(0);' <?php echo  $customer->same_delivery_address == 0 ? "checked":"";?>/>
						</td>
					</tr>
				</table>
			</br>
			</tr>
	<?php	echo "</table>";
		
		echo "<table id='new_delivery_address'>";
		echo "<tr>";
			echo "<td>";
				echo "<table>";
					echo "<tr>";
						echo "<td>";
						echo $this->Form->input('del_zip',array('placeholder' => 'Postcode', 'label'=>false, 'id'=>'CustomerDelZip','rel' => $url,'size'=>'10px','style'=>'width: 120px;'));
						echo "</td>";
						echo "<td>";
						echo "<button type='button' id='find_del_address' class='btn' style='margin-top: 6px;margin-left: -8px;width: 130px;height: 29px;'>Find my address</button>";
						echo "</td>";
					echo "</tr>";
				echo "</table>";
			echo "</td>";
			echo "<td colspan='2'>".$this->Form->input('del_address_1',array('label'=>'Delivery Address 1','placeholder' => 'property name/no. and street name'));
		?>
			<select name='del_street_address' id='del_street_address'><option>--postcode--</option></select>
			<span id='del_address_missing'><br/><a href='#-1'>Address is not on the list</a></span>
			
			</td>
			</tr>
		<?php	echo "<tr>";
		echo "<td>".$this->Form->input('del_address_2',array('label'=>'Delivery Address 2','placeholder' => "further address details (optional)"))."</td>";
			echo "<td>".$this->Form->input('del_city',array('label' => 'Town/City','placeholder' => "name of town or city"))."</td>";
			echo "<td>".$this->Form->input('del_state',array('label'=>'County', 'placeholder' => 'name of county (optional)','label'=>'County'))."</td>";
			echo "</tr>";
		echo "</table>";
		
	?>
	</fieldset>
     <?= $this->Form->submit(__('Submit'),array('name'=>'submit',"style"=>"width: 120px;height: 40px;")) ?>
    <?= $this->Form->end() ?>
<?php 
echo $table;
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
        <li><?php echo $this->Html->link(__('View'), array('action' => 'view', $customer->id)); ?></li>
		<li><?php echo $this->Html->link(__('Sell'), array('controller'=>'kiosk_product_sales','action' => 'new_sale', $customer->id)); ?> </li>
		<li><?php echo $this->Html->link(__('Create Performa'), array('controller'=>'invoice_order_details','action' => 'create_invoice', $customer->id)); ?> </li>
		<li><?php echo $this->Html->link(__('Credit Note'), array('controller'=>'credit_product_details','action' => 'credit_note', $customer->id)); ?> </li>
		<li><?php echo $this->Html->link(__('List Customers'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Customer'), array('action' => 'add')); ?> </li>
	</ul>
</div>

<script type='text/javascript'>
    var optVal = 0;
    function showhide_info(optVal){
        if (optVal == 1){
            document.getElementById('new_delivery_address').style.display = 'none';
        }else{
            document.getElementById('new_delivery_address').style.display = 'table';
        }
    }
</script>
<script>
$(function() {
	$('#address_missing').click(function(){
		$('#street_address').hide();
		$('#CustomerAddress1').show("");
		$('#CustomerAddress1').val("");
		$('#CustomerAddress2').val("");
		$('#CustomerCity').val("");
		$('#CustomerState').val("");		
		$(this).hide();
	});
	$( "#street_address" ).select(function() {
		//alert($( "#street_address" ).val());
		$('#address-1').val($( "#street_address" ).val());
		$('#address-1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$( "#street_address" ).change(function() {
		$('#address-1').val($( "#street_address" ).val());
		$('#address-1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$( "#find_address" ).click(function() {
		var zipCode = $("#zip").val();
		//$.blockUI({ message: 'Just a moment...' });
		//focus++;
		//alert("focusout:"+zipCode);
		//$( "#focus-count" ).text( "focusout fired: " + focus + "x" );
		var zipCode = $("#zip").val();
		if (zipCode == "") {
            alert("Please Input Postcode");
			return false;
        }
		var targeturl = $("#zip").attr('rel') + '?zip=' + escape(zipCode);		
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
						$('#address-1').hide("");
						$('#address_missing').show();
						var toAppend = '';
						$('#street_address').find('option').remove().end();
						$.each(obj.Street, function( index, value ) {
							//alert( index + ": " + value );
							toAppend += '<option value="'+value+'">'+value+'</option>';
						});
						$('#street_address').append(toAppend);
						$('#address-2').val(obj.Address2);
						$('#city').val(obj.Town);
						$('#state').val(obj.County);
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
	$('#CustomerAddress1').show();
	$('#street_address').hide();
	$('#address_missing').hide();
	
});
</script>
<script>
$(function() {
	$('#del_address_missing').click(function(){
		$('#del_street_address').hide();
		$('#del-address-1').show("");
		$('#del-address-1').val("");
		$('#del-address-2').val("");
		$('#del-city').val("");
		$('#del-state').val("");		
		$(this).hide();
	});
	$( "#del_street_address" ).select(function() {
		alert($( "#del_street_address" ).val());
		$('#del-address-1').val($( "#del_street_address" ).val());
		$('#del-address-1').show();
		$('#del_address_missing').hide();
		$(this).hide();
	});
	$( "#del_street_address" ).change(function() {
		$('#del-address-1').val($( "#del_street_address" ).val());
		$('#del-address-1').show();
		$('#del_address_missing').hide();
		$(this).hide();
	});
	$( "#find_del_address" ).click(function() {
		var zipCode = $("#CustomerDelZip").val();
		//$.blockUI({ message: 'Just a moment...' });
		//focus++;
		//alert("focusout:"+zipCode);
		//$( "#focus-count" ).text( "focusout fired: " + focus + "x" );
		var zipCode = $("#CustomerDelZip").val();
		if (zipCode == "") {
            alert("Please Input Postcode");
			return false;
        }
		var targeturl = $("#CustomerDelZip").attr('rel') + '?zip=' + escape(zipCode);		
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
						$('#del_street_address').show();
						$('#del-address-1').hide("");
						$('#del_address_missing').show();
						var toAppend = '';
						$('#del_street_address').find('option').remove().end();
						$.each(obj.Street, function( index, value ) {
							//alert( index + ": " + value );
							toAppend += '<option value="'+value+'">'+value+'</option>';
						});
						$('#del_street_address').append(toAppend);
						$('#del-address-2').val(obj.Address2);
						$('#del-city').val(obj.Town);
						$('#del-state').val(obj.County);
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
	$('#del-address-1').show();
	$('#del_address_missing').hide();
	$('#del_street_address').hide();
 <?php
		if($customer->same_delivery_address == 1){
			echo "document.getElementById('new_delivery_address').style.display = 'none'";
		}
	?>
    $("#CustomerLandline").keydown(function (event) {  
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