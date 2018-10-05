<?php
//if(array_key_exists('message',$this->request->data)){
//	if(empty($this->request->data['message'])){
//		echo"hi";die;
//	}
//}
//pr($this->request);die;
?>
 <div class="meassage form">	
       <fieldset>
		    <legend><?php echo __('Compose'); ?></legend>
				  <?php    echo $this->Form->create('Message',array( 'inputDefaults' => array( 'label' => false,'div' => true), array('action' => 'index'), 'onSubmit' => "return validateForm();"));?>
				  <div id='error_div' tabindex = 1></div>
				   <table >
						<tr>
						      <td>Message Type:</td>
						      <td><input type='radio' name='message_type' value='kiosk' id="kiosk_radio" onclick="disableDropdowns(this.id);">Kiosk<span style="color: red;">*</span></td>
						      <td><input type='radio' name='message_type' value='personal' id="personal_radio" onclick="disableDropdowns(this.id);">Personal<span style="color: red;">*</span></td>
                                                      <td>
                                                        <input type='button' name='reset' id="reset" value = 'Reset' onclick="reset_values();" style='width: 65px;border-radius: 6px;background: darkgray;'>
                                                      </td>
						</tr>
			 			  <tr>
						      <td>To:</td>
						      <td style='width: 221px;'>Kiosks:<select name = "receiver_id[]"  multiple="multiple" size='6' id="kiosk_dropdown">
								  <option value=  '-1'>All</option>
								   <?php
									  foreach($kioskname as $key => $kioskid)
									  {
									      echo "<option value =".$key." >".$kioskid."</option>";
									  }
								  ?>
						      </select> </td>
						      <td>Managers:<select name = "sent_to_id[]"  multiple="multiple" size='6' id="manager_dropdown">
								  <option value=  '-2'>All</option>
								   <?php
									  foreach($managers as $m => $manager)
									  {
									      echo "<option value =".$m." >".$manager."</option>";
									  }
								  ?>
						      </select> </td>
						      <td>Administrators:<select name = "sent_to_id[]"  multiple="multiple" size='6' id="admin_dropdown">
								  <option value=  '-3'>All</option>
								   <?php
									  foreach($admins as $a => $admin)
									  {
									      echo "<option value =".$a." >".$admin."</option>";
									  }
								  ?>
						      </select> </td>
                                                      <td>Stock Managers:<select name = "sent_to_id[]"  multiple="multiple" size='6' id="sales_dropdown">
								  <option value=  '-5'>All</option>
								   <?php
									  foreach($salesRep as $sp => $sales_rep)
									  {
									      echo "<option value =".$sp." >".$sales_rep."</option>";
									  }
								  ?>
						      </select> </td>
						      <td>Users:<select name = "sent_to_id[]"  multiple="multiple" size='6' id="user_dropdown">
								  <option value=  '-4'>All</option>
								   <?php
									  foreach($allUsers as $u => $user)
									  {
									      echo "<option value =".$u." >".$user."</option>";
									  }
								  ?>
						      </select> </td>
				  		</tr>
		 	            <tr>
									 <td>Subject:</td><td colspan='5'>  <?php echo $this->Form->input('subject', array('name' => "subject"));   ?></td>
			           </tr>
				       <tr> 
									<td>Message:</td><td  colspan='5'> <?php echo $this->Ck->input('message', array('name' => "message"));?></td>
            		  </tr>
				     <?php   date("m/d/Y h:i:s a", time()); ?>
				    <?php echo $this->Form->input('sender_status', array('type'=>'hidden','name' => "sender_status",'value' => '2')); ?>
				    <?php echo $this->Form->input('type', array('type'=>'hidden','name' => "type",'value' => '1')); ?>
				    <?php echo $this->Form->input('type', array('type'=>'hidden','name' => "sender_type",'value' => '2')); ?>
			         <tr> <td>&nbsp;</td><td  colspan='5'><?php echo $this->Form->submit('send',array('name'=>'submit')); ?>
<?php echo $this->Form->end(); ?> </td> </tr>
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
     // CKEDITOR.replace('data[message]');
      
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
            document.getElementById("manager_dropdown").disabled = true;
            document.getElementById("admin_dropdown").disabled = true;
            document.getElementById("user_dropdown").disabled = true;
            document.getElementById("kiosk_dropdown").disabled = true;
            document.getElementById("sales_dropdown").disabled = true;
        });
      
      function reset_values() {
        $('#kiosk_dropdown').val('');
        $('#manager_dropdown').val('');
        $('#admin_dropdown').val('');
        $('#user_dropdown').val('');
        $('#sales_dropdown').val('');
      }
</script>