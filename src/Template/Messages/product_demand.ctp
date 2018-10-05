 <div class="meassage form">	
       <fieldset>
		    <legend><?php echo __('Product Demand'); ?></legend>
				  <?php    echo $this->Form->create('Message',array( 'inputDefaults' => array( 'label' => false,'div' => true), array('action' => 'index'), 'onSubmit' => "return validateForm();"));?>
				  <div id='error_div' tabindex = 1></div>
				   <table >
						<?php
						$kioskName = $this->request->session()->read('kiosk_title'); 
						$date = date('d-m-Y');
						$userId = $this->request->session()->read('Auth.User.id');
						echo $this->Form->input('subject',array('type' => 'hidden', 'value' => "Product Demand: By $kioskName ($users[$userId] on $date)")); ?>
						<input type='hidden' name='message_type' value='personal'>
				       <tr>
							<td  colspan='5'> <?php  echo $this->Ck->input('message',array('div' => false, 'label' => false)); ?></td>
            		  </tr>
				     <?php   date("m/d/Y h:i:s a", time()); ?>
				    <?php echo $this->Form->input('sender_status', array('type'=>'hidden','name' => "[sender_status]",'value' => '2')); ?>
				    <?php echo $this->Form->input('type', array('type'=>'hidden','name' => "[type]",'value' => '1')); ?>
				    <?php echo $this->Form->input('type', array('type'=>'hidden','name' => "[sender_type]",'value' => '2')); ?>
			         <tr><td  colspan='5'><?php echo $this->Form->submit('send',array('name'=>'submit')); ?>
<?php echo $this->Form->end(); ?></td></tr>
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
     // CKEDITOR.replace('message');
</script>