<div class="groups index">
    <?php
    $kioskId = '';
    if(count($this->request->query)){
        $kioskId = $this->request->query['kiosk'];
    }
    echo $this->Form->create('null',array('id' => 'KioskTrashForm','type' => 'get'));
	echo'<table>';
    if($this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id')== ADMINISTRATORS){
	$kioskOptions = $this->Form->input('kiosk',array('options' => $kioskDropdown, 'id' => 'KioskKiosk','label' => false, 'empty' => 'All', 'div'=> false, 'default' => $kioskId));
    }else{
	$kioskOptions = '';
    }
    ?>
    <td style="width: 10px;"><strong><?php echo '<span style="font-size: 20px; color: red;">Order Placed</span> '.$kioskOptions; ?></strong></td>
	 <td style="width: 10px;"><input type="submit" name = "delete" value="delete all" style="margin-top: 40px;" id="delete_all" /></td>
	 <td><input type="submit" value="delete_selected" name="delete_selected" id="delete_selected" style="margin-top: 40px;"/></td>
	 </table>
    <?php echo $this->Form->end();?>
	<form action="" method='post' >
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <td></td>
                <th><?php echo $this->Paginator->sort('id'); ?></th>
                <th><?php echo $this->Paginator->sort('kiosk_id'); ?></th>
		<th><?php echo $this->Paginator->sort('user_id','Placed By'); ?></th>
                <th><?php echo $this->Paginator->sort('modified','Placed On'); ?></th>
                <th class="actions"><?php echo __('Actions'); ?></th>
				<th></th>
            </tr>
        </thead>
        <tbody>
			
				
            <?php $kioskPlacedOrders = $data ;
	    foreach ($kioskPlacedOrders as $kioskPlacedOrder):
		if($kioskPlacedOrder['weekly_order'] == '1'){ ?>
		    <tr style="color: blue;background: yellow;">
		<?php }elseif($kioskPlacedOrder['user_id']==$croneId){?>
		    <tr style="color: blue;">
		<?php }else{?>
	    <tr>    
		<?php } ?>
		<td><input type='checkbox' name='checked[<?php echo $kioskPlacedOrder['id']?>]' /></td>
                <td><?php echo h($kioskPlacedOrder['id']); ?>&nbsp;</td>
                <td><?php echo $kiosks[$kioskPlacedOrder['kiosk_id']]; ?>&nbsp;</td>
		<td><?php if(array_key_exists($kioskPlacedOrder['user_id'], $users)){
				    echo $users[$kioskPlacedOrder['user_id']];
			    }else{
				echo "--";
			    }
		    ?>&nbsp;
		</td>
        <?php
         $created_on = $kioskPlacedOrder['modified'];
        if(!empty($created_on)){
              $created_on->i18nFormat(
                                                [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                        );
			$created_on_date =  $created_on->i18nFormat('dd-MM-yyyy HH:mm:ss');
            $created_on_date = date("d-m-y h:i a",strtotime($created_on_date)); 
        }else{
            $created_on_date = "--";
        }
        ?>
                <td><?php echo  $created_on_date; ?>&nbsp;</td>
                <td class="actions">
                    <?php echo $this->Html->link(__('View'), array('controller' => 'stock_transfer','action' => 'placed_order', $kioskPlacedOrder['id'])); ?>
		    <?php echo $this->Html->link(__('Restore'), array('controller' => 'kiosk_orders','action' => 'restore', $kioskPlacedOrder['id'])); ?>
		    <?php #echo $this->Html->link(__('Delete'), array('controller' => 'kiosk_orders','action' => 'delete_order', $kioskPlacedOrder['KioskPlacedOrder']['id'])); ?>
		    <?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete_order', $kioskPlacedOrder['id']), array(), __('Are you sure you want to delete # %s?', $kioskPlacedOrder['id'])); ?>
                </td>
            </tr>
            <?php endforeach; ?>
			</form>
	    <tr><td colspan="4">&nbsp;</td></tr>
	    <tr><td colspan="4"><i style="color: blue;">**Highlighted orders are placed by cron**</i><br/><i style="background: yellow; color: blue;">**Highlighted orders with yellow background are weekly placed order by cron**</i></td></tr>
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
	<?php echo $this->element('sidebar/order_menus');?>
	 
</div>
<script>
    $('#KioskKiosk').change(function(){
        $('#KioskTrashForm').submit();
    });
	
	$('#delete_selected').click(function(){
		if(confirm("Are you sure you want to delete this?")){
			return true;
		}else{
			 return false;
		}
	});
	
	$('#delete_all').click(function(){
		if(confirm("Are you sure you want to delete all orders?")){
			return true;
		}else{
			 return false;
		}
	});
	
</script>
 <script>
   $('#KioskKiosk').change(function(){
	$.blockUI({ message: 'Loading ...' });
	document.getElementById("KioskTrashForm").submit();
	  }); 
</script>