<head>
  <?php echo $this->Html->script('smoothness-jquery-ui.min.css');
  $inputData = '';
  $conditionRemarks = '';
    if(!empty($this->request->data)){
		$inputData = $this->request->data;
		//pr($inputData);
		if(array_key_exists('mobile_condition',$inputData['MobilePurchase'])){
				if(is_array($inputData['MobilePurchase']['mobile_condition']) && in_array(1000,$inputData['MobilePurchase']['mobile_condition'])){
				  $conditionRemarks = $inputData['MobilePurchase']['mobile_condition_remark'];
				}elseif(!is_array($inputData['MobilePurchase']['mobile_condition'])){
					$mbCondition = explode('|',$inputData['MobilePurchase']['mobile_condition']);
					if(in_array(1000,$mbCondition)){
						$conditionRemarks = $inputData['MobilePurchase']['mobile_condition_remark'];
					}
				}
		}
		if(!empty($this->request->query['customerId'])){
			$customerdetail['0']['id'] = $inputData['MobilePurchase']['serial_number'];
			$customerdetail['0']['fname'] = $inputData['MobilePurchase']['customer_fname'];
			$customerdetail['0']['lname'] = $inputData['MobilePurchase']['customer_lname'];
			$customerdetail['0']['mobile'] = $inputData['MobilePurchase']['customer_contact'];
			$customerdetail['0']['email'] = $inputData['MobilePurchase']['customer_email'];
			$customerdetail['0']['address_1'] = $inputData['MobilePurchase']['customer_address_1'];
			$customerdetail['0']['address_2'] = $inputData['MobilePurchase']['customer_address_2'];
			$customerdetail['0']['city'] = $inputData['MobilePurchase']['city'];
			$customerdetail['0']['state'] = $inputData['MobilePurchase']['state'];
			$customerdetail['0']['zip'] = $inputData['MobilePurchase']['zip'];
			  
		}
		
    }
  ?>
</head>
<style>
 .ui-draggable {
  width: 500px !important;
 }
 .ui-dialog .ui-dialog-content {
  height: auto !important;
 }
 /*.ui-dialog-titlebar-close {
  visibility: hidden;
}*/
</style>
<div id="dialog-confirm" title="Mobile Terms" style="width: 500px !important;">
 <?php echo $grades_description ;?>
</div>
 <div class="mobilePurchases form">
<?php
	//$customer_identification = $this->request['data']['customer_identification'];
	$chosenBrand = $chosenModel = $chosenGrade = $chosenType = $chosenNetwork = $costPrice = $identificationOthers = $customer_identification = $imei = $imei1 = '';
	if(!empty($this->request->data)){
	  //pr($this->request);die;
	  if(strlen($this->request['data']['MobilePurchase']['imei']) > 13){
		$rawImei = $this->request['data']['MobilePurchase']['imei'];
		$imei = substr_replace($rawImei,'',14);
		$imei1 = substr($rawImei,-1);
	  }
	  $chosenBrand = $this->request['data']['MobilePurchase']['brand_id'];
	  $chosenModel = $this->request['data']['MobilePurchase']['mobile_model_id'];
	  $chosenGrade = $this->request['data']['MobilePurchase']['grade'];
	  $chosenType = $this->request['data']['MobilePurchase']['type'];
	  $chosenNetwork = $this->request['data']['MobilePurchase']['network_id'];
	  $costPrice = $this->request['data']['MobilePurchase']['cost_price'];
	  if(array_key_exists('customer_identification_others',$this->request->data['MobilePurchase'])){
		  $identificationOthers = $this->request['data']['MobilePurchase']['customer_identification_others'];
		  $customer_identification = "Others";
	  }elseif(array_key_exists('customer_identification',$this->request->data)){
		  $customer_identification = $this->request['data']['customer_identification'];
	  }
	}
	
	$url = $this->Url->build(array('controller' => 'customers', 'action' => 'get_address'));
	$customerData = $this->Url->build(array('controller' => 'retail_customers', 'action' => 'get_customer_ajax'));
	 echo "<div id='remote'>";
	echo "<input name='cust_email' class='typeahead' id='cust_email' placeholder='check existing customer email' style='width:250px;padding-right:10px;'/>";echo "&nbsp;&nbsp;<a href='#' id='check_existing' rel = '$customerData'>Check Existing</a>";
    echo "</div>";
	
	echo $this->Form->create($new_entity, array('enctype' => 'multipart/form-data','id' => 'MobilePurchaseAddForm'));
?><fieldset>
		<legend><?php echo __('Add Mobile Purchase'); ?></legend>
                <div id="error_div" tabindex='1'></div>
	<?php
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		echo $this->Form->input('kiosk_id',array('id' => 'MobilePurchaseKioskId','name' => 'MobilePurchase[kiosk_id]','type'=>'hidden','value'=>$kiosk_id));
		//echo $this->Form->input('purchase_number', array('type'=>'text'));
		echo "<table>";
		//customer details
        //pr($this->request->query);die;
		if(!empty($this->request->query['customerId'])){
		  echo "<tr>";
		  echo "<td colspan='3'>";
		  echo ('<h4>Customer Details</h4><hr/>');
		  echo "</td>";
		  echo "</tr>";
		  echo "<tr>";
		  echo "<td>".$this->Form->input('customer_fname', array('id' => 'MobilePurchaseCustomerFname','name' => 'MobilePurchase[customer_fname]','label' => 'First Name' , 'value' => $customerdetail['0']['fname']))."</td>";
		  echo "<td>".$this->Form->input('customer_lname', array('id' => 'MobilePurchaseCustomerLname','name' => 'MobilePurchase[customer_lname]','label' => 'Last Name', 'value' => $customerdetail['0']['lname']))."</td>";
		  echo "<td>".$this->Form->input('MobilePurchase.date_of_birth', array('id' => 'MobilePurchaseDateOfBirthDay','name' => 'MobilePurchase[date_of_birth]','label' => 'Date of Birth'
																  , 'dateFormat' => 'DMY'
																  , 'minYear' => date('Y') - 110
																  , 'maxYear' => date('Y') - 18
																 ))."</td>";
		  echo "</tr>";
		  echo "<tr>";
		  echo "<td>".$this->Form->input('customer_email',array('id' => 'MobilePurchaseCustomerEmail', 'name' => 'MobilePurchase[customer_email]','value' => $customerdetail['0']['email']))."</td>";
		  echo "<td>".$this->Form->input('customer_identification', array('id' => 'MobilePurchaseCustomerIdentification', 'name' => 'MobilePurchase[customer_identification]','options' => $identificationOptions,'default'=>'Others', 'onChange' => 'showText();','value'=>$customer_identification))."</td>";
		  echo "<td>".$this->Form->input('null',array('type'=>'text','name'=>'MobilePurchase[customer_identification_others]','label'=>'Please mention if Others','id'=>'MobilePurchaseOthers','value'=>$identificationOthers))."</td>";
		  echo "</tr>";
		  echo "<tr>";
		  echo "<td>".$this->Form->input('serial_number',array('id' => 'MobilePurchaseSerialNumber','name' => 'MobilePurchase[serial_number]','label' => 'Customer ID Number','value' => $customerdetail['0']['id']))."</td>";
		  echo "<td>".$this->Form->input('customer_contact',array('id' => 'MobilePurchaseCustomerContact','name' => 'MobilePurchase[customer_contact]','maxLength'=>11,'value' => $customerdetail['0']['mobile']))."</td>";
		  
		  echo "<td>";
				  echo $this->Form->input('MobilePurchase.image',array('id' => 'MobilePurchaseImage','name' => 'data[MobilePurchase][image]','type' => 'file'));
				  
				  echo $this->Form->input('MobilePurchase.path', array('id' => 'MobilePurchasePath', 'name' => 'MobilePurchase[path]','type' => 'hidden'));
				  echo $this->Form->input('MobilePurchase.image_dir', array('id' => 'MobilePurchaseImageDir', 'name' => 'MobilePurchase[image_dir]','type' => 'hidden'));
		  echo "</td>";
		  
		  echo "</tr>";
		  echo "</tr>";
		  echo "<tr>";
		  echo "<td>";
			  echo "<table>";
				  echo "<tr>";
					  echo "<td>";
					  echo $this->Form->input('zip',array('id' => 'MobilePurchaseZip', 'name' => 'MobilePurchase[zip]','placeholder' => 'Postcode', 'label'=>false, 'rel' => $url,'size'=>'10px','value' => $customerdetail['0']['zip']));
					  echo "</td>";
					  echo "<td>";
					  echo "<button type='button' id='find_address' class='btn' style='margin-top: 6px;margin-left: -8px;width: 112px;height: 22px;'>Find my address</button>";
					  echo "</td>";
				  echo "</tr>";
			  echo "</table>";
		  echo "</td>";;
		  echo "<td>".$this->Form->input('customer_address_1', array('id' => 'MobilePurchaseCustomerAddress1', 'name' => 'MobilePurchase[customer_address_1]','placeholder' => 'property name/no. and street name','value' => $customerdetail['0']['address_1']));
		  ?>
		  <select name='street_address' id='street_address'><option>--postcode--</option></select>
		  <span id='address_missing'><br/><a href='#-1'>Address is not on the list</a></span>
		  </td>
	  <?php echo "<td>".$this->Form->input('customer_address_2', array('id' => 'MobilePurchaseCustomerAddress2', 'name' => 'MobilePurchase[customer_address_2]','placeholder' => "further address details (optional)",'value' => $customerdetail['0']['address_2']))."</td>";
		  echo "</tr>";
		  echo "<tr>";
		  echo "<td>".$this->Form->input('city',array('id' => 'MobilePurchaseCity', 'name' => 'MobilePurchase[city]','label' => 'Town/City','placeholder' => "name of town or city",'value' => $customerdetail['0']['city']))."</td>";
		  echo "<td>".$this->Form->input('state', array('id' => 'MobilePurchaseState', 'name' => 'MobilePurchase[state]','label'=>'County', 'placeholder' => "name of county (optional)",'value' => $customerdetail['0']['state']))."</td>";
		  echo "<td>".$this->Form->input('country',array('id' => 'MobilePurchaseCountry', 'name' => 'MobilePurchase[country]','options'=>$countryOptions))."</td>";
		  echo "</tr>";
		  echo "<tr>";
		}else{
		  echo "<tr>";
			  echo "<td colspan='3'>";
			  echo ('<h4>Customer Details</h4><hr/>');
			  echo "</td>";
			  echo "</tr>";
			  echo "<tr>";
			  echo "<td>".$this->Form->input('MobilePurchase.customer_fname', array('id' => 'MobilePurchaseCustomerFname', 'name' => 'MobilePurchase[customer_fname]','label' => 'First Name'))."</td>";
			  echo "<td>".$this->Form->input('MobilePurchase.customer_lname', array('id' => 'MobilePurchaseCustomerLname', 'name' => 'MobilePurchase[customer_lname]','label' => 'Last Name'))."</td>";
			  echo "<td>".$this->Form->input('MobilePurchase.date_of_birth', array('label' => 'Date of Birth'
								  , 'dateFormat' => 'DMY'
								  , 'minYear' => date('Y') - 110
								  , 'maxYear' => date('Y') - 18))."</td>";
			  echo "</tr>";
			  echo "<tr>";
			  echo "<td>".$this->Form->input('MobilePurchase.customer_email',array('id' => 'MobilePurchaseCustomerEmail', 'name' => 'MobilePurchase[customer_email]'))."</td>";
			  echo "<td>".$this->Form->input('MobilePurchase.customer_identification', array('id' => 'MobilePurchaseCustomerIdentification', 'name' => 'MobilePurchase[customer_identification]','options' => $identificationOptions,'default'=>'Others', 'onChange' => 'showText();','value'=>$customer_identification))."</td>";
			  echo "<td>".$this->Form->input('MobilePurchase.null',array('type'=>'text','name'=>'MobilePurchase[customer_identification_others]','label'=>'Please mention if Others','id'=>'MobilePurchaseOthers','value'=>$identificationOthers))."</td>";
			  echo "</tr>";
			  echo "<tr>";
			  echo "<td>".$this->Form->input('MobilePurchase.serial_number',array('id' => 'MobilePurchaseSerialNumber', 'name' => 'MobilePurchase[serial_number]','label' => 'Customer ID Number'))."</td>";
			  echo "<td>".$this->Form->input('MobilePurchase.customer_contact',array('id' => 'MobilePurchaseCustomerContact', 'name' => 'MobilePurchase[customer_contact]','maxLength'=>11))."</td>";
			  
			  echo "<td>";
				echo "**Only images are allowed to upload.";
					  echo $this->Form->input('MobilePurchase.image',array( 'id' => 'MobilePurchaseImage', 'name' => 'MobilePurchase[image]','type' => 'file'));
					  echo $this->Form->input('MobilePurchase.path', array('id' => 'MobilePurchasePath', 'name' => 'MobilePurchase[path]','type' => 'hidden'));
					  echo $this->Form->input('MobilePurchase.image_dir', array('id' => 'MobilePurchaseImageDir', 'name' => 'MobilePurchase[image_dir]','type' => 'hidden'));
			  echo "</td>";
			  
			  echo "</tr>";
			  echo "</tr>";
			  echo "<tr>";
			  echo "<td>";
				  echo "<table>";
					  echo "<tr>";
						  echo "<td>";
						  echo $this->Form->input('MobilePurchase.zip',array('id' => 'MobilePurchaseZip', 'name' => 'MobilePurchase[zip]','placeholder' => 'Postcode', 'label'=>false, 'rel' => $url,'size'=>'10px','style'=>'width: 120px;'));
						  echo "</td>";
						  echo "<td>";
						  echo "<button type='button' id='find_address' class='btn' style='margin-top: 6px;margin-left: -8px;width: 130px;height: 29px;'>Find my address</button>";
						  echo "</td>";
					  echo "</tr>";
				  echo "</table>";
			  echo "</td>";;
			  echo "<td>".$this->Form->input('MobilePurchase.customer_address_1', array('id' => 'MobilePurchaseCustomerAddress1', 'name' => 'MobilePurchase[customer_address_1]','placeholder' => 'property name/no. and street name'));
			  ?>
			  <select name='street_address' id='street_address'><option>--postcode--</option></select>
			  <span id='address_missing'><br/><a href='#-1'>Address is not on the list</a></span>
			  </td>
		  <?php echo "<td>".$this->Form->input('MobilePurchase.customer_address_2', array('id' => 'MobilePurchaseCustomerAddress2', 'name' => 'MobilePurchase[customer_address_2]','placeholder' => "further address details (optional)"))."</td>";
			  echo "</tr>";
			  echo "<tr>";
			  echo "<td>".$this->Form->input('MobilePurchase.city',array('id' => 'MobilePurchaseCity', 'name' => 'MobilePurchase[city]','label' => 'Town/City','placeholder' => "name of town or city"))."</td>";
			  echo "<td>".$this->Form->input('MobilePurchase.state', array('id' => 'MobilePurchaseState', 'name' => 'MobilePurchase[state]','label'=>'County', 'placeholder' => "name of county (optional)"))."</td>";
			  echo "<td>".$this->Form->input('MobilePurchase.country',array('id' => 'MobilePurchaseCountry', 'name' => 'MobilePurchase[country]','options'=>$countryOptions))."</td>";
			  echo "</tr>";
			  echo "<tr>";
		}
		echo "<td colspan='3'>";
		echo ('<h4>Mobile Details</h4><hr/>');
		echo "</td>";
		echo "</tr>";
		echo "<tr>";
		$urlBrand = $this->Url->build(array('controller' => 'MobilePurchases', 'action' => 'get_models'));//get_models
		$urlMobileModel = $this->Url->build(array('controller' => 'MobilePurchases', 'action' => 'get_price'));
		$webRoot = $this->request->webroot."MobilePurchases/index";
		echo "<td colspan='3'>
			<table>
				<tr>
					<td>".$this->Form->input('brand_id',array('id' => 'MobilePurchaseBrandId','name' => 'MobilePurchase[brand_id]','rel'=>$urlBrand,'default'=>$chosenBrand))."</td>";
                    //pr($mobileModels);die;
					echo "<td>".$this->Form->input('mobile_model_id',array('id' => 'MobilePurchaseMobileModelId','name' => 'MobilePurchase[mobile_model_id]','options'=>$mobileModels,'type' => 'select','empty'=>'Select Model','default'=>$chosenModel))."</td>";
					echo "<td><span class='input select'>".$this->Form->input('grade',array('id' => 'MobilePurchaseGrade','name' => 'MobilePurchase[grade]',
                                                                                                    'options' => $gradeType,
                                                                                                    'empty'=>'Choose Grade',
                                                                                                    'rel'=>$urlMobileModel,
                                                                                                    'default'=>$chosenGrade,
                                                                                                    'div' => false,
                                                                                                    )
												   )."</span>&nbsp;&nbsp;<a id='openwindow' style=\"cursor: pointer;\"><i>Grade Info</i>.</a></td>";
					?>
					<?php
					echo "<td>".$this->Form->input('type',array('id' => 'MobilePurchaseType','name' => 'MobilePurchase[type]','options'=>array('1'=>'locked','0'=>'unlocked'),'empty'=>'-Choose-','default'=>$chosenType))."</td> 
					<td><span id='networkId'>".$this->Form->input('network_id',array('id' => 'MobilePurchaseNetworkId','name' => 'MobilePurchase[network_id]','empty'=>'Choose','default'=>$chosenNetwork))."</span></td>
				</tr>
			</table>
		</td>";
		?>
		
     <?php
		echo "</tr>";
		echo "<tr>";
		$allowedDiscount = array();
		$fieldCP = $this->Form->input('cost_price',array('id' => 'MobilePurchaseCostPrice','name' => 'MobilePurchase[cost_price]','type'=>'text','style'=>"width: 75px;",'readonly'=>'readonly','value'=>$costPrice));
		$fieldMaxTopUp = $this->Form->input('maximum_topup',array('id' => 'MobilePurchaseMaximumTopup','name' => 'MobilePurchase[maximum_topup]',
																  'style' => 'display:none;',
																  'options' => $discountOptions,
																  'label' => 'Max Topup',
																  'div' => 'false'));
		$fieldIMEI = $this->Form->input('imei',array('id' => 'MobilePurchaseImei','name' => 'c',
													 'label' => 'IMEI',
													 'name' => 'MobilePurchase[imei]',
													 'maxlength'=>14,
													 'style'=>"width: 116px;",
													 'value' => $imei,
													 'autocomplete' => 'off'));
		$fieldIMEI1 = $this->Form->input('imei1',array('id' => 'imei1','name' => 'MobilePurchase[imei1]','type' => 'text','label' => false, 'id' =>'imei1','readonly'=>'readonly','style'=>"width: 30px;margin-left: -18px;margin-top: 17px", 'value' => $imei1));
		$fieldColor = $this->Form->input('MobilePurchase.color',array('id' => 'MobilePurchaseColor','name' => 'MobilePurchase[color]','options'=>$colorOptions,'empty'=>'Choose color'));
		$fieldUpdatedPrice = $this->Form->input('topedup_price',array('id' => 'MobilePurchaseTopedupPrice','name' => 'MobilePurchase[topedup_price]','style' => 'width:70px;', 'label' => 'Updated Price', 'type' => 'text','onblur'=>'updateprice();' ));
		$heightestCostPrice = "<a id='heighestCP' href='#' title='Heighest Cost Price:100:00' alt='Heighest Cost Price:100.00'>##</a>";
		echo "<td colspan='3'>
		  <table>
			<tr>
			  <td>$fieldCP</td>
			  <td>$fieldMaxTopUp{$heightestCostPrice}</td>
			  <td>$fieldUpdatedPrice</td>
			  <td>$fieldIMEI</td>
			  <td>$fieldIMEI1</td>
			  <td>$fieldColor</td>
			</tr>
		  </table>
		</td>";
		echo "</tr>";
        echo "</table>";
                //******************this block is for showing mobile conditions and function conditions***
		if(count($mobileConditions)){
		  $mobileConditions['1000'] = 'Other';
		  $chunks = array_chunk($mobileConditions,4,true);
		  if(count($chunks)){
			echo "<table id = 'mobile_condition_table'>";
			echo "<tr>";
				echo "<td colspan='8'>";
					echo ("<h4>Phone's Condition</h4><hr/>");
				echo "</td>";
			echo "</tr>";
			echo "<tr>";
			foreach($chunks as $c => $chunk){
			  echo "<td>";
				  foreach($chunk as $ch => $condition){
					  if(!empty($inputData) && array_key_exists('mobile_condition',$inputData['MobilePurchase'])){
						  if(is_array($inputData['MobilePurchase']['mobile_condition'])){
							  $mbCondition = $inputData['MobilePurchase']['mobile_condition'];
						  }else{
							  $mbCondition = explode('|',$inputData['MobilePurchase']['mobile_condition']);
						  }
						  if(array_key_exists('mobile_condition',$inputData['MobilePurchase']) && in_array($ch,$mbCondition)){
							  $checked = "checked";	
						  }else{
							  $checked = '';
						  }
					  }else{
						  $checked = '';
					  }
					  echo $this->Form->input($condition, array('type' => 'checkbox',
							'name'=>'MobilePurchase[mobile_condition][]',
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
		  echo $this->Form->input('mobile_condition_remark',array('id' => 'MobilePurchaseMobileConditionRemark','name' => 'MobilePurchase[mobile_condition_remark]','label' => false, 'type' => 'text','placeholder' => 'Mobile Condition Remarks(Fill in case of other)','style' => 'display: none;', 'value' => $conditionRemarks));
		}
		if(count($functionConditions)){
			$functionChunks = array_chunk($functionConditions,2,true);
			if(count($functionChunks)){
				echo "<table id='function_condition_table'>";
					echo "<tr>";
						echo "<td colspan = '4'>";
							echo ("<h4>Phone's Functions Test<sup>**</sup> (For internal use Only)</h4><hr/>");
						echo "</td>";
					echo "</tr>";
					echo "<tr>";
						foreach($functionChunks as $f => $Fchunk){
								echo "<td>";
									foreach($Fchunk as $fch => $functionCondition){
										if(!empty($inputData) && array_key_exists('function_condition',$inputData['MobilePurchase'])){
                                                                                    if(is_array($inputData['MobilePurchase']['function_condition'])){
                                                                                        $funcCondition = $inputData['MobilePurchase']['function_condition'];
                                                                                    }else{
                                                                                        $funcCondition = explode('|',$inputData['MobilePurchase']['function_condition']);
                                                                                    }
											if(array_key_exists('function_condition',$inputData['MobilePurchase']) && in_array($fch,$funcCondition)){
												$checked = "checked";	
											}else{
												$checked = '';
											}
										}else{
											$checked = '';
										}
										echo $this->Form->input($functionCondition, array('type' => 'checkbox',
										'name'=>'MobilePurchase[function_condition][]',
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
                //*******till here **mobile conditions and function conditions**********
                echo "<table>";
		echo "<tr>";
		echo "<table>";
		echo "<td>".$this->Form->input('MobilePurchase.description',array('id' => 'MobilePurchaseDescription','name' => 'MobilePurchase[description]'))."</td>";
		echo "<td>".$this->Form->input('MobilePurchase.brief_history', array('id' => 'MobilePurchaseBriefHistory','name' => 'MobilePurchase[brief_history]','label' => 'Apple ID / Password'))."</td>";
		echo "</tr>";
		echo "</table>";
		echo "</tr>";
		echo "<tr>";
		#echo "<td colspan='3'>".$this->Form->input('status', array('options' => $purchasingOptions))."</td>";
		echo "</tr>";
		echo "</table>";
	?>
	</fieldset>
	
<?php
$options = array('name'=>'submit','value'=>'Submit','onClick'=>'submitForm();');
echo "<input type='hidden' id = 'hiddenHeighestCP' name = 'hiddenHeighestCP' value=''/>";
echo $this->Form->Submit("Submit",$options);
echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('Mobile Stock/Sell'), array('action' => 'index'),array('style'=>"width: 120px;")); ?></li>
		<li><?php # echo $this->Html->link(__('List Kiosks'), array('controller' => 'kiosks', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php # echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('View Mobile Sale'), array('controller' => 'mobile_re_sales', 'action' => 'index'),array('style'=>"width: 120px;")); ?></li>
		<li><?php echo $this->Html->link(__('Buy Mobile Phones'), array('controller' => 'mobile_purchases', 'action' => 'add'),array('style'=>"width: 120px;")); ?></li>
		<li><?php #echo $this->Html->link(__('Mobile Stock/Sell'), array('controller' => 'mobile_purchases', 'action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('Global Mobile Search'), array('controller' => 'mobile_purchases', 'action' => 'global_search'),array('style'=>"width: 120px;")); ?></li>
	</ul>
</div>
<script>
  $('document').ready(function(){
	  var valu = $('#MobilePurchaseMobileModelId').val();
	  if(valu == ""){
		  $('#MobilePurchaseBrandId').val("<?php echo $brandID; ?>");
		  $('#MobilePurchaseCostPrice').val("");
		  $('#MobilePurchaseGrade').val("");
		  $('#MobilePurchaseType').val("");
		  $('#MobilePurchaseCustomerIdentification').val("Others");
		  $("#networkId").hide();
	  }
	  showText();
  });
	
  $("#MobilePurchaseBrandId").change(function(){
	$('#MobilePurchaseCostPrice').val("");
	$('#MobilePurchaseGrade').val("");
	$('#MobilePurchaseType').val("");
	var selectedValue = $(this).val();
	var targetUrl = $(this).attr('rel') + '?id=' + selectedValue;
	$('#MobilePurchaseGrade').val("");
	$('#heighestCP').tooltip("option", "content","Heighest Cost Price:Undefined");
	$('#MobilePurchaseMobileModelId').val("");
	// alert(targetUrl);
	$.blockUI({ message: 'Just a moment...' });
	$.ajax({
		type: 'get',
		url: targetUrl,
		beforeSend: function(xhr) {
		  xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		},
		success: function(response) {
		  $.unblockUI();
		  if (response) {
			$('#MobilePurchaseMobileModelId').find('option').remove().end();
			$('#MobilePurchaseMobileModelId').append(response);//html(response.content);
		  }
		},
		error: function(e) {
		  $.unblockUI();
		  alert("An error occurred: " + e.responseText.message);
		  console.log(e);
		}
	});
  });
	
  $('#MobilePurchaseMobileModelId').change(function(){
	$('#MobilePurchaseGrade').val("");
	$('#MobilePurchaseType').val("");
	$('#MobilePurchaseCostPrice').val("");
	$('#heighestCP').tooltip("option", "content","Heighest Cost Price:Undefined");
  });
	
  $('#MobilePurchaseGrade').change(function(){
	$('#MobilePurchaseType').val("");
	$('#MobilePurchaseCostPrice').val("");
	$('#heighestCP').tooltip("option", "content","Heighest Cost Price:Undefined");
  });
	
  //----------------------rajju--------
  $('#openwindow').each(function() {
	  var $link = $(this);
	  var $dialog = $('#dialog-confirm')
		  .load($link.attr('href'))
		  .dialog({
			  autoOpen: false,
			  title: $link.attr('title'),
			  width: 500,
			  height: 300,
		  });

	  $link.click(function() {
		  $dialog.dialog('open');
		  return false;
	  });
  });
  //----------------------/rajju--------
	
  $('#MobilePurchaseType').change(function(){
	var selectedValue = $('#MobilePurchaseGrade').val();
	var type = $(this).val();
	var brandId = $('#MobilePurchaseBrandId').val();
	var mobileModelId = $('#MobilePurchaseMobileModelId').val();
	var targetUrl =$('#MobilePurchaseGrade').attr('rel') + '?model=' + mobileModelId + '&brand=' + brandId + '&grade=' + selectedValue + '&type=' + type;
	$('#MobilePurchaseCostPrice').val("");
	if (parseInt(mobileModelId) == 0 || mobileModelId == "") {
	  $('#MobilePurchaseGrade').val("");
	  alert("Either there is no model for selected brand or you have not seleted any brand");
	  return;
	}
	  
	$.blockUI({ message: 'Just a moment...' });
	
	$.ajax({
	  type: 'get',
	  url: targetUrl,
	  beforeSend: function(xhr) {
		xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	  },
	  success: function(response) {
		$.unblockUI();
		var obj = jQuery.parseJSON(response);
		if (obj.err == 0) {
		  if(type == 1){
			$("#networkId").show();
		  }else{
			$("#networkId").hide();
		  }
		  $('#MobilePurchaseCostPrice').val(obj.cost_price);
		  $('#MobilePurchaseTopedupPrice').val(obj.cost_price);
		  //return false;
		  var toAppend = '';
		  var lastIdx = 0;
		  $.each(obj.discountOptions, function( index, value ) {
			toAppend += '<option value="'+index+'">'+value+'</option>';
			lastIdx = parseInt(index);
		  });
		  var heighestCP = parseFloat(obj.cost_price);
		  if (lastIdx > 0) {
            heighestCP += (heighestCP * lastIdx) / 100; 
          }
		  heighestCP = heighestCP.toFixed(2);
        
		  //$('#heighestCP').attr("alt", "Heighest Cost Price:"+heighestCP);
		  $('#hiddenHeighestCP').val(heighestCP);
		  $('#heighestCP').tooltip("option", "content","Heighest Cost Price:"+heighestCP);

		  $('#MobilePurchaseMaximumTopup').empty();
		  $('#MobilePurchaseMaximumTopup').append(toAppend);
		}else if(obj.err == 1){
		  alert("No detail found for this combination!!");
		}
	  },
	  error: function(e) {
		$.unblockUI();
		alert("An error occurred: " + e.responseText.message);
		console.log(e);
	  }
	});
  });
</script>

<script>
	$('#MobilePurchaseMaximumTopup').change(function(){
	  var topup = $(this).val();
	  var purchaseCost = $('#MobilePurchaseCostPrice').val();
	  var updatedPrice = parseFloat(topup * purchaseCost / 100) + parseFloat(purchaseCost);
	  updatedPrice = updatedPrice.toFixed(2);
	  if(purchaseCost != 0 || purchaseCost != ""){
		$('#MobilePurchaseTopedupPrice').val(updatedPrice);
	  }
	});
	
	function updateprice(){
	  var topup = $('#MobilePurchaseMaximumTopup').val();
	  var purchaseCost = $('#MobilePurchaseCostPrice').val();
	  var TopedupPrice = $('#MobilePurchaseTopedupPrice').val();
	  var updatedPrice = parseFloat(topup * purchaseCost / 100) + parseFloat(purchaseCost);
	  updatedPrice = updatedPrice.toFixed(2);
	  /*if(updatedPrice < parseFloat(TopedupPrice)){
		alert("Please enter updated price between 0 to " +updatedPrice);
	  }*/
	  updatedPrice = parseFloat($('#hiddenHeighestCP').val());
	  var topUpCost = parseFloat($('#MobilePurchaseTopedupPrice').val());
	  if(topUpCost > updatedPrice){
		alert("Please enter updated price between 0 to " + updatedPrice);
		$('#MobilePurchaseTopedupPrice').val('');
		$( "#MobilePurchaseTopedupPrice" ).focus();
	  }
	}
</script>

<script>
  $("#MobilePurchaseAddForm").submit(function(){
	var topUpPurchasedPrice = $.trim($('#MobilePurchaseTopedupPrice').val());
	if ( topUpPurchasedPrice == "" || parseFloat(topUpPurchasedPrice) <= 0) {
	  $('#error_div').html("Please input valid Mobile Price!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
	  alert('Please input mobile price!');
	  return false;
    }
	
	var mobileType = $('#MobilePurchaseType').val();
	var networkId = $('#MobilePurchaseNetworkId').val();
	if(mobileType == 1 && networkId == ""){
	  $('#error_div').html("Please choose mobile network!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
	  alert("Please choose mobile network");
	  return false;
	}
				  
	if (document.getElementById("function_condition_table")) {
	  var mobileCondChk = $('input[name="MobilePurchase[function_condition][]"]:checkbox:checked');
	  if (mobileCondChk.length == 0)  {
		$('#error_div').html("Please select phone's function!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert("Please select phone's function!");
		return false;
	  }
	}
				  
	if ($('#other').is(":checked")) {
	  if ($('#MobilePurchaseMobileConditionRemark').val() == '') {
		$('#error_div').html("Please input mobile condition remarks!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert('Please input mobile condition remarks!');
		return false;
	  }
	}
	
	if ($('#MobilePurchaseCustomerIdentification').val() == 'Others' ) {
	  if ($('#MobilePurchaseOthers').val() == '') {
		$('#error_div').html("Please input other Customer Identification!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert('Please input other Customer Identification!');
		return false;
	  }
	}
	
	if ($('#MobilePurchaseImei').val() == '') {
	  $('#error_div').html('Please input the mobile imei!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
	  alert("Please input the mobile imei!");
	  return false;
	}else if ($('#MobilePurchaseImei').val().length < 14) {
	  $('#error_div').html('Input imei should be of 14 characters!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
	  alert('Input imei should be of 14 characters!');
	  return false;
	}
	var topup = $('#MobilePurchaseMaximumTopup').val();
	var purchaseCost = $('#MobilePurchaseCostPrice').val();
	var TopedupPrice = $('#MobilePurchaseTopedupPrice').val();
	var updatedPrice = parseFloat(topup * purchaseCost / 100) + parseFloat(purchaseCost);
	updatedPrice = updatedPrice.toFixed(2);
	updatedPrice = parseFloat($('#hiddenHeighestCP').val());//new change
	
	if ($('#MobilePurchaseMaximumTopup').val() > 0) {
	  if ($('#MobilePurchaseTopedupPrice').val() == '') {
		$('#error_div').html("Please input mobile Update Price!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert('Please input mobile Update Price!');
		return false;
	  }else if (parseFloat(TopedupPrice) > updatedPrice) {
	    alert("Please enter updated price between 0 to " +updatedPrice);
		$('#error_div').html("Please enter updated price between 0 to " +updatedPrice+"!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
	    return false;
	  }
	}
	
	if (parseFloat(TopedupPrice) > parseFloat(updatedPrice)) {
	  alert("Please enter updated price between 0 to " +updatedPrice);
	  $('#error_div').html("Please enter updated price between 0 to " +updatedPrice+"!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
	  return false;
	 }
  });
</script>

<script>
  function showText(a){
	var custIdent = document.getElementById('MobilePurchaseCustomerIdentification').value;
	if(custIdent == 'Others'){			   
	  document.getElementById("MobilePurchaseOthers").disabled = '';
	}else{
	  document.getElementById("MobilePurchaseOthers").disabled = 'false';
	}
  }
	
  //to be done
  function submitForm(){
	var b=document.getElementById('MobilePurchaseCustomerIdentification').value;
	if(b == 'Others'){	
	  document.getElementById("MobilePurchaseCustomerIdentification").disabled = 'false';
	  document.getElementById('MobilePurchaseAddForm').submit;
	}
  }
</script>

<script>
  $(function() {
	$('#address_missing').click(function(){
	  $('#street_address').hide();
	  $('#MobilePurchaseCustomerAddress1').show("");
	  $('#MobilePurchaseCustomerAddress2').val("");
	  $('#MobilePurchaseCustomerAddress2').val("");
	  $('#MobilePurchaseCity').val("");
	  $('#MobilePurchaseState').val("");		
	  $(this).hide();
	});
	
	$( "#street_address" ).select(function() {
	  alert($( "#street_address" ).val());
	  $('#MobilePurchaseCustomerAddress1').val($( "#street_address" ).val());
	  $('#MobilePurchaseCustomerAddress2').show();
	  $('#address_missing').hide();
	  $(this).hide();
	});
	
	$( "#street_address" ).change(function() {
	  $('#MobilePurchaseCustomerAddress1').val($( "#street_address" ).val());
	  $('#MobilePurchaseCustomerAddress1').show();
	  $('#address_missing').hide();
	  $(this).hide();
	});
	
	$( "#find_address" ).click(function() {
	  var zipCode = $("#MobilePurchaseZip").val();
	  var zipCode = $("#MobilePurchaseZip").val();
	  if (zipCode  == "") {
        alert("Please Input Postcode");
			return false;
      }
	  var targeturl = $("#MobilePurchaseZip").attr('rel') + '?zip=' + escape(zipCode);		
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
			  $('#MobilePurchaseCustomerAddress1').hide("");
			  $('#address_missing').show();
			  var toAppend = '';
			  $('#street_address').find('option').remove().end();
			  $.each(obj.Street, function( index, value ) {
				  //alert( index + ": " + value );
				  toAppend += '<option value="'+value+'">'+value+'</option>';
			  });
			  $('#street_address').append(toAppend);
			  $('#MobilePurchaseCustomerAddress2').val(obj.Address2);
			  $('#MobilePurchaseCity').val(obj.Town);
			  $('#MobilePurchaseState').val(obj.County);
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
	
	$('#MobilePurchaseCustomerAddress1').show();
	$('#street_address').hide();
	$('#address_missing').hide();
	  
	$("#MobilePurchaseCustomerContact, #MobilePurchaseImei").keydown(function (event) {  
	  if (event.shiftKey == true) {event.preventDefault();}
	  if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		 (event.keyCode >= 96 && event.keyCode <= 105) ||
		 event.keyCode == 8 || event.keyCode == 9 ||
		 event.keyCode == 37 || event.keyCode == 39 ||
		 event.keyCode == 46 || event.keyCode == 183) {
		   ;
	  }else{
		event.preventDefault();
	  }
	});
	
	$("#MobilePurchaseTopedupPrice").keydown(function (event) {  
	  if (event.shiftKey == true) {event.preventDefault();}
	  if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		 (event.keyCode >= 96 && event.keyCode <= 105) ||
		 event.keyCode == 8 || event.keyCode == 9 ||
		 event.keyCode == 37 || event.keyCode == 39 ||
		 event.keyCode == 46 || event.keyCode == 183 || event.keyCode == 190 || event.keyCode == 110) {
		   ;
	  }else{
		event.preventDefault();
	  }
	});
	  
	$( "#MobilePurchaseImei" ).keyup(function() {
	  if ($('#MobilePurchaseImei').val().length < 14) {
		$('#imei1').val("");
	  }
	});
	
	$('#MobilePurchaseImei').keyup(function(event){
	  if ($('#MobilePurchaseImei').val().length == 14 && ((event.keyCode >= 48 && event.keyCode <= 57 ) ||
	  ( event.keyCode >= 96 && event.keyCode <= 105))) {
		var i;
		var singleNum;
		var finalStr = 0;
		var total = 0;
		var numArr = $('#MobilePurchaseImei').val().split('');
		
		for (i = 0; i < $('#MobilePurchaseImei').val().length; i++) {
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
		var newNumb = $('#MobilePurchaseImei').val() + Dnum;
		$('#imei1').val(Dnum);
	  }
	});
  });

  $('#other').click(function(){
	  if ($(this).is(":checked")) {
		$('#MobilePurchaseMobileConditionRemark').css("display","block");
	  } else {
		$('#MobilePurchaseMobileConditionRemark').css("display","none");
	  }
  });
  
  $( document ).ready(function() {
	if ($('#other').is(":checked")) {
	  $('#MobilePurchaseMobileConditionRemark').css("display","block");
	} else {
	  $('#MobilePurchaseMobileConditionRemark').css("display","none");
	}
  });
  $(document).ready(function(){
	$(function(){$('#heighestCP').tooltip();});
  });

</script>
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
				$("#MobilePurchaseCustomerFname").val(obj.fname);
				$("#MobilePurchaseCustomerLname").val(obj.lname);
				$("#MobilePurchaseCustomerContact").val(obj.mobile);
				$("#MobilePurchaseCustomerEmail").val(obj.email);
				$("#MobilePurchaseZip").val(obj.zip);
				$("#MobilePurchaseCustomerAddress1").val(obj.address_1);
				$("#MobilePurchaseCustomerAddress2").val(obj.address_2);
				$("#MobilePurchaseCity").val(obj.city);
				$("#MobilePurchaseState").val(obj.state);
				var country = obj.country;
				if (country != "") {
					if (country) {
                     // alert(obj.country);
					   $("#MobilePurchaseCountry").val(obj.country);
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
</script>