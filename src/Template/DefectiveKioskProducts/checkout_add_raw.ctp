<div class="kioskProductSales index">
	
	<table style="width: 30%;">
		<tr>
		<td><h2><?php echo __('Checkout'); ?></h2></td>
		<?php
		$session_basket = $this->request->Session()->read('raw_faulty_product_basket');
		//if(!empty($session_basket)){
		//echo "<td style='padding-top: 10px;'>".$this->Html->link('Update stock',array('action'=>'add_raw_data'),array('name'=>'dispatch'))."</td>
		//					<td style='padding-top: 10px;'>".$this->Html->link('Edit basket',array('action'=>'add'))."</td>";
		//}
		?>
		</tr>
	</table>
	<?php
	echo $this->Form->create('CheckOut',array('type'=>'post'));
	
		$session_basket = $this->request->Session()->read('ch_raw_faulty_product_basket');//change rajju 13/12/17
		$basketStrDetail = '';
		if(is_array($session_basket)){
			foreach($session_basket as $productId=>$productDetails){
				if(array_key_exists($productDetails['remarks'], $faulty_conditions)){
					$remark = $faulty_conditions[$productDetails['remarks']];
				}else{
					$remark = '';
				}
				
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
				$this->request->session()->read('Auth.User.group_id') == MANAGERS){
					$quantityField = "<td><input type='text' value='{$productDetails['quantity']}' name='CheckOut[$productIds[$productId]]' style='width: 50px;'/></td>";
					$updateButton = "<td><input type='submit' name='update_quantity' value='Update Quantity'/></td>";
				}else{
					$quantityField = "<td>".$productDetails['quantity']."</td>";
					$updateButton = "";
				}
				$basketStrDetail.= "<tr>
				<td>".$productIds[$productId]."</td>
				<td>".$productArr[$productId]."</td>
				$quantityField
				<td>".$productDetails['price']."</td>
				<td>".$remark."</td>
				<td>".$this->Html->link('delete',array('action'=>'delete_product_from_session',$productId),array('id'=>$productId,'onClick'=>'return reply_click(this.id)','value'=>'delete','name'=>'delete_product'))."</td>
				</tr>";
			}
			
			if(!empty($basketStrDetail)){
				$basketStr = "<table>
				<tr>
					<th>Product code</th>
					<th>Product</th>
					<th>Quantity</th>
					<th>Selling price</th>
					<th>Remarks</th>
				</tr>".$basketStrDetail.
				"<tr><td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>"
					.$updateButton.
					"<td style='padding-top: 10px;'><span class='actions'>".$this->Html->link('Update stock',array('action'=>'add_raw_data'),array('name'=>'dispatch'))."</span></td>
					<td style='padding-top: 10px;'><span class='actions'>".$this->Html->link('Edit basket',array('action'=>'add'))."</span></td>
				</tr>
				</table>";
			}
			
			$totalItems = count($session_basket);
			if($totalItems){
				echo "Total item Count:$totalItems<br/>$basketStr";
			}
			
		}else{
			echo "<h4>Please add products to the basket!!</h4>";
		}
	echo $this->Form->end();
	?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){?>
	 <?=$this->element('faulty_slide_menu');?>
	<?php }else{ ?>
	 <ul>
	   <li><?php echo $this->Html->link(__('Add Faulty'), array('controller' => 'defective_kiosk_products', 'action' => 'add')); ?></li>
	   <li><?php echo $this->Html->link(__('Faulty References'), array('controller' => 'defective_kiosk_products', 'action' => 'list_defective_references')); ?></li>
	   <li><?php echo $this->Html->link(__('View Faulty'), array('controller' => 'defective_kiosk_products', 'action' => 'view_faulty_products')); ?></li> 
	 </ul>
	<?php } ?>
</div>
<script>
	function reply_click(clicked_id)
	{
	    if(!confirm("Do you really want to delete "+clicked_id))
	    return false;
	}
</script>