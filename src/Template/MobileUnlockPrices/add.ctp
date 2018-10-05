<div class="mobileUnlockPrices form">
<?php echo $this->Form->create($mobileUnlockPrices,array('id' => 'MobileUnlockPriceAddForm')); ?>
	<fieldset>
		<legend><?php echo __('Add Mobile Unlock Price'); ?></legend>
<?php
	echo $this->Form->input('brand_id',array('id' => 'MobileUnlockPriceBrandId','onChange' => 'submitForm();'));
?>	
		<table>
			<tr>
				<th>Mobile Model</th>
				<th>Network</th>
				<th>Unlock Costing</th>
				<th>Unlocking Price</th>
				<th>Unlocking Days</th>
				<th>Unlocking Minutes</th>
			</tr>
		<?php
			$mobileModelOptions = "";
			$networkOptions = "";
			foreach($mobileModels as $key => $value){
				$mobileModelOptions.="<option value='$key'>$value</option>";
			}		
			
			$i = 0;
			$mobileUnlockPrice = array();
			if(array_key_exists('MobileUnlockPrice',$this->request->data)){
				$mobileUnlockPrice = $this->request->data['MobileUnlockPrice'];
			}
			foreach($networks as $k => $v){
				$mobileModelOptions = $networkOptions = "";
				$modelT = $unlockingDays = $unlockingPrice = $networkT = $unlockingCost = "";
				
				if(!empty($mobileUnlockPrice['mobile_model_id'][$i])){
					$modelT = $mobileUnlockPrice['mobile_model_id'][$i];
				}
				
				if(!empty($mobileUnlockPrice['network_id'][$i])){
					$networkT = $mobileUnlockPrice['network_id'][$i];
				}
				
				if(!empty($mobileUnlockPrice['unlocking_cost'][$i])){
					$unlockingCost = $mobileUnlockPrice['unlocking_cost'][$i];
				}
				
				if(!empty($mobileUnlockPrice['unlocking_price'][$i])){
					$unlockingPrice = $mobileUnlockPrice['unlocking_price'][$i];
				}
				
				if(!empty($mobileUnlockPrice['unlocking_days'][$i])){
					$unlockingDays = $mobileUnlockPrice['unlocking_days'][$i];
				}
				
				foreach($mobileModels as $modelID => $modelName){
					$selected = '';
					if($modelT == $modelID){
						$selected = "selected='selected'";
					}else{
						if($mobileModels[$modelID] == $modelT){
							$selected = "selected='selected'";
						}
					}
					$mobileModelOptions.="<option value='$modelID' $selected>$modelName</option>";
				}
				
				foreach($networks as $networkID => $networkValue){
					$selected = '';
					if($networkT == $networkID){
						$selected = "selected='selected'";
					}else{
						//if form not posted
						if($networks[$k] == $networkValue){
							$selected = "selected='selected'";
						}
					}
					$networkOptions.="<option value='$networkID' $selected>$networkValue</option>";
				}
		?>
	<tr>
		<td><select name="MobileUnlockPrice[mobile_model_id][]" id='mobile_model_id_<?php echo $i;?>'><?php echo $mobileModelOptions; ?></select></td>
		<td><select name="MobileUnlockPrice[network_id][]"><?php echo $networkOptions; ?></select></td>
		<td><input type='text' name='MobileUnlockPrice[unlocking_cost][]' id='unlocking_cost_id_<?php echo $i;?>' value='<?php echo $unlockingCost;?>'></td>
		<td><input type='text' name='MobileUnlockPrice[unlocking_price][]' id='unlocking_price_id_<?php echo $i;?>' value="<?php echo $unlockingPrice;?>"></td>
		<td><input type='text' name='MobileUnlockPrice[unlocking_days][]' id='unlocking_days_id_<?php echo $i;?>' value=<?php echo $unlockingDays;?>></td>
		<td><input type='text' name='MobileUnlockPrice[unlocking_minutes][]' id='unlocking_minutes_id_<?php echo $i;?>' value=<?php echo $unlockingDays;?>></td>
	</tr>
		<?php
			$i++;
			}
		?>
	<tr>
		<td><input type='hidden' id="hiddenController" name='hiddenController' value="0" /></td>
	</tr>
	<tr><td colspan='7'><span style="font-style: italic"><super>**</super>Network Type <b>other</b> do not have any effect</span></td></tr>
		</table>	
	</fieldset>
<?php
echo $this->Form->Submit(__('Submit'));
echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Mobile Unlock Prices'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Mobile Models'), array('controller' => 'mobile_models', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Mobile Model'), array('controller' => 'mobile_models', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Networks'), array('controller' => 'networks', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Network'), array('controller' => 'networks', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
	function updateHiddens(){return false;}
	function submitForm(){
		document.getElementById("hiddenController").value = 1;
		var selectedValue = $("#MobileUnlockPriceBrandId").val();
		$("#MobileUnlockPriceAddForm" ).submit();
	}
	
	$(function() {
		$('#mobile_model_id_0').change(function() {
			var selectedValue = $(this).val();
			<?php
				for($i = 1; $i < count($networks); $i++){
					echo "\n$('#mobile_model_id_{$i}').val(selectedValue);";
				}
			?>
		});
	});
	$("input[id*='unlocking_cost_id_']").keydown(function (event) {		
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||
		event.keyCode == 46 || event.keyCode == 190 || event.keyCode == 183 ||
		event.keyCode == 110) {
			;
			//48-57 => 0..9
			//8 => Backspace; 9 => tab 
		} else {
			event.preventDefault();
		}
		//if($(this).val().indexOf('.') !== -1 && event.keyCode == 190){event.preventDefault();}
        });
	$("input[id*='unlocking_price_id_']").keydown(function (event) {		
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||
		event.keyCode == 46 || event.keyCode == 190 || event.keyCode == 183 ||
		event.keyCode == 110) {
			;
			//48-57 => 0..9
			//8 => Backspace; 9 => tab 
		} else {
			event.preventDefault();
		}
		//if($(this).val().indexOf('.') !== -1 && event.keyCode == 190){event.preventDefault();}
        });
	$("input[id*='unlocking_days_id_']").keydown(function (event) {		
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||
		event.keyCode == 46 || event.keyCode == 190 || event.keyCode == 183 ||
		event.keyCode == 110) {
			;
			//48-57 => 0..9
			//8 => Backspace; 9 => tab 
		} else {
			event.preventDefault();
		}
		//if($(this).val().indexOf('.') !== -1 && event.keyCode == 190){event.preventDefault();}
        });
	
	$("input[id*='unlocking_minutes_id_']").keydown(function (event) {		
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||
		event.keyCode == 46 || event.keyCode == 190 || event.keyCode == 183 ||
		event.keyCode == 110) {
			;
			//48-57 => 0..9
			//8 => Backspace; 9 => tab 
		} else {
			event.preventDefault();
		}
		//if($(this).val().indexOf('.') !== -1 && event.keyCode == 190){event.preventDefault();}
        });
</script>