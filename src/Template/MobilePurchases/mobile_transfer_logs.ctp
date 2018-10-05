<div class="mobilePurchases index">
	<?php $i = 1;
	$imeiArr = array();
	if(empty($mobileTransferLogs)){?>
		<h4>Sorry, no logs found for this mobile.</h4>
	<?php }else{?>
		<?php foreach($mobileTransferLogs as $key=>$mobileTransferLog){
			 //pr($mobileTransferLog);die;
			$imeiArr[$mobileTransferLog->imei] = $mobileTransferLog->imei;
		}
		$imeiKey = array_keys($imeiArr);
		$imei = $imeiKey[0];
		?>
		<span style="color: red;font-size: 20px;"><?php echo __('Mobile Logs'); ?></span>
		(<strong>Brand:</strong><?php  echo   $brand['brand'];  ?>
		,<strong>Model:</strong><?php  echo   $mobileModel['model'];  ?>
		,<strong>IMEI:</strong><?php echo $imei;
		?>) <br/><br/>
		<p><?php foreach($mobileTransferLogs as $key=>$mobileTransferLog){?>
		<?php echo $i++.".\t\t\t";?>&nbsp;
        <?php //pr($mobileTransferLog);die; ?>
		Updated by <span style="color: red;"> <?php echo $userName[$mobileTransferLog->user_id];?></span> at <span style="color: red;"><?php echo $kioskName[$mobileTransferLog->kiosk_id];?></span> on <?php echo date('M jS, Y g:i:s A',strtotime($mobileTransferLog->created));//$this->Time->format('M jS, Y g:i:s A',$mobileTransferLog->created,null,null);?>
		<?php if(!empty($mobileTransferLog->mobile_purchase_reference)){?>and purchase reference: <?php echo $mobileTransferLog->mobile_purchase_reference.","; } ?> <?php if(!empty($mobileTransferLog->mobile_resale_id)){?>And sold under sale id:
		<?php
			if(!empty($customGrade)){
				echo $this->Html->link(__($mobileTransferLog->mobile_resale_id), array('controller'=>'mobile_blk_re_sales','action' => 'view',  $mobileTransferLog->mobile_resale_id),array('target'=>'_blank'));
			}else{
				echo $this->Html->link(__($mobileTransferLog->mobile_resale_id), array('controller'=>'mobile_re_sales','action' => 'view',  $mobileTransferLog->mobile_resale_id),array('target'=>'_blank'));
			}
		}?>
		
		
		And have purchase id:<?php echo $this->Html->link(__($mobileTransferLog->mobile_purchase_id), array('controller'=>'mobile_purchases','action' => 'view',  $mobileTransferLog->mobile_purchase_id),array('target'=>'_blank'));?>
			
		 <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Specification :
		[<strong>Grade:&nbsp;</strong><?php echo $mobileTransferLog->grade;?>,
		<strong>Type:&nbsp;</strong><?php echo $type[$mobileTransferLog->type];?>
		<?php if($mobileTransferLog->type==1){?>,
		<strong>Network:&nbsp;</strong><?php echo $networks[$mobileTransferLog->network_id];}?>] 
		
		 
		<?php
		//pr($receiving_status);
		?>
	  <span style="color: magenta;">Current status</span>: <?php if(!empty($mobileTransferLog->receiving_status)){ echo $receiving_status[$mobileTransferLog->receiving_status];}else{echo $status[$mobileTransferLog->status];}?>.<br/><br/> </p>
			
		<?php } ?>
		<p>
		<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
	<?php } ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('View Mobile Sale'), array('controller' => 'mobile_re_sales', 'action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('Buy Mobile Phones'), array('controller' => 'mobile_purchases', 'action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('Mobile Stock/Sell'), array('controller' => 'mobile_purchases', 'action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('Global Mobile Search'), array('controller' => 'mobile_purchases', 'action' => 'global_search')); ?></li>
	</ul>
</div>