<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<div class="kioskOrders index">
	<?php
	$siteUrl = Configure::read('SITE_BASE_URL');
	$crossStockRel = $this->Html->link(array('controller'=>'StockTransfer','action'=>'search_cross_stock_transfer'));
	$repairSaleRel = $this->Html->link(array('controller'=>'StockTransfer','action'=>'total_repair_cost'));
	$crossReturnRel = $this->Html->link(array('controller'=>'StockTransfer','action'=>'search_cross_stock_return'));
	$unlockSaleRel = $this->Html->link(array('controller'=>'StockTransfer','action'=>'total_unlock_cost'));
	$phoneCostRel = $this->Html->link(array('controller'=>'StockTransfer','action'=>'total_phone_cost'));
	$bulkPhoneCostRel = $this->Html->link(array('controller'=>'StockTransfer','action'=>'total_bulk_phone_cost'));
	$from_date = $to_date = $kiosk = '';
	//pr($this->request->query);die;
    if($this->request->query){
		$from_date = $this->request->query['from_date'];
		$to_date = $this->request->query['to_date'];
		$kiosk = $this->request->query['kiosk'];
	}
	
	$crossStockUrl = "search_cross_stock_transfer?from_date=".$from_date."&to_date=".$to_date."&data%5Bkiosk%5D=".$kiosk;
	$crossStockUrlFixed = "search_cross_stock_transfer?from_date=".$from_date."&to_date=".$to_date."&data%5Bkiosk%5D=".$kiosk."&req_type=fixed";
	$repairSaleUrl = "total_repair_cost?from_date=".$from_date."&to_date=".$to_date."&data%5Bkiosk%5D=".$kiosk;
	$repairSaleUrlFixed = "total_repair_cost?from_date=".$from_date."&to_date=".$to_date."&data%5Bkiosk%5D=".$kiosk."&req_type=fixed";
	$crossReturnUrl = "search_cross_stock_return?from_date=".$from_date."&to_date=".$to_date."&data%5Bkiosk%5D=".$kiosk;
	
	$orderDisputeUrl = "disputed_order_list?from_date=".$from_date."&to_date=".$to_date."&data%5Bkiosk%5D=".$kiosk;
	$orderDisputeUrlFixed = "disputed_order_list?from_date=".$from_date."&to_date=".$to_date."&data%5Bkiosk%5D=".$kiosk."&req_type=fixed";
	
	$crossReturnUrlFixed = "search_cross_stock_return?from_date=".$from_date."&to_date=".$to_date."&data%5Bkiosk%5D=".$kiosk."&req_type=fixed";
	$unlockSaleUrl = "total_unlock_cost?from_date=".$from_date."&to_date=".$to_date."&data%5Bkiosk%5D=".$kiosk;
	$unlockSaleUrlFixed = "total_unlock_cost?from_date=".$from_date."&to_date=".$to_date."&data%5Bkiosk%5D=".$kiosk."&req_type=fixed";
	$phoneCostUrl = "total_phone_cost?from_date=".$from_date."&to_date=".$to_date."&data%5Bkiosk%5D=".$kiosk;
	$bulkPhoneCostUrl = "total_bulk_phone_cost?from_date=".$from_date."&to_date=".$to_date."&data%5Bkiosk%5D=".$kiosk;
	$phoneCostUrlFixed = "total_phone_cost?from_date=".$from_date."&to_date=".$to_date."&data%5Bkiosk%5D=".$kiosk."&req_type=fixed";
	$totalDetail = '';
	$totalDetailFixed = '';
	$total = $sumRepairCost+$sumUnlockCost+$totalPhoneCost-$totalReturnCost+($totalBulkPhoneCost-$totalBulkReturnCost);
	$totalFixed = $fixedRepairCost+$fixedUnlockCost+($totalBulkPhoneCost-$totalBulkReturnCost)+($totalPhoneCost - $totalReturnCost);
	
	?>
	<form action='<?php echo $this->request->webroot;?>StockTransfer/summary_sale' method = 'get'>
    
		<div class="search_div">
			<fieldset>
				<legend>Search</legend>
				<table style="margin-top: -13px;margin-bottom: -18px;">
					<tr>
						<td>
							<input type = "text" name = "from_date" id = "datepicker_1" placeholder = "From date" style = "width:100px" value="<?php echo $from_date; ?>"/>
						</td>
						<td>
							<input type = "text" name = "to_date" id = "datepicker_2" placeholder = "To date" style = "width:100px" value="<?php echo $to_date; ?>"/>
						</td>
						<td style='position: relative;right: -45px;'>
							Select Kiosk:
						</td>
						<td style='position: relative;top: -8px;'>
							<?php echo $this->Form->input('kiosk',array('options'=>$kiosks,'label'=>false,'default'=>$kiosk));?>
						</td>
						<td>
							<input type = "submit" name = "submit" value = "Search"/>
						</td>
						<td>
							<input type = "button" name = "reset" value = "Reset" id="reset" style="border-radius: 5px;padding: 4px;"/>
						</td>
					</tr>
				</table>
			</fieldset>	
		</div>
	</form>

	<?php 
		$sumByWHCostPrice=0;
		foreach($transferredByWarehouse as $key=>$warehouseData){
			$whProductCode = $whProduct = $whCostPrice = '--';
            //pr($warehouseData);die;
			if(array_key_exists($warehouseData['product_id'],$productArr)){
				$whCostPrice = $productArr[$warehouseData['product_id']]['cost_price'];
				$sumByWHCostPrice+= $warehouseData['quantity']*$whCostPrice;
			}
		}
		$total+=$sumByWHCostPrice;
		$totalFixed+=$costTransByWh;
		$totalDetail.=number_format($sumByWHCostPrice,2)."-".number_format($total_dispute_cost,2);
		$totalDetailFixed.=number_format($costTransByWh,2);
	?>
	<?php
		
		$sumByKskCostPrice = 0;
		foreach($transferredByKiosk as $key=>$kioskData){
			$kskProductCode = $kskProduct = $kskCostPrice = '--';
            //pr($kioskData);die;
			if(array_key_exists($kioskData['product_id'],$productArr)){
				$kskCostPrice = $productArr[$kioskData['product_id']]['cost_price'];
				$sumByKskCostPrice+=$kioskData['quantity']*$kskCostPrice;
			}
		}
		$total+=-$sumByKskCostPrice;
		$total+=-$total_dispute_cost;
		
		$totalFixed+=-$costStockTransByKiosk;
		$totalFixed+=-$total_dispute_cost_static;
		$totalDetail.="-".number_format($sumByKskCostPrice,2);
		$totalDetail.="+".number_format($sumRepairCost,2)."+".number_format($sumUnlockCost,2)."+".number_format($totalPhoneCost,2).'+'.number_format($totalBulkPhoneCost,2)."-".number_format($totalReturnCost,2)."-".number_format($totalBulkReturnCost,2);
		
		$totalDetailFixed.="-".number_format($total_dispute_cost_static,2);
		$totalDetailFixed.="-".number_format($costStockTransByKiosk,2);
		$totalDetailFixed.="+".number_format($fixedRepairCost,2)."+".number_format($fixedUnlockCost,2)."+".$totalPhoneCost."+".number_format($totalBulkPhoneCost,2)."-".$totalReturnCost."-".number_format($totalBulkReturnCost,2);
		
		
	?>
	<?php
		$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
      $updateUrl = "/img/16_edit_page.png";
	?>
	<span style="float: right;"><i>**Fixed cost is only for the entries post 29th April 2016</i></span>
	<table cellpadding="0" cellspacing="0">
	<tr>
		<td><strong><?php #print_r($kioskOrders);
	echo __('<span style="font-size: 20px;color: red;">Billed Amount</span></strong>')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>";?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
		<?php if($from_date && $to_date){?>
		<h4>Accessory sale from <?=$from_date;?> to <?=$to_date;?></h4>
		<?php }else{echo "<br/>";} ?>
		<strong><span style="font-size: 17px;">Warehouse to Kiosk:</span></strong></td>
		<td><strong style="position: relative;top: 29px;right: 15px;">Amount</strong></td>
	</tr>
	
	<tr>
		<td><strong>Stock Transferred(Dynamic)</strong></td>
		<?php
			$sumByWHCostPrice = $CURRENCY_TYPE.$sumByWHCostPrice;
		?>
		<td><?php #echo $this->Html->link($sumByWHCostPrice,$crossStockUrl); ?>
		<?php echo "<a href='$crossStockUrl'>".$sumByWHCostPrice."</a>";?>
		</td>
	</tr>
	<tr>
		<td><strong>Stock Transferred(Fixed)</strong></td>
		<?php
			$costTransByWh = $CURRENCY_TYPE.$costTransByWh;
		?>
		<td><?php #echo $this->Html->link($sumByWHCostPrice,$crossStockUrl); ?>
		<?php echo "<a href='$crossStockUrlFixed'>".$costTransByWh."</a>";?>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><strong>Disputed Order(Dynamic)(Receive Less Cost- Receive More Cost)</strong></td>
		<td><?php echo "<a href = '$orderDisputeUrl'>".$total_dispute_cost."</a>"; ?></td>
	</tr>
	<tr>
		<tr>
		<td><strong>Disputed Order(Fixed)(Receive Less Cost- Receive More Cost)</strong></td>
		<td><?php echo "<a href = '$orderDisputeUrlFixed'>".$total_dispute_cost_static."</a>"; ?>
		</br>
		**Static costs  would be applicable from 13/04/2018 onwards.
		</td>
	</tr>
	<tr>
		<td><strong><span style="font-size: 17px;">Kiosk to Warehouse:</span></strong></td>
	</tr>
	<tr>
		
	</tr>
	<tr>
		<td><strong>Stock Returned(Dynamic)</strong></td>
		<td>
			
			<?php
			$sumByKskCostPrice = $sumByKskCostPrice;
			?>
			<?php #echo $this->Html->link($sumByKskCostPrice,$crossReturnUrl); ?>
			<?php echo "<a href='$crossReturnUrl'>".$CURRENCY_TYPE.$sumByKskCostPrice."</a>";?>
		</td>
	</tr>
	<tr>
		<td><strong>Stock Returned(Fixed)</strong></td>
		<td>
			
			<?php
			$costStockTransByKiosk = $CURRENCY_TYPE.$costStockTransByKiosk;
			?>
			<?php #echo $this->Html->link($sumByKskCostPrice,$crossReturnUrl); ?>
			<?php echo "<a href='$crossReturnUrlFixed'>".$costStockTransByKiosk."</a>";?>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><strong>Total Repair Cost(Dynamic)</strong></td>
		<td><?php
		$sumRepairCost = $CURRENCY_TYPE.$sumRepairCost;
		echo "<a href='$repairSaleUrl'>".$sumRepairCost."</a>";?></td>
	</tr>
	<tr>
		<td><strong>Total Repair Cost(Fixed)</strong></td>
		<td><?php
		$fixedRepairCost = $CURRENCY_TYPE.$fixedRepairCost;
		echo "<a href='$repairSaleUrlFixed'>".$fixedRepairCost."</a>";?></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		
	</tr>
	<tr>
		<td><strong>Total Unlock Cost(Dynamic)</strong></td>
		<td><?php $sumUnlockCost = $CURRENCY_TYPE.$sumUnlockCost;
		echo "<a href='$unlockSaleUrl'>".$sumUnlockCost."</a>";?></td>
	</tr>
	<tr>
		<td><strong>Total Unlock Cost(Fixed)</strong></td>
		<td><?php $fixedUnlockCost = $CURRENCY_TYPE.$fixedUnlockCost;
		echo "<a href='$unlockSaleUrlFixed'>".$fixedUnlockCost."</a>";?></td>
	</tr>
	
	<tr>
		<td>&nbsp;</td>
		
	</tr>
	<tr>
		<td><strong>Cost of Sold Phones</strong></td>
		<td><?php $totalPhoneCost = $CURRENCY_TYPE.$totalPhoneCost;
		echo "<a href='$phoneCostUrl'>".$totalPhoneCost."</a>";?></td>
	</tr>
	<tr>
		<td><strong>Cost of Bulk Sold Phones</strong></td>
		<td><?php $totalBulkPhoneCost = $CURRENCY_TYPE.$totalBulkPhoneCost;
		echo "<a href='$bulkPhoneCostUrl'>".$totalBulkPhoneCost."</a>";?></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		
	</tr>
	<tr>
		<td><strong>Total Mobile Return Cost</strong></td>
		<td><?php $totalReturnCost = $CURRENCY_TYPE.$totalReturnCost;
		echo "<a href='$phoneCostUrl'>".$totalReturnCost."</a>";?></td>
	</tr>
	<tr>
		<td><strong>Total bulk Mobile Return Cost</strong></td>
		<td><?php $totalBulkReturnCost = $totalBulkReturnCost;
		echo "<a href='$bulkPhoneCostUrl'>".$CURRENCY_TYPE.$totalBulkReturnCost."</a>";?></td>
	</tr>
	<tr>
		<td><strong style='background-color: yellow;'>Total(<?php echo $totalDetail;?>)</strong></td>
		<td><?php echo "<strong style='background-color: yellow;'>".$CURRENCY_TYPE.$total."</strong>";?></td>
	</tr>
	<tr>
		<td><strong style='background-color: yellow;'>Total(<?php echo $totalDetailFixed;?>)</strong></td>
		<td><?php echo "<strong style='background-color: yellow;'>".$CURRENCY_TYPE.$totalFixed."</strong>";?></td>
	</tr>
	</table>
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
<script>
	$('#reset').click(function(){
		$( "#datepicker_1" ).val("");
		$( "#datepicker_2" ).val("");
        $( "#kiosk" ).val("1");
		return false;
	});
</script>
</script>
<script>
$(function() {
  $( document ).tooltip({
   content: function () {
    return $(this).prop('title');
   }
  });
 });
</script>