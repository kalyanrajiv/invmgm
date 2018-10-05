<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php
//pr($this->request->query);die;
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
    //pr($kiosk);die;
?>
<div class="kioskOrders index">
	<strong><?php #print_r($kioskOrders);
	echo __('<span style="font-size: 17px;">Repair Details:</span>'); ?></strong>
	<?php if(!$repairDetail){
		echo "<h4>No result found!</h4>";
	}else{?>
	<h4>Sale from <?=$from_date;?> to <?=$to_date;?> for <?php if(array_key_exists($kiosk,$kiosks)){ ?><?=$kiosks[$kiosk];?><?php } ?></h4>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th>Repair Id</th>
		<th>Kiosk</th>
		<th>Brand</th>
		<th>Model</th>
		<th>Problem Type 1</th>
		<th>Problem Type 2</th>
		<th>Problem Type 3</th>
		<th>Cost Price</th>
		<th>Repair Date</th>
	</tr>
	</thead>
	<tbody>
		<?php
		$totalRepairCost = 0;
		$status = array('0'=>'No','1'=>'Yes');
		foreach($repairDetail as $key=>$repairInfo){
			$repairCost = 0;
//pr($repairInfo);die;
            $problemType1 = $problemType2 = $problemType3 = "--";
			$problemTypeArr = explode("|",$repairdata[$repairInfo['id']]['problem_type']);
			//pr($problemTypeArr);
			 
				if(array_key_exists('0',$problemTypeArr)){
					if(array_key_exists($problemTypeArr[0],$problemTypeOptions)){
						$problemType1 = $problemTypeOptions[$problemTypeArr[0]];
					}else{
						$problemType1 = "--";
					}
					
				}
				if(array_key_exists('1',$problemTypeArr)){
                    if(array_key_exists($problemTypeArr[1],$problemTypeOptions)){
                        $problemType2 = $problemTypeOptions[$problemTypeArr[1]];
                    }
				}
				if(array_key_exists('2',$problemTypeArr)){
					if(array_key_exists($problemTypeArr[2],$problemTypeOptions)){
						$problemType3 = $problemTypeOptions[$problemTypeArr[2]];
					}
				}
			 
			
			if($req_type == 'fixed'){
				$repairCost = $repairInfo['net_cost'];
			}else{
				//pr($repairCostArr);die;
				foreach($repairCostArr[$repairInfo['id']] as $cp => $costDetail){
					//pr($costDetail);die;
					$repairCost+=$costDetail['repair_cost'];
				}
			}
			$totalRepairCost+=$repairCost;
			?>
		<tr>
			<td><?php echo $this->Html->link($repairInfo['id'],array('controller'=>'mobile_repairs','action'=>'view',$repairInfo['id']),array('target'=>"_blank"));?></td>
			<td><?php echo $kiosk = $kiosks[$repairInfo['kiosk_id']]?></td>
			 
                        <td><?php #pr($repairInfo);
                        echo $brands[$repairInfo['brand_id']];
                                        //echo $brands[$repairAttr[$key]["MobileRepair"]["brand_id"]];?></td>
			<td><?php
            //pr($mobileModels);die;
			echo $mobileModels[$repairInfo['mobile_model_id']];?></td>
			<td><?=$problemType1;?></td>
			<td><?=$problemType2;?></td>
			<td><?=$problemType3;?></td>
			<td><?php echo $CURRENCY_TYPE.$repairCost;?></td>
			<td><?php echo date('d-m-Y h:i A',strtotime($repairIdsData[$repairInfo['id']]));//$this->Time->format('d-m-Y h:i A', $repairIdsData[$repairInfo['id']],null,null);?></td>
		</tr>
		<?php }?>
		<tr>
			<td colspan='7'><strong>Total</strong></td>
			<td><?php echo "<strong>".$CURRENCY_TYPE.$totalRepairCost."</strong>";?></td>
		</tr>
	</tbody>
	</table>
	<?php } ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Dispatched Products'), array('controller' => 'stock_transfer', 'action' => 'dispatched_products')); ?> </li>
		<li><?php echo $this->Html->link(__('Sale Summary'), array('controller' => 'stock_transfer', 'action' => 'summary_sale')); ?> </li>
	</ul>
</div>