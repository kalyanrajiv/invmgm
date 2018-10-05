<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<?php //pr($payment_amount_arr);die;
//echo $paymentMode;die;
	if(!isset($start_date)){$start_date = "";}
	if(!isset($end_date)){$end_date = "";}
	if(!isset($search_kw)){$search_kw = "";}
	if(!isset($search_kw1)){$search_kw1 = "";}
	if(!isset($paymentMode)){$paymentMode = "";}
	$kiosks['-1'] = 'All';
    $allowed_users['-1'] = 'All';
?>
<?php
 ksort($kiosks);
 ksort($allowed_users);
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
	<h2><?php echo __('Amended Payments')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>";?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	</h2>
	<form action='<?php echo $this->request->webroot; ?>KioskProductSales/search_sale_log' method = 'post'>
		<fieldset>
			<legend>Search</legend>
			<div style="height: 69px;">
				<table>
					<tr>
						<td><input type = "text" name = "search_kw" id = "search_kw" placeholder = "product code,title" autofocus style = "width:150px;height: 25px;"value='<?php echo $search_kw;?>'/></td>
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
                                    $kioskId = -1;
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
								      }
								?></span>
						</td>
							<?php  }  ?>
                <td>
							<?php
								if(!empty($allowed_users_id)){
									echo $this->Form->input(null, array(
										'options' => $allowed_users,
										 'label' => false,
										 'div' => false,
										       'name' => 'ProductSale[user_id]',
										      'id'=> 'user_id',
										      'value' => $allowed_users_id,
										      //'empty' => 'Select Kiosk',
										      'style' => 'width:185px'
											)
										);
								}else{
                                    $allowed_users_id = -1;
										echo $this->Form->input(null, array(
											'options' => $allowed_users,
											'label' => false,
											'div' => false,
											 'name' => 'ProductSale[user_id]',
											'id'=> 'user_id',
                                            'value' => $allowed_users_id,
											//'empty' => 'Select Kiosk',
											'style' => 'width:185px'
												)
											);
								}
								?></span>
						</td>
						<td><input type = "submit" value = "Search" name = "submit" 'style' ='width:185px'/>
						<td><input type='button' name='reset' value='Reset' style='padding:6px 8px;color:#333; border:1px solid #bbb;border-radius:4px;' onClick='reset_search();'/></td>
						
					</tr>
				</table>
			</div>
            <?php
            if(!isset($totalsum)){
                $totalsum = 0;
            }
            
            if($totalsum < 0){
                $totalsum = $totalsum * -1;?>
                <span style="float: left; font-weight : bold">Loss =<?=$this->Number->currency($totalsum,'BRL');?>, Modified amount =<?=$this->Number->currency($totalModfyAmt,'BRL');?>, Org amount =<?=$this->Number->currency($totalOrgAmt,'BRL');?> </span>
            <? }else{ ?>
                <span style="float: left; font-weight : bold">Profit =<?=$this->Number->currency($totalsum,'BRL');?>, Modified amount =<?=$this->Number->currency($totalModfyAmt,'BRL');?>, Org amount =<?=$this->Number->currency($totalOrgAmt,'BRL');?></span>
           <?php }
            ?>
		</fieldset>	
	</form>
	 
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('receipt_id', 'ReciptId'); ?></th>
            <th><?php echo $this->Paginator->sort('kiosk_id','Kiosk'); ?></th>
            <th><?php echo $this->Paginator->sort('product_code','Product Code'); ?></th>
            <th><?php echo $this->Paginator->sort('product_title','Product Title'); ?></th>
            <th><?php echo $this->Paginator->sort('quantity','Qantity'); ?></th>
			<th><?php echo $this->Paginator->sort('orignal_amount','Lowest sale price'); ?></th>
			<th><?php echo $this->Paginator->sort('modified_amount','Modified Amount'); ?></th>
			<th>Amended By</th>
			<th><?php echo $this->Paginator->sort('sale_date'); ?></th>
			<th><?php echo $this->Paginator->sort('Amend(Dt)'); ?></th>
	</tr>
	</thead>
	<tbody>
		<?php
        foreach($saleLog as $key => $value){
            //pr($value);die;
		?>
			<tr>
				<td><?=$value->receipt_id;?></td>
				<td><?=$kiosks[$value->kiosk_id];?></td>
                <td><?php echo $value->product_code;?></td>
                <td><?php echo $value->product_title;?></td>
                <td><?php echo $value->quantity;?></td>
				<td><?php echo $CURRENCY_TYPE.$value->orignal_amount;?></td>
				<td><?php echo $CURRENCY_TYPE.$value->modified_amount;?></td>
				<td><?=$users[$value->user_id];?></td>
				<td><?=date('jS M, Y h:i A',strtotime($value->sale_date));//$this->Time->format('jS M, Y h:i A', $value->sale_date,null,null);?></td>
                <td><?=date('jS M, Y h:i A',strtotime($value->created));//$this->Time->format('jS M, Y h:i A', $value->created,null,null);?></td>
			</tr>
		<?php }
		
    ?>
	</tbody>
	<tr><td colspan='1'></td>
	<td colspan='3'><strong>Total (as per selected mode)</strong></td>
	<th><?php
				
	?></th>
    
	</tr>
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
		<li><?php #echo $this->Html->link(__('New Mobile Repair Log'), array('action' => 'add')); ?></li>
		<li><?php #echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php #echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('View Sales'), array('controller' => 'kiosk_product_sales', 'action' => 'index')); ?> </li>
	</ul>
</div>
 <script>
	function reset_search(){
		jQuery( "#datepicker1" ).val("");
		jQuery( "#datepicker2" ).val("");
		jQuery("#search_kw").val("");
		jQuery("#search_kw1").val("");
        jQuery("#kioskid").val("");
        jQuery("#user_id").val("");
		$('#user_id').val("-1");
		$('#kioskid').val("-1");
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