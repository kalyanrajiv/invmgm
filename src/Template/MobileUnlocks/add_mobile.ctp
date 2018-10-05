<div class="mobileUnlocks form">
<?php
if(!isset($internal_unlock_default_cost)){
	$internal_unlock_default_cost = "";
}
if(!isset($internal_unlock_default_price)){
	$internal_unlock_default_price = "";
}
	$kioskName = $kioskContact = $kioskEmail = $kioskZip = $kioskAddress1 = $kioskAddress2 = $kioskCity = $kioskState = $brandId = $modelId = $networkId = $imei = '';
	//pr($mobilePurchaseDetails);
	if(!empty($mobilePurchaseDetails)){
		$k_id = $mobilePurchaseDetails['kiosk']['id'];
		$kioskName = $mobilePurchaseDetails['kiosk']['name'];
		$kioskContact = $mobilePurchaseDetails['kiosk']['contact'];
		$kioskEmail = $mobilePurchaseDetails['kiosk']['email'];
		$kioskZip = $mobilePurchaseDetails['kiosk']['zip'];
		$kioskAddress1 = $mobilePurchaseDetails['kiosk']['address_1'];
		$kioskAddress2 = $mobilePurchaseDetails['kiosk']['address_2'];
		$kioskCity = $mobilePurchaseDetails['kiosk']['city'];
		$kioskState = $mobilePurchaseDetails['kiosk']['state'];
		$brandId = $mobilePurchaseDetails['brand_id'];
		$modelId = $mobilePurchaseDetails['mobile_model_id'];
		$networkId = $mobilePurchaseDetails['network_id'];
		$imei = $mobilePurchaseDetails['imei'];
                $imei1 = substr($imei, -1);
		$imei2 = substr_replace($imei,'',-1) ;
		$country['GB'] = "United Kingdom";//Keeping only UK for the kiosk users.
		//pr($mobileUnlockPrice);
		if(!empty($internal_unlock_default_price)){
			$unlocking_price = .0001;
		}else{
			$unlocking_price = $mobileUnlockPrice['unlocking_price'];
		}
		
		$unlocking_days = $mobileUnlockPrice['unlocking_days'];
	}else{
		echo "<h3>Please choose a valid entry!!</h3>";
	}
	$url = $this->Url->build(array('controller' => 'customers', 'action' => 'get_address'));
	echo $this->Form->create($mobile_unlocks); ?>
	<fieldset>
		<legend><?php echo __('Add Mobile Unlock'); ?></legend>
	<?php	$date = date('Y-m-d h:i:s A');
		echo $this->Form->input('unlock_number', array('type' => 'hidden', 'value' => 1));
		echo $this->Form->input('route',array('type'=>'hidden','name'=>'add_mobile'));
		//customer details
		echo ('<h4>Customer Details</h4><hr/>');
		echo "<table>";
		echo "<tr>";
		echo $this->Form->input('retail_customer_id', array('type' => 'hidden', 'value' => $k_id));
		echo "<td>".$this->Form->input('customer_fname', array('id' => 'MobileUnlockCustomerFname','name' => 'customer_fname','label' => 'First Name','value'=>$kioskName,'readonly'=>'readonly'))."</td>";
		echo "<td>".$this->Form->input('customer_lname', array('id' => 'MobileUnlockCustomerLname','name' => 'customer_lname','label' => 'Last Name','value'=>$kioskName,'readonly'=>'readonly'))."</td>";
		echo "<td>".$this->Form->input('customer_contact',array('id' => 'MobileUnlockCustomerContact','name' => 'customer_contact','label' => 'Mobile/Phone','maxLength' => 11,'value'=>$kioskContact,'readonly'=>'readonly'))."</td>";
		echo "</tr>";
		echo "<tr>";		
		echo "<td>".$this->Form->input('customer_email',array('id' => 'MobileUnlockCustomerEmail','name' => 'customer_email','value'=>$kioskEmail,'readonly'=>'readonly'))."</td>";
		echo "<td>";
			echo "<table>";
				echo "<tr>";
					echo "<td>";
					echo $this->Form->input('zip',array('id' => 'MobileUnlockZip','name' => 'zip','placeholder' => 'Postcode', 'label'=>false, 'rel' => $url,'size'=>'10px','value'=>$kioskZip,'readonly'=>'readonly'));
					echo "</td>";
					echo "<td>";
					#echo "<button type='button' id='find_address' class='btn' style='margin-top: 6px;margin-left: -8px;width: 112px;height: 22px;'>Find my address</button>";
					echo "</td>";
				echo "</tr>";
			echo "</table>";
		echo "</td>";
		echo "<td>".$this->Form->input('customer_address_1', array('id' => 'MobileUnlockCustomerAddress1','name' => 'customer_address_1','placeholder' => 'property name/no. and street name','value'=>$kioskAddress1,'readonly'=>'readonly'));
	?>
		
		</td>	
	<?php
		echo "</tr>";
		echo "<table>";
		echo "<tr>";
		echo "<td>".$this->Form->input('customer_address_2', array('id' => 'MobileUnlockCustomerAddress2','name' => 'customer_address_2','placeholder' => "further address details (optional)",'value'=>$kioskAddress2,'readonly'=>'readonly'))."</td>";
		echo "<td>".$this->Form->input('city',array('id' => 'MobileUnlockCity','name' => 'city','label' => 'Town/City','placeholder' => "name of town or city",'value'=>$kioskCity,'readonly'=>'readonly'))."</td>";
		echo "<td>".$this->Form->input('state',array('id' => 'MobileUnlockState','name' => 'state','label'=>'County', 'placeholder' => "name of county (optional)",'value'=>$kioskState,'readonly'=>'readonly'))."</td>";
		echo "<td>".$this->Form->input('country',array('id' => 'MobileUnlockCountry','name' => 'country','options'=>$country))."</td>";
		echo "</tr>";
		echo "</table>";
		echo "</tr>";
		echo "</table>";	
		
		//phone details
		echo ('<h4>Mobile Details</h4><hr/>');
		$url = $this->Url->build(array('controller' => 'mobile_unlocks', 'action' => 'get_models'));
		$priceURL = $this->Url->build(array('controller' => 'mobile_unlocks', 'action' => 'get_unlock_price'));
		echo $this->Form->input('brand_id',array('id' => 'MobileUnlockBrandId','name' => 'brand_id','options' => $brands,'rel' => $url,'default'=>$brandId,'disabled'=>'disabled'));
		echo $this->Form->input('brand_id',array('id' => 'MobileUnlockBrandId','name' => 'brand_id','type'=>'hidden','value'=>$brandId));
		echo $this->Form->input('mobile_model_id',array('id' => 'MobileUnlockMobileModelId','name' => 'mobile_model_id','options' => $mobileModels,'type' => 'select','empty' => 'choose model','value'=>$modelId,'disabled'=>'disabled'));
		echo $this->Form->input('mobile_model_id',array('id' => 'MobileUnlockMobileModelId','name' => 'mobile_model_id','type'=>'hidden','value'=>$modelId));
		echo $this->Form->input('network_id',array(
								'id' => 'MobileUnlockNetworkId','name' => 'MobileUnlock[network_id]',
							   'options' => $networks,
							   'rel' => $priceURL,
							   'empty' => 'choose network',
							   'default' => $networkId,
							   'disabled'=>'disabled'
							   )
					);
		echo $this->Form->input('network_id',array('id' => 'MobileUnlockNetworkId','name' => 'network_id','type'=>'hidden','value'=>$networkId));
		echo $this->Form->input('unlocking_price', array(
								'type' => 'text',
								'style'=>'width: 50%',
								'name' => 'estimated_cost',
								'readonly' => true,
								'id' => 'unlocking_price',
								'value' => $unlocking_price
								));
					
		echo $this->Form->input('unlocking_days', array(
								'type' => 'text',
								'style'=>'width: 50%',
								'name' => 'unlocking_days',
								'readonly' => true,
								'id' => 'unlocking_days',
								'value' => $unlocking_days
							));
        echo "<table>";
            echo "<tr>";
                echo "<td>";
		echo $this->Form->input('imei',array('label' => array('id' => 'MobileUnlockImei','text' => 'Imei', 'style' => 'margin-left: 6px;'), 'maxlength'=>14,'value'=>$imei2,'readonly'=>'readonly','style'=>'width: 120px; margin-left: 6px;','div' => false));
                echo "</td>";
                echo "<td>";
                echo $this->Form->input('imei1',array('label' => false, 'value'=>$imei1,'readonly'=>'readonly', 'id' =>'imei1', 'style'=>"width: 15px; margin-right: 670px; margin-top: 13px; /*! margin-left: 10px */height: 10px;",'div' => false));
                echo "</td>";
            echo "</tr>";
         echo "</table>";   
		echo $this->Form->input('received_at', array('id' => 'MobileUnlockReceivedAt','type' => 'hidden', 'value' => $date));
		echo $this->Form->input('description',array('id' => 'MobileUnlockDescription','label' => 'Unlock Description', 'style' => 'width:50%'));
		echo $this->Form->input('internal_unlock',array('id' => 'MobileUnlockInternalUnlock','type'=>'hidden','value'=>'1'));		
		echo $this->Form->input('brief_history', array('id' => 'MobileUnlockBriefHistory','type' => 'hidden','label' => 'Unlock History</br/>(For Internal Use)'));
		echo $this->Form->input('status',array('id' => 'MobileUnlockStatus','options' => array('0'=>'Virtually Booked','1'=>'Physically Booked')));
		
	?>
	</fieldset>
<?php if(!empty($mobilePurchaseDetails)){
	echo $this->Form->submit('submit');
	echo $this->Form->end();
	}
	?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Mobile Unlocks'), array('action' => 'index')); ?></li>
		<li><?php # echo $this->Html->link(__('List Kiosks'), array('controller' => 'kiosks', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php # echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
		<li><?php echo $this->element('unlock_navigation'); ?></li>		
	</ul>
</div>