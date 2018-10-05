<div class="kioskProductSales index">
	<?php $kioskId = $this->request->query['kiosk-dropdown'];?>
	<table style="width: 90%;">
		<tr>
		<td><h2><?php echo __('Checkout'); ?></h2></td>
		<?php
		$session_basket = $this->request->Session()->read('consolidate_faulty');
		//if(!empty($session_basket)){
		//echo "<td style='padding-top: 10px;'>".$this->Html->link('Update stock',array('action'=>"consolidate_faulty?kioskDropdown=$kioskId&Dispatch=1"))."</td>
		//<td style='padding-top: 10px;'>".$this->Html->link('Edit basket',array('action'=>"consolidate_faulty?kioskDropdown=$kioskId"))."</td>
		//<td style='padding-top: 10px;'>".$this->Html->link('Move to Bin',array('action'=>"consolidate_faulty?kioskDropdown=$kioskId&bin=1"))."</td>";
		//}
		?>
		</tr>
	</table>
	<?php
	echo $this->Form->create('CheckOut',array('type'=>'post'));
	
		$basketStrDetail = '';
		if(is_array($session_basket)){
			foreach($session_basket as $productId=>$productDetails){
				foreach($productDetails as $defectiveId => $defectiveQtt){
					$quantityField = "<td><input readonly = 'readonly' type='text' value='{$defectiveQtt}' name='data[CheckOut][$defectiveId]' style='width: 50px;'/></td>";//made readonly as of now
					$updateButton = "<td><input type='submit' name='update_quantity' value='Update Quantity'/></td>";
					$basketStrDetail.= "<tr>
					<td>".$productIds[$productId]."</td>
					<td>".$productArr[$productId]."</td>
					$quantityField
					<td>".$this->Html->link('delete',array('action'=>'delete_product_from_condolidate_session',$productId, $defectiveId, $kioskId),array('id'=>$productId,'onClick'=>'return reply_click(this.id)','value'=>'delete','name'=>'delete_product'))."</td>
					</tr>";
				}
			}
			
			if(!empty($basketStrDetail)){
				$basketStr = "<table>
				<tr>
					<th>Product code</th>
					<th>Product</th>
					<th>Quantity</th>
				</tr>".$basketStrDetail.
				"<tr>"
					.//.$updateButton.
					"<td style='padding-top: 10px;'><span class='actions'>".$this->Html->link('Move to Transient',array('action'=>"consolidate_faulty?kioskDropdown=$kioskId&Dispatch=1"))."</span></td>
					<td style='padding-top: 10px;'><span class='actions'>".$this->Html->link('Edit basket',array('action'=>"consolidate_faulty?kioskDropdown=$kioskId"))."</span></td>
					<td style='padding-top: 10px;'><span class='actions'>".$this->Html->link('Move to Bin',array('action'=>"consolidate_faulty?kioskDropdown=$kioskId&bin=1"))."</span></td>
				</tr>
				</table>";
			}
			if(!empty($session_basket)){
				$totalItems = 0;
				foreach($session_basket as $ss_key => $ss_value){
					$totalItems += count($ss_value);
				}
			}
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
	<?=$this->element('faulty_slide_menu');?>
</div>
<script>
	function reply_click(clicked_id)
	{
	    if(!confirm("Do you really want to delete "+clicked_id))
	    return false;
	}
</script>