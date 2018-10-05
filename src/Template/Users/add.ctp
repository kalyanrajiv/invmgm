<div class="users form">
	<?php  
  $inputData = '';
   
	if(!empty($this->request->data)){
		//pr($this->request->data);//die;
		$inputData = $this->request->data;
		//pr($inputData);
		if(array_key_exists('kiosk_assigned',$inputData['User'])){
				 $mbCondition = explode('|',$inputData['User']['kiosk_assigned']);
		}
	}
  ?>
 
<?php
	$url = $this->Url->build(array('controller' => 'customers', 'action' => 'get_address'));
	echo $this->Form->create($user,array("onsubmit"=>"return checkFunction();")); ?>
	<fieldset>
		<legend><?php echo __('Add User'); ?></legend>
	<?php
		echo "<table>";
			echo "<tr>";
				echo "<td>";
					echo $this->Form->input('User.f_name',array('label'=>'First Name'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('User.l_name',array('label'=>'Last Name'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('User.email');
				echo "</td>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td>";
					echo $this->Form->input('User.username');
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('User.password');
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('User.confirm_password', array('type' => 'password'));
				echo "</td>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td>";
				
				echo "<b>Visa Expiry Date</b>";echo "</br>";echo "</br>";
				echo $this->Form->year('Profile.visa_expiry_date',[ 'minYear' => date('Y')-20,
																   'maxYear' => date('Y')+20,
																   'empty' => false,
																   ]);
				echo $this->Form->month('Profile.visa_expiry_date',['empty' => false]);
				echo $this->Form->day('Profile.visa_expiry_date',['empty' => false]);
					//echo $this->Form->input('Profile.visa_expiry_date',array('minYear' => date('Y')));
				echo "</td>";
				
				echo "<td>";
					echo $this->Form->input('User.start_from',array('minYear' => date('Y')-15,'maxYear'=>date('Y')));
				echo "</td>";
				
				echo "<td>";
				
				echo "<b>Date Of Birth</b>";echo "</br>";echo "</br>";
				echo $this->Form->year('Profile.date_of_birth',[ 'minYear' => date('Y')-55,
																   'maxYear' => date('Y')-15,
																   'empty' => false,
																   ]);
				echo $this->Form->month('Profile.date_of_birth',['empty' => false]);
				echo $this->Form->day('Profile.date_of_birth',['empty' => false]);
				
					//echo $this->Form->input('Profile.date_of_birth',array('minYear' => date('Y')-55,'maxYear' => date('Y')-15));
				echo "</td>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td>";
					echo "<table>";
					echo "<tr>";
						echo "<td>";
						echo $this->Form->input('User.zip',array('placeholder' => 'Postcode', 'label'=>false, 'rel' => $url,'size'=>'10px','style'=>'width: 120px;'));
						echo "</td>";
						echo "<td>";
						echo "<button type='button' id='find_address' class='btn' style='margin-top: 6px;margin-left: -8px;width: 130px;height: 29px;'>Find my address</button>";
						echo "</td>";
					echo "</tr>";
					echo "</table>";
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('User.address_1', array('placeholder' => 'property name/no. and street name'));
	?>
				<select name='street_address' id='street_address'><option>--postcode--</option></select>
				<span id='address_missing'><br/><a href='#-1'>Address is not on the list</a></span>
				
				</td>
	<?php
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('User.address_2', array('placeholder' => "further address details (optional)"));
				echo "</td>";
			echo "</tr>";
			
			
			
			echo "<tr>";
				echo "<table>";
					echo "<tr>";
						echo "<td>";
							echo $this->Form->input('User.city',array('label' => 'Town/City','placeholder' => "name of town or city"));
						echo "</td>";
						echo "<td>";
							echo $this->Form->input('User.state',array('label'=>'County', 'placeholder' => "name of county (optional)"));
						echo "</td>";
						echo "<td>";
							echo $this->Form->input('User.country',array('options'=>$countryOptions));
						echo "</td>";
						echo "<td>";
							echo $this->Form->input('User.mobile',array('id' => 'UserMobile','maxLength'=>'11'));
						echo "</td>";
					echo "</tr>";
				echo "</table>";
			echo "</tr>";
			
			
			
			echo "<tr>";
				echo "<table>";
					echo "<tr>";
						echo "<td>";
							echo $this->Form->input('Profile.national_insurance');
						echo "</td>";
						echo "<td>";
							echo $this->Form->input('Profile.visa_type', array('options' => $visaOptions));
						echo "</td>";
						echo "<td onchange=hideRadio();>";
							echo $this->Form->input('User.group_id',array('id' => 'UserGroupId'));
						echo "</td>";
						echo "<td rowspan='2'>";
							echo $this->Form->input('Profile.memo',array('type' => 'textarea'));
						echo "</td>";
					echo "</tr>";
					echo "<tr>";
						echo "<td>";
							echo "";
						echo "</td>";
						echo "<td>";
							echo "";
						echo "</td>";
						echo "<td>";
							echo "<span id='user_type'><input type='radio' name='User[user_type]' value='retail' id='retail'>Retail Kiosk<br/><br/>";
							echo "<input type='radio' name='User[user_type]' value='wholesale' id='wholesale'>Wholesale Kiosk</span>";
						echo "</td>";
					echo "</tr>";
				echo "</table>";
			echo "</tr>";
			echo "<tr>";
			echo "<td>";
			 
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
		 // $mobileConditions['1000'] = 'Other';
				$chunks = array_chunk($kiosk_list,6,true);
				 if(count($chunks)){
					echo "<table id = 'mobile_condition_table'>";
					echo "<tr>";
						echo "<td colspan='8'>";
							echo ("<h4>Kiosk List</h4><hr/>");
						echo "</td>";
					echo "</tr>";
					echo "<tr>";
					foreach($chunks as $c => $chunk){	
						echo "<td>";
						foreach($chunk as $ch => $condition){
							if(!empty($inputData) && array_key_exists('kiosk_assigned',$inputData['User'])){
									 if(is_array($inputData['User']['kiosk_assigned'])){
										 $mbCondition = $inputData['User']['kiosk_assigned'];
									}else{
										$mbCondition = explode('|',$inputData['User']['kiosk_assigned']);
									}
									
									if(array_key_exists('kiosk_assigned',$inputData['User']) && in_array($ch,$mbCondition)){
										 $checked = "checked";	
									}else{
										if($inputData['User']['kiosk_assigned'] =='-1'){
											$checked = "checked";
										}else{
											$checked = '';
										}
										
									}
							}else{
								$checked = '';
							}
							 
							echo $this->Form->input($condition, array('type' => 'checkbox',
								'name'=>'User[kiosk_assigned][]',
								'label' => array('style' => "color: blue;"),
								'value' => $ch,
								'hiddenField' => false,
								'checked' => $checked,
								'class' =>'checkbox1'
								));
						}
						echo "<td>";
					}
					  echo "</tr>";
		   echo "</table>";
				}
		  }
		 
			echo "</tr>";
			echo "<tr>";
				echo "<table>";
		echo "</table>";
		
		/*echo $this->Form->input('Image.0.attachment', array('type' => 'file', 'label' => 'Document 1'));
		echo $this->Form->input('Image.0.model', array('type' => 'hidden', 'value' => 'User'));
		echo $this->Form->input('Image.1.attachment', array('type' => 'file', 'label' => 'Document 2'));
		echo $this->Form->input('Image.1.model', array('type' => 'hidden', 'value' => 'User'));
		echo $this->Form->input('Image.2.attachment', array('type' => 'file', 'label' => 'Document 3'));
		echo $this->Form->input('Image.2.model', array('type' => 'hidden', 'value' => 'User'));
		echo $this->Form->input('Image.3.attachment', array('type' => 'file', 'label' => 'Document 4'));
		echo $this->Form->input('Image.3.model', array('type' => 'hidden', 'value' => 'User'));
		echo $this->Form->input('Image.4.attachment', array('type' => 'file', 'label' => 'Document 5'));
		echo $this->Form->input('Image.4.model', array('type' => 'hidden', 'value' => 'User'));*/
		#echo $this->Form->input('active');
		echo $this->Form->select('User.status',$active,array('empty' => false));
	?>
	</fieldset>
<?php
echo $this->Form->submit("submit");
echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Users'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Groups'), array('controller' => 'groups', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Group'), array('controller' => 'groups', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
	
$(function() {
	$('#address_missing').click(function(){
		$('#street_address').hide();
		$('#user-address-1').show("");
		$('#user-address-1').val("");
		$('#user-address-2').val("");
		$('#user-city').val("");
		$('#user-state').val("");		
		$(this).hide();
	});
	$( "#street_address" ).select(function() {
		alert($( "#street_address" ).val());
		$('#user-address-1').val($( "#street_address" ).val());
		$('#user-address-1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$( "#street_address" ).change(function() {
		$('#user-address-1').val($( "#street_address" ).val());
		$('#user-address-1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$("#find_address").click(function() {
		var zipCode = $("#user-zip").val();
		//$.blockUI({ message: 'Just a moment...' });
		//focus++;
		//alert("focusout:"+zipCode);
		//$( "#focus-count" ).text( "focusout fired: " + focus + "x" );	
		var zipCode = $("#user-zip").val();
		var targeturl = $("#user-zip").attr('rel') + '?zip=' + escape(zipCode);		
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
						$('#user-address-1').hide("");
						$('#address_missing').show();
						var toAppend = '';
						$('#street_address').find('option').remove().end();
						$.each(obj.Street, function( index, value ) {
							//alert( index + ": " + value );
							toAppend += '<option value="'+value+'">'+value+'</option>';
						});
						$('#street_address').append(toAppend);
						$('#user-address-2').val(obj.Address2);
						$('#user-city').val(obj.Town);
						$('#user-state').val(obj.County);
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
	$('#user-address-1').show();
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
 
	    $('#selectall').click(function(event) {
		if(this.checked) { 
		    $('.checkbox1').each(function() {
				 $('input.checkbox1').attr('readonly','true');
				//$("input.checkbox1").attr('disabled','true');
			   this.checked = true;
			   });
		}else{
		    $('.checkbox1').each(function() {
				$("input.checkbox1").prop("readonly", false);
				this.checked = false;
				
				 
		    });        
		}
	    });
	  
	});
	
	
 
	</script>