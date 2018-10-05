<div class="mobilePurchases form">
<?php
$add_2_cart_full = $this->Url->build(['controller' => 'mobile-purchases', 'action' => 'update_imei'],true); ?>
<input type='hidden' name='add_2_cart_full' id='update_url' value='<?=$add_2_cart_full?>' />
<?php

	if(array_key_exists('MobilePurchase',$this->request->data)){
		if(array_key_exists('mobile_model_id',$this->request->data['MobilePurchase'])){
			$modelId = $this->request->data['MobilePurchase']['mobile_model_id'];
			$brandId = $this->request->data['MobilePurchase']['brand_id'];
		}	
	}
	
	$count = count($imeiData);
	$numberOfMobiles = ceil($count/3);
	
	if(!empty($this->request['data']['MobilePurchase']['mobile_purchase_reference'])){
		$referenceNumber = $this->request['data']['MobilePurchase']['mobile_purchase_reference'];
		$costPrice = $this->request['data']['MobilePurchase']['purchase_cost'];
	}
	$url = $this->Url->build(array('controller' => 'customers', 'action' => 'get_address'));
	echo $this->Form->create('MobilePurchase');
	
	$fieldSP = $fieldLSP = "";
	if($isCustomGradeRef){
		$gradeType = array($grade => $grade);
		if(array_key_exists('MobilePurchase',$this->request->data)){
			if(array_key_exists('grade',$this->request->data['MobilePurchase'])){
				$grade = $this->request['data']['MobilePurchase']['grade'];
				$sellingPrice = $this->request['data']['MobilePurchase']['selling_price'];
				$lowestSellingPrice = $this->request['data']['MobilePurchase']['lowest_selling_price'];
			}
		}
		$fieldSP = $this->Form->input('selling_price', array(
                                                            'id' => 'MobilePurchaseSellingPrice',
                                                            'name' => 'MobilePurchase[selling_price]',
															 'type' => 'text',
															 'style' => 'width:70px;',
															 'value' => $sellingPrice,
															)
									  );
		$fieldLSP = $this->Form->input('lowest_selling_price', array(
                                                            'id' => 'MobilePurchaseLowestSellingPrice',
                                                            'name' => 'MobilePurchase[lowest_selling_price]',
															 'type' => 'text',
															 'style' => 'width:70px;',
															 'value' => $lowestSellingPrice,
															)
									  );
		$fieldGrade = $this->Form->input('grade',array(
                                                        'id' => 'MobilePurchaseGrade',
                                                        'name' => 'MobilePurchase[grade]',
													   'type' => 'text',
														'value' => $grade,
														'style' => 'width:70px;',
														));
		$filedHiddenCustomGrade = "<input type='hidden' name='MobilePurchase[custom_grade]' value='1'/>";
	}else{
		$gradeUrl = $this->Url->build(array('action' => 'get_price'));
		$fieldGrade = $this->Form->input('grade',array('options' => $gradeType,
															  'id' => "MobilePurchaseGrade",
															  'rel' => $gradeUrl,
															  'name' => 'MobilePurchase[grade]',
															  'empty' => 'Choose',
															  'onchange' => "getcostprice();",
															  'value' => $grade));
		$filedHiddenCustomGrade = "<input type='hidden' name='MobilePurchase[custom_grade]' value='0'/>";
	}
	//if phone is purchased in bulk by admin, than $purchaseStatus should be = 1
	$filedHiddenPurchaseStatus = "
		<input type='hidden' name='MobilePurchase[purchase_status]' value='$purchaseStatus'/>
		<input type='hidden' name='MobilePurchase[custom_grades]' value='$customGrades'/>
	";
?>
	<fieldset>
		<legend><?php echo __('Edit Mobile Reference List'); ?></legend>
	<?php
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		echo $this->Form->input('kiosk_id',array('id' =>'MobilePurchaseKioskId', 'name' => 'MobilePurchase[kiosk_id]','type'=>'hidden','value'=>$kiosk_id));
		//echo $this->Form->input('purchase_number', array('type'=>'text'));
		echo "<table>";
		echo "<tr>";
		echo "<td>Purchasing reference number:".$this->Form->input('mobile_purchase_reference',array('id' => 'MobilePurchaseMobilePurchaseReference','name' => 'MobilePurchase[mobile_purchase_reference]','type'=>'text','label'=>false,'style'=>'width: 150px;height: 10px;margin-left: 10px;','value'=>$referenceNumber))."</td>
		<td>&nbsp;<td>
		";
		//$this->Html->link('Change screen',array('action'=>'bulk_mobile'))
		echo "<tr>";
		echo "<td colspan='2'>";
		echo ('<h4>Mobile Details</h4><hr/>');
		echo $filedHiddenCustomGrade;echo $filedHiddenPurchaseStatus;
		echo "</td>";
		echo "</tr>";
		$url = $this->Url->build(array('action'=>'get_bulk_models'));
		
		echo "<tr>";
			echo "<td colspan='2'>
				<table cellspacing='0'>
					
					<tr>
						<td>".$this->Form->input('brand_id',array('id' => 'MobilePurchaseBrandId', 'name' => 'MobilePurchase[brand_id]','rel' => $url,'value' => $brandId))."</td>";
						//pr($mobileModels);
						echo "<td>".$this->Form->input('mobile_model_id',array(
																			   'id' => 'MobilePurchaseMobileModelId',
                                                                               'name' => 'MobilePurchase[mobile_model_id]',
                                                                               'value' => $modelId,
																			   'default' => $modelId))."</td>";
						echo "<td>".$this->Form->input('type',array('options' => array('1' => 'locked','0' => 'unlocked'),
																	'empty' => '-Choose-',
																	'id' => "MobilePurchaseType",
																	'name' => 'MobilePurchase[type]',
																	'onchange' => "gettype();",
																	'value' => $type));
						echo "</td>";
						echo "<td><span id='network'>".$this->Form->input('network_id',array('id' => 'MobilePurchaseNetworkId','name' => 'MobilePurchase[network_id]','empty' => 'Choose', 'value' => $networkId))."</span></td>";
						
					echo "</tr>
				
					<tr>";
						echo "<td>";
						echo "Color".$this->Form->input('color', array('label' => false,
																		'name' => 'MobilePurchase[color]',
																		'options' => $colorOptions,
																		'value' => $color,
																		));
						echo "</td>";
						echo "<td>$fieldGrade</td>";
						echo "<td>" ;
						echo $this->Form->input('temp_purchase_cost',array('type' => 'text',
																		   'name' => 'MobilePurchase[purchase_cost]',
																		   'label' => 'Cost Price',
																		   'id' => "MobilePurchaseTempPurchaseCost",
																		   'style' => "width: 65px;",
																		   'value' => $costPrice));
						echo "</td>
					</tr>
					<tr><td>$fieldSP</td><td>$fieldLSP</td></tr>
				</table>
			</td>";
		echo "</tr>";
		
		echo "<tr>
			<td colspan='2'>
				<table>
					<tr>
					<td>Imeis:</td>
					</tr>
					<tr>
						<td>";
							echo "<table style='width: 27%;'>";
								$firstEnd = $numberOfMobiles - 1;
								for($i = 0; $i <= $firstEnd; $i++){
									if(!empty($this->request['data']['MobilePurchase']['imei'])){
										$imei = $this->request['data']['MobilePurchase']['imei'][$i];
										$id = $this->request['data']['MobilePurchase']['id'][$i];
									}elseif(isset($imeiData[$i])){
										$imei = $imeiData[$i]['imei'];
										$id = $imeiData[$i]['id'];
									}else{
										$imei = '';
										$id = '';
									}
									
									echo "<tr>";
										echo "<td>";
											echo $this->Form->input('temp_imei', array('type' => 'text' ,'name'=>"MobilePurchase[imei][$i]",'style'=>"width: 200px;",'label'=>false, 'id'=>"MobilePurchaseTempImei_$i",'maxLength'=>15,'old_value'=>$imei,'value'=>$imei, 'tabindex' => $i));
											echo $this->Form->input('id',array('type'=>'hidden','id' =>"id_".$i,'name'=>"MobilePurchase[id][$i]",'value'=>$id));
											echo "<a href='#' id='update' rel= $i>Update</a>";
										echo"</td>";
									echo"</tr>";
								}
							echo "</table>";
						echo "</td>";
						
						echo "<td>";
							echo "<table style='width: 27%;'>";
								 $ending = $numberOfMobiles+$numberOfMobiles-1;
								for($i = $numberOfMobiles; $i <= $ending; $i++){
									if(!empty($this->request['data']['MobilePurchase']['imei'])){
										$imei1 = $this->request['data']['MobilePurchase']['imei'][$i];
										$id = $this->request['data']['MobilePurchase']['id'][$i];
									}elseif(isset($imeiData[$i])){
                                        //pr($imeiData);die;
										$imei1 = $imeiData[$i]['imei'];
										$id = $imeiData[$i]['id'];
									}else{
										$imei1 = '';
										$id = '';
									}
									
									echo "<tr>";
										echo "<td>";
											echo $this->Form->input('temp_imei', array('type' => 'text' ,'name'=>"MobilePurchase[imei][$i]",'style'=>"width: 200px;",'label'=>false,'id'=>"MobilePurchaseTempImei_$i",'maxLength'=>15,'value'=>$imei1,'old_value'=>$imei1));
											echo $this->Form->input('id',array('type'=>'hidden','id' =>"id_".$i,'name'=>"MobilePurchase[id][$i]",'value'=>$id));
											echo "<a href='#' id='update' rel= $i >Update</a>";
										echo"</td>";
										
									echo "</tr>";
								}
							echo "</table>";
						echo "</td>";
						
						echo "<td>";
							echo "<table style='width: 27%;'>";
							 $finalEnding = $ending + $numberOfMobiles;
								for($i = $ending + 1; $i <= $finalEnding; $i++){
									if(!empty($this->request['data']['MobilePurchase']['imei'])){
										$imei2 = $this->request['data']['MobilePurchase']['imei'][$i];
										$id = $this->request['data']['MobilePurchase']['id'][$i];
									}elseif(isset($imeiData[$i])){
										$imei2 = $imeiData[$i]['imei'];
										$id = $imeiData[$i]['id'];
									}else{
										$imei2 = '';
										$id = '';
									}
									echo "<tr>";
										echo "<td>";
											echo $this->Form->input('temp_imei', array('type' => 'text' ,'name'=>"MobilePurchase[imei][$i]",'style'=>"width: 200px;",'label'=>false,'id'=>"MobilePurchaseTempImei_$i",'maxLength'=>15,'value'=>$imei2,'old_value'=>$imei2));
											echo $this->Form->input('id',array('type'=>'hidden','id' =>"id_".$i,'name'=>"MobilePurchase[id][$i]",'value'=>$id));
											echo "<a href='#' id='update' rel= $i>Update</a>";
										echo"</td>";
										
									echo "</tr>";
								}
							echo "</table>";
						echo "</td>
					</tr>
				</table>
			</td>";
		echo "</tr>";
		echo "<tr>
				<td colspan='2'>";
					echo "<table>
							<tr>";
							echo "<td>".$this->Form->input('description',array('id' => 'MobilePurchaseDescription','name' => 'MobilePurchase[description]','value'=>$description)).$this->Form->input('purchasing_date',array('id' => 'MobilePurchasePurchasingDate','name' => 'MobilePurchase[purchasing_date]','type'=>'hidden','value'=>$purchaseDate))."</td>";
							echo "<td>".$this->Form->input('brief_history', array('id' => 'MobilePurchaseBriefHistory','name' => 'MobilePurchase[brief_history]','value' => $brief_history, 'label' => 'Purchase History(For Internal Use)'))."</td>";
						echo "</tr>";
					echo "</table>
				</td>";
		echo "</tr>";
		
		echo "</table>";
	?>
	</fieldset>
	
<?php
//$options = array('name'=>'submit','value'=>'Submit');
//echo $this->Form->end($options);

#echo $this->Form->input('button',array('type'=>'button'));
?>
<div>
<input type="Submit" name="submit2" value="Submit" style="width: 87px;" id="submit_button">
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('Stock by Reference'), array('action' => 'reference_number_listing')); ?></li>
		<li><?php # echo $this->Html->link(__('List Kiosks'), array('controller' => 'kiosks', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php # echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
	</ul>
</div>
<?php
	if($isCustomGradeRef){
		$condtionalJS = "";
	}else{
		$condtionalJS = <<< COND_JS
		$('#MobilePurchaseTempPurchaseCost').val("");
		$('#MobilePurchaseGrade').val("");
COND_JS;
	}
	
?>
<script>
	$('#MobilePurchaseBrandId').change(function(){
		$('#MobilePurchaseNetworkId').val("");
		$('#MobilePurchaseType').val("");
		<?php echo $condtionalJS;?>
		var id = $(this).val();
		var targetUrl = $(this).attr('rel') + '?id=' + id;
		$.blockUI({ message: 'Just a moment...' });
		$.ajaxSetup({
		url: targetUrl,
			success: function(result){
				$.unblockUI();
			$('#MobilePurchaseMobileModelId').empty();
			$('#MobilePurchaseMobileModelId').append(result);
			}
		});
		$.ajax();
	});
	
	$('#MobilePurchaseMobileModelId').change(function(){
		$('#MobilePurchaseNetworkId').val("");
		$('#MobilePurchaseType').val("");
		<?php echo $condtionalJS;?>
	});
</script>
<script>
	function getcostprice(){
		var mobileType = $("#MobilePurchaseType").val();
		var selectedgrade = $("#MobilePurchaseGrade").val();
		var model = $('#MobilePurchaseMobileModelId').val();
		var brand = $('#MobilePurchaseBrandId').val();
		
		if(mobileType==""){
			alert("Please choose type locked or unlocked!!");
			$("#MobilePurchaseGrade").val("");
			return;
		}
		var getpriceUrl = $('#MobilePurchaseGrade').attr('rel') + '?model=' + model + '&brand=' + brand + '&grade=' + selectedgrade + '&type=' + mobileType;
		$.blockUI({ message: 'Just a moment...' });
		$.ajaxSetup({
		url: getpriceUrl,
			success: function(result){
				$.unblockUI();
				var obj = jQuery.parseJSON(result);
				if (obj.err==0) {
					$('#MobilePurchaseTempPurchaseCost').val(obj.cost_price);
				}else if(obj.err==1){
					alert("No detail found for this combination!!");
				}
			}
		});
		$.ajax();
	}
</script>
<script>
	function gettype(){
		<?php echo $condtionalJS;?>
		var mobileType = $('#MobilePurchaseType').val();
		if(mobileType ==1){
			$('#network').show();
			$('#MobilePurchaseNetworkId').val("");
		}else if(mobileType == 0){
			$('#network').hide();
			$('#MobilePurchaseNetworkId').val("");
		}
	}
</script>
<script>
	$(function() {
		$("#MobilePurchaseSellingPrice, #MobilePurchaseLowestSellingPrice, #MobilePurchaseTempPurchaseCost").keydown(function (event) {			
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
		<?php
			  $firstEnd = $numberOfMobiles - 1;
				for($i = 0; $i <= $firstEnd; $i++){?> 
				$("input[id*='MobilePurchaseTempImei_<?php echo $i;?>']").keydown(function (event) {			
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
		 <?php } ?>
		 <?php
			  $ending = $numberOfMobiles + $numberOfMobiles - 1;
				for($i = $numberOfMobiles; $i <= $ending; $i++){?> 
				$("input[id*='MobilePurchaseTempImei_<?php echo $i;?>']").keydown(function (event) {			
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
		 <?php } ?>
		<?php
			  $finalEnding = $ending + $numberOfMobiles;
				for($i = $ending + 1; $i <= $finalEnding; $i++){?> 
				$("input[id*='MobilePurchaseTempImei_<?php echo $i;?>']").keydown(function (event) {			
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
		 <?php } ?>
	}); 
</script>
<script>
	$(document).ready(function(){
		<?php $firstEnd = $numberOfMobiles-1;
				for($i = 0; $i <= $firstEnd; $i++){?>
				var MobilePurchaseTempImei = $('#MobilePurchaseTempImei_<?php echo $i;?>').val();
				if(MobilePurchaseTempImei ==""){
					$("input[id*='MobilePurchaseTempImei_<?php echo $i;?>']").hide();
				}else {
					$("input[id*='MobilePurchaseTempImei_<?php echo $i;?>']").show();
				}
		<?php } ?>
		<?php $ending = $numberOfMobiles+$numberOfMobiles-1;
				for($i = $numberOfMobiles; $i <= $ending; $i++){?>
				var MobilePurchaseTempImei = $('#MobilePurchaseTempImei_<?php echo $i;?>').val();
					if(MobilePurchaseTempImei ==""){
						$("input[id*='MobilePurchaseTempImei_<?php echo $i;?>']").hide();
					}else {
						$("input[id*='MobilePurchaseTempImei_<?php echo $i;?>']").show();
					}
		<?php 	} ?>
		<?php 	$finalEnding = $ending+$numberOfMobiles;
				for($i = $ending + 1; $i <= $finalEnding; $i++){?>
				var MobilePurchaseTempImei = $('#MobilePurchaseTempImei_<?php echo $i;?>').val();
					if(MobilePurchaseTempImei ==""){
						$("input[id*='MobilePurchaseTempImei_<?php echo $i;?>']").hide();
					}else {
					 
						$("input[id*='MobilePurchaseTempImei_<?php echo $i;?>']").show();
					
					}
			<?php } ?>
		var MobilePurchaseType = $('#MobilePurchaseType').val();
		if(MobilePurchaseType==1){
			$("#network").show();
		}else if(MobilePurchaseType==0){
			$("#network").hide();
		}
	});
</script>
<script>
		//$("#MobilePurchaseBulkMobilePurchaseForm").submit(function(){
		//	return false;
		//});
		
		$("#submit_button").click(function(){
			var MobilePurchaseType = $('#MobilePurchaseType').val();
			var grade = $('#MobilePurchaseGrade').val();
			var price = $('#MobilePurchaseTempPurchaseCost').val();
			if (MobilePurchaseType == "") {
				alert("Please choose Type from dropdown");
				return false;
			}
			if (grade == "") {
				alert("Please choose Grade from dropdown");
				return false;
			}
			if (price == "") {
				alert("Please input the cost price");
				return false;
			}
			
			var MobilePurchaseNetworkId = $('#MobilePurchaseNetworkId').val();
			if(MobilePurchaseType==1 &&
			   MobilePurchaseNetworkId==0
			   ){
				alert("Please choose network id");
				return false;
			}else{
				$.blockUI({ message: 'Please Wait...' });
				$("#MobilePurchaseEditMobileReferenceListForm").submit();
			}
		})
</script>
<script>
	$(document).on('click', '#update', function() {
		var i = $(this).attr('rel');
		var imei = $("#MobilePurchaseTempImei_"+i).val();
		var id = $("#id_"+i).val();
		var targeturl = $("#update_url").val();
		targeturl += '?id='+id;
		targeturl += '&imei='+imei;
		$.blockUI({ message: 'Updating...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				var objArr = $.parseJSON(response);
				alert(objArr.msg);
				$.unblockUI();
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
	<?php $firstEnd = $numberOfMobiles-1;
				for($i = 0; $i <= $firstEnd; $i++){?>
					$('#MobilePurchaseTempImei_<?php echo $i;?>').blur(function(){
						var i = <?php echo $i;?>;
						var old_val = $(this).attr("old_value");
						var new_val = $(this).val();
						if (old_val != new_val) {
                            if (new_val.length >= 14) {
								if (confirm("Are You Sure You Want to Update Imei?")) {
                                   update(i); 
                                }else{
									
								}
                                //
                            }
                        }
					});
				
		<?php } ?>
		
		<?php $ending = $numberOfMobiles+$numberOfMobiles-1;
				for($i = $numberOfMobiles; $i <= $ending; $i++){?>
					$('#MobilePurchaseTempImei_<?php echo $i;?>').blur(function(){
						var i = <?php echo $i;?>;
						var old_val = $(this).attr("old_value");
						var new_val = $(this).val();
						if (old_val != new_val) {
                            if (new_val.length >= 14) {
								if (confirm("Are You Sure You Want to Update Imei?")) {
                                   update(i); 
                                }else{
									
								}
                                //
                            }
                        }
					});
				
		<?php } ?>
		
		<?php $finalEnding = $ending+$numberOfMobiles;
				for($i = $ending + 1; $i <= $finalEnding; $i++){?>
					$('#MobilePurchaseTempImei_<?php echo $i;?>').blur(function(){
						var i = <?php echo $i;?>;
						var old_val = $(this).attr("old_value");
						var new_val = $(this).val();
						if (old_val != new_val) {
                            if (new_val.length >= 14) {
								if (confirm("Are You Sure You Want to Update Imei?")) {
                                   update(i); 
                                }else{
									
								}
                                //
                            }
                        }
					});
				
		<?php } ?>
		
		
		function update(i) {
            var imei = $("#MobilePurchaseTempImei_"+i).val();
			var id = $("#id_"+i).val();
			var targeturl = $("#update_url").val();
			targeturl += '?id='+id;
			targeturl += '&imei='+imei;
			$.blockUI({ message: 'Updating...' });
			$.ajax({
				type: 'get',
				url: targeturl,
				beforeSend: function(xhr) {
					xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				},
				success: function(response) {
					var objArr = $.parseJSON(response);
					alert(objArr.msg);
					$.unblockUI();
				},
				error: function(e) {
					$.unblockUI();
					alert("An error occurred: " + e.responseText.message);
					console.log(e);
				}
			});
        }
</script>