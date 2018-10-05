
<div class="productReceipts index">
	<?php
	if(!isset($cust_hidden_id)){
		$cust_hidden_id = 0;
	}
	$kskId = $this->request->Session()->read('kiosk_id');
	if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == SALESMAN || $this->request->session()->read('Auth.User.group_id') == inventory_manager ){//saleman
		if(array_key_exists(0,$this->request->params['pass'])){
			$kskId = $this->request->params['pass'][0];
		}
		$kiosk_id = 0;
	}else{
		$kiosk_id = $this->request->Session()->read('kiosk_id');
	}
	$totalPaymentAmount = 0;
	$grandNetAmount = 0;
	$totalVat = 0;
    //$product_receipt_data[$productReceipt->product_receipt_id]['status'] = $productReceipt
	//pr($productReceipts);die;
	foreach($productReceipts as $key => $productReceipt){
        //pr($productreceiptArr);die;
		//showing the data with product receipt status == 0 ie. the payment went through and data saved properly
		if(!array_key_exists($productReceipt->product_receipt_id,$productreceiptArr)){
			continue;
		}
		if($productreceiptArr[$productReceipt->product_receipt_id]['status']==0){
			if(!array_key_exists($productReceipt->product_receipt_id,$recit_ids)){
						continue;
				}
			$paymentAmount = $productReceipt->amount;
			$totalPaymentAmount+=$paymentAmount;
			$vatPercentage = $productreceiptArr[$productReceipt->product_receipt_id]['vat']/100;
			$netAmount = $paymentAmount/(1+$vatPercentage);
			$grandNetAmount+=$netAmount;
		}
	}
	$totalVat = $totalPaymentAmount - $grandNetAmount;
	if(!isset($start_date)){$start_date = "";}
	if(!isset($end_date)){$end_date = "";}
	if(!isset($searchKeyword)){$searchKeyword = "";}//textKeyword
	if(!isset($textKeyword)){$textKeyword = "";}
	if(!isset($invoiceSearchKeyword)){$invoiceSearchKeyword = "";}
	if(!isset($date_type)){$date_type = "invoice";}
	if(!isset($agent_id)){$agent_id = 0;}
	
	?>
	<?php $webroot = $this->request->webroot."product-receipts/dr_search";?>
	<form id ="search_form" action="<?php echo $webroot;?>" method = 'get'>
	<fieldset>
		<legend>Payment Type</legend>
		<table style="
    margin-bottom: -21px;
    margin-top: -18px;
    margin-left: -2px;">
			<tr>
				
				<td><input type="radio" name="date_type" value="invoice" <?php if($date_type == "invoice"){echo "checked";}?>>invoice date</td>	
				<td><input type="radio" name="date_type" value="payment" <?php if($date_type == "payment"){echo "checked";}?>>payment date</td>
				<td>
					<?php echo $this->Html->link(
									'Missing Payment',
									['controller' => 'missingpayment', 'action' => 'dr-kiosk-payment']
								);
				?>
				</td>
			</tr>
			<tr>
				<td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date" style = "width:100px;margin-top: 5px;" value='<?php echo $start_date;?>' /><br/><div style="padding-top: 4px; margin-top: 12px;margin-left: -6px;"><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date" style = "width:100px" value='<?php echo $end_date;?>' /></div></td>
				<?php
				$payment_type = '';
				$invoice_detail = '';
				if(array_key_exists('payment_type',$this->request->query)){
					$payment_type = $this->request->query['payment_type'];
				}
				if(array_key_exists('invoice_detail',$this->request->query)){
					$invoice_detail = $this->request->query['invoice_detail'];
				}else{
					$invoice_detail = "receipt_number";
				}
				if(empty($this->request->query)){
					$payment_type = "All";
				}
				?>
				<td>
					<table>
					<tr>
						<td><input type="radio" name="payment_type" value="On Credit" <?php if($payment_type == "On Credit"){echo "checked";}?>>On credit</td>
						<td><input type="radio" name="payment_type" value="Cash" <?php if($payment_type == "Cash"){echo "checked";}?>>Cash</td>
						<td><input type="radio" name="payment_type" value="Card" <?php if($payment_type == "Card"){echo "checked";}?>>Card</td>
					</tr>
					<tr>
						<td><input type="radio" name="payment_type" value="Bank Transfer" <?php if($payment_type == "Bank Transfer"){echo "checked";}?>>Bank Tx</td>
						<td><input type="radio" name="payment_type" value="Cheque" <?php if($payment_type == "Cheque"){echo "checked";}?>>Cheque</td>
						<td><input type="radio" name="payment_type" value="All" <?php if($payment_type == "All"){echo "checked";}?>>All</td>
					</tr>
					</table>
				</td>
				<td>
					<table>
					<tr>
						<?php ////saleman add by rajju
						if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == SALESMAN || $this->request->session()->read('Auth.User.group_id') == inventory_manager){?>
						<td>
							 <select name = "kiosk_id" id="search_kiosks" >
								<?php
								//echo $kiosk;die;
								if($kiosk == 0){ //onchange="this.form.submit()"
									$kiosk = 10000;
								}
								foreach($kiosk_list as $skey => $svalue){?>
									<option  value="<?php echo $skey;?>"<?php if($skey == $kiosk){echo "selected=selected";}?>><?php echo $svalue;?></option>
									<?php } ?>
							</select> 
							
						</td>
						<?php } ?>
						<td><input type="radio" id="recipt_radio" name="invoice_detail" class = "radio1" value="receipt_number" <?php if($invoice_detail == "receipt_number"){echo "checked";}?>>Receipt number</td>
						<td><input type="radio" id="cust_radio" name="invoice_detail" class = "radio1" value="customer_id" <?php if($invoice_detail == "customer_id"){echo "checked";}?>>Customer number</td>
						<td>
							<?php
								echo "<div id='remote'>";
						echo "<input name='search_kw' class='typeahead' id='cust_email' placeholder='Customer email, mobile or business' style = 'width:148px;height: 50px;margin-bottom: 10px;'   autofocus/>";;
						echo "</div>";
							?>
						</td>
					</tr>
					<tr>
						<?php //saleman add by rajju
						if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == SALESMAN || $this->request->session()->read('Auth.User.group_id') == inventory_manager ){?>
						<td>
							<select id="agent_id" name="agent_id">
								<?php foreach($agents as $ag_key => $ag_value){//onchange="this.form.submit()"?>
								
									<option  value="<?php echo $ag_key;?>"<?php if($ag_key == $agent_id){echo "selected=selected";}?>><?php echo $ag_value;?></option>
									<?php } ?>
							</select>
						</td>
						<?php } ?>
						<td>
							<input type="hidden" value="<?php echo $cust_hidden_id;?>" id="cust_hidden_id"/>
							<input type="text" name="search_kw" style="width: 100px;margin-right: -30px;" id = "search_kw" placeholder = "business,cst id or inv#" value="<?php echo $textKeyword;?>"></td>
						
						<td colspan="2" ><input type="submit" name="submit1" id="submit1" value="Search Payment" style="margin-left: 3px;">
						<input type='button' name='reset' value='Reset Search' style='padding:4px 8px;background:#dcdcdc;color:#333;border:1px solid #bbb;border-radius:4px;width: 120px;height: 30px;' onClick='reset_search();'/></td>
						
					</tr>
					</table>
				</td>
			</tr>
		</table>
	</fieldset>
	</form>
	<?php
		$dueButtonStr = "";
		if(!empty($textKeyword)){
			$dueImgLink = $this->Html->image("button_manage-dues.png", ['fullBase' => true]);
			$dueButtonStr = "<td style='width: 100px;'><a href='#' onclick='add_drBulkUrl();'>$dueImgLink</a></td>";
		}
	
		if(!empty($textKeyword)){ ?>
		<table>
		<tr>
			<td style='width: 100px;'><a href="#" onclick="add_drAccountUrl();"><?php echo $this->Html->image("button_acct-stmt.png", ['fullBase' => true]);?></a></td>
			<?php echo $dueButtonStr;?>
			<td><table>
				<tr><td><span style="float: right;"><i>**Total on top comes as per the search result</i></span></td></tr>
				<tr><td><span style="float: right;"><i>**Total at bottom comes per page</i></span></td></tr>
			</table></td>
		</tr>
		<tr><td colspan='5'><span style="float: right;"><i>**Change in customer will not change account manager, in that case origianl account manger will stand for that invoice.</i></span></td></tr>
	</table>
	<?php }else{ ?>
	<span style="float: right;"><i>**Fixed cost is only for the entries post 29th April 2016</i></span>
	<span style="float: right;"><i>**Total on top comes as per the search result</i></span>
	<span style="float: right;"><i>**Total at bottom comes per page</i></span>
	<?php } ?>
	<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<td colspan="5">
				<?php //saleman add by rajju
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == SALESMAN || $this->request->session()->read('Auth.User.group_id') == inventory_manager){
					if($kiosk == 0){
						$kiosk = 10000;
					}
					?>
				<strong><?php echo __('<span style="color:red; font-size:20px;">'.$kiosk_list[$kiosk].' Quotation</span>').$this->Html->link(' (view all invoices)',array('action'=>'all_invoices')); ?></strong>
				<?php }else{ ?>
				<strong><?php echo __('<span style="color:red; font-size:20px;">Processed Quotation</span>').$this->Html->link(' (view all invoices)',array('action'=>'all_invoices')); ?></strong>
				<?php } ?>
			</td>
			<?php //saleman add by rajju
			if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS ||
			   //$this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER||
			   //$this->request->session()->read('Auth.User.group_id') == SALESMAN ||
			$this->request->session()->read('Auth.User.group_id') == inventory_manager ){?>
			<td><strong> Dynamic Cost </strong>&#163;<?=number_format($totalCost,2);?>,</td>
			<td><strong> Fixed Cost </strong>&#163;<?=number_format($fixed_cost_sum['fixed_cost'],2);?>,</td>
			<?php } ?>
			<?php /*if(array_key_exists(0,$this->request->params['pass'])){
					$s_kskId = $this->request->params['pass'][0];
			}else{
				$s_kskId = $this->Session->read('kiosk_id');
			}
			if(empty($s_kskId)){
				$s_kskId = 0;
			}
			if($s_kskId == 0){*/
			?>
			
			
			<?php // } ?>
			<td><strong>Total</strong>&nbsp;&#163;<?=number_format($lptotalPaymentAmount,2); //$lptotalPaymentAmount?></td>
			
		</tr>
	<tr>
		<th>Invoice Date</th>
			<th><?php echo $this->Paginator->sort('created','Payment Date'); ?></th>
			<th><?php echo $this->Paginator->sort('product_receipt_id',"#Invoice"); ?></th>
			<th>Customer</th>
			<th>Business</th>
			<th>#Cust</th>
            	<?php //saleman add by rajju
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS ||
				   //$this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
				   //$this->request->session()->read('Auth.User.group_id') == SALESMAN ||
				   $this->request->session()->read('Auth.User.group_id') == inventory_manager ){?>
			<th><span style='float: right;'><?php echo $this->Paginator->sort('bill_cost','Bill Cost(Fixed)'); ?></span></th>
            	<?php } ?>
			<th><span style='float: left;'>Payment</span></th>
			<th><span style='float: right;'><?php echo $this->Paginator->sort('payment_method',"Mode"); ?></span></th>
			<th><span style='float: right;'>Total</span></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
		$final_bill_cost = $tolalBillCosting = 0.0;
		//pr($productReceipts);
		$recipt_cost_arry = array();
		foreach ($productReceipts as $productReceipt):
			if(!array_key_exists($productReceipt->product_receipt_id,$productreceiptArr)){
			continue;
		}
			if($productreceiptArr[$productReceipt->product_receipt_id]['status']==0){
				if(!array_key_exists($productReceipt->product_receipt_id,$recit_ids)){
						continue;
				}
				if(!empty($productreceiptArr[$productReceipt->product_receipt_id]['bill_cost'])){
					$tolalBillCosting = $tolalBillCosting + $productreceiptArr[$productReceipt->product_receipt_id]['bill_cost'];
					if(!array_key_exists($productReceipt->product_receipt_id,$recipt_cost_arry)){
							$final_bill_cost += $productreceiptArr[$productReceipt->product_receipt_id]['bill_cost'];
							$recipt_cost_arry[$productReceipt->product_receipt_id] = $productreceiptArr[$productReceipt->product_receipt_id]['bill_cost'];
					}
					$billCost = "&#163;".number_format($productreceiptArr[$productReceipt->product_receipt_id]['bill_cost'],2);
					
				}else{
					$billCost = "--";
				}
				
				
	?>
	<tr>
		<td><?php echo date('d-m-Y',strtotime($createdArr[$productReceipt->product_receipt_id]));//$this->Time->format('d-m-Y',$createdArr[$productReceipt->product_receipt_id],null,null);?></td>
		<td><?php echo date('d-m-Y',strtotime($productReceipt->created));//$this->Time->format('d-m-Y',$productReceipt->created,null,null); ?>&nbsp;</td>
		<td><?php echo h($productReceipt->product_receipt_id); ?>&nbsp;</td>
		<td><?php echo $productreceiptArr[$productReceipt->product_receipt_id]['fname']; ?>&nbsp;</td>
		<?php if(array_key_exists($productreceiptArr[$productReceipt->product_receipt_id]['customer_id'],$customerBusiness)){ ?>
		<td><?php echo $customerBusiness[$productreceiptArr[$productReceipt->product_receipt_id]['customer_id']]; ?>&nbsp;</td>
		<?php }else{
			echo "<td>"."--"."</td>";
		}
			?>
		
		<?php
		if($productReceipt->agent_id != 0){
			$agent_name = $agents[$productReceipt->agent_id];
		}else{
			$agent_name = "--";
		}
		 if($agent_name != "--"){
		 ?>
		
		<td title="<?php echo $agent_name;?>"><?php echo h($productreceiptArr[$productReceipt->product_receipt_id]['customer_id'])." [$agent_name]"; ?>&nbsp;</td>
		<?php
		}else{
		?>
		<td title="<?php echo $agent_name;?>"><?php echo h($productreceiptArr[$productReceipt->product_receipt_id]['customer_id']); ?>&nbsp;</td>
		<?php
		}
		?>
		<?php //saleman add by rajju
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS ||
		   //$this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
		   //$this->request->session()->read('Auth.User.group_id') == SALESMAN ||
		   $this->request->session()->read('Auth.User.group_id') == inventory_manager ){?>
		<td><span style='float: right;'><?=$billCost; ?></span></td>
		<?php } ?>
		
		<td><span style='float: right;'>&#163;<?php echo number_format($productReceipt->amount,2);?></span></td>
		<?php $description = $productReceipt->description;
		if(empty($description)){
			$description = "--";
		}
		?>
		<td title="<?php echo $description;?>"><span style='float: right;'><?php echo $productReceipt->payment_method?>&nbsp;</span></td>
		<td><span style='float: right;'><?php echo "&#163;".number_format($productreceiptArr[$productReceipt->product_receipt_id]['bill_amount'],2); ?>&nbsp;</span></td>
		<td class="actions">
			
			<?php if(array_key_exists('0',$this->request->params['pass'])){
						$kid = $this->request->params['pass'][0];
						$viewImgHTML = $this->Html->image('view20X20.png', array('fullBase' => true, 'alt' => 'View Invoice', 'title' => 'View Invoice', 'border' => '0'));
						echo $this->Html->link($viewImgHTML, array('action' => 'dr_generate_receipt', $productreceiptArr[$productReceipt->product_receipt_id]['id'],$kid), array('escapeTitle' => false, 'title' => 'View Invoice', 'alt' => 'View Invoice'));
						//echo $this->Html->link(__('View'), array('action' => 'dr_generate_receipt', $productReceipt['ProductReceipt']['id'],$kid));
				}elseif(array_key_exists('kiosk_id',$this->request->query)){
					$kid = $this->request->query['kiosk_id'];
					$viewImgHTML = $this->Html->image('view20X20.png', array('fullBase' => true, 'alt' => 'View Invoice', 'title' => 'View Invoice', 'border' => '0'));
					echo $this->Html->link($viewImgHTML, array('action' => 'dr_generate_receipt', $productreceiptArr[$productReceipt->product_receipt_id]['id'],$kid), array('escapeTitle' => false, 'title' => 'View Invoice', 'alt' => 'View Invoice'));
					//echo $this->Html->link(__('View'), array('action' => 'dr_generate_receipt', $productReceipt['ProductReceipt']['id'],$kid));
				}else{
					$viewImgHTML = $this->Html->image('view20X20.png', array('fullBase' => true, 'alt' => 'View Invoice', 'title' => 'View Invoice', 'border' => '0'));
					echo $this->Html->link($viewImgHTML, array('action' => 'dr_generate_receipt', $productreceiptArr[$productReceipt->product_receipt_id]['id']), array('escapeTitle' => false, 'title' => 'View Invoice', 'alt' => 'View Invoice'));
					//echo $this->Html->link(__('View'), array('action' => 'dr_generate_receipt', $productReceipt['ProductReceipt']['id']));
				}
				?>
			
			<?php
			$editImgHTML = $this->Html->image('16_edit_page.png', array('fullBase' => true, 'alt' => 'Edit', 'title' => 'Edit', 'border' => '0'));
			if(array_key_exists('0',$this->request->params['pass'])){
				    $kid = $this->request->params['pass'][0];
					echo $this->Html->link($editImgHTML, array('action' => 'dr_edit', $productreceiptArr[$productReceipt->product_receipt_id]['id'],$kid) , array('escapeTitle' => false, 'title' => 'Edit', 'alt' => 'Edit'));
				 }elseif(array_key_exists('kiosk_id',$this->request->query)){
					$kid = $this->request->query['kiosk_id'];
					echo $this->Html->link($editImgHTML, array('action' => 'dr_edit', $productreceiptArr[$productReceipt->product_receipt_id]['id'],$kid) , array('escapeTitle' => false, 'title' => 'Edit', 'alt' => 'Edit'));
				 }else{
						echo $this->Html->link($editImgHTML, array('action' => 'dr_edit', $productreceiptArr[$productReceipt->product_receipt_id]['id']), array('escapeTitle' => false, 'title' => 'Edit', 'alt' => 'Edit'));
				 }
			?>
			<?php
			$pmtImgHTML = $this->Html->image('edit_amount20X20.png', array('fullBase' => true, 'alt' => 'Update Payment', 'title' => 'Update Payment', 'border' => '0'));
			if(array_key_exists('0',$this->request->params['pass'])){
						$kid = $this->request->params['pass'][0];
						echo $this->Html->link($pmtImgHTML, array('action' => 'dr_update_payment', $productReceipt->id, $kid), array('escapeTitle' => false, 'title' => 'Update Payment', 'alt' => 'Update Payment'));
					}elseif(array_key_exists('kiosk_id',$this->request->query)){
						$kid = $this->request->query['kiosk_id'];
						echo $this->Html->link($pmtImgHTML, array('action' => 'dr_update_payment', $productReceipt->id, $kid), array('escapeTitle' => false, 'title' => 'Update Payment', 'alt' => 'Update Payment'));
					}else{
						echo $this->Html->link($pmtImgHTML, array('action' => 'dr_update_payment', $productReceipt->id), array('escapeTitle' => false, 'title' => 'Update Payment', 'alt' => 'Update Payment'));
					}
			?>
			
			
			
			<?php //echo $this->Html->link(__('Update Payment'), array('action' => 'update_payment', $productReceipt['PaymentDetail']['id'])); ?>
			<?php if(array_key_exists('0',$this->request->params['pass'])){
				$kid = $this->request->params['pass'][0];
				//echo $this->Html->link(__('Delivery Note'), array('action' => 'dr_delivery_note', $productReceipt['ProductReceipt']['id'],$kid));
			}elseif(array_key_exists('kiosk_id',$this->request->query)){
				$kid = $this->request->query['kiosk_id'];
				//echo $this->Html->link(__('Delivery Note'), array('action' => 'dr_delivery_note', $productReceipt['ProductReceipt']['id'],$kid));
			}else{
				//echo $this->Html->link(__('Delivery Note'), array('action' => 'dr_delivery_note', $productReceipt['ProductReceipt']['id']));
			}
				?>
			<?php #echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $productReceipt['ProductReceipt']['id']), array(), __('Are you sure you want to delete # %s?', $productReceipt['ProductReceipt']['id']));
			$editUrl = "/img/16_edit_page.png";
			$invChngImgHTML = $this->Html->image('invoice_edit20X20.png', array('fullBase' => true, 'alt' => 'Change Invoice', 'title' => 'Change Invoice', 'border' => '0'));
			echo $this->Html->link($invChngImgHTML, array('action' => 'special_to_orig', $productreceiptArr[$productReceipt->product_receipt_id]['id']), array('escapeTitle' => false, 'title' => 'Change Invoice', 'alt' => 'Change Invoice','confirm' => 'Are you sure you want to convert Quotation to Normal Invoice ?'));
			
			$custChngImgHTML = $this->Html->image('change_customer20X20.png', array('fullBase' => true, 'alt' => 'Change Customer', 'title' => 'Change Customer', 'border' => '0'));
			echo $this->Html->link( $custChngImgHTML, array('action' => 'dr_change_customer', $productreceiptArr[$productReceipt->product_receipt_id]['id']), array('escapeTitle' => false, 'title' => 'Change customer', 'alt' => 'Change customer'));  
			?>
		</td>
	</tr>
<?php 			}
	endforeach; ?>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td></td>
		<td></td>
		<?php //if($s_kskId == 0){?>
		<td style="text-align: center;"></td>
		<?php //} ?>
		<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
				 $this->request->session()->read('Auth.User.group_id') == SALESMAN ||
				 $this->request->session()->read('Auth.User.group_id') == inventory_manager){?>
		<td><?php echo "<b>TOTAL COST = </b>".$final_bill_cost;?></td>
		<?php } ?>
		
		<td style="border-top: 1px solid;border-bottom: 1px solid;padding-left: 31px;"><b>Total = </b>&#163;<?=number_format($totalPaymentAmount,2);?></td>
		
	</tr>
	</tbody>
	</table>
	<p>
	<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php #echo $this->Html->link(__('New Product Receipt'), array('action' => 'add')); ?></li>
		<li><?php //echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index')); ?> </li>
        <?php
        $loggedInUser = $this->request->session()->read('Auth.User.username');
        if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering
			?>
        <li><?=$this->element('tempered_side_menu')?></li>
		<li><?php //echo $this->Html->link(__('ManXX Quotation'), array('controller' => 'product_receipts', 'action' => 'dr_index',1)); ?> </li>
        <?php }?>
		<li><?php echo $this->Html->link(__('View Invoices'), array('controller' => 'product_receipts', 'action' => 'all_invoices')); ?> </li>
		<li><?php echo $this->Html->link(__('View Sales'), array('controller' => 'kiosk_product_sales', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('View Inv Pmt<br/>Logs'), array('controller' => 'product_receipts', 'action' => 'invoicePaymentClearness'),array('escape'=>false)); ?> </li>
		<li><?php echo $this->Html->link(__('View Quot Pmt<br/>Logs'), array('controller' => 'product_receipts', 'action' => 'quotationPaymentClearness'),array('escape'=>false)); ?> </li>
		
	</ul>
</div>
<script>
	function reset_search(){
		jQuery( "#datepicker1" ).val("");
		jQuery( "#datepicker2" ).val("");
		jQuery("#search_kw").val("");
	}
	jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy" });
	});
	
	$('#submit1').click(function(){
		var cnt = $("input[class = 'radio1']:checked").length;
			if(cnt<1)
			{
			    alert("Please check at least one Receipt number/Business/Customer number");
			    return false;
			}
	});
	$('#search_kiosks').change(function(){
		$.blockUI({ message: 'Loading ...' });
		document.getElementById("search_form").submit();
	});
	$('#agent_id').change(function(){
		$.blockUI({ message: 'Loading ...' });
		document.getElementById("search_form").submit();
	});
</script>
<script>
	function add_drBulkUrl() {
        var start_date = $("#datepicker1").val();
		var end_date = $("#datepicker2").val();
		var payment_date_type = $('[name="date_type"]:checked').val();
		var kiosk = $("#search_kiosks").val();
		
		
		if(typeof kiosk === "undefined"){
			kiosk = <?php if(!empty($this->request->Session()->read('kiosk_id'))){
				echo $this->request->Session()->read('kiosk_id');
				}else{
					echo "''";
					}; ?>;
		}
		
		var customer_number = $("#cust_hidden_id").val();
		var acc_manger = $("#agent_id").val();
		var action ="product-receipts/search";
		var base_url = window.location.origin;
		var action  = base_url + "/product-receipts/dr_processBulkInvoices?start_date="+start_date+"&end_date="+end_date+"&payment_date_type="+payment_date_type+"&kiosk="+kiosk+"&customer_number="+customer_number+"&acc_manger="+acc_manger;
		
		window.open(action, '_blank')
		
    }
	
	function add_drAccountUrl() {
        var start_date = $("#datepicker1").val();
		var end_date = $("#datepicker2").val();
		var payment_date_type = $('[name="date_type"]:checked').val();
		var kiosk = $("#search_kiosks").val();
		
		
		if(typeof kiosk === "undefined"){
			kiosk = <?php if(!empty($this->request->Session()->read('kiosk_id'))){
				echo $this->request->Session()->read('kiosk_id');
				}else{
					echo "''";
					}; ?>;
		}
		
		var customer_number = $("#cust_hidden_id").val();
		var acc_manger = $("#agent_id").val();
		var action ="product-receipts/search";
		var base_url = window.location.origin;
		var action  = base_url + "/product-receipts/dr_customer_account_statement?start_date="+start_date+"&end_date="+end_date+"&payment_date_type="+payment_date_type+"&kiosk="+kiosk+"&customer_number="+customer_number+"&acc_manger="+acc_manger;
		
		window.open(action, '_blank')
		
    }
</script>
 <script>
 var user_dataset = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  //prefetch: "/products/data",
  remote: {
    url: "/retail-customers/custemail_w?search=%QUERY",
    wildcard: "%QUERY"
  }
});

$('#remote .typeahead').typeahead(null, {
  name: 'email',
  display: 'email',
  source: user_dataset,
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
    suggestion: Handlebars.compile('<div id="cust_id" style="background-color:lightgrey;width:550px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{fname}}</a>  <a class="row_hover" href="#-1">{{lname}}</a>  <a class="row_hover" href="#-1">{{business}}</a>  <a id="cust" rel={{id}} class="row_hover" href="#-1">{{email}}</a></strong></div>'),
	header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------hpwaheguru.co.uk---------</b></div>"),
  }
}).bind("typeahead:selected", function(obj, datum, name) {
	
	$("#cust_radio").prop('checked', true);
$("#search_kw").val(datum.id);
});

</script>