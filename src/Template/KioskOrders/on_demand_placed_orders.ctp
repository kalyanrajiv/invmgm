<?php
use Cake\I18n\Time;
?>
<div class="groups index">
    
    <?php
   
    $kioskId = '';
    if(count($this->request->query)){
        $kioskId = $this->request->query['kiosk'];
    }
    echo $this->Form->create('null',array('type' => 'get','id' => 'on_demand_form'));
    if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
		$this->request->session()->read('Auth.User.group_id') == MANAGERS ||
		$this->request->session()->read('Auth.User.group_id') == inventory_manager){
	$kioskOptions = $this->Form->input('kiosk',array('options' => $kioskDropdown, 'label' => false, 'empty' => 'All', 'div'=> false, 'default' => $kioskId));
    }else{
	$kioskOptions = '';
    }
    ?>
	<?php
    
		$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
      $updateUrl = "/img/16_edit_page.png";
	?>
    <strong><?php echo '<span style="font-size: 20px; color: red;">Extra Stock Required Orders</span> '.$kioskOptions; ?></strong>
	<?php echo "<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
    <?php echo $this->Form->end();?>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 86px;"><?php echo $this->Paginator->sort('id','Placed Id'); ?></th>
                <th><?php echo $this->Paginator->sort('kiosk_id'); ?></th>
		<th><?php echo $this->Paginator->sort('user_id','Placed By'); ?></th>
                <th><?php echo $this->Paginator->sort('modified','Placed On'); ?></th>
                <th class="actions"><?php echo __('Actions'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php  
	    foreach ($OnDemandOrders as $OnDemandOrder){ ?>
<tr>
                <td><?php echo h($OnDemandOrder['id']); ?>&nbsp;</td>
				<td><?php echo $this->Html->link(__($kiosks[$OnDemandOrder['kiosk_id']]), array('controller' => 'stock_transfer','action' => 'placed_order_on_demand', $OnDemandOrder['id'])); ?></td>
               
		<td> <?php if(array_key_exists($OnDemandOrder['user_id'], $users)){
				    echo $users[$OnDemandOrder['user_id']];
			    }else{
				echo "--";
			    }
		    ?>&nbsp;
		</td>
        <?php
                $modified = $OnDemandOrder['modified'];
                if(!empty($modified)){
                     $modified->i18nFormat(
                                                        [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                                );
					$modified_date =  $modified->i18nFormat('dd-MM-yyyy HH:mm:ss');
                
                    $modified_date = date("d-m-y h:i a",strtotime($modified_date)); 
                }else{
                    $modified_date = "--";
                }
        ?>
                <td><?php echo $modified_date ; ?>&nbsp;</td>
                <td class="actions">
                    <?php echo $this->Html->link(__('View'), array('controller' => 'stock_transfer','action' => 'placed_order_on_demand', $OnDemandOrder['id'])); ?>
		    <?php echo $this->Html->link(__('trash'), array('controller' => 'kiosk_orders','action' => 'on_demand_order_trash', $OnDemandOrder['id'])); ?>
                </td>
            </tr>
            <?php } ?>
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
<?php  if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
					$this->request->session()->read('Auth.User.group_id') == MANAGERS ||
					$this->request->session()->read('Auth.User.group_id') == inventory_manager){
					echo $this->element('sidebar/order_menus');
				}else{
					echo $this->element('sidebar/kiosk_order_menus');
		}?>
	 
</div>
<script>
    $('#kiosk').change(function(){
		//alert("hi");
        $('#on_demand_form').submit();
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
 <script>
   $('#kiosk').change(function(){
	$.blockUI({ message: 'Loading ...' });
	document.getElementById("on_demand_form").submit();
	  }); 
</script>