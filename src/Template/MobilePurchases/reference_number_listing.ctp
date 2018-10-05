<div class="mobilePurchases index">
	<?php
	
	if(isset($this->request->query['reference_number'])){
		$value = $this->request->query['reference_number'];
	}else{
		$value = "";
	}
	if(isset($this->request->query['imei'])){
		$imei = $this->request->query['imei'];
	}else{
		$imei = "";
	}
	
	echo $this->Form->create('MobilePurchase',array('type'=>'get','url'=>array('controller'=>'mobile_purchases','action'=>'search_mobile_reference')));?>
	<table>
		<tr>
			<td><?php echo $this->Form->input('mobile_purchase_reference',array('type'=>'text','name'=>'reference_number', 'value'=>$value));?></td>
			<td><?php echo $this->Form->input('Imei',array('type'=>'text','name'=>'imei', 'value'=>$imei));?></td>
			<td><?php echo $this->Form->submit('Search',array('name'=>'submit'));?></td>
			
			<td><?php echo $this->Form->end();?></td>
		</tr>
	</table>
	<?php
			$screenHint = $hintId = "";
					if(!empty($hint)){
						
					   $screenHint = $hint["hint"];
					   $hintId = $hint["id"];
					}
					$updateUrl = "/img/16_edit_page.png";
	echo "<b>Note: For IMEI based search, you will get mobile count = 1 for any of reference number if found;</b>"					
	?>
	
	<h2><?php echo __('Mobile Purchase References')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>				
	</h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('mobile_purchase_reference'); ?></th>
			<th><?php echo $this->Paginator->sort('mobile_model_id','Model'); ?></th>
			<th><?php echo $this->Paginator->sort('color'); ?></th>
			<th>Mobile Count</th>
			<th>Created Date</th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($referenceNumbers as $referenceNumber):
	//pr($referenceNumber); die;
	?>
	<tr>
		<td><?php echo $this->Html->link(h($referenceNumber['mobile_purchase_reference']), array('action' => 'mobile_listing_per_reference', $referenceNumber['mobile_purchase_reference'], $referenceNumber['rand_num'])); ?>&nbsp;
		</td>
		<td>
			<?php echo h($mobileModels[$referenceNumber['mobile_model_id']]); ?>&nbsp;
		</td>
		<td><?php
			if(array_key_exists($referenceNumber['color'],$color)){
				echo h($color[$referenceNumber['color']]); }
			else{
				echo "--";
			}?>&nbsp;
		</td>
		<td><?php echo h($referenceNumber['count']); ?>&nbsp;</td>
		<td><?php echo 
		date("jS M, Y g:i A",strtotime($referenceNumber['created']));
		//$this->Time->format('M jS, Y g:i A',$referenceNumber['created'],null,null); ?>&nbsp;</td>
		<td class="actions">
			<?php
			$reference = $referenceNumber['mobile_purchase_reference'];
			if(empty($reference)){
				$reference = " ";
			}
				echo $this->Html->link(__('View Detail'), array('action' => 'mobile_listing_per_reference', $reference, $referenceNumber['rand_num']));
				?>
		</td>
	</tr>
<?php endforeach; ?>
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
		<li><?php echo $this->Html->link(__('Stock by Reference'), array('action' => 'reference_number_listing')); ?></li>
		<li><?php # echo $this->Html->link(__('New Kiosk'), array('controller' => 'kiosks', 'action' => 'add')); ?> </li>
		<li><?php # echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
$(function() {
  $( document ).tooltip({
   content: function () {
    return $(this).prop('title');
   }
  });
 });
</script>