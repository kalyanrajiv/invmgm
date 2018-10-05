<table>
	<tr>
		<?php if(count($products) == 0){?>
		<th>Delete</th>
		<?php } ?>
		<th>Repair Id</th>
		<th>Repaired At</th>
		<th>Product</th>
		<th>Product Code</th>
		<th>Kiosk name </th>
		<th>User name </th>
		<th>opperation <br/>status </th>
		<th>Opperation Date</th>
		<th>Date</th>
		<th>Replace</th>
	</tr>
	<?php
	$counter = 0;
	//pr($viewRepairParts);die;
	
	$session_kiosk_id = $this->request->Session()->read('kiosk_id');
	foreach($viewRepairParts as $key => $viewRepairPart){
        //pr($viewRepairPart);die;
		$partId = $viewRepairPart['id'];//primary id of mobile_repair_parts
		$productId = $viewRepairPart['product_id'];
		$kiosk_id = $viewRepairPart['kiosk_id'];
		$user_id = $viewRepairPart['user_id'];
		$repairId = $viewRepairPart['mobile_repair_id'];
		$opp_status = $viewRepairPart['opp_status'];
		$created = $viewRepairPart['created'];
        if(!empty($viewRepairPart['opp_date'])){
            $opp_date = date('jS M, Y',strtotime($viewRepairPart['opp_date']));    
        }else{
            $opp_date = "";
        }
		//$opp_date = date('jS M, Y',strtotime($viewRepairPart['opp_date']));//$this->Time->format('jS M, Y',$viewRepairPart['opp_date'],null,null);
		$counter++;
		$prodName = array_key_exists($productId, $productName) ? $productName[$productId] : "Not Found-$productId";
		$prodCode = array_key_exists($productId, $productsCode) ? $productsCode[$productId] : "Not Found-$productId";
		
		$database_date = strtotime(date('y-m-d',strtotime($created)));
		$current_date = strtotime(date('y-m-d'));
		
		?>
		<tr>
			<input type="hidden" name="data[kiosk_ID][<?=$key;?>]" value='<?=$kiosk_id;?>' />
			<?php if(count($products) == 0){
				if($opp_status == 0){
					if($this->request->session()->read('Auth.User.group_id') != ADMINISTRATORS){
						if($database_date == $current_date){ // ?>
							<td><?=$this->Form->input('delete', array('type' => 'checkbox', 'name' => "data[delete][$key]", 'value' => $productId, 'label' => false));?></td>
				<?php	}else{
									echo "<td></td>";
						}
					}else{ ?>
						<td><?=$this->Form->input('delete', array('type' => 'checkbox', 'name' => "data[delete][$key]", 'value' => $productId, 'label' => false));?></td>
			<?php	}
					
			}else{
				echo "<td></td>";} }?>
			<td><?=$viewRepairPart['mobile_repair_id'] ;?></td>
			<td><?=$kiosks[$viewRepairPart['kiosk_id']];?></td>
			<td><?= $prodName?></td>
			<td><?= $prodCode?></td>
			<td><?php if(array_key_exists($kiosk_id,$kiosks)){echo $kiosks[$kiosk_id];}else{echo "--";}?></td>
			<td><?php if(isset($users) && array_key_exists($user_id,$users)){echo $users[$user_id];}else{echo "--";}?></td>
			<td><?php if($opp_status == 1){echo "Move To Stock";}elseif($opp_status == 2){echo "Move To Faulty";}else{echo "";};?></td>
			<td><?php echo $opp_date;?></td>
			<td><?php
            if(!empty($viewRepairPart['created'])){
                echo date('jS M, Y g:i A',strtotime($viewRepairPart['created']));//$this->Time->format('jS M, Y g:i A',$viewRepairPart['created'],null,null) ;
            }else{
                echo "";
            }
            ?></td>
			<?php
				if(
				   $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
				   $this->request->session()->read('Auth.User.group_id') == MANAGERS){
					if($opp_status == 0){
			?>
			<td><input type='radio' name="data[PartsRepaired][original_product]" value='<?=$productId;?>' onclick="updateHidden(<?=$partId;?>, <?=$kiosk_id;?>);" <?=$checked = ($partId == $repairPartId) ? "checked" : ""; ?>></td>
			<?php
					}
				}elseif($this->request->session()->read('Auth.User.group_id') != ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') != MANAGERS){
					if(
					   isset($currntDate) &&
					   $kiosk_id == $session_kiosk_id &&
					   $currntDate == date('Y-m-d',strtotime($viewRepairPart['created'])))
					{
						if($opp_status == 0){
			?>
			<td><input type='radio' name="data[PartsRepaired][original_product]" value='<?=$productId;?>' onclick="updateHidden(<?=$partId;?>, <?=$kiosk_id;?>);" <?=$checked = ($partId == $repairPartId) ? "checked" : ""; ?>></td>
			<?php
						}
					}else{
						echo "<td>&nbsp;</td>";
					}
				}
			?>
		</tr>
	<?php }
	?>
	<input type="hidden" name='data[PartsRepaired][part]' id='part_id' <?= $repairdPartVal; ?>>
	<input type="hidden" name='data[PartsRepaired][kiosk_id]' id='kiosk_id' value='<?=$kiosk_id;?>'>
</table>