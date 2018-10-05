<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$currency = Configure::read('CURRENCY_TYPE');
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00",'places' => 4, 'escape' => false));
?>
<meta http-equiv="expires" content="0">
<?php $unlockOptions = $unlockStatusUserOptions+$unlockStatusTechnicianOptions;
$unlockOptions[-1] = "Select Status";

?>


<div class="mobileUnlocks index">
	<?php
	if(!isset($status)){$status = "-1";}
	if(!isset($start_date)){$start_date = "";}
	if(!isset($end_date)){$end_date = "";}
	if(!isset($kioskId )){$kioskId = "";}
	if(isset($cookieKioskId)){$kioskId = $cookieKioskId;}
    //pr($kioskId);die;
	$unlock_id = $imeiSearched = $value = '';
	if(!empty($this->request->query['unlock_id'])){ $unlock_id = $this->request->query['unlock_id'];}
	if(!empty($this->request->query['search_kw'])){$value = $this->request->query['search_kw'];}
	if(!empty($this->request->query['imei'])){$imeiSearched = $this->request->query['imei'];}
	?>
	<form action='<?php echo $this->request->webroot; ?>mobile-unlocks/search' method = 'get'>
		<fieldset>
			<legend>Search</legend>
			<div>
				<table>
					<tr>
						
						<td><input type = "text" name = "unlock_id" id ='unlock_id' value ='<?= $unlock_id; ?>' placeholder = "id" style = "width:70px" autofocus/></td>
						<td><input type = "text" name = "search_kw" id ='search_kw' value ='<?= $value; ?>' placeholder = "name, model or email" style = "width:130px" autofocus/></td>
						<td><input type = "text" name = "imei" id ='imei' value ='<?=$imeiSearched;?>' placeholder = "imei" style = "width:150px" autofocus/></td>
						
					<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == UNLOCK_TECHNICIANS || $this->request->session()->read('Auth.User.group_id') == inventory_manager ||  $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
						<td><?php
						
							echo $this->Form->input(null,array('options' => $kiosks,'label' => false, 'empty' => 'Select Kiosk', 'style' => 'width:180px', 'id'=> 'kioskid', 'name' => 'MobileUnlock[kiosk_id]', 'value' => $kioskId));
						
						?></td>
					<?php } ?>
						
						<td><input type='button' name='reset' value='Reset Search' style='padding:6px 8px;color:#333;border:1px solid #bbb;border-radius:4px;' onClick='reset_search();'/></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date"  style = "width:80px;height: 25px;"value='<?php echo $start_date;?>' /></td>
						<td><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date"  style = "width:80px;height: 25px;" value='<?php echo $end_date;?>'  /></td>
						<td><?=$this->Form->input(null,array('options' => $unlockOptions,'label' => false, 'value' => $status, 'style' => 'width:180px', 'id'=> 'status', 'name' => 'MobileUnlock[status]'));
						//,'selected' => $status
						?>
						</td>
						<td>&nbsp;</td>
						<td><input type = "submit" value = "Search" name = "submit" style="margin-left: -140px;width: 132px;"/></td>
						</tr>
				</table>
			</div>
		</fieldset>	
	</form>
<script>
	
	function reset_search(){
		jQuery( "#unlock_id" ).val("");
		jQuery( "#search_kw" ).val("");
		jQuery("#imei").val("");
		jQuery("#datepicker1").val("");
		jQuery("#datepicker2").val("");
		jQuery("#kioskid").val("");
		jQuery("#status").val("-1");
	}

</script>
<?php
	$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
      $updateUrl = "/img/16_edit_page.png";
?>
	<strong style="font-size: 20px; color: red;"><?php echo __('Mobile Unlocks')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?></strong>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	<span style="float: right;"><i style="color: blue;">**Internal Booking</i></span>
	
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			
			<th><?php echo $this->Paginator->sort('kiosk_id'); ?></th>
			
			<th><?php echo $this->Paginator->sort('network_id'); ?></th>
			<th><?php echo $this->Paginator->sort('mobile_model_id'); ?></th>
			<th><?php echo $this->Paginator->sort('imei','IMEI'); ?></th>
			<th><?php echo $this->Paginator->sort('received_at','Receiving'); ?></th>
			<th><?php echo $this->Paginator->sort('delivered_at','Delivery'); ?></th>
			<th><?php echo $this->Paginator->sort('customer_fname','Cust First Name'); ?></th>			
			
			<th><?php echo $this->Paginator->sort('customer_contact','Mobile/Phone'); ?></th>
			
			<th><?php echo $this->Paginator->sort('estimated_cost'); ?></th>
			
			
			<th><?php echo $this->Paginator->sort('status'); ?></th>			
			
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
		
	<?php foreach ($mobileUnlocks as $mobileUnlock):
	//pr($mobileUnlock);
	?>
	
	<tr>
		<?php 
	 
		if($mobileUnlock->internal_unlock == 1) {?>
		   <tr style=" color: blue"> 
		  
	<?php }  ?>
		<?php if($this->request->session()->read('Auth.User.group_id')==ADMINISTRATORS ||
		 $this->request->session()->read('Auth.User.group_id') == MANAGERS){//MANAGERS?>
		<td><?php echo $this->Html->link(h($mobileUnlock->id), array('action' => 'manager_edit', $mobileUnlock->id)); ?>&nbsp;</td>
		<?php }else{ ?>
		<td><?php echo $mobileUnlock->id; ?></td>
		<?php } ?>
		<td>
			<?php echo $this->Html->link($mobileUnlock->kiosk->name, array('action' => 'view', $mobileUnlock->id),array('title'=>'View','alt'=>'View','escape' => false)); ?>
		</td>
		<td>
			<?php echo $this->Html->link($mobileUnlock->network->name, array('controller' => 'networks', 'action' => 'view', $mobileUnlock->network->id)); ?>
		</td>
		<td>
			<?php
			if(!empty($mobileUnlock->mobile_model)){
			echo $this->Html->link($mobileUnlock->mobile_model->model, array('controller' => 'mobile_models', 'action' => 'view', $mobileUnlock->mobile_model->id));
			}
			?></td>
		<td><?php echo h($mobileUnlock->imei); ?>&nbsp;</td>
		<td><?php
		if(!empty($mobileUnlock->received_at)){
			$mobileUnlock->received_at->i18nFormat(
										[\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
								);
			$received_at =   $mobileUnlock->received_at->i18nFormat('dd-MM-yyyy HH:mm:ss');
			echo date('d/m/y h:i:s',strtotime($received_at));
		}else{
			echo "--";
		}
		?>&nbsp;</td>
		<td><?php
		if(!empty($mobileUnlock->delivered_at)){
			$delivered_at = $mobileUnlock->delivered_at->i18nFormat(
										[\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
								);
			echo date('d/m/y h:i:s',strtotime($delivered_at));
		}else{
			echo "--";
		}
		?>&nbsp;</td>		
		<td><?php echo h($mobileUnlock->customer_fname); ?>&nbsp;</td>		
		
		<td><?php echo h($mobileUnlock->customer_contact); ?>&nbsp;</td>
		
		<td><?php echo $CURRENCY_TYPE.$mobileUnlock->estimated_cost; ?>&nbsp;</td>
		
		
		<td><?php echo $unlockOptions[$mobileUnlock->status]; ?>&nbsp;</td>
		
		<td>
				<?php 
				$editUrl = "/img/16_edit_page.png";
				$viewUrl = "/img/text_preview.png";
				$deleteUrl = "/img/list1_delete.png";
				$cloneUrl = "/img/fileview_close_right.png";
				?>
				<?php echo $this->Html->link($this->Html->image($viewUrl,array('fullBase' => true, 'title' => 'View','alt'=>'View')), array('action' => 'view', $mobileUnlock->id),
							     array('escapeTitle' => false, 'title' => 'View','alt'=>'View')); ?>
				<?php echo $this->Html->link($this->Html->image($editUrl,array('fullBase' => true, 'title' => 'Edit','alt'=>'Edit')), array('action' => 'edit', $mobileUnlock->id),
							     array('escapeTitle' => false, 'title' => 'Edit','alt'=>'Edit')); ?>
				<?php
					if($this->request->session()->read('Auth.User.group_id')==ADMINISTRATORS ||  $this->request->session()->read('Auth.User.group_id') == MANAGERS){
						$image_path = $this->Html->image($deleteUrl,['fullBase' => true, 'title' => 'Delete','alt'=>'Delete']);
					 echo $this->Form->postLink(__('Delete'),
												['action' => 'delete', $mobileUnlock->id],
												//['escapeTitle' => false, 'title' => 'Delete'],
												['confirm' => "Are you sure you want to delete # {$mobileUnlock->id}?"]
												);
					}
    ?>
				<?php #echo $this->Html->link($this->Html->image($cloneUrl,array('fullBase' => true)), array('action' => 'update_unlock_payment', $mobileUnlock['MobileUnlock']['id']), array('escapeTitle' => false, 'title' => 'Update payment', 'alt' => 'Update payment')); ?>
			</td>
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
	<p>
		<span style="float: right;"><i style="color: blue;">**Internal Booking</i></span>
	<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link('New Mobile Unlock',array('action'=>'add'))?></li>		
		<li><?php echo $this->element('unlock_navigation'); ?></li>		
	</ul>	
</div>
<script>
	jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
	});
</script>
<script>
$(function() {
  $( document ).tooltip({
   content: function () {
    return $(this).prop('title');
   }
  });
 });
</script>