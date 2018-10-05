<div class="meassage form">
 <?php
use Cake\Utility\Text;
use Cake\Routing\Router;
  
?>
	<SCRIPT >

	
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
	     $('#Delete').click(function(){
		var cnt = $("input[class = 'checkbox1']:checked").length;
		// alert (cnt);
		if(cnt<1)
		{
		    alert("Please check at least one checkbox");
		    return false;
		}
		else {
		    return true;
		
		}
	    });
	 
	});
	$(function() {
		$("#type").change(function() {
			var type = $(this).val();
			var type1 = $("#type1").val(type);
		 });
		$("#type1").change(function() {
			var type1 = $(this).val();
			var type = $("#type").val(type1);
		 });
	});     
	 
		</script>
	
	
	 <fieldset>
		<legend><?php echo __('Sent Message'); ?></legend>
		<table>
		 
			<?php   echo $this->Form->create('messages',[
                                                 'url' => ['action' => 'index'],
                                                 'type' => 'post']);
           ?>
			 <tr>
				<td><input type = 'checkbox'  name = 'selectall' id = 'selectall' >SelectAll</td>
				<td><select name = "data[Message][type]"   id =   'type' >
								<option value="1" selected>Mark as UnRead</option>
								<option value="2">Mark as Read</option>
				</select></td>
				<td><?php echo "<input type= 'submit' name = 'submit' value ='Mark as Read/Unread'/>";?></td>
				<td><?php echo "<input type= 'submit' name = 'Delete' id = 'Delete' value ='Delete'/>";?></td>
			</tr>
			<tr>
				<th></th>
				<th>To</th>
				<th>Subject </th>
				<th>Sent Date</th>
				<th>Read By</th>
				<th>Read On</th>
			</tr>
			<?php
				$loggedInUser = $this->request->session()->read('Auth.User.id');
				$sender_id = '';
				$sent_by = '';
           //  pr($message);
				foreach ( $message as $key => $messages){
                   // echo $messages->subject;
					//showing the messages sent to kiosk and user by logged in user, not the mails sent by others to individuals
					//mails sent purely to kiosk will be visible to everyone in sent
					if(!empty($messages->sent_to_id)){
						if($messages->sent_by != $loggedInUser){
							continue;
						}
					}
					$sender_id = $messages->sender_id;
					$sent_by = $messages->sent_by;
					$subject = $messages->subject;
					$sub = substr($subject, 0, strpos($subject, "/"));
					//echo "</br>";continue;
			?>
			<?php
				if(!empty($messages->read_by)){
					$readBy = "";
					$kskName = "";
					$kioskID = $messages->read_by;
					if(!empty($kioskID)){$kskName = $kioskname[$kioskID];}
					$userId = $messages->read_by_user ;
					if( !empty($userId)){
						if(array_key_exists($userId,$users)){
							$userName = $users[$userId];
						}else{
							$userName = '--';
						}
						
						$readBy = "({$userName})";
					}
					$readBy = $kskName.$readBy;
				}else{
					$readBy = "--";
				}
				
				if(strtotime($messages->date)>0){
					$readOn = $messages->date;
				}else{
					$readOn = "--";
				}
			
				$type = $messages->sender_read ;
				if($type == 1 || $type == 0){?>
					<tr style="background: palegoldenrod;">
				<?php }else{?>
					<tr>
				<?php }
				?>
					<td>
            <?php //pr($messages);
                if($messages->count > 1){
                   // echo $messages->subject;
                    $createdDate = date("Y-m-d",strtotime($messages->created ));
            ?>
                    <input type = 'checkbox' class = 'checkbox1' name = "data[Message][subject][<?=$messages->subject ;?>][created][]" id = 'data[Message][<?= $messages->id?>]' value = '<?= $createdDate?>'>
           <?php 
                }else{ echo "sds"; ?>
					<input type = 'checkbox' class = 'checkbox1' name = 'data[Message][id][]' id = 'data[Message][<?= $messages->id?>]' value = '<?= $messages->id?>'>
            <?php } ?>
					<?php echo $this->Form->input('sender_id',array('type'=>'hidden','name'=>'data[Message][sender_id]','value'=>$sender_id));?>
					<?php echo $this->Form->input('sent_by',array('type'=>'hidden','name'=>'data[Message][sent_by]','value'=>$sent_by));?>
					</td>
					<td>
					<?php
						
                        if($messages->count > 1){
							$recipients = $messages->count." recipients";
							echo $this->Html->link($recipients, array('action' => 'group_message_details', strtotime($messages->created), $sub));
						}else{
							$receiver_id= $messages['Message']['receiver_id'];
							$read_by = $messages['Message']['read_by'];
							if(!empty($receiver_id) || empty($messages->sent_to_id)){
								//this is for showing kiosk name
								if($type == 1 || $type == 0){
                                   if($receiver_id ==0){
                                   // echo $messages->user_id;
                                      echo $users[$messages->user_id];
									}else{
										//echo "<b>".$sentArr[$messages['Message']['receiver_id']]."</b>";
										echo $users[$messages->user_id];
									}
								}else{
                                    echo $receiver_id;
									if($receiver_id == 0){
										echo $users[$messages->user_id];
										//echo "Warehouse";	
									}else{
										echo $users[$messages->user_id];
										//echo $sentArr[$messages['Message']['receiver_id']];
									}
								}
							}else{
								if($type == 1 || $type == 0){
									//this is for showing user name for personal message
									if(array_key_exists($messages->sent_to_id,$users)){
										echo "<b>".$users[$messages->sent_to_id]."</b>";
									}else{
										echo "<b>--</b>";
									}
								}else{
									if(array_key_exists($messages->sent_to_id,$users)){
										echo $users[$messages->sent_to_id];
									}else{
										echo "--";
									}
								}
							}
						}				?>
					 </td>
					<td>
					<?php
           
						if($messages->count > 1){
							  echo $this->Html->link($messages->subject, array('action' => 'group_message_details', strtotime($messages->created), $sub));
						}else{
							  echo $this->Html->link ($messages->subject ,
																					array('controller' => 'messages',
																						  'action'=> 'view',
																						  $messages->id)) ;
										  echo "\t\t\t\t";
										  $msg  = strip_tags($messages->message);
                                           $truncatedmessage =  Text::truncate(
                                            $messages->message,
                                            50,
                                            [
                                                'ellipsis' => '...',
                                                'exact' => false
                                            ]
                                        );
					 
										  
										   echo  $truncatedmessage;
						}   ?>
					</td>
					<td>
					<?php
						$meassagedate= $messages->created;
						echo  date('d-m-Y h:i:s',strtotime($meassagedate)) ;?></td>
					<td><?php echo $readBy;?></td>
					<td><?php
					if($readOn != '--'){
						echo date('d-m-Y h:i:s',strtotime($readOn));
					}else{
						echo'--';
					}
					?></td>
				</tr>
				<?php } // END FOREACH ?>
			<tr>
				<td></td>
				<td><select name = "data[Message][type]"   id =   'type1' >
								<option value="1" selected>Mark as UnRead</option>
								<option value="2">Mark as Read</option>
				</select></td>
				<td><?php echo "<input type= 'submit' name = 'submit' value ='Mark as Read/Unread'/>";?></td>
				<td><?php echo "<input type= 'submit' name = 'Delete' id = 'Delete' value ='Delete'/>";?></td>
				<td><?php echo $this->Form->end(); ?></td>
			</tr>
		
		
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
				<h1><li><?php echo $this->Html->link("Inbox($inbox)", array('controller' => 'messages','action'=> 'inbox'));   ?></li></h1> 
				<h1><li><?php echo $this->Html->link("sent Item", array('controller' => 'messages','action'=> 'index')); ?></li></h1>
				<h1><li><?php echo $this->Html->link("Trash", array('controller' => 'messages','action'=> 'trash')); ?></li></h1>				                         
				<h1><li><?php echo $this->Html->link("Compose", array('controller' => 'messages','action'=> 'add'));?></li> </h1> 
		</ul>
		
</div>






