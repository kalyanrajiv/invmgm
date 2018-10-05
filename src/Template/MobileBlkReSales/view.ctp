<?php
	use Cake\Core\Configure;
	use Cake\Core\Configure\Engine\PhpConfig;
	
	
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<div class="mobileReSales view"> 
<?php $status = array('0'=>'Sold','1'=>'Refunded');
?>	
<h2><?php echo __('Mobile Re Sale'); ?></h2>
<?php
if((($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER) && ($mobileReSale['kiosk_id']==0 || $mobileReSale['kiosk_id']== 10000 ) ||
			  	 $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS && $mobileReSale['kiosk_id']==$kiosk_id) &&
			 	 $mobileReSale['status']==0){
				 ?>
			<?php echo $this->Html->link(__('Refund'), array('action' => 'mobile_refund', $mobileReSale['id'])); }?>
			<?php echo $this->Html->link('Thermal receipt',array('controller' => 'prints','action'=>'mobile-bulk-sale',$mobileReSale['id'])); ?>
			
			|&nbsp;<?php echo $this->Html->link(__('Sale Receipt'), array('action' => 'mobile_sale_receipt', $mobileReSale['id'])); ?>
			|&nbsp;<?php echo $this->Html->link(__('Purchase Receipt'), array('controller' => 'mobile_purchases', 'action' => 'kiosk_receipt', $mobileReSale['mobile_purchase_id'])); ?>
			<?php #echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $mobileReSale['MobileReSale']['id']), array(), __('Are you sure you want to delete # %s?', $mobileReSale['MobileReSale']['id'])); ?>
		 
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($mobileReSale['id']); ?>
			&nbsp;
		</dd>
		
		<dt><?php echo __('Kiosk'); ?></dt>
		<dd>
			<?php 
				//pr($mobileReSale);die;
			echo $this->Html->link($mobileReSale['kiosk_id'], array('controller' => 'kiosks', 'action' => 'view', $mobileReSale['kiosk_id']['id'])); ?>
			&nbsp;
		</dd>
	</dl>
<h4><?php echo __('Customer Detail'); ?></h4>
	<dl>
		<dt><?php echo __('First Name'); ?></dt>
		<dd>
			<?php echo h($mobileReSale['customer_fname']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Last Name'); ?></dt>
		<dd>
			<?php echo h($mobileReSale['customer_lname']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Customer Email'); ?></dt>
		<dd>
			<?php echo h($mobileReSale['customer_email']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Customer Contact'); ?></dt>
		<dd>
			<?php echo "&nbsp;&nbsp;".$mobileReSale['customer_contact']; ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Address 1'); ?></dt>
		<dd>
			<?php echo h($mobileReSale['customer_address_1']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Address 2'); ?></dt>
		<dd>
			<?php echo h($mobileReSale['customer_address_2']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('City'); ?></dt>
		<dd>
			<?php echo h($mobileReSale['city']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('State'); ?></dt>
		<dd>
			<?php echo h($mobileReSale['state']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Country'); ?></dt>
		<dd>
			<?php //pr($countryOptions);
			//change in code
			if(array_key_exists($mobileReSale['country'],$countryOptions)){
				//echo $countryOptions[$mobileReSale['country']];
				echo $countryOptions[$mobileReSale['country']];
			}else{
				echo "----";
			}
			?>
			&nbsp;
		</dd>
		<dt><?php echo __('Postal Code'); ?></dt>
		<dd>
			<?php echo h($mobileReSale['zip']); ?>
			&nbsp;
		</dd>
	</dl>
<h4><?php echo __('Mobile Detail'); ?></h4>
	<dl>
		<dt><?php echo __('Brand'); ?></dt>
		<dd>
			<?php 
			echo $this->Html->link($mobileReSale['brand']['brand'], array('controller' => 'brands', 'action' => 'view', $mobileReSale['brand']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Model'); ?></dt>
		<dd>
			<?php echo h($mobileModels[$mobileReSale['mobile_model_id']]); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Network'); ?></dt>
		<dd>
			<?php
			if(array_key_exists('network_id',$networks)){
				 echo $networks[$mobileReSale['MobileBlkReSale']['network_id']];
			}
			?>
			&nbsp;
		</dd>
		<dt><?php echo __('Color'); ?></dt>
		<dd>
			<?php echo h($colorOptions[$mobileReSale['color']]); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('IMEI'); ?></dt>
		<dd>
		 
			<?php echo $this->Html->link(__($mobileReSale['imei']), array('controller'=>'mobile_purchases','action' => 'mobile_transfer_logs',  $mobileReSale['imei'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Description'); ?></dt>
		<dd>
			<?php echo h($mobileReSale['description']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Grade'); ?></dt>
		<dd>
			<?php
				if($mobileReSale['custom_grade'] == 1){
					echo h($mobileReSale['grade']);
				}else{
					if(array_key_exists($mobileReSale['grade'],$gradeType)){
						echo h($gradeType[$mobileReSale['grade']]);
					}else{
						echo "";
					}
					
				}
			?>
			&nbsp;
		</dd>
		<dt><?php echo __('Type'); ?></dt>
		<dd>
			<?php  echo h($type[$mobileReSale['type']]); ?>
			&nbsp;
		</dd>
		<?php if(($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER)){
			echo "<dt>";
			  echo __('Original Cost Price');
			  echo "&nbsp</dt>";
			  echo "<dd>";
			  //echo $currency;
			  $cost_price =  h($mobileReSale['cost_price']);
			  echo $CURRENCY_TYPE.$cost_price;
			  
			  echo "</dd>";
			  
			  echo "<dt>";
			  echo __('Top-up Price');
			  echo "&nbsp</dt>";
			  echo "<dd>";
			  //echo $currency;
			  $top_up_price =  h($mobilePurchaseData['topedup_price']);
			  echo $CURRENCY_TYPE.$top_up_price;
			  
			  echo "</dd>";
		 }  ?>
		<dt><?php echo __('Selling Price'); ?></dt>
		<dd style='color: blue'>
			<?php  $selling_price =  h($mobileReSale['selling_price']);
				 echo $CURRENCY_TYPE.$selling_price;
			?>
			&nbsp;
		</dd>
		<dt><?php echo __('Actual Sale Price'); ?></dt>
		<dd><?php echo $CURRENCY_TYPE.$orgSP;?>&nbsp;</dd>
		<dt><?php echo __('Lowest Sale Price'); ?></dt>
		<dd><?php echo $CURRENCY_TYPE.$lowestSP;?>&nbsp;</dd>
		<dt><?php echo __('Discount  '); ?></dt>
		<dd>
			<?php
			if($mobileReSale['discount']>0){
				echo h($discountOptions[$mobileReSale['MobileBlkReSale']['discount']]);
			}else{
				echo "--";
			}
			 ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Discount Price'); ?></dt>
		<?php if(!empty($mobileReSale['MobileBlkReSale']['discounted_price'])){
			$discountedPrice = $mobileReSale['MobileBlkReSale']['discounted_price'];
			
		}else{
			$discountedPrice = "";
		}
			?>
		<dd>
			<?php   echo $CURRENCY_TYPE.$discountedPrice; ?>
			&nbsp;
		</dd>
	</dl>
<h4><?php echo __('Miscellaneous'); ?></h4>
	<dl>
		<dt><?php # echo __('Brief History'); ?></dt>
		<dd>
			<?php # echo __('<b>Under Development</b>')?>
			<?php # echo h($mobileReSale['MobileReSale']['brief_history']); ?>
			&nbsp;
		</dd>
		 
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo $status[$mobileReSale['status']]; ?>
			&nbsp;
		</dd>
		
		<?php //pr($status);
		echo "<dt>"; 
			 if($status[$mobileReSale['status']] == 'Refunded'){
				 echo __('Refund Price'); 
				 echo "</dt> ";
				 echo "<dd>";
				 $refund_price = h($mobileReSale['refund_price']);
				    echo $CURRENCY_TYPE.$refund_price;  
				  echo "</dd>";
				  echo "<dt>";
				 echo __('Refund Gain');
				 echo "</dt>";
				   echo "<dd>";
				   echo $currency;
				$refund_gain =  h($mobileReSale['refund_gain']);
				  echo $CURRENCY_TYPE.$refund_gain;  
				  echo "</dd>";
				 
			} 
				 
			 
			?>
		 
	 
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($mobileReSale['created']));?>
			&nbsp;
		</dd>
		
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo date('jS M, Y g:i A',strtotime($mobileReSale['modified'])); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
			 
		<li><?php echo $this->Html->link(__('Edit Mobile Re Sale'), array('action' => 'edit', $mobileReSale['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Mobile Re Sale'), array('action' => 'delete', $mobileReSale['id']), array(), __('Are you sure you want to delete # %s?', $mobileReSale['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Mobile Re Sales'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Mobile Re Sale'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Kiosks'), array('controller' => 'kiosks', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('Edit Mobile Purchase'), array('controller'=>'mobile_purchases','action' => 'edit',$mobilePurchaseData['id'])); ?> </li>
		<li><?php # echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
	</ul>
</div>
