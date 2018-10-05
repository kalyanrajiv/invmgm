<h2>Comments(Posted by kiosk users)</h2>
 <?php
use Cake\Utility\Text;
use Cake\Routing\Router;
  
?>
<?php
$id = $start_date = $end_date = $kiosk = '';
if(count($this->request->query)){
    $id = $this->request->query['id'];
    $start_date = $this->request->query['start_date'];
    $end_date = $this->request->query['end_date'];
    if(array_key_exists('kiosk',$this->request->query)){
	$kiosk = $this->request->query['kiosk'];
    }
}
if(count($comments) && $this->request->session()->read('Auth.User.group_id') == UNLOCK_TECHNICIANS ||
	    count($this->request->query) != 0){?>
	<table>
	    <tr>
		<?php echo $this->Form->create(null,array('url' => array('controller' => 'home', 'action' => 'search_comments_4_unlock_center'), 'type' => 'get'));?>
		<td colspan="5">
		    <table style='width: 50%;'>
			<tr>
			    <td><?=$this->Form->input('id', array('type' => 'text', 'value' => $id, 'label' => false, 'placeholder' => 'Unlock Id','style'=>"width: 70px;"));?></td>
			    <td><?=$this->Form->input('start_date', array('type' => 'text', 'value' => $start_date, 'label' => false, 'placeholder' => 'Start Date','style'=>"width: 70px;"));?></td>
			    <td><?=$this->Form->input('end_date', array('type' => 'text', 'value' => $end_date, 'label' => false, 'placeholder' => 'End Date','style'=>"width: 70px;"));?></td>
			    <td><?=$this->Form->input('kiosk', array('options' => $kiosks, 'default' => $kiosk, 'label' => false, 'empty' => 'Choose Kiosk', 'id' => 'kioskid'));?></td>
			    <td><?=$this->Form->Input('Reset', array('type' => 'button', 'label' => false, 'id' => 'reset', 'onclick' => 'return reset_search();', 'style' => 'padding: 4px 8px;background: #dcdcdc;background-image: -webkit-linear-gradient(top, #fefefe, #dcdcdc);color: #333;border: 1px solid #bbb;border-radius: 4px;'));?></td>
			    <td><?php
                echo $this->Form->submit("Search",array('name'=>'submit'));
                echo $this->Form->end(); 
               ?></td>
			</tr>
		    </table>
		</td>
	    </tr>
	    <tr>
		<th><?php echo $this->Paginator->sort('created','Date');?></th>
		<th><?php echo $this->Paginator->sort('mobile_unlock_id','Unlock#');?></th>
		<th>Kiosk</th>
		<th><?php echo $this->Paginator->sort('user_id');?></th>
		<th><?php echo $this->Paginator->sort('brief_history','Comment');?></th>
	    </tr>
	    <?php foreach($comments as $key => $comment){
           // pr($comment);
              $text = $comment->brief_history;
                        $truncatedcomment =  Text::truncate(
                             $text,
                             110,
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
		<td style='width: 160px;color: blue;'><?=date('jS M, Y g:i A',strtotime($comment->created ));?></td>
		<td style='text-align: center;font-weight: bold;'><?=$this->Html->link($comment->mobile_unlock_id,array('controller' => 'mobile_unlocks', 'action'=>'view',$comment->mobile_unlock_id),array('target'=>'_blank'));?></td>
		<td style='color: brown;'><?=$kiosks[$comment->mobile_unlock->kiosk_id];?></td>
		<td style='color: darkviolet;'><?=$users[$comment->user_id];?></td>
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
		jQuery( "#start-date" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#end-date" ).datepicker({ dateFormat: "d M yy " });
	});
	
	function reset_search(){
		jQuery( "#id" ).val("");
		jQuery("#start-date").val("");
		jQuery("#end-date").val("");
		jQuery("#kioskid").val("");
		return false;
	}
</script>