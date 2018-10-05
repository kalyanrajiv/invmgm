
<?php
	use Cake\Core\Configure;
    use Cake\Core\Configure\Engine\PhpConfig;
    $siteBaseURL = Configure::read('SITE_BASE_URL');
    //pr($mobilePurchase);die;//rasu
	$lockedUnlocked = $mobilePurchase['type'];
?>
<?php
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<div class="mobilePurchases view">
<h2><?php echo __('Mobile Purchase'); ?></h2>
<?php
//pr($mobilePurchase);die;
	if(!empty($mobilePurchase['function_condition'])){
		$function_condition = explode("|",$mobilePurchase['function_condition']);
		if(is_array($function_condition)){
			//count($function_condition) > 1
			$functionConditionArray = array();
			foreach($function_condition as $key => $functionCon){
				if(array_key_exists($functionCon, $functionConditions)){
					$functionConditionArray[] = $functionConditions[$functionCon];
				}
				
			}
			$functionConditionStr = implode(', ',$functionConditionArray);
		}else{
			$functionConditionStr = '';
		}
	}else{
		$functionConditionStr = '';
	}
	
	if(!empty($mobilePurchase['mobile_condition'])){
		$mobile_condition = explode("|",$mobilePurchase['mobile_condition']);
		if(is_array($mobile_condition)){
			//count($mobile_condition) > 1
			$mobileConditionArray = array();
			foreach($mobile_condition as $key => $mobileCon){
				if($mobileCon == 1000){
					$mobileConditionArray[] = $mobilePurchase['mobile_condition_remark'];
				}else{
					if(array_key_exists($mobileCon, $mobileConditions)){
						$mobileConditionArray[] = $mobileConditions[$mobileCon];
					}
				}
			}
			$mobileConditionStr = implode(', ',$mobileConditionArray);
		}else{
			$mobileConditionStr = '';
		}
	}else{
		$mobileConditionStr = '';
	}
	?>
 
<?php

if($mobilePurchase['status']==0 && $mobilePurchase['receiving_status']==0){?>
	<?php if($lockedUnlocked==1){echo $this->Html->link('Send for unlock',array('controller'=>'mobile_unlocks','action'=>'add',$mobilePurchase['id']));}?>
	&nbsp;
	<?php echo $this->Html->link('Send for repair',array('controller'=>'mobile_repairs','action'=>'add',$mobilePurchase['id']));?>
	&nbsp;
	<span style='margin-right: 10px;'>
	<?php echo $this->Html->link('Thermal receipt',array('controller' => 'prints','action'=>'mobile-purchases',$mobilePurchase['id'])); ?>
	</span>
 <?php echo $this->Html->link('Generate Receipt',array('action'=>'kiosk_receipt',$mobilePurchase['id']));
}
?>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($mobilePurchase['id']); ?>
			&nbsp;
		</dd>
		
		<dt><?php echo __('Kiosk'); ?></dt>
		<dd>
			<?php echo $this->Html->link($mobilePurchase['kiosk']['name'], array('controller' => 'kiosks', 'action' => 'view', $mobilePurchase['kiosk']['id'])); ?>
			&nbsp;
		</dd>
		
	</dl>
<h4><?php echo __('Customer Detail'); ?></h4>
	<dl>	
		<dt><?php echo __('First Name'); ?></dt>
		<dd>
			<?php echo h($mobilePurchase['customer_fname']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Last name'); ?></dt>
		<dd>
			<?php echo h($mobilePurchase['customer_lname']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('date of Birth'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($mobilePurchase['date_of_birth']));//$this->Time->format('M jS, Y',$mobilePurchase['date_of_birth'],null,null);	  ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Email'); ?></dt>
		<dd>
			<?php echo h($mobilePurchase['customer_email']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Contact'); ?></dt>
		<dd>
			<?php echo h($mobilePurchase['customer_contact']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Address 1'); ?></dt>
		<dd>
			<?php echo h($mobilePurchase['customer_address_1']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Address 2'); ?></dt>
		<dd>
			<?php echo h($mobilePurchase['customer_address_2']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('City'); ?></dt>
		<dd>
			<?php echo h($mobilePurchase['city']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('State'); ?></dt>
		<dd>
			<?php echo h($mobilePurchase['state']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Country'); ?></dt>
		<dd>
			<?php //pr($countryOptions);
			echo h($countryOptions[$mobilePurchase['country']]);
			?>
			&nbsp;
		</dd>
		<dt><?php echo __('Postal Code'); ?></dt>
		<dd>
			<?php echo h($mobilePurchase['zip']); ?>
			&nbsp;
		</dd>
	</dl>
<h4><?php echo __('Mobile Detail'); ?></h4>
	<dl>
		<dt><?php echo __('Brand'); ?></dt>
		<dd>
			<?php echo $this->Html->link($mobilePurchase['brand']['brand'], array('controller' => 'brands', 'action' => 'view', $mobilePurchase['brand']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Model'); ?></dt>
		<dd>
			<?php
            //pr($mobileModels);die;
            //echo $mobilePurchase['mobile_model_id'];die;
            echo h($mobileModels[$mobilePurchase['mobile_model_id']]); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Network'); ?></dt>
		<dd>
			<?php echo h($networks[$mobilePurchase['network_id']]); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Image'); ?></dt>
		<dd>
			<?php
                 $imageDir = WWW_ROOT."files".DS.'MobilePurchases'.DS.'image'.DS.$mobilePurchase['id'].DS;
				 $imageName = $mobilePurchase['image'];
				  $absoluteImagePath = $imageDir.$imageName;
                  $targetimageName = $mobilePurchase['image'];
				$imageURL = "/thumb_no-image.png";
				$targetimageURL = "/thumb_no-image.png";
				//echo $absoluteImagePath;die;
				if(@readlink($absoluteImagePath) ||file_exists($absoluteImagePath)){
                   $imageURL = $absoluteImagePath;//"$siteBaseURL/files/mobile_purchase/image/".$mobilePurchase['id']."/$imageName";
					$targetimageURL = "$siteBaseURL/files/MobilePurchases/image/".$mobilePurchase['id']."/$targetimageName";
				} 
				echo $this->Html->link(
							  $this->Html->image($targetimageURL, array('fullBase' => false,'height' => '100px','width' => '100px')),
							  $targetimageURL,
							  array('escapeTitle' => false, 'title' => $mobilePurchase['id'],'target'=>'_blank')
							 );
			?>
			&nbsp;
		</dd>
		<dt><?php echo __('Color'); ?></dt>
		<dd>
			<?php if(array_key_exists($mobilePurchase['color'],$colorOptions)){
                echo h($colorOptions[$mobilePurchase['color']]);
            }
                ?>
			&nbsp;
		</dd>
		
		<?php if(!empty($mobileConditionStr)){?>
		<dt><?php echo __('Phone"s Condition'); ?></dt>
		<dd>
			<?php echo $mobileConditionStr; ?>
			&nbsp;
		</dd>
		<?php } ?>
		
		<?php if(!empty($functionConditionStr)){?>
		<dt><?php echo __('Phone"s Function Test'); ?></dt>
		<dd>
			<?php echo $functionConditionStr; ?>
			&nbsp;
		</dd>
		<?php } ?>
		<dt><?php echo __('IMEI'); ?></dt>
		<dd>
			
			<?php echo $this->Html->link(__($mobilePurchase['imei']), array('controller'=>'mobile_purchases','action' => 'mobile_transfer_logs',  $mobilePurchase['imei'])); ?>
			&nbsp;
		</dd>
		
<h4><?php echo __('History'); ?></h4>
	<dl>			
		<dt><?php echo __('Brief History'); ?></dt>
		
		<dt><?php echo __('Reserved By'); ?></dt>
		<dd>
			<?php
			$reservedBy = (array_key_exists($mobilePurchase['reserved_by'],$users)) ? $users[$mobilePurchase['reserved_by']] : '--';
			echo $reservedBy; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Reserve Date'); ?></dt>
		<dd>
			<?php if(!empty($mobilePurchase['reserve_date'])){
                echo date('jS M, Y g:i A',strtotime($mobilePurchase['reserve_date']));
            }else{
                echo "--";
            }
                //$this->Time->format('jS M, Y g:i A',$mobilePurchase['reserve_date'],null,null); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Transferred By'); ?></dt>
		<dd>
			<?php
			$transBy = (array_key_exists($mobilePurchase['transient_by'],$users)) ? $users[$mobilePurchase['transient_by']] : '--';
			echo $transBy; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Transfer Date'); ?></dt>
		<dd>
			<?php if(!empty($mobilePurchase['transient_date'])){
                echo date('jS M, Y g:i A',strtotime($mobilePurchase['transient_date']));
            }else{
                echo "--";
            }//$this->Time->format('jS M, Y g:i A',$mobilePurchase['transient_date'],null,null); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Description'); ?></dt>
		<dd>
			<?php echo h($mobilePurchase['description']); ?>
			&nbsp;
		</dd>
		<?php if($mobilePurchase['custom_grades'] == 1 && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
			
		}else{?>
		<dt><?php echo __('Purchase Cost'); ?></dt>
		<dd>
			<?php 	//echo $currency;
			$cost_price = h($mobilePurchase['cost_price']);
			echo $CURRENCY_TYPE.$cost_price;
			?>
			&nbsp;
		</dd>
		<?php } ?>
		<dt><?php echo __('Top up price'); ?></dt>
		<dd>
			<?php  
			$topedup_price =  h($mobilePurchase['topedup_price']);
			echo $CURRENCY_TYPE.$topedup_price;
			?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php  echo h($status[$mobilePurchase['status']]); 
			 ?>
			&nbsp;
		</dd>
		<?php
			if(array_key_exists($mobilePurchase['grade'], $gradeType)){
				$mobGrade = $gradeType[$mobilePurchase['grade']];
			}else{
				$mobGrade = $mobilePurchase['grade'];
			}
		?>
		<dt><?php echo __('Grade'); ?></dt>
		<dd>
			<?php echo $mobGrade; ?>
			&nbsp;
		</dd>
		 
		<dt><?php echo __('Receiving  '); echo "<br/>";echo __('Status '); ?></dt> 
		<dd>
			<?php  echo h($received[ $mobilePurchase['receiving_status']]); ?>
			&nbsp;
		</dd>
		<dt><?php   echo __('StockIn Ref:');  ?></dt> 
		<dd>
			<?php   $mobile_purchase_reference =   h($mobilePurchase['mobile_purchase_reference']);
			if(!empty($mobile_purchase_reference)){
				 echo   $mobile_purchase_reference;
				} else{
					echo "--";
				}
				  ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Type'); ?></dt>
		<dd>
			<?php echo h($type[ $mobilePurchase['type']]); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($mobilePurchase['created']));//$this->Time->format('jS M, Y g:i A',$mobilePurchase['created'],null,null);?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($mobilePurchase['modified']));//$this->Time->format('jS M, Y g:i A',$mobilePurchase['modified'],null,null);?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Mobile Purchase'), array('action' => 'edit', $mobilePurchase['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Mobile Purchase'), array('action' => 'delete', $mobilePurchase['id']), array(), __('Are you sure you want to delete # %s?', $mobilePurchase['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Mobile Purchases'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Mobile Purchase'), array('action' => 'add')); ?> </li>
		<li><?php # echo $this->Html->link(__('List Kiosks'), array('controller' => 'kiosks', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php # echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
	</ul>
</div>