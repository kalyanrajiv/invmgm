<?php
use Cake\I18n\Time;
?>
<style>
@-webkit-keyframes blink {
    50% {
        background: rgba(255, 0, 0, 0.5);
    }
}
@-moz-keyframes blink {
    50% {
        background: rgba(255, 0, 0, 0.5);
    }
}
@keyframes blink {
    50% {
        background: rgba(255, 0, 0, 0.5);
    }
}
.blink {
    -webkit-animation-direction: normal;
    -webkit-animation-duration: 5s;
    -webkit-animation-iteration-count: infinite;
    -webkit-animation-name: blink;
    -webkit-animation-timing-function: linear;
    -moz-animation-direction: normal;
    -moz-animation-duration: 5s;
    -moz-animation-iteration-count: infinite;
    -moz-animation-name: blink;
    -moz-animation-timing-function: linear;
    animation-direction: normal;
    animation-duration: 5s;
    animation-iteration-count: infinite;
    animation-name: blink;
    animation-timing-function: linear;
}
</style>
<div class="groups index">
    <?php
   
    $kioskId = '';
    if(count($this->request->query)){
		if(array_key_exists('kiosk',$this->request->query)){
			$kioskId = $this->request->query['kiosk'];
		}
        
    }
    
    echo $this->Form->create('null',array('type' => 'get','id' =>'KioskPlacedOrdersForm'));
  
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
    <strong><?php echo '<span style="font-size: 20px; color: red;">Order Placed</span> '.$kioskOptions; ?></strong>
	<?php echo "<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
    <?php echo $this->Form->end();?>
	<?php
	if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS && $settingArr['merge_by_kiosk'] == 1){
		if(!empty($data)){ ?>
			<form action="/kiosk-orders/merge_order" method="post" onsubmit="return merge_orders()">
				
		<input type="submit" name="merge_data"  value="Merge" confirm('are you sure you want to merge these order ?'); style="width: 91px;margin-left: 424px;" />
		
		<?php }
	}else{
		if($kioskId != "" && !empty($data)){?>
		<form action="/kiosk-orders/merge_order" method="post" onsubmit="return merge_orders()">
		<input type="submit" name="merge_data"  value="Merge" confirm('are you sure you want to merge these order ?'); style="width: 91px;margin-left: 424px;" />
		
		<?php }
	}
		?>
	
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
				
                <th style="width: 86px;"><?php echo $this->Paginator->sort('id','Placed Id'); ?></th>
				<?php if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS && $settingArr['merge_by_kiosk'] == 1){ ?>
				<th></th>
				<?php }else{
					if($kioskId != ""){
					?>
				<th></th>
				<?php }}?>
                <th><?php echo $this->Paginator->sort('kiosk_id'); ?></th>
		<th><?php echo $this->Paginator->sort('user_id','Placed By'); ?></th>
                <th><?php echo $this->Paginator->sort('modified','Placed On'); ?></th>
                <th class="actions"><?php echo __('Actions'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php  $kioskPlacedOrders = $data ; 
            //pr($kioskPlacedOrders);?>
			<input name="_csrfToken" autocomplete="off" value="<?php echo $token = $this->request->getParam('_csrfToken');?>" type="hidden">
	    <?php foreach ($kioskPlacedOrders as $kioskPlacedOrder):
		if($kioskPlacedOrder->weekly_order == '1'){ ?>
		    <tr style="color: blue;background: yellow;">
		<?php }elseif($kioskPlacedOrder->user_id ==$croneId){?>
		    <tr style="color: blue;">
		<?php }elseif($kioskPlacedOrder->merged == 1){
			if($kioskPlacedOrder->kiosk_merged == 1){?>
	    <tr class='blink'>	
			<?php }else{?>
			<tr style="background-color: lightgreen;;">	
			<?php }?>
		<?php }elseif($kioskPlacedOrder->kiosk_merged == 1){ ?>
		<tr class='blink'>	
			<?php }else{?>
	    <tr>    
		<?php } ?>
                <td><?php echo h($kioskPlacedOrder->id); ?>&nbsp;</td>
				
				<?php
				if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS && $settingArr['merge_by_kiosk'] == 1){
					//if($kioskId != ""){ ?>
					<td><input type="checkbox" name="merge_ids[]" value="<?php echo $kioskPlacedOrder->id;?>"/>
					<input type="hidden" id="kiosk_<?php echo $kioskPlacedOrder->id;?>" value = "<?php echo $kioskPlacedOrder->kiosk_id; ?>" />
					<input type="hidden" name="kiosk_id" value = "<?php echo $kioskPlacedOrder->kiosk_id; ?>" />
					</td>
					<?php //} 
				}else{
					if($kioskId != ""){?>
					<td><input type="checkbox" name="merge_ids[]" value="<?php echo $kioskPlacedOrder->id;?>"/>
					<input type="hidden" id="kiosk_<?php echo $kioskPlacedOrder->id;?>" value = "<?php echo $kioskPlacedOrder->kiosk_id; ?>" />
					<input type="hidden" name="kiosk_id" value = "<?php echo $kioskPlacedOrder->kiosk_id; ?>" />
					</td>
					<?php } 
				}?>	
				
				
				<td><?php echo $this->Html->link(__($kiosks[$kioskPlacedOrder->kiosk_id]), array('controller' => 'stock_transfer','action' => 'placed_order', $kioskPlacedOrder->id)); ?></td>
              
		<td><?php if(array_key_exists($kioskPlacedOrder->user_id, $users)){
				    echo $users[$kioskPlacedOrder->user_id];
			    }else{
				echo "--";
			    }
		    ?>&nbsp;
		 
                <td><?php
                 $modified = $kioskPlacedOrder->modified;
                if(!empty($modified)){
                     $modified->i18nFormat(
                                                        [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                                );
					$modified_date =  $modified->i18nFormat('dd-MM-yyyy HH:mm:ss');
                    $modified_date = date("d-m-y h:i a",strtotime($modified_date)); 
                }else{
                    $modified_date = "--";
                }
                
                
				echo  $modified_date; ?>&nbsp;</td>
			    <td class="actions">
                    <?php echo $this->Html->link(__('View'), array('controller' => 'stock_transfer','action' => 'placed_order', $kioskPlacedOrder->id)); ?>
		    <?php echo $this->Html->link(__('trash'), array('controller' => 'kiosk_orders','action' => 'place_order_trash', $kioskPlacedOrder->id)); ?>
                </td>
				
            </tr>
		
            <?php endforeach; ?>
	    <tr><td colspan="4">&nbsp;</td></tr>
	    <tr><td colspan="4"><i style="color: blue;">**Highlighted orders are placed by cron**</i><br/><i style="background: yellow; color: blue;">**Highlighted orders with yellow background are weekly placed order by cron**</i>
		</br><i style="background: lightgreen;">**Highlighted orders are Merged Orders**</i>
		</td></tr>
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
        $('#KioskPlacedOrdersForm').submit();
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
	document.getElementById("KioskPlacedOrdersForm").submit();
	  }); 
</script>
<script>
	function merge_orders(args) {
		var count = 0
		var old_val = "";
		$.each($("input[name='merge_ids[]']:checked"), function(){
			count ++;
			var value = $(this).val();
			var test = "kiosk_"+value;
			old_val = $('#'+test).val();
		});
		if (count <= 1) {
            alert("Please Choose more then one order to merge");
            return false;
        }
		if (old_val == "") {
			alert("Please Select Kiosk");
            return false;
        }
		if(confirm("Are You Sure You Want To Merge These Orders")){
			
		}else{
			return false;
		}
    }
</script>