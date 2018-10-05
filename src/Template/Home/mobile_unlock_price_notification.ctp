<h2>Unlock Price Change</h2>
<?php if(count($statusChangeData)){?>
<h3>Activation Status</h3>
<table style="width: 60%;">
    <tr>
	<th>Date</th>
	<th>New Status</th>
	<th>Brand</th>
	<th>Model</th>
	<th>Network</th>
    </tr>
    <?php foreach($statusChangeData as $key => $statusChange){?>
    <tr>
	<td><?=$statusChange['status_change_date'];?></td>
	<td><?php
				$unlockStatus = $activeStatus[$statusChange['status']];
				if($unlockStatus == 'Online'){
					echo "<span style='color:green'>$unlockStatus</span>";
				}else{
					echo "<span style='color:red'>$unlockStatus</span>";
				}
		?></td>
	<td><?=$brandName[$statusChange['brand_id']];?></td>
	<td><?=$statusMobileModelNames[$statusChange['mobile_model_id']];?></td>
	<td><?=$networks[$statusChange['network_id']];?></td>
    </tr>
    <?php }?>
</table>
 
<?php } ?>
<h3>Price Changes</h3>
    <?php
		if($mobileUnlockPriceNotification){
			echo "<table style='width: 60%;'>
					<tr>
						<th>Date</th>
						<th>Updated Price</th>
						<th>Brand</th>
						<th>Model</th>
						<th>Network</th>
					</tr>";
        
		foreach($mobileUnlockPrices as $mobileUnlockPrice){
			$modified = $mobileUnlockPrice['modified'];
			$modified = $modified;
			$unlockPrice = $mobileUnlockPrice['unlocking_price'];
			$brand = $brandName[$mobileUnlockPrice['brand_id']];
			$model = $unlockMobileModelNames[$mobileUnlockPrice['mobile_model_id']];
			$network = $networks[$mobileUnlockPrice['network_id']];
			echo "<tr>
					<td>$modified</td>
					<td><span style='color: crimson'><strong>{$CURRENCY_TYPE}{$unlockPrice}</span></td>
					<td><span style='color: blue'>$brand</span></td>
					<td>$model</td>
					<td>$network</td></tr>";
		}
		echo "</table>";
    }else{
			echo "<h4>No notification for today!</h4>";
	}?>