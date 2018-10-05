<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php
//pr($refundData);die;
//pr($refundData);die;
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<?php
	if(!isset($start_date)){$start_date = "";}
	if(!empty($start_date)){
	 $start_date = date('d M Y',strtotime($start_date));
	}
	if(!isset($end_date)){$end_date = "";}
	if(!empty($end_date)){
	 $end_date = date('d M Y',strtotime($end_date));
	}
	if(!isset($search_kw)){$search_kw = "";}
	if(!isset($search_kw1)){$search_kw1 = "";}
	if(!isset($paymentMode)){$paymentMode = "";}
	$kiosks['-1'] = 'All';
	$kioskId = -1;
?>
<div class="mobileRepairLogs index">
	<h2><?php echo __('Wholesale Sale'); ?></h2>
	<form name= "search_form" id="search_form" action='<?php echo $this->request->webroot; ?>KioskProductSales/search_wholsale' method = 'get'>
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
						<td rowspan="3"><select id='category_dropdown' name='category[]' style="height: 82px;" multiple="multiple" size='6' onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select></td>
						
						<td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date"  style = "width:90px;height: 25px;"value='<?php echo $start_date;?>' /></td>
						<td><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date"  style = "width:90px;height: 25px;" value='<?php echo $end_date;?>'  /></td>
	<?php	
	if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == SALESMAN){?>
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
	 			
						<td><input type = "submit" value = "Search" name = "submit1" 'style' ='width:185px'/>
						<td><input type='button' name='reset' value='Reset' style='padding:6px 8px;color:#333; border:1px solid #bbb;border-radius:4px;' onClick='reset_search();'/>
						<table>
							<tr>
								<td></td>
							</tr>
							<tr>
								<td colspan='2'>
									 <?php $loggedInUser = $this->request->session()->read('Auth.User.username');
									if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering
										echo $this->Html->link(__('View Quotation'), array('controller' => 'kiosk_product_sales', 'action' => 'dr_index')); 
									 }
									 ?>
								</td>
							</tr>
						</table>
						</td>
						
					</tr>
					
				</table>
			
			</div>
		</fieldset>	
	</form>
	<table>
		<tr>
			<th>Kiosk</th>
			<th>Total Sale(Ex vat)</th>
			<?php if(!empty($search_kw) || !empty($catagory)){echo "<th>Qantity</th>";
			echo "<th>Cost Price</th>";}?>
			<th>Refund</th>
			<th>Net Sale after refund(Ex vat)</th>
		</tr>
		<?php
        
		//pr($t_sale_Arr);
		$final_vat = $final_with_vat = $final_qantity = $sale = $final_cost_price = $quantity = $grandSale = $grandRefund = $grandNetSale = 0;
		foreach($saleSumData as $kiosk_Id => $saleSum){
		$vat_amount = $with_vat = $total_w_cost = $sale =  $w_cost_price = 0;
		 if(!empty($search_kw) || !empty($catagory)){
			if(!empty($sale_Arr[$kiosk_Id])){
				foreach($sale_Arr[$kiosk_Id] as $key =>$value){
                    //pr($value);die;
					$sale_price = $value['sale_price'];
					$qantity = $value['quantity'];
					$dis_status = $value['discount_status'];
					$w_cost_price = $value['cost_price']*$qantity;
					$total_w_cost += $w_cost_price;
					$discount = $value['discount'];
					if(!empty($discount)){
						//$withot_vat_sale_price = $sale_price/(1+($vat/100));
						$discouted_val = $sale_price * ($discount/100);
						$val_s = ($sale_price - $discouted_val) * $qantity;
						$val_s = $val_s - $val_s*($bulk_dis_arr[$kiosk_Id][$value['product_receipt_id']]/100);
						$sale += $val_s;
						//$total_wv_sp = $qantity * $discouted_val;
						//$sale += $total_wv_sp * ($vat/100);
					}else{
						$sale_price = $sale_price - $sale_price*($bulk_dis_arr[$kiosk_Id][$value['product_receipt_id']]/100);
						$sale += $sale_price * $qantity;
					}
				}
                //echo $total_w_cost;die;
			}else{
				$sale = 0;
			}
			 $vat_amount = 	$sale * ($vat/100);
			 $final_vat += $vat_amount;
			 $with_vat = $sale +$vat_amount;
			 $final_with_vat += $with_vat;
		 }else{
			 $with_vat = $saleSum['totalsale'];
			 $sale = 	$with_vat / (1+($vat/100));
			 $vat_amount = $with_vat - $sale;
			 $final_vat += $vat_amount;
			 $final_with_vat += $with_vat;
		 }
		 //pr($refundData);die;
		 $refund = 0;
		 $refund = $refundData[$kiosk_Id];
		 
		 $total_sale = $sale; //+ $refund;
		//echo $total_sale;die;
		 if(!empty($qanArr)){
			 $quantity = $qanArr[$kiosk_Id];
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
			 $netSale = $CURRENCY_TYPE.number_format($netSale,2);
		 }
		 
		 if(!empty($search_kw) || !empty($catagory)){
		  $final_qantity += $quantity;
		  $final_cost_price += $total_w_cost;
		  }
		 
		 ?>
		<tr>
			<td><?=$kiosks[$kiosk_Id];?></td>
			<td><?php echo $total_sale;
            //$total_sale = (float)$total_sale;echo number_format($total_sale,2);?></td>
			<?php if(!empty($search_kw) || !empty($catagory)){echo "<td>";?> <?php echo $quantity; echo "</td>";
			echo "<td>"; echo $total_w_cost; echo "</td>";
			}?>
			<td><?=$refund;?></td>
		
			<td><?=$netSale;?></td>
		</tr>
		<?php
		$t_qan = $t_vat_amount = $t_with_vat = $t_total_w_cost = $t_sale =  $t_w_cost_price = $t_cost = 0;
			if(!empty($search_kw) || !empty($catagory)){
				if($kiosk_Id == 10000){
					$k_id = 0;
				}else{
					$k_id = $kiosk_Id;
				}
                //pr($t_sale_Arr);die;
				if(!empty($t_sale_Arr[$k_id])){
					//if(array_key_exists(0,$t_sale_Arr[$k_id])){
						//pr($t_sale_Arr[$k_id]);die;
						foreach($t_sale_Arr[$k_id] as $t_key =>$t_value){
							//pr($t_value);//die;
							$t_sale_price = $t_value['sale_price'];
							$t_qty = $t_value['quantity'];
							$t_dis_status = $t_value['discount_status'];
							$t_w_cost_price = $t_value['cost_price']*$t_qty;
							$t_total_w_cost += $t_w_cost_price;
							$t_discount = $t_value['discount'];
							if(!empty($t_discount)){
								$t_discount = $t_value['discount'];
								$t_discouted_val = $t_sale_price * ($t_discount/100);							
								$res_s = ($t_sale_price - $t_discouted_val) * $t_qty;
								$res_s = $res_s-$res_s*($t_bulk_dis_arr[$k_id][$t_value['product_receipt_id']]/100);
								$t_sale += $res_s;
							}else{
								$t_sale_price = $t_sale_price-$t_sale_price*($t_bulk_dis_arr[$k_id][$t_value['product_receipt_id']]/100);
								$t_sale += $t_sale_price * $t_qty;
							}
						}
						$t_vat_amount = 	$t_sale * ($vat/100);
						 //$t_final_vat += $t_vat_amount;
						 $t_with_vat = $t_sale +$t_vat_amount;
						 //$t_final_with_vat += $t_with_vat;
					//}
					
				}else{
					$t_sale = 0;
				}
			}else{
				if($kiosk_Id == 10000){
					$k_id = 0;
				}else{
					$k_id = $kiosk_Id;
				}
				$t_sale = $t_data[$k_id];
                
				//$t_with_vat = $t_data[$k_id];
				//$t_sale = 	$t_with_vat / (1+($vat/100));
				//$t_vat_amount = $t_with_vat - $t_sale;
				
				
				//$t_final_vat += $t_vat_amount;
				//$final_with_vat += $t_with_vat;
			}
		?>
		<?php $loggedInUser = $this->request->session()->read('Auth.User.username');
		if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
		?>
		
		<tr>
			<td><b><?=$kiosks[$kiosk_Id]."(Quotation)";?></b></td>
			<td><b><?php echo  $CURRENCY_TYPE.number_format($t_sale,2) ; ?></b></td>
			<?php
			if(!empty($search_kw) || !empty($catagory)){
				if($kiosk_Id == 10000){
					if(array_key_exists(0,$t_qantity)){
						$t_qan = $t_qantity[0];
						$t_cost = $t_cost_price[0];
					}
				}else{
					if(array_key_exists($kiosk_Id,$t_qantity)){
						$t_qan = $t_qantity[$kiosk_Id];
						$t_cost = $t_cost_price[$kiosk_Id];
					}
				}
				
				echo "<td><b>";?> <?php echo $t_qan; echo "</b></td>";
				echo "<td><b>"; echo  $t_cost  ; echo "</b></td>";
			}?>
					
			<td><b><?php
			if($kiosk_Id == 10000){
				if($t_data['refund_data'][0] == ""){
                   echo $CURRENCY_TYPE."0.00";
					$data_to_Show = $t_sale;
				}else{
                    echo $CURRENCY_TYPE.number_format($t_data['refund_data'][0],2)  ;
					$grandRefund = $grandRefund + $t_data['refund_data'][0];
					$data_to_Show = $t_sale-$t_data['refund_data'][0];
				}
				
			}else{
				//pr($t_data);die;
				if($t_data['refund_data'][$kiosk_Id] == ""){
                    echo $CURRENCY_TYPE."0.00";
					$data_to_Show = $t_sale;
				}else{
					echo $CURRENCY_TYPE.number_format($t_data['refund_data'][$kiosk_Id],2) ;
					$grandRefund = $grandRefund + $t_data['refund_data'][$kiosk_Id];
					$data_to_Show = $t_sale-$t_data['refund_data'][$kiosk_Id];
				}
				
			}
			 ?></b></td>
			<td><b><?php  echo number_format($data_to_Show,2); ?></b></td>
		</tr>
		<?php
			$grandSale = $grandSale + $t_sale;
			$grandNetSale = $grandNetSale + $data_to_Show;
			$final_vat = $final_vat + $t_vat_amount;
			$final_with_vat = $final_with_vat + $t_with_vat;
			if(!empty($search_kw) || !empty($catagory)){
				$final_qantity = $final_qantity + $t_qan;
				$final_cost_price = $final_cost_price + $t_cost;
			}
		?>
		
		<?php } ?>
		
  <?php } ?>
		<tr>
			<td><strong>Grand Total</strong></td>
			<td><?=$CURRENCY_TYPE.number_format($grandSale,2);?></td>
			<?php if(!empty($search_kw) || !empty($catagory)){ ?>
			<td><?=$final_qantity;?></td>
			<td><?=number_format($final_cost_price,2);?></td>
			<?php } ?>
			<td><?=$CURRENCY_TYPE.number_format($grandRefund,2);?></td>
			<td><?=$CURRENCY_TYPE.number_format($grandNetSale,2);?></td>
		</tr>
	</table>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php #echo $this->Html->link(__('New Mobile Repair Log'), array('action' => 'add')); ?></li>
		<li><?php #echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php #echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('All Kiosk Sale'), array('controller' => 'kiosk_product_sales', 'action' => 'all_kiosk_sale')); ?></li>
			   <li><?php echo $this->Html->link(__('All WholeSale Sale'), array('controller' => 'kiosk_product_sales', 'action' => 'all_wholesale_kiosk_sale')); ?></li>
			   <li><?php echo $this->Html->link(__('View Sales'), array('controller' => 'kiosk_product_sales', 'action' => 'index')); ?></li>
			    <li><?php echo $this->Html->link(__('Kiosk Sale Stat'), array('controller' => 'ProductSellStats', 'action' => 'index')); ?></li> 
	</ul>
</div>
 <script>
	function reset_search(){
		jQuery( "#datepicker1" ).val("");
		jQuery( "#datepicker2" ).val("");
		jQuery("#search_kw").val("");
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
			footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------<?php echo ADMIN_DOMAIN;?>---------</b></div>"),
		}
	});
</script>
<script>
   $('#kioskid').change(function(){
	$.blockUI({ message: 'Loading ...' });
	document.getElementById("search_form").submit();
	  }); 
</script>