<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>

<?php
	if(isset($kiosks) && !empty($kiosks)){
		$kiosks[-1] = "All";
	}
    if($this->request->query){
		$from_date = $this->request->query['from_date'];
		$to_date = $this->request->query['to_date'];
		$kiosk = $this->request->query['kiosk'];
	}
	$queryStr = "?req_type=fixed";
	//pr($this->request);
	if( isset($this->request->query['from_date']) ){
			$queryStr.="&from_date=".$this->request->query['from_date'];
	}
	if( isset($this->request->query['to_date']) ){
			$queryStr.="&to_date=".$this->request->query['to_date'];
	}
	
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
  
<div class="kiosks index">
	<h2>Billed Amount</h2>
	<?php echo $this->Form->create('KioskSearch',array('url'=>array('controller'=>'StockTransfer','action'=>'summary_sale'),'type'=>'get'));?>
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
        <?php echo $this->Form->end(); ?>
	<table>
		<tr>
			<th>Kiosk Name</th>
			<th style="width: 100px;">Total</th>
			<th>Stock Transferred</th>
			<th>Disputed Order</th>
			<th>Stock Returned</th>
			<th>Total Repair Cost</th>
			<th>Total Unlock Cost</th>
			<th>Cost of Sold Phones</th>
			<th>Cost of Bulk Sold Phones</th>
			<th>Total Mobile Return Cost</th>
            <th>Total bulk Mobile Return Cost</th>
            
		</tr>
        <?php
		$stock_trn_total = $order_dis_total = $stock_ret_total = $repair_cst_total = $unlock_cst_total = $phn_total = $blk_phn_total = $return_phn_total = $blk_return_phn_total = $final_total = 0;
		foreach($final_arr as $key => $value){
			
			$kioskStr = "&kiosk=".$key;
			$total = 0;
			?>
            <tr>
                <td><b>
                    <?php echo $kiosks[$key];
					?>
					</b>
                </td>
				<?php
				$total = $value['stock_transfer'] - $value['order_dispute'] - $value['stock_returned'] + $value['repair_cost'] + $value['unlock_cost'] + $value['sold_phone_cost'] + $value['bulk_sold_phone_cost'] - $value['return_phone_cost'] - $value['blk_return_phone_cost'];
				
				?>
				<td style="background-color: lawngreen;"><b>
					<?php echo $total;
					$final_total += $total;
					?>
					</b>
				</td>
                <td>
					<a href="export<?php echo $queryStr.$kioskStr;?>"><?php echo $value['stock_transfer']; ?></a>
                    <?php 
						$stock_trn_total += $value['stock_transfer'];
					?>
                </td>
                <td>
					<a href="disputed_order_export<?php echo $queryStr.$kioskStr;?>"><?php echo $value['order_dispute']; ?></a>
                    <?php 
						$order_dis_total += $value['order_dispute'];
					?>
                </td>
				<td>
					<a href="crossStockReturnExport<?php echo $queryStr.$kioskStr;?>"><?php echo $value['stock_returned']; ?></a>
					<?php 
						$stock_ret_total += $value['stock_returned'];
					?>
				</td>
				<td>
					<a href="totalRepairCostExport<?php echo $queryStr.$kioskStr;?>"><?php echo $value['repair_cost']; ?></a>
					<?php //echo $value["repair_cost"];
					$repair_cst_total += $value['repair_cost'];
					?>
				</td>
				<td>
					<a href="totalUnlockCostExport<?php echo $queryStr.$kioskStr;?>"><?php echo $value['unlock_cost']; ?></a>
					<?php //echo $value["unlock_cost"];
					$unlock_cst_total += $value['unlock_cost'];
					?>
				</td>
				<td>
					<a href="totalPhoneCostExport<?php echo $queryStr.$kioskStr."&refunded=1";?>"><?php echo $value['sold_phone_cost']; ?></a>
					<?php //echo $value["sold_phone_cost"];
						$phn_total += $value['sold_phone_cost'];
					?>
				</td>
				<td>
					<a href="totalBulkPhoneCostExport<?php echo $queryStr.$kioskStr."&refunded=1";?>"><?php echo $value['bulk_sold_phone_cost']; ?></a>
					<?php //echo $value["bulk_sold_phone_cost"];
					$blk_phn_total += $value['bulk_sold_phone_cost'];
					?>
				</td>
				<td>
					<a href="totalPhoneCostExport<?php echo $queryStr.$kioskStr."&refunded=0";?>"><?php echo $value['return_phone_cost']; ?></a>
					<?php //echo $value["return_phone_cost"];
					$return_phn_total += $value['return_phone_cost'];
					?>
				</td>
				<td>
					<a href="totalBulkPhoneCostExport<?php echo $queryStr.$kioskStr."&refunded=0";?>"><?php echo $value['blk_return_phone_cost']; ?></a>
					<?php //echo $value["blk_return_phone_cost"];
					$blk_return_phn_total += $value['blk_return_phone_cost'];
					?>
				</td>
				
            </tr>
        <?php } ?>
		<tr style="background-color: yellow;">
			<td><b>Total : </b></td>
			<td><b><?php echo $final_total; ?></b></td>
			<td>
				<a href="export<?php echo $queryStr;?>">
				<b><?php echo $stock_trn_total; ?></b>
				</a>
			</td>
			<td><a href="disputed_order_export<?php echo $queryStr;?>">
				<b><?php echo $order_dis_total; ?></b></td></a>
			<td><a href="crossStockReturnExport<?php echo $queryStr;?>">
				<b><?php echo $stock_ret_total; ?></b></a>
			</td>
			<td><a href="totalRepairCostExport<?php echo $queryStr;?>">
				<b><?php echo $repair_cst_total; ?></b></a>
			</td>
			<td><a href="totalUnlockCostExport<?php echo $queryStr;?>">
				<b><?php echo $unlock_cst_total; ?></b></a>
			</td>
			<td>
				<a href="totalPhoneCostExport<?php echo $queryStr."&refunded=1";?>">
				<b><?php echo $phn_total; ?></b></a>
			</td>
			<td>
				<a href="totalBulkPhoneCostExport<?php echo $queryStr."&refunded=1";?>">
				<b><?php echo $blk_phn_total; ?></b></a>
			</td>
			<td>
				<a href="totalPhoneCostExport<?php echo $queryStr."&refunded=0";?>">
				<b><?php echo $return_phn_total; ?></b></a>
			</td>
			<td><a href="totalBulkPhoneCostExport<?php echo $queryStr."&refunded=0";?>">
				<b><?php echo $blk_return_phn_total; ?></b></a>
			</td>
			
		</tr>
		<tr>
			<td><span id="a_1"></span></td>
			<td><span id="b_1"></span></td>
			<td><span id="c_1"></span></td>
			<td><span id="d_1"></span></td>
			<td><span id="e_1"></span></td>
			<td><span id="f_1"></span></td>
			<td><span id="g_1"></span></td>
			<td><span id="h_1"></span></td>
			<td><span id="i_1"></span></td>
		</tr>
		<tr>
			<td><span id="a_2"></span></td>
			<td><span id="b_2"></span></td>
			<td><span id="c_2"></span></td>
			<td><span id="d_2"></span></td>
			<td><span id="e_2"></span></td>
			<td><span id="f_2"></span></td>
			<td><span id="g_2"></span></td>
			<td><span id="h_2"></span></td>
			<td><span id="i_2"></span></td>
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
<script>
	$(window).on('load', function() {
		return false;
		<?php foreach($final_arr as $key => $value){ ?>
				var kiosk_id = <?php echo $key;?>;
				
				var b = test(kiosk_id);
		<?php }?>
	});
	 function test(kiosk_id) {
		var msg = "updating..."+kiosk_id;
				$.blockUI({ message: msg });
		var targeturl = $("#update_url").val();
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				var id = "#a_"+kiosk_id;
				$(id).text("testing");
				$("#b_"+kiosk_id).text("testing");
				$("#c_"+kiosk_id).text("testing");
				$("#d_"+kiosk_id).text("testing");
				$("#e_"+kiosk_id).text("testing");
				$("#f_"+kiosk_id).text("testing");
				$("#g_"+kiosk_id).text("testing");
				$("#h_"+kiosk_id).text("testing");
				var a = "test";
				$.unblockUI();
				return a;
			},
			error: function(e) {
				$.unblockUI();
				alert("An error occurred: " + e.responseText.message);
				console.log(e);
			}
		});
	}
</script>