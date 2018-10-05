<h2>Comments(Posted by kiosk users)</h2>
<?php
$id = $start_date = $end_date = $kiosk = '';
if(count($this->request->query)){
	//pr($this->request->query);die;
	if(array_key_exists('id',$this->request->query)){
		$id = $this->request->query['id'];
	}
    if(array_key_exists('start_date',$this->request->query)){
		$start_date = $this->request->query['start_date'];
    }
	if(array_key_exists('end_date',$this->request->query)){
		$end_date = $this->request->query['end_date'];
	}
    if(array_key_exists('kiosk',$this->request->query)){
		$kiosk = $this->request->query['kiosk'];
    }
}
if(count($comments) && $this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS ||
	    count($this->request->query) != 0){
    ?>
	<table>
	    <tr>
		<?php echo $this->Form->create(null,array('url' => array('controller' => 'home', 'action' => 'search_comments_4_service_center'), 'type' => 'get'));?>
		<td colspan="5">
		    <table style='width: 50%;'>
			<tr>
			    <td><?=$this->Form->input('id', array('type' => 'text','id'=>'MobileRepairSaleId' ,'value' => $id, 'label' => false, 'placeholder' => 'Unlock Id','style'=>"width: 70px;"));?></td>
			    <td><?=$this->Form->input('start_date', array('type' => 'text','id'=>'MobileRepairSaleStartDate', 'value' => $start_date, 'label' => false, 'placeholder' => 'Start Date','style'=>"width: 70px;"));?></td>
			    <td><?=$this->Form->input('end_date', array('type' => 'text','id'=>'MobileRepairSaleEndDate' ,'value' => $end_date, 'label' => false, 'placeholder' => 'End Date','style'=>"width: 70px;"));?></td>
			    <td><?=$this->Form->input('kiosk', array('options' => $kiosks,'id'=>'kioskid' ,'default' => $kiosk, 'label' => false, 'empty' => 'Choose Kiosk', 'id' => 'kioskid'));?></td>
			    <td><?=$this->Form->Input('Reset', array('type' => 'button', 'label' => false, 'id' => 'reset', 'onclick' => 'return reset_search();', 'style' => 'padding: 4px 8px;background: #dcdcdc;background-image: -webkit-linear-gradient(top, #fefefe, #dcdcdc);color: #333;border: 1px solid #bbb;border-radius: 4px;'));?></td>
			    <td>
                <?=$this->Form->Submit('Search',array('name'=>'submit'));?>
                <?=$this->Form->end();?></td>
			</tr>
		    </table>
		</td>
	    </tr>
	    <tr>
		<th><?php echo $this->Paginator->sort('created','Date');?></th>
		<th><?php echo $this->Paginator->sort('mobile_repair_id','Repair#');?></th>
		<th>Kiosk</th>
		<th><?php echo $this->Paginator->sort('user_id');?></th>
		<th><?php echo $this->Paginator->sort('brief_history','Comment');?></th>
	    </tr>
	    <?php foreach($comments as $key => $comment){
            //pr($comment);
            //pr($comment['mobile_repair']['kiosk_id']);
            //pr($kiosks);die;
		$truncatedcomment  = \Cake\Utility\Text::truncate(
						$comment['brief_history'],110,
					[
						'ellipsis' => '...',
						'exact' => false,
						 
					]);
		if(strlen($comment['brief_history'])>110){
		    $bh = $comment['brief_history'];
		    $history = "<a href =\"\" title = \"$bh\" alt = \"$bh\" style='color: green; font-weight: normal;'>$truncatedcomment</a>";
		}else{ 
		    $history = $comment['brief_history'];
		}
		?>
	    <tr>
		<td style='width: 160px;color: blue;'><?=date('jS M, Y g:i A',strtotime($comment['created']));//$this->Time->format('jS M, Y g:i A', $comment['created'],null,null);?></td>
		<td style='width: 50px; text-align: center;font-weight: bold;'><?=$this->Html->link($comment['mobile_repair_id'],array('controller' => 'mobile_repairs', 'action'=>'view',$comment['mobile_repair_id']),array('target'=>'_blank'));?></td>
		<td style='width: 140px; color: brown;'>
        <?php if(array_key_exists($comment['mobile_repair']['kiosk_id'],$kiosks)){ ?>
        <?=$kiosks[$comment['mobile_repair']['kiosk_id']];?>
        <?php } ?>
        </td>
		<td style='width: 140px; color: darkviolet;'><?=$comment['user']['username'];?></td>
		<td style='color: green;'><?=$history;?></td>
	    </tr>
	    <?php } ?>
	</table>
	<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
</div>
<?php }else{
			echo "<h4>No notification for today!</h4>";
	}?>
<script>
	$(function() {
	  $( document ).tooltip();
	});
	
	jQuery(function() {
		jQuery( "#MobileRepairSaleStartDate" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#MobileRepairSaleEndDate" ).datepicker({ dateFormat: "d M yy " });
	});
	
	function reset_search(){
		jQuery( "#MobileRepairSaleId" ).val("");
		jQuery("#MobileRepairSaleStartDate").val("");
		jQuery("#MobileRepairSaleEndDate").val("");
		jQuery("#kioskid").val("");
		return false;
	}
</script>