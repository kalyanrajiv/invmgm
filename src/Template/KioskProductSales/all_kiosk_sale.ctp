<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<style>
 #remote .tt-dropdown-menu {max-height: 250px;overflow-y: auto;}
 #remote .twitter-typehead {max-height: 250px;overflow-y: auto;}
.tt-dataset, .tt-dataset-product {max-height: 250px;overflow-y: auto;}
.row_hover:hover{color:blue;background-color:yellow;}
</style>
<?php
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
	//pr($paymentArr);die;
	//echo $paymentMode;die;
	if(!isset($start_date)){$start_date = "";}
	if(!isset($end_date)){$end_date = "";}
	if(!isset($search_kw)){$search_kw = "";}
	if(!isset($search_kw1)){$search_kw1 = "";}
	if(!isset($paymentMode)){$paymentMode = "";}
	if(!isset($catagoryIds)){$catagoryIds = "";}
	//echo $wholesale;die;
	if(!isset($retail)){$retail = "";}
	if(!isset($wholesale)){$wholesale = 1;}
	$kiosks['-1'] = 'All';
	//pr($this->request);die;
	if(!isset($kioskId)){
		$kioskId = '';
	}
	if(array_key_exists('kiosk_id',$this->request->query)){
		$kioskId = $this->request->query['kiosk_id'];
	}
	if(array_key_exists('category_id',$this->request->query)){
		$catagoryIds = $this->request->query['category_id'];
	}
	//pr($this->request->query);
	//echo $catagoryIds;die;
	if(!empty($start_date)){$start_date = date('d M Y',strtotime($start_date));}
	if(!empty($end_date)){$end_date = date('d M Y',strtotime($end_date));}
?>
<div class="mobileRepairLogs index">
	<?php
	$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
      $updateUrl = "/img/16_edit_page.png";
	?>
	<h2><?php echo __('Retail Kiosk Sale')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>";?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	</h2>
	<form name= "search_form" id = "search_form" action='<?php echo $this->request->webroot; ?>KioskProductSales/search_kiosk_sale' method = 'get'>
		<fieldset>
			<legend>Search</legend>
			<div style="height: 69px;">
				<table>
					<tr>
						<td>
							<div id='remote'>
								<input class="typeahead" type = "text" name = "search_kw" id = "search_kw" placeholder = "product name or code" autofocus style = "width:150px;height: 25px;"value='<?php echo $search_kw;?>'/>
							</div>
						</td>
						<td rowspan="3"><select id='category_dropdown' name='category[]' style="height: 97px;" multiple="multiple" size='6' onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select></td>
						<td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date"  style = "width:90px;height: 25px;"value='<?php echo $start_date;?>' /></td>
						<td><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date"  style = "width:90px;height: 25px;" value='<?php echo $end_date;?>'  /></td>
	<?php	
	if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){?>
						<td>
		
							<?php
								if(!empty($kioskId)){
									echo $this->Form->input(null, array(
										'options' => $kiosks,
										 'label' => false,
										 'div' => false,
										       'name' => 'ProductSale[kiosk_id]',
										      'id'=> 'kioskid',
										      'value' => $kioskId,
										      //'empty' => 'Select Kiosk',
										      'style' => 'width:185px'
											)
										);
								}else{
										echo $this->Form->input(null, array(
											'options' => $kiosks,
											'label' => false,
											'div' => false,
											 'name' => 'ProductSale[kiosk_id]',
											'id'=> 'kioskid',
											//'empty' => 'Select Kiosk',
											'style' => 'width:185px'
												)
											);
								      }
								?></span>
						</td>
							<?php  }  ?>
	 			
						<td><input type = "submit" value = "Search"  'style' ='width:185px'/>
						<td><input type='button' name='reset' value='Reset' style='padding:6px 8px;color:#333; border:1px solid #bbb;border-radius:4px;' onClick='reset_search();'/></td>
						
					</tr>
					<tr>
					</tr>
				</table>
			
			</div></br>
		</fieldset>	
	</form>
	<table>
		<tr>
			<th>Kiosk</th>
			<th>Total Sale
			</th>
			<?php if(!empty($search_kw) || !empty($catagoryIds)){echo "<th>Qantity</th>";
			echo "<th> Cost Price
			
			</th>";}?>
			<th>Refund</th>
			<th>Net Sale</th>
			<th style="color: blue;">vat</th>
			<th style="color: brown;">wiithout vat sale</th>
		</tr>
		<?php
		//pr($sale_Arr);
		//pr($saleSumData);die;
		$final_cost_price =  $netQantity = $total_price = $grandWVatValue = $grandVatValue = $grandSale = $grandRefund = $grandNetSale = 0;
        //pr($saleSumData);die;
		foreach($saleSumData as $kiosk_Id => $saleSum){
            //pr($saleSum);die;
			$total_r_cost = $w_total_cost_price = $sale = $sum = $quantity = $price = 0;
			if(!empty($wholesale_Arr)){
				if(!empty($search_kw) || !empty($catagoryIds)){
					if(!empty($wholesale_Arr[$kiosk_Id])){
						//pr($sale_Arr[$kiosk_Id]);die;
						foreach($wholesale_Arr[$kiosk_Id] as $key =>$value){
                            //pr($value);die;
							$sale_price = $value['KioskProductSale']['sale_price'];
							$qantity = $value['KioskProductSale']['quantity'];
							$dis_status = $value['KioskProductSale']['discount_status'];
							$w_cost_price = $value['KioskProductSale']['cost_price'] * $qantity;
							$w_total_cost_price += $w_cost_price; 
							if($dis_status == 0){
								$discount = $value['KioskProductSale']['discount'];
								//$withot_vat_sale_price = $sale_price/(1+($vat/100));
								$discouted_val = $sale_price * ($discount/100);							
								$sum += ($sale_price - $discouted_val) * $qantity;
								//$total_wv_sp = $qantity * $discouted_val;
								//$sale += $total_wv_sp * ($vat/100);
							}else{
								$sum += $sale_price * $qantity;
							}
						}
					}else{
						$sum = 0;
					}
				}else{
					//foreach($wholesale_Arr[$kiosk_Id] as $key =>$value){
					//	$w_cost_price = $value['KioskProductSale']['cost_price'];
					//	$w_total_cost_price += $w_cost_price; 
					//}
					$sum = $sum_arr[$kiosk_Id];
				}
				
			}
			if(!empty($qanArr)){
				$quantity = $qanArr[$kiosk_Id];
			}
			if(!empty($search_kw) || !empty($catagoryIds)){
				if(!empty($sale_Arr[$kiosk_Id])){
					//pr($sale_Arr[$kiosk_Id]);die;
					foreach($sale_Arr[$kiosk_Id] as $key =>$value){
                        //pr($value);die;
						$sale_price = $value['sale_price'];
						$qantity = $value['quantity'];
						$dis_status = $value['discount_status'];
						$r_cost_price = $value['cost_price']*$qantity;
						$total_r_cost += $r_cost_price;
						if($dis_status == 1){
							$discount = $value['discount'];
							//$withot_vat_sale_price = $sale_price/(1+($vat/100));
							$discouted_val = $sale_price * ($discount/100);							
							$sale += ($sale_price - $discouted_val) * $qantity;
							//$total_wv_sp = $qantity * $discouted_val;
							//$sale += $total_wv_sp * ($vat/100);
						}else{
							$sale += $sale_price * $qantity;
						}
					}
				}else{
					$sale = 0;
				}
			}else{
				//foreach($sale_Arr[$kiosk_Id] as $key =>$value){
				//	$r_cost_price = $value['KioskProductSale']['cost_price']*$qantity;
				//	$total_r_cost += $r_cost_price;
				//}
				$sale = $saleSum['totalsale']; 
			}
			
			$refund = $refundData[$kiosk_Id]['todayProductRefund'];
			$vat_val = $sum * $vat/100;
			$sum = $sum+$vat_val;
			$total_sale = $sale + $refund; //+ $sum;
			//$cardP = $cardPayment[$kiosk_Id][0]['totalsale'];
			//$cashP = $cashPayment[$kiosk_Id][0]['totalsale'];
			
			//if(is_numeric($cardP)){
			//	$cardP = $CURRENCY_TYPE.$cardP;
			//}
			//
			//if(is_numeric($cashP)){
			//	$cashP = $CURRENCY_TYPE.$cashP;
			//}
			
			if($paymentMode == 'Card' && !empty($sale)){
				$refund = 0;
			}
			
			if(is_numeric($sale) && is_numeric($refund)){
				$netSale = $total_sale - $refund; //$sale
			}else{
				$netSale = $total_sale; //$sale
			}
			if(is_numeric($total_sale)){ // $sale
				$grandSale+=$total_sale;//$sale;
				$total_sale = $CURRENCY_TYPE.number_format($total_sale,2);
			}
			if(is_numeric($refund)){
				$grandRefund+=$refund;
				$refund = $CURRENCY_TYPE.number_format($refund,2);
			}
			if(is_numeric($netSale)){
				$grandNetSale+=$netSale;
				//$vatValue = $netSale * ($vat/100);
				//$withoutVatSale = $netSale - $vatValue;
				
				$withoutVatSale = $netSale/(1+($vat/100));
				$vatValue = $netSale - $withoutVatSale;
				
				
				$grandVatValue += $vatValue;
				$grandWVatValue += $withoutVatSale;
				
				$vatValue = $CURRENCY_TYPE.number_format($vatValue,2);
				$withoutVatSale = $CURRENCY_TYPE.number_format($withoutVatSale,2);
				$netSale = $CURRENCY_TYPE.number_format($netSale,2);
				$netQantity += $quantity;
			}
			if($search_kw){
				$wSale = $sum;
			}else{
				if(empty($sum_arr[$kiosk_Id])){
					$wSale = 0;
				}else{
					$wSale = $sum_arr[$kiosk_Id];
				}
			}
			
			$final_cost_price += $total_r_cost; //+ $w_total_cost_price;
			
			?>
			<tr>
				<td><?=$kiosks[$kiosk_Id];?></td>
				<td><?=$total_sale;?></td> 
				<?php if(!empty($search_kw) || !empty($catagoryIds)){echo "<td>";?> <?php echo $quantity;?>
				<td><?=$CURRENCY_TYPE.$total_r_cost;?><?php echo "</td>";}?>
				<td><?=$refund;?></td>
				<td><?=$netSale?></td>
				<td style="color: blue;"><?=$vatValue;?></td>
				<td style="color: brown;"><?=$withoutVatSale;?></td>
			</tr>
		<?php } ?>
		<tr>
			<td><strong>Grand Total</strong></td>
			<td><?=$CURRENCY_TYPE.number_format($grandSale,2);?></td>
			<?php if(!empty($search_kw) || !empty($catagoryIds)){ echo "<td> $netQantity</td>";}?>
			<?php if(!empty($search_kw)|| !empty($catagoryIds)){echo "<td>";?><?=$CURRENCY_TYPE.$final_cost_price;?><?php echo "</td>";}?>
			<td><?=$CURRENCY_TYPE.number_format($grandRefund,2);?></td>
			<td><?=$CURRENCY_TYPE.number_format($grandNetSale,2);?></td>
			<td><?=$CURRENCY_TYPE.number_format($grandVatValue,2);?></td>
			<td><?=$CURRENCY_TYPE.number_format($grandWVatValue,2);?></td>
		</tr>
	</table>
	** Vat may or may not be correct for wholesale case but it is correct for retail case.
	<p>
		<?php
	//	echo $this->Paginator->counter(array(
		//'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
		//));
		?>	</p>
	<div class="paging">
	<?php
		//echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		//echo $this->Paginator->numbers(array('separator' => ''));
		//echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
	
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php #echo $this->Html->link(__('New Mobile Repair Log'), array('action' => 'add')); ?></li>
		<li><?php #echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php #echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('View Sales'), array('controller' => 'kiosk_product_sales', 'action' => 'index')); ?> </li>
        <?php  if($this->request->session()->read('Auth.User.group_id')== ADMINISTRATORS){?>
		<li><?php echo $this->Html->link(__('All Kiosk Sale'), array('controller' => 'kiosk_product_sales', 'action' => 'all_kiosk_sale')); ?></li>
		<li><?php echo $this->Html->link(__('All WholeSale Sale'), array('controller' => 'kiosk_product_sales', 'action' => 'all_wholesale_kiosk_sale')); ?></li>
		<li><?php echo $this->Html->link(__('Kiosk Sale Stat'), array('controller' => 'ProductSellStats', 'action' => 'index')); ?></li> 
        <?php  } ?> 
	</ul>
</div>
<script>
	function reset_search(){
		jQuery( "#datepicker1" ).val("");
		jQuery( "#datepicker2" ).val("");
		jQuery("#search_kw").val("");
		jQuery("#search_kw1").val("");
        jQuery("#category_dropdown").val("");
        jQuery("#kioskid").val("-1");
		$('#cash_id').attr('checked', false)
		$('#card_id').attr('checked', false)
		$('#multiple_id').attr('checked', false)
		$('#refunded_radio').attr('checked', false)
	}
	jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
	});
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
<input type='hidden' name='url_category' id='url_category' value=''/>
<script type="text/javascript">
 function update_hidden(){
  //var singleValues = $( "#single" ).val();
  var multipleValues = $( "#category_dropdown" ).val() || [];
  $('#url_category').val(multipleValues.join( "," ));
 }
</script>
<script>
	var product_dataset = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: "/Products/admin_data?category=%CID&search=%QUERY",
			replace: function (url,query) {
				var multipleValues = $( "#category_dropdown" ).val() || [];
				$('#url_category').val(multipleValues.join( "," ));
				return url.replace('%QUERY', query).replace('%CID', $('#url_category').val());
			},
			wildcard: "%QUERY"
		}
	});

	$('#remote .typeahead').typeahead(null, {
		name: 'product',
		display: 'product',
		source: product_dataset,
		limit:120,
		minlength:3,
		classNames: {
			input: 'Typeahead-input',
			hint: 'Typeahead-hint',
			selectable: 'Typeahead-selectable'
		},
		highlight: true,
		hint:true,
		templates: {
			suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:400px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{product_code}}</a></strong></div>'),
			header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
			footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------hpwaheguru.co.uk---------</b></div>"),
		}
	});
</script>
<script>
   $('#kioskid').change(function(){
    $.blockUI({ message: 'Loading ...' });
	document.getElementById("search_form").submit();
	  }); 
</script>