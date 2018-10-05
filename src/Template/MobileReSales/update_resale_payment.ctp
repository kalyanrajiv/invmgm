<?php
use Cake\Core\Configure;
    use Cake\Core\Configure\Engine\PhpConfig;
$currency = Configure::read('CURRENCY_TYPE');
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<div class="invoiceOrders form">
<?php echo $this->Form->create(); ?>
	<fieldset>
		<legend>Update Payment</legend>
		<div id="error_div" tabindex='1'></div>
		<input type="hidden" name="sale_amount" id='sale_amount' value='<?=$saleAmount;?>'>
		<h4>Total Amount = &#163;<?=$saleAmount;?></h4>
		<table>
			<tr>
				<th>Id</th>
				<th>Kiosk</th>
				<th>Sold By</th>
				<th>Purchase Id</th>
				<th>Sale Id</th>
				<th>Payment Method</th>
				<th>Amount</th>
				<th>Payment Date</th>
				<th>Update Mode</th>
			</tr>
	<?php foreach($paymentData as $key => $paymentInfo){
        //pr($paymentInfo);die;
		$paymentId = $paymentInfo['id'];
		$amount = $paymentInfo['amount'];
		?>
			<tr>
				<td><?php echo $paymentInfo['id'];?></td>
				<td><?php echo $kiosks[$paymentInfo['kiosk_id']];?></td>
				<td><?php echo $users[$paymentInfo['user_id']];?></td>
				<td><?php echo $paymentInfo['mobile_purchase_id'];?></td>
				<td><?php echo $paymentInfo['mobile_re_sale_id'];?></td>
				<td><?php echo $paymentInfo['payment_method'];?></td>
				<td><?php echo $this->Form->input('updated_amount',array('name' => "updated_amount[$paymentId]", 'value' => $amount, 'label' => false, 'style' => "width: 60px;",'id' => "updated_amount_".$paymentInfo['id']));?></td>
				<td><?php echo date('jS M, Y g:i A',strtotime($paymentInfo['created']));//$this->Time->format('jS M, Y g:i A', $paymentInfo['created'],null,null);?></td>
				<td><?php echo $this->Form->input('change_mode',array('options'=>$paymentType,'label'=>false,'default' => $paymentInfo['payment_method'],'name' => "UpdatePayment[$paymentId]"))?></td>
			</tr>
		<?php }
		if(count($paymentData) == 1){ ?>
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
		<?php
		$options=array('div'=>false,'label'=>'Update Payment', 'id' => 'update_payment');
		echo $this->Form->Submit('Update Payment',$options);
        echo $this->Form->end(); ?>
		<td><input type="submit" name='cancel' value="Cancel" style="margin-top: 16px;"/></td>
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('View Mobile Sale'), array('action' => 'index')); ?> </li>
	</ul>
</div>
<script>
	$('#update_payment').click(function(){
		var totalamount = 0;
		var givenamount = $('#sale_amount').val();
		<?php foreach($paymentData as $key => $paymentInfo){?>
		  	totalamount+=parseFloat($('#updated_amount_'+<?=$paymentInfo['id'];?>).val());
		<?php } ?>
		var isDisabled = $('#added_amount').prop('disabled');
		if (isDisabled == false) {
			// alert('enabled');
			totalamount =  parseFloat($('#added_amount').val()) + totalamount;
		}
		if (totalamount != givenamount) {
			alert("Total amount must be equivalent to the sale amount("+ givenamount +")!");
			$('#error_div').html("Total amount must be equivalent to the sale amount("+ givenamount +")!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			return false;
		} else {
			$.blockUI({ message: 'Just a moment...' });
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
