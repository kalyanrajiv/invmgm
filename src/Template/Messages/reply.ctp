 <div class="meassage form">	
       <fieldset>
		    <legend><?php echo __('Compose'); ?></legend>
				  <?php    #echo $this->Form->create(null,array( 'inputDefaults' => array( 'label' => false,'div' => true), array('action' => 'add', 'controller' => 'message'), 'onSubmit' => "return validateForm();"));
                                  echo $this->Form->create('Message',array('url' => array('action' => 'add', 'controller' => 'messages'), 'onSubmit' => "return validateForm();"));
                                  $subject = $sender_id = $sent_by = $checked = $content = '';
                                  if(count($message)){
                                    $subject = "RE: ".$message['Message']['subject'];
                                    $sender_id = $message['Message']['sender_id'];
                                    $sent_by = $message['Message']['sent_by'];
                                    $content = "<br/><br/><br/>Originally sent on ".$this->Time->format('jS M, Y h:i A', $message['Message']['modified'],null,null).":".$message['Message']['message'];
                                  }
                                  ?>
				  <div id='error_div' tabindex = 1></div>
				   <table >
						<tr>
						      <td>Message Type:</td>
						      <td><input type='radio' name='message_type' value='kiosk' id="kiosk_radio" onclick="disableDropdowns(this.id);" <?=$checked = (!empty($sender_id))? "checked": ''; ?>>Kiosk<span style="color: red;">*</span></td>
						      <td><input type='radio' name='message_type' value='personal' id="personal_radio" onclick="disableDropdowns(this.id);" <?=$checked = (!empty($sent_by))? "checked": ''; ?>>Personal<span style="color: red;">*</span></td>
						</tr>
			 			  <tr>
						      <td>To:</td>
						      <td style='width: 221px;'>Kiosks:<select name = "data[Message][receiver_id][]"  multiple="multiple" size='6' id="kiosk_dropdown">
								  <option value=  '-1'>All</option>
								   <?php
									  foreach($kioskname as $key => $kioskid)
									  {
                                                                            if($sender_id == $key){
                                                                                $selectedKiosk = "selected";
                                                                            }else{
                                                                                $selectedKiosk = "";
                                                                            }
									      echo "<option value =".$key." ".$selectedKiosk.">".$kioskid."</option>";
									  }
								  ?>
						      </select> </td>
						      <td>Managers:<select name = "data[Message][sent_to_id][]"  multiple="multiple" size='6' id="manager_dropdown">
								  <option value=  '-2'>All</option>
								   <?php
									  foreach($managers as $m => $manager)
									  {
                                                                            if($sent_by == $m){
                                                                                $selectedManager = "selected";
                                                                            }else{
                                                                                $selectedManager = '';
                                                                            }
									      echo "<option value =".$m." ".$selectedManager.">".$manager."</option>";
									  }
								  ?>
						      </select> </td>
						      <td>Administrators:<select name = "data[Message][sent_to_id][]"  multiple="multiple" size='6' id="admin_dropdown">
								  <option value=  '-3'>All</option>
								   <?php
                                                                        
									  foreach($admins as $a => $admin)
									  {
                                                                            if($sent_by == $a){
                                                                                $selectedAdmin = 'selected';
                                                                            }else{
                                                                                $selectedAdmin = '';
                                                                            }
									      echo "<option value =".$a." ".$selectedAdmin.">".$admin."</option>";
									  }
								  ?>
						      </select> </td>
                                                       <td>Stock Managers:<select name = "data[Message][sent_to_id][]"  multiple="multiple" size='6' id="sales_dropdown">
								  <option value=  '-5'>All</option>
								   <?php
									  foreach($salesRep as $sp => $sales_rep)
									  {
                                                                            if($sent_by == $sp){
                                                                                $selectedSales = "selected";
                                                                            }else{
                                                                                $selectedSales = '';
                                                                            }
									      echo "<option value =".$sp." ".$selectedSales.">".$sales_rep."</option>";
									  }
								  ?>
						      </select> </td>
						      <td>Users:<select name = "data[Message][sent_to_id][]"  multiple="multiple" size='6' id="user_dropdown">
								  <option value=  '-4'>All</option>
								   <?php
                                                                        
									  foreach($allUsers as $u => $user)
									  {
                                                                            if($sent_by == $u){
                                                                                $selectedUser = "selected";
                                                                            }else{
                                                                                $selectedUser = '';
                                                                            }
									      echo "<option value =".$u." ".$selectedUser.">".$user."</option>";
									  }
								  ?>
						      </select> </td>
				  		</tr>
		 	            <tr>
									 <td>Subject:</td><td colspan='5'> <?php echo $this->Form->input('subject',array('value' => $subject)); ?></td>
			           </tr>
				       <tr>
									<td>Message:</td><td  colspan='5'> <?php   echo $this->Ck->input('message', array('value' => $content, 'label' => false)); ?></td>
            		  </tr>
				     <?php   date("m/d/Y h:i:s a", time()); ?>
				    <?php echo $this->Form->input('sender_status', array('type'=>'hidden','name' => "data[Message][sender_status]",'value' => '2')); ?>
				    <?php echo $this->Form->input('type', array('type'=>'hidden','name' => "data[Message][type]",'value' => '1')); ?>
				    <?php echo $this->Form->input('type', array('type'=>'hidden','name' => "data[Message][sender_type]",'value' => '2')); ?>
			         <tr> <td>&nbsp;</td><td  colspan='5'>
                   <?php echo $this->Form->submit('send'); ?>
<?php echo $this->Form->end(); ?></td> </tr>
 </td> </tr>
		  </table>
	   </fieldset>	                                           
    </div>      
  <div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	
		<ul>
				<h1><li><?php echo $this->Html->link("Inbox($inbox)", array('controller' => 'messages','action'=> 'inbox',$kiosk_id));   ?></li></h1> 
				<h1><li><?php echo $this->Html->link("sent Item", array('controller' => 'messages','action'=> 'index',$kiosk_id)); ?></li></h1>
				<h1><li><?php echo $this->Html->link("Trash", array('controller' => 'messages','action'=> 'trash',$kiosk_id)); ?></li></h1>				                         
				<h1><li><?php echo $this->Html->link("Compose", array('controller' => 'messages','action'=> 'add',$kiosk_id));?></li> </h1> 
		</ul>
		
</div>
            
 <script>
      //CKEDITOR.replace( 'message');
      
      function disableDropdowns(ide){
	    if (ide == 'kiosk_radio') {
		  document.getElementById("manager_dropdown").disabled = true;
		  document.getElementById("admin_dropdown").disabled = true;
		  document.getElementById("user_dropdown").disabled = true;
                  document.getElementById("sales_dropdown").disabled = true;
		  document.getElementById("kiosk_dropdown").disabled = false;
	    } else if (ide == 'personal_radio') {
		  document.getElementById("manager_dropdown").disabled = false;
		  document.getElementById("admin_dropdown").disabled = false;
		  document.getElementById("user_dropdown").disabled = false;
                  document.getElementById("sales_dropdown").disabled = false;
		  document.getElementById("kiosk_dropdown").disabled = true;
	    }
      }

      function validateForm() {
	    var radio = $("input[name='message_type']:radio:checked").length;
	    if (radio == 0) {
		  $('#error_div').html('<strong>Please choose Message Type!<strong>').css('background','yellow').focus();
		  return false;
	    } else {
		  return true;
	    }
      }
      
      $(document).ready(function(){
            disableDropdowns($('input[name="message_type"]:radio:checked').attr('id'));
        });
</script>