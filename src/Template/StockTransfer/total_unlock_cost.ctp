<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
	$req_type = 'dynamic';
	if($this->request->query){
		$from_date = $this->request->query['from_date'];
		$to_date = $this->request->query['to_date'];
		$kiosk = $this->request->query['data']['kiosk'];
		if(array_key_exists('req_type',$this->request->query)){
			$req_type = $this->request->query['req_type'];
		}
	}
?>
<div class="kioskOrders index">
	<strong><?php 
	echo __('<span style="font-size: 17px;">Unlock Details:</span>'); ?></strong>
	<?php
    $totalUnlockCost = 0;
    if(!$unlockIdsDetail){
		echo "<h4>No result found!</h4>";
	}else{?>
	<h4>Sale from <?=$from_date;?> to <?=$to_date;?> for <?=$kiosks[$kiosk];?></h4>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th>Unlock Id</th>
		<th>Kiosk</th>
		<th>Brand</th>
		<th>Model</th>
		<th>Network</th>
		<th>Cost Price</th>
		<th>Created Date</th>
	</tr>
	</thead>
	<tbody>
		<?php  
		 //pr($unlockIdsDetail);
		 //pr($Unlockarr);
                 //pr($unlockDetail);
		//$totalUnlockCost = 0;
		$status = array('0'=>'No','1'=>'Yes');
		//pr($unlockIdsDetail);die;
        //pr($Unlockarr);die;
		foreach($unlockIdsDetail as $unlockID => $unlockInfo){
            //pr($unlockInfo);die;
			$costPrice = 0;
			 $id = $unlockInfo['id'];
                    if(count($unlockInfo)){
                        //pr($unlockInfo);die;
						//pr($unlockCostArr);
						if($req_type == 'fixed'){
							$costPrice = floatval($unlockInfo['net_cost']);
						}else{
                            if(array_key_exists('unlocking_cost',$unlockInfo)){
                                $costPrice = floatval($unlockInfo['unlocking_cost']);
                            }
						}
					}
                        
			$totalUnlockCost+=$costPrice;
                    
			?>
            
		<tr>
			<td><?php echo $this->Html->link($unlockInfo['id'],array('controller'=>'mobile_unlocks','action'=>'view',$unlockInfo['id']),array('target'=>'_blank'));?></td>
			<td><?=$kiosks[$kiosk];?></td>
			<td><?php  if(array_key_exists($unlockInfo['id'],$Unlockarr)){
                       echo  $brand = $brands[$Unlockarr[$unlockInfo['id']]['brand_id']];
                    }else{
                        $brand = '--';
                    }
				?></td>
			<td><?php
					if(array_key_exists($unlockInfo['id'],$Unlockarr)){
						 if(!empty($Unlockarr[$unlockInfo['id']]['mobile_model_id']) && array_key_exists($Unlockarr[$unlockInfo['id']]['mobile_model_id'],$mobileModels)){
							echo  $mobile_model = $mobileModels[$Unlockarr[$unlockInfo['id']]['mobile_model_id']];
						 }else{
							echo $mobile_model = '--';
						 }
					}else{
                        echo $mobile_model = '--';
                    }
					 ?></td>
			<td><?php
					if(array_key_exists($unlockInfo['id'],$Unlockarr)){
						if(!empty($Unlockarr[$unlockInfo['id']]['network_id']) && array_key_exists($Unlockarr[$unlockInfo['id']]['network_id'],$networks)){
							echo  $network = $networks[$Unlockarr[$unlockInfo['id']]['network_id']];
						 }else{
							echo $network = '--';
						 }
                    }else{
                        echo $network = '--';
                    }
					 ?></td>
			 
			<td><?php echo $CURRENCY_TYPE.$costPrice;?></td>
			<td><?php echo date('d-m-Y h:i A',strtotime($unlockInfo['created']));//$this->Time->format('d-m-Y h:i A', $unlockInfo['created'],null,null);?></td>
		</tr>
		<?php }
		}?>
		<tr>
			<td colspan=5><strong>Total</strong></td>
            
			<td><?php echo "<strong>".$CURRENCY_TYPE.$totalUnlockCost."</strong>";?></td>
		</tr>
	</tbody>
	</table>
	<?php #} ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Dispatched Products'), array('controller' => 'stock_transfer', 'action' => 'dispatched_products')); ?> </li>
		<li><?php echo $this->Html->link(__('Sale Summary'), array('controller' => 'stock_transfer', 'action' => 'summary_sale')); ?> </li>
	</ul>
</div>