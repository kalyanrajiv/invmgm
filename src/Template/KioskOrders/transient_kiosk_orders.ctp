<div class="kioskOrders index">
    <?php
	$value = $start_date = $end_date = $kiosk_id ="";
	if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
	}
	if(!empty($this->request->query['start_date'])){
		$start_date = $this->request->query['start_date'];
	}
	if(!empty($this->request->query['end_date'])){
		$end_date = $this->request->query['end_date'];
	}
	$webRoot = $this->request->webroot."KioskOrders/transient_kiosk_orders_search";
	echo $this->Form->create('kiosk_orders',array('url' => $webRoot,'type' => 'get'));
    ?>
    
    <div class="search_div">
	<fieldset>
            <legend>Search</legend>
            <div>
                <table>
                    <tr>
                            <td><input type = "text" name = "search_kw" id = "search_kw", value= '<?= $value;?>' placeholder = "Product code or title"     autofocus style=" margin-top: 23px;width: 170px;"/></td>
                        <?php 
                               if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
										$this->request->session()->read('Auth.User.group_id') == MANAGERS ||
										$this->request->session()->read('Auth.User.group_id') == inventory_manager){
                                        echo "<td>";
                                        echo $this->Form->input('null',array('id' => 'datepicker1',
                                               'readonly' => 'readonly',
                                               'name' => 'start_date',
                                               'placeholder' => "From Date",
                                               'label' => false,
                                               'value' => $start_date,
                                               'style' => "width: 85px;margin-top: 17px;"
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
                                           'style' => "width: 85px;margin-top: 17px;"
                                           )
                                        );
                                        echo "</td>";
                                        echo "<td>";
                                        //pr($kiosks);die;
                                        if(!empty($this->request->query['KioskOrder']['kiosk_id'])){
                                           $kiosk_id = $this->request->query['KioskOrder']['kiosk_id'];
                                           echo $this->Form->input(null, array(
                                                         'options' => $kiosks,
                                                         'label' => 'kiosks',
                                                         'div' => false,
                                                         'id'=> 'kioskid',
                                                         'value' => $kiosk_id,
                                                          'style' => 'width:170px',
                                                         'name' => 'KioskOrder[kiosk_id]',
                                                         'empty' => 'Select Kiosk',
                                                         'style' => 'width:160px'
                                                         )
                                                  );     
                                        }else{ 
                                          echo $this->Form->input(null, array(
                                                           'options' => $kiosks,
                                                           'label' => 'kiosks',
                                                           'div' => false,
                                                           'id'=> 'kioskid',
                                                           'name' => 'KioskOrder[kiosk_id]',
                                                           'empty' => 'Select Kiosk',
                                                           'style' => 'width:170px'
                                                           )
                                                           );
                                        }
                                        echo "</td>";
                                }
                        ?>
                        <td><input type = "submit" value = "Search" name = "submit" style = "width:155px;margin-top: 30px;"/></td>
                        <td><input type='button' name='reset' value='Reset Search' style = "width:155px;margin-top: 26px;" onClick='reset_search();'/></td>
                    </tr>
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
	echo __('<span style="font-size: 20px;color: red;">Transient Kiosk Orders</span> <span style="font-size: 17px;">(Kiosk to Warehouse)</span>'); ?></strong>
	<?php echo "<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('kiosk_id'); ?></th>
			<th><?php echo $this->Paginator->sort('user_id','Dispatched by'); ?></th>
			<th><?php echo $this->Paginator->sort('status'); ?></th>
			<th><?php echo $this->Paginator->sort('dispatched_on'); ?></th>
			<th><?php echo $this->Paginator->sort('received_on'); ?></th>
			<th><?php echo $this->Paginator->sort('received_by'); ?></th>		
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($kioskOrders as $kioskOrder): ?>
        <?php
        //pr($kioskOrder);die;
		if($kioskOrder->user_id>0){
			if(array_key_exists($kioskOrder->user_id, $users)){
				$userName = $users[$kioskOrder->user_id];
			}else{
				$userName = $kioskOrder->user_id. " Missing!";
			}
		}else{
			$userName = "--";
		}
		//echo $kioskOrder->kiosk['name'];die;
//            $truncatedKiosk = \Cake\Utility\Text::truncate(
//							$kioskOrder->Kiosk['name'],
//							25,
//							[
//							    'ellipsis' => '...',
//							    'exact' => true
//							]
//						);
            $truncatedKiosk = $kioskOrder->kiosk['name'];
            //pr($truncatedKiosk);die;
        ?>
	<tr>
		<td><?php echo h($kioskOrder->id); ?>&nbsp;</td>
		<td>
            <?php echo $this->Html->link($truncatedKiosk,
                                                        ['controller'=>'stock_transfer',
                                                       'action' => 'view_stock_transfer_by_kiosk', $kioskOrder->id],
                                                        ['title'=>$kioskOrder->Kiosk['name']]);
            ?>
		</td>
		<td><?php echo $userName; ?>&nbsp;</td>
		<td><?php echo $orderOptions[$kioskOrder->status]; ?>&nbsp;</td>
		
		<td><?php echo date('d-m-y g:i A',strtotime($kioskOrder->dispatched_on));
        //echo $this->Time->format( $kioskOrder->dispatched_on,'dd.mm.yy',null,null); ?>&nbsp;</td>
		<td><?php
		//pr($kioskOrder);die;
		if(!empty($kioskOrder->received_on)){
				echo date('d-m-y g:i A',strtotime($kioskOrder->received_on));//$this->Time->format('d-m-y g:i A', $kioskOrder->received_on,null,null);
			}else{
				echo "--";
			}
		?>&nbsp;</td> 
		<?php //echo $this->Time->format('d-m-y g:i A', $kioskOrder->received_on,null,null); ?>&nbsp;</td>
		<td><?php
		if(array_key_exists($kioskOrder->received_by, $users)){
				echo $userName = $users[$kioskOrder->user_id];
			}else{
				echo $userName =  "--";
			}
		?>&nbsp;</td> 
		<td class="actions">
			<?php echo $this->Html->link(__('View Details'), array('controller' => 'stock_transfer','action' => 'view_stock_transfer_by_kiosk', $kioskOrder->id)); ?>
			<?php if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == inventory_manager):
			?>
			<?php echo $this->Html->link(__('Receive Order'), array('controller' => 'kiosk_orders','action' => 'receive_kiosk_order', $kioskOrder->id)); ?>
			<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS){ ?>
			<?php echo $this->Html->link(__('Delete Order'), array('controller' => 'kiosk_orders','action' => 'deleteKioskTransientOrder', $kioskOrder->id), array('confirm' => __('Are you sure you want to Delete order # %s?', $kioskOrder->id))); ?>
			<?php }?>
			
			
			<?php endif; ?>			
		</td>
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
	<p>
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
	<?php  if($this->request->session()->read('Auth.User.group_id') ||
					$this->request->session()->read('Auth.User.group_id') == MANAGERS ||
					$this->request->session()->read('Auth.User.group_id') == inventory_manager){
					echo $this->element('sidebar/order_menus');
				}else{
					echo $this->element('sidebar/kiosk_order_menus');
		}?>
</div>

<script>
$(function() {
  $( document ).tooltip({
   content: function () {
    return $(this).prop('title');
   }
  });
 });
</script>