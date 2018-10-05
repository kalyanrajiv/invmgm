<div class="users form">
<?php #pr($this->request['data']['User']['user_type']=="retail");
$url = $this->Url->build(array('controller' => 'customers', 'action' => 'get_address'));
	//$url = $this->Html->url(array('controller' => 'customers', 'action' => 'get_address'));
	//pr($this->request->data);
	 $inputData = array();
	// $selectall = '-1';
    // pr($this->request['data']); //die;
	if($this->request['data']){
	  if(array_key_exists('kiosk_assigned',$this->request['data'])){
		$inputDataStr = $this->request['data']['kiosk_assigned'];
		//pr($inputDataStr);
		 if(is_array(explode('|',$inputDataStr))){
				   $inputData =explode('|',$inputDataStr);
			}
	  }else{
		if(array_key_exists('kiosk_assigned',$this->request['data']['User'])){
		$inputDataStr = $this->request['data']['User']['kiosk_assigned'];
		//pr($inputDataStr);
		 if(is_array(explode('|',$inputDataStr))){
				   $inputData =explode('|',$inputDataStr);
			}
		}
	  }
	  
	 // pr($inputData);
 
	}
//	if(array_key_exists('kiosk_assigned',$this->request['data'])){
//      $functionStr = $this->request['data']['kiosk_assigned'];
//      if(!is_array($functionStr)){
//        $functionArr = explode('|',$functionStr);
//      }else{
//        $functionArr = $functionStr;
//      }
//    }else{
//      $functionArr = array();
//    }
	echo $this->Form->create($user, array('type' => 'file')); ?>
	<fieldset>
		<legend><?php echo __('Edit User'); ?></legend><br/>
	<?php  echo $this->Html->link('Change Password', array('controller' => 'users', 'action' => 'change_password', 'full_base' => true,
                                                           $user->id));?>
	<?php
		echo $this->Form->input('id',array('name' => 'User[id]'));
		echo $this->Form->input('role',array('type' => 'hidden','name' => 'User[role]'));
		if(!empty($this->request['data']['profiles'])){
			echo $this->Form->input('Profile.id', array('name' => 'Profile[profile_id]', 'type' => 'hidden','value' => $this->request['data']['profiles'][0]['id']));
		}
		echo "<table>";
			echo "<tr>";
				echo "<td>";
					echo $this->Form->input('f_name',array('label'=>'First Name','name' => 'User[f_name]'));				
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('l_name',array('label'=>'Last Name','name' => 'User[l_name]'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('email',array('name' => 'User[email]'));
				echo "</td>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td>";
					if( $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER && $user->username!='admin'){
						echo $this->Form->input('username',array('label' => 'Username','name' => 'User[username]'));
					}else{
						echo $this->Form->input('username',array('label' => 'Username', 'readonly'=> true,'name' => 'User[username]'));	
					}
				echo "</td>";
				echo "<td>";
					echo "";
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('mobile',array('maxLength'=>'11','name' => 'User[mobile]'));
				echo "</td>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td>";
					echo "<table>";
						echo "<tr>";
							echo "<td>";
							echo $this->Form->input('zip',array('placeholder' => 'Postcode',
																'name' => 'User[zip]',
																'label'=>false,
																'rel' => $url,
																'size'=>'10px',
																'style'=>'width: 120px;'));
							echo "</td>";
							echo "<td>";
							echo "<button type='button' id='find_address' class='btn' style='margin-top: 6px;margin-left: -8px;width: 130px;height: 29px;'>Find my address</button>";
							echo "</td>";
						echo "</tr>";
					echo "</table>";
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('address_1', array('name' => 'User[address_1]','placeholder' => 'property name/no. and street name'));
	?>
				<select name='street_address' id='street_address'><option>--postcode--</option></select>
				<span id='address_missing'><br/><a href='#-1'>Address is not on the list</a></span>
	<?php
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('address_2', array('name' => 'User[address_2]','placeholder' => "further address details (optional)"));
				echo "</td>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td>";
					echo $this->Form->input('city',array('name' => 'User[city]','label' => 'Town/City','placeholder' => "name of town or city"));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('state',array('name' => 'User[state]','label'=>'County', 'placeholder' => "name of county (optional)"));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('country',array('name' => 'User[country]','options'=>$countryOptions));
				echo "</td>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td>";
					echo $this->Form->input('start_from',array('name' => 'User[start_from]','minYear' => date('Y')-15,'maxYear'=>date('Y')));
				echo "</td>";
				echo "<td>";
				echo "<b>Date Of Birth</b>";echo "</br>";echo "</br>";
				if(!empty($this->request->data['profiles'])){
					$date_of_birth = $this->request->data['profiles'][0]['date_of_birth'];
						echo $this->Form->year('Profile.date_of_birth',[ 'minYear' => date('Y')-55,
																		   'maxYear' => date('Y')-15,
																		   'empty' => false,
																		   'value' => date('Y',strtotime($date_of_birth))
																		   ]);
						echo $this->Form->month('Profile.date_of_birth',['empty' => false,'value' => date('m',strtotime($date_of_birth))]);
						echo $this->Form->day('Profile.date_of_birth',['empty' => false,'value' => date('d',strtotime($date_of_birth))]);
					//echo $this->Form->input('Profile.date_of_birth', array('minYear' => date('Y')-55,'maxYear'=> date('Y')-15));
					echo "</td>";
					echo "<td>";
						echo $this->Form->input('Profile.national_insurance',array('value' =>$this->request->data['profiles'][0]['national_insurance']));
					echo "</td>";
				echo "</tr>";
				
				echo "<tr>";
					echo "<td>";
					echo $this->Form->input('Profile.visa_type', array('options' => $visaOptions,'value' =>$this->request->data['profiles'][0]['visa_type']));
						//echo $this->Form->input('Profile.visa_type', array('options' => $visaOptions));
					echo "</td>";
					echo "<td>";
						//echo $this->Form->input('Profile.visa_expiry_date');
						echo "<b>Visa Expiry Date</b>";echo "</br>";echo "</br>";
						$visa_date = $this->request->data['profiles'][0]['visa_expiry_date'];
					echo $this->Form->year('Profile.visa_expiry_date',[ 'minYear' => date('Y')-20,
																	   'maxYear' => date('Y')+20,
																	   'value' => date('Y',strtotime($visa_date)),
																	   'empty' => false,
																	   ]);
					echo $this->Form->month('Profile.visa_expiry_date',['empty' => false,'value' => date('m',strtotime($visa_date)),]);
					echo $this->Form->day('Profile.visa_expiry_date',['empty' => false,'value' => date('d',strtotime($visa_date)),]);
					echo "</td>";
					echo "<td rowspan='2'>";
						echo $this->Form->input('Profile.memo',array('type' => 'textarea','value' =>$this->request->data['profiles'][0]['memo']));
					echo "</td>";
				}else{
					//$date_of_birth = $this->request->data['profiles'][0]['date_of_birth'];
						echo $this->Form->year('Profile.date_of_birth',[ 'minYear' => date('Y')-55,
																		   'maxYear' => date('Y')-15,
																		   'empty' => false,
																		   //'value' => date('Y',strtotime($date_of_birth))
																		   ]);
						echo $this->Form->month('Profile.date_of_birth',['empty' => false]);
						echo $this->Form->day('Profile.date_of_birth',['empty' => false]);
					//echo $this->Form->input('Profile.date_of_birth', array('minYear' => date('Y')-55,'maxYear'=> date('Y')-15));
						echo "</td>";
						echo "<td>";
							echo $this->Form->input('Profile.national_insurance');
						echo "</td>";
					echo "</tr>";
					
					echo "<tr>";
						echo "<td>";
						echo $this->Form->input('Profile.visa_type', array('options' => $visaOptions));
							//echo $this->Form->input('Profile.visa_type', array('options' => $visaOptions));
						echo "</td>";
						echo "<td>";
							//echo $this->Form->input('Profile.visa_expiry_date');
							echo "<b>Visa Expiry Date</b>";echo "</br>";echo "</br>";
							//$visa_date = $this->request->data['profiles'][0]['visa_expiry_date'];
						echo $this->Form->year('Profile.visa_expiry_date',[ 'minYear' => 2000,
																		   'maxYear' => date('Y'),
																		   //'value' => date('Y',strtotime($visa_date)),
																		   'empty' => false,
																		   ]);
						echo $this->Form->month('Profile.visa_expiry_date',['empty' => false]);
						echo $this->Form->day('Profile.visa_expiry_date',['empty' => false]);
						echo "</td>";
						echo "<td rowspan='2'>";
							echo $this->Form->input('Profile.memo',array('type' => 'textarea'));
						echo "</td>";
				}
				
				
			echo "</tr>";
			echo "<tr>";
						echo "<td onchange = hideRadio();>";
							echo $this->Form->input('group_id',array('id' => 'UserGroupId','name' => 'User[group_id]'));
						echo "</td>";
						echo "<td>";?>
							<span id='user_type'><input type='radio' name='User[user_type]' value='retail' id='retail' <?php if($user->user_type=="retail"){echo "checked";}?>>Retail Kiosk<br/><br/>
							<input type='radio' name='User[user_type]' value='wholesale' id='wholesale' <?php if($user->user_type=="wholesale"){echo "checked";}?>>Wholesale Kiosk</span>
			<?php
						echo "</td>";
					echo "</tr>";
			echo "<tr>";
			 echo "</table>";
            echo "<tr>";
			echo "<td>";  //pr($this->request['data']);
			// pr($inputData);
			if(!empty($this->request['data'])){
				if(array_key_exists('kiosk_assigned',$this->request['data'] )){
					if($this->request['data']['kiosk_assigned'] == -1){
					$checked = "checked";
				  }else{
					 $checked = "";
				  }
			   }
			   if(array_key_exists('User',$this->request['data']  )){
				  if($this->request['data']['User']['kiosk_assigned'] == -1){
					$checked = "checked";
				  }else{
					 $checked = "";
				  }
			   }
			}else{
			 	$checked = ' ';
			 	 
			 }
			echo "<input type = 'checkbox' class='checkbox' value = '-1'   name = 'User[selectall]' id = 'selectall' $checked >SelectAll";
			echo "</td>";
						 
			echo "</tr>";
		  echo "<tr>";
		  if(count($kiosk_list)){
			$kioskChunks = array_chunk($kiosk_list,6,true);
			if(count($kioskChunks)){
			  echo "<table id= 'kiosk_list_table'>";
				  echo "<tr>";
					  echo "<td colspan = '4'>";
						  echo ("<h4>Kiosk List</h4><hr/>");
					  echo "</td>";
				  echo "</tr>";
				  echo "<tr>";
				  
					  foreach($kioskChunks as $f => $Fchunk){
						 echo "<td>";
						  foreach($Fchunk as $fch => $kioskCondition){
							 $checked = '';
						 	if(in_array($fch,$inputData)){
								$checked = "checked";
							}
							echo $this->Form->input($kioskCondition, array('type' => 'checkbox',
																			 'name'=>'User[kiosk_assigned][]',
																			  'label' => array('style' => "color: blue;"),
																			  'value' => $fch,
																			  'class' => 'checkbox1',
																			  'hiddenField' => false,
																			  'checked' => $checked 
																			   
													));
							
						  }
						  echo "</td>";
					  }
					  
				  echo "</tr>";
			  echo "</table>";
		  }
		}
			echo "</tr>";
			echo "<tr>";
				echo "<table>";
				echo "<tr>";
				echo "<td>";
					echo $this->Form->input('Image.0.attachment', array('type' => 'file', 'label' => 'Document 1'));
					echo $this->Form->input('Image.0.model', array('type' => 'hidden', 'value' => 'User'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('Image.1.attachment', array('type' => 'file', 'label' => 'Document 2'));
					echo $this->Form->input('Image.1.model', array('type' => 'hidden', 'value' => 'User'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('Image.2.attachment', array('type' => 'file', 'label' => 'Document 3'));
					echo $this->Form->input('Image.2.model', array('type' => 'hidden', 'value' => 'User'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('Image.3.attachment', array('type' => 'file', 'label' => 'Document 4'));
					echo $this->Form->input('Image.3.model', array('type' => 'hidden', 'value' => 'User'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('Image.4.attachment', array('type' => 'file', 'label' => 'Document 5'));
					echo $this->Form->input('Image.4.model', array('type' => 'hidden', 'value' => 'User'));
				echo "</td>";
				echo "</tr>";
				echo "</table>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td>";
                //echo $this->Form->select('User.status',$active,array('empty' => false));
					echo $this->Form->select('active',$active,array('name' =>'User[active]', 'empty' => false));
				echo "</td>";
			echo "</tr>";
		echo "</table>";
	?>
	</fieldset>
<?php echo $this->Form->submit("submit",array('name'=>'submit'));
echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('User.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('User.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Users'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Groups'), array('controller' => 'groups', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Group'), array('controller' => 'groups', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
$(function() {
	$('#address_missing').click(function(){
		$('#street_address').hide();
		$('#address-1').show("");
		$('#address-1').val("");
		$('#address-2').val("");
		$('#city').val("");
		$('#state').val("");		
		$(this).hide();
	});
	$( "#street_address" ).select(function() {
		alert($( "#street_address" ).val());
		$('#address-1').val($( "#street_address" ).val());
		$('#address-1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$( "#street_address" ).change(function() {
		$('#address-1').val($( "#street_address" ).val());
		$('#address-1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$("#find_address").click(function() {
		var zipCode = $("#zip").val();
		//$.blockUI({ message: 'Just a moment...' });
		//focus++;
		//alert("focusout:"+zipCode);
		//$( "#focus-count" ).text( "focusout fired: " + focus + "x" );	
		var zipCode = $("#zip").val();
		var targeturl = $("#zip").attr('rel') + '?zip=' + escape(zipCode);		
		$.blockUI({ message: 'Just a moment...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
			    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				$.unblockUI();
				var obj = jQuery.parseJSON( response);			
				if (response) {
					if (obj.ErrorNumber == 0) {
						$('#street_address').show();
						$('#address-1').hide("");
						$('#address_missing').show();
						var toAppend = '';
						$('#street_address').find('option').remove().end();
						$.each(obj.Street, function( index, value ) {
							//alert( index + ": " + value );
							toAppend += '<option value="'+value+'">'+value+'</option>';
						});
						$('#street_address').append(toAppend);
						$('#address-2').val(obj.Address2);
						$('#city').val(obj.Town);
						$('#state').val(obj.County);
					}else{
						alert("Error Code: "+obj.ErrorNumber+ ", Error Message: "+ obj.ErrorMessage);
					}					
				}
			},
			error: function(e) {
			    $.unblockUI();
			    alert("Error:"+response);
			    alert("An error occurred: " + e.responseText.message);
			    console.log(e);
			}
		});
	});
	$('#address-1').show();
	$('#street_address').hide();
	$('#address_missing').hide();
	//----------------------------
	$("#UserMobile").keydown(function (event) {  
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
			(event.keyCode >= 96 && event.keyCode <= 105) ||
			event.keyCode == 8 || event.keyCode == 9 ||
			event.keyCode == 37 || event.keyCode == 39 ||
			event.keyCode == 46 || event.keyCode == 183) {
			;
		} else {
			event.preventDefault();
		}
    });
	//----------------------------
});
	function hideRadio(){
	       if(document.getElementById("UserGroupId").value==3){
		       document.getElementById('user_type').style.display='block';
	       }else if(document.getElementById("UserGroupId").value==1){
		       document.getElementById('user_type').style.display='none';
	       }else if(document.getElementById("UserGroupId").value==2){
		       document.getElementById('user_type').style.display='none';
	       }else if(document.getElementById("UserGroupId").value==4){
		       document.getElementById('user_type').style.display='none';
	       }else if(document.getElementById("UserGroupId").value==5){
		       document.getElementById('user_type').style.display='none';
	       }else if(document.getElementById("UserGroupId").value==6){
		       document.getElementById('user_type').style.display='none';
	       }else if(document.getElementById("UserGroupId").value==7){
		       document.getElementById('user_type').style.display='none';
	       }else if(document.getElementById("UserGroupId").value==8){
		       document.getElementById('user_type').style.display='none';
	       }
	}

	window.onload = function(){
		hideRadio();
	};
	
	function checkFunction(){
		if(!document.getElementById("retail").checked &&
		   !document.getElementById("wholesale").checked &&
		   document.getElementById("UserGroupId").value==3
		   ){
			alert("Please choose the kiosk type!");
			return false;
		}
	}
	
</script>
<script>
	$(document).ready(function() {
		  var chk = document.getElementById("selectall");
        if (chk.checked) {
             $('.checkbox1').each(function() {
				 $('input.checkbox1').attr('disabled','true');
				//$("input.checkbox1").attr('disabled','true');
			   this.checked = true;
			   });
        }  
	    $('#selectall').click(function(event) {  
		if(this.checked) { 
		    $('.checkbox1').each(function() {
				 $('input.checkbox1').attr('disabled','true');
				//$("input.checkbox1").attr('disabled','true');
			   this.checked = true;
			   });
		}else{
		    $('.checkbox1').each(function() {
				$("input.checkbox1").prop("disabled", false);
				this.checked = false;
				
				 
		    });        
		}
	    });
	  
	});
	
 
	</script>