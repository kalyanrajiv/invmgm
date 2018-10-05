<div class="mobileUnlocks view">
<?php
$currentStatus = $mobileUnlock['status'];
$estimatedCost = $mobileUnlock['estimated_cost'];
echo $this->Form->create();
//pr($mobileUnlock);
?>
<strong><?php
//pr($mobileUnlock);die;
	$id  = $mobileUnlock['id'];
	echo __('<span style="color:red;font-size:20px">Mobile Unlock</span> '); echo "<span style='color:blue;font-size:20px'>(".$mobileUnlock['id'].")</span> ";
	if($mobileUnlock['status'] == UNLOCKING_FAILED_CONFIRMATION_PASSED ||
	   $mobileUnlock['status'] == DELIVERED_UNLOCKING_FAILED_AT_CENTER ||
	   $mobileUnlock['status'] == DELIVERED_UNLOCKING_FAILED_AT_KIOSK){
		echo "";
	}else{
	 echo "<span style='margin-right: 10px;'>";
			echo  $this->Html->link('Thermal receipt',array('controller' => 'prints','action'=>'unlock',$mobileUnlock['id']));
			echo "</span>";
		echo $this->Html->link('Generate receipt',array('action'=>'unlock_receipt',$mobileUnlock['id']));
	}?>
</strong><br>
<?php $unlockOptions = $unlockStatusUserOptions+$unlockStatusTechnicianOptions;
	unset($unlockOptions[REFUND_RAISED]);
	unset($unlockOptions[UNLOCK_UNDER_PROCESS]);
	unset($unlockOptions[WAITING_FOR_DISPATCH_UNLOCKED]);
?>
	<dl>
		<dt><?php echo __('Kiosk'); ?></dt>
		<dd>
			<?php echo "<strong>".$mobileUnlock['kiosk']['name']."</strong>"; ?>
			&nbsp;
		</dd>
	</dl>
<h4><?php echo __('</br><strong>Customer Detail</strong>'); ?></h4>
	<dl>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($mobileUnlock['customer_fname']); ?> <?php echo h($mobileUnlock['customer_lname']); ?>
			&nbsp;
		</dd>		
		<dt><?php echo __('Customer Email'); ?></dt>
		<dd>
			<?php echo h($mobileUnlock['customer_email']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Mobile/Phone'); ?></dt>
		<dd>
			<?php echo h($mobileUnlock['customer_contact']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Customer Address'); ?></dt>
		<dd>
			&nbsp;&nbsp;<?php echo h($mobileUnlock['customer_address_1']); ?>
			&nbsp;
		</dd>
		<dt>&nbsp;&nbsp;&nbsp;&nbsp;</dt>
		<dd>
			<?php echo h($mobileUnlock['customer_address_2']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Postal Code'); ?></dt>
		<dd>
			<?php echo h($mobileUnlock['zip']); ?>
			&nbsp;
		</dd>
	</dl>
<h4><?php echo __('</br><strong>Mobile Detail</strong>'); ?></h4>
	<dl>
		<dt><?php echo __('Brand'); ?></dt>
		<dd>
			<?php echo $this->Html->link($mobileUnlock['brand']['brand'], array('controller' => 'brands', 'action' => 'view', $mobileUnlock['brand']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Mobile Model'); ?></dt>
		<dd>
			<?php echo $this->Html->link($mobileUnlock['mobile_model']['model'], array('controller' => 'mobile_models', 'action' => 'view', $mobileUnlock['mobile_model']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('IMEI'); ?></dt>
		<dd>
			<?php echo h($mobileUnlock['imei']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Network'); ?></dt>
		<dd>
			<?php echo $this->Html->link($mobileUnlock['network']['name'], array('controller' => 'networks', 'action' => 'view', $mobileUnlock['network']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Unlock Description'); ?></dt>
		<dd>
			<?php echo h($mobileUnlock['description']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Received At'); ?></dt>
		<dd>
			<?php echo $this->Time->format('M jS, Y g:i A', $mobileUnlock['received_at'],null,null); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Delivered At'); ?></dt>
		<dd>
			<?php echo $this->Time->format('M jS, Y g:i A', $mobileUnlock['delivered_at'],null,null); ?>
			&nbsp;
		</dd>
		
	</dl>
<h4><?php echo __('</br><strong>Unlock Logs</strong>'); ?></h4>

	<?php $unlockStatus = $unlockStatusUserOptions+$unlockStatusTechnicianOptions;
		?>
		
		<table>
			<?php $count = 0;
			foreach($unlockLogs as $id => $unlockLog){
				$count++;
				if(!empty($unlockLog['comments'])){?>
				<tr>
					<td><?= $count; ?></td>
					<td>Comment Posted by <span style="color: crimson"><strong><?= $users[$unlockLog['user_id']]; ?></strong></span> &#40;comment id:<?=$unlockLog['comments'];?>&#41; on <?= $this->Time->format('M jS, Y g:i A', $unlockLog['created'],null,null); ?> for <span style="color: darkorange"><?= $kiosks[$unlockLog['kiosk_id']]; ?></span></td>
				</tr>
				<?php }else{ ?>
				<tr>
					<td><?= $count; ?></td>
					<td>Last updated by <span style="color: crimson"><strong><?php
					if(array_key_exists($unlockLog['user_id'],$users)){
					echo $users[$unlockLog['user_id']];
					}
					?></strong></span> on <?= date('M jS, Y g:i:s A',strtotime($unlockLog['created'])); ?>, <span style="color: blue">Status: <?= $unlockStatus[$unlockLog['unlock_status']]; ?></span> for <span style="color: darkorange"><?= $kiosks[$unlockLog['kiosk_id']]; ?></span></td>
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
				$postedOn = $this->Time->format('M jS, Y h:i A',$postedOn,null,null); /*h:i A*/
				$postedBy = $sngComment['user']['username'];
				$userID = $sngComment['user']['id'];
				
				$tableStr.="";
				$tableStr.="<tr><td colspan='3'>$comment</td></tr>";
				$userLink = $this->Html->link($postedBy, array('controller' => 'users','action' => 'view', $sngComment['user']['id']));
				$commentLink = $this->Html->link('Edit', array('controller' => 'comment_mobile_unlocks','action' => 'edit', $commentID));
				$tableStr.="<tr><td>$commentLink</td><td>Posted By:$userLink</td><td>Posted On:$postedOn<br/></td></tr>";
				
			}
			if(empty($tableStr)){
				$tableStr = "<tr><td><span style='color:red'>No Record Found!!!</span></td></tr>";
			}
			echo "<table cellspacing='2' cellpadding='2'><tr><td colspan='3'><h3>Comments:</h3></td></tr>$tableStr</table>"
		?>
			&nbsp;
	<dl>
		<dt><?php echo __('Estimated Cost'); ?></dt>
		<dd>
			<?php echo h($mobileUnlock['estimated_cost']); ?>
			&nbsp;
		</dd>
		
		<dt><?php echo __('Estimated Days'); ?></dt>
		<dd>
			<?php echo $unlockingDays; ?>
			&nbsp;
		</dd>
		
		<dt><?php echo __('Current Status'); ?></dt>
		<dd style="width: 455px;">
		<?php
			echo $this->Form->input('status',array('options' => $unlockOptions,'default' => $currentStatus,'name' => 'MobileUnlock[status]','label'=>false));
			echo $this->Form->input('id',array('type'=>'hidden','value'=>$mobileUnlock['id'],'name' => 'MobileUnlock[id]'));
			echo $this->Form->input('estimated_cost',array('type'=>'hidden','value'=>$mobileUnlock['estimated_cost'],'name' => 'MobileUnlock[estimated_cost]'));
			echo $this->Form->input('kiosk_id',array('type'=>'hidden','value'=>$mobileUnlock['kiosk_id'],'name' => 'MobileUnlock[kiosk_id]'));
			?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo $this->Time->format('M jS, Y g:i A', $mobileUnlock['created'],null,null); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo $this->Time->format('M jS, Y g:i A', $mobileUnlock['modified'],null,null); ?>
			&nbsp;
		</dd>
		<?php echo $this->Form->input('send', array('type' => 'checkbox', 'name' => 'MobileUnlock[send]', 'label' => array('text' => 'Send mail', 'style' => "font-weight: bold;margin-left: 10px;"),  'value' =>'1'));?>
	</dl>
	<?php
	echo $this->Form->submit('Submit',array('name'=>'submit'));
	echo $this->Form->end();?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Mobile Unlock'), array('action' => 'edit', $mobileUnlock['id'])); ?> </li>
		<li><?php #echo $this->Form->postLink(__('Delete Mobile Unlock'), array('action' => 'delete', $mobileUnlock['MobileUnlock']['id']), array(), __('Are you sure you want to delete # %s?', $mobileUnlock['MobileUnlock']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Mobile Unlocks'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Mobile Unlock'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->element('unlock_navigation'); ?></li>		
		<li><?php # echo $this->Html->link(__('List Kiosks'), array('controller' => 'kiosks', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php # echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
		<li><?php # echo $this->Html->link(__('List Networks'), array('controller' => 'networks', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Network'), array('controller' => 'networks', 'action' => 'add')); ?> </li>
	</ul>
</div>
