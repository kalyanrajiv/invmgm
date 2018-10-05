<div class="kioskProductSales index">
	<?php 
		$kiosks['0'] = "Warehouse";
		echo $this->Form->create("Kiosks",array('url' => array('action'=>'global_search','type'=>'post')));?>
		<table style="width: 30%;">
		<tr>
		<td><h2><?php echo __('Checkout'); ?></h2></td>
		<?php
		$sessionChosenImeis = $this->request->Session()->read('chosenImeis');
		if(!empty($sessionChosenImeis)){
		echo "<td>".$this->Form->input('Transfer',array('type'=>'submit','label'=>false))."</td>
							<td>".$this->Form->input('Edit basket',array('name'=>'edit_basket','type'=>'submit','label'=>false))."</td>";
		}
		?>
		</tr>
	</table>
		<?php $selectedKiosk = $this->request->Session()->read('selectedKiosk');
		$sessionChosenImeis = $this->request->Session()->read('chosenImeis');
		if(is_array($sessionChosenImeis)){
			$serialNo = 0;
			$flashTable = "";
			
			foreach($sessionChosenImeis as $purchaseId=>$chosenImei){
				$serialNo++;
				//pr($serialNo);
				$flashTable.= "<tr>
						<td>".$serialNo."</td>
						<td>".$chosenImei."</td>
						<td>".$this->Html->link('delete',array('action'=>'delete_mobile_from_session',$chosenImei),array('id'=>$chosenImei,'onClick'=>'return reply_click(this.id)','value'=>'delete','name'=>'delete_product'))."</td>
					</tr>";
			}
			
			if(!empty($flashTable)){
				$flashTable = "<table>
					<tr>
					<th>Kiosk: ".$kiosks[$selectedKiosk]."</th>
					</tr>
					<tr>
						<th>Serial No.</th>
						<th>Imei</th>
					</tr>".$flashTable."
					</table>";
			}
			echo $flashTable;
		}else{
			echo "<h4>Please add products to the basket!!</h4>";
		}
	echo $this->Form->end();
	?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Sale'), array('action' => 'new_order')); ?></li>		
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'stock', 'action' => 'index')); ?> </li>
	</ul>
</div>
<script>
	function reply_click(clicked_id)
	{
	    if(!confirm("Do you really want to delete "+clicked_id))
	    return false;
	}
</script>