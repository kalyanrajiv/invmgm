<?php
//pr($this->request->data);
$inputData = '';
$conditionRemarks = '';
$imei = $imei1 = '';
 
if(!empty($this->request->data)){
	$inputData = $this->request->data;
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
<head>
	<?php #echo $this->Html->css('smoothness-jquery-ui.min.css');?>
</head>
<div id="dialog-confirm" title="Repair Terms" style="width: 500px !important;">
	<?php echo $terms_repair;?>
</div>
<div id="submit-confirm" title="Please Confirm!" style="background: greenyellow; display: none;">
	Please confirm that <h2>All entries are correct</h2><h2>No Changes</h2> can be made after submission of this booking.<br/>Are you sure you want to continue?
</div>
<div class="mobileRepairs form" >
<?php
	$url = $this->Url->build(array('controller' => 'customers', 'action' => 'get_address'));
	echo $this->Form->create($mobile_repair_entity, array('id' => 'MobileRepairAddForm','onSubmit' => 'return validateForm();')); ?>
	 
	
		<h2><?php echo __('Add Mobile Repair'); ?></h2>
	<?php 	$date = date('Y-m-d h:i:s A');		
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		//print_r($kiosk_id);
		echo $this->Form->input('repair_number', array('name' => 'MobileRepair[repair_number]','type' => 'hidden', 'value' => 1));
		echo $this->Form->input('kiosk_id', array('name' => 'MobileRepair[kiosk_id]','type' => 'hidden', 'value' => $kiosk_id));
		echo $this->Form->input('imei', array('name' => 'MobileRepair[imei]','type' => 'hidden'  ,'id'=>'imemivalue'));
		//customer details
		?>
		
		<div id="error_div" tabindex='1'></div>
		
		
	<?php
	$customerData = $this->Url->build(array('controller' => 'retail_customers', 'action' => 'get_customer_ajax'));
   echo "<div id='remote'>";
	echo "<input name='cust_email' class='typeahead' id='cust_email' placeholder='check existing customer email' style='width:250px;padding-right:10px;'/>";echo "&nbsp;&nbsp;<a href='#' id='check_existing' rel = '$customerData'>Check Existing</a>";
    echo "</div>";
		echo ('<h4>Customer Details</h4><hr/>');
		if(!empty($this->request->query['customerId'])){
		echo "<table>";
			echo "<tr>";
				echo "<td>".$this->Form->input('customer_fname', array('name' => 'MobileRepair[customer_fname]','id'=>'MobileRepairCustomerFname','label' => 'First Name','value' => $customerdetail['0']['fname']))."</td>";
				echo "<td>".$this->Form->input('customer_lname', array('name' => 'MobileRepair[customer_lname]','id'=>'MobileRepairCustomerLname','label' => 'Last Name','value' => $customerdetail['0']['lname']))."</td>";
				echo "<td>".$this->Form->input('customer_contact',array('name' => 'MobileRepair[customer_contact]','id'=>'MobileRepairCustomerContact','label' => 'Mobile/Phone','maxlength'=> '11','value' => $customerdetail['0']['mobile'],'autocomplete' => 'off'))."</td>";
			echo "</tr>";
			
			echo "<tr>";  
				echo "<td>".$this->Form->input('customer_email',array('name' => 'MobileRepair[customer_email]','id'=>'MobileRepairCustomerEmail','value' => $customerdetail['0']['email']))."</td>";
				echo "<td>";
					echo "<table>";
						echo "<tr>";
							echo "<td>";
								echo $this->Form->input('zip',array('name' => 'MobileRepair[zip]','id'=>'MobileRepairZip','placeholder' => 'Postcode', 'label'=>false, 'rel' => $url,'size'=>'10px','value' => $customerdetail['0']['zip'],'style'=>'width: 120px;'));
							echo "</td>";
							echo "<td>";
								echo "<button type='button' id='find_address' class='btn' style='margin-top: 6px;margin-left: -8px;width: 130px;height: 29px;'>Find my address</button>";
							echo "</td>";
						echo "</tr>";
					echo "</table>";
				echo "</td>";
				echo "<td colspan='2'>".$this->Form->input('customer_address_1', array('name' => 'MobileRepair[customer_address_1]','id' => 'MobileRepairCustomerAddress1','placeholder' => 'property name/no. and street name','value' => $customerdetail['0']['address_1']));
 ?>
					<select name='street_address' id='street_address'><option>--postcode--</option></select>
						<span id='address_missing'><br/><a href='#-1'>Address is not on the list</a></span>
				</td>
 <?php
			echo "</tr>";
			
			echo "<tr>";
				echo "<table>";
					echo "<tr>";
						echo "<td>".$this->Form->input('customer_address_2', array('name' => 'MobileRepair[customer_address_2]','id' => 'MobileRepairCustomerAddress2','placeholder' => "further address details (optional)",'value' => $customerdetail['0']['address_2']))."</td>";
						echo "<td>".$this->Form->input('city',array('name' => 'MobileRepair[city]','id' => 'MobileRepairCity','label' => 'Town/City','placeholder' => "name of town or city",'value' => $customerdetail['0']['city']))."</td>";  
						echo "<td>".$this->Form->input('state',array('name' => 'MobileRepair[state]','id' => 'MobileRepairState','label'=>'County', 'placeholder' => "name of county (optional)",'value' => $customerdetail['0']['state']))."</td>";  
						echo "<td>".$this->Form->input('country',array('name' => 'MobileRepair[country]','id' => "MobileRepairCountry",'options'=>$countryOptions))."</td>";
					echo "</tr>";
				echo "</table>";
			echo "</tr>";
		echo "</table>";
	}else{
		echo "<table>";
		echo "<tr>";
		echo "<td>".$this->Form->input('customer_fname', array('name' => 'MobileRepair[customer_fname]','id' => 'MobileRepairCustomerFname','label' => 'First Name'))."</td>";
		echo "<td>".$this->Form->input('customer_lname', array('name' => 'MobileRepair[customer_lname]','id' => 'MobileRepairCustomerLname','label' => 'Last Name'))."</td>";
		echo "<td>".$this->Form->input('customer_contact',array('name' => 'MobileRepair[customer_contact]','id' => 'MobileRepairCustomerContact','label' => 'Mobile/Phone','maxlength'=> '11','autocomplete' => 'off'))."</td>";
		echo "</tr>";
		echo "<tr>";		
		echo "<td>".$this->Form->input('customer_email',array('name' => 'MobileRepair[customer_email]','id' => 'MobileRepairCustomerEmail'))."</td>";
		echo "<td>";
			echo "<table>";
				echo "<tr>";
					echo "<td>";
					echo $this->Form->input('zip',array('name' => 'MobileRepair[zip]','id' => 'MobileRepairZip','placeholder' => 'Postcode', 'label'=>false, 'rel' => $url,'size'=>'10px','style'=>'width: 120px;'));
					echo "</td>";
					echo "<td>";
					echo "<button type='button' id='find_address' class='btn' style='margin-top: 6px;margin-left: -8px;width: 130px;height: 29px;'>Find my address</button>";
					echo "</td>";
				echo "</tr>";
			echo "</table>";
		echo "</td>";
		echo "<td colspan='2'>".$this->Form->input('customer_address_1', array('name' => 'MobileRepair[customer_address_1]','id' =>'MobileRepairCustomerAddress1','placeholder' => 'property name/no. and street name'));
	?>
		<select name='street_address' id='street_address'><option>--postcode--</option></select>
		<span id='address_missing'><br/><a href='#-1'>Address is not on the list</a></span>
		</td>
	<?php
		echo "</tr>";
		echo "<tr>";
		echo "<table>";
		echo "<tr>";
		echo "<td>".$this->Form->input('customer_address_2', array('name' => 'MobileRepair[customer_address_2]','id' =>'MobileRepairCustomerAddress2','placeholder' => "further address details (optional)"))."</td>";
		echo "<td>".$this->Form->input('city',array('name' => 'MobileRepair[city]','id' =>'MobileRepairCity','label' => 'Town/City','placeholder' => "name of town or city"))."</td>";		
		echo "<td>".$this->Form->input('state',array('name' => 'MobileRepair[state]','id' =>'MobileRepairState','label'=>'County', 'placeholder' => "name of county (optional)"))."</td>";		
		echo "<td>".$this->Form->input('country',array('name' => 'MobileRepair[country]','id' =>'MobileRepairCountry','options'=>$countryOptions,'selected'=>$countryOptions['GB']))."</td>";
		echo "</tr>";
		echo "</table>";
		echo "</tr>";
		echo "</table>";
	}
		//phone details
		echo ('<h4>Mobile Details</h4><hr/>');
		$url = $this->Url->build(array('controller' => 'mobile_repairs', 'action' => 'get_models'));
		$priceURL = $this->Url->build(array('controller' => 'mobile_repairs', 'action' => 'get_repair_price'));
		$problemTypesUrl = $this->Url->build(array('controller' => 'mobile_repairs', 'action' => 'get_repair_problems'));
		echo '<div class="input select required">';
		echo $this->Form->input('brand_id',array('name' => 'MobileRepair[brand_id]','id'=>'MobileRepairBrandId','rel' => $url, 'div' => 'false', 'required' => 'required'));
		echo '</div>';
		echo '<div class="input select required">';
		echo $this->Form->input('mobile_model_id',array('name' => 'MobileRepair[mobile_model_id]','id' => 'MobileRepairMobileModelId','options' => $mobileModels,'type' => 'select','empty' => 'choose model', 'rel' => $problemTypesUrl, 'div' => false, 'required' => 'required'));
		echo '</div>';
		echo "<table>";
			echo "<tr>";
				echo "<td>";
				echo '<div class="input select required">';
					echo $this->Form->input('problem_type', array(
										'options' => $problemArrOptns,
										'name' => 'MobileRepair[problem_type_a]',
										'id' => 'problem_type_a',
										'rel' => $priceURL,
										'empty' => '1st problem',
										'div' => false,
										'required' => 'required',
										));
					echo '</div>';
					echo '<div class="input text required">';
					echo $this->Form->input('estimated_cost', array(
										'options' => array(),
										//'type' => 'text',
										'style'=>'width: 50%',
										'name' => 'MobileRepair[estimated_cost_a]',
										'readonly' => true,
										'id' => 'estimated_cost_a',
										'div' => false,
										'required' => 'required',
										));
					echo $this->Form->input('net_cost', array(
										'type' => 'hidden',
										//'style'=>'width: 50%',
										'name' => 'MobileRepair[net_cost_a]',
										//'readonly' => true,
										'id' => 'net_cost_a',
										'div' => false,
										//'required' => 'required',
										));
					echo '</div>';
					echo '<div class="input text required">';
					echo $this->Form->input('repair_days', array(
										'type' => 'text',
										'style'=>'width: 50%',
										'name' => 'MobileRepair[repair_days_a]',
										'readonly' => true,
										'id' => 'repair_days_a',
										'div' => false,
										'required' => 'required',
										));
					echo '</div>';
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('problem_type', array(
										'options' => $problemArrOptns,
										'name' => 'MobileRepair[problem_type_b]',
										'id' => 'problem_type_b',
										'rel' => $priceURL,
										'empty' => '2nd problem'
										));
					
					echo $this->Form->input('estimated_cost', array(
										'options' => array(),
										//'type' => 'text',
										'style'=>'width: 50%',
										'name' => 'MobileRepair[estimated_cost_b]',
										'readonly' => true,
										'id' => 'estimated_cost_b'
										));
					
					echo $this->Form->input('net_cost', array(
										'type' => 'hidden',
										//'style'=>'width: 50%',
										'name' => 'MobileRepair[net_cost_b]',
										//'readonly' => true,
										'id' => 'net_cost_b',
										'div' => false,
										//'required' => 'required',
										));
					
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
										'rel' => $priceURL,
										'style'=>'width: 95%',
										'empty' => '3rd problem'
										));
					
					echo $this->Form->input('estimated_cost', array(
										//'type' => 'text',
										'options' => array(),
										'style'=>'width: 50%',
										'name' => 'MobileRepair[estimated_cost_c]',
										'readonly' => true,
										'id' => 'estimated_cost_c'
										));
					
					echo $this->Form->input('net_cost', array(
										'type' => 'hidden',
										//'style'=>'width: 50%',
										'name' => 'MobileRepair[net_cost_c]',
										//'readonly' => true,
										'id' => 'net_cost_c',
										'div' => false,
										//'required' => 'required',
										));
					
					echo $this->Form->input('repair_days', array(
										'type' => 'text',
										'style'=>'width: 50%',
										'name' => 'MobileRepair[repair_days_c]',
										'readonly' => true,
										'id' => 'repair_days_c'
										));
				echo "</td>";
			echo "</tr>";
			echo $this->Form->input('status_freezed',array('name' => 'MobileRepair[status_freezed]','type' => 'hidden',
													   'value' => 1,
													   'label' => false,
													   'div' => false,
													   ));
		echo "</table>";
		if(count($mobileConditions)){
			$mobileConditions['1000'] = 'Other';
			$chunks = array_chunk($mobileConditions,4,true);
			if(count($chunks)){
				echo "<table id = 'mobile_condition_table'>";
					echo "<tr>";
						echo "<td colspan='8'>";
							echo ('<h4>Phone"s Condition</h4><hr/>');
						echo "</td>";
					echo "</tr>";
					echo "<tr>";
						foreach($chunks as $c => $chunk){
								echo "<td>";
									foreach($chunk as $ch => $condition){
										if(!empty($inputData)){
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
						echo "<td colspan = '4'>";
							echo ('<h4>Phone"s Functions Test (For internal use**)</h4><hr/>');
						echo "</td>";
					echo "</tr>";
					echo "<tr>";
						foreach($functionChunks as $f => $Fchunk){
								echo "<td>";
									foreach($Fchunk as $fch => $functionCondition){
										if(!empty($inputData)){
											if(array_key_exists('function_condition',$inputData['MobileRepair']) && in_array($fch,$inputData['MobileRepair']['function_condition'])){
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
					echo $this->Form->input('total_price', array('label' => 'Total price',
													 'name'=>'MobileRepair[total_price]',
													 'id' =>'total_price',
													 'readonly' => true,
													 'style'=>'width: 315px;margin-left: 7px;',

													 ));
				echo "</td>";
				echo "<td>";
					echo '<div class="input input required">Imei<span style="color: red;">*</span><span id = "imei_quest" title="Please fill digit only. Incase serial number put in fault description!" style="background: steelblue;color: white;font-size: 14px;margin-left: 3px;">?</span></div>';
					echo $this->Form->input('null',array('label' => false,
														 'id'=>'MobileRepairImei',
														 'maxlength' => 14,
														 //'name' => 'MobileRepair[null]',
														 'div' => false,
														  'value' => $imei,
														'autocomplete' => 'off',
														 'style'=>'width: 149;margin-left: 7px;margin-top: -7px'
														 ));
				echo "</td>";
				echo "<td>";
				echo $this->Form->input('null',array(
													  'type' => 'text',
													  'label' => false,
													  'name' => 'MobileRepair[null]',
													  'id' =>'imei1',
													  'readonly'=>'readonly',
													  'value' => $imei1,
													  'style'=>"width: 25px; hieght: 30px; margin-right: 395px; margin-top: 17px"));
			echo "</td>";
				echo "</tr>";
				echo "</table>";
				 echo "<table>";
			echo "<tr>";
				echo "<td>";
						echo $this->Form->input('description',array('name' => 'MobileRepair[description]','label' => 'Fault Description', 'style' => 'width:322px'));
				echo "</td>";
				echo "<td>";
				   echo $this->Form->input('phone_password', array('label' => array(
																					'class' => 'Your-Class',
																					'text' => 'Phone Password :',
																					 'style' => 'color: red;',
																					),
																   'name' => 'MobileRepair[phone_password]',
																    'style'=>"width: 322px; margin-right: 395px; margin-top: 1px"
																  
																   ));
				echo "</td>";
			echo "</tr>";
		echo "</table>";	
		echo $this->Form->input('brief_history', array('type' => 'hidden','name' => 'MobileRepair[brief_history]','label' => 'Repair History</br/>(For Internal Use'));		
		echo $this->Form->input('actual_cost', array('name' => 'MobileRepair[actual_cost]','type' => 'hidden', 'value' => 0));
		echo $this->Form->input('received_at',array('name' => 'MobileRepair[received_at]','type'=>'hidden','value'=>$date));
		echo $this->Form->input('status',array('name' => 'MobileRepair[status]','type' => 'hidden', 'value' => '1'));	
		echo $this->Form->input('internal_repair',array('name' => 'MobileRepair[internal_repair]','type' => 'hidden', 'value' => NULL));
			
		#echo $this->Form->input('status');
		//, 'ext' => 'json'
		// /InventoryManagement/mobile_repairs/get_models.json
		// /InventoryManagement/sandbox/mobile_repairs/get_models.json [if plugin=>sandbox]
	?>
	
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

		
		<li><?php #echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php echo $this->element('repair_navigation'); ?></li>
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
function initializeSum(){
	var val1 = 0;
	if ($('#estimated_cost_a').val().length != 0) {
		val1 = parseInt($('#estimated_cost_a').val());
		//Rasu: This needs to be changed w.r.t to selected Index as we are chaning it to dropdown from inputbox
	}
	var val2 = 0;
	if ($('#estimated_cost_b').val().length != 0) {
		val2 = parseInt($('#estimated_cost_b').val());
		//Rasu: This needs to be changed w.r.t to selected Index
	}
	var val3 = 0;
	if ($('#estimated_cost_c').val().length != 0) {
		val3 = parseInt($('#estimated_cost_c').val());
		//Rasu: This needs to be changed w.r.t to selected Index
	}
	total = val1+val2+val3;
	$('#total_price').val(total);
}

function initialize_sum() {
	//-----------------
	$('#estimated_cost_a').find('option').remove().end();
	$('#estimated_cost_a').append("<option value='0'></option>");
	
	//-----------------
	$('#estimated_cost_b').find('option').remove().end();
	$('#estimated_cost_b').append("<option value='0'></option>");
	
	//-----------------
	$('#estimated_cost_c').find('option').remove().end();
	$('#estimated_cost_c').append("<option value='0'></option>");
	
    initializeSum();
	$('#net_cost_a').val($('#estimated_cost_a').val());
	$('#net_cost_b').val($('#estimated_cost_b').val());
	$('#net_cost_c').val($('#estimated_cost_c').val());
}
$(function() {
	$('#problem_type_a, #problem_type_b, #problem_type_c').change(function() {
		//need to update net_price as well.
		if ($('#problem_type_a').val() == '1st problem') {
			val1 = 0;
			$('#estimated_cost_a').find('option').remove().end();
			$('#estimated_cost_a').append("<option value='0'></option>");
			$('#net_cost_a').val(0);
		}else{
			val1 = parseInt($('#estimated_cost_a').val());
		}
		
		if ($('#problem_type_b').val() == '2nd problem') {
			val2 = 0;
			$('#estimated_cost_b').find('option').remove().end();
			$('#estimated_cost_b').append("<option value='0'></option>");
			$('#net_cost_b').val(0);
		}else{
			val2 = parseInt($('#estimated_cost_b').val());
		}
		
		if ($('#problem_type_c').val() == '3rd problem') {
			val3 = 0;
			$('#estimated_cost_c').find('option').remove().end();
			$('#estimated_cost_c').append("<option value='0'></option>");
			$('#net_cost_c').val(0);
		}else{
			val3 = parseInt($('#estimated_cost_c').val());
		}
		total = val1+val2+val3;
		$('#total_price').val(total);
	});
	
	$('#estimated_cost_a, #estimated_cost_b, #estimated_cost_c').change(function() {
		//rasu:newly added function
		var val1 = 0;
		if ($('#estimated_cost_a').val().length != 0) {
			val1 = parseInt($('#estimated_cost_a').val());
		}
		$('#net_cost_a').val($('#estimated_cost_a').val());
		
		//---------------------------
		var val2 = 0;
		if ($('#estimated_cost_b').val().length != 0) {
			val2 = parseInt($('#estimated_cost_b').val());
		}
		$('#net_cost_b').val($('#estimated_cost_b').val());
		
		//---------------------------
		var val3 = 0;
		if ($('#estimated_cost_c').val().length != 0) {
			val3 = parseInt($('#estimated_cost_c').val());
		}
		$('#net_cost_c').val($('#estimated_cost_c').val());
		
		//---------------------------
		total = val1+val2+val3;
		$('#total_price').val(total);
	});
	
	//On change of mobile price
	$('#MobileRepairBrandId').change(function() {
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
					//$('#MobileRepairMobileModelId').children().remove();
					$('#MobileRepairMobileModelId').find('option').remove().end();
					$('#MobileRepairMobileModelId').append(response);//html(response.content);
				}
				//Rasu: newly added
				initialize_sum();
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
	$('#MobileRepairMobileModelId').change(function() {
		var selectedValue = $(this).val();
		var brandId = $('#MobileRepairBrandId').val();
		var targeturl = $(this).attr('rel') + '?brandID=' + brandId + '&modelID=' + selectedValue;
		var valu;
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
					var problemTypeA = "<option>1st problem</option>";
					var obj = jQuery.parseJSON( response);
					//$('#MobileRepairMobileModelId').children().remove();
					$('#problem_type_a').find('option').remove().end();
					$.each(obj, function(i, elem){
						problemTypeA+= "<option value=" + i + ">" + elem + "</option>";
					});
					$('#problem_type_a').append(problemTypeA);//html(response.content);
					//------------------------------------
					var problemTypeB = "<option>2nd problem</option>";
					var obj = jQuery.parseJSON( response);
					
					//$('#MobileRepairMobileModelId').children().remove();
					$('#problem_type_b').find('option').remove().end();
					$.each(obj, function(i, elem){
						problemTypeB+= "<option value=" + i + ">" + elem + "</option>";
					});
					$('#problem_type_b').append(problemTypeB);//html(response.content);
					//------------------------------------
					var problemTypeC = "<option>3rd problem</option>";
					var obj = jQuery.parseJSON( response);
					
					//$('#MobileRepairMobileModelId').children().remove();
					$('#problem_type_c').find('option').remove().end();
					$.each(obj, function(i, elem){
						problemTypeC+= "<option value=" + i + ">" + elem + "</option>";
					});
					$('#problem_type_c').append(problemTypeC);//html(response.content);
					
					//Rasu:newly added
					initialize_sum();
				}
			},
			error: function(e) {
			    $.unblockUI();
			    alert("An error occurred: " + e.responseText.message);
			    console.log(e);
			}
		});
	});
	
	$('#problem_type_a').change(function() {
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
			//when choosing first option, we are initializing variables
			$('#repair_days_a').val("0");
			$('#estimated_cost_a').val("0");
			//Rasu: This needs to be changed and first option would be set to 0
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
					$('#repair_days_a').val(obj.repair_days);
					$('#estimated_cost_a').val(obj.repair_price);
					var startCost = parseInt(obj.repair_price);
					var endCost = startCost + 200;
					var optionStr = "";
					for(i = startCost; i <= endCost; i++){
						optionStr += "<option value='" + i + "' >" + i + "</option>";
					}
					$('#estimated_cost_a').find('option').remove().end();
					$('#estimated_cost_a').append(optionStr);//html(response.content);
					//Rasu: This needs to be changed and first option would be set to cost received upto + 30
					$('#net_cost_a').val(obj.repair_cost);
					//Rasu: This needs to be dynamic now as options will be changed in dropdown, than we need to create function for onChange for estimated_cost_a to reset value of net_cost_a
					initializeSum();
				}else{
					$('#repair_days_a').val("");
					//$('#estimated_cost_a').val(""); //Rasu:needs to be updated
					$('#estimated_cost_a').find('option').remove().end();
					$('#estimated_cost_a').append("<option value='0'></option>");
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
			//Rasu: This needs to be changed and first option would be set to 0
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
					$('#repair_days_b').val(obj.repair_days);
					//$('#estimated_cost_b').val(obj.repair_price);
					var startCost = parseInt(obj.repair_price);
					var endCost = startCost + 200;
					var optionStr = "";
					for(i = startCost; i <= endCost; i++){
						optionStr += "<option value='" + i + "' >" + i + "</option>";
					}
					$('#estimated_cost_b').find('option').remove().end();
					$('#estimated_cost_b').append(optionStr);//html(response.content);
					$('#net_cost_b').val(obj.repair_cost);
					initializeSum();
				}else{
					$('#repair_days_b').val("");
					//$('#estimated_cost_b').val("");
					$('#estimated_cost_b').find('option').remove().end();
					$('#estimated_cost_b').append("<option value='0'></option>");
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
			//Rasu: This needs to be changed and first option would be set to 0
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
					$('#repair_days_c').val(obj.repair_days);
					//$('#estimated_cost_c').val(obj.repair_price);
					var startCost = parseInt(obj.repair_price);
					var endCost = startCost + 200;
					var optionStr = "";
					for(i = startCost; i <= endCost; i++){
						optionStr += "<option value='" + i + "' >" + i + "</option>";
					}
					$('#estimated_cost_c').find('option').remove().end();
					$('#estimated_cost_c').append(optionStr);//html(response.content);
					$('#net_cost_c').val(obj.repair_cost);
					initializeSum();
				}else{
					$('#repair_days_c').val("");
					//$('#estimated_cost_c').val("");
					$('#estimated_cost_c').find('option').remove().end();
					$('#estimated_cost_c').append("<option value='0'></option>");
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
	      document.location.href = "<?php echo $this->Url->build(array('controller'=>'mobile_repairs','action'=>'index'));?>";
	    }
	  }
	});
	<?php }else{ ?>
		//for hiding the dialog-confirm which is not required on the page in this case
		$('#dialog-confirm').hide();
	<?php } ?>
  });
  </script>
<script type='text/javascript'>
    var optVal = 0;
    function showhide_info(optVal){
		if (document.getElementById('new_delivery_address')) {
			//code
		
			if (optVal == 1){
				document.getElementById('new_delivery_address').style.display = 'none';
			}else{
				document.getElementById('new_delivery_address').style.display = 'table';
			}
		}
    }
	window.onload = function() {
	showhide_info(1);  
	};
</script>
<script>
	
	$(function() {
	
	//-------------------------
	
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
				$("#MobileRepairCustomerFname").val(obj.fname);
				$("#MobileRepairCustomerLname").val(obj.lname);
				$("#MobileRepairCustomerContact").val(obj.mobile);
				$("#MobileRepairCustomerEmail").val(obj.email);
				$("#MobileRepairZip").val(obj.zip);
				$("#MobileRepairCustomerAddress1").val(obj.address_1);
				$("#MobileRepairCustomerAddress2").val(obj.address_2);
				$("#MobileRepairCity").val(obj.city);
				$("#MobileRepairState").val(obj.state);
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
	
	//-------------------------
	
	
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
		if (zipCode == "") {
            alert("Please Input Postcode!");
			return false;
        }
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
	
function validateForm(){
	if (document.getElementById("mobile_condition_table")) {
		var mobileCondChk = $('input[name="MobileRepair[mobile_condition][]"]:checkbox:checked');
		if (mobileCondChk.length == 0)  {
			$('#error_div').html('Please select phone"s condition!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert('Please select phone"s condition!');
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
	
	if ($('#MobileRepairCustomerEmail').val() == '') {
		//$('#error_div').html("Please input the customer's email").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		//alert("Please input the customer's email");
		//return false;
	}else if (!isValidEmailAddress($('#MobileRepairCustomerEmail').val())) {
		$('#error_div').html("Please input valid email address!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert('Please input valid email address!');
		return false;
	}
	
	if ($('#MobileRepairCustomerFname').val() == '') {
		$('#error_div').html("Please input the first name").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert("Please input the first name");
		return false;
	}
	
	if ($('#MobileRepairCustomerLname').val() == '') {
		$('#error_div').html("Please input the last name").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert("Please input the last name");
		return false;
	}
	
	if ($('#MobileRepairCustomerContact').val() == '') {
		$('#error_div').html("Please input the phone number").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert("Please input the phone number");
		return false;
	}else if ($('#MobileRepairCustomerContact').val().length < 11) {
		$('#error_div').html("Phone number should be minimum 11 characters long!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert('Phone number should be minimum 11 characters long!');
		return false;
	}
	
	if ($('#MobileRepairImei').val() == '') {
		$('#error_div').html("Please input the imei number!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert("Please input the imei number!");
		return false;
	}else if ($('#MobileRepairImei').val().length < 14) {
		$('#error_div').html('IMEI"s Minimum length should be 14 characters!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert('IMEI"s Minimum length should be 14 characters!');
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
			height:140,
			modal: true,
			buttons: {
			  "Agree": function() {
				$('#formValid').val('1');
				$('#MobileRepairAddForm').submit();
			  },
			  Cancel: function() {
				//alert('Cancel');
				$(this).dialog("close");
			  }
			}
		});
	}
	
	
/*$("#MobileRepairAddForm").submit(function(){
	
	var repairDescription = $('#MobileRepairDescription').val();
	if (repairDescription == "") {
		alert("Please input the fault description!");
		return false;
	}
});*/

	$("#MobileRepairCustomerContact").keydown(function (event) {		
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
	
	$( "#MobileRepairImei" ).keyup(function() {
		//var MobileUnlockImei = $('#MobileUnlockImei').val();
		if ($('#MobileRepairImei').val().length < 14) {
			//alert('hello');
			$('#imei1').val("");
		}
		
	});
	function isValidEmailAddress(emailAddress) {
		var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
		return pattern.test(emailAddress);
	}
	$(function() {
	  $( document ).tooltip();
	});
	
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
