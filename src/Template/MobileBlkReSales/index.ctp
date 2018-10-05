<?php
    use Cake\Core\Configure;
	use Cake\Core\Configure\Engine\PhpConfig;
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
	//sort($kiosks);
	if(!isset($paymentMode)){$paymentMode = "Multiple";}
	if(!isset($mobileCostData)){
		$totalMobileCost = '';
	}else{
		//$totalMobileCost = $this->Number->currency(round($mobileCostData['0']['0']['total_mobile_cost'],2),'BRL');
		$totalMobileCost = round($mobileCostData,2);
	}
	//pr($payment_amount_arr);
?>
<div class="mobileReSales index">
	<?php //pr($mobileReSales);die;
	$adminKiosk = '';
	if(!empty($this->request->data)){
		$adminKiosk = $this->request->data['MobileResale']['kiosk_id'];
	}
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!isset($start_date)){$start_date = "";}
		if(!isset($end_date)){$end_date = "";}
		if(!isset($search_kw)){$search_kw = "";}
       ?>
	<?php
		if(!empty($this->request->query['search_kw'])){
			$value = $this->request->query['search_kw'];
		}else{
			 $value = '';
		}
	?>
	<?php $webRoot = $this->request->webroot."MobileBlkReSales/search";?>
	<?php echo $this->Form->create('Mobile_re_sales',array('url' => $webRoot,'type' => 'get'));?>
 
	 <div class="search_div">
 
			<fieldset>
			<legend>Search</legend>
			<div>
				<table style="width: 75%;">
					<tr>
						<td>
							<input type = "text" name = "search_kw" id = "search_kw" value= '<?= $value;?>' placeholder = "imei,model,brand ,email" style = "width:337px"  autofocus/>
						</td>
						<td>
							<input type = "radio" name = "payment_mode" value="Cash" id="cash_id" <?= $checked = ($paymentMode == 'Cash') ? 'checked' : '';?>>Cash&nbsp;
						</td>
						<td>
							<input type = "radio" name = "payment_mode" value="Card" id="card_id" <?= $checked = ($paymentMode == 'Card') ? 'checked' : '';?>>Card&nbsp;
						</td>
						<td>
							<input type = "radio" name = "payment_mode" value="refunded" id="refunded_radio" <?= $checked = ($paymentMode == 'refunded') ? 'checked' : '';?>>Refunded&nbsp;
						</td>
						<td>
							<input type = "radio" name = "payment_mode" value="Multiple" id="multiple_id" <?= $checked = ($paymentMode == 'Multiple') ? 'checked' : '';?>>All&nbsp;
						</td>
					</tr>
				</table>
			</div>
			<div>
			<table>
				<td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date" style = "width:155px" autofocus value='<?php echo $start_date;?>' /></td>
				<td><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date" style = "width:155px" value='<?php echo $end_date;?>' /></td>
				
				<?php 
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){?>
				<td>
				 
				 <?php
				   
						//if( $this->request->session()->read('Auth.User.group_id')!= MANAGERS){
								if(!empty($kioskId)){
									echo $this->Form->input(null, array(
																			'options' => $kiosks,
																			'label' => false,
																			'div' => false,
																			'id'=> 'kioskid',
																			'value' => $kioskId,
																			'name' => 'MobileBlkReSale[kiosk_id]',
																			'empty' => 'Select Kiosk',
																			'default' => $adminKiosk,
																			'style' => 'width:160px'
																		)
									  );
							   }else{
									 echo $this->Form->input(null, array(
																			'options' => $kiosks,
																			'label' => false,
																			'div' => false,
																			'id'=> 'kioskid',
																			'name' => 'MobileBlkReSale[kiosk_id]',
																			'empty' => 'Select Kiosk',
																			'style' => 'width:160px'
																)
											);
								}
						//}else{
						//	 if(!empty($kioskId)){
						//					echo $this->Form->input(null, array(
						//														'options' => $manager_kiosks,
						//														'label' => false,
						//														'div' => false,
						//														'id'=> 'kioskid',
						//														'value' => $kioskId,
						//														'name' => 'MobileBlkReSale[kiosk_id]',
						//														'empty' => 'Select Kiosk',
						//														'default' => $adminKiosk,
						//														'style' => 'width:160px'
						//														)
						//									);
						//	}else{
						//			echo $this->Form->input(null, array(
						//										'options' => $manager_kiosks,
						//										'label' => false,
						//										'div' => false,
						//										'id'=> 'kioskid',
						//										 'name' => 'MobileBlkReSale[kiosk_id]',
						//										'empty' => 'Select Kiosk',
						//										'style' => 'width:160px'
						//										)
						//									   );
						//	}
						//}
					  
				  
				  ?></span>
				</td>
				<?php  }  ?>
				<td><input type = "submit" value = "Search Mobile Re-Sales" name = "submit",style = "width:155px"/></td>
				<td><input type='button' name='reset' value='Reset Search' style = "width:155px" onClick='reset_search();'/></td>
			</div>
			</table>
			<?php
			if(count($this->request->query)){
                //pr($this->request->query);die;
                if(array_key_exists('submit',$this->request->query)){
                    $netSale = $saleSum-$totalMobileRefund;
                    $start = $this->request->query['start_date'];
					$last = $this->request->query['end_date'];
					if(array_key_exists('MobileBlkReSale',$this->request->query)){
						$kiosk = $this->request->query['MobileBlkReSale']['kiosk_id'];
					}else{
						$kiosk = $_SESSION['kiosk_id'];
					}
                
				?>
			<span style="float: left; font-weight : bold">Gross Sale = &#163;<?=$saleSum?>, Refund = &#163;<?=$totalMobileRefund;?>, Net Sale = &#163;<?=$netSale;?></span>
			<span style="float: right; font-weight : bold;">Cost of sold phones = &#163 <?php echo $this->Html->link($totalMobileCost, array('action' => 'research',$start,$last,$kiosk),array('target'=>'_blank','escape'=>false)); ?><?//=$totalMobileCost;?></span>
			
			<?php  } } ?>
		</fieldset>
	</div>
   

 <?php echo $this->Form->end(); ?>

 <?php
  
  $queryStr = "";
  $rootURL = $this->request->webroort;//$this->html->url('/', true);
  if( isset($this->request->query['search_kw']) ){
  // $queryStr.="search_kw=".$this->request->query['search_kw'];
  $queryStr.="?search_kw=".$this->request->query['search_kw']."&start_date=".$start_date."&end_date=".$end_date."&kiosk_id=".$kiosk_id ;
  }
 
   
 ?>
 <?php if( isset($this->request->query['search_kw']) ){ ?>
	<h2><?php echo __('Mobile Bulk Sales'); ?>&nbsp;<a href="<?php echo $rootURL;?>export/?<?php echo $queryStr;?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png', array('fullBase' => true));?></a></h2>
    <?php }else{ ?>
    <h2><?php echo __('Mobile Bulk Sales'); ?>&nbsp;<a href="<?php echo $rootURL;?>MobileBlkReSales/export/?<?php echo $queryStr;?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png', array('fullBase' => true));?></a></h2>
    <?php } ?>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id','#Rcpt'); ?></th>
			
		<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){?>
			<th><?php echo $this->Paginator->sort('kiosk_id'); ?></th>
		<?php } ?>
			<th><?php echo $this->Paginator->sort('brand_id'); ?></th>
			<th><?php echo $this->Paginator->sort('model'); ?></th>
			<th><?php echo $this->Paginator->sort('color'); ?></th>
			<th><?php echo $this->Paginator->sort('imei','IMEI'); ?></th>
			<th><?php echo $this->Paginator->sort('selling_price'); ?></th>
			<th>Payment Mode</th>
			<th><?php echo $this->Paginator->sort('customer_fname','Name'); ?></th>
			<th><?php echo $this->Paginator->sort('Sold By'); ?></th>
			<th><?php echo $this->Paginator->sort('Refund by'); ?></th>
			<th><?php echo $this->Paginator->sort('refund_price'); ?></th>
			
			<th><?php echo $this->Paginator->sort('created','Sale Date'); ?></th>
			<th><?php echo $this->Paginator->sort('Refund Date'); ?></th>
			
			<th class="actions"><?php #echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	$totalPrice = $totalRefundPrice = $totalSearchedAmount = 0;
   // pr($brands);die;
	foreach ($mobileReSales as $mobileReSale):
    //pr($mobileReSale);die;
		if((int)$mobileReSale->sale_id){
			$id = $mobileReSale->sale_id;
		}else{
			$id = $mobileReSale->id;
		}
	
		//pr($this->request->query);
		if(array_key_exists($mobileReSale->id,$paymentArr)){
			foreach($payment_amount_arr[$mobileReSale->id] as $pmtMode => $pmtAmt){
				if($paymentMode != '' && $paymentMode != 'Multiple'){
					if($paymentMode == $pmtMode){
						$totalSearchedAmount+=$pmtAmt;//getting total amount as per the searched mode
					}
				}
			}
			
			if(count($paymentArr[$mobileReSale->id]) > 1){
				$multipleModes = array();
				foreach($payment_amount_arr[$mobileReSale->id] as $pmtMode => $pmtAmt){
					$multipleModes[] = "$pmtMode = $pmtAmt";
				}
				$multileStr = implode(', ',$multipleModes);
				$mode = "Multiple($multileStr)";
			}elseif(count($paymentArr[$mobileReSale->id]) == 1){
				#$mode = $paymentArr[$mobileReSale['MobileReSale']['mobile_purchase_id']][0]['RepairPayment']['payment_method'];
				$singleMode = $paymentArr[$mobileReSale->id][0]['payment_method'];
				$singleAmount = $paymentArr[$mobileReSale->id][0]['amount'];
				$mode = "$singleMode($singleAmount)";
			}
		}else{
			$mode = '--';
			}
			if($mobileReSale->discounted_price>0){
				$price = $mobileReSale->discounted_price;
			}else{
				$price = $mobileReSale->selling_price;
			}
		
			$totalPrice+=$price;
		
			if($mobileReSale->refund_price > 0){
				$refundPrice = $mobileReSale->refund_price;
				//echo "['MobileReSale']['refund_date']:->".$mobileReSale['MobileReSale']['refund_date'];
				if(!empty($mobileReSale->refund_date)){
					$refundDate = date('jS M, Y g:i A',strtotime($mobileReSale->refund_date));//$this->Time->format('jS M, Y g:i A', $mobileReSale->refund_date,null,null);
				}else{
					$refundDate = '--';
				}
			}else{
				$refundPrice = '--';
				$refundDate = '--';
			}
		
			$totalRefundPrice+=(int)$refundPrice;
			
			if(array_key_exists($mobileReSale->user_id,$users)){
				$soldBy = $users[$mobileReSale->user_id];
			}else{
				$soldBy = '--';
			}
			
			if($mobileReSale->status==1){
?>
	<tr style="background:yellow;">
	<?php }else{?>
	<tr>
	<?php } ?>
		<td><?php echo $id; ?>&nbsp;</td>
		
        
		<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){?>
		<td>
			<?php
			if(empty($mobileReSale->kiosk_id)){
				echo "Warehouse";
			}else{
				echo $this->Html->link($kiosks[$mobileReSale->kiosk_id], array('controller' => 'kiosks', 'action' => 'view', $mobileReSale->kiosk_id));
			}
			 ?>
		</td>
		<?php } ?>
		<td>
			<?php echo $this->Html->link($brands[$mobileReSale->brand_id], array('controller' => 'brands', 'action' => 'view', $mobileReSale->brand_id)); ?>
		</td>
		<td><?php
        if(array_key_exists($mobileReSale->mobile_model_id,$modelName)){
            echo h($modelName[$mobileReSale->mobile_model_id]);
        }else{
            echo "";
        }
        //pr($modelName);die;?>&nbsp;</td>
		<td><?php
		if(array_key_exists($mobileReSale->color,$colorOptions)){
			echo h($colorOptions[$mobileReSale->color]);
		}
		 ?>&nbsp;</td>
		<td><?php echo $this->Html->link($mobileReSale->imei,array('controller'=>'mobile_purchases','action'=>'mobile_transfer_logs', $mobileReSale->imei),array('alt'=>'Logs','title'=>'Logs')); ?>&nbsp;</td>
		<td><?php echo $CURRENCY_TYPE.$price;  ?>&nbsp;</td>
		<td><?=$mode;?></td>
		<td><?php echo h($mobileReSale->customer_fname); ?>&nbsp;</td>
		<td><?php echo $soldBy;?></td>
		
		<td><?php if(!$mobileReSale->refund_by){
			echo "--";
		}
		else{
			if(array_key_exists($mobileReSale->refund_by,$users)){
				echo  $users[$mobileReSale->refund_by];	
			}else{
				echo "User Is either deleted or not Found";
			}
			
		}?></td>
		<?php if($refundPrice>0){?>
		<td><?php  echo $CURRENCY_TYPE.$refundPrice;?>&nbsp;</td>
		<?php }else{ ?>
		<td><?php if($refundPrice == '' || $refundPrice == '--'){
					echo $CURRENCY_TYPE."0.00";
					}else{
						echo $CURRENCY_TYPE.$refundPrice;
					};?>&nbsp;
		</td>
		<?php } ?>
		
		<?php
				if($mobileReSale->selling_date == "0000-00-00 00:00:00"||empty($mobileReSale->selling_date)){
					$created = $mobileReSale->created;
				}else{
					$created = $mobileReSale->selling_date;
				}
		?>
		<td><?php echo date('jS M, Y g:i A',strtotime($created));//$this->Time->format('jS M, Y g:i A', $created,null,null); ?>&nbsp;</td>
		<td><?php echo $refundDate; ?>&nbsp;</td>
		
		<td>
			
			<?php $editUrl = "/img/16_edit_page.png";
			$viewUrl = "/img/text_preview.png";
			$cloneUrl = "/img/fileview_close_right.png";
			?>
			<?php echo $this->Html->link($this->Html->image($editUrl,array('fullBase' => true, 'title' => 'Edit', 'alt' => 'Edit')), array('action' => 'edit', $id),array('escapeTitle' => false, 'title' => 'Edit', 'alt' => 'Edit'));?>
			<?php echo $this->Html->link($this->Html->image($viewUrl,array('fullBase' => true, 'title' => 'View', 'alt' => 'View')), array('action' => 'view', $id), array('escapeTitle' => false, 'title' => 'View', 'alt' => 'View'));?>
			<?php echo $this->Html->link($this->Html->image($cloneUrl,array('fullBase' => true, 'title' => 'Update Payment', 'alt' => 'Update Payment')), array('action' => 'update_resale_payment', $id), array('escapeTitle' => false, 'title' => 'Update Payment', 'alt' => 'Update Payment'));?>
			<?php //if((AuthComponent::user('group_id') == ADMINISTRATORS && $mobileReSale['MobileReSale']['kiosk_id']==0 ||
			  	// AuthComponent::user('group_id') == KIOSK_USERS && $mobileReSale['MobileReSale']['kiosk_id']==$kiosk_id) &&
			 	 //$mobileReSale['MobileReSale']['status']==0){
				 ?>
			<?php #echo $this->Html->link(__('Refund'), array('action' => 'mobile_refund', $mobileReSale['MobileReSale']['id'])); }?>
			<?php #echo $this->Html->link(__('Receipt'), array('action' => 'mobile_sale_receipt', $mobileReSale['MobileReSale']['mobile_purchase_id'])); ?>
			<?php #echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $mobileReSale['MobileReSale']['id']), array(), __('Are you sure you want to delete # %s?', $mobileReSale['MobileReSale']['id'])); ?>
		</td>
	</tr>
<?php endforeach;
	if($totalSearchedAmount > 0){
		$totalPrice = $totalSearchedAmount;
	}
	?>
		<tr>
			<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
			<td>&nbsp;</td>
			<?php } ?>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td><strong>Total:</strong></td>
			<td><strong><?php echo $CURRENCY_TYPE.$totalPrice;//echo $currency.$totalPrice; ?></strong></td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td><strong><?php echo $CURRENCY_TYPE.$totalRefundPrice;//echo $currency.$totalRefundPrice; ?></strong></td>			
		</tr>
		<tr>
			<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
			<td>&nbsp;</td>
			<?php } ?>
			<td>&nbsp;</td>
			<td colspan='3'><strong>Sale including VAT(<i>sum of all payment modes</i>):</strong></td>
			<td><strong><?php echo $CURRENCY_TYPE.$grandNetSale;//echo $currency.$grandNetSale; ?></strong></td>
		</tr>
		<?php $netAmount = $grandNetSale/(1+$vat/100);
			$vatAmount = $grandNetSale-$netAmount;?>
		<tr>
			<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
			<td>&nbsp;</td>
			<?php } ?>
			<td colspan='4'><strong>Sale excluding VAT(<?php echo $CURRENCY_TYPE.$vatAmount;//echo $currency.number_format($vatAmount,2);?>):</strong></td>
			<td><strong><?php echo $CURRENCY_TYPE.$netAmount;//echo $currency.number_format($netAmount,2); ?></strong></td>
				
		</tr>
		<tr>
			<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
			<td>&nbsp;</td>
			<?php } ?>
			<td colspan='4'><strong>Total refund gain:</strong></td>
			<td><strong><?php echo $CURRENCY_TYPE.$totalRefundGain;//echo $currency.number_format($totalRefundGain,2); ?></strong></td>
				
		</tr>
		<tr>
			<td colspan='10'><i>**highlighted rows have been refunded**</i></td>
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
		
		
		<li><?php echo $this->Html->link(__('List Mobile Purchases'), array('controller'=>'mobile_purchases','action' => 'index')); ?></li>
		
		<li><?php echo $this->Html->link(__('View Mobile Sale'), array('controller' => 'mobile_re_sales', 'action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('Buy Mobile Phones'), array('controller' => 'mobile_purchases', 'action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('Mobile Stock/Sell'), array('controller' => 'mobile_purchases', 'action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('Global Mobile Search'), array('controller' => 'mobile_purchases', 'action' => 'global_search')); ?></li>
	</ul>
</div>
<script>
 function reset_search(){
	jQuery( "#datepicker1" ).val("");
	jQuery( "#datepicker2" ).val("");
	jQuery("#search_kw").val("");
	jQuery("#kioskid").val("");
	$('#cash_id').attr('checked', false);
	$('#card_id').attr('checked', false);
	$('#multiple_id').attr('checked', false);
       }
       jQuery(function() {
	jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
	jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
 });
</script>