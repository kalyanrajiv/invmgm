<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\I18n\Time;
?>
<?php


	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>

<div class="productReceipts index">
	<?php
	if(!isset($date_type)){$date_type = "invoice";}
	if(!isset($start_date)){$start_date = "";}
	if(!isset($end_date)){$end_date = "";}
	if(!isset($searchKeyword)){$searchKeyword = "All";}//textKeyword
	if(!isset($textKeyword)){$textKeyword = "";}
	if(!isset($invoiceSearchKeyword)){$invoiceSearchKeyword = "";}
	if(!isset($agent_id)){$agent_id = 0;}
	if(isset($this->request->query['payment_type'])){
		$paymenttype = $this->request->query['payment_type'];
	}else{
		$paymenttype = "All";
	}
	if(isset($this->request->query['payment_type'])){
		$invoice_detail = $this->request->query['invoice_detail'];
	}else{
		$invoice_detail = "receipt_number";
	}
	if(!isset($t_searched)){
		$t_searched = 0;
	}
	
	?>
	<?php if($t_searched == 1){
			$webroot = $this->request->webroot."CreditProductDetails/t_search";
		}else{
			$webroot = $this->request->webroot."CreditProductDetails/search";
		}?>
	<form id = "search_form" name="search_form" action="<?php echo $webroot;?>" method = 'get'>
	<fieldset>
		<legend>Payment Type</legend>
		<table  style="
    margin-bottom: -21px;
    margin-top: -18px;
    margin-left: -2px;">
			<tr>
				<td><input type="radio" name="date_type" value="invoice" <?php if($date_type == "invoice"){echo "checked";}?>>invoice date</td>	
				<td><input type="radio" name="date_type" value="payment" <?php if($date_type == "payment"){echo "checked";}?>>payment date</td>	
			</tr>
			<?php //echo $kiosk_id;die; ?>
				<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER  || $this->request->session()->read('Auth.User.group_id') == SALESMAN || $this->request->session()->read('Auth.User.group_id') == inventory_manager){
					if($kiosk_id == ""){
						$kiosk_id = 10000;
					}
								//echo "hi";die;
								?>
                    <tr><td><strong>Kiosks</strong></td></tr>
                    <tr>
						<td>
							 <select id="search_kiosks" name = "kiosk_id" ">
								<?php  foreach($kiosk_list as $skey => $svalue){ //onchange="this.form.submit()?>
									<option  value="<?php echo $skey;?>"<?php if($skey == $kiosk_id){echo "selected=selected";}?>><?php echo $svalue;?></option>
									<?php } ?>
							</select> 
							<table><tr>
								<td>
							<select id="agent_id" name="agent_id">
								<?php foreach($agents as $ag_key => $ag_value){//onchange="this.form.submit()"?>
								
									<option  value="<?php echo $ag_key;?>"<?php if($ag_key == $agent_id){echo "selected=selected";}?>><?php echo $ag_value;?></option>
									<?php } ?>
							</select>
						</td>
							</tr></table>
						</td>
						<?php } ?>
						
						
				<td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date" style = "width:75px" value='<?php echo $start_date;?>' /></td>
				<td><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date" style = "width:75px" value='<?php echo $end_date;?>' /></td>
				<td>
					<table>
						<tr>
							
							<td>
					<input type="radio" name="payment_type" value="On Credit" <?php if($paymenttype == "On Credit"){echo "checked";}?>>On credit</td>
				<td><input type="radio" name="payment_type" value="Cash" <?php if($paymenttype == "Cash"){echo "checked";}?>>Cash</td>
				<td><input type="radio" name="payment_type" value="Card" <?php if($paymenttype == "Card"){echo "checked";}?>>Card</td>
				</tr>
						<tr>
							<td><input type="radio" name="payment_type" value="Bank Transfer" <?php if($paymenttype == "Bank Transfer"){echo "checked";}?>>Bank Tx</td>
						<td><input type="radio" name="payment_type" value="Cheque" <?php if($paymenttype == "Cheque"){echo "checked";}?>>Cheque</td>
						<td><input type="radio" name="payment_type" value="All" <?php if($paymenttype == "All"){echo "checked";}?>>All</td>
						</tr>
			</table>
				</td>
			
				<td>
					<table>
						<tr>
							<td><input type="radio" name="invoice_detail" class = "radio1" value="receipt_number" <?php if($invoice_detail == "receipt_number"){echo "checked";}?>>Receipt number</td>
						<td><input type="radio" name="invoice_detail" class = "radio1" value="business" <?php if($invoice_detail == "business"){echo "checked";}?>>Business</td>
						<td><input type="radio" name="invoice_detail" class = "radio1" value="customer_id" <?php if($invoice_detail == "customer_id"){echo "checked";}?>>Customer number</td>
						</tr>
				<td style="width: 100px;"><input type="text" name="search_kw" id = "search_kw" style="width: 190px; height: 25px" autofocus placeholder = "Receipt number/Business/Customer number" value="<?php echo $textKeyword;?>"></td>
			
				<td><input type='submit' name='submit1' id = 'Search'  value='Search'></td>
				<td> <input type='button' name='reset' id = 'reset' value='Reset Search' style = "width:155px" onClick='reset_search();'/>
				</td>
					</table>
			</tr>
		</table>
	</fieldset>
	</form>
	 <?php if($t_searched == 1){
		$text = "Quotation";
	}else{
		$text ='Notes';
		}?>
	<h2><?php // pr($kiosk_list);
    //echo $kiosk_id;
    if(empty($kiosk_id)){
       $kiosk_id = 10000;
       
    }
    if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||   $this->request->session()->read('Auth.User.group_id') == SALESMAN || $this->request->session()->read('Auth.User.group_id') == inventory_manager){ 
		$kiosk_name =  $kiosk_list[$kiosk_id];echo __("$kiosk_name Credit $text");
	  }else{
		echo __("Credit $text");
	  }
	//$kiosk_name =  $kiosk_list[$kiosk_id]; echo __("$kiosk_name Credit $text"); ?></h2>
	<b>Total Amount : </b><?php echo $amt_to_show;//$totalAmt;//$amt_to_show;?>
	<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == SALESMAN || $this->request->session()->read('Auth.User.group_id') == inventory_manager){ ?>
	<b>Total Cost = </b><?php echo $cost_sum;?>
	<?php } ?>
	<?php //<b>vat applied amount(with vat) : <?php //echo $withVat_amt;</b> ?>
	<b>Total Vat:</b><?php echo round($lptotalVat,2);?>
	<b>Total Amt (without vat) : </b><?php $amt =$amt_to_show - $lptotalVat;
	echo round($amt,2);
	?>
	
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th>Invoice Date</th>
			<th><?php echo $this->Paginator->sort('created','Payment Date'); ?></th>
			
			<th><?php echo $this->Paginator->sort('credit_receipt_id'); ?></th>
			<th>Customer</th>
			<th>Business</th>
			<th>#Cust</th>
			<th>Cost</th>
			<th><?php echo $this->Paginator->sort('payment_method'); ?></th>
			<th>Total</th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
		<?php //pr($creditPaymentDetails);die;
        //pr($customerBusiness);die;
		//pr($creditPaymentDetails);die;
        ?>
	<?php foreach ($creditPaymentDetails as $creditPaymentDetail): //pr($creditReceiptData);die;
        $receipt_id = $creditPaymentDetail->credit_receipt_id;
		//echo $receipt_id;die;
        //echo $creditReceiptData[$receipt_id]['fname'];die;
		//pr($creditPaymentDetail);die;
    ?>
	<?php
	$invoice_date = $creditReceiptData[$creditPaymentDetail->credit_receipt_id]['created'];
	if(!empty($invoice_date)){
		 $invoice_date->i18nFormat(
							[\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
					);
		 $date =  $invoice_date->i18nFormat('dd-MM-yyyy HH:mm:ss');
		$date_to_show = date('d-m-Y',strtotime($date));
	}else{
		$date_to_show = "--";
	}
	?>
	<tr>
		<td><?php echo $date_to_show; ?></td>
		<td><?php
		if(!empty($creditPaymentDetail->created)){
			echo date('d-m-Y',strtotime($creditPaymentDetail->created));
		}else{
			echo'--';
		}
		//$this->Time->format('d-m-Y',$creditPaymentDetail->created,null,null); ?>&nbsp;</td>
		
		<td><?php echo h($creditPaymentDetail->credit_receipt_id); ?>&nbsp;</td>
		<td><?php //pr($creditReceiptData);die;
		//if(array_key_exists($receipt_id,$creditReceiptData)){
			echo $creditReceiptData[$creditPaymentDetail->credit_receipt_id]['fname'];
		//}
		?>&nbsp;</td>
		<td><?php
		//if(array_key_exists($receipt_id,$creditReceiptData)){
			if(array_key_exists($creditReceiptData[$creditPaymentDetail->credit_receipt_id]['customer_id'],$customerBusiness)){
				echo $customerBusiness[$creditReceiptData[$creditPaymentDetail->credit_receipt_id]['customer_id']];
			}
		//}
		?>&nbsp;</td>
		<td><?php
		//if(array_key_exists($receipt_id,$creditReceiptData)){
			if(is_array($creditReceiptData[$creditPaymentDetail->credit_receipt_id])){
				echo h($creditReceiptData[$creditPaymentDetail->credit_receipt_id]['customer_id'])."[".$customerAgent[$creditReceiptData[$creditPaymentDetail->credit_receipt_id]['customer_id']]."]";
			}
		//}
			?>&nbsp;</td>
		<td><?php echo $creditReceiptData[$creditPaymentDetail->credit_receipt_id]['bill_cost'];?></td>
		<?php $desc = $creditPaymentDetail->description;
		if(empty($desc)){
			$desc = "--";
		}
		?>
		<td title="<?php echo $desc; ?>"><?php echo $creditPaymentDetail->payment_method." ( ".$CURRENCY_TYPE.$creditPaymentDetail->amount;?>)&nbsp;</td>
		<td><?php
		//if(array_key_exists($receipt_id,$creditReceiptData)){
			echo $CURRENCY_TYPE.$creditReceiptData[$creditPaymentDetail->credit_receipt_id]['credit_amount'];
		//}
		?>&nbsp;</td>
		<td class="actions">
			<?php
			$viewImgHTML = $this->Html->image('view20X20.png', array('fullBase' => true, 'alt' => 'View Credit', 'title' => 'View Credit', 'border' => '0'));
			$editImgHTML = $this->Html->image('16_edit_page.png', array('fullBase' => true, 'alt' => 'Edit', 'title' => 'Edit', 'border' => '0'));
			$pmtImgHTML = $this->Html->image('edit_amount20X20.png', array('fullBase' => true, 'alt' => 'Update Payment', 'title' => 'Update Payment', 'border' => '0'));
			$invChngImgHTML = $this->Html->image('invoice_edit20X20.png', array('fullBase' => true, 'alt' => 'Change Credit', 'title' => 'Change Credit', 'border' => '0'));
			$custChngImgHTML = $this->Html->image('change_customer20X20.png', array('fullBase' => true, 'alt' => 'Change Customer', 'title' => 'Change Customer', 'border' => '0'));
			//pr($creditPaymentDetail);die;
			if($t_searched == 1){
			 
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == SALESMAN || $this->request->session()->read('Auth.User.group_id') == inventory_manager){
					if(!empty($creditPaymentDetail->credit_receipt_id)){
						echo $this->Html->link($viewImgHTML, array('action' => 't_view', $creditPaymentDetail->credit_receipt_id), array('escapeTitle' => false, 'title' => 'View Credit', 'alt' => 'View Credit'));
					}else{
						echo $this->Html->link($viewImgHTML, '#-1', array('escapeTitle' => false, 'title' => 'View Credit', 'alt' => 'View Credit'));
					}
					// echo $this->Html->link(__('View'), array('action' => 't_view', $creditPaymentDetail['CreditReceipt']['id']));
					 echo $this->Html->link($pmtImgHTML, array('action' => 't_update_credit_payment', $creditPaymentDetail->id), array('escapeTitle' => false, 'title' => 'Update Payment', 'alt' => 'Update Payment'));
					//  echo $this->Html->link(__('Update Payment'), array('action' => 't_update_credit_payment', $creditPaymentDetail['CreditPaymentDetail']['id']));
					
					if(!empty($creditPaymentDetail->credit_receipt_id)){
						echo $this->Html->link($custChngImgHTML, array('action' => 'dr_change_customer', $creditPaymentDetail->credit_receipt_id), array('escapeTitle' => false, 'title' => 'Change customer', 'alt' => 'Change customer','confirm' => 'Are you sure you want to change customer?'));
						echo $this->Html->link($invChngImgHTML, array('action' => 'special_to_orig', $creditPaymentDetail->credit_receipt_id,$kiosk_id), array('escapeTitle' => false, 'title' => 'Change Credit', 'alt' => 'Change Credit','confirm' => 'Are you sure you want to change Credit Quotation to Credit note?'));
					}else{
						echo $this->Html->link($custChngImgHTML, '#-1', array('escapeTitle' => false, 'title' => 'Change customer', 'alt' => 'Change customer'));
						echo $this->Html->link($invChngImgHTML, '#-1', array('escapeTitle' => false, 'title' => 'Change Credit', 'alt' => 'Change Credit'));
					}
					
					
					  //echo $this->Html->link( $custChngImgHTML, array('action' => 'dr_change_customer', $creditPaymentDetail['CreditReceipt']['id']), array('escapeTitle' => false, 'title' => 'Change customer', 'alt' => 'Change customer')); 
					  //echo $this->Html->link(__('Change Customer'), array('action' => 'dr_change_customer', $creditPaymentDetail['CreditReceipt']['id']));
					  //echo $this->Html->link(__('Change Invoice'), array('action' => 'special_to_orig', $creditPaymentDetail['CreditReceipt']['id']));
					  
					//  echo $this->Html->link(__('Chng 2 Cred Inv'), array('action' => 'special_to_orig', $creditPaymentDetail['CreditReceipt']['id'],$kiosk_id),array('title' => 'Change to Credit Invoice','confirm' => 'Are you sure you want to change Credit Quotation to Credit Invoice?'));
					  	//echo $this->Html->link('Change Invoice', array('action' => 'org_to_special', $creditPaymentDetail['CreditPaymentDetail']['id']));
					  
				}else{
                     if(!empty($creditPaymentDetail->credit_receipt_id)){
						echo $this->Html->link($viewImgHTML, array('action' => 't_view', $creditPaymentDetail->credit_receipt_id), array('escapeTitle' => false, 'title' => 'View Credit', 'alt' => 'View Credit'));
					 }else{
						echo $this->Html->link($viewImgHTML, '#-1', array('escapeTitle' => false, 'title' => 'View Credit', 'alt' => 'View Credit'));
					 }
					 //echo $this->Html->link(__('View'), array('action' => 't_view', $creditPaymentDetail['CreditReceipt']['id']));
					 echo $this->Html->link($pmtImgHTML, array('action' => 't_update_credit_payment', $creditPaymentDetail->id), array('escapeTitle' => false, 'title' => 'Update Payment', 'alt' => 'Update Payment'));
					 // echo $this->Html->link(__('Update Payment'), array('action' => 't_update_credit_payment', $creditPaymentDetail['CreditPaymentDetail']['id']));
					 if(!empty($creditPaymentDetail->credit_receipt_id)){
						echo $this->Html->link($invChngImgHTML, array('action' => 'special_to_orig', $creditPaymentDetail->credit_receipt_id), array('escapeTitle' => false, 'title' => 'Change Credit', 'alt' => 'Change Credit','confirm' => 'Are you sure you want to change Credit Quotation to Credit note?'));
						echo $this->Html->link($custChngImgHTML, array('action' => 'dr_change_customer', $creditPaymentDetail->credit_receipt_id), array('escapeTitle' => false, 'title' => 'Change customer', 'alt' => 'Change customer','confirm' => 'Are you sure you want to change customer?'));
					 }else{
						echo $this->Html->link($custChngImgHTML, '#-1', array('escapeTitle' => false, 'title' => 'Change customer', 'alt' => 'Change customer'));
						echo $this->Html->link($invChngImgHTML, '#-1', array('escapeTitle' => false, 'title' => 'Change Credit', 'alt' => 'Change Credit'));
					 }
					 
					 // echo $this->Html->link(__('Chng 2 Cred Inv'), array('action' => 'special_to_orig', $creditPaymentDetail['CreditReceipt']['id']),array('title' => 'Change to Credit Invoice','confirm' => 'Are you sure you want to change Credit Quotation to Credit Invoice?'));
					  //echo $this->Html->link(__('Change Customer'), array('action' => 'change_customer', $creditPaymentDetail['CreditPaymentDetail']['id']));
					 
				}
				?>
				     
			<?php }else{
				$loggedInUser = $this->request->session()->read('Auth.User.username');
					if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == SALESMAN || $this->request->session()->read('Auth.User.group_id') == inventory_manager){
						$loggedInUser = $this->request->session()->read('Auth.User.username');
						if($kiosk_id == 10000){
                             if(!empty( $creditReceiptData[$receipt_id]['id'] )){
								echo $this->Html->link($viewImgHTML, array('action' => 'view', $creditReceiptData[$receipt_id]['id']), array('escapeTitle' => false, 'title' => 'View Credit', 'alt' => 'View Credit'));
							 }else{
								echo $this->Html->link($viewImgHTML, '#-1', array('escapeTitle' => false, 'title' => 'View Credit', 'alt' => 'View Credit'));
							 }
							//echo $this->Html->link(__('View'), array('action' => 'view', $creditPaymentDetail['CreditReceipt']['id'])); 
							echo $this->Html->link($pmtImgHTML, array('action' => 'update_credit_payment', $creditPaymentDetail->id), array('escapeTitle' => false, 'title' => 'Update Payment', 'alt' => 'Update Payment'));
							//echo $this->Html->link(__('Update Payment'), array('action' => 'update_credit_payment', $creditPaymentDetail['CreditPaymentDetail']['id']));
							
							if(!empty( $creditReceiptData[$receipt_id]['id'] )){
								echo $this->Html->link($custChngImgHTML, array('action' => 'change_customer', $creditReceiptData[$receipt_id]['id']), array('escapeTitle' => false, 'title' => 'Change customer', 'alt' => 'Change customer','confirm' => 'Are you sure you want to change customer?'));
								if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
									echo $this->Html->link($invChngImgHTML, array('action' => 'org_to_special', $creditReceiptData[$receipt_id]['id']), array('escapeTitle' => false, 'title' => 'Change Credit', 'alt' => 'Change Credit','confirm' => 'Are you sure you want to change Credit note to Credit Quotation?'));
								}
							}else{
								echo $this->Html->link($custChngImgHTML, '#-1', array('escapeTitle' => false, 'title' => 'Change customer', 'alt' => 'Change customer'));
								if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
									echo $this->Html->link($invChngImgHTML, '#-1', array('escapeTitle' => false, 'title' => 'Change Credit', 'alt' => 'Change Credit'));
								}
							}
							
							//echo $this->Html->link( $custChngImgHTML, array('action' => 'change_customer', $creditPaymentDetail['CreditReceipt']['id']), array('escapeTitle' => false, 'title' => 'Change customer', 'alt' => 'Change customer')); 
							//echo $this->Html->link(__('Change Customer'), array('action' => 'change_customer', $creditPaymentDetail['CreditReceipt']['id']));
							 //echo $this->Html->link(__('Change Invoice'), array('action' => 'org_to_special', $creditPaymentDetail['CreditReceipt']['id']));
							 
							
							// echo $this->Html->link(__('Chng 2 Cred Quot'), array('action' => 'org_to_special', $creditPaymentDetail['CreditReceipt']['id']),array('title' => 'Change to Credit Quotation','confirm' => 'Are you sure you want to change Credit Invoice to Credit Quotation?'));
							?>
						<?php }else{
                             if(!empty( $creditReceiptData[$receipt_id]['id'] )){
							   echo $this->Html->link($viewImgHTML, array('action' => 'view', $creditReceiptData[$receipt_id]['id'],$kiosk_id), array('escapeTitle' => false, 'title' => 'View Credit', 'alt' => 'View Credit'));
							 }else{
								echo $this->Html->link($viewImgHTML, '#-1', array('escapeTitle' => false, 'title' => 'View Credit', 'alt' => 'View Credit'));
							 }
							//  echo $this->Html->link(__('View'), array('action' => 'view', $creditPaymentDetail['CreditReceipt']['id'],$kiosk_id));
							echo $this->Html->link($pmtImgHTML, array('action' => 'update_credit_payment', $creditPaymentDetail->id,$kiosk_id), array('escapeTitle' => false, 'title' => 'Update Payment', 'alt' => 'Update Payment'));
						  // echo $this->Html->link(__('Update Payment'), array('action' => 'update_credit_payment', $creditPaymentDetail['CreditPaymentDetail']['id'],$kiosk_id));
						   //echo $this->Html->link(__('Change Customer'), array('action' => 'change_customer', $creditPaymentDetail['CreditReceipt']['id'],$kiosk_id));
							if(!empty( $creditReceiptData[$receipt_id]['id'] )){
								if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
									echo $this->Html->link($invChngImgHTML, array('action' => 'org_to_special', $creditReceiptData[$receipt_id]['id'],$kiosk_id), array('escapeTitle' => false, 'title' => 'Change Credit', 'alt' => 'Change Credit','confirm' => 'Are you sure you want to change Credit note to Credit Quotation?'));
								}
								echo $this->Html->link($custChngImgHTML, array('action' => 'change_customer', $creditReceiptData[$receipt_id]['id'],$kiosk_id), array('escapeTitle' => false, 'title' => 'Change customer', 'alt' => 'Change customer','confirm' => 'Are you sure you want to change customer?'));
							}else{
								if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
									echo $this->Html->link($invChngImgHTML, '#-1', array('escapeTitle' => false, 'title' => 'Change Credit', 'alt' => 'Change Credit'));
								}
								echo $this->Html->link($custChngImgHTML, '#-1', array('escapeTitle' => false, 'title' => 'Change customer', 'alt' => 'Change customer'));
							}
						    //echo $this->Html->link(__('Change Customer'), array('action' => 'change_customer', $creditPaymentDetail['CreditReceipt']['id'],$kiosk_id));
						   //echo $this->Html->link(__('Chng 2 Cred Quot'), array('action' => 'org_to_special', $creditPaymentDetail['CreditReceipt']['id'],$kiosk_id),array('title' => 'Change to Credit Quotation','confirm' => 'Are you sure you want to change Credit Invoice to Credit Quotation?'));
						}
					}else{
                          if(!empty( $creditReceiptData[$receipt_id]['id'] )){
								echo $this->Html->link($viewImgHTML, array('action' => 'view', $creditReceiptData[$receipt_id]['id']), array('escapeTitle' => false, 'title' => 'View Credit', 'alt' => 'View Credit'));
						  }else{
							echo $this->Html->link($viewImgHTML, '#-1', array('escapeTitle' => false, 'title' => 'View Credit', 'alt' => 'View Credit'));
						  }
						//echo $this->Html->link(__('View'), array('action' => 'view', $creditPaymentDetail['CreditReceipt']['id']));
						echo $this->Html->link($pmtImgHTML, array('action' => 'update_credit_payment', $creditPaymentDetail->id), array('escapeTitle' => false, 'title' => 'Update Payment', 'alt' => 'Update Payment'));
					//	echo $this->Html->link(__('Update Payment'), array('action' => 'update_credit_payment', $creditPaymentDetail['CreditPaymentDetail']['id']));
						if(!empty( $creditReceiptData[$receipt_id]['id'] )){
							if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
								echo $this->Html->link($invChngImgHTML, array('action' => 'org_to_special', $creditReceiptData[$receipt_id]['id']), array('escapeTitle' => false, 'title' => 'Change Credit', 'alt' => 'Change Credit','confirm' => 'Are you sure you want to change Credit note to Credit Quotation?'));
							}
							echo $this->Html->link($custChngImgHTML, array('action' => 'change_customer', $creditReceiptData[$receipt_id]['id']), array('escapeTitle' => false, 'title' => 'Change customer', 'alt' => 'Change customer','confirm' => 'Are you sure you want to change customer?'));
						}else{
							if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
								echo $this->Html->link($invChngImgHTML, '#-1', array('escapeTitle' => false, 'title' => 'Change Credit', 'alt' => 'Change Credit','confirm' => 'Are you sure you want to change Credit note to Credit Quotation?'));
							}
							echo $this->Html->link($custChngImgHTML, '#-1', array('escapeTitle' => false, 'title' => 'Change customer', 'alt' => 'Change customer'));
						}
						//echo $this->Html->link(__('Chng 2 Cred Quot'), array('action' => 'org_to_special', $creditPaymentDetail['CreditReceipt']['id']),array('title' => 'Change to Credit Quotation','confirm' => 'Are you sure you want to change Credit Invoice to Credit Quotation?'));
					}
				
				?>
					
			<?php }?>
			<?php #echo $this->Html->link(__('Edit'), array('action' => 'edit', $creditPaymentDetail['CreditReceipt']['id'])); ?>
			<?php #echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $productReceipt['ProductReceipt']['id']), array(), __('Are you sure you want to delete # %s?', $productReceipt['ProductReceipt']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
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
		<li><?php echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Customer'), array('controller' => 'customers', 'action' => 'add')); ?> </li>
		<?php $loggedInUser = $this->request->session()->read('Auth.User.username');
		if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
		?>
		<li><?php echo $this->Html->link(__('Credit Quotation'), array('controller' => 'credit_product_details', 'action' => 't_view_credit_note')); ?> </li>
		<li><?php echo $this->Html->link(__('View Credit Note'), array('controller' => 'credit_product_details', 'action' => 'view_credit_note')); ?> </li>
		<?php } ?>
		<?php //echo $this->Html->link(__('List Kiosk Product Sales'), array('controller' => 'kiosk_product_sales', 'action' => 'index')); ?> 
		<?php //echo $this->Html->link(__('New Kiosk Product Sale'), array('controller' => 'kiosk_product_sales', 'action' => 'add')); ?> 
	</ul>
</div>
<script>
	 function reset_search(){
		jQuery( "#datepicker1" ).val("");
		jQuery( "#datepicker2" ).val("");
		jQuery("#search_kw").val("");
        jQuery("#payment_type").val("");
        jQuery("#invoice_detail").val("");
	 
       }
       jQuery(function() {
			jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
			jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
		});
		$('#search_kiosks').change(function(){
			$.blockUI({ message: 'Loading ...' });
			document.getElementById("search_form").submit();
		});
</script>
<script>
	$('#agent_id').change(function(){
		$.blockUI({ message: 'Loading ...' });
		document.getElementById("search_form").submit();
	});
</script>