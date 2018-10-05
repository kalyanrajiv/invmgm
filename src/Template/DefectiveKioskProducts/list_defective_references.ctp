<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$currency = Configure::read('CURRENCY_TYPE');
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
if(!isset($selectedKiosk)){$selectedKiosk = '';}
if(!isset($reference)){$reference = '';}
if(!isset($startDate)){$startDate = '';}
if(!isset($endDate)){$endDate = '';}
if(!isset($date_type)){$date_type = 'created_date';}
?>
<div class="mobileUnlocks index">
	<h2><?php //pr($this->request->query);
   
    ?></h2>
	<?php
	$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
		  $updateUrl = "/img/16_edit_page.png";
	?>
	<td><h2><?php echo __('View/Receive Faulty')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	</h2></td>
	<fieldset>
		<legend>Search</legend>
	<form action='<?php echo $this->request->webroot?>defective-kiosk-products/search_defective_references' method='get'>
	<span style="display: flex;margin-left: 324px;margin-top: -16px;margin-bottom: -7px;">
			<input type="radio" name="date_type" style="margin-left: -313px;" value="receiving_date" <?=($date_type == 'receiving_date') ? 'checked' : '' ;?>/>Receiving Date&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="date_type" value="created_date" <?=($date_type == 'created_date') ? 'checked' : '' ;?>/>Created Date
	</span>
	<table>
	<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){
		 if(array_key_exists('selectKiosk',$this->request->query)){
            $selectKiosk = $this->request->query['selectKiosk'];
         }else{
            $selectKiosk = "";
         }
		?>
		<?php
		if( $this->request->session()->read('Auth.User.group_id')!= MANAGERS){
			echo "<td style=width:10px;>".$this->Form->input('selectKiosk', array('options' => $kiosks,'value' => $selectKiosk , 'div' => false, 'empty' => 'All', 'default' => $selectedKiosk));
		}else{
			echo "<td style=width:10px;>".$this->Form->input('selectKiosk', array('options' => $kiosks,'value' => $selectKiosk , 'div' => false,  'empty' => 'All','default' => $selectedKiosk));
		}
		
		?>
	<?php } ?>
	</td>
		<td style="width: 10px;"><input type="text" placeholder="enter reference" name="reference" style="width: 107px;margin-top: 25px;height: 19px;" id="reference_id" value='<?=$reference;?>'></td>
		<td style="width: 10px;"><input type="text" placeholder="from date" name="from_date" style="width: 107px;margin-top: 25px;height: 19px;" id="datepicker1" value='<?=$startDate;?>'></td>
		<td style="width: 10px;"><input type="text" placeholder="to date" name="to_date" style="width: 107px;margin-top: 25px;height: 19px;" id="datepicker2" value='<?=$endDate;?>'></td>
		<td style="width: 10px;"><input type="submit" name="submit" value="Search" style="margin-top: 25px;height: 30px;"></td>
		<td><input type="button" value="Reset" style="border-radius: 4px;padding: 4px;width: 65px;color: currentColor;margin-top: 25px;height: 30px;" onClick='reset_search();'></td>
	</table>
	</form>
	</fieldset>
	<span><i>*Date of receiving is the receiving date of the recently received product</i></span><br/>
	<span style="color: darkorange;"><strong><i>**Highlighted rows have not been fully received</i></strong></span><br/>
	<span><i>***Data From <strong>defective_kiosk_references</strong></i></span>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('kiosk_id'); ?></th>
			<th><?php echo $this->Paginator->sort('user_id','Created By'); ?></th>
			<th><?php echo $this->Paginator->sort('reference'); ?></th>
			
			<th><?php echo $this->Paginator->sort('date_of_receiving'); ?></th>
			<th><?php echo $this->Paginator->sort('received_by'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
		<?php foreach($defectiveReferences as $key => $defectiveReference){
            
			$date = $defectiveReference->date_of_receiving;//date('jS M, Y g:i A',strtotime($defectiveReference->date_of_receiving));//$this->Time->format('jS M, Y g:i A',$defectiveReference['date_of_receiving'],null,null);
			$created = date('jS M, Y g:i A',strtotime($defectiveReference->created));//$this->Time->format('jS M, Y g:i A',$defectiveReference['created'],null,null);
			if(empty($date)){
				$date = '--';
			}else{
                $date = date('jS M, Y g:i A',strtotime($defectiveReference->date_of_receiving));
            }
			$receivedBy = "--";
			if(array_key_exists($defectiveReference['received_by'],$users)){
				$receivedBy = $users[$defectiveReference['received_by']];
			}
	?>
	<?php 
		if(array_key_exists($defectiveReference['id'], $receivingArr)){
			?>
			<tr style="background-color: darkorange;">
		<?php }else{ ?>
			<tr>
		<?php }
			?>
			<td><?php if(array_key_exists($defectiveReference['kiosk_id'],$kiosks)){
                echo $kiosks[$defectiveReference['kiosk_id']];
                }else{
                    echo "--";
                }?></td>
			<td><?php if(array_key_exists($defectiveReference['user_id'],$users)){
                echo $users[$defectiveReference['user_id']];
                }else{
                    echo "--";
                }?></td>
			<td><?=$defectiveReference['reference'];?></td>
			
			<td><?=$date;?></td>
			<td><?=$receivedBy;?></td>
			<td><?=$created;?></td>
			<td><?=$this->Html->link('View/Receive', array('action' => 'view_transient_faulty',$defectiveReference['id']));?>
			<?php if($defectiveReference['DefectiveKioskReference']['status'] == 0 && ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS)){?>
			<?php //$this->Html->link('Receive', array('action' => 'receive_faulty',$defectiveReference['DefectiveKioskReference']['id']));?>
			<?php } ?>
			</td>
		</tr>
		<?php }?>
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
	<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){?>
	 <?=$this->element('faulty_slide_menu');?>
	<?php }else{ ?>
	 <ul>
	   <li><?php echo $this->Html->link(__('Add Faulty'), array('controller' => 'defective_kiosk_products', 'action' => 'add')); ?></li>
	   <li><?php echo $this->Html->link(__('Faulty References'), array('controller' => 'defective_kiosk_products', 'action' => 'list_defective_references')); ?></li>
	   <li><?php echo $this->Html->link(__('View Faulty'), array('controller' => 'defective_kiosk_products', 'action' => 'view_faulty_products')); ?></li> 
	 </ul>
	<?php } ?>
</div>
<script>
	jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
	});
	
	function reset_search(){
		jQuery( "#selectKiosk" ).val("");
		jQuery( "#reference_id" ).val("");
		jQuery("#datepicker1").val("");
		jQuery("#datepicker2").val("");
	}
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