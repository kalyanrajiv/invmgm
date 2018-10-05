<div class="mobileUnlocks view">
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
<?php $unlockOptions = $unlockStatusUserOptions+$unlockStatusTechnicianOptions;	?>
<?PHP
// pr($mobileUnlock);
 if($mobileUnlock['internal_unlock']==1 ){?>
    <span><i style="color: blue;">**Internal Booking</i>   </span>
	 <?php
 }else{ 
  echo "Normal Unlock";
   } ?>
	<dl>
		<dt><?php echo __('Kiosk'); ?></dt>
		<dd>
			<?php echo "<strong>".$mobileUnlock['kiosk']['name']."</strong>"; ?>
			&nbsp;
		</dd>
		<dt><?php echo __(' Unlock Id'); ?></dt>
		<dd>
			<?php echo h($mobileUnlock['id']); ?>
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
		
		<dt><?php echo __('Received At'); ?></dt>
		<dd>
			<?php
			if(!empty($mobileUnlock['received_at'])){
			 echo date('M jS, Y g:i A',strtotime($mobileUnlock['received_at']));
			}else{
			 echo'--';
			}
			//$this->Time->format('M jS, Y g:i A', $mobileUnlock['received_at'],null,null); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Delivered At'); ?></dt>
		<dd>
			<?php
			if(!empty($mobileUnlock['delivered_at'])){
			 echo date('M jS, Y g:i A',strtotime($mobileUnlock['delivered_at']));
			}else{
			 echo'--';
			}
			//$this->Time->format('M jS, Y g:i A', $mobileUnlock['delivered_at'],null,null); ?>
			&nbsp;
		</dd>
		
	</dl>
	<table style = 'width:900px;'>
	 <tr>
	   <td width='200'><strong><?php echo __('Unlock Code'); ?></strong></td>
	   <td><?php
       if(isset($mobileUnlock['code'])){
            echo h($mobileUnlock['code']); 
       }
        ?></td>
		 </tr>
		 <tr><td><strong><?php echo __('Unlock Code Instructions'); ?></strong></td>
		 <td><?php echo h($mobileUnlock['unlock_code_instructions']); ?></td>
		 </tr>
		 <tr><td><strong><?php echo __('Unlock Description'); ?></strong></td>
		 <td><?php echo h($mobileUnlock['description']); ?></td>
		 </tr>
		 
		</table>
<h4><?php echo __('</br><strong>Unlock Logs</strong>'); ?></h4>

	<?php $unlockStatus = $unlockStatusUserOptions+$unlockStatusTechnicianOptions;
		?>
		
		<table>
			<?php $count = 0;
			foreach($unlockLogs as $id => $unlockLog){
                //pr($unlockLog);die;
				$count++;
				if(!empty($unlockLog['comments'])){?>
				<tr>
					<td><?= $count; ?></td>
					<td>Comment Posted by <span style="color: crimson"><strong><?= $users[$unlockLog['user_id']]; ?></strong></span> &#40;comment id:<?=$unlockLog['comments'];?>&#41; on <?= $this->Time->format('M jS, Y g:i A', $unlockLog['created'],null,null); ?> for <span style="color: darkorange"><?= $kiosks[$unlockLog['kiosk_id']]; ?></span></td>
				</tr>
				<?php }else{ ?>
				<tr>
					<td><?= $count; ?></td>
					<td>Last updated by <span style="color: crimson"><strong><?= $users[$unlockLog['user_id']]; ?></strong></span> on <?= date('M jS, Y g:i:s A',strtotime($unlockLog['created']));//$this->Time->format('M jS, Y g:i:s A', $unlockLog['created'],null,null); ?>, <span style="color: blue">Status: <?= $unlockStatus[$unlockLog['unlock_status']]; ?></span> for <span style="color: darkorange"><?= $kiosks[$unlockLog['kiosk_id']]; ?></span></td>
				</tr>
				<?php }
			}?>
		</table>

			<?php
			$tableStr = "";
			$i = 1;
            //pr($comments);
			foreach($comments as $sngComment){
				$comment = $sngComment['brief_history'];
				$commentID = $sngComment['id'];
				$postedOn = $sngComment['modified'];
				$postedOn = date('M jS, Y g:i A',strtotime($postedOn));//$this->Time->format('M jS, Y h:i A',$postedOn,null,null); /*h:i A*/
                $userID = $sngComment['user_id'];
				$postedBy = $users[$userID];
				
                
                $truncatedcomment  = \Cake\Utility\Text::truncate( $comment,
                                                     155,
                                                     [ 'ellipsis' => '...',
                                                      'exact' => false ]
                                                     );
				//$truncatedcomment  = String::truncate(
				//										$comment,155,
				//									array(
				//										'ellipsis' => '...',
				//										'exact' => false,
				//										 
				//									));
				$tableStr.="";
				//$tableStr.="<tr><td style='width: 2px'>".$i++."</td>";
				if(strlen($comment)>155){
					
					$userLink = "<span style='color: crimson'><strong>".$postedBy."</strong></span>";
					$commentLink = $this->Html->link('Edit', array('controller' => 'comment_mobile_unlocks','action' => 'edit', $commentID));
					$tableStr.="<td style='width: 90px'>$postedOn<br/></td>
					<td>$userLink</td>
					<td colspan='3'><a href = \"\" title = \"$comment\" alt = \"$comment\">$truncatedcomment</a></td>
					<td style='width: 1px;'>$commentLink</td></tr>";
				}else{
					
					$userLink = "<span style='color: crimson'><strong>".$postedBy."</strong></span>";
					$commentLink = $this->Html->link('Edit', array('controller' => 'comment_mobile_unlocks','action' => 'edit', $commentID));
					$tableStr.="<td style='width: 60px'>$postedOn<br/></td>
					<td>$userLink</td>
					
					<td colspan='3'>$comment</td>
					<td style='width: 1px;'>$commentLink</td></tr>";
				}
			  	
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
		<dt><?php echo __('Estimated Minutes'); ?></dt>
		<dd>&nbsp;&nbsp;
			<?php echo $unlockingMinutes; ?>
			&nbsp;
		</dd>
		
		<dt><?php echo __('Current Status'); ?></dt>
		<dd>
			<?php echo $unlockOptions[$mobileUnlock['status']]; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php
			if(!empty($mobileUnlock['created'])){
			 echo date('M jS, Y g:i A',strtotime($mobileUnlock['created']));
			}else{
			 echo'--';
			}
			//$this->Time->format('M jS, Y g:i A', $mobileUnlock['created'],null,null); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php
			if(!empty($mobileUnlock['modified'])){
			 echo date('M jS, Y g:i A',strtotime($mobileUnlock['modified']));
			}else{
			 echo'--';die;
			}
			//$this->Time->format('M jS, Y g:i A', $mobileUnlock['modified'],null,null); ?>
			&nbsp;
		</dd>
	</dl>
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
<script>
 $(function() {
	  $( document ).tooltip();
	});
</script>
 