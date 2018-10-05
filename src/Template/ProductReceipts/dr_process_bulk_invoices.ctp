<?php
if(!empty($paymentType)){
	 array_unshift($paymentType,"Choose Payment Method");
    unset($paymentType['On Credit']);
}
$created = "";
//pr($recipt_table_data);
if(!empty($recipt_table_data)){
	ksort($recipt_table_data);
	$res =current($recipt_table_data);
	$created = $res['created'];
}
$process_cart = $this->Url->build(['controller' => 'product-receipts', 'action' => 'dr_process_cart'],true);
$process_credit_cart = $this->Url->build(['controller' => 'product-receipts', 'action' => 'dr_process_credit_cart'],true);
?>
<input type='hidden' name='process_cart' id='process_cart' value='<?=$process_cart?>' />
<input type='hidden' name='process_credit_cart' id='process_credit_cart' value='<?=$process_credit_cart?>' />
<input type='hidden' name='kiosk_id' id='kiosk_id' value='<?=$kiosk_id?>' />
<input type= "hidden" id="alert_box_value" />
</br>
    <table cellpadding="0" cellspacing="0">
	<thead>
	 <tr>
		  <td>
			   <b>Customer : </b></br><span><?php echo $customer_data[0]->fname; ?></span>
		  </td>
		  <td>
			   <b>Business : </b></br><?php echo $customer_data[0]->business; ?>
		  </td>
		  <td>
			   <b>Cust : </b></br><?php echo $customer_data[0]->id; ?>
		  </td>
	 </tr>
		<tr>
			<td colspan="3">
			   <?php
                if(array_key_exists('HTTP_REFERER',$_SERVER)){
                    $referer = $_SERVER['HTTP_REFERER'];
                }else{
					$referer = "http://hpwaheguru.co.uk/product-receipts";
				}
			   ?>
                <?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS ||
						 $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
						 $this->request->session()->read('Auth.User.group_id') == SALESMAN ||
						 $this->request->session()->read('Auth.User.group_id') == inventory_manager){ ?>
				<strong><?php echo __('<span style="color:red; font-size:20px;">'.'Manage Outstanding For '.$kiosks[$kiosk_id].'</span>') ?></strong>
                <?php }else{?>
                <strong><?php echo __('<span style="color:red; font-size:20px;">Manage Outstanding For Quotation</span>')?></strong>
                <?php } ?>
			</td>
			<td>
			
			   
			</td>
            </tr><tr>
            <td style="width: 235px;"><strong>Choose Payment Method</strong></br><?php echo $this->Form->input('change_mode',array('options'=>$paymentType,'label'=>false))?></td>
			<td style="width: 102px;"><strong>Total Due:</strong></br>&nbsp;&#163;<?=number_format($total_amount,2);?></td>
            <td><strong>Enter Amount</strong></br><input type="text" id="raw_amount" name="amount" placeholder="Amount" autocomplete="off" style="width: 101px;" />
			</td><td><strong>Clearing Amt</strong>
			</br>
			<input type="text" readonly="readonly" disabled id="raw_amount_run_time" name="amount" style="width: 99px;" />
			</td>
            <td style="padding-left: 5px;"><input type="checkbox" onclick="show_hide();" name="credit_note_pay" id="credit_note_pay" value="Bike">Pay By Credit Note</td>
			<td colspan="2">
				<input type="checkbox" onclick="show_hide_datepicker();" name="credit_note_pay" id="chk_datepicker" value="Bike">Choose Payment Date
				<div id="datepicker_div">
					<input type="text" id="datepicker" readonly="readonly" />
					<input type="hidden" value="<?php echo $created; ?>" id="min_val_for_date" /> 
				</div>
				</td>
			</td>
            <td id="rest_td"><a href="#" onclick="reset_all();">Reset</a>
			<input type="hidden" id="referer" value="<?php if(isset($referer)){echo $referer;}?>">
			</td>
			<td>
			<a href="#" onclick="reset_all_on_page();">Reset All</a>
			<div id="process_credit_div" style="float: left;padding-right: 45px;">
			   <a href="#" onclick="process_invoice_with_credit();">Process Credit</a>
			</div>
			</td>
			
		</tr>
		<tr>
		<td valign='center'>
		Memo:
		&nbsp;
		<span padding-left="10px">
		<textarea name="memo" id="memo_content" rows='3' cols='50'></textarea>
			</span>
			</td>
		<td></td>
		<td colspan=6>
			<span>
				<b>**Adjustment against credit quotation will keep payment date same as of Credit quotation and method would be Cash always.</b>
			</span>
		</td>
		</tr>
	<tr>
		<th>Invoice Date</th>
			<th><?php echo $this->Paginator->sort('created','Payment Date'); ?></th>
			<th><?php echo $this->Paginator->sort('product_receipt_id',"#Invoice"); ?></th>
			<th>Customer</th>
			<th>Business</th>
			<th>#Cust</th>
            
			<th><span style='float: left;'>Inv Total Amt</span></th>
			<th><span ><?php echo $this->Paginator->sort('payment_method',"Mode"); ?></span></th>
			<th><span >Due Pmt</span></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
		$tolalBillCosting = 0.0;
		
		foreach ($pay_res as $productReceipt):
		
		//pr($recipt_table_data);die;
		if(array_key_exists($productReceipt->product_receipt_id,$recipt_table_data)){
			if($recipt_table_data[$productReceipt->product_receipt_id]['status']==0){
				if(!empty($recipt_table_data[$productReceipt->product_receipt_id]['bill_cost'])){
					$tolalBillCosting = $tolalBillCosting + $recipt_table_data[$productReceipt->product_receipt_id]['bill_cost'];
					$billCost = $recipt_table_data[$productReceipt->product_receipt_id]['bill_cost'];
					
				}else{
					$billCost = "--";
				}
	?>
	<tr>
		<td><?php
		//pr($createdArr);die;
				echo date("d-m-Y",strtotime($recipt_table_data[$productReceipt->product_receipt_id]['created']));
		//echo $this->Time->format('d-m-Y',$createdArr[$productReceipt['product_receipt_id']],null,null);?></td>
		<td><?php
				echo date("d-m-Y",strtotime($productReceipt['created']));
		//echo $this->Time->format('d-m-Y',$productReceipt['created'],null,null); ?>&nbsp;</td>
		<td><?php echo h($productReceipt['product_receipt_id']); ?>&nbsp;</td>
		<td><?php echo $recipt_table_data[$productReceipt->product_receipt_id]['fname']; ?>&nbsp;</td>
		<td>
            <?php echo $customer_data[0]->business;?>
        </td>
			
		<?php
		if($productReceipt->agent_id != 0){
			$agent_name = $agents[$productReceipt->agent_id];
		}else{
			$agent_name = "--";
		}
		 ?>
		<td title="<?php echo $agent_name;?>"><?php echo h($recipt_table_data[$productReceipt->product_receipt_id]['customer_id']); ?>&nbsp;</td>
       
	   <td><span id="span_<?php echo $productReceipt->product_receipt_id; ?>" style="padding-left: 24px;"><?php echo "&#163;".number_format($recipt_table_data[$productReceipt->product_receipt_id]['bill_amount'],2); ?>&nbsp;</span>
        <span id="temp_<?php echo $productReceipt->id; ?>">
            
        </span>
        <input type="hidden" id= "amt_<?php echo $productReceipt->id; ?>" value="<?php echo round($productReceipt['amount'],2); ?>" /> 
        </td>
	   
		
		<?php $desc = $productReceipt['description'];
		if(empty($desc)){
			$desc = "--";
		}
		?>
		<td title="<?php echo $desc;?>"><span style="padding-left: 24px;"><?php echo $productReceipt['payment_method']?>&nbsp;</span></td>
		
		<td><span style="padding-left: 24px;">&#163;<?php echo number_format($productReceipt['amount'],2);?>
        </span>
       
    </td>
		
        <td>
            <input type="checkbox" id="chk_<?php echo $productReceipt->id; ?>" name="check[<?php echo $productReceipt->product_receipt_id; ?>]" value="Bike">
        </td>
	</tr>
<?php 			 }}
	endforeach; ?>
	
	</tbody>
	</table>
    
<?php // credit div ?>
<?php
		  $totalAmtDue = "0";
		  foreach ($credit_pay_res as $credit_productReceipt){
			   $totalAmtDue+= $credit_productReceipt['amount'];
		  }
		  $totalAmtDue = number_format($totalAmtDue,2);
	 ?>
<div id="credit_note_div">
<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
            <td colspan='3'>
                <strong><?php echo __('<span style="color:red; font-size:20px;">'.$kiosks[$kiosk_id].' Credit Note</span>') ?></strong>
				<?php echo"(Total Due: $totalAmtDue)"; ?>
            </td>
        </tr>
	<tr>
	 <th>Credit Note</th>
			<th><?php echo $this->Paginator->sort('created','Payment Date'); ?></th>
			<th><?php echo $this->Paginator->sort('credit_receipt_id',"#Invoice"); ?></th>
			<th>Customer</th>
			<th>Business</th>
			<th>#Cust</th>
            <th><span style='float: left;'>Total</span></th>
			
			<th><span ><?php echo $this->Paginator->sort('payment_method',"Mode"); ?></span></th>
			<th><span >Due Pmt</span></th>
			
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
		$tolalBillCosting = 0.0;
		//pr($credit_pay_res);
		foreach ($credit_pay_res as $credit_productReceipt):
		
		//pr($recipt_table_data);die;
		if(array_key_exists($credit_productReceipt->credit_receipt_id,$credit_data)){
			if($credit_data[$credit_productReceipt->credit_receipt_id]['status']==0){
				if(!empty($credit_data[$credit_productReceipt->credit_receipt_id]['bill_cost'])){
					$tolalBillCosting = $tolalBillCosting + $credit_data[$credit_productReceipt->credit_receipt_id]['bill_cost'];
					$billCost = $credit_data[$credit_productReceipt->credit_receipt_id]['bill_cost'];
					
				}else{
					$billCost = "--";
				}
	?>
	<tr>
		<td><?php
		//pr($createdArr);die;
				echo date("d-m-Y",strtotime($credit_productReceipt->created));
		//echo $this->Time->format('d-m-Y',$createdArr[$productReceipt['product_receipt_id']],null,null);?></td>
		<td><?php
				echo date("d-m-Y",strtotime($credit_productReceipt['created']));
		//echo $this->Time->format('d-m-Y',$productReceipt['created'],null,null); ?>&nbsp;</td>
		<td><?php echo h($credit_productReceipt['credit_receipt_id']); ?>&nbsp;</td>
		<td><?php echo $credit_data[$credit_productReceipt->credit_receipt_id]['fname']; ?>&nbsp;</td>
		<td>
            <?php echo $customer_data[0]->business;?>
        </td>
			
		<?php
		if($credit_productReceipt->agent_id != 0){
			$agent_name = $agents[$credit_productReceipt->agent_id];
		}else{
			$agent_name = "--";
		}
		 ?>
		<td title="<?php echo $agent_name;?>"><?php echo h($credit_data[$credit_productReceipt->credit_receipt_id]['customer_id']); ?>&nbsp;</td>
      
	  
		  <td><span style="padding-left: 24px;"><?php echo "&#163;".number_format($credit_data[$credit_productReceipt->credit_receipt_id]['bill_amount'],2); ?>&nbsp;</span>
		<input type="hidden" id= "credit_amt_<?php echo $credit_productReceipt->id; ?>" value="<?php echo round($credit_productReceipt['amount'],2); ?>" /> 
		</td>
		
		<?php $desc = $credit_productReceipt['description'];
		if(empty($desc)){
			$desc = "--";
		}
		?>
		<td title="<?php echo $desc;?>"><span style="padding-left: 24px;"><?php echo $credit_productReceipt['payment_method']?>&nbsp;</span>
		
		</td>
		<td><span style="padding-left: 24px;">&#163;<?php echo number_format($credit_productReceipt['amount'],2);?></span></td>
        <td>
             <input type="checkbox" id="credit_chk_<?php echo $credit_productReceipt->id; ?>" name="check[<?php echo $credit_productReceipt->credit_receipt_id; ?>]" value="Bike">
        </td>
	</tr>
<?php 			 }}
	endforeach; ?>
	
	</tbody>
	</table>

</div>
<script>
    function show_hide() {
        var checked = $('#credit_note_pay').is(':checked');
            if (checked) {
				$("#process_credit_div").show();
				$("#rest_td").hide();
               $("#credit_note_div").show();
			   
               $("#raw_amount").attr("disabled", "disabled");
			   $("#raw_amount_run_time").val("");
			    <?php
					foreach ($pay_res as $productReceipt){ ?>
					 $('#chk_'+<?php echo $productReceipt->id?>).attr("disabled", true);
					 $('#chk_'+<?php echo $productReceipt->id?>).attr("checked", false);
					 $('#temp_'+<?php echo $productReceipt->id?>).text("");
					 $('#span_'+<?php echo $productReceipt->id?>).css('background', '');
					 $("#raw_amount").val("");
				<?php }?>
				 
            } else {
			   $("#process_credit_div").hide();
				$("#rest_td").show();
                $("#raw_amount").removeAttr("disabled");
				$("#raw_amount").val("");
               $("#credit_note_div").hide();
			   <?php
					foreach ($pay_res as $productReceipt){ ?>
					 $('#chk_'+<?php echo $productReceipt->id?>).attr("disabled", false);
					 $('#chk_'+<?php echo $productReceipt->id?>).attr("checked", false);
					 $('#temp_'+<?php echo $productReceipt->id?>).text("");
					 $('#span_'+<?php echo $productReceipt->id?>).css('background', '');
				<?php }?>
			   <?php
					foreach ($credit_pay_res as $credit_productReceipt){ ?>
					$('#credit_chk_'+<?php echo $credit_productReceipt->id?>).attr("disabled", false);
					$('#credit_chk_'+<?php echo $credit_productReceipt->id?>).attr("checked", false);
					<?php } ?>
            }
    }
	
	function show_hide_datepicker() {
		var checked = $('#chk_datepicker').is(':checked');
            if (checked) {
              $("#datepicker_div").show(); 
            } else {
               $("#datepicker_div").hide();
            }
    }
</script>
<script>
    $( document ).ready(function() {
   $("#credit_note_div").hide();
   $("#datepicker_div").hide();
   $("#process_credit_div").hide();
   
   
});
</script>
<script>
     $(document).ready(function() {
		  //document.getElementById("referer").value = currentLocation;
		  var referer = document.getElementById('referer').value;
		<?php
	  foreach ($pay_res as $productReceipt){ ?>
      $('#chk_'+<?php echo $productReceipt->id?>).change(function() {
        if($(this).is(":checked")) { // if checked
			var pay_method = $("#change-mode").val();
			if (pay_method  == 0) {
                alert("Please Choose Payment Method");
				$('#chk_'+<?php echo $productReceipt->id?>).attr("checked", false);
				return false;
            }
			
			if($("#chk_datepicker").is(":checked")){
				
			}else{
				alert("Please Choose Date");
				$('#chk_'+<?php echo $productReceipt->id?>).attr("checked", false);
				return false;
			}
			
			var amt = parseFloat($('#amt_'+<?php echo $productReceipt->id?>).val());
				var alert_box_val = $("#alert_box_value").val();
				
				if(alert_box_val == "" || alert_box_val == "NaN"){
					
					$("#alert_box_value").val(amt);
				}else{
					var final_val = parseFloat(amt)+parseFloat(alert_box_val);
					$("#alert_box_value").val(final_val);
				}
			
			var checked = $('#credit_note_pay').is(':checked');
			 if (checked) {
				var pay_id = <?php echo $productReceipt->id?>;
				var invoice_id = <?php echo $productReceipt->product_receipt_id?>;
                on_check_do_credit_operation(pay_id,invoice_id);
				return false;
             }
            var amt = parseFloat($('#amt_'+<?php echo $productReceipt->id?>).val());
            var main_box_val = parseFloat($("#raw_amount").val());
            
            if (checked == false && (main_box_val === "" || main_box_val === "NaN" || main_box_val === null ||  isNaN(main_box_val))) {
                alert("Please enter the bulk Amount");
                 $('#chk_'+<?php echo $productReceipt->id?>).prop("checked", false);
                return false;
            }
            var remaining_amt = parseFloat(main_box_val)-parseFloat(amt);
			var remaining_amt = remaining_amt.toFixed(2);
            if (remaining_amt < 0) {
				remaining_amt = (-1)*remaining_amt;
				var amt_to_show = parseFloat($("#alert_box_value").val());
				amt_to_show = amt_to_show.toFixed(2);
				var Clearng_amt = $("#raw_amount_run_time").val();
				if (confirm("Selection Amt is "+ amt_to_show +", Clearing Amt Is "+Clearng_amt+". In this case invoice no "+<?php  echo $productReceipt->product_receipt_id ?> +" will be split in two rows, \r\n"+main_box_val+" will be cleared and "+ remaining_amt +" will left over."+'\r\n' +"Do You Want To process Invoices with these adjustments?")) {
				//if (confirm("Invoice Amt Is Exceding The Amt BY "+remaining_amt+" amt."+'\r\n' +"Do You Want To process Invoices with these adjustments?")) {
					process_invoice();
					//window.location.href = referer;
					
				} else {
					$('#chk_'+<?php echo $productReceipt->id?>).attr("checked", false);
					$("#raw_amount").val("");
					$("#raw_amount").val(main_box_val);
					if(alert_box_val == "" || alert_box_val == "NaN"){
					
						 $("#alert_box_value").val("");
					 }else{
						 //alert(amt);
						 //alert(alert_box_val);
						 //var final_val = parseFloat(amt)-parseFloat(alert_box_val);
						 //alert(final_val);
						 $("#alert_box_value").val(alert_box_val);
					 }
					 
				}
                // var text = $('#span_'+<?php echo $productReceipt->id?>).text();
                //$('#span_'+<?php echo $productReceipt->id?>).css('background', 'yellow');
                //$('#temp_'+<?php echo $productReceipt->id?>).text("amount adjusted is "+main_box_val);
                //disable_other();
                //$("#raw_amount").val("");
                //$("#raw_amount").val(0);
            }else if(remaining_amt == 0){
				if (confirm("Invoice Amt Is Matching The Amt "+'\r\n' +"Do You Want To process Invoices ?")) {
					process_invoice();
					
				} else {
					$('#chk_'+<?php echo $productReceipt->id?>).attr("checked", false);
					$("#raw_amount").val("");
					$("#raw_amount").val(main_box_val);
					
				}
			}else{
                $("#raw_amount").val("");
				
                $("#raw_amount").val(remaining_amt);
            }
            
           
        }else{ // unchecked
            
             var amt = parseFloat($('#amt_'+<?php echo $productReceipt->id?>).val());
		   var alert_box_val = $("#alert_box_value").val();
			
			if(alert_box_val == "" || alert_box_val == "NaN"){
				
				$("#alert_box_value").val();
			}else{
			   
				var final_val = parseFloat(alert_box_val)-parseFloat(amt);
				
				$("#alert_box_value").val(final_val);
			}
			
             var main_box_val = parseFloat($("#raw_amount").val());
              var checked = $('#credit_note_pay').is(':checked');
             if (checked == false && (main_box_val === "" || main_box_val === "NaN" || main_box_val === null ||  isNaN(main_box_val))) {
                alert("Please enter the bulk Amount");
                 $('#chk_'+<?php echo $productReceipt->id?>).prop("checked", false);
                return false;
            }
             $('#temp_'+<?php echo $productReceipt->id?>).text("");
             $('#span_'+<?php echo $productReceipt->id?>).css('background', '');
              var remaining_amt = parseFloat(main_box_val)+parseFloat(amt);
              remaining_amt = remaining_amt.toFixed(2);
              $("#raw_amount").val("");
              $("#raw_amount").val(remaining_amt);
              
        }
      });
      <?php
      }
      ?>
     });
     
     function disable_other() {
          <?php
	  foreach ($pay_res as $productReceipt){ ?>
       var checked = $('#chk_'+<?php echo $productReceipt->id?>).is(':checked');
       if (checked) {
        //do nothing
       }else{
            $('#chk_'+<?php echo $productReceipt->id?>).attr("disabled", true);
       }
      
      <?php }?>
     }
     
</script>
<script>
    jQuery('#raw_amount').on('input', function() {
		var amt_val = $(this).val();
		$("#raw_amount_run_time").val(amt_val);
       <?php
	  foreach ($pay_res as $productReceipt){ ?>
      $('#chk_'+<?php echo $productReceipt->id?>).prop("checked", false);
      $('#chk_'+<?php echo $productReceipt->id?>).attr("disabled", false);
      $('#temp_'+<?php echo $productReceipt->id?>).text("");
      $('#span_'+<?php echo $productReceipt->id?>).css('background', '');
      <?php
      }
      ?>
    });
</script>
<script>
    function reset_all() {
         <?php
	  foreach ($pay_res as $productReceipt){ ?>
       $('#chk_'+<?php echo $productReceipt->id?>).attr("disabled", false);
       $('#chk_'+<?php echo $productReceipt->id?>).attr("checked", false);
       $('#temp_'+<?php echo $productReceipt->id?>).text("");
       $('#span_'+<?php echo $productReceipt->id?>).css('background', '');
       $("#raw_amount").val("");
	  $("#alert_box_value").val("");
      <?php }?>
    }
    
</script>
<script>
	$(document).ready(function() {
        <?php
	  foreach ($credit_pay_res as $credit_productReceipt){ ?>
      $('#credit_chk_'+<?php echo $credit_productReceipt->id?>).change(function() {
        if($(this).is(":checked")) { // if checked
			var amt = parseFloat($('#credit_amt_'+<?php echo $credit_productReceipt->id?>).val());
			$("#raw_amount").val(amt);
			$("#raw_amount_run_time").val(amt);
			enable_or_disable_all_invoice(false);
            disable_other_credit_note();
        }else{ // unchecked
            $("#raw_amount").val("");
			uncheck_invoice_check_box();
			enable_or_disable_all_invoice(true);
            enable_other_credit_note(); 
        }
      });
      <?php
      }
      ?>
     });
	
		function uncheck_invoice_check_box() {
             <?php
					foreach ($pay_res as $productReceipt){ ?>
					 $('#chk_'+<?php echo $productReceipt->id?>).attr("disabled", false);
					 $('#chk_'+<?php echo $productReceipt->id?>).attr("checked", false);
					 $("#raw_amount").val("");
					<?php }?>
        }
	
	 function disable_other_credit_note() {
          <?php
	  foreach ($credit_pay_res as $credit_productReceipt){ ?>
       var checked = $('#credit_chk_'+<?php echo $credit_productReceipt->id?>).is(':checked');
       if (checked) {
        //do nothing
       }else{
            $('#credit_chk_'+<?php echo $credit_productReceipt->id?>).attr("disabled", true);
       }
      
      <?php }?>
     }
	 
	 function enable_other_credit_note() {
          <?php
	  foreach ($credit_pay_res as $credit_productReceipt){ ?>
       var checked = $('#credit_chk_'+<?php echo $credit_productReceipt->id?>).is(':checked');
       if (checked) {
        //do nothing
		$('#credit_chk_'+<?php echo $credit_productReceipt->id?>).attr("disabled", false);
       }else{
            $('#credit_chk_'+<?php echo $credit_productReceipt->id?>).attr("disabled", false);
       }
      
      <?php }?>
     }
	 
	 function on_check_do_credit_operation(id,invoice_id) {
        var amt = parseFloat($('#amt_'+id).val()); // invoice amt
            var main_box_val = parseFloat($("#raw_amount").val()); // box amt
			var remaining_amt = parseFloat(main_box_val)-parseFloat(amt);
			remaining_amt = remaining_amt.toFixed(2);
			
			var Clearng_amt = $("#raw_amount_run_time").val();
			var amt_to_show = parseFloat($("#alert_box_value").val());
			if (remaining_amt < 0) {
				remaining_amt = (-1)*remaining_amt;
				remaining_amt = remaining_amt.toFixed(2);
				amt_to_show = amt_to_show.toFixed(2);
				if (confirm("Selection Amt is "+ amt_to_show +", Clearing Amt Is "+Clearng_amt+". In this case invoice no "+invoice_id +" will be split in two rows, \r\n"+main_box_val+" will be cleared and "+ remaining_amt +" will left over."+'\r\n' +"Do You Want To process Invoices with these adjustments?")) {
				//if (confirm("Invoice Amt Is Exceding The Credit Note Amt BY "+remaining_amt+" amt."+'\r\n' +"Do You Want To process Invoices with these adjustments?")) {
					process_invoice_with_credit();
					$("#raw_amount").val("");
				} else {
					$('#chk_'+id).attr("checked", false);
					$("#raw_amount").val("");
					$("#raw_amount").val(main_box_val);
				}
                
            }else{
			   //remaining_amt = remaining_amt.toFixed(2);
                $("#raw_amount").val("");
                $("#raw_amount").val(remaining_amt);
            }
     }
	 
	 function enable_or_disable_all_invoice(true_or_false) {
         <?php
					foreach ($pay_res as $productReceipt){ ?>
						$('#chk_'+<?php echo $productReceipt->id?>).attr("disabled", true_or_false);
						$('#chk_'+<?php echo $productReceipt->id?>).attr("checked", false);
			  <?php }?>
     }
</script>
<script>
	function reset_all_on_page(){
		<?php
			foreach ($pay_res as $productReceipt){ ?>
				$('#chk_'+<?php echo $productReceipt->id?>).attr("disabled", false);
				$('#chk_'+<?php echo $productReceipt->id?>).attr("checked", false);
				$('#temp_'+<?php echo $productReceipt->id?>).text("");
				$('#span_'+<?php echo $productReceipt->id?>).css('background', '');
				$("#raw_amount").val("");
      <?php }?>
	  <?php
			foreach ($credit_pay_res as $credit_productReceipt){ ?>
				$('#credit_chk_'+<?php echo $credit_productReceipt->id?>).attr("disabled", false);
				$('#credit_chk_'+<?php echo $credit_productReceipt->id?>).attr("checked", false);
      <?php }?>
	  $('#credit_note_pay').attr("checked", false);
	  $('#raw_amount').attr("disabled", false);
	  $('#chk_datepicker').attr("checked", false);
	  $("#credit_note_div").hide();
	  $("#datepicker_div").hide();
	  $("#change-mode").val(0);
	  $("#raw_amount_run_time").val("");
		$("#alert_box_value").val("");	  
	}
</script>
<script>
	jQuery(function() {
		jQuery( "#datepicker" ).datepicker({ dateFormat: "d M yy" });
		$("#datepicker").val($.datepicker.formatDate("d M yy", new Date()));
		var min_date = $("#min_val_for_date").val();
		
		min_date = $.datepicker.formatDate('yy,mm,dd', new Date(min_date));
		$( "#datepicker" ).datepicker( "option", "minDate", new Date(min_date) );
		$( "#datepicker" ).datepicker( "option", "maxDate", new Date() );
	});
</script>
<script>
	function process_invoice() {
        var total_amt = $("#raw_amount_run_time").val();
		var ids = "";
		<?php
		foreach ($pay_res as $productReceipt){ ?>
			if($('#chk_'+<?php echo $productReceipt->id?>).is(":checked")) {
				if (ids == "") {
					ids =  <?php echo $productReceipt->id?>;
				}else{
					ids += ","+<?php echo $productReceipt->id?>;
				}
		
			}
		<?php
		}?>
		var payment_method = $("#change-mode").val();
		if($("#chk_datepicker").is(":checked")){
			var date = $("#datepicker").val();
		}else{
			var date = "";
		}
		
		var kiosk_id = $("#kiosk_id").val();
		var memo_content = $("#memo_content").val();
		var targeturl = $("#process_cart").val();
		targeturl += '?ids='+ids;
		targeturl += '&total_amt='+total_amt;
		targeturl += '&date='+date;
		targeturl += '&payment_method='+payment_method;
		targeturl += '&kiosk_id='+kiosk_id;
		targeturl += '&memo_content='+memo_content;
		
		//alert(targeturl);return false;
		
		$.blockUI({ message: 'Processing...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				var objArr = $.parseJSON(response);
				
				alert(objArr.msg);
			   var referer = document.getElementById('referer').value;
			   window.location.href = referer;
				//location.reload();
			},
			error: function(e) {
				$.unblockUI();
				alert("An error occurred: " + e.responseText.message);
				console.log(e);
			}
		});
		
    }
</script>
<script>
	 function process_invoice_with_credit() {
        var ids = "";
		<?php
		foreach ($pay_res as $productReceipt){ ?>
			if($('#chk_'+<?php echo $productReceipt->id?>).is(":checked")) {
				if (ids == "") {
					ids =  <?php echo $productReceipt->id?>;
				}else{
					ids += ","+<?php echo $productReceipt->id?>;
				}
			}
		<?php
		}?>
		
		
		var credit_id = "";
		<?php
		foreach ($credit_pay_res as $credit_productReceipt){ ?>
			if($('#credit_chk_'+<?php echo $credit_productReceipt->id?>).is(":checked")) {
				if (credit_id == "") {
					credit_id =  <?php echo $credit_productReceipt->id?>;
				}else{
					credit_id += ","+<?php echo $credit_productReceipt->id?>;
				}
			}
		<?php
		}?>
		
		var payment_method = $("#change-mode").val();
		if($("#chk_datepicker").is(":checked")){
			var date = $("#datepicker").val();
		}else{
			var date = "";
		}
		
		var kiosk_id = $("#kiosk_id").val();
		var memo_content = $("#memo_content").val();
		
		var targeturl = $("#process_credit_cart").val();
		targeturl += '?ids='+ids;
		targeturl += '&date='+date;
		targeturl += '&payment_method='+payment_method;
		targeturl += '&kiosk_id='+kiosk_id;
		targeturl += '&credit_id='+credit_id;
		targeturl += '&memo_content='+memo_content;
		
		
		//alert(targeturl);return false;
		$.blockUI({ message: 'Processing...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				var objArr = $.parseJSON(response);
				
				alert(objArr.msg);
				var referer = document.getElementById('referer').value;
			   window.location.href = referer;
			},
			error: function(e) {
				$.unblockUI();
				alert("An error occurred: " + e.responseText.message);
				console.log(e);
			}
		});
		
     }
</script>
<script>
	 $( document ).ready(function() {
     $("#credit_note_pay").trigger('click');
});
</script>