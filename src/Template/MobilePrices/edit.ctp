<div class="mobilePrices form">
<?php //pr($mobilePriceData);
echo $this->Form->create('MobilePrice');
$statusArray = array(0 => 'Inactive', 1 => 'Active');
?>
	<fieldset>
			<legend><?php echo __('Edit Mobile Price ').$this->Form->input('status', array('options' => $statusArray, 'div' => false, 'label' => false, 'style' => "font-size: 13px;", 'default' => $mobilePriceData[0]['status'])); ?></legend>
	<table>
		<?php $discountStatusArr = array();
		$maxDiscountArr = array();
		$topupStatusArr = array();
		$maxTopupArr = array();
		//pr($mobilePriceData);
		foreach($mobilePriceData as $key=>$mobilePrices){
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
			<th>Brand</th>
			<th>Model</th>
			<th>Type</th>
			<th>Grade</th>
			<th>Cost Price</th>
			<th>Sale Price</th>
		</tr>
	<?php
	$count = 0;
	foreach($mobilePriceData as $key=>$mobilePrices){
		//pr($mobilePrices);
		$count++;
	?>
		<tr>
			<td><?php echo $brands[$mobilePrices['brand_id']];?>
			<?php echo $this->Form->input('id',array('type'=>'hidden','value'=>$mobilePrices['id'],'name'=>"MobilePrice[id][]"));?>
			<?php echo $this->Form->input('locked',array('type'=>'hidden','value'=>$mobilePrices['locked'],'name'=>"MobilePrice[locked][]"));?>
			</td>
			<td><?php echo $mobileModelName[$mobilePrices['mobile_model_id']];?></td>
			<td><?php echo $type[$mobilePrices['locked']];?></td>
			<td><?php echo $gradeType[$mobilePrices['grade']];?></td>
			<td><?php echo $this->Form->input('cost_price',array('type'=>'text','value'=>$mobilePrices['cost_price'],'name'=>"MobilePrice[cost_price][]"));?></td>
			<td><?php echo $this->Form->input('sale_price',array('type'=>'text','value'=>$mobilePrices['sale_price'],'name'=>"MobilePrice[sale_price][]"));?></td>
		</tr>
	<?php } ?>
	</table>
<?php
echo $this->Form->submit('Submit' , array('name'=>'submit'));
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
</script>