<?php 
	use Cake\Core\Configure;
	use Cake\Core\Configure\Engine\PhpConfig;
?>

<div class="mobilePurchases index">
	<?php
		$screenHint = $hintId = "";
					if(!empty($hint)){
					   $screenHint = $hint["hint"];
					   $hintId = $hint["id"];
					}
					$updateUrl = "/img/16_edit_page.png";
	?>
	<?php echo $this->Form->create('transientMobiles');?>
	<h2><?php echo __('Transient Mobiles')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>				
	</h2>
	<?php if(!empty($transientMobiles)){?>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th>&nbsp;</th>
			<th>Transferred From</th>
			<th>Brand</th>
			<th>Model</th>
			<th>Color</th>
			<th>Imei</th>
			<th>Transferred By</th>
			<th>Transferred On</th>
	</tr>
	</thead>
	<tbody>
	<?php $kioskName[0] = "<strong>Warehouse</strong>";
		foreach($transientMobiles as $key=>$transientMobile){
		$primaryId = $transientMobile['id'];
		if(!empty($transientMobile['transient_date'])){
			$tranDate = $transientMobile['transient_date'];
		}else{
			$tranDate = "--";
		}
		
		$tranBy = (array_key_exists($transientMobile['transient_by'],$users)) ? $users[$transientMobile['transient_by']] : '--';
			?>
	<tr>
		<td><?php echo $this->Form->input('receive', array('type' => 'checkbox', 'label' => false, 'name' => 'transientMobiles[receive][]', 'value' => $primaryId));?></td>
		<td><?php
		if($transientMobile['kiosk_id'] == 10000){
		echo "Warehouse";
		}else{
			echo $kioskName[$transientMobile['kiosk_id']];	
		}
		?></td>
		<td><?php echo $brandName[$transientMobile['brand_id']];?></td>
		<td><?php echo $modelName[$transientMobile['mobile_model_id']];?></td>
		<td><?php
        if(array_key_exists($transientMobile['color'],$colorOptions)){
            echo $colorOptions[$transientMobile['color']];
        }
        ?></td>
		<td><?php echo $transientMobile['imei'];?></td>
		<td><?php echo $tranBy;?></td>
		<td><?php echo date("d/m/y h:i:s",strtotime($tranDate));?></td>
	</tr>
	<?php } ?>
	</tbody>
	</table>
	<?php
	$options = array('name'=>'receive','label'=>'Receive');
	echo $this->Form->Submit('receive',$options);
	echo $this->Form->end();
	}else{
		echo "<h4>Currently, no mobile has been transferred to this kiosk. Please try again!</h4>";
	}
	?>	
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Mobile Purchase'), array('action' => 'add'),array('style'=>"
    width: 123px;")); ?></li>
		<?php if($this->request->session()->read('Auth.User.group_id') != KIOSK_USERS){?>
		<li><?php echo $this->Html->link(__('Mobile Stock In'), array('action' => 'bulk_mobile_purchase')); ?> </li>
		<?php } ?>
		<li><?php # echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php # echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
$(function() {
  $( document ).tooltip({
   content: function () {
    return $(this).prop('title');
   }
  });
 });
</script>
<script>
	$('input[name = "receive"]').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	});
</script>