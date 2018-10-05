<div class="kioskProductSales index">
	
	<table>
		<tr>
		<td><h2><?php echo 'Checkout (Reference: '.$this->request->Session()->read('session_reference').')'; ?></h2></td>
		<?php
		$import_basket = $this->request->Session()->read('import');
		if(!empty($import_basket)){
		#echo "<td style='padding-top: 10px;'>".$this->Html->link('Update stock',array('action'=>'update_stock'),array('name'=>'dispatch'))."</td>
							#<td style='padding-top: 10px;'>".$this->Html->link('Edit basket',array('action'=>'index'))."</td>";
		}
		?>
		</tr>
	</table>
	<?php
	echo $this->Form->create('CheckOut',array('type'=>'post'));
	
		$import_basket = $this->request->Session()->read('import');
	
		$basketStrDetail = '';
		if(is_array($import_basket)){
			
			foreach($import_basket as $productId=>$productDetails){
				$basketStrDetail.= "<tr>
				<td>". $productIds[$productId]."</td>
				<td>".$productArr[$productId]."</td>
				<td>"."<input type = text name =  CheckOut[$productId] value = $productDetails[quantity] "."</td>
			 	<td>".$this->Html->link('delete',array('action'=>'delete_import_basket',$productId),array('id'=>$productId,'onClick'=>'return reply_click(this.id)','value'=>'delete','name'=>'delete_product'))."</td>
				</tr>";
			}
			
			if(!empty($basketStrDetail)){
				$basketStr = "<table>
				<tr>
					<th>Product code</th>
					<th>Product</th>
					<th style='width: 102px;'>Quantity</th>
					
				</tr>".$basketStrDetail.
				"<tr> 
					<td></td>
					<td><span> 
						<input type='submit' name='update_quantity' value='Update Quantity'  style ='float: right; margin-left: 9px;'/>
						</span>
						<span class='actions'>". $this->Html->link('Place Order', array('action' => 'import_stock'), array('style' => "float: right; margin-left: 9px;height:18px")) .
						"</span>
					</td>
					
					<td  ><span class='actions'>".$this->Html->link('Edit basket',array('action'=>'index'),array('style' => "float: right; height:18px"))."</span></td>
					<td><input type='submit' name='Move_to_bin' value='Move to bin'/></td>
				</tr>
				</table>";
			}
			
			$totalItems = count($import_basket);
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
