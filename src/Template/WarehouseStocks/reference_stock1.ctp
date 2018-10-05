<?php
	if(!isset($start_date))$start_date = "";
	if(!isset($end_date))$end_date = "";
	if(!isset($search_kw))$search_kw = "";
?>
<?php
	$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
		$updateUrl = "/img/16_edit_page.png";
?>
<div class="warehouseStocks index">
	<h2><?php echo __('Stock Reference')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	</h2>
	<fieldset>
		<legend>Search</legend>
		<form action='<?php echo $this->request->webroot; ?>warehouse-stocks/search_stock' method='get'>
		<table>
			<tr>
				<td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date" style = "width:106px;height: 27px;" value='<?php echo $start_date;?>' /></td>
				<td><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date" style = "width:106px;height: 27px;" value='<?php echo $end_date;?>' /></td>
				<td><input type="text" name="search_kw" id = 'search_kw' placeholder="stock reference number" autofocus style = "width:250px ;height: 26px;"  value="<?php echo $search_kw;?>"</td>
				<td><input type="submit" name="submit" value="Search "></td>
				<td><input type='button' name='reset' value='Reset Search' style='padding:6px 8px;color:#333;border:1px solid #bbb;border-radius:4px;' onClick='reset_search();'/></td>
			</tr>
		</table>
		</form>
	</fieldset>
	<?php echo $this->Form->create('WarehouseStock',['url' =>['action' => 'update_stock']]); ?>
	
	
	
	<span class='paging' style='text-align:right;float:right;'>
			<?php
				echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
				echo $this->Paginator->numbers(array('separator' => ''));
				echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
			?>
		</span>
	<table cellpadding="0" cellspacing="0">	
		<tr><td colspan='8'><hr></td></tr>
		<tr>
			<th><?php #echo $this->Paginator->sort('product_id'); ?></th>
			<th><?php echo $this->Paginator->sort('user_id');?></th>
			<th><?php echo $this->Paginator->sort('reference_number');?></th>
			<th>Stock Value</th>
			<th><?php echo $this->Paginator->sort('modified', 'Created');?></th>
			<th>Action</th>
		</tr>
			<?php foreach($referenceStockData as $key=>$referenceStock){
				//pr($referenceStock);die;
				$product_id = $referenceStock->product_id;
				$user_id = $referenceStock->user_id;
				$reference_number = $referenceStock->reference_number;
                $modified = $referenceStock->modified;
				if(!empty($modified)){
                    $res_to_use = $modified->i18nFormat(
								[\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
							);
                }else{
                    $res_to_use = "";
                }
				
				$stock_value = $referenceStock->stock_value;?>
		<tr>
			<td><?php #echo $product_id;?></td>
			<td><?php
			if(array_key_exists($user_id,$userName)){
				echo $userName[$user_id];
			}else{
				echo"--";
			}
			?></td>
			<td><?php echo $reference_number;?></td>			
			<td><?php echo $stock_value;?></td>
			<td><?php
			echo date("jS M, Y g:i:s A",strtotime($res_to_use)); 
			//echo $this->Time->format('jS M, Y g:i A', $modified,null,null); ?></td>
			<td><?php echo $this->Html->link('View',array('action'=>'view_reference_stock',strtotime($res_to_use)))?></td>
		</tr>
		<?php } ?>
		<tr><td></td><td colspan='2' align='right'><strong>Total Stock Value</strong></td><td><span style='background-color: yellow;'><?php echo number_format($totalStockValue, 2, null,',');?></span></td><td colspan='3'></td></tr>
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
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div>

<script>
	function inputReference(){
		if(document.getElementById("reference_number").value == null || document.getElementById("reference_number").value == ""){
			alert("Please input the reference number!");
			return false;
		}
	}
	
	function submitForm(){
		document.getElementById("display_form").submit();
	}
	

</script>
<script>
	function reset_search(){
		jQuery( "#datepicker1" ).val("");
		jQuery( "#datepicker2" ).val("");
		jQuery("#search_kw").val("");
	}
jQuery(function() {
	jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
	jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
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