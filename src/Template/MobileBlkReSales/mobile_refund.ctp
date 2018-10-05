<div class="mobileBlkReSales index">
	<h2><?php echo __('Mobile Refund');?></h2>
	<?php  //pr($this->request->data);
	echo $this->Form->create('MobileRefund');
	$kiosk_id = $this->request->Session()->read('kiosk_id');
	$refund_by = $this->request->Session()->read('Auth.User.id');
	$mobileResaleData = $this->request->data;
	$mobile_model =  $modelName[$mobileResaleData['mobile_model_id']];
	echo $this->Form->input('mobile_purchase_id',array('type'=>'hidden'));
	echo $this->Form->input('refund_by',array('type'=>'hidden','value'=>$refund_by));
	echo $this->Form->input('mobile_model_id',array('type'=>'hidden','value'=>$mobile_model));
	echo $this->Form->input('refund_status',array('type'=>'hidden','value'=>1));
	echo $this->Form->input('kiosk_id',array('type'=>'hidden','value'=>$kiosk_id));
	
	if($mobileResaleData['discounted_price']>0){
		$sellingPrice = $mobileResaleData['discounted_price'];
	}else{
		$sellingPrice = $mobileResaleData['selling_price'];
	}
	echo $this->Form->input('sale_price',array('type'=>'hidden','value'=>$sellingPrice,'name'=>'sale_price'));
	if($mobileResaleData['network_id']==""){
		$network = "--";
	}else{
        if(array_key_exists($mobileResaleData['network_id'],$networkName)){
            $network = $networkName[$mobileResaleData['network_id']];
        }else{
            $network = "--";
        }
		
	}
	?>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<td><strong>Sold On</strong></td>
			<td><?php echo date('M jS, Y', strtotime($mobileResaleData['created']));?></td>
	</tr>
	<tr>
			<td><strong>Sold By</strong></td>
			<td><?php echo $userName[$mobileResaleData['user_id']];?></td>
	</tr>
	<tr>
			<td><strong>Brand</strong></td>
			<td><?php echo $brandName[$mobileResaleData['brand_id']];?></td>
	</tr>
	<tr>
			<td><strong>Model</strong></td>
			<td><?php echo $modelName[$mobileResaleData['mobile_model_id']];?></td>
	</tr>
	<tr>
			<td><strong>Grade</strong></td>
			<td><?php echo $mobileResaleData['grade'];?></td>
	</tr>
	<tr>
			<td><strong>Type</strong></td>
			<td><?php echo $type[$mobileResaleData['type']];?></td>
	</tr>
	<tr>
			<td><strong>Network</strong></td>
			<td><?php echo $network;?></td>
	</tr>
	<tr>
			<td><strong>Selling Price</strong></td>
			<td><?php echo $CURRENCY_TYPE.$sellingPrice;?></td>
	</tr>
	<tr>
			<td><strong>Reason for refund</strong></td>
			<td><input type="text" name="refund_remarks"></td>
	</tr>
	<tr>
			<td><strong>Refund Amount</strong></td>
			<td><input type="text" name="refund_price"></td>
	</tr>
	</table>
	<?php
	echo $this->Form->submit('Submit',['name'=>'submit1']);
	echo $this->Form->end()?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		
		<li><?php echo $this->Html->link(__('View Mobile Sale'), array('controller'=>'mobile_re_sales','action' => 'index')); ?></li>
		
	</ul>
</div>
<script>
	$('input[name = "submit1"]').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	});
</script>