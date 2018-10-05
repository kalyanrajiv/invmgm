<?php
$inputData = '';
$conditionRemarks = '';
$imei = $imei1 = '';
if(!isset($internal_repair_default_cost)){
	$internal_repair_default_cost = "";
}

if(!empty($this->request->data)){
	$inputData = $this->request->data;
	//pr($inputData);
	if(array_key_exists('mobile_condition',$inputData['MobileRepair']) && in_array(1000,$inputData['MobileRepair']['mobile_condition'])){
		$conditionRemarks = $inputData['MobileRepair']['mobile_condition_remark'];
	}
	if(strlen($this->request['data']['MobileRepair']['imei']) > 13){
		$rawImei = $this->request['data']['MobileRepair']['imei'];
		$imei = substr_replace($rawImei,'',14);
		$imei1 = substr($rawImei,-1);
	}
}
?>
<div id="dialog-confirm" title="Repair Terms" style="width: 500px !important;">
	<?php echo $terms_repair;?>
</div>
<div id="submit-confirm" title="Please Confirm!" style="background: greenyellow; display: none;">
	Please confirm that <h2>All entries are correct</h2><h2>No Changes</h2> can be made after submission of this booking.<br/>Are you sure you want to continue?
</div>
<div class="mobileRepairs form">
<?php
//pr($mobilePurchaseDetails);
	$kioskName = $kioskContact = $kioskEmail = $kioskZip = $kioskAddress1 = $kioskAddress2 = $kioskCity = $kioskState = $brandId = $modelId = $networkId = $imei = '';
	if(!empty($mobilePurchaseDetails)){
		$kioskName = $mobilePurchaseDetails['kiosk']['name'];
		$kioskContact = $mobilePurchaseDetails['kiosk']['contact'];
		$kioskEmail = $mobilePurchaseDetails['kiosk']['email'];
		$kioskZip = $mobilePurchaseDetails['kiosk']['zip'];
		$kioskAddress1 = $mobilePurchaseDetails['kiosk']['address_1'];
		$kioskAddress2 = $mobilePurchaseDetails['kiosk']['address_2'];
		$kioskCity = $mobilePurchaseDetails['kiosk']['city'];
		$kioskState = $mobilePurchaseDetails['kiosk']['state'];
		$brandId = $mobilePurchaseDetails['brand_id'];
		$modelId = $mobilePurchaseDetails['mobile_model_id'];
		$imei = $mobilePurchaseDetails['imei'];
		$country['GB'] = "United Kingdom";//Keeping only UK for the kiosk users.
	}else{
		echo "<h3>Please choose a valid entry!!</h3>";
	}
	$url = $this->Url->build(array('controller' => 'customers', 'action' => 'get_address'));
	echo $this->Form->create($mobile_repair_entity, array('id' =>'MobileRepairAddForm','onSubmit' => 'return validateForm();')); ?>
	<fieldset>
		<legend><?php echo __('Add Mobile Repair'); ?></legend>
		<div id="error_div" tabindex='1'></div>
	<?php 	$date = date('Y-m-d h:i:s A');		
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		//print_r($kiosk_id);
		echo $this->Form->input('repair_number', array('name' => 'MobileRepair[repair_number]','type' => 'hidden', 'value' => 1));
		echo $this->Form->input('kiosk_id', array('name' => 'MobileRepair[kiosk_id]','type' => 'hidden', 'value' => $kiosk_id));
		echo $this->Form->input('route',array('type'=>'hidden','name'=>'add_repair'));
		echo $this->Form->input('internal_repair',array('id' => 'MobileRepairInternalRepair','name' => 'MobileRepair[internal_repair]','type'=>'hidden', 'value' => '1'));
		echo $this->Form->input('imei', array('name' => 'MobileRepair[imei]','type' => 'hidden'  ,'id'=>'imemivalue'));
		//customer details
			
		echo ('<h4>Customer Details</h4><hr/>');
		echo "<table>";
		echo "<tr>";
		echo "<td>".$this->Form->input('customer_fname', array('id' => 'MobileRepairCustomerFname','name' => 'MobileRepair[customer_fname]','label' => 'First Name','value'=>$kioskName,'readonly'=>'readonly'))."</td>";
		echo "<td>".$this->Form->input('customer_lname', array('id' => 'MobileRepairCustomerLname','name' => 'MobileRepair[customer_lname]','label' => 'Last Name','value'=>$kioskName,'readonly'=>'readonly'))."</td>";
		echo "<td>".$this->Form->input('customer_contact',array('id' => 'MobileRepairCustomerContact','name' => 'MobileRepair[customer_contact]','label' => 'Mobile/Phone','maxlength'=> '11','value'=>$kioskContact,'readonly'=>'readonly'))."</td>";
		echo "</tr>";
		echo "<tr>";		
		echo "<td>".$this->Form->input('customer_email',array('id' => 'MobileRepairCustomerEmail','name' => 'MobileRepair[customer_email]','value'=>$kioskEmail,'readonly'=>'readonly'))."</td>";
		echo "<td>";
			echo "<table>";
				echo "<tr>";
					echo "<td>";
					echo $this->Form->input('zip',array('id' => 'MobileRepairZip','name' => 'MobileRepair[zip]','placeholder' => 'Postcode', 'label'=>false, 'rel' => $url,'value'=>$kioskZip,'size'=>'10px','readonly'=>'readonly'));
					echo "</td>";
				echo "</tr>";
			echo "</table>";
		echo "</td>";
		echo "<td colspan='2'>".$this->Form->input('customer_address_1', array('id' => 'MobileRepairCustomerAddress1','name' => 'MobileRepair[customer_address_1]','placeholder' => 'property name/no. and street name','value'=>$kioskAddress1,'readonly'=>'readonly'));
	?>
		
		</td>
	<?php
		echo "</tr>";
		echo "<tr>";
		echo "<table>";
		echo "<tr>";
		echo "<td>".$this->Form->input('customer_address_2', array('id' => 'MobileRepairCustomerAddress2','name' => 'MobileRepair[customer_address_2]','placeholder' => "further address details (optional)",'value'=>$kioskAddress2,'readonly'=>'readonly'))."</td>";
		echo "<td>".$this->Form->input('city',array('id' => 'MobileRepairCity','name' => 'MobileRepair[city]','label' => 'Town/City','placeholder' => "name of town or city",'value'=>$kioskCity,'readonly'=>'readonly'))."</td>";		
		echo "<td>".$this->Form->input('state',array('id' => 'MobileRepairState','name' => 'MobileRepair[state]','label'=>'County', 'placeholder' => "name of county (optional)",'value'=>$kioskState,'readonly'=>'readonly'))."</td>";		
		echo "<td>".$this->Form->input('country',array('id' => 'MobileRepairCountry','name' => 'MobileRepair[country]','options'=>$country))."</td>";
		echo "</tr>";
		echo "</table>";
		echo "</tr>";
		echo "</table>";		
		//phone details
		echo ('<h4>Mobile Details</h4><hr/>');
		$url = $this->Url->build(array('controller' => 'mobile_repairs', 'action' => 'get_models'));
		$priceURL = $this->Url->build(array('controller' => 'mobile_repairs', 'action' => 'get_repair_price'));
		echo $this->Form->input('brand_id',array('id' => 'MobileRepairBrandId','name' => 'MobileRepair[brand_id]','rel' => $url,'default'=>$brandId,'disabled'=>'disabled'));
		echo $this->Form->input('brand_id',array('id' => 'MobileRepairBrandId','name' => 'MobileRepair[brand_id]','type'=>'hidden','value'=>$brandId));
		echo $this->Form->input('mobile_model_id',array('id' => 'MobileRepairMobileModelId','name' => 'MobileRepair[mobile_model_id]','options' => $mobileModels,'type' => 'select','default'=>$modelId,'empty' => 'choose model','disabled'=>'disabled'));
		echo $this->Form->input('mobile_model_id',array('id' => 'MobileRepairMobileModelId','name' => 'MobileRepair[mobile_model_id]','type'=>'hidden','value'=>$modelId));
		echo "<table>";
			echo "<tr>";
				echo "<td>";
				if(!empty($internal_repair_default_cost)){
					echo $this->Form->input('default_cost', array(
										
										'type' => 'hidden',
										'name' => 'default_cost',
										'id' => 'default_cost',
										'value' => '0.0001', // this selling price of repair is aplicable only  for internal repair purpose 
										));
				}
					echo $this->Form->input('problem_type', array(
										'options' => $problemArrOptns,
										'name' => 'MobileRepair[problem_type_a]',
										'id' => 'problem_type_a',
										'rel' => $priceURL,
										'empty' => '1st problem',
										'div' => false,
										'required' => 'required',
										));
					if(!empty($internal_repair_default_cost)){
						echo $this->Form->input('estimated_cost', array(
										'type' => 'text',
										'style'=>'width: 50%',
										'name' => 'MobileRepair[estimated_cost_a]',
										'readonly' => true,
										'id' => 'estimated_cost_a1',
										));
					}else{
						echo $this->Form->input('estimated_cost', array(
										'type' => 'text',
										'style'=>'width: 50%',
										'name' => 'MobileRepair[estimated_cost_a]',
										'readonly' => true,
										'id' => 'estimated_cost_a'
										));
					}
					
					
					echo $this->Form->input('repair_days', array(
										'type' => 'text',
										'style'=>'width: 50%',
										'name' => 'MobileRepair[repair_days_a]',
										'readonly' => true,
										'id' => 'repair_days_a'
										));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('problem_type', array(
										'options' => $problemArrOptns,
										'name' => 'MobileRepair[problem_type_b]',
										'id' => 'problem_type_b',
										'rel' => $priceURL,
										'div' => false,
										'empty' => '2nd problem'
										));
					if(!empty($internal_repair_default_cost)){
						echo $this->Form->input('estimated_cost', array(
										'type' => 'text',
										'style'=>'width: 50%',
										'name' => 'MobileRepair[estimated_cost_b]',
										'readonly' => true,
										'id' => 'estimated_cost_b1'
										));
					}else{
						echo $this->Form->input('estimated_cost', array(
										'type' => 'text',
										'style'=>'width: 50%',
										'name' => 'MobileRepair[estimated_cost_b]',
										'readonly' => true,
										'id' => 'estimated_cost_b',
										));
					}
					echo $this->Form->input('repair_days', array(
										'type' => 'text',
										'style'=>'width: 50%',
										'name' => 'MobileRepair[repair_days_b]',
										'readonly' => true,
										'id' => 'repair_days_b'
										));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('problem_type', array(
										'options' => $problemArrOptns,
										'name' => 'MobileRepair[problem_type_c]',
										'id' => 'problem_type_c',
										'div' => false,
										'rel' => $priceURL,
										'empty' => '3rd problem'
										));
					if(!empty($internal_repair_default_cost)){
						echo $this->Form->input('estimated_cost', array(
										'type' => 'text',
										'style'=>'width: 50%',
										'name' => 'MobileRepair[estimated_cost_c]',
										'readonly' => true,
										'id' => 'estimated_cost_c1'
										));
					}else{
						echo $this->Form->input('estimated_cost', array(
										'type' => 'text',
										'style'=>'width: 50%',
										'name' => 'MobileRepair[estimated_cost_c]',
										'readonly' => true,
										'id' => 'estimated_cost_c'
										));
					}
					
					
					echo $this->Form->input('repair_days', array(
										'type' => 'text',
										'style'=>'width: 50%',
										'name' => 'MobileRepair[repair_days_c]',
										'readonly' => true,
										'id' => 'repair_days_c'
										));
				echo "</td>";
			echo "</tr>";
		echo "</table>";
		if(count($mobileConditions)){
			$mobileConditions['1000'] = 'Other';
			$chunks = array_chunk($mobileConditions,4,true);
			if(count($chunks)){
				echo "<table id = 'mobile_condition_table'>";
					echo "<tr>";
						echo "<td>";
							echo ('<h4>Mobile Condition</h4><hr/>');
						echo "</td>";
					echo "</tr>";
					echo "<tr>";
					//pr($chunks);
						foreach($chunks as $c => $chunk){
								echo "<td>";
							
									foreach($chunk as $ch => $condition){
										if(!empty($inputData)){
												
											//pr($inputData['MobileRepair']);
											if(array_key_exists('mobile_condition',$inputData['MobileRepair']) && in_array($ch,$inputData['MobileRepair']['mobile_condition'])){
												
												$checked = "checked";	
											}else{
													
												$checked = '';
											}
										}else{
												
											$checked = '';
										}
										echo $this->Form->input($condition, array('type' => 'checkbox',
										'name'=>'MobileRepair[mobile_condition][]',
										'label' => array('style' => "color: blue;"),
										'value' => $ch,
										'hiddenField' => false,
										'checked' => $checked
										));
									}
								echo "<td>";
						}
					echo "</tr>";
				echo "</table>";
			}
			echo $this->Form->input('mobile_condition_remark',array('id' => 'MobileRepairMobileConditionRemark','name' => 'MobileRepair[mobile_condition_remark]','label' => false, 'type' => 'text','placeholder' => 'Mobile Condition Remarks(Fill in case of other)','style' => 'display: none;', 'value' => $conditionRemarks));
		}
		if(count($functionConditions)){
			$functionChunks = array_chunk($functionConditions,2,true);
			if(count($functionChunks)){
				echo "<table>";
					echo "<tr>";
						echo "<td>";
							echo ('<h4>Mobile Functions Condition</h4><hr/>');
						echo "</td>";
					echo "</tr>";
					echo "<tr>";
						foreach($functionChunks as $f => $Fchunk){
								echo "<td>";
									foreach($Fchunk as $fch => $functionCondition){
										if(!empty($inputData)){
											if(array_key_exists('function_condition',$inputData['MobileRepair']) || in_array($fch,$inputData['MobileRepair']['function_condition'])){
												$checked = "checked";	
											}else{
												$checked = '';
											}
										}else{
											$checked = '';
										}
										echo $this->Form->input($functionCondition, array('type' => 'checkbox',
										'name'=>'MobileRepair[function_condition][]',
										'label' => array('style' => "color: blue;"),
										'value' => $fch,
										'hiddenField' => false,
										'checked' => $checked
										));
									}
								echo "<td>";
						}
					echo "</tr>";
				echo "</table>";
			}
		}
		echo "<table>";
			echo "<tr>";
				echo "<td>";
				 //$imei = $this->request->data['MobileRepair']['imei'];
					 $imei1 = substr($imei, -1);
					 $imei2 = substr_replace($imei,'',-1) ;
					//echo $this->Form->input('imei',array('label' => 'IMEI', 'maxlength' => 16,'value'=>$imei,'readonly'=>'readonly'));
					echo $this->Form->input('null',array(
														 'name' => "MobileRepair[null]",
														 'label' => 'IMEI', 'id'=>'MobileRepairImei','maxlength' => 14,'value'=>$imei2,
														 'readonly'=>'readonly',
														 'style'=>"width: 170px;hieght: 20px;"));
				echo "</td>";
				echo "<td>";
				echo $this->Form->input('null',array(
														'name' => "MobileRepair[null]",
													  'type' => 'text',
													  'label' => false,
													  'id' =>'imei1',
													  'readonly'=>'readonly',
													  'value' => $imei1,
													  'style'=>"width: 30px; hieght: 10px; margin-right: 895px; margin-top: 17px"));
			
				echo "</td>";
				//echo "<td>";
				//	echo $this->Form->input('phone_password', array('type' => 'text', 'label' => array('class' => 'Your-Class', 'text' => '<span style="color:red">Phone Password :</span>'), 'width:322px'));
				//echo "</td>";
			echo "</tr>";
		echo "</table>";
		echo "<table>";
		echo "<tr>";
			echo "<td>";
				echo $this->Form->input('description',array(
															'id' => 'MobileRepairDescription','name' => 'MobileRepair[description]',
															'label' => 'Fault Description',
															'style'=>"width: 322px; hieght: 146px; "
															));
			echo "</td>";
			//echo "<td>";
			//	echo $this->Form->input('phone_password', array('type' => 'text', 'label' => array('class' => 'Your-Class', 'text' => '<span style="color:red">Phone Password :</span>'), 'width:322px'));
			//echo "</td>";
			echo "<td>";
				   echo $this->Form->input('phone_password', array('id' => 'MobileRepairPhonePassword','name' => 'MobileRepair[phone_password]','label' => array(
																					
																					'class' => 'Your-Class',
                                                                                    'style'=> 'color: red;',
																					//'text' => '<span style="color:red">Phone Password :</span>',
																					 
																					),
																    'style'=>"width: 322px; margin-right: 395px;hieght: 30px;color: red; margin-top: 1px"
																  
																   ));
				echo "</td>";
			echo $this->Form->input('brief_history', array('id' => 'MobileRepairBriefHistory','name' => 'MobileRepair[brief_history]','type' => 'hidden','label' => 'Repair History</br/>(For Internal Use'));		
			echo $this->Form->input('actual_cost', array('id' => 'MobileRepairActualCost','name' => 'MobileRepair[actual_cost]','type' => 'hidden', 'value' => 0));
			echo $this->Form->input('received_at',array('id' => 'MobileRepairReceivedAt','name' => 'MobileRepair[received_at]','type'=>'hidden','value'=>$date));
			echo $this->Form->input('status',array('id' => 'MobileRepairStatus','name' => 'MobileRepair[status]','type' => 'hidden', 'value' => '1'));	
		echo "<tr>";
		echo "</table>";
		#echo $this->Form->input('status');
		//, 'ext' => 'json'
		// /InventoryManagement/mobile_repairs/get_models.json
		// /InventoryManagement/sandbox/mobile_repairs/get_models.json [if plugin=>sandbox]
	?>
	</fieldset>
	<input type='hidden' name='formValid' id = 'formValid' value='0' />
<?php
echo $this->Form->submit('Submit');
echo $this->Form->end(); ?>
<?php
	//echo $this->Html->script('jquery');
	//$this->Js->JqueryEngine->jQueryObject = '$j';
	//echo $this->Html->scriptBlock(
	//    'var $j = jQuery.noConflict();',
	//    array('inline' => false)
	//);
	// Tell jQuery to go into noconflict mode
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Mobile Repairs'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('New Mobile Repair'), array('action' => 'add')); ?> </li>
		<li><?php #echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
	</ul>	
</div>
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
	      document.location.href = "<?php echo $this->Url->build(array('controller'=>'mobile_purchases','action'=>'index'));?>";
	    }
	  }
	});
	<?php }else{ ?>
		//for hiding the dialog-confirm which is not required on the page in this case
		$('#dialog-confirm').hide();
	<?php } ?>
  });
  </script>
<script>
$(function() {
	//case : on change of model set problem type to default and empty the value of estimated cost
	//and estimated price for all 3 cases
	//On change of mobile repair a 
	$('#problem_type_a').change(function() {
		var default_cost = $('#default_cost').val();
		var brandID = $('#MobileRepairBrandId').val();
		var modelID = $('#MobileRepairMobileModelId').val();
		var problemType = $(this).val();		
		var targeturl = $(this).attr('rel') + '?problemType=' + problemType + "&brandID="+brandID+"&modelID="+modelID;
		if (parseInt(modelID) == 0) {
			$(this).val("");
			alert("Either there is no model for selected brand or you have not seleted any brand");
			return;
		}
		//alert("Brand ID: "+brandID + " Problem ID "+ problemType + " modelID "+ modelID + " <br/>targeturl "+ targeturl);
		if (problemType == "1st problem" || problemType == "0" || problemType == "") {
			$('#repair_days_a').val("0");
			$('#estimated_cost_a').val("0");
			$('#estimated_cost_a1').val("0");
			initializeSum();
			getMaxVal();
			return false;
			}
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
					if (default_cost != '') {
                        $('#estimated_cost_a1').val(default_cost);
                    }
					$('#repair_days_a').val(obj.repair_days);
					$('#estimated_cost_a').val(obj.repair_price);
				}else{
					$('#repair_days_a').val("");
					if (default_cost != '') {
						$('#estimated_cost_a1').val("");
					}
					$('#estimated_cost_a').val("");
					alert("No price for this combination");
				}
				
				//if (response) {
				//	$('#MobileRepairMobileModelId').find('option').remove().end();
				//	$('#MobileRepairMobileModelId').append(response);//html(response.content);
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
	$('#problem_type_b').change(function() {
		var default_cost = $('#default_cost').val();
		var brandID = $('#MobileRepairBrandId').val();
		var modelID = $('#MobileRepairMobileModelId').val();
		var problemType = $(this).val();		
		var targeturl = $(this).attr('rel') + '?problemType=' + problemType + "&brandID="+brandID+"&modelID="+modelID;
		if (parseInt(modelID) == 0) {
			$(this).val("");
			alert("Either there is no model for selected brand or you have not seleted any brand");
			return;
		}
		if (problemType == "2nd problem" || problemType == "0" || problemType == "") {
			$('#repair_days_b').val("0");
			$('#estimated_cost_b').val("0");
			$('#estimated_cost_b1').val("0");
			initializeSum();
			getMaxVal();
			return false;
			}
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
					if (default_cost != '') {
                        $('#estimated_cost_b1').val(default_cost);
                    }
					$('#repair_days_b').val(obj.repair_days);
					$('#estimated_cost_b').val(obj.repair_price);
				}else{
					$('#repair_days_b').val("");
					$('#estimated_cost_b').val("");
					if (default_cost != '') {
						$('#estimated_cost_b1').val("");
					}
					alert("No price for this combination");
				}
			},
			error: function(e) {
			    $.unblockUI();
			    alert("An error occurred: " + e.responseText.message);
			    console.log(e);
			}
		});
	});
	
	//---------------------------------------------
	$('#problem_type_c').change(function() {
		var default_cost = $('#default_cost').val();
		var brandID = $('#MobileRepairBrandId').val();
		var modelID = $('#MobileRepairMobileModelId').val();
		var problemType = $(this).val();		
		var targeturl = $(this).attr('rel') + '?problemType=' + problemType + "&brandID="+brandID+"&modelID="+modelID;
		if (parseInt(modelID) == 0) {
			$(this).val("");
			alert("Either there is no model for selected brand or you have not seleted any brand");
			return;
		}
		if (problemType == "3rd problem" || problemType == "0" || problemType == "") {
			$('#repair_days_c').val("0");
			$('#estimated_cost_c').val("0");
			$('#estimated_cost_c1').val("0");
			initializeSum();
			getMaxVal();
			return false;
			}
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
					if (default_cost != '') {
                        $('#estimated_cost_c1').val(default_cost);
                    }
					$('#repair_days_c').val(obj.repair_days);
					$('#estimated_cost_c').val(obj.repair_price);
				}else{
					$('#repair_days_c').val("");
					$('#estimated_cost_c').val("");
					if (default_cost != '') {
						$('#estimated_cost_c1').val("");
					}
					alert("No price for this combination");
				}
			},
			error: function(e) {
			    $.unblockUI();
			    alert("An error occurred: " + e.responseText.message);
			    console.log(e);
			}
		});
	});
	//---------------------------------------------
	$('#MobileRepairMobileModelId').change(function() {
		initialize_inputs();
	});
	
	
});
function initialize_inputs() {
	//------------------------------------
	$('#problem_type_a').val("");
	$('#problem_type_b').val("");
	$('#problem_type_c').val("");
	//------------------------------------
	$('#estimated_cost_a').val("");
	$('#estimated_cost_b').val("");
	$('#estimated_cost_c').val("");
	//------------------------------------
	$('#repair_days_a').val("");
	$('#repair_days_b').val("");
	$('#repair_days_c').val("");
}
initialize_inputs();
if( parseInt($("#MobileRepairBrandId")[0].selectedIndex) != 0){
	$( document ).ready(function() {
		//var brandId = $('#MobileRepairBrandId').val();
		$('#MobileRepairBrandId').change();
	});
}
/*
 http://malsup.com/jquery/block/
 If you want to use the default settings and have the UI blocked for all ajax requests, it's as easy as this:

$(document).ajaxStart($.blockUI).ajaxStop($.unblockUI);
$.blockUI({ css: { backgroundColor: '#f00', color: '#fff'} });
Trigger for change event
$('select#some').val(10).change(); or $('select#some').val(10).trigger('change');
 */
</script>

<script type='text/javascript'>
    var optVal = 0;
    function showhide_info(optVal){
        if (optVal == 1){
            document.getElementById('new_delivery_address').style.display = 'none';
        }else{
            document.getElementById('new_delivery_address').style.display = 'table';
        }
    }
	window.onload = function() {
	showhide_info(1);  
	};
</script>
<script>
$(function() {
	$('#address_missing').click(function(){
		$('#street_address').hide();
		$('#MobileRepairCustomerAddress1').show("");
		$('#MobileRepairCustomerAddress1').val("");
		$('#MobileRepairCustomerAddress2').val("");
		$('#MobileRepairCity').val("");
		$('#MobileRepairState').val("");		
		$(this).hide();
	});
	$( "#street_address" ).select(function() {
		alert($( "#street_address" ).val());
		$('#MobileRepairCustomerAddress1').val($( "#street_address" ).val());
		$('#MobileRepairCustomerAddress1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$( "#street_address" ).change(function() {
		$('#MobileRepairCustomerAddress1').val($( "#street_address" ).val());
		$('#MobileRepairCustomerAddress1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$( "#find_address" ).click(function() {
		var zipCode = $("#MobileRepairZip").val();
		//$.blockUI({ message: 'Just a moment...' });
		//focus++;
		//alert("focusout:"+zipCode);
		//$( "#focus-count" ).text( "focusout fired: " + focus + "x" );
		var zipCode = $("#MobileRepairZip").val();
		var targeturl = $("#MobileRepairZip").attr('rel') + '?zip=' + escape(zipCode);		
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
						$('#MobileRepairCustomerAddress1').hide("");
						$('#address_missing').show();
						var toAppend = '';
						$('#street_address').find('option').remove().end();
						$.each(obj.Street, function( index, value ) {
							//alert( index + ": " + value );
							toAppend += '<option value="'+value+'">'+value+'</option>';
						});
						$('#street_address').append(toAppend);
						$('#MobileRepairCustomerAddress2').val(obj.Address2);
						$('#MobileRepairCity').val(obj.Town);
						$('#MobileRepairState').val(obj.County);
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
	$('#MobileRepairCustomerAddress1').show();
	$('#street_address').hide();
	$('#address_missing').hide();
	
});
</script>
<script> 
	function validateForm(){
		if (document.getElementById("mobile_condition_table")) {
			var mobileCondChk = $('input[name="MobileRepair[mobile_condition][]"]:checkbox:checked');
			//alert(mobileCondChk.length);
			if (mobileCondChk.length == 0)  {
				$('#error_div').html('Please select mobile condition!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
				alert('Please select mobile condition!');
				return false;
			}
		}
		
		if ($('#other').is(":checked")) {
			if ($('#MobileRepairMobileConditionRemark').val() == '') {
				$('#error_div').html("Please input mobile condition remarks!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
				alert('Please input mobile condition remarks!');
				return false;
			}
		}
		
		//alert($('#MobileRepairCustomerEmail').val());
		//return false;
		var modelIdx = $('#MobileRepairMobileModelId').prop("selectedIndex");
		if (modelIdx == 0) {
			$('#error_div').html("Please choose mobile model").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert('Please choose mobile model');
			return false;
		}
		var problemIdx = $('#problem_type_a').prop("selectedIndex");
		if (problemIdx == 0) {
			$('#error_div').html("Please choose first Problem Type").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert('Please choose first Problem Type');
			return false;
		}
		if ($('#MobileRepairDescription').val() == "") {
		   $('#error_div').html('Please input the fault description!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		   alert("Please input the fault description!");
		   return false;
	   }
	
		if ($('#MobileRepairPhonePassword').val() == "") {
			$('#error_div').html('Please input the phone password!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert("Please input the phone password!");
			return false;
		}
		var repairImei = $('#MobileRepairImei').val();
		var newimei1 = $('#imei1').val() ;
		total = repairImei+newimei1;
		//alert(total);
		$('#imemivalue').val(total);
		validateAgree();
		
		if (parseInt($('#formValid').val()) == 1 || $('#formValid').val() == '1') {
			
			return true;
		}else{
			return false;
		}
	}
	
	function validateAgree(){
		$( "#submit-confirm" ).dialog({
			resizable: false,
			height:240,
			modal: true,
			buttons: {
			  "Agree": function() {
				 
				 $('#formValid').val('1');
				 $('#MobileRepairAddForm').submit();
				
				
			  },
			  Cancel: function() {
				$(this).dialog("close");
			  }
			}
		});
	}
	//rajju
	$("#MobileRepairImei").keydown(function (event) {		
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
	$('#MobileRepairImei').keyup(function(event){
        if ($('#MobileRepairImei').val().length == 14 && ((event.keyCode >= 48 && event.keyCode <= 57 ) ||
		( event.keyCode >= 96 && event.keyCode <= 105))) {
            var i;
            var singleNum;
            var finalStr = 0;
            var total = 0;
            var numArr = $('#MobileRepairImei').val().split('');
            
            for (i = 0; i < $('#MobileRepairImei').val().length; i++) {
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
            var newNumb = $('#MobileRepairImei').val() + Dnum;
			//alert(Dnum);
             $('#imei1').val(Dnum);
        }
    });
	
	//$( "#MobileRepairImei" ).keyup(function() {
	//	//var MobileUnlockImei = $('#MobileUnlockImei').val();
	//	if ($('#MobileRepairImei').val().length < 14) {
	//		//alert('hello');
	//		$('#imei1').val("");
	//	}
		
	//});//rajju
	$('#other').click(function(){
		if ($(this).is(":checked")) {
			$('#MobileRepairMobileConditionRemark').css("display","block");
		} else {
			$('#MobileRepairMobileConditionRemark').css("display","none");
		}
	});
	
	$( document ).ready(function() {
		if ($('#other').is(":checked")) {
			$('#MobileRepairMobileConditionRemark').css("display","block");
		} else {
			$('#MobileRepairMobileConditionRemark').css("display","none");
		}
	});
</script>