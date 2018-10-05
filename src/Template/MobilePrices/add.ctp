<div class="mobilePrices form">
<?php #pr($this->request['data']['MobilePrice']);
//pr($this->request);
echo $this->Form->create('MobilePrice',['id' => 'MobilePriceAddForm']); ?>
	<fieldset>
		<legend><?php echo __('Add Mobile Price'); ?></legend>
	<?php
	//pr($mobileModels);
	if(!empty($this->request->data)){
		$res = $this->request->data['MobilePrice']['brand_id'];
	}else{
		$res = "";
	}
		echo $this->Form->input('brand_id',array('value' => $res,'id' => 'MobilePriceBrandId','name' => 'MobilePrice[brand_id]','onChange' => 'submitForm();'));
	if(!empty($mobileModels)){
		echo "<table>";
	?>
	<table>
	<tr>
		
		<td style="width: 15%;">Enable top-up:</td>
		<td><input type="radio" name="MobilePrice[topup_status]" value="1" id="dis_yes">Yes<br/><br/>
		<input type="radio" name="MobilePrice[topup_status]" value="0" id="dis_no">No</td>
		<td id="max_dis" ><?php echo $this->Form->input('maximum_topup',array('name' => 'MobilePrice[maximum_topup]','label'=>'Maximum value','options' => $discountOptions));?></td>
		<td style="width: 15%;">Enable discount:</td>
			<td><input type="radio" name="MobilePrice[discount_status]" value="1" id="dis_unlocked_yes">Yes<br/><br/>
			<input type="radio" name="MobilePrice[discount_status]" value="0" id="dis_unlocked_no">No</td>
			<td id="max_unlocked_dis"><?php echo $this->Form->input('maximum_discount',array('name' => 'MobilePrice[maximum_discount]','label'=>'Maximum discount','options' => $discountOptions));?></td>
	</tr>
	</table>
	<table>
		<tr>
			<td><h4>Locked Mobile:</h4></td>
		</tr>
		<tr>
			<th>Mobile Model</th>
			<th>Grade</th>
			<th>Purchase Price</th>
			<th></th>
			<th>Selling Price</th>			
		</tr>
	<?php
		$n = 0;
		foreach($gradeType as $key => $grade){
			$mobileModelOptions = "";
			$gradeTypeOptions= "";			
			$sellingPriceLocked = $purchasePriceLocked = $mobileModelId = $grade_locked = $grade_unlocked = $sellingPriceUnlocked = $purchasePriceUnlocked = $lockedMobileModelId = $unlockedMobileModelId  = '';
			$nameLockedPurchasePrice = 'MobilePrice[locked_purchase_price][]';
			$nameLockedSellingPrice = 'MobilePrice[locked_selling_price][]';
			
			if(!empty($this->request['data']['MobilePrice']['locked_purchase_price'])){
				$purchasePriceLocked = $this->request['data']['MobilePrice']['locked_purchase_price'][$n];
			}
			
			if(!empty($this->request['data']['MobilePrice']['locked_selling_price'])){
				$sellingPriceLocked = $this->request['data']['MobilePrice']['locked_selling_price'][$n];
			}
			
			if(!empty($this->request['data']['MobilePrice']['locked_mobile_model_id'])){
				$lockedMobileModelId = $this->request['data']['MobilePrice']['locked_mobile_model_id'][$n];
			}
			
			if(!empty($this->request['data']['MobilePrice']['grade_locked'])){
				$grade_locked = $this->request['data']['MobilePrice']['grade_locked'][$n];
			}
			foreach($mobileModels as $k => $value){
					$selected = $lockedMobileModelId == $k ? "selected='selected'" : '';
					$mobileModelOptions.="<option value='$k' $selected>$value</option>";
				
			}
			
			foreach($gradeType as $k => $value){
				$selected = '';
				if($grade_locked == $k){
					$selected = "selected='selected'";
				}else{
				$selected = '';
					if(empty($grade_locked) && $key == $k){
							$selected = "selected='selected'";
					}
				}
				$gradeTypeOptions.="<option value='$k' $selected>$value</option>";
			}
			//pr($mobileModelOptions);
			echo "<tr>";
			//echo "<td><select name='data[MobilePrice][brand_id][]'>".$mobileModels."</select></td>";
			//echo "<td>".$this->Form->input('mobile_model_id',array('options'=>$mobileModels,'name'=>$nameMobileModel))."</td>";
	?>
			<td><select name="MobilePrice[locked_mobile_model_id][]" id='mobile_model_id_<?php echo $n;?>'><?php echo $mobileModelOptions; ?></select></td>
			
			<td><select name="MobilePrice[grade_locked][]"><?php echo $gradeTypeOptions; ?></select></td>
	<?php
			echo "<td style='width:80px;'>".$this->Form->input('cost_price',array('type'=>'text',
															  'name'=>$nameLockedPurchasePrice,
															  'value'=>$purchasePriceLocked,
															  'label'=>false,
															  'id'=>"locked_purchase_price_$n",
															  'style' => 'width:80px;'
															  )
										   )."</td>";
			?>
			<?php if($key == 1){?>
			<td rowspan='4' style="width: 180px;">
				<br/><br/>
				<input type="text" placeholder="Difference" id="difference_cost" style="width:100px;">
				<br/><br/>
				<input type="button" value="Update Selling Price &raquo;" id="cost_d" onclick="populateLockedSale();" style="width:180px;">
			</td>
			<?php } ?>
			<?php
			echo "<td>".$this->Form->input('sale_price',array('type'=>'text',
															  'name'=>$nameLockedSellingPrice,
															  'value'=>$sellingPriceLocked,
															  'label'=>false,
															  'id'=>"locked_selling_price_$n",
															  'style' => 'width:80px;'
															  )
										   )."</td>";
			echo "</tr>";
			$n++;
		}?>
		
		<tr><td><h4>Unlocked Mobile:</h4></td>
			<td>&nbsp;</td>
			<td><input type="text" placeholder="Difference" id="difference_cost_price" style="width: 100px;"></td>
			<td colspan="2"><input type="button" value="Update Purchase Price below" id="purchase_difference" onclick="populatePurchasePrice();" style="width: 215px;"></td>
		</tr>
		<tr>
			<th>Mobile Model</th>
			<th>Grade</th>
			<th>Purchase Price</th>
			<th></th>
			<th>Selling Price</th>			
		</tr>
		
		<?php
		
		$m = 0;
		foreach($gradeType as $cey => $grade){
			if(!empty($this->request['data']['MobilePrice']['grade_unlocked'])){
				$grade_unlocked = $this->request['data']['MobilePrice']['grade_unlocked'][$m];
			}
			$nameUnlockedPurchasePrice = 'MobilePrice[unlocked_purchase_price][]';
			$nameUnlockedSellingPrice = 'MobilePrice[unlocked_selling_price][]';
			if(!empty($this->request['data']['MobilePrice']['unlocked_purchase_price'])){
				$purchasePriceUnlocked = $this->request['data']['MobilePrice']['unlocked_purchase_price'][$m];
			}
			
			if(!empty($this->request['data']['MobilePrice']['unlocked_selling_price'])){
				$sellingPriceUnlocked = $this->request['data']['MobilePrice']['unlocked_selling_price'][$m];
			}
			
			if(!empty($this->request['data']['MobilePrice']['unlocked_mobile_model_id'])){
				$unlockedMobileModelId = $this->request['data']['MobilePrice']['unlocked_mobile_model_id'][$m];
			}
			
			foreach($mobileModels as $k => $value){
					$selected = $unlockedMobileModelId == $k ? "selected='selected'" : '';
					$mobileModelOptions.="<option value='$k' $selected>$value</option>";
			}
			
			$gradeTypeOpts ="";
			foreach($gradeType as $k => $value){
				//echo "k";pr($k);
				//echo "grade_unlocked";pr($grade_unlocked);
				if($grade_unlocked == $k){
					$selected = "selected='selected'";
				}else{
				$selected = '';
					if(empty($grade_unlocked) && $cey == $k){
							$selected = "selected='selected'";
					}
				}
				$gradeTypeOpts.="<option value='$k' $selected>$value</option>";
			}
			//pr($gradeTypeOpts);
			foreach($mobileModels as $k => $value){
				$selected = $mobileModelId == $k ? "selected='selected'" : '';
				$mobileModelOptions.="<option value='$k' $selected>$value</option>";
			}
			?>
		<tr>
			<td><select name="MobilePrice[unlocked_mobile_model_id][]" id='unlock_mobile_model_id_<?php echo $m;?>'><?php echo $mobileModelOptions; ?></select></td>
			<td><select name="MobilePrice[grade_unlocked][]"><?php echo $gradeTypeOpts; ?></select></td>
			<?php echo "<td>".$this->Form->input('cost_price',array('type'=>'text','name'=>$nameUnlockedPurchasePrice, 'value'=>$purchasePriceUnlocked,'label'=>false,'id'=>"unlocked_purchase_price_$m"))."</td>";
			if($cey==1){?>
				<td rowspan="4">
				<br/><br/>	
				<input type="text" placeholder="Difference" id="difference_sale" style="width: 100px;"><br/><br/>
				<input type="button" value="Update Selling Price &raquo;" id="sale_d" onclick="populateSale();" style="width: 180px;"></td>
			<?php }
			echo "<td>".$this->Form->input('sale_price',array('type'=>'text','name'=>$nameUnlockedSellingPrice, 'value'=>$sellingPriceUnlocked,'label'=>false,'id'=>"unlocked_selling_price_$m",'style'=>"width: 80px;"))."</td>";
			echo "</tr>";
			$m++;
			?>
		</tr>
		<?php } ?>
	<?php
	}else{
		echo "<h4><b>Sorry! No Mobile Models are available for this brand</b></h4>";
	}
	?>
			<tr>
				<td><input type='hidden' id="hiddenController" name='hiddenController' value="0" /></td>
			</tr>
	<?php
		
		echo "</table>";
		#echo $this->Form->input('status');
	?>
	</fieldset>
<?php
	if(!empty($mobileModels)){
		echo $this->Form->submit('Submit');
		echo $this->Form->end();
	}
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Mobile Prices'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		
		<li><?php echo $this->Html->link(__('List Mobile Models'), array('controller' => 'mobile_models', 'action' => 'index')); ?> </li>
		
	</ul>
</div>

<script>
	function updateHiddens(){return false;}
	function submitForm(){
		document.getElementById("hiddenController").value = 1;
		var selectedValue = $("#MobilePriceBrandId").val();
		$("#MobilePriceAddForm" ).submit();
	}
	
	
	$(function() {
		//On change of mobile price
		$('#mobile_model_id_0').change(function() {
			var selectedValue = $(this).val();
			<?php
				for($i = 1; $i < count($gradeType); $i++){
					echo "\n$('#mobile_model_id_{$i}').val(selectedValue);";
					echo "\n$('#unlock_mobile_model_id_0').val(selectedValue);";
					echo "\n$('#unlock_mobile_model_id_{$i}').val(selectedValue);";
				}
			?>
		});
	});
</script>
<script>
	function populateLockedSale(){
		var costDifference = document.getElementById("difference_cost").value;
		<?php
		for($i = 0 ; $i < count($gradeType); $i++){?>
		<?php $purchasePrice = "document.getElementById('locked_purchase_price_$i').value";?>
			if (costDifference=='') {
				<?php echo "\n document.getElementById('locked_selling_price_$i').value = ''"; ?>
			} else if (<?php echo $purchasePrice;?>=="") {
				<?php echo "\n document.getElementById('locked_selling_price_$i').value = ''"; ?>
			} else {
				<?php $lockedResult = "parseFloat(document.getElementById('locked_purchase_price_$i').value) + parseFloat(document.getElementById('difference_cost').value)";
				echo "\n document.getElementById('locked_selling_price_$i').value = $lockedResult"; ?>
			}
		<?php }
		?>
	}
	
	function populateSale(){
		var saleDifference = document.getElementById("difference_sale").value;
		<?php
		for($i = 0 ; $i < count($gradeType); $i++){?>
		<?php $purchasePrice = "document.getElementById('unlocked_purchase_price_$i').value";?>
			if (saleDifference=='') {
				<?php echo "\n document.getElementById('unlocked_selling_price_$i').value = ''"; ?>
			} else if (<?php echo $purchasePrice;?>=="") {
				<?php echo "\n document.getElementById('unlocked_selling_price_$i').value = ''"; ?>
			} else {
				<?php echo "\n document.getElementById('unlocked_selling_price_$i').value = parseFloat(document.getElementById('unlocked_purchase_price_$i').value) + parseFloat(document.getElementById('difference_sale').value)"; ?>
			}
			;
		<?php }
		?>
	}
	
	function populatePurchasePrice(){
		var purchaseDifference = document.getElementById("difference_cost_price").value;
		<?php
		for($i = 0 ; $i < count($gradeType); $i++){?>
		<?php $purchasePrice = "document.getElementById('locked_purchase_price_$i').value";?>
			if (purchaseDifference=='') {
				<?php echo "\n document.getElementById('unlocked_purchase_price_$i').value = ''"; ?>
			} else if (<?php echo $purchasePrice;?>=="") {
				<?php echo "\n document.getElementById('unlocked_purchase_price_$i').value = ''"; ?>
			} else {
				<?php echo "\n document.getElementById('unlocked_purchase_price_$i').value = parseFloat(document.getElementById('locked_purchase_price_$i').value) + parseFloat(document.getElementById('difference_cost_price').value)"; ?>
			}
			;
		<?php }
		?>
	}
</script>
<script>
	$('#dis_yes').click(function(){
		if($('#dis_yes').is(':checked')){
			$("#max_dis").show();
		}
	});
	
	$('#dis_no').click(function(){
		if($('#dis_no').is(':checked')){
			$("#max_dis").hide();
			$("#MobilePriceMaximumTopup").val('0');
		}
	});
	
	$('document').ready(function(){
		 $('#dis_no').prop('checked', true);
			$("#max_dis").hide();
			$("#MobilePriceMaximumTopup").val('0');
		 $('#dis_unlocked_no').prop('checked', true);
			$("#max_unlocked_dis").hide();
			$("#MobilePriceMaximumDiscount").val('0');
	});
	
	$('#dis_unlocked_yes').click(function(){
		if($('#dis_unlocked_yes').is(':checked')){
			$("#max_unlocked_dis").show();
		}
	});
	
	$('#dis_unlocked_no').click(function(){
		if($('#dis_unlocked_no').is(':checked')){
			$("#max_unlocked_dis").hide();
			$("#MobilePriceMaximumDiscount").val('0');
		}
	});
	
	$("#MobilePriceAddForm").submit(function(){
		if($('#dis_yes').is(':checked')){
			var discount = $('#MobilePriceMaximumTopup').val();
			if(discount==0){
				alert('Please choose a max value for top up');
				return false;
			}
		}
		
		if($('#dis_unlocked_yes').is(':checked')){
			var discount = $('#MobilePriceMaximumDiscount').val();
			if(discount==0){
				alert('Please choose a value of discount');
				return false;
			}
		}
	});
</script>