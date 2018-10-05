<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
	$qryStrArr = array();
	$rootURL = "";//$this->html->url('/', true);
	foreach($requestParams as $requestKey => $requestVal){
		$qryStrArr[] = "{$requestKey}={$requestVal}";
	}
?>
<div class="mobileRepairs index">
	<?php //pr($userName);die;?>
	<h3>Repair report for <?=ucfirst($userName[$user]);?>&nbsp;<a href="<?php echo $rootURL;?>export_user_repairs/?<?php echo implode('&',$qryStrArr);?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png', array('fullBase' => true));?></a></h3>
	<table>
		<tr>
			<th><?php echo $this->Paginator->sort('created','Dispatch Date'); ?></th>
			<th>RepairId</th>
			<th>Cost Price</th>
			<th>Selling Price</th>
			<th>Refund</th>
			
			<th><?php echo $this->Paginator->sort('kiosk_id'); ?></th>
			<th>Model</th>
			<th>Problem 1</th>
			<th>Problem 2</th>
			<th>Problem 3</th>
			<th>Parts used</th>
			<th><?php echo $this->Paginator->sort('repair_status'); ?></th>
		</tr>
	<?php
	//pr($repairData);
	$check = array();
	foreach($repairData as $key => $repairInfo){
		$cost_price = 0;
		//if(in_array($repairInfo->mobile_repair_id,$check)){
		//	//continue;
		//}else{
		//	$check[] = $repairInfo->mobile_repair_id;
		//}
		$part_used = 0;
		if(array_key_exists($repairInfo->mobile_repair_id,$finalPart)){
			$part_used = count($finalPart[$repairInfo->mobile_repair_id]);
		}
		//$check = array();
		$problemType1 = $problemType2 = $problemType3 = "--";
                $problemTypeArr = array();
                if(array_key_exists($user, $repairDetail) &&
                is_array($repairDetail[$user])){
					//echo'hi';die;
                    $repair_detail = array_values($repairDetail);
					//pr($repair_detail);die;
                    foreach($repair_detail[0] as $k => $repairDet){
                        if($repairInfo->mobile_repair_id == $repairDet['id']){
							//echo'hi';die;
                            $problemTypeArr = explode("|",$repairDet["problem_type"]);
                            if(array_key_exists('0',$problemTypeArr)){
                                if(!empty($problemTypeArr[0])){
                                        $problemType1 = $problemTypeOptions[$problemTypeArr[0]];
                                }
                                else{
                                        $problemType1 = "";
                                }
                            }
                            if(array_key_exists('1',$problemTypeArr)){
                                //pr($problemTypeArr[1]);
                                //pr($problemTypeOptions);die;
                                if(array_key_exists($problemTypeArr[1],$problemTypeOptions)){
                                    $problemType2 = $problemTypeOptions[$problemTypeArr[1]];
                                }
                            }
                            if(array_key_exists('2',$problemTypeArr)){
                                if(array_key_exists($problemTypeArr[2],$problemTypeOptions)){
                                    $problemType3 = $problemTypeOptions[$problemTypeArr[2]];
                                }
                            }
                            
                            $modelName = $mobileModels[$repairDet["mobile_model_id"]];
							$cost_price = $repairDet['net_cost'];
								if(array_key_exists($repairDet['id'],$final)){
									$sale_price =  $final[$repairDet['id']]['amount'];
									$refundAmount = $final[$repairDet['id']]['refund_amount'];
								}else{
									$sale_price = 0;
									$refundAmount = 0;
								}
							
                        }/*else{
							//echo'bye';die;
							$sale_price = 0;
							$refundAmount = 0;
							$modelName = 'id = '.$repairInfo->mobile_repair_id;
						}*/
                    }
                }else{
                    $modelName = 'id = '.$repairInfo->mobile_repair_id;
                    //$modelName = '--';
                }
                
		
                
                if(!empty($repairInfo->service_center_id) && array_key_exists($repairInfo->service_center_id,$kiosks)){
                    $serviceCenter = $kiosks[$repairInfo['service_center_id']];
                }else{
                    $serviceCenter = '--';
                }
		?>
		<tr>
			<td><?=date('d-m-Y g:i A',strtotime($repairInfo->created));//$this->Time->format('d-m-Y g:i A', $repairInfo->created,null,null);?></td>
			<td><?=$this->Html->link($repairInfo->mobile_repair_id,array('controller' => 'mobile_repairs',
																		 'action' => 'view', $repairInfo->mobile_repair_id),array('target' => '_blank'));?></td>
			<td><?php
			if(in_array($repairInfo->mobile_repair_id,$check)){
				echo 0;
				
			}else{
				echo $cost_price;
				
			}
			?></td>
			<td><?php
			if(in_array($repairInfo->mobile_repair_id,$check)){
				echo 0;
				
			}else{
				echo $sale_price;
				
			}
			
			
			?></td>
			<td>
				<?php
				if($refundAmount == ""){
					echo 0;
				}else{
					if(in_array($repairInfo->mobile_repair_id,$check)){
						echo 0;
						
					}else{
						echo $refundAmount;
						
					}
					
				}
				if(in_array($repairInfo->mobile_repair_id,$check)){
					//continue;
				}else{
					$check[] = $repairInfo->mobile_repair_id;
				}
				?>
			</td>
			
			<td><?=$kiosks[$repairInfo->kiosk_id];?></td>
			<td><?=$modelName;?></td>
			<td><?=$problemType1;?></td>
			<td><?=$problemType2;?></td>
			<td><?=$problemType3;?></td>
			<td><?php
				echo $part_used;
			?></td>
			<td><?=$repairStatusTechnicianOptions[$repairInfo->repair_status];?></td>
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
		<li><?php echo $this->Html->link(__('Repair Report'), array('action' => 'repair_technician_report')); ?></li>
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