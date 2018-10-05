
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
	$webRoot = $this->request->webroot."KioskOrders/confirmedKioskOrdersSearch";
    ?>
    
    <?php
	echo $this->Form->create('kiosk_orders',array('url' => $webRoot,'type' => 'get'));
    ?>
    
    <div class="search_div">
	<fieldset>
            <legend>Search</legend>
            <div>
                <table>
                    <tr>
                            <td><input type = "text" name = "search_kw" id = "search_kw", value= '<?= $value;?>' placeholder = "Product code or title" style = "width:180px"  autofocus/></td>
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
                                               'style' => "width: 85px;margin-top: -10px;"
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
                                           'style' => "width: 85px;margin-top: -10px;"
                                           )
                                        );
                                        echo "</td>";
                                        echo "<td>";
                                        //pr($this->request->query);die;
                                        if(!empty($this->request->query['KioskOrder']['kiosk_id'])){
                                            //echo'hi';die;
                                           $kiosk_id = $this->request->query['KioskOrder']['kiosk_id'];
                                           //echo $kiosk_id;die;
                                           echo $this->Form->input(null, array(
                                                         'options' => $kiosks,
                                                         'label' => false,
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
                                            //echo'bye';die;
                                          echo $this->Form->input(null, array(
                                                           'options' => $kiosks,
                                                           'label' => false,
                                                           'div' => false,
                                                           'id'=> 'kioskid',
                                                           'name' => 'KioskOrder[kiosk_id]',
                                                           'empty' => 'Select Kiosk',
                                                           'style' => 'width:170px',
                                                           //'selected' => $kiosk_id
                                                           )
                                                           );
                                        }
                                        echo "</td>";
                                }
                        ?>
                        <td><input type = "submit" value = "Search" name = "submit",style = "width:155px"/></td>
                        <td><input type='button' name='reset' value='Reset Search' style = "width:155px" onClick='reset_search();'/></td>
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
	<strong><?php
	echo __('<span style="font-size: 20px;color: red;">Confirmed Kiosk Orders</span> <span style="font-size: 17px;">(Kiosk to Warehouse)</span>'); ?></strong>
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
	<?php  foreach ($kioskOrders as $kioskOrder):
              //pr($kioskOrder);die;          ?>
        <?php
            $truncatedKiosk = \Cake\Utility\Text::truncate(
							$kioskOrder->kiosk['name'],
							25,
							array(
							    'ellipsis' => '...',
							    'exact' => true
							)
						);
            //$truncatedKiosk = $kioskOrder->kiosk['name']
        ?>
	<tr>
		<td><?php echo h($kioskOrder->id); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($truncatedKiosk,
                                                     array('controller' => 'stock_transfer', 'action' => 'view_stock_transfer_by_kiosk', $kioskOrder->id),
                                                     array('escapeTitle' => false, 'title' => $kioskOrder->kiosk['name'])
                                                     ); ?>
		</td>
		<td><?php
		if(array_key_exists($kioskOrder->user_id,$users)){
		    echo $users[$kioskOrder->user_id];
		}else{
		    echo '--';
		}?>&nbsp;</td>
		<td><?php echo $orderOptions[$kioskOrder->status]; ?>&nbsp;</td>
		<td><?php echo date('d-m-y g:i A',strtotime($kioskOrder->dispatched_on));//$this->Time->format($kioskOrder->dispatched_on,'dd.mm.yy',null,null); ?>&nbsp;</td>
		<td><?php echo date('d-m-y g:i A',strtotime($kioskOrder->received_on));//$this->Time->format($kioskOrder->received_on,'dd.mm.yy',null,null); ?>&nbsp;</td>
		<td>
		<?php
			$userId = $kioskOrder->received_by;
			if(!empty($userId)){
				if(array_key_exists($userId,$users)){
					$username = $users[$userId];
				}else{
					$username = "";
				}
				echo $this->Html->link($username, array('controller' => 'users', 'action' => 'view', $userId));
			}
		?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View Details'), array('controller' => 'stock_transfer','action' => 'view_stock_transfer_by_kiosk', $kioskOrder->id)); ?>
			<?php #echo $this->Html->link(__('Create Dispute'), array('controller' => 'stock_transfer','action' => 'create_dispute', $kioskOrder['KioskOrder']['id'])); ?>			
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