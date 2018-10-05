<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$jQueryURL = "http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
if(defined('URL_SCHEME')){
	$jQueryURL = URL_SCHEME."ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
}
?>
<script type="text/javascript" src="<?php echo $jQueryURL;?>"></script>
<?php echo $this->Html->script('jquery.printElement');?>
<?php
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
	if(!isset($paymentMode)){$paymentMode = "";}
	if(!isset($mobileCostData)){
		$totalMobileCost = '';
	}else{
        //pr($mobileCostData);die;
		$totalMobileCost = $CURRENCY_TYPE.round($mobileCostData['total_mobile_cost'],2);
	}
?>
<div class="mobileReSales index">
	<?php 
		$value = $adminKiosk = '';
		if(!empty($this->request->data)){
			$adminKiosk = $this->request->data['MobileResale']['kiosk_id'];
		}
		$qryStrArr = array();
		$kiosk_id = $this->request->Session()->read('kiosk_id');
		if(!isset($start_date)){$start_date = "";}else{$qryStrArr[]="start_date=$start_date";}
		if(!isset($end_date)){$end_date = "";}else{$qryStrArr[]="end_date=$end_date";}
		if(!isset($search_kw)){$search_kw = "";}else{$qryStrArr[]="search_kw=$search_kw";}
		if(!isset($kioskId)){$kioskId = "";}else{$qryStrArr[]="kioskId=$kioskId";}
		if(!empty($this->request->query['search_kw'])){$value = $this->request->query['search_kw'];}
	?>
	<?php $webRoot = $this->request->webroot."mobile_re_sales/search";?>
 <?php
	$queryStr = "";
	$rootURL = "";//$this->html->url('/', true);
	if( isset($this->request->query['search_kw']) ){
	 $queryStr.="search_kw=".$this->request->query['search_kw'];
	} 
 ?>
	<div id='printDiv'>
	<input type='button' id='printSelected' name='print' value='Print Receipt' style='width:200px;' align='center' />
	<?php if(array_key_exists('submit',$this->request->query)){ ?>
    <a href="/mobile-blk-re-sales/export_cost/?<?php echo implode('&',$qryStrArr);?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png', array('fullBase' => true));?></a>
    <?php }else{  ?>
    <a href="/mobile-blk-re-sales/export_cost/?<?php echo implode('&',$qryStrArr);?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png', array('fullBase' => true));?></a>
    <?php } ?>
	<h3><?php echo __('Mobile Sales and Purchase Summary For The Period From '.$start_date.' To '.$end_date); ?></h3>
	
	 
	<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th><?php echo $this->Paginator->sort('id','#Rcpt'); ?></th>
			<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){?>
			<?php } ?>
			<th><?php echo $this->Paginator->sort('brand_id'); ?></th>
			<th><?php echo $this->Paginator->sort('model'); ?></th>
			<th><?php echo $this->Paginator->sort('imei','IMEI'); ?></th>
			<th><?php echo $this->Paginator->sort('selling_price'); ?></th>
			<th><?php echo $this->Paginator->sort('cost_price'); ?></th>
			<th><?php echo $this->Paginator->sort('refund_price'); ?></th>
			<th><?php echo $this->Paginator->sort('customer_fname','Name'); ?></th>
			<th><?php echo $this->Paginator->sort('refund_date','Refund Date'); ?></th>
			<th><?php echo $this->Paginator->sort('created','Sale Date'); ?></th>
			<th class="actions"><?php #echo __('Actions'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	$totalPrice = $totalRefundPrice = $totalSearchedAmount = $totalCostPrice =  0;
	$refundEntry = array();
	$CostPrice = array();
	foreach ($mobileReSales as $mobileReSale):
    //pr($mobileReSale);die;
		if((int)$mobileReSale->sale_id){
			$id = $mobileReSale->sale_id;
		}else{
			$id = $mobileReSale->id;
		}
	
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
                //pr($paymentArr);die;
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
	
		if($mobileReSale->refund_status != 1){
			$totalPrice+=$price;
		}
		
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
		if($mobileReSale->refund_status == 1){
			$totalRefundPrice+=$refundPrice;
		}
		if(array_key_exists($mobileReSale->user_id,$users)){
			$soldBy = $users[$mobileReSale->user_id];
		}else{
			$soldBy = '--';
		}
		
		if($mobileReSale->refund_status==1){
			$refundEntry[$id] = $mobileReSale->cost_price;
			//$refundSellEntry[$id] = $mobileReSale['MobileBlkReSale']['refund_price'];
           // continue;
?>
	<tr style="background:yellow;">
	<?php }else{?>
	<tr>
	<?php } ?>
		<td><?php echo $id; ?>&nbsp;</td>
		<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){?>
		<?php } ?>
		<td>
			<?php echo $this->Html->link($brands[$mobileReSale->brand_id], array('controller' => 'brands', 'action' => 'view', $mobileReSale->brand_id)); ?>
		</td>
		<td><?php
        if(array_key_exists($mobileReSale->mobile_model_id,$modelName)){
            echo h($modelName[$mobileReSale->mobile_model_id]);
        }
        ?>&nbsp;</td>
		<td><?php echo $this->Html->link($mobileReSale->imei,array('controller'=>'mobile_purchases','action'=>'mobile_transfer_logs', $mobileReSale->imei),array('alt'=>'Logs','title'=>'Logs')); ?>&nbsp;</td>
		<td><?php if($mobileReSale->refund_status==1){
				echo "";
			}else{
				echo $CURRENCY_TYPE.$price;
			}
			?>&nbsp;</td>
        <td><?php if($mobileReSale->refund_status==1){
					echo "-".$CURRENCY_TYPE.$mobileReSale->cost_price;
				}else{
					echo $CURRENCY_TYPE.$mobileReSale->cost_price;
				}
		?></td>
        <?php
		if($mobileReSale->refund_status != 1){
			$CostPrice[] = $mobileReSale->cost_price;
		}
        
         ?>
		 <td><?php
		 if($mobileReSale->refund_status==1){
			if(!empty($mobileReSale->refund_price)){
						$rprice = $mobileReSale->refund_price;
			}else{
				$rprice = "--";
			}
			echo $rprice;
		 }
					?></td>
		<td><?php echo h($mobileReSale->customer_fname); ?>&nbsp;</td>
		<td><?php
				if($mobileReSale->refund_status==1){
					echo date('d-m-y g:i',strtotime($mobileReSale->refund_date));//$this->Time->format('d-m-y g:i', $mobileReSale->refund_date,null,null);
				}
		?>&nbsp;</td>
		<?php
				if($mobileReSale->selling_date == "0000-00-00 00:00:00"||empty($mobileReSale->selling_date)){
					$created = $mobileReSale->created;
				}else{
					$created = $mobileReSale->selling_date;
				}
		?>
		<td><?php echo date('d-m-y g:i',strtotime($created));//$this->Time->format('d-m-y g:i', $created,null,null); ?>&nbsp;</td>
		<td>
			<?php $editUrl = "/img/16_edit_page.png";
			$viewUrl = "/img/text_preview.png";
			$cloneUrl = "/img/fileview_close_right.png";
			?>
			</td>
	</tr>
<?php endforeach;
	if($totalSearchedAmount > 0){
		$totalPrice = $totalSearchedAmount;
	}
	if(is_array($CostPrice)){
		$totalCostPrice = array_sum($CostPrice);
		if(is_array($refundEntry)){
			$refund = array_sum($refundEntry);
			$totalCostPrice = $totalCostPrice - $refund;
		}
	}else{
		$totalCostPrice = 0;
	}
	$totalProfit = 0;
	$show = " &#163;".$totalPrice." - "."&#163;".$totalCostPrice." - "."&#163;".$totalRefundPrice;
	$totalProfit = $totalPrice - $totalCostPrice - $totalRefundPrice;
	?>
		<tr>
			<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
			<td>&nbsp;</td>
			<?php } ?>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td><strong>Total:</strong></td>
			<td><strong><?php echo $CURRENCY_TYPE.$totalPrice;//echo $currency.$totalPrice; ?></strong></td>
			<td><strong><?php echo $CURRENCY_TYPE.$totalCostPrice;//echo $currency.$totalRefundPrice; ?></strong></td>			  <td><strong><?php echo $CURRENCY_TYPE.$totalRefundPrice;//echo $currency.$totalRefundPrice; ?></strong></td>	
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td><strong>Total Profilt : </strong></td>
			<td colspan=3><strong><?php echo $show." = ".$CURRENCY_TYPE.$totalProfit; ?></strong></td>
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