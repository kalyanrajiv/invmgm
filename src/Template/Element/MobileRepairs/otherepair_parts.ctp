<?php
	if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){
		;
    }else{
?>
	<table>
		<tr>
			<th colspan='8'>Other Repair Parts</th>
		</tr>
    <tr>
		<th>Repair Id</th>
		<th>Repaired At</th>
		<th>Product</th>
		<th>Product Code</th>
		<th>User name </th>
		<th>opperation <br/>status </th>
		<th>Opperation Date</th>
		<th>Date</th>
    </tr>
    <?php  //pr($viewOtherRepairParts);
		foreach($viewOtherRepairParts as $viewOtherRepairPart){
			$otherpartId = $viewOtherRepairPart['id']; 
			$otherproductId = $viewOtherRepairPart['product_id'];
			$otherkiosk_id = $viewOtherRepairPart['kiosk_id'];
			$otheruser_id = $viewOtherRepairPart['user_id'];
			$otherrepairId = $viewOtherRepairPart['mobile_repair_id'];
			$otheropp_status = $viewOtherRepairPart['opp_status'];
			//$otheropp_date = date('jS M, Y',strtotime($viewOtherRepairPart['opp_date']));//$this->Time->format('jS M, Y',$viewOtherRepairPart['opp_date'],null,null);
			//$othercreated = date('jS M, Y',strtotime($viewOtherRepairPart['created']));//$this->Time->format('jS M, Y g:i A',$viewOtherRepairPart['created'],null,null) ;
            if(empty($viewOtherRepairPart['opp_date'])){
                $otheropp_date = "";
            }else{
                $otheropp_date = date('jS M, Y',strtotime($viewOtherRepairPart['opp_date']));
            }
            if(empty($viewOtherRepairPart['created'])){
                $othercreated = "";
            }else{
                $othercreated = date('jS M, Y',strtotime($viewOtherRepairPart['created']));
            }
            
    ?>
	<tr>
		<td><?php echo $otherrepairId;?></td>
		<td><?php
			if(array_key_exists($otherkiosk_id,$kiosks)){
				echo $kiosks[$otherkiosk_id];
			}else{
				echo "--";
			}
			?>
		</td>
		<td><?php echo  $productName[$otherproductId]   ;?></td>
		<td><?php echo  $productsCode[$otherproductId]  ; ?></td>
		<td><?php
			if(isset($users) && array_key_exists($otheruser_id,$users)){
				echo $users[$otheruser_id];
			}else{
				echo "--";
			}
			?>
		</td>
		<td>
			<?php
				if($otheropp_status == 1){
					echo "Moved to Stock";
				}elseif($otheropp_status == 2){
					echo "Moved To faulty";
				}else{
					echo "--";
				}
			?>
		</td>
		<td><?php if(empty($otheropp_date)){echo "--";}else{echo $otheropp_date;}?></td>
		<td><?php echo $othercreated;?></td>
    </tr>
    <?php
		}
	echo "</table>";
	}
?>