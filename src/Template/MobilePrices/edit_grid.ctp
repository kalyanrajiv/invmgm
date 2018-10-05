<div class="mobilePrices form">
<?php echo $this->Form->create('MobilePrice');
$statusArray = array(0 => 'Inactive', 1 => 'Active');
?>
	<fieldset>
			<legend><?php echo __('Edit Mobile Price ').$this->Form->input('status', array('options' => $statusArray, 'div' => false, 'label' => false, 'style' => "font-size: 13px;", 'default' => $mobilePriceData[0]['status'])) ?></legend>
	<table>
		<?php $discountStatusArr = array();
		$maxDiscountArr = array();
		$topupStatusArr = array();
		$maxTopupArr = array();
		//pr($mobilePriceData);
		foreach($mobilePriceData as $key => $mobilePrices){
            //pr($mobilePrices);die;
			$discountStatusArr[$mobilePrices['discount_status']]=$mobilePrices['discount_status'];
			$maxDiscountArr[$mobilePrices['maximum_discount']]=$mobilePrices['maximum_discount'];
			$topupStatusArr[$mobilePrices['topup_status']]=$mobilePrices['topup_status'];
			$maxTopupArr[$mobilePrices['maximum_topup']]=$mobilePrices['maximum_topup'];
		}
		?>
		<tr>
			<td><strong>Top-up Status</strong></td>
			<td><?php echo $this->Form->input('topup_status',array('options'=>$discountStatus,'label'=>false,'value'=>$topupStatusArr));?></td>
			<td><?php echo $this->Form->input('maximum_topup',array('options'=>$discountOptions,'label'=>false,'value'=>$maxTopupArr));?></td>
			<td><strong>Discount Status</strong></td>
			<td><?php echo $this->Form->input('discount_status',array('options'=>$discountStatus,'label'=>false,'value'=>$discountStatusArr));?></td>
			<td><?php echo $this->Form->input('maximum_discount',array('options'=>$discountOptions,'label'=>false,'value'=>$maxDiscountArr));?></td>
		</tr>
	</table>
	</fieldset>
	<table>
		<tr>
			<td colspan='6'><h4>Locked Mobile:</h4></td>
		</tr>
		<tr>
			<th>Brand</th>
			<th>Model</th>
			<th>Type</th>
			<th>Grade</th>
			<th>Cost Price</th>
			<th></th>
			<th>Sale Price</th>
		</tr>
	<?php
	$countlocked = 0;
	$n = 0;
	foreach($mobilePriceData as $key1 => $mobilePrices){
		if($mobilePrices['locked'] == 1){
		$countlocked++;
	?>
		<tr>
			<td><?php echo $brands[$mobilePrices['brand_id']];?>
			<?php echo $this->Form->input('id',array('type'=>'hidden','value'=>$mobilePrices['id'],'name'=>"MobilePrice[id][]"));?>
			<?php echo $this->Form->input('locked',array('type'=>'hidden','value'=>$mobilePrices['locked'],'name'=>"MobilePrice[locked][]"));?>
			</td>
			<td><?php echo $mobileModelName[$mobilePrices['mobile_model_id']];?></td>
			<td><?php echo $type[$mobilePrices['locked']];?></td>
			<td><?php echo $gradeType[$mobilePrices['grade']];?></td>
			<td><?php echo $this->Form->input('cost_price',array('type'=>'text','value'=>$mobilePrices['cost_price'],'name'=>"MobilePrice[cost_price][]", 'id' => "locked_purchase_price_$n"));?></td>
			<?php if($key1 == 0){?>
			<td rowspan='4' style="width: 180px;">
				<br/><br/>
				<input type="text" placeholder="Difference" id="difference_cost" style="width:100px;margin-left: 15px;">
				<br/><br/>
				<input type="button" value="Update Selling Price &raquo;" id="cost_d" onclick="populateLockedSale();" style="width:180px;margin-left: 10px;">
			</td>
			<?php }
			$lastKey = $key1;?>
			<td><?php echo $this->Form->input('sale_price',array('type'=>'text','value'=>$mobilePrices['sale_price'],'name'=>"MobilePrice[sale_price][]", 'id' => "locked_selling_price_$n"));?></td>
		</tr>
	<?php $n++;
	}
	}
	$one = '1';
	$unlockStartKey = $lastKey+$one;//we are using this variable for table allignment purpose for putting the condition like above (if($key1 == 0)
	?>
	<tr>
		<td><h4>Unlocked Mobile:</h4></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><input type="text" placeholder="Difference" id="difference_cost_price" style="width: 100px;margin-left: 15px;"></td>
		<td colspan="2"><input type="button" value="Update Purchase Price below" id="purchase_difference" onclick="populatePurchasePrice();" style="width: 215px;margin-left: 10px;"></td>
	</tr>
	<tr>
		<th>Brand</th>
		<th>Model</th>
		<th>Type</th>
		<th>Grade</th>
		<th>Cost Price</th>
		<th></th>
		<th>Sale Price</th>
	</tr>
	<?php
	$countunlocked = 0;
	$m = 0;
	foreach($mobilePriceData as $key2 => $mobilePrices){
		if($mobilePrices['locked'] == 0){
		$countunlocked++;
	?>
		<tr>
			<td><?php echo $brands[$mobilePrices['brand_id']];?>
			<?php echo $this->Form->input('id',array('type'=>'hidden','value'=>$mobilePrices['id'],'name'=>"MobilePrice[id][]"));?>
			<?php echo $this->Form->input('locked',array('type'=>'hidden','value'=>$mobilePrices['locked'],'name'=>"MobilePrice[locked][]"));?>
			</td>
			<td><?php echo $mobileModelName[$mobilePrices['mobile_model_id']];?></td>
			<td><?php echo $type[$mobilePrices['locked']];?></td>
			<td><?php echo $gradeType[$mobilePrices['grade']];?></td>
			<td><?php echo $this->Form->input('cost_price',array('type'=>'text','value'=>$mobilePrices['cost_price'],'name'=>"MobilePrice[cost_price][]", 'id' => "unlocked_purchase_price_$m"));?></td>
			<?php if($key2 == $unlockStartKey){?>
			<td rowspan='4' style="width: 180px;">
				<br/><br/>
				<input type="text" placeholder="Difference" id="difference_sale" style="width:100px;margin-left: 15px;">
				<br/><br/>
				<input type="button" value="Update Selling Price &raquo;" id="cost_d" onclick="populateSale();" style="width:180px;margin-left: 10px;">
			</td>
			<?php } ?>
			<td><?php echo $this->Form->input('sale_price',array('type'=>'text','value'=>$mobilePrices['sale_price'],'name'=>"MobilePrice[sale_price][]", 'id' => "unlocked_selling_price_$m"));?></td>
		</tr>
	<?php $m++;
	}
	} ?>
	</table>
<?php
echo $this->Form->Submit(__('Submit'),array('name'=>'submit'));
echo $this->Form->end(); ?>
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
	$('#MobilePriceTopupStatus').change(function(){
		var valu = $(this).val();
		if(valu==0){
			$('#MobilePriceMaximumTopup').hide();
			$('#MobilePriceMaximumTopup').val("0");
		}else if (valu==1){
			$('#MobilePriceMaximumTopup').show();
		}
	});
	
	$('#MobilePriceDiscountStatus').change(function(){
		var valu = $(this).val();
		if(valu==0){
			$('#MobilePriceMaximumDiscount').hide();
			$('#MobilePriceMaximumDiscount').val("0");
		}else if (valu==1){
			$('#MobilePriceMaximumDiscount').show();
		}
	});
	
	$('document').ready(function(){
		var lockedDiscStatus = $('#MobilePriceTopupStatus').val();
		var unlockedDiscStatus = $('#MobilePriceDiscountStatus').val();
		
		if(lockedDiscStatus==0){
			$('#MobilePriceMaximumTopup').hide();
		}
		
		if(unlockedDiscStatus==0){
			$('#MobilePriceMaximumDiscount').hide();
		}
	});
	
	$("#MobilePriceEditForm").submit(function(){
		var lockedDiscStatus = $('#MobilePriceLockedDiscountStatus').val();
		var unlockedDiscStatus = $('#MobilePriceUnlockedDiscountStatus').val();
		var lockedMaxDisc = $('#MobilePriceLockedMaximumDiscount').val();
		var unlockedMaxDisc = $('#MobilePriceUnlockedMaximumDiscount').val();
		if(lockedDiscStatus==1 && lockedMaxDisc==0){
			alert("Please select discount value for locked mobile");
			return false;
		}
		if(unlockedDiscStatus==1 && unlockedMaxDisc==0){
			alert("Please select discount value for unlocked mobile");
			return false;
		}
	});
	function populateLockedSale(){
		var costDifference = document.getElementById("difference_cost").value;
		<?php
		for($i = 0 ; $i <= $countlocked; $i++){?>
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
		for($i = 0 ; $i < $countunlocked; $i++){?>
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
		for($i = 0 ; $i < $countunlocked; $i++){?>
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