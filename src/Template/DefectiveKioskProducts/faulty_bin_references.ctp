<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$currency = Configure::read('CURRENCY_TYPE');
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
if(!isset($selectedKiosk)){$selectedKiosk = '';}
if(!isset($reference)){$reference = '';}
if(!isset($startDate)){$startDate = '';}
if(!isset($endDate)){$endDate = '';}
//pr($defectiveBinReferences);
?>
<div class="mobileUnlocks index">
	<h2><?php //echo __('Faulty Bin Logs'); ?></h2>
	<?php
	$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
		  $updateUrl = "/img/16_edit_page.png";
	?>
	<h2><?php echo __('Bin Value')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	</h2>
	<fieldset>
		<legend>Search</legend>
	<form action='<?php echo $this->request->webroot?>DefectiveKioskProducts/search_bin_references' method='get'>
	<table>
	<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){?>
	<?php if( $this->request->session()->read('Auth.User.group_id')== MANAGERS){
		//pr($manager_kiosks);die;?>
		<?="<td style=width:10px;>".$this->Form->input('selectKiosk', array('options' => $kiosks,'id' => 'selectKiosk', 'div' => false, 'empty' => 'All','default' => $selectedKiosk));?>
		<?php
		}else{?>
		<?="<td style=width:10px;>".$this->Form->input('selectKiosk', array('options' => $kiosks,'id' => 'selectKiosk', 'div' => false, 'empty' => 'All', 'default' => $selectedKiosk));?>
	<?php }} ?>
		</td>
		<td style="width: 10px;"><input type="text" placeholder="enter reference" name="reference" style="width: 107px;margin-top: 25px;height: 19px;" id="reference_id" value='<?=$reference;?>'></td>
		<td style="width: 10px;"><input type="text" placeholder="from date" name="from_date" style="width: 107px;margin-top: 25px;height: 19px;" id="datepicker1" value='<?=$startDate;?>'></td>
		<td style="width: 10px;"><input type="text" placeholder="to date" name="to_date" style="width: 107px;margin-top: 25px;height: 19px;" id="datepicker2" value='<?=$endDate;?>'></td>
		<td style="width: 10px;"><input type="submit" name="submit" value="Search" style="margin-top: 25px;height: 30px;"></td>
		<td><input type="button" value="Reset" style="border-radius: 4px;padding: 4px;width: 65px;color: currentColor;margin-top: 25px;height: 30px;" onClick='reset_search();'></td>
		</table>
		<span><b>Total Cost: <?php echo $CURRENCY_TYPE.round($sum,2);?></b></span>
	</form>
	</fieldset>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('kiosk_id'); ?></th>
			<th><?php echo $this->Paginator->sort('user_id','Created By'); ?></th>
			<th><?php echo $this->Paginator->sort('reference'); ?></th>
			<th><?php echo $this->Paginator->sort('total_cost'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
		<?php foreach($defectiveBinReferences as $key => $defectiveBinReference){
            //pr($defectiveBinReference);die;
			$created = date('M jS, Y',strtotime($defectiveBinReference->created));//$this->Time->format('M jS, Y',$defectiveBinReference->created,null,null);
			if($defectiveBinReference->total_cost > 0){
				$totalCost = $CURRENCY_TYPE.$defectiveBinReference->total_cost;
			}else{
				$totalCost = '--';
			}
			
			?>
		<tr>
			<td><?=$kiosks[$defectiveBinReference->kiosk_id];?></td>
			<td><?php
			if(array_key_exists($defectiveBinReference->user_id,$users)){
				echo $users[$defectiveBinReference->user_id];
			}
			?></td>
			<td><?=$defectiveBinReference->reference;?></td>
			<td><?php 	if($totalCost == "" || $totalCost == "--")
			{
				echo $CURRENCY_TYPE."0.00";
				}else{
					echo $totalCost;
					 }?></td>
			<td><?=$created;?></td>
			<td><?=$this->Html->link('View', array('action' => 'view_bin_detail', $defectiveBinReference->id));?>
			<?php #if($defectiveBinReference['DefectiveBinReference']['status'] == 0 && (AuthComponent::user('group_id') == ADMINISTRATORS || AuthComponent::user('group_id') == MANAGERS)){?>
			<?php #$this->Html->link('Receive', array('action' => 'receive_imported',$defectiveBinReference['DefectiveBinReference']['id']));?>
			<?php #} ?>
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
	<ul>
		<li><a href="#-1" id="archieve_bin_link">Archive Bin Table</a>
		<?php #echo $this->Html->link(__('Archive Bin Table'), array('controller' => 'defective_kiosk_products', 'action' => 'archive_bin'));?>
		</li>
	</ul>
	<?=$this->element('faulty_slide_menu');?>
</div>
<script>
	$('#archieve_bin_link').click(function(ev){
		msgStr = "Are you sure you want to archieve bin";
		if(!confirm(msgStr)){
		 ev.preventDefault();
		}else{
			location.href = '/defective_kiosk_products/archive_bin';
		}
	});
	jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
	});
	
	function reset_search(){
		jQuery( "#selectKiosk" ).val("");
		jQuery( "#reference_id" ).val("");
		jQuery("#datepicker1").val("");
		jQuery("#datepicker2").val("");
        jQuery("#selectKiosk").val("");
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