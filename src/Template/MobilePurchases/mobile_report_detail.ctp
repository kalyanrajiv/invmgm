<?php
	 
	$search_kw = "";
	if(!empty($this->request->query)){
		$search_kw = $this->request->query['search_kw']; 
	}
	if(!isset($start_date)){$start_date = "";}
	if(!isset($end_date)){$end_date = "";}
	if(!empty($start_date)){
		$start_date = date("d-M-Y",strtotime($start_date));
	}
	if(!empty($end_date)){
		$end_date = date("d-M-Y",strtotime($end_date));
	}
	
	if(!empty($this->request->query)){
		//pr($this->request->query);
		$kiosk_id = $this->request->query['kiosk_id'] ;
	}
?>
<div class="mobilepurchase index">
	  
	<form action='<?php echo $this->request->webroot; ?>mobile-purchases/mobile_detail_search' method = 'get'>
		<fieldset>
			<legend>Search</legend>
			<div>
				<table>
					<tr>
						<td><input type = "text" name = "search_kw" id = "search_kw" placeholder = "Model, IMEI, Brand" style = "width:230px  " autofocus value='<?php echo $search_kw; ?>'/></td>
						<td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date"  style = "width:80px;height: 25px;"value='<?php echo $start_date;?>' /></td>
						<td><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date"  style = "width:80px;height: 25px;" value='<?php echo $end_date;?>'  /></td>
						<input type='hidden' name='kiosk_id' id='kiosk_id' value='<?=$kiosk_id?>' />
						<td><input type = "submit" value = "Search" name = "submit"/></td>
						<td><input type='button' name='reset' value='Reset Search' style='padding:6px 8px;color:#333;border:1px solid #bbb;border-radius:4px;' onClick='reset_search();'/></td>
						
					</tr>
				</table>
				
				
			</div>
		</fieldset>	
	</form>
<script>
	
	function reset_search(){
		jQuery("#datepicker1").val("");
		jQuery("#datepicker2").val("");
		jQuery("#search_kw").val("");
	}

</script>
	  
	<h2><?php   
			echo __('Mobile Purchase Reports For Kiosk:'.$kiosks[$kiosk_id]);
		 ?></h2>
	 
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th>ID</th>
		<th>Original Location</th>
		<th>Current Location</th>
		<th>User Name</th>
		<th>Brand</th>
		<th>Mobile Model</th>
		<th>Color</th>
		<th>IMEI</th>
		<th>Cost Price</th>
		<th>Selling Price</th>
		<th>status</th>
		<th>Grade</th>
		<th>Type</th>
		<th>Network</th>
		 <th>Date</th>
	</tr>
	</thead>
	<tbody>
		<?php
			//pr($mobilePurchases);
			 $total_selling_price = $total_cost_price = 0;
			foreach($mobilePurchases as $mobilePurchase){
				$id = $mobilePurchase['id'];
				$kiosk_id = $mobilePurchase['kiosk_id'];
				$purchase_by = $mobilePurchase['purchased_by_kiosk'];
				$user_id = $mobilePurchase['user_id'];
				$brand_id = $mobilePurchase['brand_id'];
				$mobile_model_id = $mobilePurchase['mobile_model_id'];
				$color = $mobilePurchase['color'];
				$imei =  $mobilePurchase['imei'];
				$cost_price  =  $mobilePurchase['topedup_price'];
				$selling_price  =  $mobilePurchase['selling_price'];
				$Grade =  $mobilePurchase['grade'];
				$type_id =  $mobilePurchase['type'];
				$network_id =  $mobilePurchase['network_id'];
				$current_status = $mobilePurchase['status'];
				$created = $mobilePurchase['created'];
				$total_cost_price += $cost_price;
				
				
		?>
		<tr>
			<td><?php echo $id; ?></td>
			<td><?php echo $kiosks[$purchase_by]; ?></td>
			<td><?php echo $kiosks[$kiosk_id]; ?></td>
			<td><?php echo $users[$user_id];?></td>
			<td><?php echo $brands[$brand_id]; ?></td>
			<td><?php echo $mobileModels[$mobile_model_id];?></td>
			<td><?php echo $colorOptions[$color];?></td>
			<td><?php echo $imei;?></td>
			<td><?php echo $CURRENCY_TYPE.$cost_price;   ?>&nbsp;</td>
			<?php if(array_key_exists($id,$sell_data)){ 
			$total_selling_price += $sell_data[$id]; ?>
				<td><?php echo $CURRENCY_TYPE.$sell_data[$id] ;   ?>&nbsp;</td> 
			<?php }else{ ?>
				<td><?php echo '--'?>&nbsp;</td> 
			<?php } ?>
			<?php if(array_key_exists($id,$sell_data)){ ?>
				<td><b>SOLD</b></td>
			<?php }else{ ?>
				<td><b>Available</b></td>
			<?php } ?>
			<td><?php echo $gradeType[$Grade];?></td>
			<td><?php echo $types[$type_id];?></td>
			<td><?php
					if(array_key_exists($network_id, $networks)){
						echo h($networks[$network_id]);
					}else{
						echo "---";
					}
				?> </td>
			<td nowrap><?php echo  date('jS M, Y g:i A',strtotime($created)) ; ?></td>
		</tr>
		<?php }?>
		<tr>
			<td colspan=7></td>
			<td><b>Total</b></td>
			<td><?=$CURRENCY_TYPE.$total_cost_price; ?></td>
			<td><?=$CURRENCY_TYPE.$total_selling_price; ?></td>
		</tr>
	</tbody>
	</table>
	 
	 
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New </br>Mobile Purchase'), array('action' => 'add'),array('escape' => false)); ?></li>
		<?php if($this->request->session()->read('Auth.User.group_id') != KIOSK_USERS){?>
		<li><?php echo $this->Html->link(__('Mobile Stock In'), array('action' => 'bulk_mobile_purchase')); ?> </li>
		<?php } ?>
		<li><?php # echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php # echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('View Mobile Sale'), array('controller' => 'mobile_re_sales', 'action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('Buy Mobile Phones'), array('controller' => 'mobile_purchases', 'action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('Mobile Stock/Sell'), array('controller' => 'mobile_purchases', 'action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('Global<br/> Mobile Search'), array('controller' => 'mobile_purchases', 'action' => 'global_search'),array('escape' => false)); ?></li>
	</ul>
</div>
<script>
	jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
	});
</script>