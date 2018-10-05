<div class="meassage form">
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
	    
	    //for delete
	     $('#Delete').click(function(){
		var cnt = $("input[class = 'checkbox1']:checked").length;
		//alert (cnt);
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
		<legend><?php echo __('Inbox'); ?></legend>
		<table >
			 <?php //$webRoot = $this->request->webroot/messages/inbox;
            // $webRoot = FULL_BASE_URL.$this->webroot."messages/inbox";?>
              
			<?php   
            echo $this->Form->create('messages',[
                                                 'url' => ['action' => 'inbox'],
                                                 'type' => 'post']);?>
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
				<th>From</th>
				<th>Subject </th>
				<th>Read by</th>
				<th>Read on</th>
				<th>Sent Date</th>
			</tr>
			<?php
			$loggedInUser =$this->request->session()->read('Auth.User.id');
		 // pr($message);
			foreach ($message as $messages ):
			if(!empty($messages->sent_to_id)){
				//not showing the message of other user
				if($messages->sent_to_id != $loggedInUser){
					 continue;
				}
			}
			if(strtotime($messages->date)>0){
				$readOn =  $messages->date;
			}else{
				$readOn = "";
			}
			$receiver_id = $messages->receiver_id ;
			$sent_to_id = $messages->sent_to_id;
			if(!empty($messages->read_by)){
					$readBy = "";
					$kskName = "";
					$kioskID = $messages->read_by;
					if(!empty($kioskID)){$kskName = $kioskname[$kioskID];}
					$userId = $messages->read_by_user;
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
				//$readBy."</br>";
				$type = $messages->receiver_read;
			if($type == 1 || $type == 0){?>
				<tr style="background: palegoldenrod;">
			<?php }else{?>
				<tr>
			<?php }
			?>
				<td><input type = 'checkbox' class = 'checkbox1' name = 'data[Message][id][]' id = 'data[Message][<?= $messages->id?>]' value = '<?= $messages->id?>'>
				<?php echo $this->Form->input('receiver_id',array('type'=>'hidden','name'=>'data[Message][receiver_id]','value'=>$receiver_id));?>
				<?php echo $this->Form->input('sent_to_id',array('type'=>'hidden','name'=>'data[Message][sent_to_id]','value'=>$sent_to_id));?>
				</td>
				<td><?php //echo $userID;
					$sender_id = $messages->sender_id ;
					  $read_by = $userID ;
					if(!empty($sender_id) || empty($messages->sent_by)){
						//this is for showing kiosk name
						if($type == 1 || $type == 0){
							if($sender_id ==0){
								if(array_key_exists($messages->user_id,$users)){
									echo $users[$messages->user_id];   // on client request 12/15/2016
								}
								//echo "<b>Warehouse</b>";	
							}else{
								if(array_key_exists($messages->user_id,$users)){
									echo $users[$messages->user_id]; // on client request 12/15/2016
								}
								//echo "<b>".$sentArr[$messages['Message']['sender_id']]."</b>";
							 
							}
						}else{
							
							if($sender_id == 0){
								//echo "Warehouse";
								if(array_key_exists($messages->user_id,$users)){
									echo $users[$messages->user_id]; // on client request 12/15/2016
								}
							}else{
								if(array_key_exists($messages->user_id,$users)){
									echo $users[$messages->user_id]; // on client request 12/15/2016
								}
								//echo $sentArr[$messages['Message']['sender_id']];
							 
							}
						}
					}else{
						if($type == 1 || $type == 0){
							//this is for showing user name for personal message
							if(array_key_exists($messages->sent_by,$users)){
								echo "<b>".$users[$messages->sent_by]."</b>";
							}else{
								echo "<b>--</b>";
							}
						}else{
							if(array_key_exists($messages->sent_by,$users)){
								echo $users[$messages->sent_by];
							}else{
								echo "--";
							}
						}
					}
					
					
				    ?>
				</td>
				
				<td><?php
					echo $this->Html->link (strip_tags($messages->subject) ,
                                                                      array('controller' => 'messages',
                                                                            'action'=> 'view',
                                                                            $messages->id
									    )
								);
					echo "\t\t\t\t\t\t\t\t";
					//pr($messages);die;
                     $truncatedmessage =  Text::truncate(
                             $messages->message,
                             50,
                             [
                                 'ellipsis' => '...',
                                 'exact' => false
                             ]
                         );
					 
							 echo $truncatedmessage;
				?></td>
				
				<td>
				<?php if(!empty($readBy)){
					echo $readBy; 
					}else{
						echo "--";
					}
					?></td>
				<td><?php if(!empty($readOn)){
						echo date('d-m-Y h:i:s',strtotime($readOn));
					}else{
						echo "--";
					}
					?></td>
                                <td><?php
					$meassagedate= date('d-m-Y h:i:s',strtotime($messages->created));
					echo "<br/>";
					if(!empty($meassagedate)){
						echo  $meassagedate;
						//echo $this->Time->timeAgoInWords($meassagedate, array(
						//					       'format' => "d M, Y h:i:s a",
						//						      'end' => '+1 day')
						//			 );
					}else{
						echo "";
					}
					
				?></td>
				</tr>
				<?php // if( $messages['Message']['id'] == '2204'){
				//die;
				//} ?>
			<?php endforeach; ?>
			<td></td>
				<td><select name = "data[Message][type]"   id =   'type1' >
								<option value="1" selected>Mark as UnRead</option>
								<option value="2">Mark as Read</option>
				</select></td>
				<td><?php echo "<input type= 'submit' name = 'submit' value ='Mark as Read/Unread'/>";?></td>
				<td><?php echo "<input type= 'submit' name = 'Delete' id = 'Delete' value ='Delete'/>";?></td>
		
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





