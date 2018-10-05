<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php $currency = Configure::read('CURRENCY_TYPE');
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
if(!isset($selectedKiosk)){$selectedKiosk = '';}
if(!isset($reference)){$reference = '';}
if(!isset($startDate)){$startDate = '';}
if(!isset($endDate)){$endDate = '';}
//pr($importOrderReferences);
?>
<div class="mobileUnlocks index">
	<h2><?php //echo __('View/Receive Replacements'); ?></h2>
	<?php
	$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
		  $updateUrl = "/img/16_edit_page.png";
	?>
	<h2><?php echo __('View/Receive Replacements')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	</h2>
	<fieldset>
		<legend>Search</legend>
	<form action='<?php echo $this->request->webroot?>ImportOrderDetails/search_imported_references' method='get'>
		<input type="text" placeholder="enter reference" name="reference" style="width: 107px;" id="reference_id" value='<?=$reference;?>'>
		<input type="text" placeholder="from date" name="from_date" style="width: 107px;" id="datepicker1" value='<?=$startDate;?>'>
		<input type="text" placeholder="to date" name="to_date" style="width: 107px;" id="datepicker2" value='<?=$endDate;?>'>
		<input type="submit" name="submit" value="Search" style="width: 81px;height: 30px;">
		<input type="button" value="Reset" style="border-radius: 4px;padding: 4px;width: 81px;height: 30px;color: currentColor;" onClick='reset_search();'>
	</form>
	</fieldset>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('user_id','Created By'); ?></th>
			<th><?php echo $this->Paginator->sort('reference'); ?></th>
			<th><?php echo $this->Paginator->sort('status'); ?></th>
			<th><?php echo $this->Paginator->sort('received_date'); ?></th>
			<th><?php echo $this->Paginator->sort('received_by'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
		<?php foreach($importOrderReferences as $key => $importOrderReference){
            //echo 'hi';pr($importOrderReference->user_id);die;
            //pr($importOrderReference);die;
            $date = $importOrderReference->received_date;
			//$date = date('M jS, Y',strtotime($importOrderReference->received_date));//$this->Time->format($importOrderReference->received_date,'dd.mm.yy',null,null);
			$created = date('M jS, Y',strtotime($importOrderReference->created));//$this->Time->format($importOrderReference->created,'dd.mm.yy',null,null);
			if(empty($date)){
				$date = '--';
			}else{
                $date = date('M jS, Y',strtotime($importOrderReference->received_date));
            }
			$receivedBy = "--";
            //pr($users);die;
            //pr($importOrderReference);die;
			if(array_key_exists($importOrderReference->received_by,$users)){
				$receivedBy = $users[$importOrderReference->received_by];
			}
			?>
		<tr>
            <?php //echo 'hi';pr($users);die; ?>
			<td><?php
			if(array_key_exists($importOrderReference->user_id,$users)){
				echo $users[$importOrderReference->user_id];
			}
			?></td>
			<td><?=$importOrderReference->reference;?></td>
			<td><?=$status[$importOrderReference->status];?></td>
			<td><?=$date;?></td>
			<td><?=$receivedBy;?></td>
			<td><?=$created;?></td>
			<td><?=$this->Html->link('View', array('action' => 'view_imported_products',$importOrderReference->id));?>
			<?php if($importOrderReference->status == 0 && ($this->request->session()->read('Auth.User.group_id')  == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id')  == MANAGERS)){?>
			<?php #$this->Html->link('Receive', array('action' => 'receive_imported',$importOrderReference['ImportOrderReference']['id']));?>
			<?php } ?>
			</td>
		</tr>
		<?php }?>
	</tbody>
	</table>
	<p>
	<?php
	//echo $this->Paginator->counter(array(
	//'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	//));
	?>	</p>
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
	<?=$this->element('faulty_slide_menu');?>
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