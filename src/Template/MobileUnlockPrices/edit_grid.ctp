<div class="mobilePrices form">
<?php
$inputData = false;
if($this->request->data){
	$networkValues = array();
	$inputData = $this->request->data['MobileUnlockPrice'];
	foreach($inputData['network_id'] as $key => $networkId){
		$networkValues[$networkId] = $key;
	}
}
echo $this->Form->create('MobileUnlockPrice');
?>
		<h2>Edit Mobile Unlock Price</h2>
	<table>
		<tr>
			<td>&nbsp;</td>
			<th>Brand</th>
			<th>Model</th>
			<th>Network</th>
			<th>Unlocking Cost</th>
			<th>Unlocking Price</th>
			<th>Unlocking Days</th>
			<th>Unlocking Minutes</th>
			<th>Status</th>
			
		</tr>
	<?php
	$count = 0;
	//pr($inputData);
	//pr($networkUnlockPriceArr);die;
	foreach($networks as $key => $network){
		if($inputData){
				//above variables are for comparing the original values with the inserted values
				$orig_unlocking_cost = $inputData['orig_unlocking_cost'][$networkValues[$key]];
				$orig_unlocking_price = $inputData['orig_unlocking_price'][$networkValues[$key]];
				$orig_unlocking_days = $inputData['orig_unlocking_days'][$networkValues[$key]];
				$unlocking_cost = $inputData['unlocking_cost'][$networkValues[$key]];
				$unlocking_price = $inputData['unlocking_price'][$networkValues[$key]];
				$unlocking_days = $inputData['unlocking_days'][$networkValues[$key]];
				$unlocking_minutes = $inputData['unlocking_minutes'][$networkValues[$key]];
				$orig_unlocking_minutes = $inputData['orig_unlocking_minutes'][$networkValues[$key]];
				$selectedStatus = $inputData['status'][$networkValues[$key]];
				$orig_status = $inputData['orig_status'][$networkValues[$key]];
				echo $this->Form->input('id',array('type' => 'hidden', 'name' => 'MobileUnlockPrice[id][]','value' => $inputData['id'][$networkValues[$key]]));
				$primaryId = $inputData['id'][$networkValues[$key]];
		}else{
			if(array_key_exists($key,$networkUnlockPriceArr)){
				$unlocking_cost = $networkUnlockPriceArr[$key]['unlocking_cost'];
				$orig_unlocking_cost = $networkUnlockPriceArr[$key]['unlocking_cost'];
				$unlocking_price = $networkUnlockPriceArr[$key]['unlocking_price'];
				$orig_unlocking_price = $networkUnlockPriceArr[$key]['unlocking_price'];
				$unlocking_days = $networkUnlockPriceArr[$key]['unlocking_days'];
				$orig_unlocking_days = $networkUnlockPriceArr[$key]['unlocking_days'];
				
				$unlocking_minutes = $networkUnlockPriceArr[$key]['unlocking_minutes'];
				$orig_unlocking_minutes = $networkUnlockPriceArr[$key]['unlocking_minutes'];
				
				$selectedStatus = $networkUnlockPriceArr[$key]['status'];
				$orig_status = $networkUnlockPriceArr[$key]['status'];
				echo $this->Form->input('id',array('type' => 'hidden', 'name' => 'MobileUnlockPrice[id][]','value'=>$networkUnlockPriceArr[$key]['id']));
				$primaryId = $networkUnlockPriceArr[$key]['id'];
			}else{
				$unlocking_cost = '';
				$unlocking_price = '';
				$unlocking_days = '';
				$selectedStatus = '';
				$unlocking_minutes = '';
				$orig_unlocking_minutes = $orig_unlocking_cost = $orig_unlocking_price = $orig_unlocking_days = $orig_status = '';
				echo $this->Form->input('id',array('type' => 'hidden', 'name' => 'MobileUnlockPrice[id][]','value' => ''));
				$primaryId = '';
			}
		}
		$count++;
	?>
		<tr>
			<?php
			if(is_numeric($primaryId)){?>
				<td><?php echo $this->Form->input('del',array('label' => false, 'type' => 'checkbox', 'name' => 'MobileUnlockPrice[del][]', 'value' => $primaryId, 'id' => $networks[$key]));?></td>
				
			<?php }else{?>
				<td>&nbsp;</td>
			<?php } ?>
			<td><?php echo $brands[$brand_id];
			echo $this->Form->input('brand_id',array('type' => 'hidden', 'name' => 'MobileUnlockPrice[brand_id][]','value' => $brand_id));?></td>
			<td><?php echo $mobileModelName[$mobile_model_id];
			echo $this->Form->input('mobile_model_id',array('type' => 'hidden', 'name' => 'MobileUnlockPrice[mobile_model_id][]','value' => $mobile_model_id));?></td>
			<td><?php echo $networks[$key];
			echo $this->Form->input('network_id',array('type' => 'hidden', 'name' => 'MobileUnlockPrice[network_id][]', 'value' => $key))?></td>
			<td><?php echo $this->Form->input('null',array('label' => false, 'type'=>'text','value' => $unlocking_cost,'name'=>"MobileUnlockPrice[unlocking_cost][]",'id' => 'unlock_cost_id_'.$count));?>
			<?php echo $this->Form->input('null',array('label' => false, 'type'=>'hidden','value' => $orig_unlocking_cost,'name'=>"MobileUnlockPrice[orig_unlocking_cost][]"));?></td>
			<td><?php echo $this->Form->input('null',array('label' => false, 'type'=>'text','value' => $unlocking_price,'name'=>"MobileUnlockPrice[unlocking_price][]",'id' => 'unlock_price_id_'.$count));?>
			<?php echo $this->Form->input('null',array('label' => false, 'type'=>'hidden','value' => $orig_unlocking_price,'name'=>"MobileUnlockPrice[orig_unlocking_price][]"));?></td>
			<td><?php echo $this->Form->input('null',array('label' => false, 'type'=>'text','value' => $unlocking_days,'name'=>"MobileUnlockPrice[unlocking_days][]",'id' => 'unlock_days_id_'.$count));?>
			<?php echo $this->Form->input('null',array('label' => false, 'type'=>'hidden','value' => $orig_unlocking_days,'name'=>"MobileUnlockPrice[orig_unlocking_days][]"));?></td>
			
			<td><?php echo $this->Form->input('null',array('label' => false, 'type'=>'text','value' => $unlocking_minutes,'name'=>"MobileUnlockPrice[unlocking_minutes][]",'id' => 'unlock_minutes_id_'.$count));?>
			<?php echo $this->Form->input('null',array('label' => false, 'type'=>'hidden','value' => $orig_unlocking_minutes,'name'=>"MobileUnlockPrice[orig_unlocking_minutes][]"));?></td>
			
			<td><?php echo $this->Form->input('null',array('name'=>"MobileUnlockPrice[status][]", 'label' => false, 'options' => $status, 'value' => $selectedStatus));?>
			<?php echo $this->Form->input('null',array('type' => 'hidden', 'name'=>"MobileUnlockPrice[orig_status][]", 'label' => false, 'value' => $orig_status));?></td>
		</tr>
	<?php } ?>
	<tr>
		<td colspan='2' style='padding-top: 18px;padding-left: 11px;'><input type="submit" id='delete_button' name = 'delete' value='Delete' style='width: 66px;border-radius: 5px;height: 35px;'></td>
		<td colspan='2'><?php
		echo $this->Form->submit('submit',array('name'=>'submit'));
		echo $this->Form->end(); ?></td>
		<td colspan='4'>&nbsp;</td>
	</tr>
	</table>
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
$("input[id*='unlock_cost_id_']").keydown(function (event) {		
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
	$("input[id*='unlock_price_id_']").keydown(function (event) {		
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
	$("input[id*='unlock_days_id_']").keydown(function (event) {		
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
	
	$("input[id*='unlock_minutes_id_']").keydown(function (event) {		
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
	
	$('#delete_button').click(function(){
		var someObj={};
		someObj.delIds=[];
		var idString = '';
		idString = $('input:checked').map(function() {return this.id;}).get().join(', ');
		if (idString != '') {
			alertMsg = 'Do you really want to delete the rows with network value: ' + idString + '?';
			if (confirm(alertMsg) == 0){
				return false;
			}
		} else {
			alert('Please check the rows to delete!');
			return false;
		}
	});
</script>