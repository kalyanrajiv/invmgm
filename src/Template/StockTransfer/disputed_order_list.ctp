<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
	$from_date = $to_date = $kiosk = '';
	$req_type = 'dynamic';
	if($this->request->query){
		$from_date = $this->request->query['from_date'];
		$to_date = $this->request->query['to_date'];
		$kiosk = $this->request->query['data']['kiosk'];
		if(array_key_exists('req_type',$this->request->query)){
			$req_type = $this->request->query['req_type'];
		}
	}
?>
<div class="kioskOrders index">
	
	<strong><?php #print_r($kioskOrders);
	$rootURL = "";//$this->html->url('/', true);
	$queryStr = "";
	//pr($this->request);
	if( isset($this->request->query['from_date']) ){
			$queryStr.="from_date=".$this->request->query['from_date'];
	}
	if( isset($this->request->query['to_date']) ){
			$queryStr.="&to_date=".$this->request->query['to_date'];
	}
	if( isset($this->request->query['req_type']) ){
			$queryStr.="&req_type=".$this->request->query['req_type'];
	}
	if( isset($this->request->query['data']) ){
		if(isset($this->request->query['data']['kiosk'])){
			$queryStr.="&kiosk=".$this->request->query['data']['kiosk'];
		}
	}
	echo __('<span style="font-size: 17px;">Disputed Orders:</span>'); ?></strong></h2>
	<?php
    //pr($order_dispute_res);
    if(empty($order_dispute_res)){
		echo "<h4>No result found!</h4>";
	}else{?>
	<h4>Sale from <?=$from_date;?> to <?=$to_date;?> for <?=$kiosks[$selectedKiosk];?></h4>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		
		<th>Product Code</th>
		<th>Product</th>
		<th>Category</th>
		<th>Cost Price</th>
		<th>Quantity</th>
        <th>Receiving Status</th>
		<th>Amount</th>
		<th>Admin Acted Date</th>
		
		
	</tr>
	</thead>
	<tbody>
		<?php
		//pr($transferredByWarehouse);die;
		$whCostPrice = $sumCostPrice = $total = 0;
		$whProductCode = $whProduct = '--';
		$req_type = "";
		if(array_key_exists("req_type",$this->request->query)){
			$req_type = $this->request->query['req_type'];	
		}
		
		foreach($order_dispute_res as $key => $warehouseData){
            $amt = 0;
           $product_id = $warehouseData->product_id;
           $qty = $warehouseData->quantity;
           $reciving_status = $warehouseData->receiving_status;
           if($reciving_status == -1){
            $reciving_status_name = "Received Less";
           }else{
            $reciving_status_name = "Received More";
           }
           $admin_Acted = $warehouseData->admin_acted;
		   $cost_price = $warehouseData->cost_price;
		   if($req_type == "fixed"){
			$amt = $cost_price * $qty;
		   }else{
				$amt = $products_cost[$product_id] * $qty;
		   }
		   
		   
           
		   if($reciving_status == -1){
				$total += $amt;
		   }else{
				$total +=-$amt;
		   }
           
		?>
		<tr>
			<td><?php echo $product_code[$product_id];?></td>
			<td><?php echo $products_name[$product_id];?></td>
			<td><?php echo $cat_name[$product_cats[$product_id]]; ?></td>
			<td><?php
			if($req_type == "fixed"){
				echo $CURRENCY_TYPE.number_format($cost_price,2);
			}else{
				echo $CURRENCY_TYPE.number_format($products_cost[$product_id],2);	
			}
			
			?></td>
			<td><?php echo $qty;?></td>
            <td><?php echo $reciving_status_name;?></td>
			<td><?php echo $CURRENCY_TYPE.number_format($amt,2);?></td>
			<td><?php echo date('d-m-Y h:i A',strtotime($admin_Acted));//$this->Time->format('d-m-Y h:i A', $warehouseData['created'],null,null);?></td>
		</tr>
		<?php
		}?>
		<tr>
			<td>&nbsp;</td>
			<td colspan="4"><strong>Total</strong></td>
			<td><strong><?php echo $CURRENCY_TYPE.$total;?></strong></td>
		</tr>
	</tbody>
	</table>
	<?php } ?>

</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Dispatched Products'), array('controller' => 'stock_transfer', 'action' => 'dispatched_products')); ?> </li>
		<li><?php echo $this->Html->link(__('Sale Summary'), array('controller' => 'stock_transfer', 'action' => 'summary_sale')); ?> </li>
	</ul>
</div>
<script>
	$(function() {
		$( "#datepicker_1" ).datepicker({ dateFormat: "dd-mm-yy" })
	});
	
	$(function() {
		$( "#datepicker_2" ).datepicker({ dateFormat: "dd-mm-yy" })
	});
</script>
<script>
	
</script>