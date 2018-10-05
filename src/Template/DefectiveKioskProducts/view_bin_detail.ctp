<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$currency = Configure::read('CURRENCY_TYPE');
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
$siteBaseURL = Configure::read('SITE_BASE_URL');
$createdBy = '';
$referenceStatus = $referenceArr['status'];
$createdOn = date('M jS, Y',strtotime($referenceArr['created']));//$this->Time->format('M jS, Y',$referenceArr['created'],null,null);
if(array_key_exists($referenceArr['user_id'],$users)){
	$createdBy = "by ".$users[$referenceArr['user_id']]." ";
}
?>
<div class="mobileUnlocks index">
	<strong><?php echo __('<span style="color: red; font-size: 20px">Faulty Bin Detail</span> (Created '.$createdBy.'under the reference: '.$referenceArr['reference'].' on '.$createdOn.')'); ?></strong>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('kiosk_id'); ?></th>
			<th>Product Code</th>
			<th><?php echo $this->Paginator->sort('product_id'); ?></th>
			<th>Color</th>
			<th>Image</th>
			<th><?php echo $this->Paginator->sort('quantity'); ?></th>
			<th><?php echo $this->Paginator->sort('single_product_cost'); ?></th>
			<th><?php echo $this->Paginator->sort('total_product_cost'); ?></th>
			
	</tr>
	</thead>
	<tbody>
		<?php
		$groupStr = "";
		foreach($defectiveTransients as $key => $defectiveTransient){
			$groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
            //pr($productArr);die;
            //pr($defectiveTransient);die;
			$truncatedProduct = \Cake\Utility\Text::truncate(
				$productArr[$defectiveTransient['product_id']]['product'],
				30,
				[
				    'ellipsis' => '...',
				    'exact' => false
				]
			);
		
			$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$defectiveTransient->product_id.DS;
			$imageName =  $productArr[$defectiveTransient->product_id]['image'];
			$absoluteImagePath = $imageDir.$imageName;
			
			$LargeimageURL = $imageURL = "/thumb_no-image.png";
			if(@readlink($absoluteImagePath) || file_exists($absoluteImagePath)){
				$imageURL = "{$siteBaseURL}/files/Products/image/".$defectiveTransient->product_id."/$imageName";
				$LargeimageURL = "{$siteBaseURL}/files/Products/image/".$defectiveTransient->product_id."/vga_"."$imageName";
				 
			}
			
			if($defectiveTransient->single_product_cost > 0){
				$single_product_cost = $CURRENCY_TYPE .round($defectiveTransient->single_product_cost,2);
				$total_product_cost = $CURRENCY_TYPE . round($defectiveTransient->total_product_cost,2);
			}else{
				$single_product_cost = '--';
				$total_product_cost = '--';
			}
			
			?>
		<tr>
			<td><?=$kiosks[$defectiveTransient->kiosk_id];?></td>
			<td><?=$productArr[$defectiveTransient->product_id]['product_code'];?></td>
			<td><?=$truncatedProduct;?></td>
			<td><?=$productArr[$defectiveTransient->product_id]['color'];?></td>
			<td><?php
				echo $this->Html->link(
					$this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')),
					$LargeimageURL,
					array('escapeTitle' => false, 'title' => $productArr[$defectiveTransient->product_id]['product'],'class' => "group{$key}")
				);
			?></td>
			<td><?=$defectiveTransient->quantity;?></td>
			<td><?=$single_product_cost;?></td>
			<td><?=$total_product_cost;?></td>
		</tr>
		<?php }
		if($referenceStatus == 0 && ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS)){
		?>
		<?php } ?>
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
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>