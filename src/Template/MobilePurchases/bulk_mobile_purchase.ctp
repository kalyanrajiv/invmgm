<div class="mobilePurchases form">
<?php

//pr($mobileModels);
//pr($this->request);
	$referenceNumber = $costPrice = $sellingPrice = $lowestSellingPrice = "";
	if(!empty($this->request['data']['MobilePurchase']['mobile_purchase_reference'])){
		$referenceNumber = $this->request['data']['MobilePurchase']['mobile_purchase_reference'];
	}
	
	if(!empty($this->request['data']['MobilePurchase']['purchase_cost'])){
		$costPrice = $this->request['data']['MobilePurchase']['purchase_cost'];
	}
	if(!empty($this->request['data']['MobilePurchase']['purchase_cost'])){
		$sellingPrice = $this->request['data']['MobilePurchase']['selling_price'];
	}
	if(!empty($this->request['data']['MobilePurchase']['lowest_selling_price'])){
		$lowestSellingPrice = $this->request['data']['MobilePurchase']['lowest_selling_price'];
	}
	$url = $this->Url->build(array('controller' => 'customers', 'action' => 'get_address'));
	echo $this->Form->create('MobilePurchase',array('id' => 'MobilePurchaseBulkMobilePurchaseForm'));
	$tempPC = '<div class="input select required">'.$this->Form->input('purchase_cost', array(
															'div' => false,
															 'type' => 'text',
															 'id' => 'MobilePurchasePurchaseCost',
															 'name' => 'MobilePurchase[purchase_cost]',
															 'label' => 'Cost Price',
															 //'id' => "MobilePurchaseTempPurchaseCost",
															 'style' => "width: 65px;",
															 'value' => "$costPrice",
															 'required' => true,
															 'tabindex' => '9',
															))."</div>";
	
	$sellingPrice = '<div class="input select required">'.$this->Form->input('selling_price',array(
															'div' => false,
															'type' => 'text',
															'required' => true,
															 'label' => 'Selling Price',
															 'style' => "width: 65px;",
															 'value' => "$sellingPrice",
															 'tabindex' => '10',
															 'id' => 'MobilePurchaseSellingPrice',
															 'name' =>'MobilePurchase[selling_price]', 
															))."</div>";
	$lowestSellingPrice = $this->Form->input('lowest_selling_price',array(
																		  'id' => 'MobilePurchaseLowestSellingPrice',
																		  'type' => 'text',
																		  'label'=>'Lowest Selling Price',
																		  'style'=>"width: 65px;",
																		  'value' => $lowestSellingPrice,
																		  'tabindex' => '11',
																		  'name' => 'MobilePurchase[lowest_selling_price]',
																		  )
											 );
?><fieldset>
		<legend><?php echo __('Add  Bulk Mobile Purchase'); ?></legend>
	<?php
	
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		echo $this->Form->input('kiosk_id',array('type'=>'hidden','name' =>'MobilePurchase[kiosk_id]', 'value'=>$kiosk_id));
		//echo $this->Form->input('purchase_number', array('type'=>'text'));
		echo "<table>";
		echo "<tr>";
				echo "<td>Purchasing reference number:".$this->Form->input('mobile_purchase_reference',array('id' => 'MobilePurchaseMobilePurchaseReference','name' => 'MobilePurchase[mobile_purchase_reference]','type'=>'text','label'=>false,'div'=>false,'style'=>'width: 150px;height: 10px;margin-left: 10px;','value'=>$referenceNumber,'tabindex'=> "1"))."</td>";
				//echo "<td>".$this->Html->link('Change screen',array('action'=>'bulk_mobile'))."<td>";
				echo "<td> <a href='#####' title='Change screen' tabindex='2'> Change screen </a></td>" ; 
				 
				echo "</tr>";
		echo "<tr>";
		echo "<td colspan='2'>";
		echo ('<h4>Mobile Details</h4><hr/>');
		echo "</td>";
		echo "</tr>";
		$url = $this->Url->build(array('action'=>'get_bulk_models'));
		echo "<tr>";
			echo "<td colspan='2'>
				<table cellspacing='0'>
					<tr>
						<td>".$this->Form->input('MobilePurchase.brand_id',array('id' => 'MobilePurchaseBrandId','name' => 'MobilePurchase[brand_id]','rel'=>$url,'tabindex' => '3'))."</td>";
						echo "<td>".$this->Form->input('MobilePurchase.mobile_model_id',array('id' => 'MobilePurchaseMobileModelId','name' => 'MobilePurchase[mobile_model_id]','empty'=>'Choose','tabindex' => '4'))."</td>";
						echo "<td>".$this->Form->input('MobilePurchase.type',array('id' => 'MobilePurchaseType','name' => 'MobilePurchase[type]','options'=>array('1'=>'locked','0'=>'unlocked'),'empty'=>'-Choose-','id'=>"MobilePurchaseType",'name'=>'MobilePurchase[type]','tabindex' => '5','onchange'=>"gettype();"));
						echo "</td>";
						echo "<td><span id='network'>".$this->Form->input('MobilePurchase.network_id',array('id' => 'MobilePurchaseNetworkId','name' => 'MobilePurchase[network_id]','empty'=>'Choose','tabindex' => '6'))."</span></td>";
						$gradeUrl = $this->Url->build(array('action'=>'get_price'));
					echo "</tr>
					<tr>";
						echo "<td>";
						echo "Color".$this->Form->input('MobilePurchase.color', array(
							'label' => false,
							'name' => 'MobilePurchase[color]',
							'options' => $colorOptions,
							'tabindex' => '7',
							'id' => 'MobilePurchaseColor',
							'name'=>"MobilePurchase[color]"
						    ));
						echo "</td>";
						echo "<td>";
						/*
						echo $this->Form->input('grade', array(
															   'options' => $gradeType,
															   'id'=>"MobilePurchaseGrade",
															   'rel' => $gradeUrl,
															   'name'=>'data[MobilePurchase][grade]',
															   'empty'=>'Choose',
															   'onchange'=>"getcostprice();")
												);
						//On change cost was pre-filling automatically
						*/
						echo $this->Form->input('MobilePurchase.grade', array(
															   'style' => 'width:80px;',
															   'tabindex' => '8',
															   'type' => 'text',
															   'id' => 'MobilePurchaseGrade',
															   'name' => 'MobilePurchase[grade]'
															  )
												);
						echo "</td>";
						echo "<td>&nbsp;</td>
					</tr>";
					echo "<tr><td>$tempPC</td><td>$sellingPrice</td><td>$lowestSellingPrice</td><td><b>Phone count </b>=<span id='count'>0</span></td></tr>";
					//echo "<tr><td colspan='2'></tr>";
				echo "</table>
			</td>";
		echo "</tr>";
		
		echo "<tr>
			<td colspan='2'>
				<table>
					<tr>
						<td>";
							echo "<table style='width: 27%;'>";
								$imei = '';$index = 0;
								for($i=1;$i<=10;$i++){
									if(!empty($iemis) &&
									   array_key_exists($i,$iemis)){
										$imei = $iemis[$i];
									}
									$index = 11+$i;
									echo "<tr>";
										echo "<td>";
											echo $this->Form->input('temp_imei', array('type' => 'text' ,
																					   'name'=>"MobilePurchase[imei][$i]",
																					   'style'=>"width: 200px;",'label'=>'Imei',
																					   'id'=>"MobilePurchaseTempImei_$i",
																					   'tabindex' => $index,
																					   'maxLength'=>15,
																					   'value'=>$imei,
																					   //'tabindex' => $i
																					   ));
										echo"</td>";
									echo"</tr>";
								}
							echo "</table>";
						echo "</td>";
						
						echo "<td>";
							echo "<table style='width: 27%;'>";
								$imei = '';
								for($i=11;$i<=20;$i++){
									if(!empty($iemis) &&
									   array_key_exists($i,$iemis)){
										$imei = $iemis[$i];
									}
									$index = 11+$i;
									echo "<tr>";
										echo "<td>";
											echo $this->Form->input('temp_imei', array('type' => 'text' ,
																					   'name'=>"MobilePurchase[imei][$i]",
																					   'style'=>"width: 200px;",
																					   'label'=>'Imei','id'=>"MobilePurchaseTempImei_$i",'maxLength'=>15,'tabindex' => $index,'value'=>$imei));
											
										echo"</td>";
										
									echo "</tr>";
								}
							echo "</table>";
						echo "</td>";
						
						echo "<td>";
							echo "<table style='width: 27%;'>";
							$imei = '';
								for($i=21;$i<=30;$i++){
									if(!empty($iemis) &&
									   array_key_exists($i,$iemis)){
										echo $imei = $iemis[$i];
									}
									$index = 11+$i;
									echo "<tr>";
										echo "<td>";
											echo $this->Form->input('temp_imei', array('type' => 'text' ,
																					   'name'=>"MobilePurchase[imei][$i]",
																					   'style'=>"width: 200px;",
																					   'label'=>'Imei',
																					   'id'=>"MobilePurchaseTempImei_$i",
																					   'maxLength'=>15,
																					   'tabindex' => $index,
																					   'value'=>$imei));
											
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
							echo "<td>".$this->Form->input('MobilePurchase.description',array('type'=>'textarea','name' => 'MobilePurchase[description]','tabindex' => 42)).$this->Form->input('purchasing_date',array('type'=>'hidden','name' => 'MobilePurchase[purchasing_date]','value'=>$purchaseDate))."</td>";
							echo "<td>".$this->Form->input('MobilePurchase.brief_history', array('type'=>'textarea','name' => 'MobilePurchase[brief_history]', 'label' => 'Purchase History(For Internal Use)','tabindex' => 43))."</td>";
						echo "</tr>";
					echo "</table>
				</td>";
		echo "</tr>";
		
		echo "</table>";
	?>
	</fieldset>
	
<?php
//$options = array('name'=>'submit','value'=>'Submit');


#echo $this->Form->input('button',array('type'=>'button'));
?>
<div>
<input type="button" value="Submit"   style="width: 87px;" id="submit_button" tabindex = 43 >
<?php echo $this->Form->end(); ?>
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Mobile Purchases'), array('controller'=>'mobile_purchases','action' => 'index')); ?></li>
		<li><?php # echo $this->Html->link(__('List Kiosks'), array('controller' => 'kiosks', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php # echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
	$(function() {
		<?php for($i=1;$i<30;$i++){?>
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

	$('#MobilePurchaseBrandId').change(function(){
		$('#MobilePurchaseNetworkId').val("");
		$('#MobilePurchaseType').val("");
		$('#MobilePurchaseTempPurchaseCost').val("");
		$('#MobilePurchaseGrade').val("");
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
		$('#MobilePurchaseTempPurchaseCost').val("");
		$('#MobilePurchaseGrade').val("");
	});

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

	function gettype(){
		$('#MobilePurchaseTempPurchaseCost').val("");
		$('#MobilePurchaseGrade').val("");
		var mobileType = $('#MobilePurchaseType').val();
		if(mobileType==1){
			$('#network').show();
			$('#MobilePurchaseNetworkId').val("");
		}else if(mobileType==0){
			$('#network').hide();
			$('#MobilePurchaseNetworkId').val("");
		}
	}

	$(document).ready(function(){
		var MobilePurchaseType = $('#MobilePurchaseType').val();
		if(MobilePurchaseType==1){
			$("#network").show();
		}else if(MobilePurchaseType==0){
			$("#network").hide();
		}
	});
	
	$("#submit_button").click(function(){
		//alert("hi");
		var MobilePurchaseType = $('#MobilePurchaseType').val();
		var MobilePurchaseNetworkId = $('#MobilePurchaseNetworkId').val();
		if(MobilePurchaseType==1 &&
		   MobilePurchaseNetworkId==0
		   ){
			alert("Please choose network id");
			return false;
		}else{
			//alert("helo");
			$("#MobilePurchaseBulkMobilePurchaseForm").submit();
		}
	})
	
	$("#MobilePurchaseTempPurchaseCost, #MobilePurchaseSellingPrice, #MobilePurchaseLowestSellingPrice").keydown(function (event) {		
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||
		event.keyCode == 46 || event.keyCode == 190 || event.keyCode == 183 || event.keyCode == 190 || event.keyCode == 110) {
			;
		}  else {
			event.preventDefault();
		}
	});
	
	<?php for($i = 1; $i<= 30; $i++){ ?>
		var value = '<?=$i?>';
		$('#MobilePurchaseTempImei_'+value).focusout(function (event) {
			var counter = 0;
				for(var i=1; i<=30;i++){
					var box_val = $('#MobilePurchaseTempImei_'+i).val();
					if (box_val != "" && box_val != "undefined" && box_val != null) {
                       counter++; 
                    }
				}
				$('#count').text(counter);
		});
		<?php } ?>
	
	
	
</script>

<script type='text/javascript'>
        $(document).ready(function(){
            $('#MobilePurchaseBulkMobilePurchaseForm input').keydown(function(e){
             if(e.keyCode==13){       

                if($(':input:eq(' + ($(':input').index(this) + 1) + ')').attr('type')=='submit'){// check for submit button and submit form on enter press
                 return true;
                }

                $(':input:eq(' + ($(':input').index(this) + 1) + ')').focus();
				//alert(1);
               return false;
             }

            });
        });
</script>

