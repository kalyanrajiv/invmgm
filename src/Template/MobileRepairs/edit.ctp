<?php
use Cake\I18n\Time;
use Cake\Utility\Text;
?>
<?php
$inputData = '';
    //pr($this->request['data']);die;
	 $mobileStr = $this->request['data']['mobile_condition'];
	 if(!is_array($mobileStr)){
	   $moblileArr = explode('|',$mobileStr);
	 }else{
	   $moblileArr = $mobileStr;
	 }
	 if(in_array('1000',$moblileArr)){
	  $conditionRemarks = $this->request['data']['mobile_condition_remark'];;
	 }else{
	  $conditionRemarks = "";
	 }
	 //function condition
	 //pr($this->request['data']);
	 if(array_key_exists('function_condition',$this->request['data'])){
	   $functionStr = $this->request['data']['function_condition'];
	   if(!is_array($functionStr)){
		 $functionArr = explode('|',$functionStr);
	   }else{
		 $functionArr = $functionStr;
	   }
	 }else{
	   $functionArr = array();
	 }
	 
	 
?>

<style>
	.ui-draggable {
		width: 400px !important;
	}
	.ui-dialog .ui-dialog-content {
		height: auto !important;
	}
	.ui-dialog-titlebar-close {
		visibility: hidden;
	      }
</style>
<div id="submit-confirm" title="Please Confirm!" style="background: greenyellow; display: none; width: 500px !important;">
	Please confirm that <h2>All entries are correct</h2><h2>No Changes</h2> can be made after submission of this booking.<br/>Are you sure you want to continue?
</div>
<div class="mobileRepairs form" id='repair_form'> 
<?php //pr($this->request['data']['MobileRepair']);
 $repair_id = $this->request['data']['id'];
	echo $this->Form->create('MobileRepair',array('id' => 'MobileRepairEditForm','onSubmit' => 'return validateForm();'));
	$priceURL = $this->Url->build(array('controller' => 'mobile_repairs', 'action' => 'get_repair_price'));
	$url = $this->Url->build(array('controller' => 'mobile_repairs', 'action' => 'get_models'));
	$problemTypesUrl = $this->Url->build(array('controller' => 'mobile_repairs', 'action' => 'get_repair_problems'));
?>
		<h2><?php echo __('Edit Mobile Repair('.$repair_id.')'); ?></h2> 
		<div id="error_div" tabindex='1'></div>
		<input type='hidden' id='repair_id' name = "MobileRepair[id]"value='<?php echo $repair_id;?>'>
	<?php
		if($this->request['data']['status_rebooked'] == 1){
			echo "<h3>Rebooked Mobile Repair</h3>";
		}
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		//echo $this->Form->input('id');?>
		<?php
		echo $this->Form->input('repair_number', array('id' => 'MobileRepairRepairNumber','name' => 'MobileRepair[repair_number]','type' => 'hidden', 'value' => 1)); 
		echo $this->Form->input('kiosk_id', array('id' => 'MobileRepairKioskId','name' => 'MobileRepair[kiosk_id]','type' => 'hidden', 'value' => $kiosk_id));
		echo $this->Form->input('status_rebooked', array('id' => 'MobileRepairStatusRebooked','name' => 'MobileRepair[status_rebooked]','type' => 'hidden'));
		echo $this->Form->input('status_refund', array('id' => 'MobileRepairStatusRefund','name' => 'MobileRepair[status_refund]','type' => 'hidden'));
		echo $this->Form->input('brief_history', array('id' => 'MobileRepairBriefHistory','name' => 'MobileRepair[brief_history]','type' => 'hidden'));
		echo $this->Form->input('actual_cost', array('id' => 'MobileRepairActualCost','name' => 'MobileRepair[actual_cost]','type' => 'hidden'));
		echo $this->Form->input('received_at', array('id' => 'MobileRepairReceivedAt','name' => 'MobileRepair[received_at]','type' => 'hidden'));
		echo $this->Form->input('imei', array('type' => 'hidden'  ,'id'=>'imemivalue','name' => 'MobileRepair[imei]'));
		echo $this->Form->input('problem_type', array('id' => 'MobileRepairProblemType','name' => 'MobileRepair[problem_type]','type' => 'hidden', 'value' => $this->request['data']['problem_type']));
		echo $this->Form->input('estimated_cost', array('id' => 'MobileRepairEstimatedCost','name' => 'MobileRepair[estimated_cost]','type' => 'hidden', 'value' => $this->request['data']['estimated_cost']));
		echo $this->Form->input('mobile_condition', array('id' => 'MobileRepairMobileCondition','name' => 'MobileRepair[mobile_condition]','type' => 'hidden', 'value' => $this->request['data']['mobile_condition']));
		
		//customer details
		
		echo ('<h4>Customer Details</h4><hr/>');
		
		//phone details
		$problemStr = $this->request['data']['problem_type'];
		$problemArr = explode('|',$problemStr);
		
		$estimatedCostStr = $this->request['data']['estimated_cost'];
		$estimatedCostArr = explode('|',$estimatedCostStr);
		
		$sum = 0;
		foreach($estimatedCostArr as $key => $estimatedCost){
			$sum+=$estimatedCost;
		}
				
		if(($this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER)

		   ){ 
			echo "<table>";
				echo "<tr>";
					echo "<td>".$this->Form->input('customer_fname', array('label' => 'First Name','id'=>'MobileRepairCustomerFname','name'=>'MobileRepair[customer_fname]'))."</td>";
					echo "<td>".$this->Form->input('customer_lname', array('label' => 'Last Name','id'=>'MobileRepairCustomerLname','name'=>'MobileRepair[customer_lname]'))."</td>";
					echo "<td>".$this->Form->input('customer_contact',array('label' => 'Mobile/Phone', 'maxlength'=> '11','id'=>'MobileRepairCustomerContact','name'=>'MobileRepair[customer_contact]'))."</td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td>".$this->Form->input('customer_email',array('id'=>'MobileRepairCustomerEmail','name'=>'MobileRepair[customer_email]'))."</td>";
					echo "<td>".$this->Form->input('zip',array('label' => 'Postal Code','id'=>'MobileRepairZip','name'=>'MobileRepair[zip]'))."</td>";
					echo "<td>".$this->Form->input('customer_address_1',array('id'=>'MobileRepairCustomerAddress1','name'=>'MobileRepair[customer_address_1]'))."</td>";		
				echo "</tr>";
			echo "</table>";
			echo "<table>";
				echo "<tr>";
					echo "<td>".$this->Form->input('customer_address_2',array('id'=>'MobileRepairCustomerAddress2','name'=>'MobileRepair[customer_address_2]'))."</td>";
					echo "<td>".$this->Form->input('city',array('id'=>'MobileRepairCity','name'=>'MobileRepair[city]'))."</td>";		
					echo "<td>".$this->Form->input('state',array('id'=>'MobileRepairState','name'=>'MobileRepair[state]'))."</td>";		
					echo "<td>".$this->Form->input('country',array('options'=>$countryOptions,'id'=>'MobileRepairCountry','name'=>'MobileRepair[country]'))."</td>";
				echo "</tr>";
			echo "</table>";
			
			echo ('<h4>Mobile Details</h4><hr/>');
			echo "<table>";
			echo "<tr>";
				echo "<td>";
				echo $this->Form->input('brand_id',array('options'=>$brands, 'rel' =>$url,'id'=>'MobileRepairBrandId','name'=>'MobileRepair[brand_id]'));
				echo "<td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>";
				echo $this->Form->input('mobile_model_id',array('options'=>$mobileModels, 'rel' =>$problemTypesUrl,'id'=>'MobileRepairMobileModelId','name'=>'MobileRepair[mobile_model_id]'));
				echo "<td>";
			echo "</tr>";
			
			echo "<tr>";
			$option = array();
			//pr($problemArr);
			foreach($problemArr as $k => $value){
			  if(array_key_exists($value,$costArr)){
				$cost = (int)$costArr[$value];
				if($estimatedCostArr[$k] > $cost+200){
				  $final = $estimatedCostArr[$k];
				}else{
				  $final = $cost+200;
				}
				  for($i = 1;$i<=$final;$i++){
					$option[$k][$i] =  $i;
				  }
			  }else{
				$cost = "";
				$option[$k] = array();
			  }
			 
			}
			if(array_key_exists('MobileRepair',$this->request->data)){
			  if(array_key_exists('internal_repair',$this->request->data)){
				if(!empty($this->request->data['internal_repair'])){
					$internal_repair = $this->request->data['internal_repair'];
				}else{
				  $internal_repair = 0;
				}
			  }else{
				$internal_repair = 0;
			  }
			}else{
			  $internal_repair = 0;
			}
			  //pr($option);
			if($internal_repair == 1){
				if(!empty($option)){
				  foreach($option as $op_key => $op_val){
					$option[$op_key]['0.0001'] = .0001;
					asort($option[$op_key]);
				  }
				}
			}
			//pr($option);
			echo $this->Form->input('admin',array('name' => "MobileRepair[admin]",'type' => 'hidden','value' => 1,'id' => 'admin'));
			echo $this->Form->input('internal',array('type' => 'hidden','value' => $internal_repair,'id' => 'internal_repair','name' => "MobileRepair[internal]"));
			$estimatedCostArr[0] = (int)$estimatedCostArr[0];
			if(is_numeric($estimatedCostArr[0])){
					echo "<td>";
					echo $this->Form->input('problem_type', array('options' => $problemArrOptns,
																  'value' => $problemArr[0],
																  'name' => 'MobileRepair[problem_type_a]',
																  'id'=>'problem_type_a',
																  'rel' => $priceURL,
																  'empty' => '1st problem'
																  )
											);
					echo $this->Form->input('estimated_cost', array(//type' => 'text',
																	'options' => array($option[0]),
																	'style'=>'width: 50%',
																	'value' => $estimatedCostArr[0],
																	'name' => 'MobileRepair[estimated_cost_a]',
																	'id'=>'estimated_cost_a'
																	)
											);
					echo $this->Form->input('estimated_cost_hidden', array('type' => 'hidden',
																		   'value' => $estimatedCostArr[0],
																		   'name' => 'MobileRepair[estimated_cost_a_hidden]'
																		   )
											);
					echo $this->Form->input('net_cost_a', array('type' => 'hidden', 'name' => 'MobileRepair[net_cost_a]','id'=>'net_cost_a'));
					echo $this->Form->input('repair_days', array('type' => 'hidden','name' => 'MobileRepair[repair_days_a]','id' => 'repair_days_a'));
				echo "</td>";
			}
			if(array_key_exists(1,$estimatedCostArr) && is_numeric($estimatedCostArr[1])){
				echo "<td>";
					echo $this->Form->input('problem_type', array('options' => $problemArrOptns,
																  'value' => $problemArr[1],
																  'name' => 'MobileRepair[problem_type_b]',
																  'id'=>'problem_type_b',
																  'rel' => $priceURL,
																  'empty' => '2nd problem'
																  )
											);
					echo $this->Form->input('estimated_cost', array(//'type' => 'text',
																	'options' => array($option[1]),
																	'style'=>'width: 50%',
																	'value' => $estimatedCostArr[1],
																	'name' => 'MobileRepair[estimated_cost_b]',
																	'id'=>'estimated_cost_b'));
					echo $this->Form->input('estimated_cost_hidden', array('type' => 'hidden', 'value' => $estimatedCostArr[1], 'name' => 'MobileRepair[estimated_cost_b_hidden]'));
					echo $this->Form->input('net_cost_b', array('type' => 'hidden', 'name' => 'MobileRepair[net_cost_b]','id'=>'net_cost_b'));
					echo $this->Form->input('repair_days', array('type' => 'hidden','name' => 'MobileRepair[repair_days_b]','id' => 'repair_days_b'));
				echo "</td>";
			}else{
			  echo "<td>";
			   echo $this->Form->input('problem_type', array(
										'options' => $problemArrOptns,
										'value' => '',
										'name' => 'MobileRepair[problem_type_b]',
										'id' => 'problem_type_b',
										'rel' => $priceURL,
										'empty' => '2nd problem'
										));
			   echo $this->Form->input('estimated_cost', array(//'type' => 'text',
																	'options' => array(),
																	'style'=>'width: 50%',
																	//'value' => $estimatedCostArr[1],
																	'name' => 'MobileRepair[estimated_cost_b]',
																	'id'=>'estimated_cost_b'));
			  //echo $this->Form->input('estimated_cost_hidden', array('type' => 'hidden', 'value' => $estimatedCostArr[1], 'name' => 'data[MobileRepair][estimated_cost_b_hidden]'));
			  echo $this->Form->input('net_cost_b', array('type' => 'hidden', 'name' => 'MobileRepair[net_cost_b]','id'=>'net_cost_b'));
			  echo $this->Form->input('repair_days', array('type' => 'hidden','name' => 'MobileRepair[repair_days_b]','id' => 'repair_days_b'));
				echo "</td>";
			}
			
			
			
			
			if(array_key_exists(2,$estimatedCostArr) && is_numeric($estimatedCostArr[2])){				
				echo "<td>";
					echo $this->Form->input('problem_type', array('options' => $problemArrOptns,
																  'value' => $problemArr[2],
																  'name' => 'MobileRepair[problem_type_c]',
																  'id'=>'problem_type_c',
																  'rel' => $priceURL,
																  'empty' => '3rd problem'));
					echo $this->Form->input('estimated_cost', array(//'type' => 'text',
																	'options' => array($option[2]),
																	'style'=>'width: 50%',
																	'value' => $estimatedCostArr[2],
																	'name' => 'MobileRepair[estimated_cost_c]',
																	'id'=>'estimated_cost_c'));
					echo $this->Form->input('estimated_cost_hidden', array('type' => 'hidden', 'value' => $estimatedCostArr[2], 'name' => 'MobileRepair[estimated_cost_c_hidden]'));
					echo $this->Form->input('net_cost_c', array('type' => 'hidden', 'name' => 'MobileRepair[net_cost_c]','id'=>'net_cost_c'));
					echo $this->Form->input('repair_days', array('type' => 'hidden','name' => 'MobileRepair[repair_days_c]','id' => 'repair_days_c'));
				echo "</td>";
			}else{
			  echo "<td>";
			  echo $this->Form->input('problem_type', array(
										'options' => $problemArrOptns,
										'value' => '',
										'name' => 'MobileRepair[problem_type_c]',
										'id' => 'problem_type_c',
										'rel' => $priceURL,
										'empty' => '3rd problem'
										));
			  
			  echo $this->Form->input('estimated_cost', array(//'type' => 'text',
																	'options' => array(),
																	'style'=>'width: 50%',
																	//'value' => $estimatedCostArr[2],
																	'name' => 'MobileRepair[estimated_cost_c]',
																	'id'=>'estimated_cost_c'));
			  //echo $this->Form->input('estimated_cost_hidden', array('type' => 'hidden', 'value' => $estimatedCostArr[2], 'name' => 'data[MobileRepair][estimated_cost_c_hidden]'));
			  echo $this->Form->input('net_cost_c', array('type' => 'hidden', 'name' => 'MobileRepair[net_cost_c]','id'=>'net_cost_c'));
			  echo $this->Form->input('repair_days', array('type' => 'hidden','name' => 'MobileRepair[repair_days_c]','id' => 'repair_days_c'));
			  echo "</td>";
			}
			echo "<tr>";
			  echo "<td>";
						echo $this->Form->input('status_freezed', array('type'=>'checkbox','id'=>'MobileRepairStatusFreezed','name'=>'MobileRepair[status_freezed]'));
			  echo "</td>";
			echo "</tr>";
			
			echo "</tr>";
			echo "</table>";
			echo "<table>";
				echo "<tr>";
					echo "<td>";
						echo $this->Form->input('null',array('label' => 'Estimated Days',
															 'value' => $maxRepairDays,
															 'id'=> 'max_repair_days',
															 'name'=> 'MobileRepair[null]',
															 'style' => 'width:100px;'));
					echo "</td>";
					echo"<td>";
					  echo $this->Form->input('null',array('label' => 'Total Cost', 'name'=>'MobileRepair[total_cost]', 'value' => $sum,'id'=>'total_price', 'style'=> 'width:100px;','readonly' => 'readonly'));//made readonly on 15.01.2016
					  echo $this->Form->input('null', array('type' => 'hidden', 'value' => $sum, 'name' => 'MobileRepair[total_cost_hidden]'));
					echo "</td>";
				echo "</tr>";
				echo "<tr>";
				 
					if(count($mobileConditions)){
						$mobileConditions['1000'] = 'Other';
						   $chunks = array_chunk($mobileConditions,5,true);
						   if(count($chunks)){
							   echo "<table id = 'mobile_condition_table'>";
							   	echo "<tr>";
									   echo "<td colspan='5'>";
										   echo ('<h4>Mobile Condition</h4><hr/>');
									   echo "</td>";
								   echo "</tr>";
								   echo "<tr>";
									 foreach($chunks as $c => $chunk){
										echo "<td>";//pr($inputData);
											foreach($chunk as $ch => $condition){
											 if(in_array($ch,$moblileArr)){
													 $checked = "checked";	   
											}else{
												$checked = '';
											}
											echo $this->Form->input($condition, array('type' => 'checkbox',
												   'name'=>'MobileRepair[mobile_condition][]',
												   //'name'=>'MobileRepair[mobile_condition][]',
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
						echo $this->Form->input('mobile_condition_remark',array('name' => 'MobileRepair[mobile_condition_remark]','label' => false, 'type' => 'text','placeholder' => 'Mobile Condition Remarks(Fill in case of other)','style' => 'display: none;', 'value' => $conditionRemarks));
		}
		if(count($functionConditions)){
			$functionChunks = array_chunk($functionConditions,5,true);
			if(count($functionChunks)){
				echo "<table>";
					echo "<tr>";
						echo "<td colspan='5'>";
							echo ('<h4>Mobile Functions Condition</h4><hr/>');
						echo "</td>";
					echo "</tr>";
					echo "<tr>";
						foreach($functionChunks as $f => $Fchunk){
								echo "<td>";
									foreach($Fchunk as $fch => $functionCondition){
										  if(in_array($fch,$functionArr)){
												$checked = "checked";	
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
				echo "</tr>";
				echo "<tr>";
					echo"<td>";
						echo '<div class="input input required">Imei<span style="color: red;">*</span><span id = "imei_quest" title="Please fill digit only. Incase serial number put in fault description!" style="background: steelblue;color: white;font-size: 14px;margin-left: 3px;">?</span></div>';
						//echo $this->Form->input('imei',array('label' => false, 'maxlength' => 16, 'id' => 'imei_id', 'div' => false,'style'=>"width: 449px;margin-left: 7px;"));
					  $imei = $this->request->data['imei'];
					 $imei1 = substr($imei, -1);
					 $imei2 = substr_replace($imei,'',-1) ;
					 
					  echo $this->Form->input('null',	array(
															'label' => false,
															'maxlength'=>14,
															'div' => false,
															'id' => 'imei_id',
															'name'=>'MobileRepair[imei2]',
															'style'=>'width: 116px;height:25px;  ',
															'value' => $imei2,
														   
													  ));
					echo "</td>";
					echo "<td>";
					echo $this->Form->input('null',array(
														  'type' => 'text',
														  'label' => false,
														  'id' =>'imei1',
														  'readonly'=>'readonly',
														  'value' => $imei1,
														  'name'=>'MobileRepair[imei1]',
														  'style'=>"width: 20px; height:10px; margin-right: 825px; margin-top: 21px"));
		  
				echo "</td>";
				echo "</tr>";
				echo "</table>";
				echo "<table>";
				echo "<tr>";
					echo "<td>";
							echo $this->Form->input('description',array('label' => 'Fault Description','name'=>'MobileRepair[description]','id'=>'MobileRepairDescription'));
					echo "</td>";
					echo "<td>";
						echo $this->Form->input('phone_password', array('label' => array(
                                                                                         'class' => 'Your-Class',
                                                                                         'text' => 'Phone Password :'),
                                                                                            'style' => 'width:322px',
                                                                                            'name'=>'MobileRepair[phone_password]',
                                                                                            'id'=>'MobileRepairPhonePassword'
                                                                        )
                                                );
					echo "</td>";
				echo "<tr>";	
			echo "</table>";
		}else{
			echo $this->Form->input('brand_id', array('type' => 'hidden','id'=>'MobileRepairBrandId','name'=>'MobileRepair[brand_id]'));
			echo $this->Form->input('mobile_model_id', array('type' => 'hidden','id'=>'MobileRepairMobileModelId','name'=>'MobileRepair[mobile_model_id]'));
			echo $this->Form->input('country', array('type' => 'hidden','name' => 'MobileRepair[country]'));
		  if($this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
			echo "<table>";
			echo "<tr>";
			echo "<td>".$this->Form->input('customer_fname', array('label' => 'First Name', 'readonly' => 'readonly','id'=>'MobileRepairCustomerFname','name'=>'MobileRepair[customer_fname]'))."</td>"; 
			echo "<td>".$this->Form->input('customer_lname', array('label' => 'Last Name', 'readonly' => 'readonly','id'=>'MobileRepairCustomerLname','name'=>'MobileRepair[customer_lname]'))."</td>";
			echo "<td>".$this->Form->input('customer_contact',array('label' => 'Mobile/Phone', 'maxlength'=> '11', 'readonly' => 'readonly','id'=>'MobileRepairCustomerContact','name'=>'MobileRepair[customer_contact]'))."</td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td>".$this->Form->input('customer_email',array('id' => 'MobileRepairCustomerEmail','name' => 'MobileRepair[customer_email]','label' => 'customer email', 'readonly' => 'readonly'))."</td>";
			echo "<td>".$this->Form->input('zip',array('id' => 'MobileRepairZip','name' => 'MobileRepair[zip]','label' => 'Postal Code', 'readonly' => 'readonly'))."</td>";
			echo "<td>".$this->Form->input('customer_address_1',array('id' => 'MobileRepairCustomerAddress1','name' => 'MobileRepair[customer_address_1]','label' => 'customer address1', 'readonly' => 'readonly'))."</td>";	
			echo "</tr>";
			echo "<table>";
			echo "<tr>";
			echo "<td>".$this->Form->input('customer_address_2',array('id' => 'MobileRepairCustomerAddress2','name' => 'MobileRepair[customer_address_2]','label' => 'customer address2 Code', 'readonly' => 'readonly'))."</td>";	
			echo "<td>".$this->Form->input('city',array('id' => 'MobileRepairCity','name' => 'MobileRepair[city]','label' => 'city', 'readonly' => 'readonly'))."</td>";			
			echo "<td>".$this->Form->input('state',array('id' => 'MobileRepairState','name' => 'MobileRepair[state]','label' => 'city', 'readonly' => 'readonly'))."</td>";	
			echo "<td>".$this->Form->input('country',array('id' => 'MobileRepairCountry','name' => 'MobileRepair[country]','options'=>$countryOptions,'disabled'=>'disabled'))."</td>";//,'disabled'=>'disabled'
			echo "</tr>";
			echo "</table>";
			echo "</tr>";
			echo "</table>";
		  }else{
			echo "<table>";
			echo "<tr>";
			echo "<td>".$this->Form->input('customer_fname', array('label' => 'First Name','id'=>'MobileRepairCustomerFname','name'=>'MobileRepair[customer_fname]'))."</td>";//, 'readonly' => 'readonly'
			echo "<td>".$this->Form->input('customer_lname', array('label' => 'Last Name','id'=>'MobileRepairCustomerLname','name'=>'MobileRepair[customer_lname]'))."</td>";
			echo "<td>".$this->Form->input('customer_contact',array('label' => 'Mobile/Phone', 'maxlength'=> '11','id'=>'MobileRepairCustomerContact','name'=>'MobileRepair[customer_contact]'))."</td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td>".$this->Form->input('customer_email',array('id'=>'MobileRepairCustomerEmail','name'=>'MobileRepair[customer_email]'))."</td>";
			echo "<td>".$this->Form->input('zip',array('label' => 'Postal Code','id'=>'MobileRepairZip','name'=>'MobileRepair[zip]'))."</td>";
			echo "<td>".$this->Form->input('customer_address_1',array('name'=>'MobileRepair[customer_address_1]','id'=>'MobileRepairCustomerAddress1'))."</td>";		
			echo "</tr>";
			echo "<table>";
			echo "<tr>";
			echo "<td>".$this->Form->input('customer_address_2',array('id'=>'MobileRepairCustomerAddress2','name'=>'MobileRepair[customer_address_2]'))."</td>";
			echo "<td>".$this->Form->input('city',array('id'=>'MobileRepairCity','name'=>'MobileRepair[city]'))."</td>";		
			echo "<td>".$this->Form->input('state',array('id'=>'MobileRepairState','name'=>'MobileRepair[state]'))."</td>";		
			echo "<td>".$this->Form->input('country',array('options'=>$countryOptions,'id'=>'MobileRepairCountry','name'=>'MobileRepair[country]'))."</td>";//,'disabled'=>'disabled'
			echo "</tr>";
			echo "</table>";
			echo "</tr>";
			echo "</table>";
		  }
			
			
			echo "<table>";
			echo "<tr>";
				echo "<td>";
				echo $this->Form->input('brand_id',array('options'=>$brands,'disabled' => 'disabled','id'=>'MobileRepairBrandId','name'=>'MobileRepair[brand_id]'));
				echo "<td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>";
				echo $this->Form->input('mobile_model_id',array('options'=>$mobileModels, 'disabled' => 'disabled','id'=>'MobileRepairMobileModelId','name'=>'MobileRepair[mobile_model_id]'));
				echo "<td>";
			echo "</tr>";
		
			echo "<tr>";
			$option = array();
			foreach($problemArr as $k => $value){
			  if(array_key_exists($value,$costArr)){
				$cost = (int)$costArr[$value];
				for($i = $cost;$i<=$cost + 200;$i++){
				  $option[$k][$i] =  $i;
				}
			  }else{
				$cost = "";
				$option[$k] = array();
			  }
			  
			}
			$status_frezed = $this->request->data['status_freezed'];
			
			if(is_numeric($estimatedCostArr[0])){
				echo "<td>";
				echo $this->Form->input('repair_days', array('type' => 'hidden','name' => 'MobileRepair[repair_days_a]','id' => 'repair_days_a'));
				if($this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
					echo $this->Form->input('problem_type', array('options' => $problemArrOptns,
																  'value' => $problemArr[0],
																  'name' => 'MobileRepair[problem_type_a]',
																  'id'=>'MobileRepairProblemType',
															  
																  'rel' => $priceURL,
																  'empty' => '1st problem'
																  )
											);
				}else{
					echo $this->Form->input('problem_type', array('type'=>'hidden',
																  'options' => $problemArrOptns,
																  'value' => $problemArr[0],
																  'id'=>'MobileRepairProblemType',
																  'name' => 'MobileRepair[problem_type_a]'
																  )
											);
					echo $this->Form->input('problem_type', array('options' => $problemArrOptns,
																  'value' => $problemArr[0],
																  'disabled'=>'disabled',
																  'id'=>'MobileRepairProblemType',
																  'name' => 'MobileRepair[problem_type]'
																  )
											);
				}
				if($status_frezed == 1){ 
				  echo $this->Form->input('estimated_cost', array('type' => 'text',
																//'options' => array($option[0]),
																'style'=>'width: 20%',
																'value' => $estimatedCostArr[0],
																'name' => 'MobileRepair[estimated_cost_a]',
																'id'=>'estimated_cost_a_readonly',
																'readonly' => 'readonly'
																)
										);
				}else{
				  echo $this->Form->input('estimated_cost', array(//'type' => 'text',
																'options' => array($option[0]),
																'style'=>'width: 20%',
																'value' => $estimatedCostArr[0],
																'name' => 'MobileRepair[estimated_cost_a]',
																'id'=>'estimated_cost_a_readonly',
																'readonly' => 'readonly'
																)
										);
				}
				echo $this->Form->input('net_cost_a', array('type' => 'hidden',
															'name' => 'MobileRepair[net_cost_a]',
															'id'=>'net_cost_a'));
				echo "</td>";
			}
			if(array_key_exists(1,$estimatedCostArr) && is_numeric($estimatedCostArr[1])){
				echo "<td>";
				echo $this->Form->input('repair_days', array('type' => 'hidden','name' => 'MobileRepair[repair_days_b]','id' => 'repair_days_b'));
				if($this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
					echo $this->Form->input('problem_type', array('options' => $problemArrOptns,
																  'value' => $problemArr[1],
																  'name' => 'MobileRepair[problem_type_b]',
																  'id'=>'problem_type_b',
																  'rel' => $priceURL,
																  'empty' => '2nd problem'
																  )
											);
				}else{
					echo $this->Form->input('problem_type', array('type'=>'hidden',
																  'options' => $problemArrOptns,
																  'value' => $problemArr[1],
																  'name' => 'MobileRepair[problem_type_b]'
																  )
											);
					echo $this->Form->input('problem_type', array('options' => $problemArrOptns,
																  'value' => $problemArr[1],
																  'disabled'=>'disabled'
																  )
											);
				}
				
				if($status_frezed == 1){
				  echo $this->Form->input('estimated_cost', array('type' => 'text',
																//'options' => array($option[1]),
																'style'=>'width: 50%',
																'value' => $estimatedCostArr[1],
																'name' => 'MobileRepair[estimated_cost_b]',
																'readonly' => 'readonly',
																'id'=>'estimated_cost_b_readonly'
																)
										);
				}else{
				  echo $this->Form->input('estimated_cost', array(//'type' => 'text',
																'options' => array($option[1]),
																'style'=>'width: 50%',
																'value' => $estimatedCostArr[1],
																'name' => 'MobileRepair[estimated_cost_b]',
																'readonly' => 'readonly',
																'id'=>'estimated_cost_b'
																)
										);
				}
				echo $this->Form->input('net_cost_b', array('type' => 'hidden',
															'name' => 'MobileRepair[net_cost_b]',
															'id'=>'net_cost_b'
															)
										);
				echo "</td>";
			}
			if(array_key_exists(2,$estimatedCostArr) && is_numeric($estimatedCostArr[2])){
				echo "<td>";
				echo $this->Form->input('repair_days', array('type' => 'hidden','name' => 'MobileRepair[repair_days_c]','id' => 'repair_days_c'));
				if($this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
					echo $this->Form->input('problem_type', array('options' => $problemArrOptns,
																  'value' => $problemArr[2],
																  'name' => 'MobileRepair[problem_type_c]',
																  'id'=>'problem_type_c',
																  'rel' => $priceURL,
																  'empty' => '3rd problem'
																  )
											);
				}else{
					echo $this->Form->input('problem_type', array('type'=>'hidden',
																  'options' => $problemArrOptns,
																  'value' => $problemArr[2],
																  'name' => 'MobileRepair[problem_type_c]',
																 // 'id' => 'estimated_cost_c'
																  )
											);
					echo $this->Form->input('problem_type', array('options' => $problemArrOptns,
																  'value' => $problemArr[2],
																  'disabled'=>'disabled'
																  )
											);
				}
				
				if($status_frezed == 1){
				  echo $this->Form->input('estimated_cost', array('type' => 'text',
																//'options' => array($option[2]),
																'style'=>'width: 50%',
																'value' => $estimatedCostArr[2],
																'name' => 'MobileRepair[estimated_cost_c]',
																'readonly' => 'readonly',
																'id'=>'estimated_cost_c_readonly'
																)
										);
				}else{
				  echo $this->Form->input('estimated_cost', array(//'type' => 'text',
																'options' => array($option[2]),
																'style'=>'width: 50%',
																'value' => $estimatedCostArr[2],
																'name' => 'MobileRepair[estimated_cost_c]',
																'readonly' => 'readonly',
																'id'=>'estimated_cost_c'
																)
										);
				}
				
				echo $this->Form->input('net_cost_c', array('type' => 'hidden',
															'name' => 'MobileRepair[net_cost_c]',
															'id'=>'net_cost_c'
															)
										);
				echo "</td>";
			}
			echo "</tr>";
			if($status_frezed == 1){
			  echo "<tr>";
				echo "<td colspan='3' style='background-color: yellowgreen;'>";
					  echo "**Price freezed by admin. Please contact admin/manager to unfreeze price based on Brand,Model and problem type";
				echo "</td>";
			  echo "</tr>";
			}
			echo "</table>";
			echo "<table style='width: 137px;'>";
				echo "<tr>";
					echo "<td>";
						echo $this->Form->input('null',array('label' => 'Estimated Days', 'readonly' => 'readonly', 'value' => $maxRepairDays,'id'=>'max_repair_days','name'=>'MobileRepair[null]'));
					echo "</td>";
				echo "</tr>";
				echo "<tr>";
					echo"<td>";
					if($status_frezed == 1){
					  echo $this->Form->input('null',array('label' => 'Total Cost', 'name'=>'MobileRepair[total_cost]','readonly' => 'readonly', 'value' => $sum,'id'=>'total_price_readonly'));
					}else{
						echo $this->Form->input('null',array('label' => 'Total Cost', 'name'=>'MobileRepair[total_cost]','readonly' => 'readonly', 'value' => $sum,'id'=>'total_price_readonly'));
					}
					echo "</td>";
				echo "</tr>";
				echo "<tr>";
				
				if(count($mobileConditions)){
				  $mobileConditions['1000'] = 'Other';
				  $chunks = array_chunk($mobileConditions, 5, true);
				  if($this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
				     (($this->request['data']['status'] == DELIVERED_REPAIRED_BY_KIOSK ||
				       $this->request['data']['status'] == DELIVERED_UNREPAIRED_BY_KIOSK ||
				       $this->request['data']['status'] == DELIVERED_REPAIRED_BY_TECHNICIAN ||
				       $this->request['data']['status'] == DELIVERED_UNREPAIRED_BY_TECHNICIAN
				       )
				      && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS)
				     ){
					//show checkboxes in edit state for admin and Manager and in case of first stage of rebooking mobile for kiosk
					if(count($chunks)){
					  echo "<table id = 'mobile_condition_table'>";
						echo "<tr><td colspan='5'><h4>Mobile Condition</h4><hr/></td></tr>";
						  echo "<tr>";
							foreach($chunks as $c => $chunk){
							   echo "<td>";//pr($inputData);
								   foreach($chunk as $ch => $condition){
									   if(in_array($ch,$moblileArr)){
											$checked = "checked";	   
									   }else{
											$checked = '';
									   }
									   echo $this->Form->input($condition, array('type' => 'checkbox',
											'name'=>'MobileRepair[mobile_condition][]',
											'label' => array('style' => "color: blue;"),
											'value' => $ch,
											'hiddenField' => false,
											'checked' => $checked,
											//'id'=>'MobileRepairGoodCondition'
											//'disabled' => 'disabled'
									   ));
									}
							   echo "<td>";
							}
						echo "</tr>";
					  echo "</table>";
					  echo $this->Form->input('mobile_condition_remark',array('name' => 'MobileRepair[mobile_condition_remark]','label' => false, 'type' => 'text','placeholder' => 'Mobile Condition Remarks(Fill in case of other)','style' => 'display: none;', 'value' => $conditionRemarks));
					}
				  }else{
					if(count($chunks)){
					  echo "<table id = 'mobile_condition_table'>";
						echo "<tr><td colspan='5'><h4>Mobile Condition</h4><hr/></td></tr>";
						  echo "<tr>";
							foreach($chunks as $c => $chunk){
							   echo "<td>";//pr($inputData);
								   foreach($chunk as $ch => $condition){
									if(in_array($ch,$moblileArr)){
											$checked = "checked";	   
								   }else{
									   $checked = '';
								   }
								   echo $this->Form->input($condition, array('type' => 'checkbox',
										  'name'=>'MobileRepair[mobile_condition][]',
										  'label' => false,
										  'value' => $ch,
										  'hidden' => true,
										  //'id'=>'MobileRepairScratchAtBack',
										  'checked' => $checked
								   ));
								   echo $this->Form->input($condition, array('type' => 'checkbox',
										  'name'=>'MobileRepair[mobile_condition_temp][]',
										  'id'=>'MobileRepairScratchAtScreen',
										  'label' => array('style' => "color: blue;"),
										  'value' => $ch,
										  'hiddenField' => false,
										  'checked' => $checked,
										  'disabled' => 'disabled'
								   ));
						   }
						   echo "<td>";
					}
					  echo "</tr>";
					  echo "</table>";
					}
					echo $this->Form->input('mobile_condition_remark',array('name' => 'MobileRepair[mobile_condition_remark]','label' => false, 'type' => 'text','placeholder' => 'Mobile Condition Remarks(Fill in case of other)','style' => 'display: none;', 'value' => $conditionRemarks, 'readonly' => 'readonly'));
				  }
				}
		if(count($functionConditions)){
			$functionChunks = array_chunk($functionConditions,2,true);
			if($this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
			   (($this->request['data']['status'] == DELIVERED_REPAIRED_BY_KIOSK ||
				       $this->request['data']['status'] == DELIVERED_UNREPAIRED_BY_KIOSK ||
				       $this->request['data']['status'] == DELIVERED_REPAIRED_BY_TECHNICIAN ||
				       $this->request['data']['status'] == DELIVERED_UNREPAIRED_BY_TECHNICIAN
				       )
				      && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS)
			   ){
			  //show checkboxes in edit state for admin and Manager
			  if(count($functionChunks)){
				echo "<table><tr><td><h4>Mobile Functions Condition</h4><hr/></td></tr>";
				echo "<tr>";
				foreach($functionChunks as $f => $Fchunk){
				  echo "<td>";
				  foreach($Fchunk as $fch => $functionCondition){
					$checked = '';
					if(in_array($fch,$functionArr)){
					  $checked = "checked";	
					}
					echo $this->Form->input($functionCondition, array('type' => 'checkbox',
					  'name'=>'MobileRepair[function_condition_temp][]',
					  'label' => array('style' => "color: blue;"),
					  'id'=>'lcd-working',
					  'value' => $fch,
					  'hiddenField' => false,
					  'checked' => $checked,
					  //'disabled' => 'disabled'
					));
				  }
				  echo "<td>";
				}
				echo "</tr>";
				echo "</table>";
			  }
			}else{
			  if(count($functionChunks)){
				echo "<table><tr><td><h4>Mobile Functions Condition</h4><hr/></td></tr>";
				echo "<tr>";
				foreach($functionChunks as $f => $Fchunk){
				  echo "<td>";
				  foreach($Fchunk as $fch => $functionCondition){
					if(in_array($fch,$functionArr)){
					  $checked = "checked";	
					}else{
					  $checked = '';
					}
					echo "\n\n";
					echo $this->Form->input($functionCondition, array('type' => 'checkbox',
					  'name'=>'MobileRepair[function_condition][]',
					  'label' => false,
					  'div' => false,
					  'value' => $fch,
					  'hidden' => true,
					  'checked' => $checked,
					));
					echo "\n\n";
					echo $this->Form->input($functionCondition, array('type' => 'checkbox',
					  'name'=>'MobileRepair[function_condition_temp][]',
					  'label' => array('style' => "color: blue;"),
					  'value' => $fch,
					  'hiddenField' => false,
					  'checked' => $checked,
					  'disabled' => 'disabled'
					));
				  }
				  echo "<td>";
				}
				echo "</tr>";
				echo "</table>";
			  }
			}
			
		}
				echo "</tr>";
				echo "<tr>";
					 echo"<td>";
					 $imei = $this->request->data['imei'];
					 $imei1 = substr($imei, -1);
					 $imei2 = substr_replace($imei,'',-1) ;
					 
					if($this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
						echo '<div class="input input required">Imei<span style="color: red;">*</span><span id = "imei_quest" title="Please fill digit only. Incase serial number put in fault description!" style="background: steelblue;color: white;font-size: 14px;margin-left: 3px;">?</span></div>';
						echo "<table>";
						echo "<tr>";
											echo"<td>";
						//echo $this->Form->input('imei',array('label' => false, 'maxlength' => 14, 'id' => 'imei_id','name'=>'MobileRepair[null]' ,'div' => false,'style'=>"width: 449px;margin-left: 7px;"));
						echo $this->Form->input('null',	array(
															'label' => false,
															'maxlength'=>14,
															'div' => false,
															'id' => 'imei_id',
															'name'=>'MobileRepair[imei2]',
															'style'=>'width: 116px;height:25px;  ',
															'value' => $imei2,
														   
													  ));
											echo"</td>";
											echo"<td>";
						echo $this->Form->input('null',array(
														  'type' => 'text',
														  'label' => false,
														  'id' =>'imei1',
														  'readonly'=>'readonly',
														  'value' => $imei1,
														  'name'=>'MobileRepair[imei1]',
														  'style'=>"width: 20px; height:10px; margin-right: 825px; margin-top: -6px"));
		  					echo"<td>";
						echo "<tr>";
						echo "</table>";
						
					}else{
					  echo "<table>";
					  echo "<tr>";
								echo"<td>";
								  echo '<div class="input input required">Imei<span style="color: red;">*</span><span id = "imei_quest" title="Please fill digit only. Incase serial number put in fault description!" style="background: steelblue;color: white;font-size: 14px;margin-left: 3px;">?</span></div>';
								  echo $this->Form->input('null',array(
																	   'label' => false,
																	   'maxlength' => 14,
																	   //'readonly' => 'readonly',
																	   'id' => 'imei_id',
																	   'value' => $imei2,
																	   'div' => false,'style'=>"width: 170px;height:25px;margin-left: 7px;"));
						echo "</td>";
						echo "<td>";
						echo $this->Form->input('null',array(
															  'type' => 'text',
															  'label' => false,
															  'id' =>'imei1',
															  'readonly'=>'readonly',
															  'value' => $imei1,
															  'name'=>'MobileRepair[imei1]',
															  'style'=>"width: 20px; height:10px; margin-right: 825px; margin-top: 23px"));
						echo "</td>";
						echo "</tr>";
						echo "</table>";
					}					
					echo "</td>";
				echo "</tr>";
				echo "<tr>";
				if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
					echo "<td>";
						echo $this->Form->input('description',array('label' => 'Fault Description', 'style' => 'width:322px','id'=>'MobileRepairDescription','name'=>'MobileRepair[description]'));
					echo "</td>";
					}else{
						echo "<td>";
						if($this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
							echo $this->Form->input('description',array('label' => 'Fault Description','id'=>'MobileRepairDescription','name'=>'MobileRepair[description]'));
						}else{
								echo $this->Form->input('description',array('label' => 'Fault Description', 'readonly' => 'readonly','id'=>'MobileRepairDescription','name'=>'MobileRepair[description]'));
						}
					
							echo "</td>";
					}
					echo "<td>";
						echo $this->Form->input('phone_password', array('label' => array('class' => 'Your-Class', 'text' => 'Phone Password :'), 'style' => 'width:322px','id'=>'MobileRepairPhonePassword','name'=>'MobileRepair[phone_password]'));
					echo "</td>";
				echo "<tr>";	
			echo "</table>";
		}
		
		echo ('<h4>Repair Logs</h4><hr/>');
		
		?>
		<table>
			<?php $count = 0;
			//pr($repairLogs);
			$repairStatus = $repairStatusUserOptions+$repairStatusTechnicianOptions;
			$repairStatus[-1] = 'unambiguous';
			//pr($repairLogs);die;
			foreach($repairLogs as $id => $repairLog){
				  $repairLog['created']->i18nFormat(
                                                      [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                                      );
				$created = $repairLog['created']->i18nFormat('dd-MM-yyyy HH:mm:ss');
				$count++;
			//pr($repairLog);die;
			if($repairLog['status']==1){
				$currentStatus = "Refunded";
			}else{
				$rRepStatus = $repairLog['repair_status'];
                //pr($rRepStatus);
                //pr($repairStatus);die;
                if(array_key_exists($rRepStatus,$repairStatus)){
                    $currentStatus = $repairStatus[$rRepStatus];
                }else{
                    $currentStatus ="";
                }
			}
				if(!empty($repairLog['comments'])){
					?>
				<tr>
					<td><?= $count; ?></td>
					<?php  /*$comments = $repairLog['comments']->i18nFormat(
                                                      [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                                      );*/ ?>
					<td>Comment Posted by <span style="color: crimson"><strong><?php
					if(array_key_exists($repairLog['user_id'],$users)){
							echo $users[$repairLog['user_id']];
					}
					?></strong></span> &#40;comment id:<?=$repairLog['comments'];?>&#41; on <?= date('M jS, Y h:i:s A',strtotime($created));//$this->Time->format('M jS, Y g:i:s A', $repairLog['created'],null,null); ?> for <span style="color: darkorange"><?= $kiosks[$repairLog['kiosk_id']]; ?></span></td>
				</tr>
				<?php }else{ ?>
				<tr>
					<td><?= $count; ?></td>
					 <?php
                     
										 //echo $repairLog['created'];die;
                     //pr($users);die;
                     ?>
					<td>Last updated by <span style="color: crimson"><strong><?php if(array_key_exists($repairLog['user_id'],$users)){ ?> <?= $users[$repairLog['user_id']]; ?><?php } ?></strong></span> on <?= date('M jS, Y h:i:s A',strtotime($created));//$this->Time->format('M jS, Y h:i:s A', $repairLog['created'],null,null); ?>, <span style="color: blue">Status: <?= $currentStatus; ?></span> for <span style="color: darkorange"><?= $kiosks[$repairLog['kiosk_id']]; ?></span></td>
				</tr>
				<?php }
			}?>
		</table>
		<label for="MobileRepairBriefHistory">
			<strong>Staff Comments</strong>
			<span style='padding-left: 63%;text-align:right'><?php echo $this->Html->link('Add New Comment', array('controller' => 'comment_mobile_repairs', 'action' => 'add','id' => $this->request->data['id']));?></span>
			<h6>(For Internal Use)</h6>
		</label>
		
			<?php
			$tableStr = "";
			$i= 1;
			//pr($comments);die;
			foreach($comments as $sngComment){
				$comment = $sngComment['brief_history'];
				$commentID = $sngComment['id'];
				$postedOn = $sngComment['modified'];
				$postedOn = date('M jS, Y h:i A',strtotime($postedOn)); /*h:i A*/
				$postedBy = $sngComment['user']['username'];
				$userID = $sngComment['user']['id'];
				//$truncatedcomment  =
				//					  \Cake\Utility\Text::truncate(
				//																					$comment,
				//																					208,
				//																					[
				//																						'ellipsis' => '...',
				//																						'exact' => false
				//																					]
				//																				);
										
					$truncatedcomment = $this->Text->truncate($comment, 100);
										//echo $truncatedcomment;die;
				
				$tableStr.="";
				//$tableStr.="<tr><td style='width: 2px'>".$i++."</td>";
				//echo strlen($comment);die;
				if(strlen($comment)>100){
					$userLink = "<span style='color: crimson'><strong>".$postedBy."</strong></span>";
					$commentLink = $this->Html->link('Edit', array('controller' => 'comment_mobile_repairs','action' => 'edit', $commentID));
					$tableStr.="<td style='width: 90px'>$postedOn<br/></td>
					<td>$userLink</td>
					<td><a href = \"\" title = \"$comment\" alt = \"$comment\">$truncatedcomment</a></td>
					<td style='width: 0.5px;'>$commentLink</td></tr>";
				}else{
					$userLink = "<span style='color: crimson'><strong>".$postedBy."</strong></span>";
					$commentLink = $this->Html->link('Edit', array('controller' => 'comment_mobile_repairs','action' => 'edit', $commentID));
					$tableStr.="<td style='width: 90px'>$postedOn<br/></td>
					<td>$userLink</td>
					
					<td>$comment</td>
					<td style='width: 0.5px;'>$commentLink</td></tr>";
				}
			  	
			}
			if(empty($tableStr)){
				$tableStr = "<tr><td><span style='color:red'>No Record Found!!!</span></td></tr>";
			}
			echo "<table cellspacing='2' cellpadding='2'><tr><td colspan='3'><h3>Comments:</h3></td></tr>$tableStr</table>"
			?>
		
		<?php 	
		
		$repairStatus = $repairStatusUserOptions+$repairStatusTechnicianOptions;		
		
		
		if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			if($this->request['data']['status'] == BOOKED){
				$removeKeys = array(BOOKED, REBOOKED, RECEIVED_REPAIRED_FROM_TECHNICIAN, RECEIVED_UNREPAIRED_FROM_TECHNICIAN, DELIVERED_REPAIRED_BY_TECHNICIAN, DELIVERED_UNREPAIRED_BY_TECHNICIAN);			
				foreach($removeKeys as $key){
					unset($repairStatusUserOptions[$key]);
				}
			}
			
			if($this->request['data']['status'] == REBOOKED){
				$removeKeys = array(BOOKED, REBOOKED, RECEIVED_REPAIRED_FROM_TECHNICIAN, RECEIVED_UNREPAIRED_FROM_TECHNICIAN, DELIVERED_REPAIRED_BY_TECHNICIAN, DELIVERED_UNREPAIRED_BY_TECHNICIAN);
				
				foreach($removeKeys as $key){
					unset($repairStatusUserOptions[$key]);
				}
			}
			
			if($this->request['data']['status'] == RECEIVED_REPAIRED_FROM_TECHNICIAN){
				$removeKeys = array(BOOKED, REBOOKED, DISPATCHED_TO_TECHNICIAN, RECEIVED_REPAIRED_FROM_TECHNICIAN, RECEIVED_UNREPAIRED_FROM_TECHNICIAN, DELIVERED_REPAIRED_BY_KIOSK, DELIVERED_UNREPAIRED_BY_KIOSK, DELIVERED_UNREPAIRED_BY_TECHNICIAN);			
				foreach($removeKeys as $key){
					unset($repairStatusUserOptions[$key]);
				}
			}
			if($this->request['data']['status'] == RECEIVED_UNREPAIRED_FROM_TECHNICIAN){
				$removeKeys = array(BOOKED, REBOOKED, DISPATCHED_TO_TECHNICIAN, RECEIVED_REPAIRED_FROM_TECHNICIAN, RECEIVED_UNREPAIRED_FROM_TECHNICIAN, DELIVERED_REPAIRED_BY_KIOSK, DELIVERED_UNREPAIRED_BY_KIOSK, DELIVERED_REPAIRED_BY_TECHNICIAN);			
				foreach($removeKeys as $key){
					unset($repairStatusUserOptions[$key]);
				}
			}
			if($this->request['data']['status'] == DELIVERED_REPAIRED_BY_KIOSK ||
			   $this->request['data']['status'] == DELIVERED_UNREPAIRED_BY_KIOSK ||
			   $this->request['data']['status'] == DELIVERED_REPAIRED_BY_TECHNICIAN ||
			   $this->request['data']['status'] == DELIVERED_UNREPAIRED_BY_TECHNICIAN){
				$removeKeys = array(BOOKED, DISPATCHED_TO_TECHNICIAN, RECEIVED_REPAIRED_FROM_TECHNICIAN, RECEIVED_UNREPAIRED_FROM_TECHNICIAN, DELIVERED_REPAIRED_BY_KIOSK, DELIVERED_UNREPAIRED_BY_KIOSK, DELIVERED_REPAIRED_BY_TECHNICIAN, DELIVERED_UNREPAIRED_BY_TECHNICIAN);			
				foreach($removeKeys as $key){
					unset($repairStatusUserOptions[$key]);
				}
			}else{
				unset($repairStatusUserOptions[REBOOKED]);
			}
			
			if($this->request['data']['status'] == DISPATCHED_2_KIOSK_REPAIRED){
				$removeKeys = array(BOOKED, REBOOKED, DISPATCHED_TO_TECHNICIAN, RECEIVED_UNREPAIRED_FROM_TECHNICIAN, DELIVERED_REPAIRED_BY_KIOSK, DELIVERED_UNREPAIRED_BY_KIOSK, DELIVERED_REPAIRED_BY_TECHNICIAN, DELIVERED_UNREPAIRED_BY_TECHNICIAN);			
				foreach($removeKeys as $key){
					unset($repairStatusUserOptions[$key]);
				}
			}
			
			if($this->request['data']['status'] == DISPATCHED_2_KIOSK_UNREPAIRED){
				$removeKeys = array(BOOKED, REBOOKED, DISPATCHED_TO_TECHNICIAN, RECEIVED_REPAIRED_FROM_TECHNICIAN, DELIVERED_REPAIRED_BY_KIOSK, DELIVERED_UNREPAIRED_BY_KIOSK, DELIVERED_REPAIRED_BY_TECHNICIAN, DELIVERED_UNREPAIRED_BY_TECHNICIAN);			
				foreach($removeKeys as $key){
					unset($repairStatusUserOptions[$key]);
				}
			}
			
			$refundAmount = '';
			
			foreach($dataRepairSale as $dataSale){			
				if((int)$dataSale['refund_amount']){
					$refundAmount = $dataSale['refund_amount'];
				}
			}
			
			if((int)$refundAmount){
				echo "<h3>Status</h3><h4>Customer has already been refunded for this Mobile Repair</h4>";
			}elseif($this->request['data']['status'] == RECEIVED_BY_TECHNICIAN ||
			   $this->request['data']['status'] == REPAIR_UNDER_PROCESS ||
			   $this->request['data']['status'] == WAITING_FOR_DISPATCH ){
				echo "<h3>Status</h3><h4>".$repairStatusTechnicianOptions[$this->request['data']['status']]."</h4>";	
			}elseif($this->request['data']['status'] == DISPATCHED_TO_TECHNICIAN){
				echo "<h3>Status</h3><h4>".$repairStatusUserOptions[$this->request['data']['status']]."</h4>";	
			}			
			else{
			  //pr($repairStatusUserOptions);
				echo $this->Form->input('status',array('options' => $repairStatusUserOptions,'id'=>'MobileRepairStatus','name'=>'MobileRepair[status]'));	
			}
			
			
		}elseif($this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){			
			if($this->request['data']['status'] == DISPATCHED_TO_TECHNICIAN){
				$removeKeys = array(REPAIR_UNDER_PROCESS, DISPATCHED_2_KIOSK_REPAIRED, DISPATCHED_2_KIOSK_UNREPAIRED, WAITING_FOR_DISPATCH);			
				foreach($removeKeys as $key){
					unset($repairStatusTechnicianOptions[$key]);
				}
			}
			
			if($this->request['data']['status'] == RECEIVED_BY_TECHNICIAN){
				$removeKeys = array(RECEIVED_BY_TECHNICIAN, REPAIR_UNDER_PROCESS, WAITING_FOR_DISPATCH);			
				foreach($removeKeys as $key){
					unset($repairStatusTechnicianOptions[$key]);
				}
			}
			if($this->request['data']['status'] == REPAIR_UNDER_PROCESS){
				$removeKeys = array(RECEIVED_BY_TECHNICIAN, REPAIR_UNDER_PROCESS, WAITING_FOR_DISPATCH);			
				foreach($removeKeys as $key){
					unset($repairStatusTechnicianOptions[$key]);
				}
			}
			
			if($this->request['data']['status'] == WAITING_FOR_DISPATCH){
				$removeKeys = array(WAITING_FOR_DISPATCH, RECEIVED_BY_TECHNICIAN, REPAIR_UNDER_PROCESS, DISPATCHED_2_KIOSK_UNREPAIRED);			
				foreach($removeKeys as $key){
					unset($repairStatusTechnicianOptions[$key]);
				}
			}
			
			if($this->request['data']['status'] == DISPATCHED_2_KIOSK_REPAIRED ||
			   $this->request['data']['status'] == DISPATCHED_2_KIOSK_UNREPAIRED){
				echo "<h3>Status</h3><h4>".$repairStatusTechnicianOptions[$this->request['data']['status']]."</h4>";
				echo "<input type='hidden' name='no_parts' value='1' />";
			}elseif($this->request['data']['status'] == BOOKED ||
				$this->request['data']['status'] == REBOOKED ||
				$this->request['data']['status'] == RECEIVED_REPAIRED_FROM_TECHNICIAN ||
				$this->request['data']['status'] == RECEIVED_UNREPAIRED_FROM_TECHNICIAN ||
				$this->request['data']['status'] == DELIVERED_REPAIRED_BY_KIOSK ||
				$this->request['data']['status'] == DELIVERED_REPAIRED_BY_TECHNICIAN ||
				$this->request['data']['status'] == DELIVERED_UNREPAIRED_BY_TECHNICIAN){
				echo "<input type='hidden' name='no_parts' value='1' />";
				echo "<h3>Status</h3><h4>".$repairStatusUserOptions[$this->request['data']['status']]."</h4>";
			}else{
				echo $this->Form->input('status',array('options' => $repairStatusTechnicianOptions,'id'=>'MobileRepairStatus','name'=>'MobileRepair[status]'));	
			}
			
		}if($this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			$adminStatus = $repairStatusUserOptions+$repairStatusTechnicianOptions;
			if(array_key_exists($this->request['data']['status'],$adminStatus)){
                echo "<h3>Status</h3><h4>".$adminStatus[$this->request['data']['status']]."</h4>";
            }
			echo $this->Form->input('status',array('type'=>'hidden','name' => 'MobileRepair[status]','value'=>$this->request['data']['status']));
			echo $this->Form->input('send', array('type' => 'checkbox', 'label' => 'Send mail', 'value' =>'1','name'=>'MobileRepair[send]','id'=>'MobileRepairSend'));
		}
		
		?>
	
<?php

	$refundAmount = '';
	foreach($dataRepairSale as $dataSale){
        //pr($dataSale);die;
				if((int)$dataSale['refund_amount']){
					$refundAmount = $dataSale['refund_amount'];
				}
			}
	if(($this->request->session()->read('Auth.User.group_id') == MANAGERS ||
		$this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER)
		){
		//rajju date 26.11.2015
		$options = array(
					'label' => 'Submit',
					'id'=> 'btnSubmit',
					//'name'=> 'submit'
	 
		);
		echo $this->Form->Submit('Submit',['label' => 'Submit','id'=> 'btnSubmit']);
        echo $this->Form->end();
		//echo $this->Form->end(__('$options'));
		//rajju date 26.11.2015 till here
	}elseif(
	   $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
	 ($this->request['data']['status'] == RECEIVED_BY_TECHNICIAN ||
		$this->request['data']['status'] == REPAIR_UNDER_PROCESS ||
		$this->request['data']['status'] == WAITING_FOR_DISPATCH ||
		$this->request['data']['status'] == DISPATCHED_TO_TECHNICIAN ||
		(int)$refundAmount) 
	){
		echo $this->Form->end();
	}elseif($this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS &&
	 ($this->request['data']['status'] == DISPATCHED_2_KIOSK_REPAIRED ||
		$this->request['data']['status'] == DISPATCHED_2_KIOSK_UNREPAIRED ||
		$this->request['data']['status'] == BOOKED ||
		$this->request['data']['status'] == REBOOKED ||
		$this->request['data']['status'] == RECEIVED_REPAIRED_FROM_TECHNICIAN ||
		$this->request['data']['status'] == RECEIVED_UNREPAIRED_FROM_TECHNICIAN ||
		$this->request['data']['status'] == DELIVERED_REPAIRED_BY_KIOSK ||
		$this->request['data']['status'] == DELIVERED_REPAIRED_BY_TECHNICIAN ||
		$this->request['data']['status'] == DELIVERED_UNREPAIRED_BY_TECHNICIAN)){
		echo $this->Form->input('status',array('type'=>'hidden','name' => 'MobileRepair[status]','value'=>$this->request['data']['status']));
		echo $this->Form->Submit(__('Submit'));
        echo $this->Form->end();
	}elseif($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS ||
		$this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS
		){
		echo "<input type='hidden' name='MobileRepair[formValid]' id = 'formValid' value='0' />";
		echo $this->Form->input('change_status', array('label' => 'If unchecked only changes will be saved without affecting the status', 'type' => 'checkbox', "checked" => "checked", 'id' => 'checkbox_status','name' =>'MobileRepair[change_status]'));
		echo $this->Form->input('status',array('type'=>'hidden','name' => 'MobileRepair[status]','value'=>$this->request['data']['status'], 'id' => 'hidden_status'));
		echo $this->Form->input('hiddenStatus',array('type'=>'hidden','name' => 'MobileRepair[hiddenStatus]','value'=>$this->request['data']['status'], 'id' => 'hiddenStatus'));
		
		if($this->request['data']['status'] == 4){
		  if( $this->request['data']['status_rebooked'] == 1){
			
            echo $this->Form->Submit(__('Submit'));
            echo $this->Form->end();
		  }else{  ?>
			<input type='button' value='submit' id='s_make_payment'  style="width: 82px;" />
		  <?php }
		  ?>
		  
		<?php }else{
		  echo $this->Form->submit('submit');
		  echo $this->Form->end(); 
		}
		
	}
	?>

</div>
<div id="payment_div">
  <?php echo $this->element('/MobileRepairs/repair_payment',array(
																  'setting' => $setting,
																  )); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Mobile Repair'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Mobile Repairs'), array('action' => 'index')); ?></li>
		<li><?php
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){
		  #echo $this->Form->postLink(__('Delete Repair'), array('action' => 'delete', $this->Form->value('MobileRepair.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('MobileRepair.id')));
		}
		?></li>			
		<li><?php echo $this->element('repair_navigation'); ?></li>		
	</ul>
</div>
<script>
  $(document).ready(function (){
	  $("#hidden_status").prop('disabled', true);
	  $("#hiddenStatus").prop('disabled', true);
	});
  
  $(function() {
	$('#problem_type_a, #problem_type_b, #problem_type_c').change(function() {
		//need to update net_price as well.
		if ($('#problem_type_a').val() == null  || $('#problem_type_a').val() == '') {
		 // alert("hi");
			val1 = 0;
			$('#estimated_cost_a').find('option').remove().end();
			$('#estimated_cost_a').append("<option value='0'></option>");
			$('#net_cost_a').val(0);
		}else{
			val1 = parseInt($('#estimated_cost_a').val());
		}
		
		if ($('#problem_type_b').val() == null || $('#problem_type_b').val() == '') {
			val2 = 0;
			$('#estimated_cost_b').find('option').remove().end();
			$('#estimated_cost_b').append("<option value='0'></option>");
			$('#net_cost_b').val(0);
		}else{
			val2 = parseInt($('#estimated_cost_b').val());
		}
		
		//alert($('#problem_type_c').val());
		if ($('#problem_type_c').val() == null || $('#problem_type_c').val() == '') {
			val3 = 0;
			$('#estimated_cost_c').find('option').remove().end();
			$('#estimated_cost_c').append("<option value='0'></option>");
			$('#net_cost_c').val(0);
		}else{
			val3 = parseInt($('#estimated_cost_c').val());
		}
		total = val1+val2+val3;
		//alert(total);
		$('#total_price').val(total);
	});
	
	  $('#estimated_cost_a, #estimated_cost_b, #estimated_cost_c').change(function() {
		//alert($('#estimated_cost_c').val());
		//alert($('#estimated_cost_b').val());
		  //rasu:newly added function
		  var val1 = 0;
		  if ($('#estimated_cost_a').val()) {
			if ($('#estimated_cost_a').val().length != 0) {
				val1 = parseInt($('#estimated_cost_a').val());
			}
			$('#net_cost_a').val($('#estimated_cost_a').val());
		  }
		 
		  var val2 = 0;
		  if ($('#estimated_cost_b').val()) {
			if ($('#estimated_cost_b').val().length != 0) {
				val2 = parseInt($('#estimated_cost_b').val());
			}
			$('#net_cost_b').val($('#estimated_cost_b').val());
		  }
		
		  var val3 = 0;
		  if ($('#estimated_cost_c').val()) {
            if ($('#estimated_cost_c').val().length != 0) {
				val3 = parseInt($('#estimated_cost_c').val());
			}
			$('#net_cost_c').val($('#estimated_cost_c').val());
          }
		
		  total = val1+val2+val3;
		  $('#total_price').val(total);
		  
		  $("#hidden_status").prop('disabled', true);
	      $("#hiddenStatus").prop('disabled', true);
	  });
	});
  
  
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
						
				if (response) {
					//$('#MobileRepairMobileModelId').children().remove();
					$('#MobileRepairMobileModelId').find('option').remove().end();
					$('#MobileRepairMobileModelId').append(response);//html(response.content);
				}
			},
			error: function(e) {
			    $.unblockUI();
			     $('#error_div').html("An error occurred: " + e.responseText.message).css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			    alert("An error occurred: " + e.responseText.message);
			    console.log(e);
			}
		});
	});
	
	function initialize_inputs() {
	
		$('#problem_type_a').val("");
		$('#problem_type_b').val("");
		$('#problem_type_c').val("");
		
		$('#estimated_cost_a').val("");
		$('#estimated_cost_b').val("");
		$('#estimated_cost_c').val("");
	
		$('#repair_days_a').val("");
		$('#repair_days_b').val("");
		$('#repair_days_c').val("");
		
		$('#max_repair_days').val("");
		$('#total_price').val("");
	}
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
							
				if (response) {
					var problemTypeA = "<option>1st problem</option>";
					var obj = jQuery.parseJSON( response);
					//$('#MobileRepairMobileModelId').children().remove();
					$('#problem_type_a').find('option').remove().end();
					$.each(obj, function(i, elem){
						problemTypeA+= "<option value=" + i + ">" + elem + "</option>";
					});
					$('#problem_type_a').append(problemTypeA);//html(response.content);
					
					var problemTypeB = "<option>2nd problem</option>";
					var obj = jQuery.parseJSON( response);
					
					//$('#MobileRepairMobileModelId').children().remove();
					$('#problem_type_b').find('option').remove().end();
					$.each(obj, function(i, elem){
						problemTypeB+= "<option value=" + i + ">" + elem + "</option>";
					});
					$('#problem_type_b').append(problemTypeB);//html(response.content);
					
					var problemTypeC = "<option>3rd problem</option>";
					var obj = jQuery.parseJSON( response);
					
					//$('#MobileRepairMobileModelId').children().remove();
					$('#problem_type_c').find('option').remove().end();
					$.each(obj, function(i, elem){
						problemTypeC+= "<option value=" + i + ">" + elem + "</option>";
					});
					$('#problem_type_c').append(problemTypeC);//html(response.content);
					
					initialize_sum();
				}
			},
			error: function(e) {
			    $.unblockUI();
			     $('#error_div').html("An error occurred: " + e.responseText.message).css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			    alert("An error occurred: " + e.responseText.message);
			    console.log(e);
			}
		});
	});
	$('#problem_type_a').change(function() {
	  if ($('#admin').val()) {
		  var admin  = $('#admin').val();        
      }else{
		var admin  = '';
	  }
		var internal_repair = $('#internal_repair').val();
		var brandID = $('#MobileRepairBrandId').val();
		var modelID = $('#MobileRepairMobileModelId').val();
		var problemType = $(this).val();		
		var targeturl = $(this).attr('rel') + '?problemType=' + problemType + "&brandID="+brandID+"&modelID="+modelID;
		if (parseInt(modelID) == 0) {
			$(this).val("");
			 $('#error_div').html("Either there is no model for selected brand or you have not seleted any brand").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert("Either there is no model for selected brand or you have not seleted any brand");
			return;
		}
		//alert("Brand ID: "+brandID + " Problem ID "+ problemType + " modelID "+ modelID + " <br/>targeturl "+ targeturl);
		if (problemType == "1st problem" || problemType == "0" || problemType == "") {
			//$('#repair_days_a').val("0");
			//$('#estimated_cost_a').val("0");
			//
			$('#estimated_cost_a').find('option').remove().end();
			$('#estimated_cost_a').append("<option value='0'></option>");
			$('#net_cost_a').val(0);
			initializeSum();
			//getMaxVal();
			return false;
			}
		if (modelID == "" || modelID == "0") {$(this).val("");$('#error_div').html("Please choose model").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();alert("Please choose model");return;}
		$.blockUI({ message: 'Just a moment...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
			    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				//alert("hello");
				$.unblockUI();
				var obj = jQuery.parseJSON( response);
				//console.log(obj);
				if (obj.error == 0) {
				  //alert(obj.repair_days);
					$('#repair_days_a').val(obj.repair_days);
					$('#estimated_cost_a').val(obj.repair_price);
					var startCost = parseInt(obj.repair_price);
					if (admin == 1) {
                        var start = 1;
                    }else{
					  var start = startCost;
					}
					var endCost = startCost + 200;
					var optionStr = "";
					if (internal_repair == 1) {
                        optionStr += "<option value='" + 0.0001 + "' >" + 0.0001 + "</option>";
                    }
					for(i = start; i <= endCost; i++){
						optionStr += "<option value='" + i + "' >" + i + "</option>";
					}
					$('#estimated_cost_a').find('option').remove().end();
					$('#estimated_cost_a').append(optionStr);//html(response.content);
					if (admin == 1) {
                      $('#estimated_cost_a').val(startCost);
                    }
					$('#net_cost_a').val(obj.repair_cost);
					//getMaxVal();
					initializeSum();
				}else{
					$('#repair_days_a').val("");
					//$('#estimated_cost_a').val("");
					$('#estimated_cost_a').find('option').remove().end();
					$('#estimated_cost_a').append("<option value='0'></option>");
					initializeSum();
					//getMaxVal();
					$('#error_div').html("No price for this combination!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
					alert("No price for this combination!");
				}
				
			},
			error: function(e) {
			    $.unblockUI();
			    $('#error_div').html("An error occurred: " + e.responseText.message).css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			    alert("An error occurred: " + e.responseText.message);
			    console.log(e);
			}
		});
	});
	
	$('#problem_type_b').change(function() {
		if ($('#admin').val()) {
			var admin  = $('#admin').val();        
		}else{
		  var admin  = '';
		}
		
		var internal_repair = $('#internal_repair').val();
		var brandID = $('#MobileRepairBrandId').val();
		var modelID = $('#MobileRepairMobileModelId').val();
		var problemType = $(this).val();		
		var targeturl = $(this).attr('rel') + '?problemType=' + problemType + "&brandID="+brandID+"&modelID="+modelID;
		if (parseInt(modelID) == 0) {
			$(this).val("");
			$('#error_div').html("Either there is no model for selected brand or you have not seleted any brand").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert("Either there is no model for selected brand or you have not seleted any brand");
			return;
		}
		//alert("Brand ID: "+brandID + " Problem ID "+ problemType + " modelID "+ modelID + " <br/>targeturl "+ targeturl);
		if (problemType == "2nd problem" ||problemType == "" || problemType == "0") {
			//$('#repair_days_b').val("0");
			//$('#estimated_cost_b').val("0");
			$('#estimated_cost_b').find('option').remove().end();
			$('#estimated_cost_b').append("<option value='0'></option>");
			$('#net_cost_b').val(0);
			initializeSum();
			//getMaxVal(); 
			return false;
			}
		if (modelID == "" || modelID == "0") {$(this).val("");$('#error_div').html("Please choose model").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();alert("Please choose model");return;}
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
					if (admin == 1) {
                        var start = 1;
                    }else{
					  var start = startCost;
					}
					
					var endCost = startCost + 200;
					var optionStr = "";
					if (internal_repair == 1) {
                        optionStr += "<option value='" + 0.0001 + "' >" + 0.0001 + "</option>";
                    }
					for(i = start; i <= endCost; i++){
						optionStr += "<option value='" + i + "' >" + i + "</option>";
					}
					$('#estimated_cost_b').find('option').remove().end();
					$('#estimated_cost_b').append(optionStr);//html(response.content);
					if (admin == 1) {
                      $('#estimated_cost_b').val(startCost);
                    }
					$('#net_cost_b').val(obj.repair_cost);
					initializeSum();
					getMaxVal();
				}else{
					$('#repair_days_b').val("");
					//$('#estimated_cost_b').val("");
					$('#estimated_cost_b').find('option').remove().end();
					$('#estimated_cost_b').append("<option value='0'></option>");
					initializeSum();
					getMaxVal();
					$('#error_div').html("No price for this combination").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
					alert("No price for this combination");
				}
				
				
			},
			error: function(e) {
			    $.unblockUI();
			    $('#error_div').html("An error occurred: " + e.responseText.message).css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			    alert("An error occurred: " + e.responseText.message);
			    console.log(e);
			}
		});
	});
	
	
	
	$('#problem_type_c').change(function() {
		if ($('#admin').val()) {
			var admin  = $('#admin').val();        
		}else{
		  var admin  = '';
		}
		
		var internal_repair = $('#internal_repair').val();
		var brandID = $('#MobileRepairBrandId').val();
		var modelID = $('#MobileRepairMobileModelId').val();
		var problemType = $(this).val();		
		var targeturl = $(this).attr('rel') + '?problemType=' + problemType + "&brandID="+brandID+"&modelID="+modelID;
		if (parseInt(modelID) == 0) {
			$(this).val("");
			$('#error_div').html("Either there is no model for selected brand or you have not seleted any brand").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert("Either there is no model for selected brand or you have not seleted any brand");
			return;
		}
		//alert("Brand ID: "+brandID + " Problem ID "+ problemType + " modelID "+ modelID + " <br/>targeturl "+ targeturl);
		if (problemType == "3rd problem" ||problemType == "" || problemType == "0") {
			//rajju dated 26.11.2015
			$('#repair_days_c').val("0");
			$('#estimated_cost_c').val("0");
			$('#estimated_cost_c').find('option').remove().end();
			$('#estimated_cost_c').append("<option value='0'></option>");
			$('#net_cost_c').val(0);
			//initializeSum();
			//getMaxVal();
			//rajju dated 26.11.2015 till here
			return false;
		}
		if (modelID == "" || modelID == "0") {$(this).val("");$('#error_div').html("Please choose model").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();alert("Please choose model");return;}
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
				  if (parseInt($('#max_repair_days').val()) < obj.repair_days) {
                    $('#repair_days_c').val(obj.repair_days);
					$('#max_repair_days').val(obj.repair_days);
                  }
					//$('#estimated_cost_c').val(obj.repair_price);
					var startCost = parseInt(obj.repair_price);
					
					if(admin == 1) {
                        var start = 1;
                    }else{
					  var start = startCost;
					}
					
					var endCost = startCost + 200;
					var optionStr = "";
					if (internal_repair == 1) {
                        optionStr += "<option value='" + 0.0001 + "' >" + 0.0001 + "</option>";
                    }
					for(i = start; i <= endCost; i++){
						optionStr += "<option value='" + i + "' >" + i + "</option>";
					}
					$('#estimated_cost_c').find('option').remove().end();
					$('#estimated_cost_c').append(optionStr);//html(response.content);
					if (admin == 1) {
                      $('#estimated_cost_c').val(startCost);
                    }
					$('#net_cost_c').val(obj.repair_cost);
					initializeSum();
					getMaxVal();
				}else{
					$('#repair_days_c').val("");
					//$('#estimated_cost_c').val("");
					$('#estimated_cost_c').find('option').remove().end();
					$('#estimated_cost_c').append("<option value='0'></option>");
					initializeSum();
					getMaxVal();
					$('#error_div').html("No price for this combination").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
					alert("No price for this combination");
				}
				
				
			},
			error: function(e) {
			    $.unblockUI();
			    $('#error_div').html("An error occurred: " + e.responseText.message).css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			    alert("An error occurred: " + e.responseText.message);
			    console.log(e);
			}
		});
	});
	
	function initializeSum(){
		var val1 = 0;
		if($('#estimated_cost_a').val()){
			if ($('#estimated_cost_a').val().length != 0)   {
			 val1 = parseInt($('#estimated_cost_a').val());
		  }
		}
		//alert(document.getElementById('estimated_cost_b'));
		var val2 = 0;
		if ($('#estimated_cost_b').val()) {
			if ($('#estimated_cost_b').val().length != 0  ) {
			  val2 = parseInt($('#estimated_cost_b').val());
		    }
		}
		//alert(document.getElementById('estimated_cost_c'));
		var val3 = 0;
		if ($('#estimated_cost_c').val()) {
			if ($('#estimated_cost_c').val().length != 0 ) {
			val3 = parseInt($('#estimated_cost_c').val());
		       }
		}
		
		total = val1+val2+val3;
		
		$('#total_price').val(total);
	}
	
	function maxdays() {
        val1 = parseInt($('#repair_days_a').val());
		alert(val1);
    }
	
	function initialize_sum() {
		
		$('#estimated_cost_a').find('option').remove().end();
		$('#estimated_cost_a').append("<option value='0'></option>");
		
		
		$('#estimated_cost_b').find('option').remove().end();
		$('#estimated_cost_b').append("<option value='0'></option>");
		
	
		$('#estimated_cost_c').find('option').remove().end();
		$('#estimated_cost_c').append("<option value='0'></option>");
		
		initializeSum();
		$('#net_cost_a').val($('#estimated_cost_a').val());
		$('#net_cost_b').val($('#estimated_cost_b').val());
		$('#net_cost_c').val($('#estimated_cost_c').val());
	}
	
	
	
	
	function getMaxVal() {
		var val1 = 0;
		if ($('#repair_days_a').val().length != 0) {
		 val1 = parseInt($('#repair_days_a').val());
		}
		
		var val2 = 0;
		if (document.getElementById('repair_days_b')) {
			if ($('#repair_days_b').val().length != 0) {
			val2 = parseInt($('#repair_days_b').val());
		       }
		}
		
		var val3 = 0;
		if (document.getElementById('repair_days_c')) {
			if ($('#repair_days_c').val().length != 0) {
			val3 = parseInt($('#repair_days_c').val());
		       }
		}
		
		var maxRepairDay = Math.max(val1,val2,val3);
		
		var estCostA = 0;
		if ($('#estimated_cost_a').val()) {
            if ($('#estimated_cost_a').val().length != 0) {
				estCostA = parseInt($('#estimated_cost_a').val()); 
			}
        }
		
		
		var estCostB = 0;
		if (document.getElementById('estimated_cost_b')) {
			if ($('#estimated_cost_b').val().length != 0) {
			estCostB = parseInt($('#estimated_cost_b').val());
		       }
		}
		
		var estCostC = 0;
		if (document.getElementById('estimated_cost_c')) {
			if ($('#estimated_cost_c').val().length != 0) {
			estCostC = parseInt($('#estimated_cost_c').val());
		       }
		}
		
		var maxEstCost = Math.max(estCostA,estCostB,estCostC);
		
		if (maxRepairDay == 0 && maxEstCost == 0) {
			$('#max_repair_days').val(maxRepairDay);
		} else if (maxRepairDay != 0 && maxEstCost != 0) {
			$('#max_repair_days').val(maxRepairDay);
		}
	}
	
	function sumestprice() {
		var a = $('#estimated_cost_a').val();
		var b = $('#estimated_cost_b').val();
		var c = $('#estimated_cost_c').val();
		var total = 0;
		if ($.isNumeric(a)  ) {
			var total = parseFloat(a);
		}
		
		if ($.isNumeric(b)) {
			total+= parseFloat(b);
		}
		
		if ($.isNumeric(c)) {
			total+= parseFloat(c);
		}
		
		//alert(total);
		return total;
	}
	
	
	 
	$("#MobileRepairCustomerContact").keydown(function (event) {		
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
	
	
	$("#max_repair_days").keydown(function (event) {
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
	$("#imei").keydown(function (event) {
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
	$( "#imei_id" ).keyup(function() {
		var imei_id = $('#imei_id').val();
		if ($('#imei_id').val().length < 14) {
			//alert('hello');
			$('#imei1').val("");
		}
		
});
	
	$(function() {
	  $( document ).tooltip();
	});
</script>
<script>
function validateForm(){
 if ($('#MobileRepairCustomerFname').val() == "") {
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
	if (document.getElementById("mobile_condition_table")) {
		var mobileCondChk = $('input[name="MobileRepair[mobile_condition][]"]:checkbox:checked');
		//alert(mobileCondChk.length);
		if (mobileCondChk.length == 0)  {
			$('#error_div').html('Please select phone"s condition!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert('Please select phone"s condition!');
			return false;
		}
	}
	
	if ($('#other').is(":checked")) {
		if ($('#mobile-condition-remark').val() == '') {
			$('#error_div').html("Please input mobile condition remarks!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert('Please input mobile condition remarks!');
			return false;
		}
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
	if ($('#imei_id').val() == '') {
		$('#error_div').html("Please input the imei number!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert("Please input the imei number!");
		return false;
	}else if ($('#imei_id').val().length < 14) {
		$('#error_div').html('Input imei should be of 15 characters!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert('Input imei should be of 15 characters!');
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
	
	var problem_type_a = $("#problem_type_a");
	if (problem_type_a.val() == "" || problem_type_a.val() == "0" || problem_type_a.val() == "1st problem") {
	    $('#error_div').html("Please select Problem Type A!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
	    alert("Please select Problem Type A!");
	    return false;
	}
	
	
	if (document.getElementById('estimated_cost_a')) {
	  var estimated_cost_a = $("#estimated_cost_a").val();
		if (estimated_cost_a == "" || isNaN(estimated_cost_a)) {
	      $('#error_div').html("Please enter estimated cost A as a Number !").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
	     alert("Please enter estimated cost A as a Number!");
	      return false;
		}
	}
	if (document.getElementById('estimated_cost_b')) {
			 var estimated_cost_b = $("#estimated_cost_b").val();
			 if (estimated_cost_b == "" || isNaN(estimated_cost_b)) {
			$('#error_div').html("Please enter estimated cost B as a Number!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
					alert("Please enter estimated cost B as a Number!");
					return false;
			 }
	}
	if (document.getElementById('estimated_cost_c')) {
	  var estimated_cost_c = $("#estimated_cost_c");
			if (estimated_cost_c.val() == "" || isNaN(estimated_cost_c.val())) {
			  $('#error_div').html("Please enter estimated cost c!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			  alert("Please enter estimated cost c!");
			  return false;
			}
	}
	if($("#total_price").val() =='' || $("#total_price").val() == 'NaN'){
		$('#error_div').html("Please enter the estimate cost as a number!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
	  alert("Please enter the estimate cost as a number");
	  return false;
	}
	var repairImei = $('#imei_id').val();
	    var newimei1 = $('#imei1').val() ;
	    total = repairImei+newimei1;
	    //alert(total);
	    $('#imemivalue').val(total);
			
	<?php if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){?>
	if ($('#MobileRepairStatus').val() == 2) {
	  if ($('#checkbox_status').is(':checked')) {
	    validateAgree();
	    if (parseInt($('#formValid').val()) == 1 || $('#formValid').val() == '1') {
		    return true;
	    }else{
		    return false;
	    }
	  }
	}
	<?php } ?>	
	return true;
}
	
	function validateAgree(){
		$( "#submit-confirm" ).dialog({
			resizable: false,
			height:140,
			modal: true,
			buttons: {
			  "Agree": function() {
				$('#formValid').val('1');
				$('#MobileRepairEditForm').submit();
			  },
			  Cancel: function() {
				//alert('Cancel');
				$(this).dialog("close");
			  }
			}
		});
	}
	
	$('#other').click(function(){
		if ($(this).is(":checked")) {
			$('#mobile-condition-remark').css("display","block");
		} else {
			$('#mobile-condition-remark').css("display","none");
		}
 	});
	
	$( document ).ready(function() {
		if ($('#other').is(":checked")) {
			$('#mobile-condition-remark').css("display","block");
		} else {
			$('#mobile-condition-remark').css("display","none");
		}
	});
	
	function isValidEmailAddress(emailAddress) {
		var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
		return pattern.test(emailAddress);
	}
	 $('#imei_id').keyup(function(event){
		if ($('#imei_id').val().length == 14  && ((event.keyCode >= 48 && event.keyCode <= 57 ) ||
		( event.keyCode >= 96 && event.keyCode <= 105))) {
			var i;
			var singleNum;
			var finalStr = 0;
			var total = 0;
			var numArr = $('#imei_id').val().split('');
			
			for (i = 0; i < $('#imei_id').val().length; i++) {
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
			var newNumb = $('#imei_id').val() + Dnum;
			var MobilerepairImei = $('#imei_id').val();
			var newNumb = $('#imei_id').val() + Dnum;
			$('#imei1').val(Dnum);
			
	    }
		
		
	});
</script>
<script>
  $('#checkbox_status').click(function(){
	//alert($("#MobileRepairStatus").val());
    var ischecked= $(this).is(':checked');
	    if (!ischecked) {
	      $("#MobileRepairStatus").prop('disabled', true);
	      $("#hidden_status").prop('disabled', false);
	      $("#hiddenStatus").prop('disabled', false);//being used in controller for check if checkbox is enabled or not
	    } else if (ischecked) {
	      $("#MobileRepairStatus").prop('disabled', false);
	      $("#hidden_status").prop('disabled', true);
	      $("#hiddenStatus").prop('disabled', true);
	    }
  })
  
  $(document).ready(function(){
	$('#error_for_alert').hide();
	  $('#payment_div').hide();
});
  $(document).on('click', '#s_make_payment', function() {
	$('#payment_div').show();
	$('#repair_form').hide();
  });
</script>