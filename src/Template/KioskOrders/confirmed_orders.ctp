<?php
use Cake\I18n\Time;
?>
<style>
	.radio label {
    margin: 0 0 6px 20px;
    line-height: 16px;
}
</style>
 <?php
use Cake\Utility\Text;
use Cake\Routing\Router;
  
?>
<div class="kioskOrders index">
    <?php
	$value = $start_date = $end_date = $kiosk_id ="";
	$chosenType = "Dispatch Date";
	if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
	}
	$dispatch_place_id = "";
	if(!empty($this->request->query['dispatch_place_id'])){
		$dispatch_place_id = $this->request->query['dispatch_place_id'];
	}
	if(!empty($this->request->query['start_date'])){
		$start_date = $this->request->query['start_date'];
	}
	if(!empty($this->request->query['end_date'])){
		$end_date = $this->request->query['end_date'];
	}
	if(!empty($this->request->query['type'])){
	    $chosenType = $this->request->query['type'];
	}
	//echo  $webRoot =  "/kiosk_orders/confirmed_orders_search";
     $webRoot = $this->request->webroot."KioskOrders/confirmed_orders_search";
	echo $this->Form->create('kiosk_orders',['url' => $webRoot,'type' => 'get']);
    ?>
    <div class="search_div">
    <fieldset>
  <legend>Search</legend>
  <div>
   <table>
    <tr>
     <?php 
     if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
		$this->request->session()->read('Auth.User.group_id') == MANAGERS ||
		$this->request->session()->read('Auth.User.group_id') == inventory_manager){
      $options = array(
          'Dispatch Date' => 'Dispatch On',
          'Placed On' => 'Placed On',
           
         );
      echo "<td>".$this->Form->input('type', array(
                    'options' => $options,
                    'type' => 'radio',
                    'legend' => false,
                     'value' => $chosenType 
                   ))."</td>";
       
        
      echo "<td><input type = 'text' name = 'search_kw' id = 'search_kw', value= '$value' placeholder = 'Product code or title' style = 'width:150px;margin-top: 35px;'  autofocus/></td>";
      echo "<td>";
       echo $this->Form->input('null',array('id' => 'datepicker1',
           'readonly' => 'readonly',
           'name' => 'start_date',
           'placeholder' => "From Date",
           'label' => false,
           'value' => $start_date,
           'style' => "width: 85px;margin-top: 28px;"
           )
        );
      echo "</td>";
      echo "<td>";
        echo $this->Form->input('null',array('id' => 'datepicker2',
           'readonly' => 'readonly',
           'name' => 'end_date',
           'placeholder' => "To Date",
           'label' => false,
           'value' => $end_date,
           'style' => "width: 85px;margin-top: 28px;"
           )
        );
      echo "</td>";
      echo "<td>";
       if(!empty($this->request->query['data']['KioskOrder']['kiosk_id'])){
          $kiosk_id = $this->request->query['data']['KioskOrder']['kiosk_id'];
          echo $this->Form->input(null, array(
          'options' => $kiosks,
          'label' => 'Kiosks',
          'div' => false,
          'id'=> 'kioskid',
          'value' => $kiosk_id,
           'style' => 'width:170px;margin-top: 12px;',
          'name' => 'data[KioskOrder][kiosk_id]',
          //'empty' => 'Select Kiosk',
          'style' => 'width:160px'
          )
          );     
       }else{ 
         echo $this->Form->input(null, array(
            'options' => $kiosks,
            'label' => 'Kiosks',
            'div' => false,
            'id'=> 'kioskid',
            'name' => 'data[KioskOrder][kiosk_id]',
            'empty' => 'Select Kiosk',
            'style' => 'width:170px;margin-top: 12px;'
            )
            );
       }
      echo "</td>";
      echo "<td>";
      echo "<input type = 'text' name = 'dispatch_place_id' id = 'dispatch_place_id', value= '$dispatch_place_id' placeholder = 'Dispatch or Placed ID' style = 'width:130px;margin-top: 28px;'  autofocus/>"; 
      echo "</td>";
      echo "<tr>";
        echo "<td><input type = 'submit' value = 'Search' name = 'submit',style = 'width:155px;'/></td>";
      echo "<td><input type='button' name='reset' value='Reset' style = 'width:100px;height:28px;' onClick='reset_search();'/></td> ";
      echo "</tr>";
    
     }else{
          echo "<tr>";
       echo "<td><input type = 'text' name = 'search_kw' id = 'search_kw', value= '$value' placeholder = 'Product code or title' style = 'width:300px;margin-top: 15px;'  autofocus/></td>";
       echo "<td>";
      echo "<input type = 'text' name = 'dispatch_place_id' id = 'dispatch_place_id', value= '$dispatch_place_id' placeholder = 'Dispatch or Placed ID' style = 'width:130px;margin-top: 15px;'  autofocus/>"; 
      echo "</td>";
      echo "<td><input type = 'submit' value = 'Search' name = 'submit',style = 'width:205px;margin-right: 50px;'/></td>";
      echo "<td><input type='button' name='reset' value='Reset' style = 'width:200px;height:28px;' onClick='reset_search();'/></td> ";
      echo "</tr>";
     }
      
    ?>
    
    </tr>
    <tr><td colspan='7'><?php //echo "<input type = 'text' name = 'dispatch_place_id' id = 'dispatch_place_id', value= '$dispatch_place_id' placeholder = 'Dispatch or Placed ID' style = 'width:130px;margin-top: 15px;'  autofocus/>";?></td></tr>
  </table>
   </div>
  </fieldset> 
 </div>
<?php echo $this->Form->end(); ?>

<script>
	function reset_search(){
		jQuery( "#datepicker1" ).val("");
		jQuery( "#datepicker2" ).val("");
		jQuery("#search_kw").val("");
		jQuery("#kioskid").val("");
		jQuery( "#dispatch_place_id" ).val("");
	}
    jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
	});
</script>
 
<?php
	$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
      $updateUrl = "/img/16_edit_page.png";
?>
	<strong><?php //print_r($kioskOrders);
	echo __('<span style="font-size: 20px;color: red;">Confirmed Kiosk Orders</span> <span style="font-size: 17px;">(Warehouse to Kiosk)</span>'); ?></strong>
	<?php echo "<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	</br><b style="background-color: skyblue;">**skyblue highlighted rows are for On Demand Orders</b>
	</br><b style="background-color: lightgreen;">**Green highlighted rows are for Merged Orders</b>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id', 'Dispatch Id'); ?></th>
			<th><?php echo $this->Paginator->sort('kiosk_placed_order_id', 'Placed id'); ?></th>
			<th><?php echo $this->Paginator->sort('kiosk_id'); ?></th>
			<th><?php echo $this->Paginator->sort('status'); ?></th>
			<th><?php echo $this->Paginator->sort('user_id','Placed By'); ?></th>
		<th><?php echo $this->Paginator->sort('created','Placed On'); ?></th>
			<th><?php echo $this->Paginator->sort('user_id','Dispatched By'); ?></th>
			<th><?php echo $this->Paginator->sort('dispatched_on'); ?></th>
			<th><?php echo $this->Paginator->sort('received_on'); ?></th>
			<th><?php echo $this->Paginator->sort('received_by'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php //pr($kioskOrders);
        foreach ($kioskOrders as $kioskOrder): ?>
        <?php
		$kioskOrderStatus = $kioskOrder->status;
        $kioskname = $kioskOrder->kiosk['name'];
                        $truncatedKiosk =  Text::truncate(
                             $kioskname,
                             20,
                             [
                                 'ellipsis' => '...',
                                 'exact' => false
                             ]
                         );
                         
	    if(array_key_exists($kioskOrder->user_id, $users)){
		$username = $users[$kioskOrder->user_id];
	    }else{
		$username = '--';
	    }
        ?>
		<?php if($kioskOrder->is_on_demand == 1){ ?>
		<tr style="background-color: skyblue;">
		<?php }elseif(array_key_exists($kioskOrder->kiosk_placed_order_id,$kiosk_placed_merged_orders) && $kiosk_placed_merged_orders[$kioskOrder->kiosk_placed_order_id] == 1){ ?>
			<tr style="background-color: lightgreen;">
			<?php }else{?>
		<tr>
		<?php }?>
	
		<td><?php echo h($kioskOrder->id); ?>&nbsp;</td>
		<td><?php echo h($kioskOrder->kiosk_placed_order_id); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($truncatedKiosk,
                                                     array('controller' => 'stock_transfer', 'action' => 'view', $kioskOrder->id),
                                                     array('escapeTitle' => false, 'title' => $kioskOrder->kiosk['name'])
                                                     ); ?>
		</td>
		<td><?php echo $orderOptions[$kioskOrder->status]; ?>&nbsp;</td>
		<?php if($kioskOrder->is_on_demand == 1){ ?>
			<td nowrap=nowrap ><?php
				if(isset($on_demand_placed_user_id) && array_key_exists($kioskOrder->kiosk_placed_order_id,$on_demand_placed_user_id)){
					if(array_key_exists($on_demand_placed_user_id[$kioskOrder->kiosk_placed_order_id],$users)){
						echo  $users[$on_demand_placed_user_id[$kioskOrder->kiosk_placed_order_id]]  ;
					}else{
						echo  "--"  ;
					}
					
				}else{ 
						echo "NA";
				}  ?>&nbsp;</td>
			<td nowrap=nowrap ><?php
				if(isset($on_demand_placed_user_id) && array_key_exists($kioskOrder->kiosk_placed_order_id,$on_demand_placed_user_id)){
					$dummy_date = $on_demand_placed_user_date[$kioskOrder->kiosk_placed_order_id] ;
					 $dummy_date->i18nFormat(
                                                    [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                            );
					$real_date = $dummy_date->i18nFormat('dd-MM-yyyy HH:mm:ss');
					$real_date = date("d-m-y h:i a",strtotime($real_date)); 
					 echo $real_date;
				}else{ 
						echo "--";
				}  ?>&nbsp;</td>
		<?php }else{ ?>
					<td nowrap=nowrap ><?php
						if(isset($kiosk_placed_user_id) && array_key_exists($kioskOrder->kiosk_placed_order_id,$kiosk_placed_user_id)){
							if(array_key_exists($kiosk_placed_user_id[$kioskOrder->kiosk_placed_order_id],$users)){
								echo  $users[$kiosk_placed_user_id[$kioskOrder->kiosk_placed_order_id]]  ;
							}else{
								echo "--";
							}
							
						}else{ 
								echo "NA";
						}  ?>&nbsp;</td>
					<td nowrap=nowrap ><?php
						if(isset($kiosk_placed_user_id) && array_key_exists($kioskOrder->kiosk_placed_order_id,$kiosk_placed_user_id)){
							$dummy_date =   $kiosk_placed_user_date[$kioskOrder->kiosk_placed_order_id] ;
							 $dummy_date->i18nFormat(
                                                    [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                            );
							$real_date = $dummy_date->i18nFormat('dd-MM-yyyy HH:mm:ss');
							$real_date = date("d-m-y h:i a",strtotime($real_date));
							echo $real_date;
						}else{ 
								echo "--";
						}  ?>&nbsp;</td>
		<?php } ?>
		
		<td><?php echo $username; ?>&nbsp;</td>
        <?php
             $dispatched_on = $kioskOrder['dispatched_on'];
            if(!empty($dispatched_on)){
                $dispatched_on->i18nFormat(
                                                    [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                            );
				$dispatched_on_date = $dispatched_on->i18nFormat('dd-MM-yyyy HH:mm:ss');
                $dispatched_on_date = date("d-m-y h:i a",strtotime($dispatched_on_date)); 
            }else{
                $dispatched_on_date = "--";
            }
            
            $received_on = $kioskOrder['received_on'];
            if(!empty($received_on)){
                 $received_on->i18nFormat(
                                                    [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                            );
				$received_on_date =  $received_on->i18nFormat('dd-MM-yyyy HH:mm:ss');
                $received_on_date = date("d-m-y h:i a",strtotime($received_on_date));
            }else{
                $received_on_date = "--";
            }
        ?>
		<td nowrap=nowrap ><?php echo   $dispatched_on_date ; ?>&nbsp;</td>
		<td nowrap=nowrap ><?php echo  $received_on_date ; ?>&nbsp;</td>
		<td>
		<?php
			$userId = $kioskOrder->received_by;
			if(!empty($userId)){
				if(array_key_exists($userId,$users)){
					$username = $users[$userId];
				}else{
					$username = '--';
				}
				
				echo $this->Html->link($username, array('controller' => 'users', 'action' => 'view', $userId));
			}
		?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View Details'), array('controller' => 'stock_transfer','action' => 'view', $kioskOrder->id),array('target' => '_blank')); ?>
			<?php
			if($kioskOrderStatus==2 && $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS ||
			$kioskOrderStatus==2 && $this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
			echo $this->Html->link(__('Create Dispute'), array('controller' => 'stock_transfer','action' => 'create_dispute', $kioskOrder->id),array('id'=>'dispute_loading'));	
			}
			 ?>			
		</td>
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
	<p>
		<legend> ***Placed By(NA)Dispatch by Admin***</legend>
		</br>
	 	</p>
		
	<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
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
<script>
	$('#dispute_loading').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	});
</script>
<script>
$(function() {
  $( document ).tooltip({
   content: function () {
    return $(this).prop('title');
   }
  });
 });
</script>