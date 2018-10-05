<?php
	use Cake\Core\Configure;
    use Cake\Core\Configure\Engine\PhpConfig;
    $currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<div class="mobileRepairs view">
<strong><?php echo __('<span style="color:red;font-size:20px">Mobile Repair</span> '.$this->Html->link('Generate receipt',array('action'=>'repair_receipt',$mobileRepair['id']))); ?></strong>
<?php
$repaircurrentStatus = $mobileRepair['status'];
$repairStatusOptions = $repairStatusUserOptions+$repairStatusTechnicianOptions;
unset($repairStatusOptions[8]);
unset($repairStatusOptions[6]);
unset($repairStatusOptions[18]);
echo $this->Form->create();?>
	<dl>
		<dt><?php echo __('Kiosk'); ?></dt>
		<dd>
            <?php //pr($mobileRepair);die; ?>
			<?php echo "<strong>".$mobileRepair['kiosk']['name']."</strong>"; ?>
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
			if(array_key_exists($problemType,$problemTypeOptions)){
				echo $counter." ".$problemTypeOptions[$problemType]."<br>";
			}else{
				echo $counter." Undefinded Problem-{$problemType}-<br>";
			}
				
			
			?>
			&nbsp;
		</dd>
		<?php }?>
		<dt><?php echo __('Fault Description'); ?></dt>
		<dd>
			<?php echo h($mobileRepair['description']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Received At'); ?></dt>
		<dd>
			<?php
			if(!empty($mobileRepair['received_at'])){
				echo date('jS M, Y g:i A',strtotime($mobileRepair['received_at']));
			}else{
				echo'--';
			}
			//$this->Time->format('jS M, Y g:i A', $mobileRepair['received_at'],null,null); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Delivered At'); ?></dt>
		<dd>
			<?php
			if(!empty($mobileRepair['delivered_at'])){
				echo date('jS M, Y g:i A',strtotime($mobileRepair['delivered_at']));
			}else{
				echo'--';
			}
			//$this->Time->format('jS M, Y g:i A', $mobileRepair['delivered_at'],null,null); ?>
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
			//pr($repairStatus);die;
			if($repairLog['status']==1){
				$currentStatus = "Refunded";
			}else{
				if(!empty($repairLog['repair_status'])){
					$currentStatus = $repairStatus[$repairLog['repair_status']];
				}else{
					$currentStatus = '';
				}
			}
			
				if(!empty($repairLog['comments'])){
					?>
					<tr>
						<td><?= $count; ?></td>
						<td>Comment Posted by <span style="color: crimson"><strong><?= $users[$repairLog['user_id']]; ?></strong></span> &#40;comment id:<?=$repairLog['comments'];?>&#41; on
						<?= date('jS M, Y g:i A',strtotime($repairLog['created']));//$this->Time->format('jS M, Y g:i:s A', $repairLog['created'],null,null); ?> for <span style="color: darkorange"><?= $kiosks[$repairLog['kiosk_id']]; ?></span></td>
					</tr>
					<?php }else{ ?>
				<tr>
					<td><?= $count; ?></td>
					<td>Last updated by <span style="color: crimson"><strong><?php if(array_key_exists($repairLog['user_id'],$users)){
						$users[$repairLog['user_id']];
						} ?></strong></span> on <?= date('jS M, Y g:i A',strtotime($repairLog['created']));//$this->Time->format('jS M, Y g:i A', $repairLog['created'],null,null); ?>, <span style="color: blue">Status: <?= $currentStatus; ?></span> for <span style="color: darkorange"><?= $kiosks[$repairLog['kiosk_id']]; ?></span></td>
				</tr>
				<?php }
			}?>
		</table>
	
		
		
			<?php
			$tableStr = "";
			foreach($comments as $sngComment){
                //pr($sngComment);die;
				$comment = $sngComment['brief_history'];
				$commentID = $sngComment['id'];
				$postedOn = $sngComment['modified'];
				$postedOn = date('jS M, Y g:i A',strtotime($postedOn));//$this->Time->format('jS M, Y h:i A',$postedOn,null,null); /*h:i A*/
				$postedBy = $sngComment['user']['username'];
				$userID = $sngComment['user']['id'];
				
				$tableStr.="";
				$tableStr.="<tr><td colspan='3'>$comment</td></tr>";
				$userLink = "<strong>".$postedBy."</strong>";
				$commentLink = $this->Html->link('Edit', array('controller' => 'comment_mobile_repairs','action' => 'edit', $commentID));
				$tableStr.="<tr><td>$commentLink</td><td>Posted By:$userLink</td><td>Posted On:$postedOn<br/></td></tr>";
				
			}
			if(empty($tableStr)){
				$tableStr = "<tr><td><span style='color:red'>No Record Found!!!</span></td></tr>";
			}
			echo "<table cellspacing='2' cellpadding='2'><tr><td colspan='3'><h3>Comments:</h3></td></tr>$tableStr</table>"
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
			<?php echo $this->Form->input('status',array('options'=>$repairStatusOptions,'default'=>$repaircurrentStatus,'label'=>false));
			echo $this->Form->input('id',array('type'=>'hidden','value'=>$mobileRepair['id']));
			echo $this->Form->input('kiosk_id',array('type'=>'hidden','value'=>$mobileRepair['kiosk_id']));
			echo $this->Form->input('estimated_cost',array('type'=>'hidden','value'=>$mobileRepair['estimated_cost']));
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
		<?php echo $this->Form->input('send', array('type' => 'checkbox',  'label' => array('text' => 'Send mail', 'style' => "font-weight: bold;margin-left: 10px;"),  'value' =>'1'));?>
	</dl>
	<?php
    echo $this->Form->Submit('Submit',array('name'=>'submit'));
    echo $this->Form->end();?>
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
