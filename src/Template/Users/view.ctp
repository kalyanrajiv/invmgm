 <div class="users view">
<h2><?php
//pr($user);
$delte_image = $this->Url->build(['controller' => 'users', 'action' => 'delete_image'],true);
echo __('User'); ?></h2>
<input type='hidden' name='delte_image' id='delte_image' value='<?=$delte_image?>' />
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($user->id); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('F Name'); ?></dt>
		<dd>
			<?php echo h($user->f_name); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('L Name'); ?></dt>
		<dd>
			<?php echo h($user->l_name); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Email'); ?></dt>
		<dd>
			<?php echo h($user->email); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Username'); ?></dt>
		<dd>
			<?php echo h($user->username); ?>
			&nbsp;
		</dd>		
		<dt><?php echo __('Group'); ?></dt>
		<dd>
             <?= $user->has('group') ? $this->Html->link($user->group->name, ['controller' => 'Groups', 'action' => 'view', $user->group->id]) : '' ?> 
			
			&nbsp;
		</dd>
		<dt><?php echo __('Mobile'); ?></dt>
		<dd>
			<?php echo h($user->mobile); ?>
			&nbsp;
		</dd>		
		<dt><?php echo __('Address 1'); ?></dt>
		<dd>
			<?php echo h($user->address_1); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Address 2'); ?></dt>
		<dd>
			<?php echo h($user->address_2); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Date Of Birth'); ?></dt>
		<dd>
			<?php if(array_key_exists('profiles[0]',$user)){
                echo $user->profiles[0]->date_of_birth;
            }
              ?>
			&nbsp;
		</dd>
		</dd>
		<dt><?php echo __('National Insurance'); ?></dt>
		<dd>
			<?php   if(array_key_exists('profiles[0]',$user)){
                echo  $user->profiles[0]->national_insurance;
            }
           //pr($user1);//echo h($profiles->national_insurance); ?>
			&nbsp;
		</dd>
		</dd>
		<dt><?php echo __('Visa Type'); ?></dt>
		<dd>
			<?php if(array_key_exists('profiles[0]',$user)){
                echo  $user->profiles[0]->visa_type;
            }?>
			&nbsp;
		</dd>
		</dd>
		<dt><?php echo __('Visa Expiry Date'); ?></dt>
		<dd>
			<?php if(array_key_exists('profiles[0]',$user)){
                echo $user->profiles[0]->visa_expiry_date;
            }?>
			&nbsp;
		</dd>
		</dd>
		<dt><?php echo __('Memo'); ?></dt>
		<dd>
			<?php
            if(array_key_exists('profiles[0]',$user)){
                echo $user->profiles[0]->memo;
                }
                ?>
			&nbsp;
		</dd>
		<?php //code added by inder, starts from here ?>
		<dt><?php echo __('Document 1'); ?></dt>
		<dd>
			<input type="hidden" id="user_id" value="<?php echo $user->id;?>" />
			<?php
			if(array_key_exists(0,$user['attachments'])){
				if($user['attachments']['0']->type == 'application/pdf'){
					$imageDir = WWW_ROOT."files".DS.'documents'.DS.$user['attachments']['0']->foreign_key.DS;
					$imageName = $user['attachments']['0']->attachment;
					$absoluteImagePath = $imageDir.$imageName;
					$imageURL = "/files/documents/".$user['attachments']['0']->foreign_key."/$imageName";
				}else{
					$imageDir = WWW_ROOT."files".DS.'image'.DS.'attachment'.DS.$user['attachments']['0']->foreign_key.DS;
					$imageName = $user['attachments']['0']->attachment;
					$absoluteImagePath = $imageDir.$imageName;
					$imageURL = "/files/image/attachment/".$user['attachments']['0']->foreign_key."/$imageName";
				}
				
				
			}
			
			if(array_key_exists(0,$user['attachments'])){
				echo $this->Html->link($user['attachments']['0']->attachment, $imageURL, array('target'=>'_blank', 'fullBase' => true));echo "&nbsp;&nbsp;&nbsp";
				$att_id = $user['attachments']['0']->id;
				echo $this->Html->link('Delete', "#", array("onclick" => "test($att_id)"));
			}
			
			?>
			&nbsp;
		</dd>
		<dt><?php echo __('Document 2'); ?></dt>
		<dd>
			<?php
			if(array_key_exists(1,$user['attachments'])){
				if($user['attachments']['1']['type'] == 'application/pdf'){
					$imageDir = WWW_ROOT."files".DS.'documents'.DS.$user['attachments']['1']->foreign_key.DS;
					$imageName = $user['attachments']['1']->attachment;
					$absoluteImagePath = $imageDir.$imageName;
					$imageURL = "/files/documents/".$user['attachments']['1']->foreign_key."/$imageName";
				}else{
					$imageDir = WWW_ROOT."files".DS.'image'.DS.'attachment'.DS.$user['attachments']['1']->foreign_key.DS;
					$imageName = $user['attachments']['1']->attachment;
					$absoluteImagePath = $imageDir.$imageName;
					$imageURL = "/files/image/attachment/".$user['attachments']['1']->foreign_key."/$imageName";
				}
				
			}
			
			if(array_key_exists(1,$user['attachments'])){
				echo $this->Html->link($user['attachments']['1']->attachment, $imageURL, array('target'=>'_blank', 'fullBase' => true));echo "&nbsp;&nbsp;&nbsp";
				$att_id = $user['attachments']['1']->id;
				echo $this->Html->link('Delete', "#", array("onclick" => "test($att_id)"));
			}
			
			?>
			&nbsp;
		</dd>
		<dt><?php echo __('Document 3'); ?></dt>
		<dd>
			<?php
			if(array_key_exists(2,$user['attachments'])){
				if($user['attachments']['2']->type == 'application/pdf'){
					$imageDir = WWW_ROOT."files".DS.'documents'.DS.$user['attachments']['2']->foreign_key.DS;
					$imageName = $user['attachments']['2']->attachment;
					$absoluteImagePath = $imageDir.$imageName;
					$imageURL = "/files/documents/".$user['attachments']['2']->foreign_key."/$imageName";
				}else{
					$imageDir = WWW_ROOT."files".DS.'image'.DS.'attachment'.DS.$user['attachments']['2']->foreign_key.DS;
					$imageName = $user['attachments']['2']->attachment;
					$absoluteImagePath = $imageDir.$imageName;
					$imageURL = "/files/image/attachment/".$user['attachments']['2']->foreign_key."/$imageName";
				}
				
			}
			
			
			if(array_key_exists(2,$user['attachments'])){
				echo $this->Html->link($user['attachments']['2']->attachment, $imageURL, array('target'=>'_blank', 'fullBase' => true));echo "&nbsp;&nbsp;&nbsp";
				$att_id = $user['attachments']['2']->id;
				echo $this->Html->link('Delete', "#", array("onclick" => "test($att_id)"));
			}
			
			?>
			&nbsp;
		</dd>
		<dt><?php echo __('Document 4'); ?></dt>
		<dd>
			<?php
			if(array_key_exists(3,$user['attachments'])){
				if($user['attachments']['3']->type == 'application/pdf'){
					$imageDir = WWW_ROOT."files".DS.'documents'.DS.$user['attachments']['3']->foreign_key.DS;
					$imageName = $user['attachments']['3']->attachment;
					$absoluteImagePath = $imageDir.$imageName;
					$imageURL = "/files/documents/".$user['attachments']['3']->foreign_key."/$imageName";
				}else{
					$imageDir = WWW_ROOT."files".DS.'image'.DS.'attachment'.DS.$user['attachments']['3']->foreign_key.DS;
					$imageName = $user['attachments']['3']->attachment;
					$absoluteImagePath = $imageDir.$imageName;
					$imageURL = "/files/image/attachment/".$user['attachments']['3']->foreign_key."/$imageName";
				}
				
			}
			
			if(array_key_exists(3,$user['attachments'])){
				echo $this->Html->link($user['attachments']['3']->attachment, $imageURL, array('target'=>'_blank', 'fullBase' => true));echo "&nbsp;&nbsp;&nbsp";
				$att_id = $user['attachments']['3']->id;
				echo $this->Html->link('Delete', "#", array("onclick" => "test($att_id)"));
			}
			
			?>
			
		</dd>
		<dt><?php echo __('Document 5'); ?></dt>
		<dd>
			<?php
			if(array_key_exists(4,$user['attachments'])){
				if($user['attachments']['4']->type == 'application/pdf'){
					$imageDir = WWW_ROOT."files".DS.'documents'.DS.$user['attachments']['4']->foreign_key.DS;
					$imageName = $user['attachments']['4']->attachment;
					$absoluteImagePath = $imageDir.$imageName;
					$imageURL = "/files/documents/".$user['attachments']['4']->foreign_key."/$imageName";
				}else{
					$imageDir = WWW_ROOT."files".DS.'image'.DS.'attachment'.DS.$user['attachments']['4']->foreign_key.DS;
					$imageName = $user['attachments']['4']->attachment;
					$absoluteImagePath = $imageDir.$imageName;
					$imageURL = "/files/image/attachment/".$user['attachments']['4']->foreign_key."/$imageName";
				}
				
			}
			
			if(array_key_exists(4,$user['attachments'])){
				echo $this->Html->link($user['attachments']['4']->attachment, $imageURL, array('target'=>'_blank', 'fullBase' => true));echo "&nbsp;&nbsp;&nbsp";
				$att_id = $user['attachments']['4']->id;
				echo $this->Html->link('Delete', "#", array("onclick" => "test($att_id)"));
			}
			
			?>
			
		</dd>
		<dt><?php echo __('Active'); ?></dt>
		<dd>
			<?php echo $active[$user->active]; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo date('d-m-Y h:i:s',strtotime($user->created));   ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo date('d-m-Y h:i:s',strtotime($user->modified)); ?>
			&nbsp;
		</dd>
	</dl>
</div>
 <div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit User'), array('action' => 'edit', $user->id)); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete User'), array('action' => 'delete', $user->id), array(), __('Are you sure you want to delete # %s?', $user->id)); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Groups'), array('controller' => 'groups', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Group'), array('controller' => 'groups', 'action' => 'add')); ?> </li>
	</ul>
</div>
 <script>
	function test(att_id){
		var user_id = $("#user_id").val();
		var targeturl = $("#delte_image").val();
		targeturl += '?user_id='+user_id;
		targeturl += '&att_id='+att_id;
		
		$.blockUI({ message: 'Updating cart...' });
		$.ajax({
					type: 'get',
					url: targeturl,
					beforeSend: function(xhr) {
						xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
					},
					
					success: function(response) {
						var objArr = $.parseJSON(response);
						alert(objArr.msg);
						location.reload();
					},
					error: function(e) {
						$.unblockUI();
						alert("An error occurred: " + e.responseText.message);
						console.log(e);
					}
		});
	}
 </script>