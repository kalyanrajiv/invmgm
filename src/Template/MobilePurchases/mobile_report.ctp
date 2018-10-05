<?php
	
	if(!isset($start_date)){$start_date = "";}
	if(!isset($end_date)){$end_date = "";}
	if(!empty($start_date)){
		$start_date = date("d-M-Y",strtotime($start_date));
	}
	if(!empty($end_date)){
		$end_date = date("d-M-Y",strtotime($end_date));
	}
	if(!isset($kioskId )){$kioskId = "";}
	$str_start = strtotime($start_date);
	$str_end = strtotime($end_date);
	//pr($repairLogDetails);
?>
<div class="mobilepurchase index">
	 
	<form action='<?php echo $this->request->webroot;?>mobile-purchases/mobile_report_search' method = 'get'>
		<fieldset>
			<legend>Search</legend>
			<div>
				<table>
					<tr>
						 
						<td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date"  style = "width:80px;height: 25px;"value='<?php echo $start_date;?>' /></td>
						<td><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date"  style = "width:80px;height: 25px;" value='<?php echo $end_date;?>'  /></td>
						<td><?php
						if( $this->request->session()->read('Auth.User.group_id')!= MANAGERS){
							echo $this->Form->input(null,array(
														   'options' => $kiosks,
														   'label' => false,
														   'empty' => 'All',
														   'style' => 'width:180px',
														   'id'=> 'kioskid',
														   'name' => 'data[MobilePurchase][kiosk_id]',
														   'value' => $kioskId));
						}else{
							echo $this->Form->input(null,array(
														   'options' => $kiosks,
														   'label' => false,
														   'empty' => 'All',
														   'style' => 'width:180px',
														   'id'=> 'kioskid',
														   'name' => 'data[MobilePurchase][kiosk_id]',
														   'value' => $kioskId));
							//echo $this->Form->input('kiosk',array('options'=>$manager_kiosks,'id'=>'KioskTotalSaleKiosk','default'=>$selectedKiosk));
						}
						
						?>
						</td>
					 
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
		jQuery("#kioskid").val("");
	}

</script>
	<h2><?php echo __('Mobile Purchase Reports'); ?></h2>
	 
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th>Kiosk Name</th>
		<th>Purchased By</th>
		<th>Total Phone</th>
		<th>Total Cost of Purchase</th>
		<th>Detail</th>
			 
	</tr>
	</thead>
	<tbody>
		<?php
		  // pr($mobilePurchases);
		   $total_count_of_phone = $total_cost_price = 0;
			foreach($mobilePurchases as $mobilePurchase){
				//$id = $mobilePurchase['MobilePurchase']['id'];
				$kiosk_id = $mobilePurchase['kiosk_id'];
				$purchased_by_kiosk = $mobilePurchase['purchased_by_kiosk'];
				$total_phone = $mobilePurchase['count'];
				$cost_price = 	$mobilePurchase['total_cost'];
			 $total_count_of_phone += $total_phone;
			 $total_cost_price += $cost_price;
			
		?>
		<tr>
			<td><?php
			if($kiosk_id == 0){
				echo'warehouse';
			}else{
				echo $kiosks[$kiosk_id] ;
			}
			?></td>
			<td><?php echo $kiosks[$purchased_by_kiosk]; ?></td>
			<td><?php echo $total_phone; ?></td>
			<td><?php echo $CURRENCY_TYPE.$cost_price;?>&nbsp;</td>
			 
			<td><?php
			 echo $this->Html->link(h('View'), array('controller' => 'mobile_purchases', 'action' => 'mobile_report_detail',$mobilePurchase['kiosk_id'],$str_start,$str_end)); 
			 
			  ?></td>
		</tr>
		<?php }?>
		<tr>
            <td></td> 
			<td><b>Total</b></td>
			<td><?=$total_count_of_phone; ?></td>
			<td><?= $total_cost_price; ?></td>
		</tr>
	</tbody>
	</table>
	 
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New <br/>Mobile Purchase'), array('action' => 'add'),array('escape' => false)); ?></li>
		<?php if($this->request->session()->read('Auth.User.group_id') != KIOSK_USERS){?>
		<li><?php echo $this->Html->link(__('Mobile Stock In'), array('action' => 'bulk_mobile_purchase')); ?> </li>
		<?php } ?>
		<li><?php # echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php # echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('View Mobile Sale'), array('controller' => 'mobile_re_sales', 'action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('Buy Mobile Phones'), array('controller' => 'mobile_purchases', 'action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('Mobile Stock/Sell'), array('controller' => 'mobile_purchases', 'action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('Global <br/>Mobile Search'), array('controller' => 'mobile_purchases', 'action' => 'global_search'),array('escape' => false)); ?></li>
	</ul>
</div>
<script>
	jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
	});
</script>