<div class="stock index">
	<fieldset style='padding: 0px;'>
		<?php
		$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
		$updateUrl = "/img/16_edit_page.png";
		?>
		<legend>Search<?php echo "<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>";  ?>
		<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?></legend>
		<?php
		if(!$start){
			$start = '';
		}
		if(!$end){
			$end = '';
		}
		echo $this->Form->create(null, array('url' => array('controller' => 'Stock', 'action' => 'search_stock_level'),'type' => 'get'));?>
		<table>
			<tr>
				<td><?=$this->Form->create('SearchStockLevel');?></td>
				<td><?=$this->Form->input('start_date',array('id'=>'SearchStockLevelStartDate','type' => 'text','readonly' => 'readonly', 'value' => $start));?></td>
				<td><?=$this->Form->input('end_date',array('id'=>'SearchStockLevelEndDate','type' => 'text','readonly' => 'readonly', 'value' => $end));?></td>
				<td><?=$this->Form->input('order_id',array('id' => 'order-id','type' => 'text', 'value' => $order_id));?></td>
				<td><?=$this->Form->submit('Search',array('name'=>'submit'));?></td>
                <td><?=$this->Form->end();?></td>
				<td><?=$this->Form->input('Reset',array('label' => false, 'type' => 'button', 'onclick' => 'return reset_search();', 'style' => 'margin-top: 12px;height: 33px;width: 70px;border-radius: 5px;'));?></td>
			</tr>
		</table>
	</fieldset>
	
	<table>
		<tr>
			<th>Order Id</th>
			<th>Created On</th>
			<th>Created By</th>
			<th>Actions</th>
		</tr>
		<?php
		foreach($viewStockData as $key => $viewStockInfo){
			//pr($viewStockInfo);die;
                    $userId = $viewStockInfo['user_id'];
                    ?>
		<tr>
			<td><?php echo $viewStockInfo['order_id'];?></td>
			<td><?php
			echo date("jS M, Y g:i A",$viewStockInfo['order_id']);
			//echo $this->Time->format($viewStockInfo['order_id'],'dd.mm.yy',null,null);?></td>
			<td><?php
            //pr($nameArr);die;
            //echo $userId;die;
				if(array_key_exists($userId ,$nameArr)){
					echo $nameArr[$userId];
				}
				else{
					echo "--";
				}
			//echo $nameArr[$userId];die;?></td>
			<td><?php echo $this->Html->link(__('View List'), array('action' => 'datewise_stock_level',$viewStockInfo['order_id'])); ?></td>
		</tr>
		<?php } ?>
	</table>
	 <div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Warehouse <br/>Placed Orders'), array('controller' => 'stock', 'action' => 'view_stock_level'),array('escape' => false)); ?> </li>
		<li><?php echo $this->Html->link(__('Stock Below Level'), array('controller' => 'stock', 'action' => 'stock_level')); ?> </li>
	</ul>
</div>
<script>
	jQuery(function() {
		jQuery( "#SearchStockLevelStartDate" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#SearchStockLevelEndDate" ).datepicker({ dateFormat: "d M yy " });
	});
	
	function reset_search(){
		jQuery( "#SearchStockLevelStartDate" ).val("");
		jQuery( "#SearchStockLevelEndDate" ).val("");
		jQuery("#order-id").val("");
		return false;
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