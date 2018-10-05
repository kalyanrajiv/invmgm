<div class="meassage form">
<h2><?php echo __('Message'); ?></h2>
	<?php
		$sender_id = $message['sender_id'];
		if(!empty($sender_id) || empty($message['sent_by'])){
			//this is for showing kiosk name
			if($sender_id == 0){
				$sender = "Warehouse";	
			}else{
				$sender = $kioskname[$message['sender_id']];
			 
			}
			
			$receiver = $kioskname[$message['receiver_id']];
		}else{
			if(array_key_exists($message['sent_by'],$users)){
				$sender = $users[$message['sent_by']];
			}else{
				$sender = "--";
			}
			
			if(array_key_exists($message['sent_to_id'],$users)){
				$receiver = $users[$message['sent_to_id']];
			}else{
				$receiver = "--";
			}
		}
	?>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($message['id']); ?>
			&nbsp;
		</dd>
               <dt><?php echo __('From'); ?></dt>
		 
		<dd>
			<?php
			 
			echo $sender;
			?>
			&nbsp;
		</dd>
		 <dt><?php echo __('To'); ?></dt>
		<dd>
			<?php
			 
			echo $receiver;
			?>
			&nbsp;
			
		</dd>
		
		
		<dt><?php echo __('subject'); ?></dt>
		<dd>
			<?php echo h($message['subject']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Message'); ?></dt>
		<dd>
			<?php  echo $message['message']; ?>
			&nbsp;
		</dd>
		 <dt><?php echo __('Read by'); ?></dt>
		<dd>
			<?php
				if(!empty($message['read_by'])){
					$readBy = "";
					$kskName = "";
					$kioskID = $message['read_by'];
					if(!empty($kioskID)){$kskName = $kioskname[$kioskID];}
					$userId = $message['read_by_user'] ;
					if( !empty($userId)){
						if(array_key_exists($userId,$users)){
							$userName = $users[$userId];
						}else{
							$userName = "";
						}
						
						$readBy = "({$userName})";
					}
					echo $kskName.$readBy;
				}else{
					echo "--";
				}
				?>
			&nbsp;
			
		</dd>
		
		<dt><?php echo __('Read Date'); ?></dt>
		<dd>
			<?php
				if(strtotime($message['date'])>0){
					echo $message['date'];
				}else{
					echo "--";
				}
			?>
			&nbsp;
		</dd>
		<dt><?php echo __('Sent On'); ?></dt>
		<dd>
			<?php echo $message['created'] ;?>
			&nbsp;
		</dd>
		 
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		
		
		<h1><li><?php echo $this->Html->link("Inbox($inbox)", array('controller' => 'messages','action'=> 'inbox'));   ?></li></h1> 
				<h1><li><?php echo $this->Html->link("sent Item", array('controller' => 'messages','action'=> 'index')); ?></li></h1>
				<h1><li><?php echo $this->Html->link("Trash", array('controller' => 'messages','action'=> 'trash')); ?></li></h1>				                         
				<h1><li><?php echo $this->Html->link("Compose", array('controller' => 'messages','action'=> 'add'));?></li> </h1>
				<li><?php echo $this->Html->link("Reply", array('controller' => 'messages','action'=> 'reply', $message['id'])); ?></li>
		
	</ul>
</div>
