<?php
use Cake\I18n\Time;
?>
<style>
	.radio label {
    margin: 0 0 6px 20px;
    line-height: 16px;
}
</style>
<div class="kioskOrders index">
 
<?php
//pr($on_demand_placed_user_id);
//pr($kioskOrders);
	$value1 = $value = $start_date = $end_date = $kiosk_id ="";
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
     
    //pr($this->request->query)
	$webRoot = $this->request->webroot."KioskOrders/transient_orders_search";
	echo $this->Form->create('kiosk_orders',array('url' => $webRoot,'type' => 'get'));
?>

<?php $update_kiosk = $this->Url->build(['controller' => 'kiosk-orders', 'action' => 'change_transient_order_kiosk'],true);?>
	<input type='hidden' name='update_kiosk' id='update_kiosk' value='<?=$update_kiosk?>' />
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
       
        
      echo "<td><input type = 'text' name = 'search_kw' id = 'search_kw', value= '$value' placeholder = 'Product code or title' style = 'width:131px;margin-top: 32px;'  autofocus/></td>";
	  
      echo "<td>";
       echo $this->Form->input('null',array('id' => 'datepicker1',
           'readonly' => 'readonly',
           'name' => 'start_date',
           'placeholder' => "From Date",
           'label' => "Start Date",
           'value' => $start_date,
           'style' => "width: 85px;margin-top: 8px;"
           )
        );
      echo "</td>";
      echo "<td>";
        echo $this->Form->input('null',array('id' => 'datepicker2',
           'readonly' => 'readonly',
           'name' => 'end_date',
           'placeholder' => "To Date",
           'label' => "End Date",
           'value' => $end_date,
           'style' => "width: 85px;margin-top: 8px;"
           )
        );
      echo "</td>";
      echo "<td>";
      //pr($this->request->query);die;
       if(!empty($this->request->query['KioskOrder']['kiosk_id'])){
       $kiosk_id = $this->request->query['KioskOrder']['kiosk_id'];
                                       
          echo $this->Form->input(null, array(
          'options' => $kiosks,
          'label' => 'Kiosks',
          'div' => false,
          'id'=> 'kioskid',
          'value' => $kiosk_id,
           'style' => 'width:170px;margin-top: 12px;',
          'name' => 'KioskOrder[kiosk_id]',
          'empty' => 'Select Kiosk',
          'style' => 'width:160px'
          )
          );     
       }else{ 
         echo $this->Form->input(null, array(
            'options' => $kiosks,
            'label' => 'Kiosks',
            'div' => false,
            'id'=> 'kioskid',
            'name' => 'KioskOrder[kiosk_id]',
            'empty' => 'Select Kiosk',
            'style' => 'width:170px;margin-top: 12px;'
            )
            );
       }
        echo "</td>";
        echo "<td>";
        echo "<input type = 'text' name = 'dispatch_place_id' id = 'dispatch_place_id', value= '$dispatch_place_id' placeholder = 'Dispatch or Placed ID' style = 'width:130px;margin-top: 32px;'  autofocus/>";
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td><input type = 'submit' value = 'Search' name = 'submit',style = 'width:155px;'/></td>";
        echo "<td><input type='button' name='reset' value='Reset' style = 'width:100px;height:28px;' onClick='reset_search();'/></td> ";
        echo "</tr>";
     }else{
        echo "<tr>";
            echo "<td style=width:10px;><input type = 'text' name = 'search_kw' id = 'search_kw', value= '$value' placeholder = 'Product code or title' style = 'width:131px;margin-top: 32px;'  autofocus/></td>";
           	echo "<td style=width:10px;>";
					echo "<input type = 'text' name = 'dispatch_place_id' id = 'dispatch_place_id', value= '$dispatch_place_id' placeholder = 'Dispatch or Placed ID' style = 'width:130px;margin-top: 32px;'  autofocus/>";
				echo "</td>";
            echo "<td style=width:10px;><input type = 'submit' value = 'Search' name = 'submit' style = 'width:100px;margin-top:32px;'/></td>";
            echo "<td><input type='button' name='reset' value='Reset' style = 'width:100px;height:28px;margin-top:32px;' onClick='reset_search();'/></td> ";
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
	<strong><?php #print_r($kioskOrders);
	echo __('<span style="font-size: 20px;color: red;">Transient Kiosk Orders</span> <span style="font-size: 17px;">(Warehouse to Kiosk)</span>'); ?></strong>
	<?php echo "<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	</br><b style="background-color: skyblue;">**skyblue highlighted rows are for On Demand Orders</b>
	</br><b style="background-color: lightgreen;">**Green highlighted rows are Merged Orders</b>
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
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($kioskOrders as $kioskOrder): ?>
	<?php
		$truncatedKiosk = \Cake\Utility\Text::truncate(
						$kioskOrder->kiosk_id,
						25,
						[
							'ellipsis' => '...',
							'exact' => true
						]
					);
        if(array_key_exists($truncatedKiosk,$kiosks)){
			$truncatedKiosk = $kiosks[$truncatedKiosk];
		}
		if(array_key_exists($kioskOrder->user_id,$users)){
			$username = $users[$kioskOrder->user_id];
		}else{
			$username = '--';
		}
	?>
	<?php if($kioskOrder->is_on_demand == 1){?>
	<tr style="background-color: skyblue;">
	<?php }elseif(array_key_exists($kioskOrder->kiosk_placed_order_id,$kiosk_placed_merged_orders) && $kiosk_placed_merged_orders[$kioskOrder->kiosk_placed_order_id] == 1){ ?>
		<tr style="background-color: lightgreen;">
		<?php }else{ ?>
	<tr>
	<?php } ?>
	
		<td><?php echo h($kioskOrder->id); ?>&nbsp;</td>
		<td><?php echo h($kioskOrder->kiosk_placed_order_id); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($truncatedKiosk,
                                                     array('controller' => 'stock_transfer', 'action' => 'view', $kioskOrder->id),
                                                     array('escapeTitle' => false, 'title' => $kioskOrder['name'])
                                                     ); ?>
		</td>
		<td><?php echo $orderOptions[$kioskOrder['status']]; ?>&nbsp;</td>
		<?php if($kioskOrder->is_on_demand == 1){?>
		<td nowrap=nowrap >
				<?php
                //echo $kioskOrder->kiosk_placed_order_id;
				if(isset($on_demand_placed_user_id) && array_key_exists($kioskOrder->kiosk_placed_order_id,$on_demand_placed_user_id)){
					if(array_key_exists($on_demand_placed_user_id[$kioskOrder->kiosk_placed_order_id],$users)){
						echo  $users[$on_demand_placed_user_id[$kioskOrder->kiosk_placed_order_id]]  ;
					}else{
						echo "--";
					}
				}else{ 
					echo "--";
				}  ?>&nbsp;</td>
				<td nowrap=nowrap ><?php
					if(isset($on_demand_placed_user_id) && array_key_exists($kioskOrder['kiosk_placed_order_id'],$on_demand_placed_user_id)){
						$test_date =  $on_demand_user_date[$kioskOrder['kiosk_placed_order_id']]; 
						
				
					$dispatched_on_date111 = date("d-m-y h:i a",strtotime($test_date));
						
						 $test_date->i18nFormat(
                                                [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                        );
						$test_date_date =  $test_date->i18nFormat('dd-MM-yyyy HH:mm:ss');
						
						$test_date_date = date("d-m-y h:i a",strtotime($test_date_date));
						
						
						
						echo $test_date_date;
						//echo  $test_date =  $on_demand_user_date[$kioskOrder['kiosk_placed_order_id']]; 
					}else{ 
						echo "--";
					}  ?>&nbsp;</td>
		<?php }else{
            ?>
			<td nowrap=nowrap ><?php
				if(isset($kiosk_placed_user_id) && array_key_exists($kioskOrder['kiosk_placed_order_id'],$kiosk_placed_user_id)){
                    
                    if(array_key_exists($kiosk_placed_user_id[$kioskOrder['kiosk_placed_order_id']],$users)){
                        echo  $users[$kiosk_placed_user_id[$kioskOrder['kiosk_placed_order_id']]]  ;
                    }else{
                        echo "--";
                    }
					
				}else{ 
					echo "--";
				}  ?>&nbsp;</td>
			<td nowrap=nowrap ><?php
				if(isset($kiosk_placed_user_id) && array_key_exists($kioskOrder['kiosk_placed_order_id'],$kiosk_placed_user_id)){
					if(array_key_exists($kioskOrder['kiosk_placed_order_id'],$kiosk_placed_user_date)){
						$normal_date =   $kiosk_placed_user_date[$kioskOrder['kiosk_placed_order_id']] ;
						 $normal_date->i18nFormat(
                                                [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                        );
						$show_date =  $normal_date->i18nFormat('dd-MM-yyyy HH:mm:ss');
						
						$show_date = date("d-m-y h:i a",strtotime($show_date));
						echo $show_date;
					}else{
						echo "--";
					}
					
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
			$dispatched_on_date =  $dispatched_on->i18nFormat('dd-MM-yyyy HH:mm:ss');
			
            $dispatched_on_date = date("d-m-y h:i a",strtotime($dispatched_on_date));
        }else{
            $dispatched_on_date = "--";
        }
        
        $received_on = $kioskOrder['received_on'];
        if(!empty($received_on)){
            $received_on_date = $received_on->i18nFormat(
                                                [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                        );
            $received_on_date = date("d-m-y h:i a",strtotime($received_on_date));
        }else{
            $received_on_date = "--";
        }
        
        ?>
		<td nowrap=nowrap ><?php echo $dispatched_on_date;  ?>&nbsp;</td>
		<td nowrap=nowrap ><?php echo $received_on_date; ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View Details'), array('controller' => 'stock_transfer','action' => 'view', $kioskOrder['id'])); ?>
			<?php if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
				$status = $kioskOrder->status;
				if($status != 3){
					echo $this->Html->link(__('Revert Stock'),array('action' => 'Revert_stock',$kioskOrder['id'],$kioskOrder['kiosk_id']), 
						array('escape' => false, 'confirm' => __('Are you sure you want to Revert Stock # %s?', $kioskOrder->id)) 
					    );
					
					if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS){
						echo $this->Html->link(__('Delete Order'),array('action' => 'Delete_transient_order',$kioskOrder['id'],$kioskOrder['kiosk_id']), 
						array('escape' => false, 'confirm' => __('Are you sure you want to Delete order # %s?', $kioskOrder->id)) 
					    );	
					}
				}
			}
			?>	
			<?php if ($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS ||
				  $this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS): ?>
			<?php echo $this->Html->link(__('Receive Order'), array('controller' => 'kiosk_orders','action' => 'receive_order', $kioskOrder['id']),array('id'=>'receive_loading')); ?>
			<?php endif; ?>
			<?php if($this->request->session()->read('Auth.User.username') == SPL_PRIVILEGE_USER){?>
			<div style="display: flex;">
				<div id = "change_kiosk_div_<?php echo $kioskOrder->id;?>">
						<input type="button" id="change_kiosk" name="change_kiosk" style="background-image: -moz-linear-gradient(top, #fefefe, #dcdcdc);width: 116px;border: 1px solid #bbb;border-radius: 4px;text-shadow: #fff 0px 1px 0px;" value="Change Kiosk"/>
				</div>
				<div id = "change_kiosk_submit_div_<?php echo $kioskOrder->id;?>">
				<?php
					$change_kiosk_dropdown = $kiosks;
					if(array_key_exists($kioskOrder->kiosk_id,$kiosks)){
						unset($change_kiosk_dropdown[$kioskOrder->kiosk_id]);
					}
					if(array_key_exists(10000,$kiosks)){
						unset($change_kiosk_dropdown[10000]);
					}
					echo $this->Form->input(null, array(
															'options' => $change_kiosk_dropdown,
															'div' => false,
															'id'=> 'kioskid_'.$kioskOrder->id,
															'name' => 'KioskOrder[kiosk_id]',
															'empty' => 'Select Kiosk',
															'style' => 'width:170px;margin-top: 12px;'
														)
													);
				?>
				<input type="button" id="change_kiosk_submit_<?php echo $kioskOrder->id;?>" name="change_kiosk_submit" style="background-image: -moz-linear-gradient(top, #fefefe, #dcdcdc);width: 116px;border: 1px solid #bbb;border-radius: 4px;text-shadow: #fff 0px 1px 0px;" value="Submit"/>
				</div>
			</div>
			<?php } ?>
		</td>
		
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
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
	$('#receive_loading').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	});
</script>

<script>
	$(document).ready(function() {
		<?php foreach($kioskOrders as $kioskOrder){ ?>
				$("#change_kiosk_submit_div_"+"<?php echo $kioskOrder->id ?>").hide();
		<?php } ?>
	});
	<?php foreach($kioskOrders as $kioskOrder){ ?>
				$("#change_kiosk_div_"+"<?php echo $kioskOrder->id ?>").click(function(){
					$("#change_kiosk_submit_div_"+"<?php echo $kioskOrder->id ?>").show();
					$("#change_kiosk_div_"+"<?php echo $kioskOrder->id ?>").hide();
					var current_id = <?php echo $kioskOrder->id; ?>;
					hide_other(current_id);
					});
		<?php } ?>
		
		function hide_other(id) {
            <?php
				foreach($kioskOrders as $kioskOrder){ ?>
					var current_id = <?php echo $kioskOrder->id; ?>;
					if (current_id != id) {
						$("#change_kiosk_div_"+"<?php echo $kioskOrder->id ?>").show();
	                    $("#change_kiosk_submit_div_"+"<?php echo $kioskOrder->id; ?>").hide();
	                }
			<?php } ?>
        }
		
		<?php foreach($kioskOrders as $kioskOrder){ ?>
				$("#change_kiosk_submit_"+"<?php echo $kioskOrder->id ?>").click(function(){
					if (confirm("Are You Sure")) {
							var selected_kiosk_id = $("#kioskid_"+"<?php echo $kioskOrder->id ?>").val();
							if (selected_kiosk_id == "") {
								alert("Please Choose Kiosk");
								return false;
							}
							var order_id = <?php echo $kioskOrder->id; ?>;
							if (order_id == "") {
								alert("No order id Found");
								return false;
							}
							
							var kiosk_placed_order_id = <?php if(empty($kioskOrder->kiosk_placed_order_id)){ echo "''";}else{echo $kioskOrder->kiosk_placed_order_id;} ?>;
							
							targeturl = $("#update_kiosk").val();
							//targeturl += "?order_id="+order_id;
							//targeturl += "&selected_kiosk_id="+selected_kiosk_id;
							
							
							$.blockUI({ message: 'Just a moment...' });
							$.ajax({
								type: 'post',
								url: targeturl,
								data: { order_id: order_id, selected_kiosk_id : selected_kiosk_id, kiosk_placed_order_id : kiosk_placed_order_id } ,
								beforeSend: function(xhr) {
									xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
								},
								success: function(response) {
									
									var objArr = $.parseJSON(response);
									alert(objArr.msg);
									location.reload();
								},
								error: function(e) {
									$.unblockUI();
									alert("An error occurred: " + e.responseText.message);
									console.log(e);
								}
							});
					}
				});
		<?php } ?>
		
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
