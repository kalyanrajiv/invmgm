<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$currency = Configure::read('CURRENCY_TYPE');
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<div class="invoiceOrders form">
<?php echo $this->Form->create();
$paymentType = array('Cash' => 'Cash', 'Card' => 'Card');
?>
<?php ?>
<input type="hidden" name="sale_amount" id='sale_amount' value='<?=$changed_amount;?>'>
<input type="hidden" name="final_amount" id='final_amount' value='<?=$finalAmt;?>'>
	<div id="error_div" tabindex='1'></div>
	<fieldset>
		<legend>Update Payment</legend>
		<h4>Total Amount = &#163;<?=$finalAmt;?></h4>
		<table>
			<tr>
				<th>Id</th>
				<th>Kiosk</th>
				<th>Sold By</th>
				<th>Recit Id</th>
				<th>Payment Method</th>
				<th>Amount</th>
				<th>Payment Date</th>
				<th>Update Mode</th>
			</tr>
            
	<?php
    //pr($ProductPayment);
    foreach($ProductPayment as $key => $paymentInfo){
		$paymentId = $paymentInfo['id'];
		$amount = $paymentInfo['amount'];
		?>
		<?php
		if($paymentInfo['status'] == 2){
			echo "<tr style='background-color: yellow;'>";
		 }else{ 
			echo "<tr>";
		 }
		?>
				<td><?php echo $paymentInfo['id'];?></td>
				<td><?php echo $kiosks[$paymentInfo['kiosk_id']];?></td>
				<td><?php echo $users[$paymentInfo['user_id']];?></td>
				<td><?php echo $paymentInfo['product_receipt_id'];?></td>
				<td><?php echo $paymentInfo['payment_method'];?></td>
				<?php if($paymentInfo['status'] == 2){?>
					<td><?php echo $this->Form->input('updated_amount',array('name' => "data[updated_amount][$paymentId]", 'value' => $amount, 'label' => false, 'style' => "width: 60px;", 'id' => "updated_amount_".$paymentInfo['id'],'disabled' => TRUE));?></td>
				<?php }else{ ?>
					<td><?php echo $this->Form->input('updated_amount',array('name' => "data[updated_amount][$paymentId]", 'value' => $amount, 'label' => false, 'style' => "width: 60px;", 'id' => "updated_amount_".$paymentInfo['id']));?></td>
				<?php } ?>
				
				<td><?php echo $this->Time->format('jS M, Y g:i A', $paymentInfo['created'],null,null);?></td>
				<?php if($paymentInfo['status'] == 2){?>
				<td><?php echo $paymentInfo['payment_method'];?></td>
				<?php }else{ ?>
				<td><?php echo $this->Form->input('change_mode',array('options'=>$paymentType,'label'=>false,'default' => $paymentInfo['payment_method'],'name' => "data[UpdatePayment][$paymentId]"))?></td>
				<?php } ?>
				
			</tr>
	<?php }
		if(count($ProductPayment) == 1){ ?>
			<tr style="display: none;" id="hidden_row">
				<td>
					<input type="text" name="added_amount" id='added_amount' value='0'/>
				</td>
				<td>
					<?php echo $this->Form->input('new_change_mode',array('options'=>$paymentType,'label'=>false))?>
				</td>
			</tr>
			<tr>
				<td>
					<span><a href="javascript:void(0)" id="open_new">+</a></span>
				</td>
			</tr>
	<?php }
	?>
		</table>
	</fieldset>
	<div class="submit">
		<input type="submit" name='cancel' value="Cancel" style="margin-top: 16px;"/>
	</div>
	<?php
		$options=array('div'=>true,'label'=>'Update Payment', 'id' => 'update_payment');
        echo $this->Form->Submit('Update Payment',$options);
        echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('view sale'), array('controller' => 'product_receipts', 'action' => 'kiosk_product_payments')); ?> </li>
	</ul>
</div>
<script>
	$('#update_payment').click(function(){
		var totalamount = 0;
		var givenamount = $('#final_amount').val();
		<?php foreach($ProductPayment as $key => $paymentInfo){?>
			totalamount+=parseFloat($('#updated_amount_'+<?=$paymentInfo['id']?>).val());
		<?php } ?>
		var isDisabled = $('#added_amount').prop('disabled');
		
		if (isDisabled == false) {
			//alert('enabled');
			totalamount =  parseFloat($('#added_amount').val()) + totalamount;
		}
		totalamount = totalamount.round(2);
		if (totalamount != givenamount) {
			alert("Total amount must be equivalent to the sale amount("+ givenamount +") Now it is="+totalamount+"!");
			$('#error_div').html("Total amount must be equivalent to the sale amount("+ givenamount +")!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			return false;
		} else {
			return true;
		}
	});
	
	$('#open_new').click(function(){
		$('#hidden_row').css("display", "block");
		$('#added_amount').prop('disabled', false);
	});
	
	$(document).ready(function(){
		$('#added_amount').prop('disabled', true);
	});
</script>