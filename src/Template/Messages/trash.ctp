 <div class="trash form">
     <?php
use Cake\Utility\Text;
use Cake\Routing\Router;
  
?>
	<script>
		$(document).ready(function() {
		    $('#selectall').click(function(event) {  
			if(this.checked) { 
			    $('.checkbox1').each(function() { 
				this.checked = true;               
			    });
			}else{
			    $('.checkbox1').each(function() { 
				this.checked = false;                       
			    });        
			}
		    });
		 
		});
	 
	</script>
	
   	 <fieldset>
		<legend><?php echo __('Trash');
		?></legend>
			<table >
				<form name="myForm" action = "message/index" method = "post">
					<tr>
						 
					</tr>
					
					<tr>
						<th>From/To</th> 
						<th>Subject</th>
						<th>Read by</th>
						<th>Message date</th>
					</tr>	
					<?php
					$srNo = 0;
					$loggedInUser = $this->request->session()->read('Auth.User.id');
                    //pr($messages);
					foreach ($messages as $message): $srNo++;
					//check for showing the emails of th
					//if(!empty($message['Message']['sent_to_id'])){
					//	 //not showing the message of other user
					//	 if($message['Message']['sent_by'] != $loggedInUser &&
					//	    $message['Message']['sent_to_id'] != $loggedInUser){
					//		 continue;
					//	 }
					// }
					?>	
					
						 
						<td><?php
						 $receiver_id = $message->receiver_id;
						 $sender_id = $message->sender_id ;
						 $sent_to_id = $message->sent_to_id ;
						 $sent_by = $message->sent_by ;
						 
						 if(!empty($sent_to_id) || empty($sender_id)){
							if($loggedInUser == $sent_to_id){
							       $name = $users[$sent_to_id];
							}elseif($loggedInUser == $sent_by){
							       $name = $users[$sent_by];
							}else{
							       $name = $users[$sent_to_id];
							}
						 }else{
							if($receiver_id==$kiosk_id){
							       $name = $kiosks[$sender_id];
							}elseif($sender_id==$kiosk_id){
							       $name = $kiosks[$receiver_id];
							}else{
							       $name = $kiosks[$receiver_id];
							}
						 }
						 
						 echo $name;
						//echo  $sentArr[$message['Message']['receiver_id']];?></td>
						<td> <?php echo $this->Html->link ($message->subject,
                                                                      array('controller' => 'messages',
                                                                            'action'=> 'view',
                                                                            $message->id)) ;
							echo "\t\t\t\t";
							  $msg  = strip_tags($message->message);
                                           $truncatedmessage =  Text::truncate(
                                            $message->message,
                                            50,
                                            [
                                                'ellipsis' => '...',
                                                'exact' => false
                                            ]
                                        );
							 echo  $truncatedmessage;
							
					?></td>
						<td>
						 <?php
							if(!empty($message->read_by)){
							       $readBy = "";
							       $kskName = "";
							       $kioskID = $message->read_by;
							       if(!empty($kioskID)){$kskName = $kiosks[$kioskID];}
							       $userId = $message->read_by_user;
							       if( !empty($userId)){
								      if(array_key_exists($userId,$users)){
									     $userName = $users[$userId];
								     }else{
									     $userName = '--';
								     }
								       $readBy = "({$userName})";
							       }
							       echo $kskName.$readBy;
						       }else{
							       echo "--";
						       }
						 ?>
						</td>
						<td><?php 
							$meassagedate = $message->created;
							//pr($meassagedate);
							echo $meassagedate;
							//echo $this->Time->timeAgoInWords($meassagedate, array(
							//					      'format' => "d M, Y h:i:s a",
							//					      'end' => '+1 day')
							//			 );
						?></td>
					<tr>
					<?php endforeach; ?>
	  

				</form>
       			</table>
			  <div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
             <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
        </div>
		 </fieldset>
 </div>
	 <div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	
	<ul>
		<li><h1><?php echo $this->Html->link("Inbox($inbox)", array('controller' => 'messages','action'=> 'inbox'));   ?></h1> </li>
		<li><h1><?php echo $this->Html->link("sent Item", array('controller' => 'messages','action'=> 'index')); ?></h1></li>
		<li><h1><?php echo $this->Html->link("Trash", array('controller' => 'messages','action'=> 'trash')); ?></h1></li> 
		<li><h1><?php echo $this->Html->link("Compose", array('controller' => 'messages','action'=> 'add'));?> </h1>
		                              
				
	</ul>
		
	</div>
