<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php
	$qryStrArr = array();
	$rootURL = "";//$this->html->url('/', true);
	foreach($requestParams as $requestKey => $requestVal){
		$qryStrArr[] = "{$requestKey}={$requestVal}";
	}
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<div class="mobileUnlock index">
	<?php #pr($repairData);?>
	<h3>Unlock report for <?=ucfirst($userName[$user]);?></h3>&nbsp;<a href="<?php echo $rootURL;?>export_user_unlock/?<?php echo implode('&',$qryStrArr);?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png', array('fullBase' => true));?></a></h3>
	<table>
		<tr>
			
			<th><?php echo $this->Paginator->sort('created','Dispatch Date'); ?></th>
			<th>UnlockId</th>
			<th>Cost</th>
			<th>Selling Price</th>
			<th>refund</th>
			<th><?php echo $this->Paginator->sort('kiosk_id'); ?></th>
			<th>Model</th>
			<th>Network</th>
			<th><?php echo $this->Paginator->sort('unlock_status'); ?></th>
		</tr>
	<?php
	//pr($unlockDetail);
    //pr($sale_Arr);die;
        foreach($unlockData as $key => $unlockInfo){
            //pr($unlockInfo);die;
			$amount = $refundAmt =0;
            //pr($sale_Arr);die;
            if(array_key_exists($unlockInfo->mobile_unlock_id,$sale_Arr)){
                $amount = $sale_Arr[$unlockInfo->mobile_unlock_id]['amount'];
                $refundAmt = $sale_Arr[$unlockInfo->mobile_unlock_id]['refund_amount'];
				if($refundAmt > 0){
					$refundAmt = (-1)*$refundAmt;
				}
            }
            if(array_key_exists($user, $unlockDetail) &&
                is_array($unlockDetail[$user])){
                $unlock_detail = array_values($unlockDetail);
                //pr($unlock_detail);die;
                foreach($unlock_detail[0] as $k => $unlockDet){
                    //pr($unlockDet);die;
                    if($unlockDet['id'] == $unlockInfo->mobile_unlock_id){
                        $model = $mobileModels[$unlockDet['mobile_model_id']];
                        $network = $networks[$unlockDet['network_id']];
						$cost = $unlockDet['net_cost'];
                    }/*else{
                        $model = '--';
                        $network = '--';
                        $cost = '--';
                    }*/
                }
            }else{
                $model = '--';
                $network = '--';
                $cost = '--';
            }
            if(!empty($unlockInfo->unlock_center_id) && array_key_exists($unlockInfo->unlock_center_id,$kiosks)){
                $unlockCenter = $kiosks[$unlockInfo->unlock_center_id];
            }else{
                $unlockCenter = '--';
            }
		 ?>
		<tr>
			
			<td><?=date('d-m-Y g:i A',strtotime($unlockInfo->created));//$this->Time->format('d-m-Y g:i A',$unlockInfo->created,null,null);?></td>
			<td><?=$this->Html->link($unlockInfo->mobile_unlock_id,array('controller' => 'MobileUnlocks', 'action' => 'view', $unlockInfo->mobile_unlock_id),array('target' => '_blank'));?></td>
			<td><?php echo $cost;?></td>
			<td> <?php echo $amount;?></td>
			<td><?php if(!empty($refundAmt)){
				echo $refundAmt;
				}?></td>
			<td><?=$kiosks[$unlockInfo->kiosk_id];?></td>
			<td><?=$model;?></td>
			<td><?=$network?></td>
			<td><?=$unlockStatusTechnicianOptions[$unlockInfo->unlock_status];?></td>
		</tr>
	<?php } ?>
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
		<li><?php echo $this->Html->link(__('Unlock Report'), array('action' => 'unlock_technician_report')); ?></li>
		<li><?php #echo $this->Html->link(__('List Kiosks'), array('controller' => 'kiosks', 'action' => 'index')); ?> </li>
		<li><?php #echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>			
		<li><?php #echo $this->element('repair_navigation'); ?></li>		
	</ul>
</div>
<script>
	jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
	});
</script>