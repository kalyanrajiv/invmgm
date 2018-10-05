<style>
	.disablelink{
		pointer-events: none;
		cursor: default;
	}
	.enablelink{
		pointer-events: auto;
		cursor: pointer;
	}
	.change_colour{
		color : blue;
	}
	.default_colour{color : black;}
</style>

<?php
	 //pr($_SESSION);die;
	//sort($kiosks);
?>
<div class="mobilePurchases index">
	
	<?php $search_kw = "";
	$selectedKiosk = '';
	$kiosk_id = $this->request->session()->read('kiosk_id');
	if(array_key_exists('selectedKiosk',$this->request->session()->read())){
		$selectedKiosk =$this->request->session()->read('selectedKiosk');
	}
	$checked = "";
	if(!empty($this->request->query)&& array_key_exists('search_kw',$this->request->query))$search_kw = $this->request->query['search_kw'];
	$transient_status = $Reserve_status = '';
	if(count($this->request->query)){
		if(array_key_exists('transient_status', $this->request->query)){
			$transient_status = $this->request->query['transient_status'];
		}else{
			$transient_status = '';
		}
		
		if(array_key_exists('Reserve_status', $this->request->query)){
			$Reserve_status = $this->request->query['Reserve_status'];
		}else{
			$Reserve_status = '';
		}
	}
	?>
	
	
	
	<form action='<?php echo $this->request->webroot; ?>mobilePurchases/search_globally' method = 'get'>
		<fieldset>
			<legend>Search</legend>
			<div>
				<?php
				
					if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
					   $this->request->session()->read('Auth.User.group_id') == MANAGERS ||
					   $this->request->session()->read('Auth.User.group_id') == inventory_manager //add on 7th July,2016 on Inders request
					)
					{
                        
						$optionStr = "<option value='-1'>All</option>";
						foreach($kiosks as $kioskID => $kioskValue){
							$selected = "";
							if(array_key_exists($kioskID,  $selectedkioskArr)){
								$selected = "selected";
							}
							$optionStr.="<option value ='$kioskID' $selected>$kioskValue</option>";
						}
						$transientStatus = array('-1'=>'All','1' => 'Transient', '0' => 'Non-Transient');
						$attributes = array('legend' => false);
						$reserveStatus = array('0'=>'All','1' => 'Reserved', '2' => 'Unreserved');
						echo "<table cellspacing='4' cellpadding='4' style='width:700px;'>";
						echo "<tr>";
						//echo "<td> <input type = 'text' name = 'search_kw' id = 'search_kw' placeholder = 'model or imei' style = 'width:150px'  autofocus value = $search_kw > </td>";
						//echo "<td>  <input type = 'radio' name = 'Select Transient' id = '-1'  value = 'All' >All </td>";
							echo " <td> <input type = 'text' name = 'search_kw' id = 'search_kw' placeholder = 'model or imei ' style = 'width:150px'  autofocus value = $search_kw > </td> "; ?>
						 
							<td> <input type = 'radio' name = 'transient_status'  id = 'transient_1'  value='' <?=$checked = (empty($transient_status)) ? 'checked' : ''?>>All </td>
							<td> <input type = 'radio' name = 'transient_status' id = 'transient_2'  value = '1' <?=$checked = ($transient_status == 1) ? 'checked' : ''?>>Transient </td>
							<td> <input type = 'radio' name = 'transient_status' id = 'transient_3'  value = '0' <?=$checked = (is_numeric($transient_status) && $transient_status == 0) ? 'checked' : ''?>>Non-Transient</td>
							<?php echo "<td ><input type = 'submit' value = 'Search Mobile Purchase' name = 'submit'/><br/><br/>";
						//	echo "<td><input type='button' name='reset' value='Reset Search' style='padding:4px 8px;width:hecked120px ;color:#333;border:1px solid #bbb;border-radius:4px;' onClick='reset_search();'/></td>";
							echo "<tr>";
							 echo "<td><select name='kiosk_ids[]' id = 'kiosk_ids'  multiple='multiple' size='5' style='width:150px;'>$optionStr </select>	</td> ";?>
							<td> <input type = 'radio' name = 'Reserve_status'  id = 'Reserve_1'  value = '' <?=$checked = (empty($Reserve_status)) ? 'checked' : ''?>>All</td>
							<td> <input type = 'radio' name = 'Reserve_status' id = 'Reserve_2'  value = '1' <?=$checked = ($Reserve_status == 1) ? 'checked' : ''?>>Reserved </td>
							<td> <input type = 'radio' name = 'Reserve_status' id = 'Reserve_3'  value = '2' <?=$checked = (is_numeric($Reserve_status) && $Reserve_status == 2) ? 'checked' : ''?>>Unreserved</td>
							<?php echo "<td><input type='button' name='reset' value='Reset Search' style='padding:4px 8px;width:120px ;color:#333;border:1px solid #bbb;border-radius:4px;' onClick='reset_search();'/></td></tr> ";
						 
						 
						echo "</table>";
					}else{
						echo "<table cellspacing='4' cellpadding='4' style='width:700px;'>";
						echo "<tr>";
						echo "<td> <input type = 'text' name = 'search_kw' id = 'search_kw' placeholder = 'model or imei or brand' style = 'width:450px'  autofocus value = $search_kw > </td>";
					
						echo "<td rowspan='4'><input type = 'submit' value = 'Search Mobile Purchase' name = 'submit'/><br/><br/>";
						echo "<td><input type='button' name='reset' value='Reset Search' style='padding:4px 8px;width:220px ;color:#333;border:1px solid #bbb;border-radius:4px;' onClick='reset_search();'/></td>";
						 
						echo "</table>";
					}
				
				?>
				 
				
			</div>
		</fieldset>	
	</form>
	<?php
			$screenHint = $hintId = "";
					if(!empty($hint)){
						
					   $screenHint = $hint["hint"];
					   $hintId = $hint["id"];
					}
					$updateUrl = "/img/16_edit_page.png";
	?>
	<h2><?php echo ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER) ? "Global search<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>" : 'Global Mobile Search'; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>				
	</h2>
     
	<?php echo $this->Form->create('TransferMobile', ['url' => ['action' => 'global_search']]);
   // echo $this->Form->create('TransferMobile', array('url' => array('controller' => 'mobile_purchases', 'action' => 'global_search')));
	
	if ($this->request->session()->read('Auth.User.group_id')!= KIOSK_USERS){
		//$kiosks['0'] = "Warehouse";
		 echo $this->Form->input('kiosk',array('name' => 'TransferMobile[kiosk]','options'=>$kiosks,'empty'=>'Choose','default'=>$selectedKiosk));
	}
	?>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('kiosk_id','Current Location'); ?></th>
			<th><?php echo $this->Paginator->sort('purchased_by_kiosk','Original Location'); ?></th>
			<th><?php echo $this->Paginator->sort('brand_id'); ?></th>
			<th><?php echo $this->Paginator->sort('mobile_model_id'); ?></th>
			<th><?php echo $this->Paginator->sort('color'); ?></th>
			<th><?php echo $this->Paginator->sort('imei', 'IMEI'); ?></th>
			<?php
				if(
					 $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
					$this->request->session()->read('Auth.User.group_id') == MANAGERS ||
					$this->request->session()->read('Auth.User.group_id') == inventory_manager || 
					$this->request->session()->read('Auth.User.group_id') == KIOSK_USERS
					){
				?>
				<th><?php echo $this->Paginator->sort('selling_price'); ?></th>
			<?php } ?>
			<th><?php echo $this->Paginator->sort('grade'); ?></th>
			<th><?php echo $this->Paginator->sort('type'); ?></th>
			<th><?php echo $this->Paginator->sort('network_id'); ?></th>
			<th><?php echo $this->Paginator->sort('new_kiosk_id','Destination'); ?></th>
			 
			<?php if ($this->request->session()->read('Auth.User.group_id') != KIOSK_USERS){ ?>
			<th class="actions"><?php echo __('Current Status'); ?></th>
			<?php }  ?>
	</tr>
	</thead>
	<tbody>
	<?php if ($this->request->session()->read('Auth.User.group_id') != KIOSK_USERS){ ?>
	<tr>
		<td colspan='8'>
			<div class="submit">
				<input type="submit" value="Add to basket" name="add_2_basket" id="add_2_basket">
				<input type="submit" value="Clear basket" name="clear_basket">
				<input type="submit" value="Checkout" name="check_out">
				<input type="submit" value="Transfer mobile" name="transfer_mobile" id="transfer_mobile">
			</div>
		</td>
	</tr>
	<?php } ?>
	<?php // pr($mobilePurchases);die;
	foreach ($mobilePurchases as $mobilePurchase):
	$lockedUnlocked = $mobilePurchase['type'];
	if($lockedUnlocked==0){
		$network_id = '0';
	}else{
		$network_id = $mobilePurchase['network_id'];
	}
	$receiving_status = $mobilePurchase['receiving_status'];
	$currentStatus = $mobilePurchase['status'];
	$newKioskId = $mobilePurchase['new_kiosk_id'];
	if($newKioskId==""){
		$newKiosk = "--";
	}else{
		 if(array_key_exists($newKioskId,$kiosks)){
			if($newKioskId == 10000){
				$newKiosk = "Warehouse";
			}else{
				if(array_key_exists($newKioskId,$kiosks)){
					$newKiosk = $kiosks[$newKioskId];
				}else{
					$newKiosk = "<b style='background-color: yellow;'>Unassigned Kiosk (ID: {$newKioskId} )</b>";
				}
				
			}
		}else{
			$newKiosk = "<b style='background-color: yellow;'>Unassigned Kiosk (ID: {$newKioskId} )</b>";
		}
	  	
	}
	// status: reserved => 2, available=> 0, sold => 1
	if(array_key_exists('chosenImeis',$this->request->session()->read())){
		if(is_array($this->request->session()->read('chosenImeis'))){
			$chosenImeis = $this->request->session()->read('chosenImeis');
			$purchaseIdArr = array();
			foreach($chosenImeis as $purchaseId=>$chosenImei){
				$purchaseIdArr[$purchaseId] = $purchaseId;
			}
			if(in_array($mobilePurchase['id'],$purchaseIdArr)){
				$checked = "checked";
			}else{
				$checked = "";
			}
		}
	}
	if(empty($mobilePurchase['Kiosk']['name'])){
		$kioskName = "Warehouse";
	}else{
		$kioskName = $mobilePurchase['Kiosk']['name'];
	}
	$imeiNum = $mobilePurchase['imei'];
	$purchaseId = $mobilePurchase['id'];
	?>
	<?php if($currentStatus==2){?>
	<tr style="background-color: yellow;">
	<?php }else{ ?>
	<tr>
	<?php }
		if(
		   $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
		   $this->request->session()->read('Auth.User.group_id') == MANAGERS ||
		   $this->request->session()->read('Auth.User.group_id') == inventory_manager //Added on July 7,2016 after Inder's Request
		   ) 
		   {
			$editLink = $this->Html->link(h($mobilePurchase['id']), array('action' => 'edit', $mobilePurchase['id']));
			if($mobilePurchase['custom_grades'] == 1 && $mobilePurchase['purchase_status'] == 1){
				$editLink = $this->Html->link($mobilePurchase['id'],
											  array('action' => 'edit', $mobilePurchase['id']),
											  array('style' => 'color:red;')
											  );
			}
	?>
		<td><?php echo  $editLink;?>&nbsp;
		</td>
		<?php }else{ ?>
		<td><?php echo $mobilePurchase['id']; ?></td>
		<?php } ?>
		<td>
			<?php
			if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
				$this->request->session()->read('Auth.User.group_id') == MANAGERS ||
				$this->request->session()->read('Auth.User.group_id') == inventory_manager){
				if($mobilePurchase['kiosk_id']==0){
					echo "Warehouse";
				}else{
					//pr($mobilePurchase);
					echo $this->Html->link($mobilePurchase['kiosk']['name'], array('controller' => 'mobile_purchases', 'action' => 'view', $mobilePurchase['id']), array('title' => 'view', 'alt' => 'view'));
					}
			}else{
				echo $mobilePurchase['kiosk']['name'];
			}
			 ?>
		</td>
		<td><?php if($mobilePurchase['purchased_by_kiosk'] == 10000){
			echo "Warehouse";
		}else{
			if(array_key_exists($mobilePurchase['purchased_by_kiosk'],$kiosks)){
				echo $kiosks[$mobilePurchase['purchased_by_kiosk']];
			}else{
				echo "--";
			}
			//echo $mobilePurchase['purchased_by_kiosk'];
			
		}
			?></td>
		<td>
			<?php echo $this->Html->link($mobilePurchase['brand']['brand'], array('controller' => 'brands', 'action' => 'view', $mobilePurchase['brand']['id'])); ?>
		</td>
		<td><?php echo h($mobileModels[$mobilePurchase['mobile_model_id']]); ?>&nbsp;</td>
		<td><?php if(array_key_exists($mobilePurchase['color'],$colorOptions)){
            echo h($colorOptions[$mobilePurchase['color']]);
        }?>&nbsp;</td>
		<td><?php echo $this->Html->link($mobilePurchase['imei'],array('action'=>'mobile_transfer_logs',$mobilePurchase['imei']),array('title'=>'Logs','alt'=>'Logs')); ?>&nbsp;</td>
		<?php  
			if(
				$this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
				$this->request->session()->read('Auth.User.group_id') == MANAGERS ||
				$this->request->session()->read('Auth.User.group_id') == inventory_manager ||//Added on July 7, 2016 on Inder's request
				$this->request->session()->read('Auth.User.group_id') == KIOSK_USERS
			){
			?>
			<td><?php $sale_price = h($mobilePurchase['selling_price']);
			#echo $this->Number->currency($cost_price,'BRL');
			if(!empty($sale_price) && $sale_price != 0){
				echo $CURRENCY_TYPE.$sale_price;
			}else{
				if(array_key_exists($mobilePurchase['id'],$salePrice)){
					$sale_price = $salePrice[$mobilePurchase['id']];
					echo $CURRENCY_TYPE.$sale_price;
				}
				//$cost_price = h($mobilePurchase['selling_price']);
				
			}
			?>
			 &nbsp;</td>
		<?php }  ?>
		<td><?php
		if(array_key_exists($mobilePurchase['grade'],$gradeType)){
			echo h($gradeType[$mobilePurchase['grade']]); 
		}else{
			echo h($mobilePurchase ['grade']); 
		} 
		?>&nbsp;</td>
		<td><?php echo h($type[$mobilePurchase['type']]); ?>&nbsp;</td>
		<td><?php
					if(array_key_exists($network_id, $networks)){
						echo h($networks[$network_id]);
					}else{
						echo "---";
					}
				?>&nbsp;</td>
		<td><?php
		if(($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
			$this->request->session()->read('Auth.User.group_id') == inventory_manager ||
			$this->request->session()->read('Auth.User.group_id') == MANAGERS) &&
		   $currentStatus==0 && $receiving_status == 0
		   ){
			echo $this->Form->input('ksk',array('options'=>$kiosks,'empty'=>'Choose', 'label' => false, 'id' => "kiosk_drop_".$mobilePurchase['id'],'onChange' => "enable_reserve_button(this.id)"));
		}else{
			if($mobilePurchase['status'] == 2){
				$reservedBy = (array_key_exists($mobilePurchase['reserved_by'],$users)) ? 'Reserved by '.$users[$mobilePurchase['reserved_by']] : '--';
				$reserveDate = ($mobilePurchase['reserve_date'] != '0000-00-00 00:00:00' && $mobilePurchase['reserve_date'] != "") ? 'on '.$mobilePurchase['reserve_date'] : '--';
				echo "<span title=\"$reservedBy $reserveDate\">".$newKiosk."</span>";
			}elseif($mobilePurchase['receiving_status'] == 1){
				$reservedBy = (array_key_exists($mobilePurchase['transient_by'],$users)) ? 'Moved by '.$users[$mobilePurchase['transient_by']] : '--';
				$reserveDate = ($mobilePurchase['transient_date'] != '0000-00-00 00:00:00' && $mobilePurchase['transient_date'] != "") ? 'on '.$mobilePurchase['transient_date'] : '--';
				echo "<span title=\"$reservedBy $reserveDate\">".$newKiosk."</span>";
			}else{
				echo $newKiosk;
			}
		}
		?></td>
		<?php #if ($this->request->session()->read('Auth.User.group_id') != KIOSK_USERS){
			if($receiving_status==1){
		?>
			<td><strong>Transient</strong></td>
		<?php }elseif($currentStatus==2 &&
			      $kiosk_id==$mobilePurchase['kiosk_id']){?>
			<td><?php echo $this->Form->input('Transfer',array('type'=>'button','label'=>false,'name'=>"TransferMobile[transfer_reserved][$purchaseId]",'value'=>$newKioskId,'style'=>"padding: 2px 4px 2px 4px;cursor: pointer;border-radius: 4px;border: 1px solid #bbb;",'div'=>false)); ?>&nbsp;</td>
		<?php }elseif($currentStatus == 3 ){
			echo "<td><strong>Internal unlock</strong></td>";
		}elseif($currentStatus == 4 ){
			echo "<td><strong>Internal repair</strong></td>";
		}
		else{
			if(
			   ($this->request->session()->read('Auth.User.group_id') == MANAGERS ||
				$this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
				$this->request->session()->read('Auth.User.group_id') == inventory_manager) && //add on 7th July,2016 on Inders request &&
			   $mobilePurchase['kiosk_id'] == 0 || $mobilePurchase['kiosk_id'] == 10000
			   ){
			?>
			<td><?php echo $this->Form->input('Transfer',array('type'=>'checkbox','label'=>false,'name'=>"TransferMobile[transfer][$purchaseId]",'value'=>$mobilePurchase['imei'],'checked'=>$checked)); ?>&nbsp;</td>
		<?php	}else{?>
				<td>&nbsp;</td>
			<?php }
		}
		#} ?>
		 
		<td class="actions" style="padding: 14px;">
		<?php if (
					$this->request->session()->read('Auth.User.group_id') == MANAGERS ||
					$this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
					$this->request->session()->read('Auth.User.group_id') == inventory_manager //add on 7th July,2016 on Inders request) &&
				){
				if($currentStatus==0 && $receiving_status==0){
			?>
			<?php #echo $this->Html->link(__('Reserve'), array('action' => 'reserve', $mobilePurchase['id']),array('id' => 'res_'.$mobilePurchase['id'], 'class' => 'disablelink reserveclass', 'onclick' => "return change_link(this.id);"));?>
			<a href="/MobilePurchases/reserve/<?= $mobilePurchase['id'] ?>" id="res_<?= $mobilePurchase['id'] ?>" class=" disablelink reserveclass" onclick="return change_link(this.id);"><span id='span_<?= $mobilePurchase['id'] ?>' class='default_colour'>Reserve</span></a>
		
			<?php }elseif($currentStatus==2){
				echo $this->Html->link(__('Unreserve'), array('action' => 'unreserve', $mobilePurchase['id']),array('id' => 'unres_'.$mobilePurchase['id'],'class'=>'unreserve_loading'));
			}
		}?>
		</td>
		<td class="actions">
			<?php #echo $this->Html->link(__('Sell'), array('controller'=>'mobile_re_sales','action' => 'add', $mobilePurchase['id'])); ?>
			<?php #echo $this->Html->link(__('Edit'), array('action' => 'edit', $mobilePurchase['id'])); ?>
			<?php #echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $mobilePurchase['id']), array(), __('Are you sure you want to delete # %s?', $mobilePurchase['id'])); ?>
		</td>
	</tr>
<?php endforeach;?>
	<?php if ($this->request->session()->read('Auth.User.group_id') != KIOSK_USERS){ ?>
	<tr>
		<td colspan='8'>
			<div class="submit">
				<input type="submit" value="Add to basket" name="add_2_basket" id="add_to_basket">
				<input type="submit" value="Clear basket" name="clear_basket">
				<input type="submit" value="Checkout" name="check_out">
				<input type="submit" value="Transfer mobile" name="transfer_mobile" id="transfr_mobile">
			</div>
		</td>
	</tr>
	<?php } ?>
	</tbody>
	</table>
	<p>
		<i>***Highlitghted rows are reserved******</i>
	<?php
	echo "</br>";
	echo "</br>";
	echo "</br>";
	 
	
	?>	  </p>
	 <div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Mobile <br/> Purchase'), array('action' => 'add'),['escape'=>false]); ?></li>
		<li><?php echo $this->Html->link(__('Mobile Stock In'), array('action' => 'bulk_mobile_purchase')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php # echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
	$('.unreserve_loading').click(function(){
		$.blockUI({ message: 'Just a moment...' });
		});
</script>
<script>
	$('#add_2_basket').click(function(){
		var kiosk = $('#TransferMobileKiosk').val();
		if(kiosk==""){
			alert("Please choose a kiosk to transfer the phones!");
			return false;
		}
	});
	
	$('#transfer_mobile').click(function(){
		var kiosk = $('#TransferMobileKiosk').val();
		if(kiosk==""){
			alert("Please choose a kiosk to transfer the phones!");
			return false;
		}else{
			$.blockUI({ message: 'Just a moment...' });
		}
	});
	
	$('#add_to_basket').click(function(){
		var kiosk = $('#TransferMobileKiosk').val();
		if(kiosk==""){
			alert("Please choose a kiosk to transfer the phones!");
			return false;
		}
	});
	
	$('#transfr_mobile').click(function(){
		var kiosk = $('#TransferMobileKiosk').val();
		if(kiosk==""){
			alert("Please choose a kiosk to transfer the phones!");
			return false;
		}else{
			$.blockUI({ message: 'Just a moment...' });
		}
	});
</script>
<script>
	/*$(".reserveclass").click(function(){
		var chosenKiosk = $('#TransferMobileKiosk').val();
		if(chosenKiosk==""){
			alert("Please choose kiosk to reserve the phone!");
			return false;
		}else{
			var url = $(this).attr("href");
		var newurl = url + '/' + chosenKiosk;
		location.href = newurl;
		return false;//so that it does not pick the original url
		}
	});*/
	
	function change_link(id) {
		var thenum = id.replace( /^\D+/g, '');
        var chosenKiosk = $('#kiosk_drop_' + thenum).val();
		if(chosenKiosk=="" || chosenKiosk == null){
			alert("Please choose kiosk to reserve the phone!");
			return false;
		}else{
			$.blockUI({ message: 'Just a moment...' });
			var url = $('#' + id).attr("href");
		var newurl = url + '/' + chosenKiosk;
		location.href = newurl;
		//alert(newurl);
		return false;//so that it does not pick the original url
		}
    }
	
	
	function reset_search(){
		jQuery("input[name='transient_status'][value='']").prop("checked", true);
		//[value='']
		//alert(jQuery('input[name=transient_status]:checked').index());
		jQuery("input[name='Reserve_status'][value='']").prop("checked", true);
		//[value='']
		 
		//jQuery("#reserve_status").val("");
		jQuery("#kiosk_ids").val("-1");
		jQuery("#search_kw").val("");
	}
	
	
	
	
	function enable_reserve_button(id) {
		var thenum = id.replace( /^\D+/g, '');
		var kioskId = $("#" + id).val();
		if (kioskId != '') {
			$("#res_" + thenum).removeClass('disablelink');
			$("#span_" + thenum).removeClass('default_colour');
            $("#res_" + thenum).addClass('enablelink');
			//$("#res_" + thenum).addClass('change_colour');   //document.getElementById("#res_").style.color = "blue";  //document.getElementById("#res_" + thenum).className = "change_colour"
			//$("#res_" + thenum).delay(0).addClass('change_colour');
			$("#span_"+thenum).addClass('change_colour');
			//alert($("#res_" + thenum).attr('class'));
			
			
        } else if (kioskId == '' || kioskId == null) {
			$("#span_" + thenum).removeClass('change_colour');
			$("#span_"+thenum).addClass('default_colour');
			$("#res_" + thenum).addClass('disablelink');
			$("#res_" + thenum).removeClass('enablelink');
			//$("#res_" + thenum).removeClass('change_colour');
		}
    }
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
<script>
$(function() {
  $( document ).tooltip({
   content: function () {
    return $(this).prop('title');
   }
  });
 });
</script>