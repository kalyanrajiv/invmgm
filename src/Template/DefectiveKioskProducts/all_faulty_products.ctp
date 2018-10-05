<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$currency = Configure::read('CURRENCY_TYPE');
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
if(!isset($selectedKiosk)){$selectedKiosk = '';}
if(!isset($reference)){$reference = '';}
if(!isset($startDate)){$startDate = '';}
if(!isset($endDate)){$endDate = '';}
if(!isset($date_type)){$date_type = 'date_of_movement';}
?>
<div class="mobileUnlocks index">
	<h2><?php //pr($this->request->query);die;
    if(array_key_exists('selectKiosk',$this->request->query)){
        $selectKiosk = $this->request->query['selectKiosk'];
    }else{
        $selectKiosk = "";
    }
    ?></h2>
	<?php
	$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
		  $updateUrl = "/img/16_edit_page.png";
	?>
	<td><h2><?php echo __('View Faulty Received Items')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	</h2></td>
	<fieldset>
		<legend>Search</legend>
	<form action='<?php echo $this->request->webroot?>defective-kiosk-products/search_all_faulty' method='get'>
	<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){
		?>
		<span style="display: flex;margin-left: 324px;margin-top: -16px;margin-bottom: -7px;">
			<input type="radio" name="date_type" style="margin-left: -313px;" value="date_of_movement" <?=($date_type == 'date_of_movement') ? 'checked' : '' ;?>/>recived date&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="date_type" value="created_date" <?=($date_type == 'created_date') ? 'checked' : '' ;?>/>Created Date
		</span>
		<table>
		<td style="width: 5px;"><?php
		if( $this->request->session()->read('Auth.User.group_id')!= MANAGERS){
			echo $this->Form->input('selectKiosk', array('value' => $selectKiosk,'options' => $kiosks, 'div' => false, 'empty' => 'All', 'default' => $selectedKiosk));
		}else{
			echo $this->Form->input('selectKiosk', array('value' => $selectKiosk,'options' => $kiosks, 'div' => false, 'empty' => 'All', 'default' => $selectedKiosk));
		}
		
		?>
		</td>
	<?php } ?>
		<td style="width: 10px;"><input type="text" placeholder="from date" name="from_date" style="width: 107px;margin-top: 25px;height: 19px;" id="datepicker1" value='<?=$startDate;?>'></td>
		<td style="width: 10px;"><input type="text" placeholder="to date" name="to_date" style="width: 107px;margin-top: 25px;height: 19px;" id="datepicker2" value='<?=$endDate;?>'></td>
		<td style="width: 10px;"><input type="submit" name="submit" value="Search" style="margin-top: 25px;height: 30px;"></td>
		<td><input type="button" value="Reset" style="border-radius: 4px;padding: 4px;width: 65px;color: currentColor;margin-top: 25px;height: 30px;" onClick='reset_search();'></td>
		</table>
		<?php
			if(isset($sumResult)){
				echo "<div style='float: right;'>";
				echo "Total Cost : ".$CURRENCY_TYPE.$sumResult[0]['total'];
				echo "</div>";
			}
		?>
		<div>**Cost price will show correct values for entries post 14th May 2016</div>
	</form>
	</fieldset>
	<div>**Data from table:<strong>defective_kiosk_products</strong> (When Marked Status:0; Received: 1)</div>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th style='width: 170;'><?php echo $this->Paginator->sort('product_id'); ?></th>
		<th><?php echo $this->Paginator->sort('product_id','Prod Code'); ?></th>
		<th><?php echo $this->Paginator->sort('quantity','Qty'); ?></th>
		<th><?php echo $this->Paginator->sort('cost_price'); ?></th>
		<th><?php echo $this->Paginator->sort('kiosk_id'); ?></th>
		<th><?php echo $this->Paginator->sort('user_id'); ?></th>
		<th><?php echo $this->Paginator->sort('received_by'); ?></th>
		<th><?php echo $this->Paginator->sort('reference'); ?></th>
		<th><?php echo $this->Paginator->sort('remarks'); ?></th>
		<th><?php echo $this->Paginator->sort('received_by','recive date'); ?></th>
		<th><?php echo $this->Paginator->sort('created'); ?></th>
	</tr>
	</thead>
	<tbody><?php //pr($defectiveKioskProducts);die;?>
		<?php foreach($defectiveKioskProducts as $key => $defectiveKioskProduct){
			//$created = $this->Time->format('M jS, Y',$defectiveKioskProduct['DefectiveKioskProduct']['created'],null,null);
			$created = date('d-m-y',strtotime($defectiveKioskProduct['created']));
			//$date_of_movement = $this->Time->format('M jS, Y',$defectiveKioskProduct['DefectiveKioskProduct']['date_of_movement'],null,null);
			 $recive_date = $defectiveKioskProduct['receive_date'];
            if(empty($recive_date)){
				$recive_date = '--';
			}else{
                $recive_date =  date('d-m-y',strtotime($defectiveKioskProduct['receive_date']));
            }
           // $recive_date = date('d-m-y',strtotime($defectiveKioskProduct['receive_date']));
			$receivedBy = "---";
			//pr($users);
			if( array_key_exists($defectiveKioskProduct['received_by'],$users)){
				$receivedBy = $users[$defectiveKioskProduct['received_by']];
			}
			?>
		<tr>
			<td><?=$productNames[$defectiveKioskProduct['product_id']];?></td>
			<td><?=$productCodes[$defectiveKioskProduct['product_id']];?></td>
			<td><?=$defectiveKioskProduct['quantity'];?></td>
			<td><?=$CURRENCY_TYPE.$defectiveKioskProduct['cost_price'];?></td>
			<td><?php if(array_key_exists($defectiveKioskProduct['kiosk_id'],$kiosks)){
                echo $kiosks[$defectiveKioskProduct['kiosk_id']];
            }else{
                echo "--";
            }?></td>
			<td><?php if(array_key_exists($defectiveKioskProduct['user_id'],$users)){
                $users[$defectiveKioskProduct['user_id']];
            }else{
                echo"--";
            }?></td>
			<td><?=$receivedBy;?></td>
			<td><?=$defectiveKioskProduct['reference'];?></td>
			<td><?=$faulty_conditions[$defectiveKioskProduct['remarks']];?></td>
			<td nowrap><?=$recive_date;
         
            ?></td>
			<td><?=$created;?></td>
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
