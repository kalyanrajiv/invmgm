<h3>Repair Price Change</h3> 
<?php
	  if($mobileRepairPriceNotification){
			echo "<table style='width: 60%;'>
					<tr>
						<th>Date</th>
						<th>Updated Price</th>
						<th>Brand</th>
						<th>Model</th>
						<th>Network</th>
					</tr>";
                 //   pr($mobileRepairPrices);
			foreach($mobileRepairPrices as $mobileRepairPrice){
               $modified = date('jS M, Y g:i A',strtotime($mobileRepairPrice['modified']));
				  $modified = $modified;
				  $repairPrice = $mobileRepairPrice['repair_price'];
				  $brand = $brandName[$mobileRepairPrice['brand_id']];
				  $model = $repairMobileModelNames[$mobileRepairPrice['mobile_model_id']];
				  $probType = $problemType[$mobileRepairPrice['problem_type']];
				  echo "<tr>
						  <td>$modified</td>
						  <td><span style='color: crimson'><strong>{$CURRENCY_TYPE}{$repairPrice}</span></td>
						  <td><span style='color: blue'>$brand</span></td>
						  <td>$model</td>
						  <td>$probType</td></tr>";
			}
			echo "</table>";
		/*$i = 0;
        foreach($mobileRepairPriceNotification as $mobileRepairPriceNotice){
			  $i++;
			  echo $i;
			echo "\t";
            echo $mobileRepairPriceNotice."<br/>";
        }*/
    }else{
			echo "<h4>No notification for today!</h4>";
	}?>
    