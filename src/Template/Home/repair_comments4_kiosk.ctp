<h2>Comments(Posted by repair technicians)</h2>
 <?php
use Cake\Utility\Text;
use Cake\Routing\Router;
  
?>
<span><?php echo $this->Html->link('posted by unlock technicians',array('action' => 'unlock_comments_4_kiosk'));?></span>
<?php

$id = $start_date = $end_date = $kiosk = '';
if(count($this->request->query)){
	//pr($this->request->query);die;
    if(array_key_exists('submit',$this->request->query)){
    $id = $this->request->query['id']; //die;
    $start_date = $this->request->query['start_date'];
    $end_date = $this->request->query['end_date'];
    if(array_key_exists('data',$this->request->query) && array_key_exists('kiosk',$this->request->query['data'])){
		$kiosk = $this->request->query['data']['kiosk'];
    }
}
}
if(count($comments) &&
	 ($this->request->session()->read('Auth.User.group_id') != REPAIR_TECHNICIANS &&
	  $this->request->session()->read('Auth.User.group_id') != UNLOCK_TECHNICIANS) ||
	    count($this->request->query) != 0
	){?>
	<table>
	    <tr>
			<form action='<?php echo $this->request->webroot; ?>home/search_repair_comments_4_kiosk' method = 'get'>
			<td colspan="5">
		    <table style='width: 50%;'>
			<tr>
				<td><input type = "text" name = "id" id = "id" placeholder = "Repair Id"  autofocus  value ='<?php echo $id;?>'/></td>
			    <td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date"    value='<?php echo $start_date;?>' /></td>
				<td><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date"   value='<?php echo $end_date;?>' /></td>
			    
			    <?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
				     $this->request->session()->read('Auth.User.group_id') == MANAGERS){?>
			    <td><?=$this->Form->input('kiosk', array('options' => $kiosks, 'default' => $kiosk, 'label' => false, 'empty' => 'Choose Kiosk', 'id' => 'kioskid'));?></td>
			    <?php } ?>
			    
			   <td><input type = "submit" value = "Search" width:950px name = "submit"/></td>
			<td><input type='button' name='reset' value='Reset' style='padding:6px 8px;width:90px ;color:#333;border:1px solid #bbb;border-radius:4px;' onClick='reset_search();'/></td>
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
	    <?php //pr($comments);
        foreach($comments as $key => $comment){
           // pr($comment->MobileRepair);
            $text = $comment->brief_history;
                        $truncatedcomment =  Text::truncate(
                             $text,
                             150,
                             [
                                 'ellipsis' => '...',
                                 'exact' => false
                             ]
                         );
		 
		if(strlen($comment->brief_history)>110){
		    $bh = $comment->brief_history;
		    $history = "<a href =\"\" title = \"$bh\" alt = \"$bh\" style='color: green; font-weight: normal;'>$truncatedcomment</a>";
		}else{ 
		    $history = $comment->brief_history;
		}
		?>
	    <tr>
			<td style='width: 160px;color: blue;'><?=date('jS M, Y g:i A',strtotime($comment->created ))?></td>
			<td style='width: 50px; text-align: center;font-weight: bold;'><?=$this->Html->link($comment->mobile_repair_id,array('controller' => 'mobile_repairs', 'action'=>'view',$comment->mobile_repair_id),array('target'=>'_blank'));?></td>
			<td style='width: 140px; color: brown;'><?=$kiosks[$comment->mobile_repair->kiosk_id];?></td>
			<td style='width: 140px; color: darkviolet;'><?=$repairusers[$comment->user_id];?></td>
			<td style='color: green;'><?=$history;?></td>
	    </tr>
	    <?php } ?>
	</table>
<?php }else{
			echo "<h4>No notification for today!</h4>";
	}?>
<script>
	$(function() {
	  $( document ).tooltip();
	});
	jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
	});
	
	function reset_search(){
		jQuery( "#id" ).val("");
		jQuery("#datepicker1").val("");
		jQuery("#datepicker2").val("");
		jQuery("#kioskid").val("");
		return false;
	}
</script>