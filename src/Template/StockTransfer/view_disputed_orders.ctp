<?php
	//pr($orderDisputes['OrderDispute']);
	//
	//die;
?>
 
<div class="centralStocks index">
	<table>
		<tr>
			<th>Order Id</th>
			<th>Kiosk</th>
			<th>Product Code</th>
			<th>Product</th>		
			<th>Image</th>			
			<th>Sale Price</th>
			<th>Receiving Status</th>
			<th>Qty</th>
			<th> Action Date</th>
			<th>Kiosk User Remarks</th>
			<th>Admin Remarks</th>
		
			<th>Approve</th>
		</tr>
		<?php echo $this->Form->create(); ?>
		<?php   //pr($orderDisputes);
		foreach($orderDisputes as $key => $value){
			$imageDir = WWW_ROOT."files".DS.'product'.DS.'image'.DS.$value->product['id'].DS;
			$imageName = 'thumb_'.$value->product['image'];
			$absoluteImagePath = $imageDir.$imageName;
			$imageURL = "/thumb_no-image.png";
			
			if(file_exists($absoluteImagePath)){
				$imageURL = "/files/product/image/".$value->product['id']."/$imageName";
			}?>
			
			<tr>
				<?php //echo $this->Form->create(); ?>
				<td><?php echo $value['kiosk_order_id'] ;?></td>
				<td><?php echo $this->Html->link($kiosk[$value['kiosk_id']], array('controller' => 'kiosks', 'action' => 'view',$value['kiosk_id'])) ;?></td>
				<td><?php echo $value->product['product_code'] ;?></td>
				<td><?php echo $value->product['product'] ;?></td>
				<td><?php echo $this->Html->link(
							  $this->Html->image($imageURL, array('fullBase' => true)),
							  array('controller' => 'products','action' => 'edit', $value->product['id']),
							  array('escapeTitle' => false, 'title' => $value->product['product'])
							 ); ?>
							 </td>
				<td><?php echo  $value->product['selling_price'] ;?></td>
				<td><?php echo $disputeOptions[$value['receiving_status']] ;?></td>
				<td><?php echo $value['quantity'] ;?></td>
				<td><?php echo   $value['admin_acted'] ;   ?></td>
				<td>
					<?php echo $value['kiosk_user_remarks'] ;
							echo $this->Form->input(null,array(
										'type' => 'hidden',
										'label' => false,
										'name' => "data[admin_action_$key][receiving_status]",
										'value' => $value['receiving_status']
										)
										 );
							echo $this->Form->input(null,array(
										'type' => 'hidden',
										'label' => false,
										'name' => "data[admin_action_$key][id]",
										'value' => $value['id']
										)
										 );
							echo $this->Form->input(null,array(
										'type' => 'hidden',
										'label' => false,
										'name' => "data[admin_action_$key][product_id]",
										'value' => $value->product['id']
										)
										 );
							echo $this->Form->input(null,array(
										'type' => 'hidden',
										'label' => false,
										'name' => "data[admin_action_$key][kiosk_id]",
										'value' => $value['kiosk_id']
										)
										 );
							echo $this->Form->input(null,array(
										'type' => 'hidden',
										'label' => false,
										'name' => "data[admin_action_$key][quantity]",
										'value' => $value['quantity']
										)
									 );
					?>
				</td>
				<?php if($value['approval_status'] == 1){?>
					<td><?php echo $value['admin_remarks'];	?></td>
					<td><?php echo $approvalOptions[$value['approval_status']] ;?></td>
					<?php }elseif(($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
							   $this->request->session()->read('Auth.User.group_id') == SALESMAN||
                               $this->request->session()->read('Auth.User.group_id') == inventory_manager) &&
							  $value['OrderDispute']['approval_status'] == 0
							  ) {?>
					<td><?php echo $this->Form->input(null,array(
									'type' => 'text',
									'label' => false,
									'name' => "data[admin_action_$key][admin_remarks]",
									'value' => $value['admin_remarks'],
									'style' => "width:150px;"
									)
									 );	?></td>
					<td><?php echo $this->Form->input(null,array(
									'label' => false,
									'options' => $approvalOptions,
									'name' => "data[admin_action_$key][approval_status]",
									'value' => $value['approval_status']
									)
									 ) ;?></td>
					<?php } ?>
					
					<td>
					<?php 	if($value['approval_status'] == 1){
								//echo $this->Form->end();
							}elseif($value['approval_status'] == 0 &&
								($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
								 $this->request->session()->read('Auth.User.group_id') == SALESMAN ||
                                 $this->request->session()->read('Auth.User.group_id') == inventory_manager)){
                                 
								 $option = array('label' => 'Submit', 'name' => 'admin_action');
								//echo $this->Form->end($option);	
							}
					?>
					</td>
			</tr>
			
	<?php	}
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
								 $this->request->session()->read('Auth.User.group_id') == SALESMAN ||
                                $this->request->session()->read('Auth.User.group_id') == inventory_manager){
				echo $this->Form->button(__('Submit'),['name' => 'admin_action', 'style' =>'height:40px;width:84px;']);
                                echo  $this->Form->end() ;
								}
		?>
	</table>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<?php  if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
					$this->request->session()->read('Auth.User.group_id') == MANAGERS ||
					$this->request->session()->read('Auth.User.group_id') == inventory_manager){
					echo $this->element('sidebar/order_menus');
				}else{
					echo $this->element('sidebar/kiosk_order_menus');
		}?>
	 
</div>
<script>
	$('button[name = "admin_action"]').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	});
</script>
