<?php
	extract($this->request->query);
	if(!isset($product)){$product = "";}
	if(!isset($product_code)){$product_code = "";}
?>

<div class="centralStocks index">
	<?php #pr($orderDisputes['OrderDispute']['approval_status']);die;?>
	<table>
		<tr>
			<th>Kiosk Id</th>		
			<th>Order Id</th>
			<th><?php echo $this->Paginator->sort('disputed_by'); ?></th>
			<th>Receiving Status</th>
			<th>Approval Status</th>
			<th>Approval Action</th>
			<th><?php echo $this->Paginator->sort('admin_remarks'); ?></th>
			<th><?php echo $this->Paginator->sort('created','Dispute Date'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
		</tr>
		<?php
		$check_array = array();
		//pr($orderDispute_remarks);
		foreach($orderDisputes as $key => $orderDispute){
			if(array_key_exists($orderDispute['kiosk_order_id'],$check_array)){
				continue;
			}else{
				$check_array[$orderDispute['kiosk_order_id']] = $orderDispute['kiosk_order_id']; 
			}
			
			if(array_key_exists($orderDispute['kiosk_order_id'],$partial_or_done)){
				$approvalStatus  = "Partial";
			}else{
				$approvalStatus  = "Approved";
			}
			
			//if($orderDispute['OrderDispute']['admin_acted'] == 0){
			//	$approvalStatus = '--';
			//}else{
			//	$approvalStatus = $approvalOptions[$orderDispute['OrderDispute']['approval_status']];
			//}
		
			//if(!empty($orderDispute['OrderDispute']['admin_remarks'])){
			$adminRemarks = "";
           
				if(array_key_exists($orderDispute['kiosk_order_id'],$orderDispute_remarks)){
					$temp_remark = $orderDispute_remarks[$orderDispute['kiosk_order_id']];
					if(!empty($temp_remark)){
						$remark_array = explode("|",$temp_remark);
						if(!empty($remark_array)){
							$count = 0;
							foreach($remark_array as $t_key => $t_value){
								if($count == 1){
								 $adminRemarks .= "<span style = color:red;>".$t_value."  "."</span>";
								 $count--;
								}else{
									$count++;
									$adminRemarks .= "<span style = color:blue;>".$t_value."  "."</span>";
								}
							}
						}
						//$adminRemarks = implode(",",$remark_array);
					}
				}else{
					$adminRemarks = "--";
				}
				//$adminRemarks = $orderDispute['OrderDispute']['admin_remarks'];
			//}else{
				//$adminRemarks = "--";
			//}
			if($orderDispute['disputed_by']>0 && array_key_exists($orderDispute['disputed_by'],$users)){
				 $disputed_by = $users[$orderDispute['disputed_by']];
			}else{
				$disputed_by = "--";
			}
			
			if($orderDispute['approval_by']>0 && array_key_exists($orderDispute['approval_by'],$users)){
				 $aprove_action = $users[$orderDispute['approval_by']];
			}else{
				$aprove_action = "--";
			}
			?>		
		<tr>
			<td><?php echo $this->Html->link($kiosk[$orderDispute['kiosk_id']], array('controller' => 'kiosks', 'action' => 'view', $orderDispute['kiosk_id'])) ;?></td>		
			<td><?php echo $orderDispute['kiosk_order_id'] ;?></td>
			<td><?php echo $disputed_by; ?></td>
			<td><?php echo $disputeOptions[$orderDispute['receiving_status']] ;?></td>
			<td><?php echo $approvalStatus;?></td>
			<td><?php echo $aprove_action;?></td>
			<td><?php echo $adminRemarks ;?></td>
            <?php
                 $disputed_on = $orderDispute['created'];
                if(!empty($disputed_on)){
                     $disputed_on->i18nFormat(
                                                        [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                                );
					$disputed_on_date =  $disputed_on->i18nFormat('dd-MM-yyyy HH:mm:ss');
                    $disputed_on_date = date("d-m-y h:i a",strtotime($disputed_on_date)); 
                }else{
                    $disputed_on_date = "--";
                }
            ?>
			<td><?=$disputed_on_date;?></td>
			<td><?php echo $this->Html->link(__('View Details'),array('controller'=>'stock_transfer','action' => 'view_disputed_orders', $orderDispute['kiosk_order_id'],$orderDispute['kiosk_id'])) ;?></td>
		</tr>
		<?php }?>
	</table>
	<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<?php  if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
					$this->request->session()->read('Auth.User.group_id') == MANAGERS ||
					$this->request->session()->read('Auth.User.group_id') == inventory_manager){
					echo $this->element('sidebar/order_menus');
				}else{
					echo $this->element('sidebar/kiosk_order_menus');
		}?>
	 
</div>
