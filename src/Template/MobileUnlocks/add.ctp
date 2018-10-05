<style>
	.ui-draggable {
		width: 500px !important;
	}
	.ui-dialog .ui-dialog-content {
		height: auto !important;
	}
	.ui-dialog-titlebar-close {
		visibility: hidden;
	}
</style>
<div id="dialog-confirm" title="Mobile Unlock Terms">
	<?php echo $terms_unlock;?>
</div>
<div id="virtual-confirm" title="Virtual Booking" style="background: greenyellow; display: none;">
	Virtual booking means unlocking by code. <h2>No Need to Keep customer phone</h2> with you. Please take <h2>Advance payment</h2> in this case.<br/>Are you sure you want to continue with this booking?
</div>
<div id="physical-confirm" title="Physical Booking" style="background: palevioletred; display: none;">
	Physical booking means you will <h2>Keep customers phone</h2> for unlock at center or to send to Gurpal. Please <h2>Do not take advance payment</h2> in this case. Payment must be taken at the time of phone collection.<br/>Are you sure you want to continue with this booking?
</div>
<div class="mobileUnlocks form">
<?php
	$url = $this->Url->build(["controller" => "customers","action" => "get_address"]);
	
	
	echo $this->Form->create($mobile_unlocks, array('id' => 'MobileUnlockAddForm','onSubmit' => 'return validateForm();')); ?>
	<fieldset>
		<legend><?php echo __('Add Mobile Unlock'); ?></legend>
	<?php	$date = date('Y-m-d h:i:s A');
		echo $this->Form->input('unlock_number', array('type' => 'hidden', 'value' => 1));?>
		<div id="error_div" tabindex='1'></div>
		<?php //customer details
		$customerData = $this->Url->build(["controller" => "retail_customers","action" => "get_customer_ajax"]);
		
		echo ('<h4>Customer Details</h4><hr/>');
        echo "<div id='remote'>";
		echo "<input name='cust_email' id='cust_email'  class='typeahead' placeholder='check existing customer email' style='width:250px;padding-right:10px;'/>";echo "&nbsp;&nbsp;<a href='#' id='check_existing' rel = '$customerData'>Check Existing</a>";
		 echo "</div>";
        echo "<table>";
		echo "<tr>";
		if(!empty($this->request->query['customerId'])){ 
			echo "<td>".$this->Form->input('customer_fname', array('id' => 'MobileUnlockCustomerFname','label' => 'First Name', 'value' => $customerdetail['0']['fname'] ))."</td>";
			echo "<td>".$this->Form->input('customer_lname', array('id' => 'MobileUnlockCustomerLname','label' => 'Last Name','value' => $customerdetail['0']['lname']))."</td>";
			echo "<td>".$this->Form->input('customer_contact',array('id' => 'MobileUnlockCustomerContact','label' => 'Mobile/Phone','maxLength' => 11,'value' => $customerdetail['0']['mobile'],'autocomplete' => 'off'))."</td>";
			echo "</tr>";
			echo "<tr>";		
			echo "<td>".$this->Form->input('customer_email' ,array('id' => 'MobileUnlockCustomerEmail','value' => $customerdetail['0']['email']))."</td>";
			echo "<td>";
				echo "<table>";
					echo "<tr>";
						echo "<td>";
						echo $this->Form->input('zip',array('id' => 'MobileUnlockZip','placeholder' => 'Postcode', 'label'=>false, 'rel' => $url,'size'=>'10px','value' => $customerdetail['0']['zip'],'style'=>'width: 120px;'));
						echo "</td>";
						echo "<td>";
						echo "<button type='button' id='find_address' class='btn' style='margin-top: 6px;margin-left: -8px;width: 130px;height: 29px;'>Find my address</button>";
						echo "</td>";
					echo "</tr>";
				echo "</table>";
			echo "</td>";
			echo "<td>".$this->Form->input('customer_address_1', array('id' => 'MobileUnlockCustomerAddress1','placeholder' => 'property name/no. and street name','value' => $customerdetail['0']['address_1']));
		?>
			<select name = 'street_address' id ='street_address'><option>--postcode--</option></select>
			<span id='address_missing'><br/><a href='#-1'>Address is not on the list</a></span>
			</td>	
		<?php
			echo "</tr>";
			echo "<table>";
			echo "<tr>";
			echo "<td>".$this->Form->input('customer_address_2', array('id' => 'MobileUnlockCustomerAddress2','placeholder' => "further address details (optional)",'value' => $customerdetail['0']['address_2']))."</td>";
			echo "<td>".$this->Form->input('city',array('id' => 'MobileUnlockCity','label' => 'Town/City','value' => $customerdetail['0']['city'],'placeholder' => "name of town or city"))."</td>";
			echo "<td>".$this->Form->input('state',array('id' => 'MobileUnlockState','label'=>'County', 'placeholder' => "name of county (optional)",'value' => $customerdetail['0']['state']))."</td>";
			echo "<td>".$this->Form->input('country',array('id' => 'MobileRepairCountry','options'=>$countryOptions))."</td>";
			echo "</tr>";
			echo "</table>";
			echo "</tr>";
			echo "</table>";	
		}else{
			echo "<td>".$this->Form->input('customer_fname', array('id' => 'MobileUnlockCustomerFname','label' => 'First Name' ))."</td>";
			echo "<td>".$this->Form->input('customer_lname', array('id' => 'MobileUnlockCustomerLname','label' => 'Last Name'))."</td>";
			echo "<td>".$this->Form->input('customer_contact',array('id' => 'MobileUnlockCustomerContact','label' => 'Mobile/Phone','maxLength' => 11,'autocomplete' => 'off'))."</td>";
			echo "</tr>";
			echo "<tr>";		
			echo "<td>".$this->Form->input('customer_email',array('id' => 'MobileUnlockCustomerEmail',))."</td>";
			echo "<td>";
				echo "<table>";
					echo "<tr>";
						echo "<td>";
						echo $this->Form->input('zip',array('id' => 'MobileUnlockZip','placeholder' => 'Postcode', 'label'=>false, 'rel' => $url,'size'=>'10px','style'=>'width: 120px;' ));
						echo "</td>";
						echo "<td>";
						echo "<button type='button' id='find_address' class='btn' style='margin-top: 6px;margin-left: -8px;width: 130px;height: 29px;'>Find my address</button>";
						echo "</td>";
					echo "</tr>";
				echo "</table>";
			echo "</td>";
			echo "<td>".$this->Form->input('customer_address_1', array('id' => 'MobileUnlockCustomerAddress1','placeholder' => 'property name/no. and street name'));
		?>
			<select name = 'street_address' id='street_address'><option>--postcode--</option></select>
			<span id='address_missing'><br/><a href='#-1'>Address is not on the list</a></span>
			</td>	
		<?php
			echo "</tr>";
			echo "<table>";
			echo "<tr>";
			echo "<td>".$this->Form->input('customer_address_2', array('id' => 'MobileUnlockCustomerAddress2','placeholder' => "further address details (optional)", ))."</td>";
			echo "<td>".$this->Form->input('city',array('id' => 'MobileUnlockCity','label' => 'Town/City','placeholder' => "name of town or city"))."</td>";
			echo "<td>".$this->Form->input('state',array('id' => 'MobileUnlockState','label'=>'County', 'placeholder' => "name of county (optional)" ))."</td>";
			echo "<td>".$this->Form->input('country',array('id' => 'MobileRepairCountry','options'=>$countryOptions))."</td>";
			echo "</tr>";
			echo "</table>";
			echo "</tr>";
			echo "</table>";	
		}
		//phone details
		echo ('<h4>Mobile Details</h4><hr/>');
		$url = $this->Url->build(["controller" => "mobile_unlocks","action" => "get_models"]);
		$priceURL = $this->Url->build(["controller" => "mobile_unlocks","action" => "get_unlock_price"]);
		$networkOptions = $this->Url->build(["controller" => "mobile_unlocks","action" => "get_network_options"]);
		
		echo $this->Form->input('brand_id',array('id' => 'MobileUnlockBrandId','options' => $brands,'rel' => $url));
		
		echo '<div class="input select required">';
		if(isset($mobileModelID)){
			echo $this->Form->input('mobile_model_id',array(
							'id' => 'MobileUnlockMobileModelId',
							'options' => $mobileModels,
							'type' => 'select',
							'empty' => 'choose model',
							'selected' => $mobileModelID,
							'rel' => $networkOptions,
							'div' => false,
							'required' => 'required',
							));
			
		}else{
			echo $this->Form->input('mobile_model_id',array('id' => 'MobileUnlockMobileModelId','options' => $mobileModels,'type' => 'select','empty' => 'choose model', 'rel' => $networkOptions, 'div' => false,'required' => 'required',));
		}
		echo '</div>';
		
		echo '<div class="input select required">';
		echo $this->Form->input('network_id',array(
								'id' =>'MobileUnlockNetworkId',
							   'options' => $networks,
							   'rel' => $priceURL,
							   'empty' => 'choose network',
							   'required' => 'required',
							   'div' => false,
							   )
					);
		echo "</div>";
		if(!isset($estimatedCost)){$estimatedCost = "";}
		echo '<div class="input input required">';
		echo $this->Form->input('unlocking_price', array(
								//'type' => 'text',
								'options' => array(),
								'style'=> 'width:75px',
								'name' => 'estimated_cost',
								'readonly' => true,
								'id' => 'unlocking_price',
								'value' => $estimatedCost,
								'required' => 'required',
								'div' => false,
								));
		echo '</div>';
		
		echo '<div class="input input required">';
?>
	<table style="width:400px;">
		<tr>
			<td><label for="unlocking_days">Unlocking Days</label></td>
			<td></td>
			<td><label for="show_unlock_minutes">Unlock Minutes</label></td>
		</tr>
		<tr>
			<td><?php echo $this->Form->input('unlocking_days', array(
								'type' => 'text',
								'style'=>'width:75px',
								'name' => 'unlocking_days',
								'readonly' => true,
								'id' => 'unlocking_days',
								'required' => 'required',
								'div' => false,
								'label' => false,
								
							));?></php></td>
			<td>OR</td><td>
								<?php
								echo $this->Form->input('show_unlock_minutes', array(
								'type' => 'text',
								'style'=>'width:75px',
								'name' => 'show_unlock_minutes',
								'readonly' => true,
								'id' => 'show_unlock_minutes',
								'required' => 'required',
								'div' => false,
								'label' => false,
							));
								?>
							</td>
		</tr>
	</table>
<?php
		echo $this->Form->input('status_freezed',array('type' => 'hidden',
													   'value' => 1,
													   'label' => false,
													   'div' => false,
													   ));
		
		echo '</div>';
		
		echo $this->Form->input('unlocking_minutes', array(
										'type' => 'hidden',
										'name' => 'unlocking_minutes',
										'id' => 'unlocking_minutes',
										'div' => false,
										));
		
		//echo $this->Form->input('cst', array('id' => 'unlock_cost', 'type' => 'hidden'));
		
		
		echo $this->Form->input('cst', array(
										'type' => 'hidden',
										//'style'=>'width: 50%',
										'name' => 'net_cost',
										//'readonly' => true,
										'id' => 'net_cost',
										'div' => false,
										//'required' => 'required',
										));
		
		
		
		
		echo '<div class="input input required">Imei<span style="color: red;">*</span><span id = "imei_quest" title="Please fill digit only. Incase serial number put in fault description!" style="background: steelblue;color: white;font-size: 14px;margin-left: 3px;">?</span></div>';
		echo "<table>";
		echo "<tr>";
			echo "<td>";
				echo $this->Form->input('imei',	array(
														  'id' => 'MobileUnlockImei',
														  'label' => false,
														  'maxlength'=>14,
														  'div' => false,
														  'style'=>'width: 115px;height:25px; ',
														  'autocomplete' => 'off'
														  ));//,'style'=>"width: 449px;"
			echo "</td>";
			echo "<td>";
				echo $this->Form->input('imei1',array('type' => 'text','label' => false, 'id' =>'imei1','readonly'=>'readonly','style'=>"width: 20px;margin-right: 670px; margin-top: -7px"));
			echo "</td>";
		echo "</tr>";
		echo "</table>";
		//echo $this->Form->input('imei',array('label' => false, 'maxlength'=>16, 'div' => false,'style'=>"width: 449px;margin-left: 7px;"));
		echo $this->Form->input('received_at', array('type' => 'hidden', 'value' => $date));
		echo $this->Form->input('description',array('label' => 'Unlock Description', 'style' => 'width:50%'));
				
		echo $this->Form->input('brief_history', array('type' => 'hidden','label' => 'Unlock History</br/>(For Internal Use)'));
		echo $this->Form->input('status',array('id' => 'MobileUnlockStatus','options' => array('0' => 'Virtually Booked','1' => 'Physically Booked')));
		
	?>
	</fieldset>
	<input type='hidden' name='formValid' id = 'formValid' value='0' />
<?php
$options = array('value' => 'Submit', 'id' => 'submit_button');
echo $this->Form->submit("Submit",$options);
echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Mobile Unlocks'), array('action' => 'index')); ?></li>
		<li><?php # echo $this->Html->link(__('List Kiosks'), array('controller' => 'kiosks', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php # echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
		<li><?php echo $this->element('unlock_navigation'); ?></li>		
	</ul>
</div>
<script>
 var user_dataset = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  //prefetch: "/products/data",
  remote: {
    url: "/retail-customers/custemail?search=%QUERY",
    wildcard: "%QUERY"
  }
});

$('#remote .typeahead').typeahead(null, {
  name: 'email',
  display: 'email',
  source: user_dataset,
  limit:120,
  minlength:3,
  classNames: {
    input: 'Typeahead-input',
    hint: 'Typeahead-hint',
    selectable: 'Typeahead-selectable'
  },
  highlight: true,
  hint:true,
  templates: {
    suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:400px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{email}}</a></strong></div>'),
	header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------hpwaheguru.co.uk---------</b></div>"),
  }
});
</script>

<script>
	$(function() {
	<?php if(empty($this->request->data)){?>
	$( "#dialog-confirm" ).dialog({
	  resizable: false,
	  height:140,
	  modal: true,
	  buttons: {
	    "Agree": function() {
	      $( this ).dialog( "close" );
	    },
	    Cancel: function() {
		tabindex="-1";
		//: 'cancel_button'
	      document.location.href = "<?php echo $this->Url->build(["controller" => "mobile_unlocks","action" => "index"]);
		  ?>";
	    }
	  }/*,
	  open: function() {
		$("#cancel_button").focus();
	    }*/
	});
	<?php }else{ ?>
		//for hiding the dialog-confirm which is not required on the page in this case
		$('#dialog-confirm').hide();
	<?php } ?>
      });
</script>
<script>
	<?php
		echo 'var temp = 1;';
		echo 'var netTemp = 1;';
		echo 'var netMainTemp = 1;';
		if(isset($mobileModelID) && !empty($mobileModelID)){
			//code for pre-selecting values after submit
			echo 'var temp = 0;';
			echo 'var netTemp = 0;';
			echo 'var netMainTemp = 0;';
		}
	?>
$(function() {
	//On change of mobile price
	$('#MobileUnlockBrandId').change(function() {
		if (temp == 0) {
			temp = temp + 1;
			return;
		}
		var selectedValue = $(this).val(); 
		var targeturl = $(this).attr('rel') + '?id=' + selectedValue;
		initialize_inputs();
		$.blockUI({ message: 'Just a moment...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
			    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				$.unblockUI();
				//alert(response);
				/*if (response.error) {
					alert(response.error);
					console.log(response.error);
				}*/				
				if (response) {
					//alert(response);
					//$('#MobileUnlockMobileModelId').children().remove();
					$('#MobileUnlockMobileModelId').find('option').remove().end();
					$('#MobileUnlockMobileModelId').append(response);//html(response.content);
				}
			},
			error: function(e) {
			    $.unblockUI(); //should be updated in other add.ctp also
			    alert("An error occurred: " + e.responseText.message);
			    console.log(e);
			}
		});
	});
	
	$('#MobileUnlockMobileModelId').change(function() {
		var selectedValue = $(this).val();
		var brandId = $('#MobileUnlockBrandId').val();
		var targeturl = $(this).attr('rel') + '?brandID=' + brandId + '&modelID=' + selectedValue;
		//?brandID=1&modelID=243
		initialize_inputs();
		$.blockUI({ message: 'Just a moment...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
			    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				$.unblockUI();
				//alert(response);
				//console.log(response);
				/*if (response.error) {
					alert(response.error);
					console.log(response.error);
				}*/				
				if (response) {
					var networkOptions = "<option>choose network</option>";
					var obj = jQuery.parseJSON( response);
					$('#MobileUnlockNetworkId').find('option').remove().end();
					$.each(obj, function(i, elem){
						networkOptions+= "<option value=" + i + ">" + elem + "</option>";
					});
					$('#MobileUnlockNetworkId').append(networkOptions);//html(response.content);
					//------------------------------------
				}
			},
			error: function(e) {
			    $.unblockUI();
			    alert("An error occurred: " + e.responseText.message);
			    console.log(e);
			}
		});
	});
	
	//case : on change of model set problem type to default and empty the value of estimated cost
	//and estimated price for all 3 cases
	//On change of mobile repair a 
	$('#MobileUnlockNetworkId').change(function() {
		if (netMainTemp == 0) {
			netMainTemp = netMainTemp + 1;
			return;
		}
		var brandID = $('#MobileUnlockBrandId').val();
		var modelID = $('#MobileUnlockMobileModelId').val();
		var networkID = $(this).val();
		$('#unlocking_price').val("");
		$('#unlocking_days').val("");
		$('#unlocking_minutes').val("");
		$('#show_unlock_minutes').val("");
		var targeturl = $(this).attr('rel') + '?networkID=' + networkID + "&brandID="+brandID+"&modelID="+modelID;
		if (parseInt(modelID) == 0) {
			$(this).val("");
			alert("Either there is no model for selected brand or you have not seleted any brand");
			return;
		}
		//alert("Brand ID: "+brandID + " Problem ID "+ problemType + " modelID "+ modelID + " <br/>targeturl "+ targeturl);
		if (networkID == "" || networkID == "0") {return;}
		if (modelID == "" || modelID == "0") {$(this).val("");alert("Please choose model");return;}
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
				if (obj.error == 0) {
					//$('#unlocking_price').val(obj.unlocking_price);
					if (obj.unlocking_days == 0 || obj.unlocking_days == null) {
                        $('#unlocking_minutes').val(obj.unlocking_minutes);
						$('#show_unlock_minutes').val(obj.unlocking_minutes);
						$('#unlocking_days').val(0);
						//alert('min'+obj.unlocking_minutes);
                    }else{
                        $('#unlocking_days').val(obj.unlocking_days);
                    }
					//alert(obj.unlocking_minutes);
					//$('#show_unlock_minutes').val(obj.unlocking_minutes);
					$('#net_cost').val(obj.unlocking_cost);
					
					var startCost = parseInt(obj.unlocking_price);
					var endCost = startCost + 50;
					var optionStr = "";
					for(i = startCost; i <= endCost; i++){
						optionStr += "<option value='" + i + "' >" + i + "</option>";
					}
					$('#unlocking_price').find('option').remove().end();
					$('#unlocking_price').append(optionStr);
					
					$('#unlocking_price option[value=obj.unlocking_price]').prop('selected', 'selected').change();
					console.log(response);
				}else{
					$('#unlocking_days').val("");
					$('#unlocking_minutes').val("");
					$('#show_unlock_minutes').val();
					//$('#unlocking_price').val("");
					$('#unlocking_price').find('option').remove().end();
					$('#unlocking_price').append("<option value='0'></option>");
					$('#unlock_cost').val("");
					alert("No price for this combination");
				}
				
				//if (response) {
				//	$('#MobileUnlockMobileModelId').find('option').remove().end();
				//	$('#MobileUnlockMobileModelId').append(response);//html(response.content);
				//}
			},
			error: function(e) {
			    $.unblockUI();
			    alert("An error occurred: " + e.responseText.message);
			    console.log(e);
			}
		});
	});
	//---------------------------------------------
});
function initialize_inputs() {	
	//$('#MobileUnlockMobileModelId').val(""); 
	$('#MobileUnlockNetworkId').val("");
	$('#unlocking_price').val("");
	$('#unlocking_days').val("");
	$('#unlocking_minutes').val("");
	$('#show_unlock_minutes').val("");
}
if (netTemp == 1) {
	initialize_inputs(); //this function needs to be checked
}
if( parseInt($("#MobileUnlockBrandId")[0].selectedIndex) != 0){
	$( document ).ready(function() {
		var brandId = $('#MobileUnlockBrandId').val();
		$('#MobileUnlockBrandId').change();
	});
}
</script>
<script>
	
$(function() {
	//--------code for getting customers by ajax call
	$("#check_existing").click(function() {
		var custEmail = $("#cust_email").val();
		var cutomerURL = $("#check_existing").attr('rel') + '?cust_email=' + escape(custEmail);
		//------------
		$.blockUI({ message: 'Just a moment...' });
		$.ajax({
			type: 'get',
			url: cutomerURL,
			beforeSend: function(xhr) {
			    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				$.unblockUI();
				
				var obj = jQuery.parseJSON( response);
				$("#MobileUnlockCustomerFname").val(obj.fname);
				$("#MobileUnlockCustomerLname").val(obj.lname);
				$("#MobileUnlockCustomerContact").val(obj.mobile);
				$("#MobileUnlockCustomerEmail").val(obj.email);
				$("#MobileUnlockZip").val(obj.zip);
				$("#MobileUnlockCustomerEmail").val(obj.email);
				$("#MobileUnlockZip").val(obj.zip);
				$("#MobileUnlockCustomerAddress1").val(obj.address_1);
				$("#MobileUnlockCustomerAddress2").val(obj.address_2);
				$("#MobileUnlockCity").val(obj.city);
				$("#MobileUnlockState").val(obj.state);
				//$("#MobileUnlockCountry").val(obj.country);
				var country = obj.country;
				if (country != "") {
					if (country) {
                     // alert(obj.country);
					   $("#MobileRepairCountry").val(obj.country);
                    } 
                }
				if (response) {
					if (obj.ErrorNumber == 0) {
						
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
		//------------
	});
	//-----------------------------------------------
	$('#address_missing').click(function(){
		$('#street_address').hide();
		$('#MobileUnlockCustomerAddress1').show("");
		$('#MobileUnlockCustomerAddress1').val("");
		$('#MobileUnlockCustomerAddress2').val("");
		$('#MobileUnlockCity').val("");
		$('#MobileUnlockState').val("");		
		$(this).hide();
	});
	$( "#street_address" ).select(function() {
		alert($( "#street_address" ).val());
		$('#MobileUnlockCustomerAddress1').val($( "#street_address" ).val());
		$('#MobileUnlockCustomerAddress1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$( "#street_address" ).change(function() {
		$('#MobileUnlockCustomerAddress1').val($( "#street_address" ).val());
		$('#MobileUnlockCustomerAddress1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$("#find_address").click(function() {
		var zipCode = $("#MobileUnlockZip").val();
		//$.blockUI({ message: 'Just a moment...' });
		//focus++;
		//alert("focusout:"+zipCode);
		//$( "#focus-count" ).text( "focusout fired: " + focus + "x" );
		var zipCode = $("#MobileUnlockZip").val();
		if (zipCode == "") {
            alert("Please Input Postcode");
			return false;
        }
		var targeturl = $("#MobileUnlockZip").attr('rel') + '?zip=' + escape(zipCode);		
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
						$('#MobileUnlockCustomerAddress1').hide("");
						$('#address_missing').show();
						var toAppend = '';
						$('#street_address').find('option').remove().end();
						$.each(obj.Street, function( index, value ) {
							//alert( index + ": " + value );
							toAppend += '<option value="'+value+'">'+value+'</option>';
						});
						$('#street_address').append(toAppend);
						$('#MobileUnlockCustomerAddress2').val(obj.Address2);
						$('#MobileUnlockCity').val(obj.Town);
						$('#MobileUnlockState').val(obj.County);
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
	$('#MobileUnlockCustomerAddress1').show();
	$('#street_address').hide();
	$('#address_missing').hide();
	
});
	$('#submit_button').click(function(){
		
	});

	$('#MobileUnlockAddForm').submit(function(){
		
	});
	$("#MobileUnlockCustomerContact").keydown(function (event) {		
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||
		event.keyCode == 46 || event.keyCode == 183) {
			;
			//event.keyCode == 190 || event.keyCode == 110 for dots
			//48-57 => 0..9
			//8 => Backspace; 9 => tab 
		} else {
			event.preventDefault();
		}
		//if($(this).val().indexOf('.') !== -1 && event.keyCode == 190){event.preventDefault();}
        });
	
	$("#MobileUnlockImei").keydown(function (event) {		
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||
		event.keyCode == 46 || event.keyCode == 183) {
			;
			////event.keyCode == 190 || event.keyCode == 110 for dots
			//48-57 => 0..9
			//8 => Backspace; 9 => tab 
		} else {
			event.preventDefault();
		}
		//if($(this).val().indexOf('.') !== -1 && event.keyCode == 190){event.preventDefault();}
        });
	$( "#MobileUnlockImei" ).keyup(function() {
		//var MobileUnlockImei = $('#MobileUnlockImei').val();
		if ($('#MobileUnlockImei').val().length < 14) {
			//alert('hello');
			$('#imei1').val("");
		}
		
});
	
	function validateForm(){
		var networkId = $('#MobileUnlockNetworkId').val();
		if(networkId == "" || networkId == 0 || networkId == 'choose network'){
			$('#error_div').html('Please select a network!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert("Please select a network!");
			return false;
		}
		
		if ($('#MobileUnlockCustomerFname').val() == '') {
			$('#error_div').html('Please input the first name!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert("Please input the first name!");
			return false;
		}
		
		if ($('#MobileUnlockCustomerLname').val() == '') {
			$('#error_div').html('Please input the last name!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert("Please input the last name!");
			return false;
		}
		
		if ($('#MobileUnlockCustomerContact').val() == '') {
			$('#error_div').html('Please input the phone number!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert("Please input the phone number!");
			return false;
		}else if ($('#MobileUnlockCustomerContact').val().length < 11) {
			$('#error_div').html('Phone number should be minimum 11 characters long!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert('Phone number should be minimum 11 characters long!');
			return false;
		}
		
		if ($('#MobileUnlockCustomerEmail').val() == '') {
			//$('#error_div').html('Please input the customer"s email!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			//alert("Please input the customer's email!");
			//return false;
		}else if (!isValidEmailAddress($('#MobileUnlockCustomerEmail').val())) {
			$('#error_div').html('Please input valid email address!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert('Please input valid email address!');
			return false;
		}
		
		var networkId = $('#MobileUnlockNetworkId').val();
		if(networkId == "" || networkId == 0 || networkId == 'choose network'){
			$('#error_div').html('Please select a network!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert("Please select a network");
			return false;
		}
		
		if ($('#MobileUnlockDescription').val() == '') {
			$('#error_div').html('Please input the unlock description!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert("Please input the unlock description!");
			return false;
		}
		
		if ($('#MobileUnlockImei').val() == '') {
			$('#error_div').html('Please input the mobile imei!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert("Please input the mobile imei!");
			return false;
		}else if ($('#MobileUnlockImei').val().length < 14) {
			$('#error_div').html('Input imei should be of 14 characters!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert('Input imei should be of 14 characters!');
			return false;
		}
		validateAgree();
		if (parseInt($('#formValid').val()) == 1) {
			return true;
		}else{
			return false;
		}
		
	}
	
	function validateAgree(){
		var bookingmode = $('#MobileUnlockStatus').val();
		if (bookingmode == 0) {
			$( "#virtual-confirm" ).dialog({
				resizable: false,
				height:140,
				modal: true,
				buttons: {
				  "Agree": function() {
					//alert('Agree');
					$('#formValid').val('1');
					$('#MobileUnlockAddForm').submit();
				  },
				  Cancel: function() {
					//alert('Cancel');
					$(this).dialog("close");
				  }
				}
		    });
		}else{
			$( "#physical-confirm" ).dialog({
				resizable: false,
				height:140,
				modal: true,
				buttons: {
				  "Agree": function() {
					//alert('Agree');
					$('#formValid').val('1');
					$('#MobileUnlockAddForm').submit();
				  },
				  Cancel: function() {
					//alert('Cancel');
					$(this).dialog("close");
				  }
				}
		    });
		}
		
	}
	
	function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
    return pattern.test(emailAddress);
}
	
	$(function() {
	  $( document ).tooltip();
	});
    
    //The check digit is validated in three steps:
    //Starting from the right, double a digit every two digits (e.g., 5 - 10).
    //Sum the digits (e.g., 10 - 1+0). Check if the sum is divisible by 10.
    //Conversely, one can calculate the IMEI by choosing the check digit that would give a sum divisible by 10.
    //For example -
    //IMEI 49015420323751?
    //
    //IMEI	4	9	0	1	5	4	2	0	3	2	3	7	5	1	 ?
    //Double every other	4	18	0	2	5	8	2	0	3	4	3	14	5	2	 ?
    //Sum digits	4 + (1 + 8) + 0 + 2 + 5 + 8 + 2 + 0 + 3 + 4 + 3 + (1 + 4) + 5 + 2 + ? = 52 + ?
    //To make the sum divisible by 10, we set ? = 8, so the IMEI is 490154203237518.
    
    $('#MobileUnlockImei').keyup(function(event){
        if ($('#MobileUnlockImei').val().length == 14 && ((event.keyCode >= 48 && event.keyCode <= 57 ) ||
		( event.keyCode >= 96 && event.keyCode <= 105))) {
            var i;
            var singleNum;
            var finalStr = 0;
            var total = 0;
            var numArr = $('#MobileUnlockImei').val().split('');
            
            for (i = 0; i < $('#MobileUnlockImei').val().length; i++) {
                if (i%2 != 0) {
                    //since array starts with 0 key, multiplying the key which is not divisible by 2 with 2 ie. 1,3,5 etc till 13
                    singleNum = 2*numArr[i];
                } else {
                    singleNum = numArr[i];
                }
                finalStr+=singleNum;
            }
            
            //below creating the array from string and applying foreach to sumup all the values
            var finalArr = finalStr.split('');
            $.each(finalArr, function(key,numb){
                total+=parseInt(numb);
            });
            
            //now for example the total is 52, we need to add 8 to make it 60 ie. divisible by 10. Then 8 will be the next number in imei
            var Dnum = parseInt(Math.ceil(total/10)*10-total);//this is the required number
            var newNumb = $('#MobileUnlockImei').val() + Dnum;
             $('#imei1').val(Dnum);
        }
    });
</script>
