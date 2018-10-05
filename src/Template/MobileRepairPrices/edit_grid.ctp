<div class="mobilePrices form">
<?php
$inputData = false;
if($this->request->data){
	$problemTypeValues = array();
	$inputData = $this->request->data['MobileRepairPrice'];
	foreach($inputData['problem_type'] as $key => $problem_type){
		$problemTypeValues[$problem_type] = $key;
	}
}
echo $this->Form->create('MobileRepairPrice');
?>
		<h2>Edit Mobile Repair Price</h2>
	<table>
		<tr>
			<td>&nbsp;</td>
			<th>Brand</th>
			<th>Model</th>
			<th>Problem Type</th>
			<th>Repair Cost</th>
			<th>Repair Price</th>
			<th>Repair Days</th>
		</tr>
	<?php
	$count = 0;
    //pr($inputData);
    //pr($problemTypeValues);
    //pr($problemType);die;
	foreach($problemType as $key => $problem){
        //pr($problem);die;
        //pr($problemTypeValues[$key]);
        //pr($inputData['orig_repair_cost']);die;
		if($inputData){
            if(array_key_exists($key,$problemTypeValues)){
				$orig_repair_cost = $inputData['orig_repair_cost'][$problemTypeValues[$key]];
				$orig_repair_price = $inputData['orig_repair_price'][$problemTypeValues[$key]];
				$orig_repair_days = $inputData['orig_repair_days'][$problemTypeValues[$key]];
				$repair_cost = $inputData['repair_cost'][$problemTypeValues[$key]];
				$repair_price = $inputData['repair_price'][$problemTypeValues[$key]];
				$repair_days = $inputData['repair_days'][$problemTypeValues[$key]];
				echo $this->Form->input('id',array('type' => 'hidden', 'name' => 'MobileRepairPrice[id][]','value' => $inputData['id'][$problemTypeValues[$key]]));
				$primaryId = $inputData['id'][$problemTypeValues[$key]];
            }else{
                $orig_repair_cost = "";
				$orig_repair_price = "";
				$orig_repair_days = "";
				$repair_cost = "";
				$repair_price = "";
				$repair_days = "";
				echo $this->Form->input('id',array('type' => 'hidden', 'name' => 'MobileRepairPrice[id][]','value' => ""));
				$primaryId = "";
            }
		}else{
				if(array_key_exists($key,$problemRepairPriceArr)){
					$repair_cost = $problemRepairPriceArr[$key]['repair_cost'];
					$orig_repair_cost = $problemRepairPriceArr[$key]['repair_cost'];
					$repair_price = $problemRepairPriceArr[$key]['repair_price'];
					$orig_repair_price = $problemRepairPriceArr[$key]['repair_price'];
					$repair_days = $problemRepairPriceArr[$key]['repair_days'];
					$orig_repair_days = $problemRepairPriceArr[$key]['repair_days'];
					echo $this->Form->input('id',array('type' => 'hidden', 'name' => 'MobileRepairPrice[id][]','value'=>$problemRepairPriceArr[$key]['id']));
					$primaryId = $problemRepairPriceArr[$key]['id'];
				}else{
					$orig_repair_cost = $orig_repair_price = $orig_repair_days = $repair_cost = $repair_price = $repair_days = '';
					echo $this->Form->input('id',array('type' => 'hidden', 'name' => 'MobileRepairPrice[id][]','value' => ''));
					$primaryId = '';
				}
		}
		
		$count++;
	?>
	 
		
		<tr>
			<?php
            //pr($problem);die;
			if(is_numeric($primaryId)){?>
				<td><?php echo $this->Form->input('del',array('label' => false, 'type' => 'checkbox', 'name' => 'MobileRepairPrice[del][]', 'value' => $primaryId, 'id' => $problem));?></td>
				 
			<?php }else{?>
				<td>&nbsp;</td>
			<?php } ?>
			<td><?php echo $brands[$brand_id];
			echo $this->Form->input('brandId',array('type' => 'hidden', 'name' => 'MobileRepairPrice[brand_id][]','value' => $brand_id));?></td>
			<td><?php echo $mobileModelName[$mobile_model_id];
			echo $this->Form->input('mobileModelId',array('type' => 'hidden', 'name' => 'MobileRepairPrice[mobile_model_id][]','value' => $mobile_model_id));?></td>
			<td><?php  echo $problem;
			echo $this->Form->input('problem_type',array('type' => 'hidden', 'name' => 'MobileRepairPrice[problem_type][]', 'value' => $key))?></td>
			<td><?php echo $this->Form->input('null',array('label' => false, 'type'=>'text','value' => $repair_cost,'name'=>"MobileRepairPrice[repair_cost][]",'id' => 'repair_cost_id_'.$count));?>
			<?php echo $this->Form->input('null',array('label' => false, 'type'=>'hidden','value' => $orig_repair_cost,'name'=>"MobileRepairPrice[orig_repair_cost][]"));?></td>
			<td><?php echo $this->Form->input('null',array('label' => false, 'type'=>'text','value' => $repair_price,'name'=>"MobileRepairPrice[repair_price][]",'id' => 'repair_price_id_'.$count));?>
			<?php echo $this->Form->input('null',array('label' => false, 'type'=>'hidden','value' => $orig_repair_price,'name'=>"MobileRepairPrice[orig_repair_price][]"));?></td>
			<td><?php echo $this->Form->input('null',array('label' => false, 'type'=>'text','value' => $repair_days,'name'=>"MobileRepairPrice[repair_days][]",'id' => 'repair_days_id_'.$count));?>
			<?php echo $this->Form->input('null',array('label' => false, 'type'=>'hidden','value' => $orig_repair_days,'name'=>"MobileRepairPrice[orig_repair_days][]"));?></td>
			 
		</tr>
	<?php } ?>
	<tr>
		<td colspan='2' ><input type="submit" id='delete_button' name = 'delete' value='Delete' style='width: 66px;border-radius: 5px;height: 35px;'></td>
		<td colspan='2'>
             <?= $this->Form->submit(__('Submit',['style'=>"margin-top:20px;height:34px;"]),array('name'=>'submit')) ?>
    <?= $this->Form->end() ?>
         </td>
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
	$('#delete_button').click(function(){
		var someObj={};
		someObj.delIds=[];
		var idString = '';
		idString = $('input:checked').map(function() {return this.id;}).get().join(', ');
		if (idString != '') {
			alertMsg = 'Do you really want to delete the rows with Problem value: ' + idString + '?';
			if (confirm(alertMsg) == 0){
				return false;
			}
		} else {
			alert('Please check the rows to delete!');
			return false;
		}
	});
</script>