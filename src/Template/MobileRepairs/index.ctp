<?php
    use Cake\Core\Configure;
    use Cake\Core\Configure\Engine\PhpConfig;
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00",'places' => 4, 'escape' => false));
	$repairStatusOptions = $repairStatusUserOptions+$repairStatusTechnicianOptions;
	if(!isset($repairLogDetails)){
		$repairLogDetails = array();
	}
	if(!isset($status)){$status = "";}
	if(!isset($start_date)){$start_date = "";}
	if(!isset($end_date)){$end_date = "";}
	if(!isset($kioskId )){$kioskId = "";}
	if(!empty($cookieKioskId)){$kioskId = $cookieKioskId;}
	//pr($this->Session->read());
?>
<meta http-equiv="expires" content="0">
<div class="mobileRepairs index">
	<?php
	///pr($this->request)
	$imeiSearched = $repair_id = $value = '';
	if(!empty($this->request->query['repair_id'])){ $repair_id = $this->request->query['repair_id'];}
	if(!empty($this->request->query['search_kw'])){$value = $this->request->query['search_kw'];}
	if(!empty($this->request->query['imei'])){$imeiSearched = $this->request->query['imei'];}
	?>
	<form action='<?php echo $this->request->webroot; ?>mobile-repairs/search' method = 'get'>
		<fieldset>
			<legend>Search</legend>
			<div>
				<table>
					<tr>
						<td><input type = "text" name = "repair_id" id ='repair_id' value ='<?= $repair_id; ?>' placeholder = "id" style = "width:70px" autofocus/></td>
						<td><input type = "text" name = "search_kw" id ='search_kw' value ='<?= $value; ?>' placeholder = "name, email or model" style = "width:130px" autofocus/></td>
						<td><input type = "text" name = "imei" id ='imei' value ='<?= $imeiSearched; ?>' placeholder = "imei" style = "width:150px" autofocus/></td>
						<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS || $this->request->session()->read('Auth.User.group_id') == inventory_manager){
							?>
							<td>
							<?php
						 echo $this->Form->input(null,array('options' => $kiosks,'label' => false, 'empty' => 'Select Kiosk', 'style' => 'width:180px', 'id'=> 'kioskid', 'name' => 'MobileRepair[kiosk_id]', 'value' => $kioskId));
					
						 ?></td>
					<?php }else{ ?>
						<td>&nbsp;</td>
					<?php } ?>
					<td><input type='button' name='reset' value='Reset Search' style='padding:6px 8px;color:#333;border:1px solid #bbb;border-radius:4px;width: 95px;' onClick='reset_search();'/></td>
					</tr>
					<tr>
						<td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date"  style = "width:80px;height: 25px;"value='<?php echo $start_date;?>' /></td>
						<td><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date"  style = "width:80px;height: 25px;" value='<?php echo $end_date;?>'  /></td>
						<td><?=$this->Form->input(null,array('options' => $repairStatusOptions,'label' => false, 'empty' => 'Select Status', 'style' => 'width:180px', 'id'=> 'status', 'name' => 'MobileRepair[status]', 'value' => $status));
						?>
						</td>
						<td>&nbsp;</td>
						<td><input type = "submit" value = "Search" name = "submit"/></td>
					</tr>
				</table>
				
				
			</div>
		</fieldset>	
	</form>
<script>
	
	function reset_search(){
		jQuery( "#repair_id" ).val("");
		jQuery( "#search_kw" ).val("");
		jQuery("#imei").val("");
		jQuery("#datepicker1").val("");
		jQuery("#datepicker2").val("");
		jQuery("#kioskid").val("");
		jQuery("#status").val("");
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
	<strong style="font-size: 20px; color: red;"><?php echo __('Mobile Repairs')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?></strong>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>				
	<span style="float: right;"><i style="color: blue;">**Internal Booking</i> <i style="color: red;">**Rebooked</i></span>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			
			<th><?php echo $this->Paginator->sort('kiosk_id'); ?></th>
			<th><?php echo $this->Paginator->sort('imei','IMEI'); ?></th>
			<th><?php echo $this->Paginator->sort('model','Model'); ?></th>
			<th><?php echo $this->Paginator->sort('customer_fname','Cust First Name'); ?></th>
			
			<th>Parts Used</th>
			
			<th><?php echo $this->Paginator->sort('estimated_cost'); ?></th>
			
			<th><?php echo $this->Paginator->sort('status'); ?></th>
			<th>Technician</th>
			
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	//pr($mobileRepairs);die;
	foreach ($mobileRepairs as $mobileRepair):
		$sum = 0;
		$estimatedCostArr = explode('|',$mobileRepair->estimated_cost);
		foreach($estimatedCostArr as $ki => $estimatedCost){			
			$sum += $estimatedCost;
		}
		
	?>
	
	<?php if($mobileRepair->status_rebooked == 1){?>
		<tr style="color: red">
	<?php }else{?>
	<tr>
	<?php } ?><?php 
	 
		if($mobileRepair->internal_repair == 1) {?>
		   <tr style=" color: blue"> 
		 
	<?php }  ?>
	<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
		 $this->request->session()->read('Auth.User.group_id') == MANAGERS){?>
		<td><?php echo $this->Html->link(h($mobileRepair->id), array('action' => 'manager_edit',$mobileRepair->id)); ?>&nbsp;</td>
		<?php }else{?>
		<td><?php echo $this->Html->link(h($mobileRepair->id), array('controller' => 'mobileRepairLogs', 'action' => 'view_logs',$mobileRepair->id),array('alt'=>'View Repair Logs', 'escapeTitle'=>false, 'title'=>'View Repair Logs')); ?>&nbsp;</td>
		<?php } ?>
		<td>
			<?php echo $this->Html->link($mobileRepair->kiosk['name'], array('action' => 'view', $mobileRepair->id),array('title'=>'View','alt'=>'View','escape' => false)); ?>
		</td>
		
		<td><?php echo h($mobileRepair->imei); ?>&nbsp;</td>
		<td><?php if(array_key_exists($mobileRepair->mobile_model_id,$mobileModels)){
            echo $mobileModels[$mobileRepair->mobile_model_id];
        }else{
            echo "--";
        }?>&nbsp;</td>
		<td><?php echo h($mobileRepair->customer_fname); ?>&nbsp;</td>
		<td><?php
		if(array_key_exists($mobileRepair->id, $repairPartArr )){
			if($repairPartArr[$mobileRepair->id] > 1){
				echo "<b>".$repairPartArr[$mobileRepair->id]."</b>";
			}else{
				echo $repairPartArr[$mobileRepair->id];
			}
		}else{
			echo "--";
		}
		?>
		
		<td><?php echo $CURRENCY_TYPE.$sum;   ?>&nbsp;</td>
		
		
		<td><?php
		// pr($repairStatusOptions);//die;
		if(array_key_exists($mobileRepair->status,$repairStatusOptions)){
			echo $repairStatusOptions[$mobileRepair->status];//$repairStatusOptions[$mobileRepair->status]; 
		}?>&nbsp;
		</td>
		<td><?php
			if(array_key_exists($mobileRepair->id,$repairLogDetails)){
				echo $repairLogDetails[$mobileRepair->id][0];
			}?></td>
			<?php
			  $mobileRepair->modified->i18nFormat(
										[\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
								);
			  $modified = $mobileRepair->modified->i18nFormat('dd-MM-yyyy HH:mm:ss');
			?>
		<td><?php echo date('d/m/y h:i:s',strtotime($modified));//$this->Time->format('jS M, Y',$mobileRepair->modified,null,null); ?>&nbsp;</td>
		<td>
			<?php 
			$editUrl = "/img/16_edit_page.png";
			$viewUrl = "/img/text_preview.png";
			$viewPartsUrl = "/img/view_parts.png";
			$deleteUrl = "/img/list1_delete.png";
			$cloneUrl = "/img/fileview_close_right.png";
			?>
			<?php echo $this->Html->link($this->Html->image($viewUrl,array('fullBase' => true,'title' => 'View', 'alt' => 'View')), array('action' => 'view', $mobileRepair->id),
						     array('escapeTitle' => false, 'title' => 'View', 'alt' => 'View')); ?>
			<?php echo $this->Html->link($this->Html->image($editUrl,array('fullBase' => true,'title' => 'Edit', 'alt' => 'Edit')), array('action' => 'edit', $mobileRepair->id),
						     array('escapeTitle' => false, 'title' => 'Edit', 'alt' => 'Edit')); ?>
			
			<?php
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){
					echo $this->Form->postLink(
							$this->Html->image($deleteUrl,
							array("alt" => ('Delete'), "title" => ('Delete'))), 
							array('action' => 'delete', $mobileRepair->id), 
							array('escape' => false, 'confirm' => __('Are you sure you want to delete # %s?', $mobileRepair->id)) 
					);
				}
			?>
			<?php
			if($viewRepairParts){
				foreach($viewRepairParts as $viewRepairPart){
				//pr($viewRepairPart);die;
				if($viewRepairPart['mobile_repair_id'] == $mobileRepair->id){
						echo $this->Html->link($this->Html->image($viewPartsUrl,array('fullBase' => true,'title' => 'View Parts', 'alt' => 'View Parts')), array('action' => 'view_repair_parts', $mobileRepair->id),
							     array('escapeTitle' => false, 'title' => 'View Parts', 'alt' => 'View Parts'));
						break;
					}
				}
			
			}?>
			<?php #echo $this->Html->link($this->Html->image($cloneUrl,array('fullBase' => true)), array('action' => 'update_repair_payment', $mobileRepair['MobileRepair']['id']),
							     #array('escapeTitle' => false, 'title' => 'Update payment', 'alt' => 'Update payment')); ?>
			<?php #echo $this->Html->link('View Logs', array('controller' => 'mobile_repair_logs', 'action'=> 'view_logs', $mobileRepair['MobileRepair']['id'])) ;?>	
			<?php //echo $this->Form->postLink($this->Html->image($deleteUrl,array('fullBase' => true)), array('action' => 'delete', $mobileRepair['MobileRepair']['id']),
							// array('escapeTitle' => false, 'title' => 'Delete'), __('Are you sure you want to delete # %s?', $mobileRepair['MobileRepair']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
	<p>
		<span style="float: right;"><i style="color: blue;">**Internal Booking</i> <i style="color: red;">**Rebooked</i></span>
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
		<li><?php echo $this->Html->link(__('New Mobile Repair'), array('action' => 'add')); ?></li>
		<li><?php #echo $this->Html->link(__('List Kiosks'), array('controller' => 'kiosks', 'action' => 'index')); ?> </li>
		<li><?php #echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>			
		<li><?php echo $this->element('repair_navigation'); ?></li>		
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