<div class="stock index">
	<h2><?php echo __('Stock per kiosk'); ?></h2>
		<table cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<th>Product Code</th>
					<th>Product</th>
					<th>Details</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<?php echo $kioskProduct['product_code'];?>
					</td>
					<td>
						<?php echo $kioskProduct['product'];?>
					</td>
					<td>
						<?php
						if(!empty($kioskWiseProduct[$kioskProduct['id']])){
							foreach($kioskWiseProduct[$kioskProduct['id']] as $kiosk_name=>$productQtty){
								echo "<p>".$kiosk_name.", Quantity = ".$productQtty."</p>";
							}
						}
						?>
					</td>
				</tr>
			</tbody>
		</table>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div>
