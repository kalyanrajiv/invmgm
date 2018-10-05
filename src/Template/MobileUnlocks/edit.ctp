<?php
use Cake\I18n\Time;
?>
<div class="mobileUnlocks form" id = "unlock_form">
<?php

//pr($this->request->data);
//die;
$url = $this->Url->build(array('controller' => 'mobile_unlocks', 'action' => 'get_models'));
$priceURL = $this->Url->build(array('controller' => 'mobile_unlocks', 'action' => 'get_unlock_price'));
$networkOptions = $this->Url->build(array('controller' => 'mobile_unlocks', 'action' => 'get_network_options'));
echo $this->Form->create($mobile_unlocks_res, array('onSubmit' => 'return validateForm();')); ?>
	<fieldset>
		<legend><?php echo __('Edit Mobile Unlock'); ?> <?php echo "<span style='color:blue'>(".$this->request['data']['id'].")</span> ";?></legend>
		<div id="error_div" tabindex='1'></div>
	<?php
               $rec_at =  $this->request->data['received_at'];
                 $rec_at->i18nFormat(
                                           [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                   );
				 $res_of_dat =  $rec_at->i18nFormat('dd-MM-yyyy HH:mm:ss');
            $rec_at_org = date("Y-m-d h:i:s",strtotime($res_of_dat));
		echo $this->Form->hidden('id',array('id' => 'MobileUnlockId','name' => 'MobileUnlock[id]'));
		echo $this->Form->input('unlock_number', array('id' => 'MobileUnlockUnlockNumber','type' => 'hidden','name' => 'MobileUnlock[unlock_number]'));
		echo $this->Form->input('kiosk_id', array('id' => 'MobileUnlockKioskId','type' => 'hidden','name' => 'MobileUnlock[kiosk_id]'));
		echo $this->Form->input('received_at', array('id' => 'MobileUnlockReceivedAt','type' => 'hidden','name' => 'MobileUnlock[received_at]','value' => $rec_at_org));
		echo $this->Form->input('actual_cost', array('id' => 'MobileUnlockActualCost','type' => 'hidden','name' => 'MobileUnlock[actual_cost]'));
		
		if(($this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER)
		   /*&&
		   ($this->request['data']['MobileUnlock']['status'] == VIRTUALLY_BOOKED ||
		    $this->request['data']['MobileUnlock']['status'] == BOOKED)*/
		   ){
			
			//customer details
			echo ('<h4>Customer Details</h4><hr/>');
			echo "<table>";
				echo "<tr>";
					echo "<td>".$this->Form->input('customer_fname', array('id' => 'MobileUnlockCustomerFname','name' => 'MobileUnlock[customer_fname]','label' => 'First Name'))."</td>";
					echo "<td>".$this->Form->input('customer_lname', array('id' => 'MobileUnlockCustomerLname','name' => 'MobileUnlock[customer_lname]','label' => 'Last Name'))."</td>";
					echo "<td>".$this->Form->input('customer_contact',array('id' => 'MobileUnlockCustomerContact','name' => 'MobileUnlock[customer_contact]','label' => 'Mobile/Phone','maxlength' => '11'))."</td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td>".$this->Form->input('customer_email',array('id' => 'MobileUnlockCustomerEmail','name' => 'MobileUnlock[customer_email]'))."</td>";
					echo "<td>".$this->Form->input('zip',array('id' => 'MobileUnlockZip','name' => 'MobileUnlock[zip]','label' => 'Postal Code'))."</td>";
					echo "<td>".$this->Form->input('customer_address_1',array('id' => 'MobileUnlockCustomerAddress1','name' => 'MobileUnlock[customer_address_1]'))."</td>";		
				echo "</tr>";
				echo "<tr>";
					echo "<td colspan='3'>";
						echo "<table>";
							echo "<tr>";
								echo "<td>".$this->Form->input('customer_address_2',array('id' => 'MobileUnlockCustomerAddress2','name' => 'MobileUnlock[customer_address_2]'))."</td>";
								echo "<td>".$this->Form->input('city',array('id' => 'MobileUnlockCity','name' => 'MobileUnlock[city]'))."</td>";
								echo "<td>".$this->Form->input('state',array('id' => 'MobileUnlockState','name' => 'MobileUnlock[state]'))."</td>";
								echo "<td>".$this->Form->input('country',array('id' => 'MobileUnlockCountry','name' => 'MobileUnlock[country]','options'=>$countryOptions))."</td>";
							echo "</tr>";
						echo "</table>";
					echo "</td>";
				echo "</tr>";
			echo "</table>";
			
			//phone details
			echo ('<h4>Mobile Details</h4><hr/>');
			echo $this->Form->input('brand_id',array('id' => 'MobileUnlockBrandId','name'=> 'MobileUnlock[brand_id]', 'options' => $brands, 'rel' => $url,'id' => 'MobileUnlockBrandId'));
			echo $this->Form->input('mobile_model_id',array('id' => 'MobileUnlockMobileModelId','name'=> 'MobileUnlock[mobile_model_id]','options' => $mobileModels, 'rel' => $networkOptions,'id' => 'MobileUnlockMobileModelId'));
			echo $this->Form->input('network_id',array('id' => 'MobileUnlockNetworkId','name'=> 'MobileUnlock[network_id]','options' => $networks, 'rel' => $priceURL,'empty' => 'choose network','id' => 'MobileUnlockNetworkId'));
			echo $this->Form->input('cst', array(
										'type' => 'hidden',
										//'style'=>'width: 50%',
										'name' => 'MobileUnlock[net_cost]',
										//'readonly' => true,
										'id' => 'net_cost',
										'value' => $this->request->data['net_cost'],
										'div' => false,
										//'required' => 'required',
										));
			echo $this->Form->input('status_freezed', array('id' => 'MobileUnlockStatusFreezed','name' => 'MobileUnlock[status_freezed]','type'=>'checkbox'));
			$networkId = $this->request->data['network_id'];
			if(array_key_exists($networkId,$costArr)){
				$cost = (int)$costArr[$networkId];
				for($i = 1;$i <=$cost+50;$i++){
					$options[0][$i] = $i;
				}
			}else{
				$options[0] = array();
			}
			
			if(array_key_exists('MobileUnlock',$this->request->data)){
			  if(array_key_exists('internal_unlock',$this->request->data['MobileUnlock'])){
				if(!empty($this->request->data['MobileUnlock']['internal_unlock'])){
					$internal_unlock = $this->request->data['MobileUnlock']['internal_unlock'];
				}else{
					$internal_unlock = 0;
				}
			  }else{
				$internal_unlock = 0;
			  }
			}else{
			  $internal_unlock = 0;
			}
			if($internal_unlock == 1){
				if(!empty($options)){
				  $options[0][0] = .0001;
				  asort($options[0]);
				}
			}
			
			echo $this->Form->input('estimated_cost',array('style' => 'width:75px',
														   //'type' => 'text',
														   'name' => 'MobileUnlock[estimated_cost]',
														   'options' => $options[0],
														   'id'=>'unlocking_price'));
			
			
			echo $this->Form->input('estimated_cost_hidden', array('id' => 'MobileUnlockEstimatedCostHidden','type' => 'hidden', 'value' => $this->request->data['estimated_cost'], 'name' => 'MobileUnlock[estimated_cost_hidden]'));
			
			?><table>
				<tr>
					<td>
					<?php echo $this->Form->input('estimated_days',array('name' => 'MobileUnlock[estimated_days]','style' => 'width:75px','type'=>'text','value'=>$unlockingDays,'label'=>'Estimated Days', 'id'=>'unlocking_days'));?>	
					</td>
					<td>
						<?php echo $this->Form->input('estimated_minutes',array('name' => 'MobileUnlock[estimated_minutes]','style' => 'width:75px','type'=>'text','value'=>$unlockMinutes,'label'=>'Estimated Minutes', 'id'=>'unlocking_minutes')); ?>
					</td>
				</tr>
			</table><?php
			//echo $this->Form->input('estimated_days',array('style' => 'width:75px','type'=>'text','value'=>$unlockingDays,'label'=>'Estimated Days', 'id'=>'unlocking_days'));
			//echo $this->Form->input('estimated_minutes',array('style' => 'width:75px','type'=>'text','value'=>$unlockMinutes,'label'=>'Estimated Minutes', 'id'=>'unlocking_minutes'));
			echo '<div class="input input required">Imei<span style="color: red;">*</span><span id = "imei_quest" title="Please fill digit only. Incase serial number put in fault description!" style="background: steelblue;color: white;font-size: 14px;margin-left: 3px;">?</span></div>';
			//echo $this->Form->input('imei',array('label' => false, 'maxlength' => 16, 'div' => false,'style'=>'width: 449px;margin-left: 7px;'));
			echo $this->Form->input('admin',array('name' => 'MobileUnlock[admin]','type' => 'hidden','value' => 1,'id' => 'admin'));
			echo "<table>";
			echo "<tr>";
				echo "<td>";
				$imei = $this->request->data['imei'];
				$imei1 = substr($imei, -1);
				$imei2 = substr_replace($imei,'',-1) ;
				echo $this->Form->input('imei',	array(
													  'label' => false,
													  'maxlength'=>14,
													  'div' => false,
													  'style'=>'width: 120px;height:25px; ',
													  'value' => $imei2,
													  'id' => 'MobileUnlockImei',
													  'name' => 'MobileUnlock[imei]'
													 
													  ));//,'style'=>"width: 449px;"
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('imei1',array(
														  'type' => 'text',
														  'label' => false,
														  'id' =>'imei1',
														  'name' => 'MobileUnlock[imei1]',
														  'readonly'=>'readonly',
														  'value' => $imei1,
														  'style'=>"width: 15px; margin-right: 670px; margin-top: -7px"));
		  
				echo "</td>";
			echo "</tr>";
			echo "</table>";
			echo $this->Form->input('code',array('id' => 'MobileUnlockToken','name' => 'MobileUnlock[code]','style' => 'width:50%','label' => 'Unlock Code'));
			echo $this->Form->input('unlock_code_instructions',array('id' => 'MobileUnlockUnlockCodeInstructions','name' => 'MobileUnlock[unlock_code_instructions]','style' => 'width:50%','type' => 'text', 'label' => 'Unlock code instructions'));
			#echo $this->Form->input('delivered_at',array('minYear' => date('Y'),'maxYear' => date('Y'),'empty' => true,'empty' => array('day' => 'DAY', 'month' => 'MONTH', 'year' => 'YEAR','hour' => 'Hour','minute' => 'Minute','meridian' => 'Meridian')));
			echo $this->Form->input('description',array('id' => 'MobileUnlockDescription','name' => 'MobileUnlock[description]','label' => 'Unlock Description', 'style'=>'width:50%'));
		}else{
			//customer details
			echo ('<h4>Customer Details</h4><hr/>');
			if($this->request->session()->read('Auth.User.group_id') == UNLOCK_TECHNICIANS){
				//change on 25-02-2016
				echo "<table>";
					echo "<tr>";
						echo "<td>".$this->Form->input('customer_fname', array('id' => 'MobileUnlockCustomerFname','name' => 'MobileUnlock[customer_fname]','label' => 'First Name', 'readonly' => 'readonly'))."</td>";
						echo "<td>".$this->Form->input('customer_lname', array('id' => 'MobileUnlockCustomerLname','name' => 'MobileUnlock[customer_lname]','label' => 'Last Name', 'readonly' => 'readonly'))."</td>";
						echo "<td>".$this->Form->input('customer_contact',array('id' => 'MobileUnlockCustomerContact','name' => 'MobileUnlock[customer_contact]','label' => 'Mobile/Phone', 'readonly' => 'readonly'))."</td>";
					echo "</tr>";
					echo "<tr>";
						echo "<td>".$this->Form->input('customer_email',array('id' => 'MobileUnlockCustomerEmail','name' => 'MobileUnlock[customer_email]','readonly' => 'readonly'))."</td>";
						echo "<td>".$this->Form->input('zip',array('id' => 'MobileUnlockZip','name' => 'MobileUnlock[zip]','label' => 'Postal Code', 'readonly' => 'readonly'))."</td>";
						echo "<td>".$this->Form->input('customer_address_1',array('id' => 'MobileUnlockCustomerAddress1','name' => 'MobileUnlock[customer_address_1]','readonly' => 'readonly'))."</td>";		
					echo "</tr>";
					echo "<tr>";
						echo "<td colspan='3'>";
							echo "<table>";
								echo "<tr>";
									echo "<td>".$this->Form->input('customer_address_2',array('id' => 'MobileUnlockCustomerAddress2','name' => 'MobileUnlock[customer_address_2]','readonly' => 'readonly'))."</td>";
									echo "<td>".$this->Form->input('city',array('id' => 'MobileUnlockCity','name' => 'MobileUnlock[city]','readonly' => 'readonly'))."</td>";
									echo "<td>".$this->Form->input('state',array('id' => 'MobileUnlockState','name' => 'MobileUnlock[state]','readonly' => 'readonly'))."</td>";
									echo "<td>".$this->Form->input('country',array('id' => 'MobileUnlockCountry','name' => 'MobileUnlock[country]','disabled' => 'disabled','options'=>$countryOptions))."</td>";
								echo "</tr>";
							echo "</table>";
						echo "</td>";
					echo "</tr>";
				echo "</table>";
			}else{
				echo "<table>";
					echo "<tr>";
						echo "<td>".$this->Form->input('customer_fname', array('id' => 'MobileUnlockCustomerFname','name' => 'MobileUnlock[customer_fname]','label' => 'First Name'))."</td>";
						echo "<td>".$this->Form->input('customer_lname', array('id' => 'MobileUnlockCustomerLname','name' => 'MobileUnlock[customer_lname]','label' => 'Last Name'))."</td>";
						echo "<td>".$this->Form->input('customer_contact',array('id' => 'MobileUnlockCustomerContact','name' => 'MobileUnlock[customer_contact]','label' => 'Mobile/Phone','maxlength' => '11'))."</td>";
					echo "</tr>";
					echo "<tr>";
						echo "<td>".$this->Form->input('customer_email',array('id' => 'MobileUnlockCustomerEmail','name' => 'MobileUnlock[customer_email]'))."</td>";
						echo "<td>".$this->Form->input('zip',array('id' => 'MobileUnlockZip','name' => 'MobileUnlock[zip]','label' => 'Postal Code'))."</td>";
						echo "<td>".$this->Form->input('customer_address_1',array('id' => 'MobileUnlockCustomerAddress1','name' => 'MobileUnlock[customer_address_1]'))."</td>";		
					echo "</tr>";
					echo "<tr>";
						echo "<td colspan='3'>";
							echo "<table>";
								echo "<tr>";
									echo "<td>".$this->Form->input('customer_address_2',array('id' => 'MobileUnlockCustomerAddress2','name' => 'MobileUnlock[customer_address_2]'))."</td>";
									echo "<td>".$this->Form->input('city',array('id' => 'MobileUnlockCity','name' => 'MobileUnlock[city]'))."</td>";
									echo "<td>".$this->Form->input('state',array('id' => 'MobileUnlockState','name' => 'MobileUnlock[state]'))."</td>";
									echo "<td>".$this->Form->input('country',array('id' => 'MobileUnlockCountry','name' => 'MobileUnlock[country]','options'=>$countryOptions))."</td>";
								echo "</tr>";
							echo "</table>";
						echo "</td>";
					echo "</tr>";
				echo "</table>";
			}
			//phone details
			echo ('<h4>Mobile Details</h4><hr/>');
			if($this->request->session()->read('Auth.User.group_id') == UNLOCK_TECHNICIANS){
				if($this->request->data["status_freezed"] != 1){
					$networkId = $this->request->data['network_id'];
					if(array_key_exists($networkId,$costArr)){
						$cost = (int)$costArr[$networkId];
						for($i = $cost;$i <=$cost+50;$i++){
							$options[0][$i] = $i;
						}
					}else{
						$options[0] = array();
					}
				}
				
				echo $this->Form->input('brand_id',array('id' => 'MobileUnlockBrandId','name' => 'MobileUnlock[brand_id]','options' => $brands, 'rel' => $url,'disabled' => 'disabled'));
				echo $this->Form->input('mobile_model_id',array('id' => 'MobileUnlockMobileModelId','name' => 'MobileUnlock[mobile_model_id]','options' => $mobileModels, 'rel' => $networkOptions));
				echo $this->Form->input('network_id',array('id' => 'MobileUnlockNetworkId','name' => 'MobileUnlock[network_id]','options' => $networks, 'rel' => $priceURL,'empty' => 'choose network'));
				if($this->request->data["status_freezed"] != 1){   //not in use
					echo $this->Form->input('estimated_cost',array('style' => 'width:10%',
															   'type' => 'text',
															   'readonly' => 'readonly',
															   'name' => 'MobileUnlock[estimated_cost]',
															   // 'options' => $options[0],
															   'id' => 'unlocking_price_readonly'));
				}else{
					echo $this->Form->input('estimated_cost',array('style' => 'width:10%',
															   'type' => 'text',
															   'readonly' => 'readonly',
															   'name' => 'MobileUnlock[estimated_cost]',
															   // 'options' => $options[0],
															   'id' => 'unlocking_price_readonly'));
				}
				
				echo $this->Form->input('cst', array(
										'type' => 'hidden',
										//'style'=>'width: 50%',
										'name' => 'MobileUnlock[net_cost]',
										//'readonly' => true,
										'id' => 'net_cost',
										'value' => $this->request->data['net_cost'],
										'div' => false,
										//'required' => 'required',
										));
				
			}else{
				echo $this->Form->input('brand_id',array('id' => 'MobileUnlockBrandId','name' => 'MobileUnlock[brand_id]','options' => $brands,'disabled' => 'disabled'));
				echo $this->Form->input('mobile_model_id',array('id' => 'MobileUnlockMobileModelId','name' => 'MobileUnlock[mobile_model_id]','options' => $mobileModels,'disabled' => 'disabled'));
				echo $this->Form->input('network_id',array('id' => 'MobileUnlockNetworkId','name' => 'MobileUnlock[network_id]','options' => $networks,'disabled' => 'disabled'));
				echo $this->Form->input('estimated_cost',array('id' => 'MobileUnlockEstimatedCost','name' => 'MobileUnlock[estimated_cost]','style' => 'width:10%','readonly' => 'readonly','type' => 'text'));
			}
			?>
			<table>
				<tr>
					<td style="width: 50%;">
						<?php  echo $this->Form->input('null',array('style' => 'width:80%','type'=>'text','readonly' => 'readonly','value'=>$unlockingDays,'label'=>'Estimated Days','name' => 'MobileUnlock[null]')); ?>			
					</td>
					<td style="width: 50%;">
						<?php  echo $this->Form->input('null',array('style' => 'width:80%','type'=>'text','readonly' => 'readonly','value'=>$unlockMinutes,'label'=>'Estimated Minutes','name' => 'MobileUnlock[null]')); ?>			
					</td>
				</tr>
				<tr>
					<td colspan = 2 style="background-color: yellowgreen;">
						**Price freezed by admin. Please contact admin/manager to unfreeze price based on Brand,Model and Mobile Network
					</td>
				</tr>
			</table>
			 
			<?php
			//echo $this->Form->input('null',array('style' => 'width:50%','type'=>'text','readonly' => 'readonly','value'=>$unlockingDays,'label'=>'Estimated Days'));
			//echo $this->Form->input('null',array('style' => 'width:50%','type'=>'text','readonly' => 'readonly','value'=>$unlockMinutes,'label'=>'Estimated Minutes'));
			echo '<div class="input input required">Imei<span style="color: red;">*</span><span id = "imei_quest" title="Please fill digit only. Incase serial number put in fault description!" style="background: steelblue;color: white;font-size: 14px;margin-left: 3px;">?</span></div>';
			//echo $this->Form->input('imei',array('label' => false, 'maxlength' => 16, 'readonly' => 'readonly', 'div' => false,'style'=>'width: 449px;margin-left: 7px;'));
			echo "<table>";
			echo "<tr>";
				echo "<td>";
				$imei = $this->request->data['imei'];
				$imei1 = substr($imei, -1);
				$imei2 = substr_replace($imei,'',-1) ;
				echo $this->Form->input('imei',	array(
													  'label' => false,
													  'maxlength'=>14,
													  'div' => false,
													  'style'=>'width: 115px;height:26px; ',
													  'value' => $imei2,
													 'id' => 'MobileUnlockImei','name' => 'MobileUnlock[imei]',
													  ));//,'style'=>"width: 449px;"
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('imei1',array(
														  'type' => 'text',
														  'label' => false,
														  'id' =>'imei1',
														  'readonly'=>'readonly',
														  'value' => $imei1,
														  'name' => 'MobileUnlock[imei1]',
														  'style'=>"width: 15px; margin-right: 670px; margin-top: -7px"));
		  
				echo "</td>";
			echo "</tr>";
			echo "</table>";
			if($this->request->session()->read('Auth.User.group_id') == UNLOCK_TECHNICIANS){
				echo $this->Form->input('code',array('id' => 'MobileUnlockToken','name' => 'MobileUnlock[code]','style' => 'width:50%','label' => 'Unlock Code'));
				echo $this->Form->input('unlock_code_instructions',array('id' => 'MobileUnlockUnlockCodeInstructions','name' => 'MobileUnlock[unlock_code_instructions]','style' => 'width:50%','type' => 'text', 'label' => 'Unlock code instructions'));
			}else{
				echo $this->Form->input('code',array('id' => 'MobileUnlockToken','name' => 'MobileUnlock[code]','style' => 'width:50%','label' => 'Unlock Code','readonly' => 'readonly'));
				echo $this->Form->input('unlock_code_instructions',array('id' => 'MobileUnlockUnlockCodeInstructions','name' => 'MobileUnlock[unlock_code_instructions]','style' => 'width:50%','type' => 'text', 'label' => 'Unlock code instructions', 'readonly' => 'readonly'));
			}
			
			#echo $this->Form->input('delivered_at',array('minYear' => date('Y'),'maxYear' => date('Y'),'empty' => true,'empty' => array('day' => 'DAY', 'month' => 'MONTH', 'year' => 'YEAR','hour' => 'Hour','minute' => 'Minute','meridian' => 'Meridian')));
			if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
				echo $this->Form->input('description',array('id' => 'MobileUnlockDescription','name' => 'MobileUnlock[description]','label' => 'Unlock Description', 'style' => 'width:50%'));
			}else{
				echo $this->Form->input('description',array('id' => 'MobileUnlockDescription','name' => 'MobileUnlock[description]','label' => 'Unlock Description', 'readonly'=>'readonly','style'=>'width:50%'));
			}
		}
		
		echo ('<h4>Unlock Logs</h4><hr/>');	
		$unlockStatus = $unlockStatusUserOptions+$unlockStatusTechnicianOptions;
		?>
		
		<table>
			<?php //pr($unlockLogs);
			
            $count = 0;
			foreach($unlockLogs as $id => $unlockLog){
				  $unlockLog['created']->i18nFormat(
												[\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
											);
				$created =   $unlockLog['created']->i18nFormat('dd-MM-yyyy HH:mm:ss');
				$count++;
				if(!empty($unlockLog['comments'])){?>
				<tr>
					<td><?= $count; ?></td>
					<td>Comment Posted by <span style="color: crimson"><strong><?php if(array_key_exists($unlockLog['user_id'],$users)){
						$users[$unlockLog['user_id']];
						}else{
							echo "--";
						} 
						?></strong></span> &#40;comment id:<?=$unlockLog['comments'];?>&#41; on <?= $created;//$this->Time->format('M jS, Y g:i A', $unlockLog['created'],null,null); ?> for <span style="color: darkorange"><?= $kiosks[$unlockLog['kiosk_id']]; ?></span></td>
				</tr>
				<?php }else{ ?>
				<tr>
					<td><?= $count; ?></td>
					<td>Last updated by <span style="color: crimson"><strong><?php if(array_key_exists($unlockLog['user_id'],$users)){ echo $users[$unlockLog['user_id']];}else{echo "--";} ?></strong></span> on <?= $created;//$this->Time->format('M jS, Y g:i:s A', $unlockLog['created'],null,null); ?>, <span style="color: blue">Status: <?= $unlockStatus[$unlockLog['unlock_status']]; ?></span> for <span style="color: darkorange"><?= $kiosks[$unlockLog['kiosk_id']]; ?></span></td>
				</tr>
				<?php }
			}?>
		</table>
		
		
		<label for="MobileUnlockBriefHistory">
			<strong>Staff Comments</strong>
			<span style='padding-left: 63%;text-align:right'><?php echo $this->Html->link('Add New Comment', array('controller' => 'comment_mobile_unlocks', 'action' => 'add',$this->request->data['id']));?></span>
			<h6>(For Internal Use)</h6>
		</label>
		<?php
			$tableStr = "";
			$i =1;
			//pr($comments);
			foreach($comments as $sngComment){
				$comment = $sngComment['brief_history'];
				$commentID = $sngComment['id'];
				$postedOn = $sngComment['modified'];
				$postedOn = date('M jS, Y h:i A',strtotime($postedOn)); /*h:i A*/
				$postedBy = $sngComment['user']['username'];
				$userID = $sngComment['user']['id'];
				$truncatedcomment  = 
									\Cake\Utility\Text::truncate( $comment, 140, [ 'ellipsis' => '...', 'exact' => false ] );
				
				$tableStr.="";
				if(strlen($comment)>140){
					
					$userLink = "<span style='color: crimson'><strong>".$postedBy."</strong></span>";
					$commentLink = $this->Html->link('Edit', array('controller' => 'comment_mobile_unlocks','action' => 'edit', $commentID));
					$tableStr.="<td style='width: 90px'>$postedOn<br/></td>
					<td>$userLink</td>
					<td colspan='3'><a href = \"\" title = \"$comment\" alt = \"$comment\">$truncatedcomment</a></td>
					<td style='width: 1px;'>$commentLink</td></tr>";
				}else{
					
					$userLink = "<span style='color: crimson'><strong>".$postedBy."</strong></span>";
					$commentLink = $this->Html->link('Edit', array('controller' => 'comment_mobile_unlocks','action' => 'edit', $commentID));
					$tableStr.="<td style='width: 60px'>$postedOn<br/></td>
					<td>$userLink</td>
					
					<td colspan='3'>$comment</td>
					<td style='width: 1px;'>$commentLink</td></tr>";
				}
			  	
			}
			if(empty($tableStr)){
				$tableStr = "<tr><td><span style='color:red'>No Record Found!!!</span></td></tr>";
			}
			echo "<table cellspacing='2' cellpadding='2'><tr><td colspan='3'><h3>Comments:</h3></td></tr>$tableStr</table>"
		?>
		<?php
		//echo $this->Form->input('brief_history', array('label' => 'Unlocking History</br/>(For Internal Use)', 'disabled' => 'disabled'));
			#$this->Html->link(__('Add brief history'), array('controller' => ''));
			//pr($checkIfVirtuallyBooked);
		if($this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
			echo "<h3>Status</h3><h4>".$unlockStatus[$this->request->data['status']]."</h4>";
			echo $this->Form->input('send', array('type' => 'checkbox',  'name' => 'MobileUnlock[send]','label' => 'Send mail' ,  'value' =>'1'));
		}elseif($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){			
			if($this->request['data']['status'] == BOOKED){
				$removeKeys = array(BOOKED, VIRTUALLY_BOOKED, UNLOCK_REQUEST_SENT, UNLOCKED_CONFIRMATION_PASSED,UNLOCKING_FAILED_CONFIRMATION_PASSED, RECEIVED_UNLOCKED_FROM_CENTER, RECEIVED_UNPROCESSED_FROM_CENTER, REFUND_RAISED, DELIVERED_UNLOCKED_BY_CENTER, DELIVERED_UNLOCKING_FAILED_AT_CENTER);			
				foreach($removeKeys as $key){
					unset($unlockStatusUserOptions[$key]);
				}
			}
			
			if($this->request['data']['status'] == RECEIVED_UNLOCKED_FROM_CENTER){
				$removeKeys = array(BOOKED, VIRTUALLY_BOOKED, UNLOCK_REQUEST_SENT, DISPATCHED_2_CENTER, UNLOCKED_CONFIRMATION_PASSED, UNLOCKING_FAILED_CONFIRMATION_PASSED, RECEIVED_UNLOCKED_FROM_CENTER,RECEIVED_UNPROCESSED_FROM_CENTER, REFUND_RAISED,DELIVERED_UNLOCKING_FAILED_AT_CENTER, DELIVERED_UNLOCKED_BY_KIOSK, DELIVERED_UNLOCKING_FAILED_AT_KIOSK);			
				foreach($removeKeys as $key){
					unset($unlockStatusUserOptions[$key]);
				}
			}
			
			if($this->request['data']['status'] == UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK){
				$removeKeys = array(BOOKED, VIRTUALLY_BOOKED, UNLOCK_REQUEST_SENT, DISPATCHED_2_CENTER,UNLOCKING_FAILED_CONFIRMATION_PASSED, RECEIVED_UNLOCKED_FROM_CENTER,RECEIVED_UNPROCESSED_FROM_CENTER, REFUND_RAISED, DELIVERED_UNLOCKED_BY_CENTER, DELIVERED_UNLOCKING_FAILED_AT_CENTER, DELIVERED_UNLOCKED_BY_KIOSK,DELIVERED_UNLOCKING_FAILED_AT_KIOSK);			
				foreach($removeKeys as $key){
					unset($unlockStatusUserOptions[$key]);
				}
			}
			
			if($this->request['data']['status'] == DISPATCHED_2_KIOSK_UNLOCKED){
				$removeKeys = array(BOOKED, VIRTUALLY_BOOKED, UNLOCK_REQUEST_SENT, DISPATCHED_2_CENTER, UNLOCKED_CONFIRMATION_PASSED, UNLOCKING_FAILED_CONFIRMATION_PASSED,RECEIVED_UNPROCESSED_FROM_CENTER, REFUND_RAISED, DELIVERED_UNLOCKED_BY_CENTER, DELIVERED_UNLOCKING_FAILED_AT_CENTER, DELIVERED_UNLOCKED_BY_KIOSK,DELIVERED_UNLOCKING_FAILED_AT_KIOSK);			
				foreach($removeKeys as $key){
					unset($unlockStatusUserOptions[$key]);
				}
			}
			
			if($this->request['data']['status'] == DISPATCHED_2_KIOSK_UNPROCESSED ||
			   $this->request['data']['status'] == UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK){
				$removeKeys = array(BOOKED, VIRTUALLY_BOOKED, UNLOCK_REQUEST_SENT, DISPATCHED_2_CENTER, UNLOCKED_CONFIRMATION_PASSED, UNLOCKING_FAILED_CONFIRMATION_PASSED,RECEIVED_UNLOCKED_FROM_CENTER, REFUND_RAISED, DELIVERED_UNLOCKED_BY_CENTER, DELIVERED_UNLOCKING_FAILED_AT_CENTER, DELIVERED_UNLOCKED_BY_KIOSK,DELIVERED_UNLOCKING_FAILED_AT_KIOSK);			
				foreach($removeKeys as $key){
					unset($unlockStatusUserOptions[$key]);
				}
			}
			
			if($this->request['data']['status'] == RECEIVED_UNPROCESSED_FROM_CENTER &&
			   $checkIfVirtuallyBooked > 0){//if phone is virtually booked and unlock not processed showing option for refund
				$removeKeys = array(BOOKED, VIRTUALLY_BOOKED, UNLOCK_REQUEST_SENT, DISPATCHED_2_CENTER, UNLOCKED_CONFIRMATION_PASSED,RECEIVED_UNLOCKED_FROM_CENTER,RECEIVED_UNPROCESSED_FROM_CENTER, REFUND_RAISED, DELIVERED_UNLOCKED_BY_CENTER, DELIVERED_UNLOCKING_FAILED_AT_CENTER, DELIVERED_UNLOCKED_BY_KIOSK,DELIVERED_UNLOCKING_FAILED_AT_KIOSK);			
				foreach($removeKeys as $key){
					unset($unlockStatusUserOptions[$key]);
				}
			}elseif($this->request['data']['status'] == RECEIVED_UNPROCESSED_FROM_CENTER){
				$removeKeys = array(BOOKED, VIRTUALLY_BOOKED, UNLOCK_REQUEST_SENT, DISPATCHED_2_CENTER, UNLOCKED_CONFIRMATION_PASSED, UNLOCKING_FAILED_CONFIRMATION_PASSED, RECEIVED_UNLOCKED_FROM_CENTER,RECEIVED_UNPROCESSED_FROM_CENTER, REFUND_RAISED,DELIVERED_UNLOCKED_BY_CENTER, DELIVERED_UNLOCKED_BY_KIOSK, DELIVERED_UNLOCKING_FAILED_AT_KIOSK);			
				foreach($removeKeys as $key){
					unset($unlockStatusUserOptions[$key]);
				}
			}
			
			if($this->request['data']['status'] == VIRTUALLY_BOOKED ||
			   $this->request['data']['status'] == UNLOCK_REQUEST_SENT ||
			   $this->request['data']['status'] == DISPATCHED_2_CENTER ||
			   $this->request['data']['status'] == UNLOCKED_CONFIRMATION_PASSED ||			   
			   $this->request['data']['status'] == UNLOCKING_FAILED_CONFIRMATION_PASSED ||
			   $this->request['data']['status'] == REFUND_RAISED ||
			   $this->request['data']['status'] == DELIVERED_UNLOCKED_BY_CENTER ||
			   $this->request['data']['status'] == DELIVERED_UNLOCKING_FAILED_AT_CENTER ||
			   $this->request['data']['status'] == DELIVERED_UNLOCKED_BY_KIOSK ||
			   $this->request['data']['status'] == DELIVERED_UNLOCKING_FAILED_AT_KIOSK){
				echo "<h3>Status</h3><h4>".$unlockStatusUserOptions[$this->request['data']['status']]."</h4>";
			}elseif($this->request['data']['status'] == REQUEST_RECEIVED_IN_PROCESS ||
			   $this->request['data']['status'] == PHONE_RECEIVED_BY_CENTER ||
			   $this->request['data']['status'] == UNLOCK_UNDER_PROCESS ||
			   $this->request['data']['status'] == WAITING_FOR_DISPATCH_UNLOCKED){
				echo "<h3>Status</h3><h4>".$unlockStatusTechnicianOptions[$this->request['data']['status']]."</h4>";
			}
			else{
				echo $this->Form->input('status',array('id' => 'MobileUnlockStatus','name' => 'MobileUnlock[status]','options' => $unlockStatusUserOptions));		
			}
			
		}elseif($this->request->session()->read('Auth.User.group_id') == UNLOCK_TECHNICIANS){
			if($this->request['data']['status'] == VIRTUALLY_BOOKED){
				$removeKeys = array(PHONE_RECEIVED_BY_CENTER, UNLOCK_UNDER_PROCESS, WAITING_FOR_DISPATCH_UNLOCKED, UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK, DISPATCHED_2_KIOSK_UNLOCKED, DISPATCHED_2_KIOSK_UNPROCESSED,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK);			
				foreach($removeKeys as $key){
					unset($unlockStatusTechnicianOptions[$key]);
				}
			}
			
			if($this->request['data']['status'] == DISPATCHED_2_CENTER){
				$removeKeys = array(REQUEST_RECEIVED_IN_PROCESS,UNLOCK_UNDER_PROCESS, WAITING_FOR_DISPATCH_UNLOCKED, UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK, DISPATCHED_2_KIOSK_UNLOCKED, DISPATCHED_2_KIOSK_UNPROCESSED, UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK);			
				foreach($removeKeys as $key){
					unset($unlockStatusTechnicianOptions[$key]);
				}
			}
			
			if($this->request['data']['status'] == REQUEST_RECEIVED_IN_PROCESS){
				$removeKeys = array(PHONE_RECEIVED_BY_CENTER, REQUEST_RECEIVED_IN_PROCESS, UNLOCK_UNDER_PROCESS, WAITING_FOR_DISPATCH_UNLOCKED,DISPATCHED_2_KIOSK_UNLOCKED, DISPATCHED_2_KIOSK_UNPROCESSED);			
				foreach($removeKeys as $key){
					unset($unlockStatusTechnicianOptions[$key]);
				}
			}
			
			if($this->request['data']['status'] == PHONE_RECEIVED_BY_CENTER){
				$removeKeys = array(REQUEST_RECEIVED_IN_PROCESS, PHONE_RECEIVED_BY_CENTER,UNLOCK_UNDER_PROCESS, WAITING_FOR_DISPATCH_UNLOCKED,UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK,UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK);			
				foreach($removeKeys as $key){
					unset($unlockStatusTechnicianOptions[$key]);
				}
			}
			
			if($this->request['data']['status'] == WAITING_FOR_DISPATCH_UNLOCKED){
				$removeKeys = array(REQUEST_RECEIVED_IN_PROCESS, PHONE_RECEIVED_BY_CENTER, UNLOCK_UNDER_PROCESS, WAITING_FOR_DISPATCH_UNLOCKED,UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK, DISPATCHED_2_KIOSK_UNLOCKED, DISPATCHED_2_KIOSK_UNPROCESSED, UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK);			
				foreach($removeKeys as $key){
					unset($unlockStatusTechnicianOptions[$key]);
				}
			}
			
			if($this->request['data']['status'] == BOOKED ||
			   $this->request['data']['status'] == UNLOCKED_CONFIRMATION_PASSED ||			   
			   $this->request['data']['status'] == UNLOCKING_FAILED_CONFIRMATION_PASSED ||			   
			   $this->request['data']['status'] == RECEIVED_UNLOCKED_FROM_CENTER ||
			   $this->request['data']['status'] == RECEIVED_UNPROCESSED_FROM_CENTER ||
			   $this->request['data']['status'] == REFUND_RAISED ||
			   $this->request['data']['status'] == DELIVERED_UNLOCKED_BY_CENTER ||
			   $this->request['data']['status'] == DELIVERED_UNLOCKING_FAILED_AT_CENTER ||
			   $this->request['data']['status'] == DELIVERED_UNLOCKED_BY_KIOSK ||
			   $this->request['data']['status'] == DELIVERED_UNLOCKING_FAILED_AT_KIOSK){
				echo "<h3>Status</h3><h4>".$unlockStatusUserOptions[$this->request['data']['status']]."</h4>";
			}elseif(
			   $this->request['data']['status'] == UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK ||
			   $this->request['data']['status'] == DISPATCHED_2_KIOSK_UNLOCKED ||
			   $this->request['data']['status'] == DISPATCHED_2_KIOSK_UNPROCESSED ||
			   $this->request['data']['status'] == UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK){
				echo "<h3>Status</h3><h4>".$unlockStatusTechnicianOptions[$this->request['data']['status']]."</h4>";
			}else{
				echo $this->Form->input('status',array('id' => 'MobileUnlockStatus','name' => 'MobileUnlock[status]','options' => $unlockStatusTechnicianOptions));	
			}			
		}
	?>
	</fieldset>
<?php if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
	 ($this->request['data']['status'] == UNLOCK_REQUEST_SENT ||
			   $this->request['data']['status'] == DISPATCHED_2_CENTER ||
			   $this->request['data']['status'] == UNLOCKED_CONFIRMATION_PASSED ||			   
			   $this->request['data']['status'] == UNLOCKING_FAILED_CONFIRMATION_PASSED ||
			   $this->request['data']['status'] == REFUND_RAISED ||
			   $this->request['data']['status'] == DELIVERED_UNLOCKED_BY_CENTER ||
			   $this->request['data']['status'] == DELIVERED_UNLOCKING_FAILED_AT_CENTER ||
			   $this->request['data']['status'] == DELIVERED_UNLOCKED_BY_KIOSK ||
			   $this->request['data']['status'] == DELIVERED_UNLOCKING_FAILED_AT_KIOSK ||
			   $this->request['data']['status'] == REQUEST_RECEIVED_IN_PROCESS ||
			   $this->request['data']['status'] == PHONE_RECEIVED_BY_CENTER ||
			   $this->request['data']['status'] == UNLOCK_UNDER_PROCESS ||
			   $this->request['data']['status'] == WAITING_FOR_DISPATCH_UNLOCKED)
	 /*||//on request of client allowing submit in all cases for technician 30.12.2015
	 (AuthComponent::user('group_id') == UNLOCK_TECHNICIANS) &&
	 ($this->request['data']['MobileUnlock']['status'] == BOOKED ||
			   $this->request['data']['MobileUnlock']['status'] == UNLOCKED_CONFIRMATION_PASSED ||			   
			   $this->request['data']['MobileUnlock']['status'] == UNLOCKING_FAILED_CONFIRMATION_PASSED ||			   
			   $this->request['data']['MobileUnlock']['status'] == RECEIVED_UNLOCKED_FROM_CENTER ||
			   $this->request['data']['MobileUnlock']['status'] == RECEIVED_UNPROCESSED_FROM_CENTER ||
			   $this->request['data']['MobileUnlock']['status'] == REFUND_RAISED ||
			   $this->request['data']['MobileUnlock']['status'] == DELIVERED_UNLOCKED_BY_CENTER ||
			   $this->request['data']['MobileUnlock']['status'] == DELIVERED_UNLOCKING_FAILED_AT_CENTER ||
			   $this->request['data']['MobileUnlock']['status'] == DELIVERED_UNLOCKED_BY_KIOSK ||
			   $this->request['data']['MobileUnlock']['status'] == DELIVERED_UNLOCKING_FAILED_AT_KIOSK ||
			   $this->request['data']['MobileUnlock']['status'] == UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK ||
			   $this->request['data']['MobileUnlock']['status'] == DISPATCHED_2_KIOSK_UNLOCKED ||
			   $this->request['data']['MobileUnlock']['status'] == DISPATCHED_2_KIOSK_UNPROCESSED ||
			   $this->request['data']['MobileUnlock']['status'] == UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK)*/
	 ){
		//echo $this->Form->submit("submit");
		echo $this->Form->end();
	}elseif(($this->request->session()->read('Auth.User.group_id') == UNLOCK_TECHNICIANS) &&
	 ($this->request['data']['status'] == BOOKED ||
			   $this->request['data']['status'] == UNLOCKED_CONFIRMATION_PASSED ||			   
			   $this->request['data']['status'] == UNLOCKING_FAILED_CONFIRMATION_PASSED ||			   
			   $this->request['data']['status'] == RECEIVED_UNLOCKED_FROM_CENTER ||
			   $this->request['data']['status'] == RECEIVED_UNPROCESSED_FROM_CENTER ||
			   $this->request['data']['status'] == REFUND_RAISED ||
			   $this->request['data']['status'] == DELIVERED_UNLOCKED_BY_CENTER ||
			   $this->request['data']['status'] == DELIVERED_UNLOCKING_FAILED_AT_CENTER ||
			   $this->request['data']['status'] == DELIVERED_UNLOCKED_BY_KIOSK ||
			   $this->request['data']['status'] == DELIVERED_UNLOCKING_FAILED_AT_KIOSK ||
			   $this->request['data']['status'] == UNLOCK_PROCESSED_CONFIRMATION_SENT_2_KIOSK ||
			   $this->request['data']['status'] == DISPATCHED_2_KIOSK_UNLOCKED ||
			   $this->request['data']['status'] == DISPATCHED_2_KIOSK_UNPROCESSED ||
			   $this->request['data']['status'] == UNLOCK_FAILED_CONFIRMATION_SENT_2_KIOSK)){
		echo $this->Form->input('status',array('id' => 'MobileUnlockStatus','name' => 'MobileUnlock[status]','type'=>'hidden','value'=>$this->request['data']['status']));
		echo $this->Form->submit("Submit");
		echo $this->Form->end();
	}else{
		if($this->request->session()->read('Auth.User.group_id') == UNLOCK_TECHNICIANS){
			echo $this->Form->input('change_status', array('label' => 'If unchecked only changes will be saved without affecting the status', 'type' => 'checkbox', "checked" => "checked", 'id' => 'checkbox_status','name' => 'MobileUnlock[change_status]'));
			echo $this->Form->input('status',array('name' => 'MobileUnlock[status]','type'=>'hidden','value'=>$this->request['data']['status'], 'id' => 'hidden_status'));
			#echo $this->Form->input('hiddenStatus',array('type'=>'hidden','value'=>$this->request['data']['MobileUnlock']['status'], 'id' => 'hiddenStatus'));
			echo $this->Form->submit("submit");
			echo $this->Form->end();
		}elseif($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			if(
				array_key_exists($this->request['data']['status'],$unlockStatusUserOptions) &&
				$unlockStatusUserOptions[$this->request['data']['status']] == VIRTUALLY_BOOKED
			){
				//Note: if virtually booked than submit button should not be displayed to kiosk user in edit screen
				;
			}else{
				echo $this->Form->input('change_status', array('label' => 'If unchecked only changes will be saved without affecting the status', 'type' => 'checkbox', "checked" => "checked", 'id' => 'checkbox_status','name' => 'MobileUnlock[change_status]'));
				echo $this->Form->input('status',array('type'=>'hidden','name' => 'MobileUnlock[status]','value'=>$this->request['data']['status'], 'id' => 'hidden_status'));
				#echo $this->Form->input('hiddenStatus',array('type'=>'hidden','value'=>$this->request['data']['MobileUnlock']['status'], 'id' => 'hiddenStatus'));
				
				$options = array
							(
								'label' => 'Submit',
								'value' => 'Submit',
								'id' => 'Submit_res',
								//'name'=>'submit'
								);
							echo $this->Form->submit("Submit",$options);
							echo $this->Form->end();
				
				//echo $this->Form->end(__('Submit',array('id' => 'submit')));
			}
		}elseif(($this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER)
			/*&&
		   ($this->request['data']['MobileUnlock']['status'] == VIRTUALLY_BOOKED ||
		    $this->request['data']['MobileUnlock']['status'] == BOOKED)*/
			){
			echo $this->Form->input('status',array('type'=>'hidden','name' => 'MobileUnlock[status]','value'=>$this->request->data['status']));
			echo $this->Form->submit("Submit");
			echo $this->Form->end();
		}
	}
 ?>
</div>
<div id="payment_div">
	<?php echo $this->element('/MobileUnlock/payment',array(
															'setting' => $setting,
															));?>
</div>
<?php $update_payment = $this->Url->build(array('controller' => 'mobile_unlocks', 'action' => 'calculate_payment_ajax')); ?>
<input type='hidden' name='update_payment_ajax' id='update_payment_ajax' value='<?=$update_payment?>' />


<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php #echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('MobileUnlock.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('MobileUnlock.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Mobile Unlocks'), array('action' => 'index')); ?></li>
		<li><?php # echo $this->Html->link(__('List Kiosks'), array('controller' => 'kiosks', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php # echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
		<li><?php # echo $this->Html->link(__('List Networks'), array('controller' => 'networks', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Network'), array('controller' => 'networks', 'action' => 'add')); ?> </li>
		<li><?php echo $this->element('unlock_navigation'); ?></li>		
	</ul>
</div>
<script>
	$('#MobileUnlockBrandId').change(function() {
		var selectedValue = $(this).val(); 
		var targeturl = $(this).attr('rel') + '?id=' + selectedValue;
		initialize_inputs();
		$.blockUI({ message: 'Just a moment...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
			    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				$.unblockUI();
				//alert(response);
				/*if (response.error) {
					alert(response.error);
					console.log(response.error);
				}*/				
				if (response) {
					//alert(response);
					//$('#MobileUnlockMobileModelId').children().remove();
					$('#MobileUnlockMobileModelId').find('option').remove().end();
					$('#MobileUnlockMobileModelId').append(response);//html(response.content);
					
					$('#unlocking_price').find('option').remove().end();
					$('#unlocking_price').append("<option value='0'></option>");
					$('#net_cost').val(0);
				}
			},
			error: function(e) {
			    $.unblockUI(); //should be updated in other add.ctp also
			    $('#error_div').html("An error occurred: " + e.responseText.message).css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			    alert("An error occurred: " + e.responseText.message);
			    console.log(e);
			}
		});
	});
	
	$('#MobileUnlockMobileModelId').change(function() {
		var selectedValue = $(this).val();
		var brandId = $('#MobileUnlockBrandId').val();
		var targeturl = $(this).attr('rel') + '?brandID=' + brandId + '&modelID=' + selectedValue;
		//?brandID=1&modelID=243
		initialize_inputs();
		$.blockUI({ message: 'Just a moment...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
			    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				$.unblockUI();
				//alert(response);
				//console.log(response);
				/*if (response.error) {
					alert(response.error);
					console.log(response.error);
				}*/				
				if (response) {
					var networkOptions = "<option>choose network</option>";
					var obj = jQuery.parseJSON( response);
					$('#MobileUnlockNetworkId').find('option').remove().end();
					$.each(obj, function(i, elem){
						networkOptions+= "<option value=" + i + ">" + elem + "</option>";
					});
					$('#MobileUnlockNetworkId').append(networkOptions);//html(response.content);
					
					
					//------------------------------------
				}
			},
			error: function(e) {
			    $.unblockUI();
			    $('#error_div').html("An error occurred: " + e.responseText.message).css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			    alert("An error occurred: " + e.responseText.message);
			    console.log(e);
			}
		});
	});
	
	//case : on change of model set problem type to default and empty the value of estimated cost
	//and estimated price for all 3 cases
	//On change of mobile repair a 
	$('#MobileUnlockNetworkId').change(function() {
		var admin = $('#admin').val();
		//alert(admin);
		var brandID = $('#MobileUnlockBrandId').val();
		var modelID = $('#MobileUnlockMobileModelId').val();
		var networkID = $(this).val();
		$('#unlocking_price').val("");
		$('#unlocking_days').val("");
		$('#unlocking_minutes').val("");
		var targeturl = $(this).attr('rel') + '?networkID=' + networkID + "&brandID="+brandID+"&modelID="+modelID;
		if (parseInt(modelID) == 0) {
			$(this).val("");
			$('#error_div').html("Either there is no model for selected brand or you have not seleted any brand").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert("Either there is no model for selected brand or you have not seleted any brand");
			return;
		}
		//alert("Brand ID: "+brandID + " Problem ID "+ problemType + " modelID "+ modelID + " <br/>targeturl "+ targeturl);
		if (networkID == "" || networkID == "0") {
			$('#unlocking_price').find('option').remove().end();
			$('#unlocking_price').append("<option value='0'></option>");
			$('#net_cost').val(0);
			return;
			}
		if (modelID == "" || modelID == "0") {$(this).val("");$('#error_div').html("Please choose model").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();alert("Please choose model");return;}
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
				
				if (obj.error == 0) {
					//$('#unlocking_price').val(obj.unlocking_price);
					$('#unlocking_days').val(obj.unlocking_days);
					$('#unlocking_minutes').val(obj.unlocking_minutes);
					$('#net_cost').val(obj.unlocking_cost);
					//$('#net_cost').val(obj.unlocking_cost);
					var startCost = parseInt(obj.unlocking_price);
					if (admin == 1) {
                        var start = 1;
                    }else{
						var start = startCost;
					}
					
					var endCost = startCost + 50;
					var optionStr = "";
					for(i = start; i <= endCost; i++){
						optionStr += "<option value='" + i + "' >" + i + "</option>";
					}
					$('#unlocking_price').find('option').remove().end();
					$('#unlocking_price').append(optionStr);
					//if (admin == 1) {
                      //  $('#unlocking_price option[value=startCost]').prop('selected', 'selected').change();
					  //$("#unlocking_price option[value='startCost']").prop('selected', true);
                    //}
					$('#unlocking_price').val(startCost);
					//$("#unlocking_price option[value='obj.unlocking_price']").prop('selected', 'selected');
					//$('#unlocking_price option[value=obj.unlocking_price]').prop('selected', 'selected').change();
					console.log(response);
				}else{
					$('#unlocking_days').val("");
					$('#unlocking_minutes').val("");
					$('#unlocking_price').val("");
					$('#error_div').html("No price for this combination").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
					alert("No price for this combination");
				}
				
				//if (response) {
				//	$('#MobileUnlockMobileModelId').find('option').remove().end();
				//	$('#MobileUnlockMobileModelId').append(response);//html(response.content);
				//}
			},
			error: function(e) {
			    $.unblockUI();
			    $('#error_div').html("An error occurred: " + e.responseText.message).css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			    alert("An error occurred: " + e.responseText.message);
			    console.log(e);
			}
		});
	});
	//---------------------------------------------
	
	function initialize_inputs() {	
		//$('#MobileUnlockMobileModelId').val(""); 
		$('#MobileUnlockNetworkId').val("");
		$('#unlocking_price').val("");
		$('#unlocking_days').val("");	
	}
	
	$("#MobileUnlockCustomerContact").keydown(function (event) {		
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||
		event.keyCode == 46 || event.keyCode == 183) {
			;
			//event.keyCode == 190 || event.keyCode == 110 for dots
			//48-57 => 0..9
			//8 => Backspace; 9 => tab 
		} else {
			event.preventDefault();
		}
		//if($(this).val().indexOf('.') !== -1 && event.keyCode == 190){event.preventDefault();}
        });
	
	$("#MobileUnlockImei").keydown(function (event) {		
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||
		event.keyCode == 46 || event.keyCode == 183) {
			;
			//event.keyCode == 190 || event.keyCode == 110 for dots
			//48-57 => 0..9
			//8 => Backspace; 9 => tab 
		} else {
			event.preventDefault();
		}
		//if($(this).val().indexOf('.') !== -1 && event.keyCode == 190){event.preventDefault();}
    });
	$( "#MobileUnlockImei" ).keyup(function() {
		var MobileUnlockImei = $('#MobileUnlockImei').val();
		if ($('#MobileUnlockImei').val().length < 14) {
			//alert('hello');
			$('#imei1').val("");
		}
		
});
	
	
	function validateForm(){
		var networkId = $('#MobileUnlockNetworkId').val();
		if(networkId == "" || networkId == 0 || networkId == 'choose network'){
			$('#error_div').html('Please select a network!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert("Please select a network!");
			return false;
		}
		 if ($('#MobileUnlockImei').val().length < 14) {
			$('#error_div').html('Input imei should be of 14 characters!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert('Input imei should be of 14 characters!');
			return false;
		}
		return true;
	}
	$(function() {
	  $( document ).tooltip();
	});
	
	  $('#MobileUnlockImei').keyup(function(event){
		if ($('#MobileUnlockImei').val().length == 14  && ((event.keyCode >= 48 && event.keyCode <= 57 ) ||
		( event.keyCode >= 96 && event.keyCode <= 105))) {
			var i;
			var singleNum;
			var finalStr = 0;
			var total = 0;
			var numArr = $('#MobileUnlockImei').val().split('');
			
			for (i = 0; i < $('#MobileUnlockImei').val().length; i++) {
			    if (i%2 != 0) {
				//since array starts with 0 key, multiplying the key which is not divisible by 2 with 2 ie. 1,3,5 etc till 13
				singleNum = 2*numArr[i];
			    } else {
				singleNum = numArr[i];
			    }
			    finalStr+=singleNum;
			}
			
			//below creating the array from string and applying foreach to sumup all the values
			var finalArr = finalStr.split('');
			$.each(finalArr, function(key,numb){
			    total+=parseInt(numb);
			});
			
			//now for example the total is 52, we need to add 8 to make it 60 ie. divisible by 10. Then 8 will be the next number in imei
			var Dnum = parseInt(Math.ceil(total/10)*10-total);//this is the required number
			var newNumb = $('#MobileUnlockImei').val() + Dnum;
			var MobileUnlockImei = $('#MobileUnlockImei').val();
			var newNumb = $('#MobileUnlockImei').val() + Dnum;
			$('#imei1').val(Dnum);
	    }
		
		
	});
	 
	  
</script>
<script>
	$('#checkbox_status').click(function(){
	  var ischecked= $(this).is(':checked');
		  if (!ischecked) {
		    $("#MobileUnlockStatus").prop('disabled', true);
		    $("#hidden_status").prop('disabled', false);
		    //$("#hiddenStatus").prop('disabled', false);//being used in controller for check if checkbox is enabled or not
		  } else if (ischecked) {
		    $("#MobileUnlockStatus").prop('disabled', false);
		    $("#hidden_status").prop('disabled', true);
		    //$("#hiddenStatus").prop('disabled', true);
		  }
	})
  
	$(document).ready(function (){
		$("#hidden_status").prop('disabled', true);
		//$("#hiddenStatus").prop('disabled', true);
	});
</script>

<script>
	$(document).on('click','#Submit_res',function(){
		var status = $('#MobileUnlockStatus').val();
		var id = $('#MobileUnlockId').val();
		if (status == 11 || status == 9) {
			targeturl = $("#update_payment_ajax").val();
			targeturl +="?id="+id;
			
			$.blockUI({ message: 'Updating cart...' });
			$.ajax({
				type: 'get',
				url: targeturl,
				beforeSend: function(xhr) {
					xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			   },
			   success: function(response) {
				var objArr = $.parseJSON(response);
					if (objArr.hasOwnProperty('amount')) {
						document.getElementById('final_amount').value  = objArr.amount;
						document.getElementById('invoice_amt').innerHTML = objArr.amount;
						
						document.getElementById('payment_method_0').value  = objArr.amount;
						document.getElementById('due_amount').innerHTML  = objArr.amount;
						document.getElementById('total').value  = objArr.amount;
						document.getElementById('unlock_id').value  = id;
						//document.getElementById('error_div_pay').value  = "";
						$('#error_div_pay').val('');
						$('#divid_1').hide();
						$('#payment_method_1').val("");
						$('#full_or_part_1').prop('checked', true);
						$('#full_or_part_2').removeAttr('checked');
						$.unblockUI();
						$('#unlock_form').hide();
						$('#payment_div').show();   
                    }else if(objArr.hasOwnProperty('error')){
						$('#unlock_form').show();
						$('#payment_div').hide();
						$.unblockUI();
						document.getElementById('error_for_alert').innerHTML = "An error occurred: " + e.responseText.message;
						$( "#error_for_alert" ).dialog({
							   resizable: false,
							   height:140,
							   modal: true,
							   closeText: "Close",
							   width:300,
							   maxWidth:300,
							   title: '!!! Error!!!',
							   buttons: {
								   "OK": function() {
									   $( this ).dialog( "close" );
								   }
							   }
						}); 
						console.log(e);
						return false;
					}
			   },
			   error: function(e) {
					$.unblockUI();
					$('#unlock_form').show();
					$('#payment_div').hide();
					//alert("An error occurred: " + e.responseText.message);
					document.getElementById('error_for_alert').innerHTML = "An error occurred: " + e.responseText.message;
					$( "#error_for_alert" ).dialog({
						   resizable: false,
						   height:140,
						   modal: true,
						   closeText: "Close",
						   width:300,
						   maxWidth:300,
						   title: '!!! Error!!!',
						   buttons: {
							   "OK": function() {
								   $( this ).dialog( "close" );
							   }
						   }
					}); 
					console.log(e);
					return false;
			  }
			});
			return false;
        }
	});
	$(document).ready(function (){
		$('#payment_div').hide();
	});
</script>