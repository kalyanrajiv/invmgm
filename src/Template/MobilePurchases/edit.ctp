<?php
	//pr($this->request->data);die;
    $imei = $this->request->data['imei'];
    $imei1 = substr($imei, -1);
    $imei2 = substr_replace($imei,'',-1);
    $moblileArr = array();
    //pr($this->request['data']);die;
    if(array_key_exists('mobile_condition',$this->request['data'])){
	  $mobileStr = $this->request['data']['mobile_condition'];
	  if(!is_array($mobileStr)){
		$moblileArr = explode('|',$mobileStr);
	  }else{
		$moblileArr = $mobileStr;
	  }
    }
    
    if(in_array('1000',$moblileArr)){
	  $conditionRemarks = $this->request['data']['mobile_condition_remark'];;
    }else{
	  $conditionRemarks = "";
    }
    
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
  $topupPercetage = 0;
  $topedUpPrice = $this->request->data['topedup_price'];
  if(!empty($topedUpPrice)){
	$topupDiff = $topedUpPrice - $this->request->data['cost_price'];
	if($this->request->data['cost_price'] > 0){
	  $topupPercetage = ($topupDiff / $this->request->data['cost_price']) * 100;  
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
 /*.ui-dialog-titlebar-close {
  visibility: hidden;
}*/
</style>
<div id="dialog-confirm" title="Mobile Terms" style="width: 500px !important;">
 <?php echo $grades_description ;?>
</div>
<div class="mobilePurchases form">
<?php
   // $identificationOthers = $customer_identification = '';
	$mobile_purchase_reference = $this->request['data']['mobile_purchase_reference'];
	$mobile_customer_identification = $this->request['data']['customer_identification'];
        $chosenGrade = $this->request['data']['grade'];
	$othersValue = '';
	$defaultValue = $mobile_customer_identification;
	if(!array_key_exists($mobile_customer_identification,$identificationOptions)){
		$othersValue = $mobile_customer_identification;
		$defaultValue = "Others";
	}
	$url = $this->Url->build(array('controller' => 'customers', 'action' => 'get_address'));
	$gradeUrl = $this->Url->build(array('controller' => 'mobile_purchases', 'action' => 'get_price'));
	$brandUrl = $this->Url->build(array('controller' => 'mobile_purchases', 'action' => 'get_models'));
	echo $this->Form->create($mobile_purchase_entity, array('enctype' => 'multipart/form-data','id' => 'MobilePurchaseEditForm'));
?>
	<fieldset>
		<legend><?php echo __('Edit Mobile Purchase'); ?></legend>
                <div id="error_div" tabindex='1'></div>
	<?php
		echo $this->Form->input('id',array('name' => 'MobilePurchase[id]','type' => 'hidden'));
		echo $this->Form->input('mobile_purchase_reference',array('type' => 'hidden','name' => 'MobilePurchase[mobile_purchase_reference]','value'=>$mobile_purchase_reference));
		//echo $this->Form->input('kiosk_id',array('disabled' => 'disabled'));
		
		echo "<table>";
		//customer details
		echo "<tr>";
		echo "<td colspan='3'>";
		echo ('<h4>Customer Details</h4><hr/>');
		echo "</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td>".$this->Form->input('customer_fname', array('id' => 'MobilePurchaseCustomerFname','name' => 'MobilePurchase[customer_fname]','label' => 'First Name'))."</td>";
		echo "<td>".$this->Form->input('customer_lname', array('id' => 'MobilePurchaseCustomerLname','name' => 'MobilePurchase[customer_lname]','label' => 'Last Name'))."</td>";
		echo "<td>".$this->Form->input('date_of_birth', array('name' => 'MobilePurchase[date_of_birth]','label' => 'Date of Birth'
							, 'dateFormat' => 'DMY'
							, 'minYear' => date('Y') - 110
							, 'maxYear' => date('Y') - 18))."</td>";
		echo "</tr>";
		
		echo "<tr>";
		echo "<td>".$this->Form->input('customer_email',array('id' => 'MobilePurchaseCustomerEmail','name' => 'MobilePurchase[customer_email]'))."</td>";
       // pr($identificationOptions);
       //pr($customer_identification);
       
		echo "<td>".$this->Form->input('customer_identification', array('options' => $identificationOptions,'id' => 'MobilePurchaseCustomerIdentification','default'=>'Others', 'onChange' => 'showText();', 'value'=> $defaultValue,'name' => 'MobilePurchase[customer_identification]','id' => "MobilePurchaseCustomerIdentification"))."</td>";
		echo "<td>".$this->Form->input('null',array('type'=>'text','name'=>'MobilePurchase[customer_identification]','label'=>'Please mention if Others','id'=>'MobilePurchaseOthers','value'=> $othersValue))."</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td>".$this->Form->input('serial_number',array('id' => 'MobilePurchaseSerialNumber','label' => 'Customer ID Number','name' => 'MobilePurchase[serial_number]'))."</td>";
		echo "<td>".$this->Form->input('customer_contact',array('id' => 'MobilePurchaseCustomerContact','maxLength'=>11,'name' => 'MobilePurchase[customer_contact]'))."</td>";
		echo "<td>";
				echo $this->Form->input('MobilePurchase.image_dir', array('type' => 'hidden','name' => 'MobilePurchase[image_dir]'));
				echo $this->Form->input('path', array('type' => 'hidden','name' => 'MobilePurchase[image_dir]'));
				$imageDir = WWW_ROOT."files".DS.'MobilePurchases'.DS.'image'.DS.$this->request['data']['id'].DS;
              //  pr($this->request['data']);
              $imageName1 = $this->request['data']['image'];
		 
                
				if(!empty($this->request['data']['image'])){
				  $imageName = $imageName1;  
				}else{
                    $imageName = '';
                    } 
				
			  $absoluteImagePath = $imageDir.$imageName;
				$imageURL = "/thumb_no-image.png";
				if(@readlink($absoluteImagePath) ||file_exists($absoluteImagePath) && !empty($imageName)){
					$imageURL = "/files/MobilePurchases/image/".$this->request['data']['id']."/$imageName";
				}
				echo $this->Html->link(
							  $this->Html->image($imageURL, array('fullBase' => true,'height' => '100px','width' => '100px')),
							  array('controller' => 'mobile_purchases','action' => 'index'),
							  array('escapeTitle' => false, 'title' => $this->request['data']['imei'])
							 );
				echo $this->Form->input('MobilePurchase.image.remove', array('type' => 'checkbox', 'label' => 'Remove existing file'));
				echo $this->Form->input('MobilePurchase.image',array('name' => 'MobilePurchase[image]','type' => 'file'));
		echo "</td>";
		echo "</tr>";
		
		echo "<tr>";
		echo "<td>";
			echo "<table>";
				echo "<tr>";
					echo "<td>";
					echo $this->Form->input('zip',array('id' => 'MobilePurchaseZip','placeholder' => 'Postcode','name' => 'MobilePurchase[zip]', 'label'=>false, 'rel' => $url,'size'=>'10px','style'=>'width: 120px;'));
					echo "</td>";
					echo "<td>";
					echo "<button type='button' id='find_address' class='btn' style='margin-top: 6px;margin-left: -8px;width: 130px;height: 29px;'>Find my address</button>";
					echo "</td>";
				echo "</tr>";
			echo "</table>";
		echo "</td>";
		echo "<td>".$this->Form->input('customer_address_1', array('id' => 'MobilePurchaseCustomerAddress1','name' => 'MobilePurchase[customer_address_1]','placeholder' => 'property name/no. and street name'));
                    ?>
                    <select name='street_address' id='street_address'><option>--postcode--</option></select>
                    <span id='address_missing'><br/><a href='#-1'>Address is not on the list</a></span>
		</td>
        <?php echo "<td>".$this->Form->input('customer_address_2', array('id' => 'MobilePurchaseCustomerAddress2','name' => 'MobilePurchase[customer_address_2]','placeholder' => "further address details (optional)")).
                "</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td>".$this->Form->input('city',array('id' => 'MobilePurchaseCity','name' => 'MobilePurchase[city]','label' => 'Town/City','placeholder' => "name of town or city"))."</td>";
		echo "<td>".$this->Form->input('state', array('id' => 'MobilePurchaseState','name' => 'MobilePurchase[state]','label'=>'County', 'placeholder' => "name of county (optional)"))."</td>";
		echo "<td>".$this->Form->input('country',array('id' => 'MobilePurchaseCountry','name' => 'MobilePurchase[country]','options'=>$countryOptions))."</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td colspan='3'>";
		echo ('<h4>Mobile Details</h4><hr/>');
		echo "</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td colspan='3'>
				<table>
					<tr>
						<td>".$this->Form->input('brand_id',array('id' => 'MobilePurchaseBrandId','name' => 'MobilePurchase[brand_id]','rel'=>$brandUrl))."</td>";
						echo "<td>".$this->Form->input('mobile_model_id',array('id' => 'MobilePurchaseMobileModelId','name' => 'MobilePurchase[mobile_model_id]'))."</td>";
						echo "<td><span class='input select'>".$this->Form->input('grade',array(
						  'id' => 'MobilePurchaseGrade',
                                                                                        'options' => $gradeType,
                                                                                        'empty'=>'Choose Grade',
                                                                                        'rel'=>$gradeUrl,
																						'name' => 'MobilePurchase[grade]',
                                                                                        'default'=>$chosenGrade,
                                                                                        'div' => false,
                                                                                        )
                                                          )."</span>&nbsp;&nbsp;<a id='openwindow' style=\"cursor: pointer;\"><i>Grade Info</i>.</a></td>";
?>
					<?php
						echo "<td>".$this->Form->input('type',array('id' => 'MobilePurchaseType','name' => 'MobilePurchase[type]','options'=>array('1'=>'locked','0'=>'unlocked'),'empty'=>'-Choose-'))."</td>
						<td><span id='networkId'>".$this->Form->input('network_id',array('id' => 'MobilePurchaseNetworkId','name' => 'MobilePurchase[network_id]','empty'=>'Choose'))."</span></td>";
					echo "</tr>
				</table>
			</td>
		</tr>";
		$allowedDiscount = array();
		
		//pr($discountOptions);die;
		foreach($discountOptions as $dis => $discountOption){
		  if($dis > $maximum_topup)break;
			$allowedDiscount[$dis] = $discountOption;
		}
		$maxCP = $this->request->data['cost_price'];
		$maxAllowed = 0;
		if(is_array($discountOptions)){
		  $maxAllowed = end($allowedDiscount);
		}
		$maxCP = (((int)$maxAllowed / 100) * $maxCP) + $maxCP;
		
		$fieldColor = $this->Form->input('color',array('name' => 'MobilePurchase[color]','options' => $colorOptions));
		
		$fieldIMEI = $this->Form->input('imei',array('id' => 'MobilePurchaseImei',
													 'label' => 'IMEI',
													 'name' => 'MobilePurchase[imei]',
													 'maxlength'=>14,
													 'value' => $imei2,
													 'style'=>"width: 118px"));
		
		$fieldIMEI1 = $this->Form->input('imei1',array(
														'id' => 'imei1',
													   'type' => 'text',
													   'label' => false,
													   'id' =>'imei1',
													   'value' => $imei1,
													   'style'=>"width: 30px;margin-left: -45px;margin-top: 17px",
													   'name' => 'MobilePurchase[imei1]',
													   'readonly'=>'readonly'));
		
		$fieldMaxTopUp = $this->Form->input('maximum_topup',array(
																  'id' => 'MobilePurchaseColor',
																  'options' => $allowedDiscount,
																  'selected' => $topupPercetage,
																  'name' => 'MobilePurchase[maximum_topup]',
																  'label' => 'Max Topup',
																  'style' => 'display:none',
																  ));
		
		$fieldCP = $this->Form->input('cost_price',array( //MobilePurchaseCostPrice
														  'id' => 'MobilePurchaseCostPrice',
														 'label' => 'Purchasing Cost',
														 'type' => 'text',
														 'name' => 'MobilePurchase[cost_price]',
														 'readonly' => 'readonly',   // on 9/6/2016 by sourabh
														 'style' => 'width:80px;'));
		
		if($this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id')==ADMINISTRATORS){?>
		  <input type="hidden" id="manager" value=1 />
		  <?php $fieldUpdatedPrice = $this->Form->input('topedup_price',array(
																	  'id' => 'MobilePurchaseTopedupPrice',
																	  'label' => 'Updated Price',
																	  'type' => 'text',
																	  'name' => 'MobilePurchase[topedup_price]',
																	  //'onblur'=>'updateprice();',
																	  'style' => 'width:80px;'
																	  ));
		}else{?>
		  <input type="hidden" id="manager" value=0 />
		  <?php $fieldUpdatedPrice = $this->Form->input('topedup_price',array(
																	   'id' => 'MobilePurchaseTopedupPrice',
																	  'label' => 'Updated Price',
																	  'type' => 'text',
																	  'name' => 'MobilePurchase[topedup_price]',
																	  'onblur'=>'updateprice();',
																	  'style' => 'width:80px;'
																	  ));
		}
		
		
		
		$heightestCostPrice = "<a id='heighestCP' href='#' title='Heighest Cost Price:$maxCP' alt='Heighest Cost Price:$maxCP'>##</a>";
		$hiddenTopUPPrice = $this->request['topedup_price'];
		echo "<tr>
				<td colspan='3'>
				  <table>
					<tr>
					  <td>$fieldColor</td>
					  <td>$fieldIMEI</td>
					  <td>$fieldIMEI1</td>
					  <td>$fieldCP</td>
					  <td>{$fieldMaxTopUp}{$heightestCostPrice}</td>
					  <td>$fieldUpdatedPrice</td>
					</tr>
				  </table>
				</td>
			  </tr>";
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
					  $checked = '';
					  if(in_array($ch,$moblileArr)){$checked = "checked";}
					  echo $this->Form->input($condition, array(
																'type' => 'checkbox',
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
		  echo $this->Form->input('mobile_condition_remark',array(
																  'id' => 'MobilePurchaseMobileConditionRemark',
																  'label' => false,
																  'type' => 'text',
																  'name'=>'MobilePurchase[mobile_condition_remark]',
																  'placeholder' => 'Mobile Condition Remarks(Fill in case of other)',
																  'style' => 'display: none;',
																  'value' => $conditionRemarks
																  ));
		}
		
		if(count($functionConditions)){
		  $functionChunks = array_chunk($functionConditions,2,true);
		  if(count($functionChunks)){
			  echo "<table id= 'function_condition_table'>";
				  echo "<tr>";
					  echo "<td colspan = '4'>";
						  echo ("<h4>Phone's Functions Test<sup>**</sup> (For internal use only)</h4><hr/>");
					  echo "</td>";
				  echo "</tr>";
				  echo "<tr>";
					  foreach($functionChunks as $f => $Fchunk){
						echo "<td>";
						  foreach($Fchunk as $fch => $functionCondition){
							$checked = '';
							if(in_array($fch,$functionArr)){$checked = "checked";}
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
			echo "<td>".$this->Form->input('description',array('id' => 'MobilePurchaseDescription','name'=>'MobilePurchase[description]'))."</td>";
			echo "<td>".$this->Form->input('brief_history', array('id' => 'MobilePurchaseBriefHistory','label' => 'Apple ID / Password','name'=>'MobilePurchase[brief_history]'))."</td>";
			//'disabled' => 'disabled' commented on 31st Oct on Inder request
		echo "</tr>";
	echo "</table>";
	echo "<input type='hidden' id = 'hiddenHeighestCP' name = 'hiddenHeighestCP' value='$maxCP'/>";
	?>
	</fieldset>
	
<?php
    echo $this->Form->Submit(__('Submit'));
    echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
	  <li><?php #echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('MobilePurchase.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('MobilePurchase.id'))); ?></li>
	  <li><?php echo $this->Html->link(__('List Mobile Purchases'), array('action' => 'index')); ?></li>
	</ul>
</div>

<script>
	$('#MobilePurchaseBrandId').change(function(){
	  $('#MobilePurchaseCostPrice').val("");
	  $('#MobilePurchaseTopedupPrice').val("");
	  $('#MobilePurchaseMaximumTopup').val(0);
	  var brand = $('#MobilePurchaseBrandId').val();
	  var targetUrl = $('#MobilePurchaseBrandId').attr('rel') + '?id=' + brand;
	  $.blockUI({ message: 'Just a moment...' });
	  $.ajax({
		type: 'get',
		url: targetUrl,
		beforeSend: function(xhr) {
		  xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		},
		success: function(response) {
		  $.unblockUI();
		  //console.log(response);
		  $('#MobilePurchaseMobileModelId').empty();
		  $('#MobilePurchaseMobileModelId').append(response);
		},
		error: function(e) {
		  $.unblockUI();
		  alert("An error occurred: " + e.responseText.message);
		  console.log(e);
		}
	  })
	});
	
	$('#MobilePurchaseMobileModelId').change(function(){
	  $('#MobilePurchaseCostPrice').val("");
	  $('#MobilePurchaseTopedupPrice').val("");
	  $('#MobilePurchaseType').val("");
	});
	
	$('#MobilePurchaseGrade').change(function(){
	  $('#MobilePurchaseType').val("");
	  $('#MobilePurchaseCostPrice').val("");
	  $('#MobilePurchaseTopedupPrice').val("");
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
	  var model = $('#MobilePurchaseMobileModelId').val();
	  var brand = $('#MobilePurchaseBrandId').val();
	  var grade = $('#MobilePurchaseGrade').val();
	  var type = $('#MobilePurchaseType').val();
	  var targetUrl = $('#MobilePurchaseGrade').attr('rel') + '?model=' + model + '&brand=' + brand + '&grade=' + grade + '&type=' + type;
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
			$('#hiddenHeighestCP').val(heighestCP);
			$('#heighestCP').tooltip("option", "content","Heighest Cost Price:" + heighestCP);
			
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
	
	$('#MobilePurchaseMaximumTopup').change(function(){
		$('#MobilePurchaseTopedupPrice').val("");
		var topup = $(this).val();
		var purchaseCost = $('#MobilePurchaseCostPrice').val();
		var updatedPrice = parseFloat(topup * purchaseCost / 100) + parseFloat(purchaseCost);
		 updatedPrice = updatedPrice.toFixed(2);
		$('#MobilePurchaseTopedupPrice').val(updatedPrice);
	});
	
	function updateprice(){
	  var topup = $('#MobilePurchaseMaximumTopup').val();
	  var purchaseCost = $('#MobilePurchaseCostPrice').val();
	  var TopedupPrice = $('#MobilePurchaseTopedupPrice').val();
	  var updatedPrice = parseFloat(topup * purchaseCost / 100) + parseFloat(purchaseCost);
	  updatedPrice = updatedPrice.toFixed(2);
	  /*if(updatedPrice<parseFloat(TopedupPrice)){
		alert("Please enter updated price between 0 to " +updatedPrice);
	  }*/
	  updatedPrice = parseFloat($('#hiddenHeighestCP').val());
	  var topUpCost = parseFloat($('#MobilePurchaseTopedupPrice').val());
	  if(topUpCost > updatedPrice){
		alert("Please enter updated price between 0 to " + updatedPrice);
		$('#MobilePurchaseTopedupPrice').val('');
		$( "#MobilePurchaseTopedupPrice" ).focus();
		return;
	  }
	}
	
	$('document').ready(function(){
	  var topup = $('#MobilePurchaseMaximumTopup').val();
	  var purchaseCost = $('#MobilePurchaseCostPrice').val();
	  var TopedupPrice = $('#MobilePurchaseTopedupPrice').val();
	  var updatedPrice = parseFloat(topup * purchaseCost / 100) + parseFloat(purchaseCost);
	  updatedPrice = updatedPrice.toFixed(2);
	  <?php if($topupPercetage == 0 && ($topedUpPrice == '' || $topedUpPrice == 0)){?>
		//$('#MobilePurchaseTopedupPrice').val(purchaseCost); //new change on Aug 16, I am commenting it
	  <?php }elseif($topupPercetage > 0){?>
		//$('#MobilePurchaseTopedupPrice').val(updatedPrice);//new change on Aug 16, I am commenting it. this worked
	  <?php }elseif(is_numeric($topedUpPrice) && $topedUpPrice > 0){?>
		//$('#MobilePurchaseTopedupPrice').val(<?=$topedUpPrice?>);//new change on Aug 16, I am commenting it
	  <?php } ?>
		if ($('#other').is(":checked")) {
		  $('#MobilePurchaseMobileConditionRemark').css("display","block");
		}else{
		  $('#MobilePurchaseMobileConditionRemark').css("display","none");
		}
	});
</script>

<script>
  $(function() {
	$('#address_missing').click(function(){
	  $('#street_address').hide();
	  $('#MobilePurchaseCustomerAddress1').show("");
	  $('#MobilePurchaseCustomerAddress1').val("");
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
	
	$('#street_address').hide();
	$('#MobilePurchaseCustomerAddress1').show();
	$('#address_missing').hide();
	//-----------
	//rajju jan4.2016
	$("#MobilePurchaseCustomerContact").keydown(function (event) {  
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
	//rajju jan 4,2016 till here
	//-----------
	$("#MobileUnlockImei").keydown(function (event) {		
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
	
	$("#MobilePurchaseTopedupPrice").keydown(function (event) {  
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
  });
</script>

<script>
  $("#MobilePurchaseEditForm").submit(function(){
	//----------------------------------------
	var purchaseCost = $('#MobilePurchaseCostPrice').val(); //purchase cost
	var TopedupPrice = $.trim($('#MobilePurchaseTopedupPrice').val()); //updated price
	var topup = $('#MobilePurchaseMaximumTopup').val();
	var updatedPrice = parseFloat(topup * purchaseCost / 100) + parseFloat(purchaseCost);
	updatedPrice = parseFloat($('#hiddenHeighestCP').val());//new change
	
   
	if ($('#MobilePurchaseTopedupPrice').val() == '') {
	  //if updated price is empty
	  $('#error_div').html("Please input mobile Update Price!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
	  alert('Please input mobile Update Price!');
	  return false;
	}else if (parseFloat(TopedupPrice) > updatedPrice) {
	  var manager = $('#manager').val();
	  if (manager == 1) {
      ;  
      }else{
		alert("Please enter updated price between 0 to " +updatedPrice);
		$('#error_div').html("Please enter updated price between 0 to " +updatedPrice+"!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		return false;
	  }
	  
	}
	//----------------------------------------
	var topUpPurchasedPrice = $.trim($('#MobilePurchaseTopedupPrice').val());
	if ( topUpPurchasedPrice == "" || parseFloat(topUpPurchasedPrice) <= 0) {
	  $('#error_div').html("Please input valid Mobile Price!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
	  alert('Please input mobile price!');
	  $('#MobilePurchaseTopedupPrice').focus();
	  return false;
    }
	
	var mobileType = $('#MobilePurchaseType').val();
	var networkId = $('#MobilePurchaseNetworkId').val();
	if(mobileType == 1 && networkId == "" ){
	  $('#error_div').html("Please choose mobile network").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
	  alert("Please choose mobile network");
	  return false;
	}
				
	if($('#MobilePurchaseImei').val().length < 14){
	  $('#error_div').html("Input Imei must be more than 14 digits").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
	  alert("Input Imei must be more than 14 digits");
	  return false;
	}
                        
	if (document.getElementById("mobile_condition_table")) {
	  var mobileCondChk = $('input[name="MobilePurchase[function_condition][]"]:checkbox:checked');
	  if (mobileCondChk.length == 0)  {
		$('#error_div').html("Please select function's condition!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert('Please select phone"s condition!');
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
	
	if(mobileType == 0){$('#MobilePurchaseNetworkId').val("");}
	
  });
</script>

<script>
	function showText(){
	  var a = document.getElementById('MobilePurchaseCustomerIdentification').value;
	  if(a == 'Others'){			   
		document.getElementById("MobilePurchaseOthers").disabled = '';
	  }else{
		document.getElementById("MobilePurchaseOthers").disabled = 'false';
	  }
	}
	
	//to be done
	function submitForm(){
	  var b = document.getElementById('MobilePurchaseCustomerIdentification').value;
	  if(b == 'Others'){	
		document.getElementById("MobilePurchaseCustomerIdentification").disabled='false';
		document.getElementById('MobilePurchaseAddForm').submit;
	  }
	}
	
	window.onload = showText();
	$("#MobilePurchaseTopedupPrice").keydown(function (event) {  
	  if (event.shiftKey == true) {event.preventDefault();}
	  if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		 (event.keyCode >= 96 && event.keyCode <= 105) ||
		 event.keyCode == 8 || event.keyCode == 9 ||
		 event.keyCode == 37 || event.keyCode == 39 ||
		 event.keyCode == 46 || event.keyCode == 183 || event.keyCode == 190) {
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
        
    $('#other').click(function(){
	  if ($(this).is(":checked")) {
		$('#MobilePurchaseMobileConditionRemark').css("display","block");
	  } else {
		$('#MobilePurchaseMobileConditionRemark').css("display","none");
	  }
    });
	
    $(document).ready(function(){
	  $(function(){$('#heighestCP').tooltip();});
	  //$('#hiddenHeighestCP').val($('#MobilePurchaseTopedupPrice').val());
   });
</script>