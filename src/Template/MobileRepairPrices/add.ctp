<div class="mobileRepairPrices form">
<?php echo $this->Form->create('MobileRepairPrice',['id'=>'MobileRepairPriceAddForm']); ?>
	<fieldset>
		<legend><?php echo __('Add Mobile Repair Price'); ?></legend>
<?php
	 echo $this->Form->input('brand_id',array('onChange' => 'submitForm();'));
  //  echo $this->Form->input('brand_id');	
	if(!empty($mobileModels)){
?>
		<table>
			<tr>
				<th>Mobile Model</th>
				<th>Problem Type</th>
				<th>Problem Description</th>
				<th>Repair Cost</th>
				<th>Repair Price</th>
				<th>Repair Days</th>
			</tr>
	<?php
		$n = 0;
		foreach($problemType as $key => $value){
			$mobileModelOptions = "";
			$problemTypeOptions = "";
			$repairDays = $repairPrice = $repairCost = $problem =$problemT = $mobileModelId = '';
			if(!empty($this->request->data['MobileRepairPrice']['mobile_model_id'][$n])){
				$mobileModelId = $this->request->data['MobileRepairPrice']['mobile_model_id'][$n];
			}
			
			if(!empty($this->request->data['MobileRepairPrice']['problem_type'][$n])){
				$problemT = $this->request->data['MobileRepairPrice']['problem_type'][$n];
			}
			
			if(!empty($this->request->data['MobileRepairPrice']['problem'][$n])){
				$problem = $this->request->data['MobileRepairPrice']['problem'][$n];
			}
			
			if(!empty($this->request->data['MobileRepairPrice']['repair_cost'][$n])){
				$repairCost = $this->request->data['MobileRepairPrice']['repair_cost'][$n];
			}
			
			if(!empty($this->request->data['MobileRepairPrice']['repair_price'][$n])){
				$repairPrice = $this->request->data['MobileRepairPrice']['repair_price'][$n];
			}
			
			if(!empty($this->request->data['MobileRepairPrice']['repair_days'][$n])){
				$repairDays = $this->request->data['MobileRepairPrice']['repair_days'][$n];
			}			
			 
			foreach($mobileModels as $k => $value){
              	$selected = $mobileModelId == $k ? "selected='selected'" : '';
				$mobileModelOptions.="<option value='$k' $selected>$value</option>";
			}			
		  
			foreach($problemType as $k => $value){
				if($problemT == $k){
					$selected = "selected='selected'";
				}else{
					$selected = '';
					if($problemType[$key] == $value){
						$selected = "selected='selected'";
					}
				}
				$problemTypeOptions.="<option value='$k' $selected>$value</option>";
			}
			
		?>	
<tr>
	<td><select name="MobileRepairPrices[mobile_model_id][]" id='mobilemodelid_<?php echo $n;?>'><?php echo $mobileModelOptions; ?></select></td>
	<td><select name="MobileRepairPrices[problem_type][]"><?php echo $problemTypeOptions; ?></select></td>
	<td><input type='text' name='MobileRepairPrices[problem][]' value='<?php echo $problem; ?>'></td>
	<td><input type='text' name='MobileRepairPrices[repair_cost][]' id='repair_cost_id_<?php echo $n;?>' value='<?php echo $repairCost;?>'></td>
	<td><input type='text' name='MobileRepairPrices[repair_price][]' id='repair_price_id_<?php echo $n;?>' value='<?php echo $repairPrice;?>'></td>
	<td><input type='text' name='MobileRepairPrices[repair_days][]' id='repair_days_id_<?php echo $n;?>' value='<?php echo $repairDays;?>'></td>
</tr>

<?php
			$n++;
		}				
	}else{
		echo "<h4><b>Sorry! No Mobile Models are available for this brand</b></h4>";
	}
?>
			<tr>
				<td><input type='hidden' id="hiddenController" name='hiddenController' value="0" /></td>
			</tr>
			<tr><td colspan='7'><span style='font-style: italic'><super>**</super>Problem Type <b>other</b> do not have any effect</span></td></tr>
		</table>	
	</fieldset>
	
<?php
	if(!empty($mobileModels)){
      
 		//$options = array('label' => 'Submit','onclick' => 'updateHiddens()');
 		//echo  $this->Form->button(__($options)) ;
        echo $this->Form->submit('Submit',['onclick' => 'updateHiddens()']);
        echo $this->Form->end();
      
	}
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Mobile <br/>Repair Prices'), array('action' => 'index'),array('escape' => false)); ?></li>
		<li><?php echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Models'), array('controller' => 'mobile_models', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Model'), array('controller' => 'mobile_models', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
	function updateHiddens(){
            return false;
        }
	function submitForm(){
	   document.getElementById("hiddenController").value = 1;
		var selectedValue = $("#brand-id").val();
        //alert(selectedValue);return false;
		$("#MobileRepairPriceAddForm" ).submit();
	}
	$(function() {
		//On change of mobile price
		$('#mobilemodelid_0').change(function() {
			var selectedValue = $(this).val();
           // elert(selectedValue);
			<?php
				for($i = 1; $i < count($problemType); $i++){
					echo "\n$('#mobilemodelid_{$i}').val(selectedValue);";
				}
			?>
		});
	});
	$("input[id*='repair_cost_id_']").keydown(function (event) {		
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
	$("input[id*='repair_price_id_']").keydown(function (event) {		
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
	$("input[id*='repair_days_id_']").keydown(function (event) {		
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