<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php $currency = Configure::read('CURRENCY_TYPE');
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
$siteBaseURL = Configure::read('SITE_BASE_URL');
$adminDomainURL = URL_SCHEME.ADMIN_DOMAIN;
$createdBy = '';
//pr($referenceArr);die;
$referenceStatus = $referenceArr['status'];
$createdOn = $this->Time->format($referenceArr['created'],'dd.mm.yy',null,null);
if(array_key_exists($referenceArr['user_id'],$users)){
	$createdBy = "by ".$users[$referenceArr['user_id']]." ";
}
?>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<?=$this->element('faulty_slide_menu');?>
</div>
<div class="mobileUnlocks index">
	<?=$this->Form->create();?>
	<strong><?php echo __('<span style="color: red; font-size: 20px">Faulty Transient Products</span> (Created '.$createdBy.'under the reference: '.$referenceArr['reference'].' on '.$createdOn.')'); ?></strong>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<?php if($referenceStatus == 0){ ?>
			<td>&nbsp;</td>
		<?php } ?>
			
			<th>Product Code</th>
			<th><?php echo $this->Paginator->sort('product_id'); ?></th>
			<th>Color</th>
			<th>Image</th>
			<th><?php echo $this->Paginator->sort('quantity', 'Ordered Quantity'); ?></th>
			<th>Received Quantity</th>
	</tr>
	</thead>
	<tbody>
		<?php //pr($importedProducts);
		 $groupStr = "";
		foreach($importedProducts as $key => $importedProduct){
			$groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
            //pr($importedProduct);die;
			$truncatedProduct = \Cake\Utility\Text::truncate(
				$productArr[$importedProduct->product_id]['product'],
				30,
				[
				    'ellipsis' => '...',
				    'exact' => false
				]
			);
		
			$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$importedProduct->product_id.DS;
			$imageName = $productArr[$importedProduct->product_id]['image'];
			$absoluteImagePath = $imageDir.$imageName;
			$LargeimageURL = $imageURL = "/thumb_no-image.png";
			if(!empty($imageName)){
				$imageURL = "{$adminDomainURL}/files/Products/image/".$importedProduct->product_id."/thumb_"."$imageName";
				$LargeimageURL = "{$adminDomainURL}/files/Products/image/".$importedProduct->product_id."/vga_"."$imageName";
			}
			?>
		<tr>
			<?php if($referenceStatus == 0){ ?>
			<td style="width: 65px;"><?php echo $this->Form->input('received', array('name' => 'ImportOrderDetail[received_checkbox][]', 'type' => 'checkbox', 'label' => false, 'hiddenField' => false, 'value' => $importedProduct->product_id, 'checked' => 'checked'));?></td>
			<?php } ?>
			<td><?=$productArr[$importedProduct->product_id]['product_code'];?></td>
			<td><?=$truncatedProduct;?></td>
			<td><?=$productArr[$importedProduct->product_id]['color'];?></td>
			<td><?php
				echo $this->Html->link(
					$this->Html->image($imageURL, array('fullBase' => false,'width' => '100px','height' => '100px')),
					$LargeimageURL,
					array('escapeTitle' => false, 'title' => $productArr[$importedProduct->product_id]['product'],'class' => "group{$key}")
				);
			?></td>
			<td><?=$importedProduct->quantity;
				echo $this->Form->input('original_quantity', array('type' => 'hidden', 'name' => 'ImportOrderDetail[original_quantity][]','value' => $importedProduct->quantity));
				echo $this->Form->input('id', array('type' => 'hidden', 'name' => 'ImportOrderDetail[id][]','value' => $importedProduct->id));
				echo $this->Form->input('product_id', array('type' => 'hidden', 'name' => 'ImportOrderDetail[product_id][]','value' => $importedProduct->product_id));
			?></td>
			<?php if($referenceStatus == 0){ ?>
			<td><?php echo $this->Form->input('received_quantity', array('name' => 'ImportOrderDetail[received_quantity][]', 'value' => $importedProduct->quantity, 'label' => false, 'style' => "width: 80px;"));?></td>
			<?php }else{ ?>
			<td><?php echo $importedProduct->quantity_received;?></td>
			<?php } ?>
		</tr>
		<?php }
		if($referenceStatus == 0 && ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS)){
		?>
		<tr>
			<td>
            <?=$this->Form->submit('Receive',array('name'=>'submit'));?>
			<?=$this->Form->end();?>
			</td>
		</tr>
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
</div>
<script>
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>