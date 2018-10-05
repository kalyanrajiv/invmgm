<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$currency = Configure::read('CURRENCY_TYPE');
///$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
//pr($productCodes);die;
?>
<div class="centralStocks index">
	<?php $session_basket = $this->request->Session()->read('stock_taking_basket');?>
	<h2><?php echo __('Stock Taking Checkout'); ?></h2>
	<h4>You have <?php echo count($session_basket);?> item(s) in the cart for <?php echo $kiosks[$this->request->Session()->read('stock_taking_kiosk_id')];?></h4>
	<?php if(count($session_basket)>0){?>
	<?php echo $this->Form->create('StockTakingCheckout');?>
	<table>
		<tr>
			<th>Product id</th>
			<th>Product code</th>
			<th>Product name</th>
			<th>Selling price</th>
			<th>Orignal Qantity</th>
			<th>Quantity</th>
		</tr>
		<?php foreach($session_basket as $productCode=>$productData){
			?>
		<tr>
			<td><?= $ProductIDs[$productCode];?></td>
			<td><?=$productCode;?></td>
			<td><?=$productNameArr[$productCode];?></td>
			<td><?php echo $currency.$sellingArr[$productCode];?></td>
			<td><?php echo $productCodes[$productCode];?></td>
			<td><?php echo $this->Form->input('quantity',array('type'=>'text','id' => 'qty_'.$productCode,'name'=>"StockTakingCheckout[$productCode]",'value'=>$productData['quantity'],'label'=>false,'style'=>"
width: 50px;"));?></td>
			<td><?php echo $this->Html->link(
			'Delete',
			array('controller' => 'stock_initializers', 'action' => 'delete_from_stock_tkn_checkout', $productCode),
			array('id'=>$productCode,'onClick'=>'return reply_click(this.id)','value'=>'delete','name'=>'delete_product')
			);?>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan='5'>
				<table style="width: 50%;float: right;">
					<tr>
						<td><input type='submit' name='initialize_stock' value='process stock'/></td>
						<td><input type='submit' name='edit_basket' value='Edit basket'/></td>
						<td><input type='submit' name='update_quantity' value='Update Quantity'/></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	
	<?php } ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
	<li><?php echo $this->Html->link(__('Stock Taking'), array('action' => 'stock_taking')); ?> </li>
	<li><?php echo $this->Html->link(__('Placed Order'), array('controller' => 'kiosk_orders', 'action' => 'placed_orders')); ?> </li>
	<li><?php echo $this->Html->link(__('Confirmed Orders'), array('controller' => 'kiosk_orders', 'action' => 'confirmed_orders')); ?> </li>		
	</ul>
</div>
<script>
	$('input[name = "initialize_stock"]').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	});
</script>
<script>
	function reply_click(clicked_id)
	{
	    if(!confirm("Do you really want to delete "+clicked_id))
	    return false;
	}
</script>
<script>
	$("input[id*='qty_']").keydown(function (event) {
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||
		event.keyCode == 46  || event.keyCode == 183
		) {
			;
			//48-57 => 0..9
			//8 => Backspace; 9 => tab 
		} else {
			event.preventDefault();
		}
    });
</script>