<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php
//pr($mobileRepair);
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'places' => 4,'escape' => false));
?>
<div class="mobileRepairs view">
<strong>
	<?php
    //pr($mobileRepair);die;
	if(!empty($mobileRepair['function_condition'])){
		$function_condition = explode("|",$mobileRepair['function_condition']);
		if(is_array($function_condition)){
			//count($function_condition) > 1
			$functionConditionArray = array();
			foreach($function_condition as $key => $functionCon){
				if(array_key_exists($functionCon, $functionConditions)){
					$functionConditionArray[] = $functionConditions[$functionCon];
				}
				
			}
			$functionConditionStr = implode(', ',$functionConditionArray);
		}else{
			$functionConditionStr = '';
		}
	}else{
		$functionConditionStr = '';
	}
	
	if(!empty($mobileRepair['mobile_condition'])){
		$mobile_condition = explode("|",$mobileRepair['mobile_condition']);
		if(is_array($mobile_condition)){
			//count($mobile_condition) > 1
			$mobileConditionArray = array();
			foreach($mobile_condition as $key => $mobileCon){
				if($mobileCon == 1000){
					$mobileConditionArray[] = $mobileRepair['mobile_condition_remark'];
				}else{
					if(array_key_exists($mobileCon, $mobileConditions)){
						$mobileConditionArray[] = $mobileConditions[$mobileCon];
					}
				}
			}
			$mobileConditionStr = implode(', ',$mobileConditionArray);
		}else{
			$mobileConditionStr = '';
		}
	}else{
		$mobileConditionStr = '';
	}
	
	  $id = $mobileRepair['id'] ;
		echo __('<span style="color:red;font-size:20px">Mobile Repair</span>') ;
		if($mobileRepair['status'] == DELIVERED_UNREPAIRED_BY_KIOSK ||
		   $mobileRepair['status'] == DELIVERED_UNREPAIRED_BY_TECHNICIAN){
			echo "";
		}else{
			echo "<span style='margin-right: 10px;'>";
			echo  $this->Html->link('Thermal receipt',array('controller' => 'prints','action'=>'repair',$mobileRepair['id']));
			echo "</span>";
			echo  $this->Html->link('Generate receipt',array('action'=>'repair_receipt',$mobileRepair['id']));
		}
	?>
			</strong>
<br>
<?php $repairStatusOptions = $repairStatusUserOptions+$repairStatusTechnicianOptions;?>
<?PHP
 
if($mobileRepair['internal_repair']==1 ){?>
    <span><i style="color: blue;">**Internal Booking</i>   </span>
	 <?php
}else{ 
 echo "Normal Repair";
  } ?>
 
	<dl>
		<dt><?php echo __('Kiosk'); ?></dt>
		<dd>
			<?php echo "<strong>".$mobileRepair['kiosk']['name']."</strong>"; ?>
			&nbsp;
		</dd>
		<dt><?php echo __(' Repair Id'); ?></dt>
		<dd>
			<?php echo h($mobileRepair['id']); ?>
			&nbsp;
		</dd>
	</dl>
<h4><?php echo __('</br><strong>Customer Detail</strong>'); ?></h4>
	<dl>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($mobileRepair['customer_fname']); ?> <?php echo h($mobileRepair['customer_lname']); ?>
			&nbsp;
		</dd>		
		<dt><?php echo __('Customer Email'); ?></dt>
		<dd>
			<?php echo h($mobileRepair['customer_email']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Mobile/Phone'); ?></dt>
		<dd>
			<?php echo h($mobileRepair['customer_contact']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Customer Address'); ?></dt>
		<dd>
			&nbsp;&nbsp;<?php echo $mobileRepair['customer_address_1']; ?>
			&nbsp;
		</dd>
		<dt>&nbsp;&nbsp;&nbsp;&nbsp;</dt>
		<dd>
			<?php echo h($mobileRepair['customer_address_2']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Postal Code'); ?></dt>
		<dd>
			<?php echo h($mobileRepair['zip']); ?>
			&nbsp;
		</dd>
	</dl>
<h4><?php echo __('</br><strong>Mobile Detail</strong>'); ?></h4>
		<?php
		$problemTypeArr = explode("|",$mobileRepair['problem_type']);
		?>
	<dl>
		<dt><?php echo __('Brand'); ?></dt>
		<dd>
			<?php echo $brands[$mobileRepair['brand_id']]; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Mobile Model'); ?></dt>
		<dd>
			<?php echo $mobileModels[$mobileRepair['mobile_model_id']]; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('IMEI'); ?></dt>
		<dd>
			<?php echo h($mobileRepair['imei']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Problem Type'); ?></dt>
		
		<?php $counter = 0;
		foreach($problemTypeArr as $key => $problemType){?>
		<dd>
			<?php
			$counter++;  
			echo $counter." ".$problemTypeOptions[$problemType]."<br>";
				
			?>
			&nbsp;
		</dd>
		<?php }?>
		<dt><?php echo __('Fault Description'); ?></dt>
		<dd>
			<?php echo h($mobileRepair['description']); ?>
			&nbsp;
		</dd>
		<?php if(!empty($mobileConditionStr)){?>
		<dt style="width: 131px;"><?php echo __('Phone"s Condition'); ?></dt>
		<dd style="margin-left: 145px;">
			<?php echo $mobileConditionStr; ?>
			&nbsp;
		</dd>
		<?php } ?>
		
		<dt><?php echo __('Phone Password'); ?></dt>
		<dd>
			<?php echo $mobileRepair['phone_password']; ?>
			&nbsp;
		</dd>
		
		
		<?php if(!empty($functionConditionStr)){?>
		<dt style="width: 159px;"><?php echo __('Phone"s Function Test'); ?></dt>
		<dd style="margin-left: 186px;">
			<?php echo $functionConditionStr; ?>
			&nbsp;
		</dd>
		<?php } ?>
		
		<dt><?php echo __('Received At'); ?></dt>
		<dd>
			<?php
            if(empty($mobileRepair['received_at'])){
                echo "--";
            }else{
             echo date('jS M, Y g:i A',strtotime($mobileRepair['received_at']));//$this->Time->format('jS M, Y g:i A', $mobileRepair['received_at'],null,null);   
            }
             ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Delivered At'); ?></dt>
		<dd>
			<?php
            if(empty($mobileRepair['delivered_at'])){
                echo "--";
            }else{
                echo date('jS M, Y g:i A',strtotime($mobileRepair['delivered_at']));//$this->Time->format('jS M, Y g:i A', $mobileRepair['delivered_at'],null,null);   
            }
             ?>
			&nbsp;
		</dd>
		
	</dl>
<h4><?php echo __('</br><strong>Repair Logs</strong>'); ?></h4>
		<table>
			<?php $count = 0;
            //pr($repairLogs);die;
			foreach($repairLogs as $id => $repairLog){
                //pr($repairLog);die;
				$count++;
				$repairStatus = $repairStatusUserOptions+$repairStatusTechnicianOptions;
				$repairStatus[-1] = 'unambiguous';
				if($repairLog['status']==1){
					$currentStatus = "Refunded";
				}else{
                  if(array_key_exists($repairLog['repair_status'],$repairStatus)){
					$currentStatus = $repairStatus[$repairLog['repair_status']];
                  }else{
                    $currentStatus ='';
                  }
				}
			
				if(!empty($repairLog['comments'])){
					?>
					<tr>
						<td><?= $count; ?></td>
						<td>Comment Posted by <span style="color: crimson"><strong><?php
						if(array_key_exists($repairLog['user_id'],$users)){
							echo $users[$repairLog['user_id']];
						}
						?></strong></span> &#40;comment id:<?=$repairLog['comments'];?>&#41; on
						<?= date('jS M, Y g:i A',strtotime($repairLog['created']));//$this->Time->format('jS M, Y g:i:s A', $repairLog['created'],null,null); ?> for <span style="color: darkorange"><?= $kiosks[$repairLog['kiosk_id']]; ?></span></td>
					</tr>
					<?php }else{ ?>
				<tr>
					<td><?= $count; ?></td>
					<td>Last updated by <span style="color: crimson"><strong><?php if(array_key_exists($repairLog['user_id'],$users)){ ?><?= $users[$repairLog['user_id']]; ?><?php } ?></strong></span> on <?= date('jS M, Y g:i A',strtotime($repairLog['created']));//$this->Time->format('jS M, Y g:i:s A', $repairLog['created'],null,null); ?>, <span style="color: blue">Status: <?= $currentStatus; ?></span> for <span style="color: darkorange"><?= $kiosks[$repairLog['kiosk_id']]; ?></span></td>
				</tr>
				<?php }
			}?>
		</table>
	
		
		
			<?php
			$tableStr = "";
			$i= 1;
            //pr($comments);die;
			foreach($comments as $sngComment){
                //pr($sngComment);die;
				$comment = $sngComment['brief_history'];
				$commentID = $sngComment['id'];
				$postedOn = $sngComment['modified'];
				$postedOn = date('jS M, Y g:i A',strtotime($postedOn));//$this->Time->format('jS M, Y h:i A',$postedOn,null,null); /*h:i A*/
				$postedBy = $sngComment['user']['username'];
				$userID = $sngComment['user']['id'];
				$truncatedcomment  = \Cake\Utility\Text::truncate(
                                                                    $comment,120,
                                                                    [
                                                                        'ellipsis' => '...',
                                                                        'exact' => false, 
                                                                    ]
                                                                );
				$tableStr.="";
				//$tableStr.="<tr><td style='width: 2px'>".$i++."</td>";
				if(strlen($comment)>120){
					
					$userLink = "<span style='color: crimson'><strong>".$postedBy."</strong></span>";
					$commentLink = $this->Html->link('Edit', array('controller' => 'comment_mobile_repairs','action' => 'edit', $commentID));
					$tableStr.="<td style='width: 90px'>$postedOn<br/></td>
					<td>$userLink</td>
					<td colspan='3'><a href = \"\" title = \"$comment\" alt = \"$comment\">$truncatedcomment</a></td>
					<td style='width: 1px;'>$commentLink</td></tr>";
				}else{
					
					$userLink = "<span style='color: crimson'><strong>".$postedBy."</strong></span>";
					$commentLink = $this->Html->link('Edit', array('controller' => 'comment_mobile_repairs','action' => 'edit', $commentID));
					$tableStr.="<td style='width: 60px'>$postedOn<br/></td>
					<td>$userLink</td>
					
					<td colspan='3'>$comment</td>
					<td style='width: 1px;'>$commentLink</td></tr>";
				}
			  	
			}
			if(empty($tableStr)){
				$tableStr = "<tr><td><span style='color:red'>No Record Found!!!</span></td></tr>";
			}
			echo "<table cellspacing='0' cellpadding='0'><tr><td colspan='0'><h3>Comments:</h3></td></tr>$tableStr</table>"
			?>
			&nbsp;
		
		<?php
		$sum = 0;
		$estimatedCostArr = explode('|',$mobileRepair['estimated_cost']);
		foreach($estimatedCostArr as $ki => $estimatedCost){			
			$sum += $estimatedCost;
		}
		?>
		<dl>
		<dt><?php echo __('Estimated Cost'); ?></dt>
		<dd>
			<?php echo $CURRENCY_TYPE.$sum;  ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Estimated Days'); ?></dt>
		<dd>
			<?php echo $maxRepairDays; ?>
			&nbsp;
		</dd>
		
		<dt><?php echo __('Current Status'); ?></dt>
		<dd>
			<?php
            if(array_key_exists($mobileRepair['status'],$repairStatusOptions)){
                echo $repairStatusOptions[$mobileRepair['status']];
            }
            ?>
			&nbsp;
		</dd>
		<?php if(!empty($viewRepairParts)){?>
		<strong>Parts Repaired:</strong>
		<table>
				<tr>					
					<th>Product Id</th>
					<th>Product</th>
					<th>Date</th>
				</tr>
				<?php
				$counter = 0;
				foreach($viewRepairParts as $key => $viewRepairPart){
                    //pr($viewRepairPart);die;
					if(array_key_exists($viewRepairPart['product_id'],$products)){
						$partName = $products[$viewRepairPart['product_id']];
					}else{
						$partName = '--';
					}
					$counter++;?>
				<tr>					
					<td style="width:20%"><?=$viewRepairPart['product_id'] ;?></td>
					<td><?= $partName;?></td>
					<td><?=date('jS M, Y g:i A',strtotime($viewRepairPart['created']));//$this->Time->format('jS M, Y g:i A',$viewRepairPart['created'],null,null) ;?></td>
				</tr>
				<?php } ?>
		
			</table>
		<?php }?>		
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($mobileRepair['created']));//$this->Time->format('jS M, Y g:i A', $mobileRepair['created'],null,null); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($mobileRepair['modified']));//$this->Time->format('jS M, Y g:i A', $mobileRepair['modified'],null,null); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Mobile Repair'), array('action' => 'edit', $mobileRepair['id'])); ?> </li>
		<li><?php #echo $this->Form->postLink(__('Delete Mobile Repair'), array('action' => 'delete', $mobileRepair['MobileRepair']['id']), array(), __('Are you sure you want to delete # %s?', $mobileRepair['MobileRepair']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Mobile Repairs'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Mobile Repair'), array('action' => 'add')); ?> </li>
		<li><?php #echo $this->Html->link(__('List Kiosks'), array('controller' => 'kiosks', 'action' => 'index')); ?> </li>
		<li><?php #echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php echo $this->element('repair_navigation'); ?></li>
	</ul>
</div>
