<style>
	
	.ui-dialog .ui-dialog-content {
		height: auto !important;
	}
	.ui-dialog-titlebar-close {
		visibility: hidden;
	      }
</style>
<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Routing\Router;

$currency = Configure::read('CURRENCY_TYPE');
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
$siteBaseURL = Configure::read('SITE_BASE_URL');
$createdBy = '';
$referenceStatus = $referenceArr['status'];
$createdOn = date('jS M, Y',strtotime($referenceArr['created']));//$this->Time->format('M jS, Y',$referenceArr['created'],null,null);
if(array_key_exists($referenceArr['user_id'],$users)){
	$createdBy = "by ".$users[$referenceArr['user_id']]." ";
}
// $webRoot = $this->request->webroot;
    $ajax_url = $this->Url->build(['controller' => 'defective-kiosk-products', 'action' => 'test']);
	$passid = $this->request->params['pass'][0];
	$rediredt_url = $this->Url->build(['controller' => 'defective-kiosk-products', 'action' => 'view_transient_faulty',$passid]);
?>
<input type='hidden' name='pass_id' id='pass_id' value='<?=$passid?>' />
<input type='hidden' name='ajax_url' id='ajax_url' value='<?=$ajax_url?>' />
<input type='hidden' name='redirect_url' id='redirect_url' value='<?=$rediredt_url?>' />
<div id="error_for_alert" title="error_for_alert">Recived</div>
<div class="mobileUnlocks index">
	<?php echo $this->Form->create();?>
	<strong><?php echo __('<span style="color: red; font-size: 20px">Faulty Transient Products</span> (Created '.$createdBy.'under the reference: '.$referenceArr['reference'].' on '.$createdOn.')'); ?></strong></br>
	<span>
		<i>
			*Move to bin will move the entire product to bin<br/>
			**To receive the partial product, please choose the quantity to receive from the dropdown<br/>
			***Data from <span style="background-color: yellow">defective_kiosk_transients</span><br/>
			****We are updating status in <span style="background-color: yellow">defective_kiosk_transients</span> after receiving
		</i></span>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('kiosk_id'); ?></th>
			<th>Product Code</th>
			<th><?php echo $this->Paginator->sort('product_id'); ?></th>
			<th>Color</th>
			<th>Image</th>
			<th><?php echo $this->Paginator->sort('quantity'); ?></th>
	</tr>
	</thead>
	<tbody>
		<?php
		echo $this->Form->input('id',array('type' => 'hidden','name' => "DefectiveKioskTransient[checked_id]", 'id' => 'transient_id'));
		echo $this->Form->input('null',array('type' => 'hidden','id' => 'reference', 'name' => "DefectiveKioskTransient[refrence]", 'value' => $referenceArr['reference']));
		$groupStr = "";
		foreach($defectiveTransients as $key => $defectiveTransient){
			$groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
			$jsonArray = json_encode($defectiveTransient);
            $id = $defectiveTransient['id'];
			echo $this->Form->input('null',array('type' => 'hidden', 'name' => "DefectiveKioskTransient[json_data][$key]", 'id' => "json_$id",'value' => $jsonArray));
			echo $this->Form->input('id',array('type' => 'hidden', 'name' => "DefectiveKioskTransient[id][$key]", 'value' => $defectiveTransient['id']));
			echo $this->Form->input('product_id',array('type' => 'hidden', 'name' => "DefectiveKioskTransient[product_id][$key]", 'id' => "product_id_{$id}",'value' => $defectiveTransient['product_id']));
			$truncatedProduct =
								\Cake\Utility\Text::truncate(
                                                                $productArr[$defectiveTransient['product_id']]['product'],
                                                                        30,
                                                                        [
                                                                                'ellipsis' => '...',
                                                                                'exact' => false
                                                                        ]
                                                                );
		
			$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$defectiveTransient['product_id'].DS;
			$imageName =  $productArr[$defectiveTransient['product_id']]['image'];
			$absoluteImagePath = $imageDir.$imageName;
			$LargeimageURL = $imageURL = "/thumb_no-image.png";
			if(@readlink($absoluteImagePath) || file_exists($absoluteImagePath)){
				$imageURL = "{$siteBaseURL}/files/Products/image/".$defectiveTransient['product_id']."/$imageName";
				$LargeimageURL = "{$siteBaseURL}/files/Products/image/".$defectiveTransient['product_id']."/vga_"."$imageName";
			}
			?>
		<tr>
			<td><?=$kiosks[$defectiveTransient['kiosk_id']];?></td>
			<td><?=$productArr[$defectiveTransient['product_id']]['product_code'];?></td>
			<td><?=$truncatedProduct;?></td>
			<td><?=$productArr[$defectiveTransient['product_id']]['color'];?></td>
			<td><?php
				echo $this->Html->link(
					$this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')),
					$LargeimageURL,
					array('escapeTitle' => false, 'title' => $productArr[$defectiveTransient['product_id']]['product'],'class' => "group{$key}")
				);
			?></td>
			<td><?php //$defectiveTransient['DefectiveKioskTransient']['quantity'];
			echo $this->Form->input('original_quantity',array('type' => 'hidden', 'name' => "DefectiveKioskTransient[original_quantity][$key]",'id' => "original_quantity_$id", 'value' => $defectiveTransient['quantity']));
			$optns = array();
			for($qtt = 1;$qtt <= $defectiveTransient['quantity'];$qtt++){
				$optns[$qtt] = $qtt;
			}
			echo $this->Form->input('quantity',array('id' => "DefectiveKioskProductQuantity_{$id}",'name' => "DefectiveKioskTransient[quantity][$key]", 'options' => $optns, 'label' => false));
			?>
			</td>
			<?php if($defectiveTransient['status'] != 1){?>
			<td><input type="submit" class = "recive" value="Receive" id="<?=$defectiveTransient['id'];?>" name="DefectiveKioskTransient[Receive]" onclick="return update_transient_id(this.id);"/></td>
			<td><input type="submit" class = "move_to_bin" value="Move to Bin" name="DefectiveKioskTransient[move_to_bin]" onclick="return update_transient_id(<?php echo $defectiveTransient['id'];?>);"/></td>
			<?php } ?>
		</tr>
		<?php }
		if($referenceStatus == 0 && ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS)){
		?>
		<tr>
			<td>
			<?php //$this->Html->link('Receive', array('action' => 'receive_faulty', $id));?>
			<?php //$this->Html->link($this->Form->button('Receive', array('style' => "width: 65px;background: #62af56;background-image: -webkit-linear-gradient(top, #76BF6B, #3B8230); background-color: #2d6324;color: #fff;text-shadow: rgba(0, 0, 0, 0.5) 0px -1px 0px;padding: 8px 10px;border: 1px solid #bbb;border-radius: 4px;")), array('action' => 'receive_faulty',$id), array('escape'=>false,'title' => "Receive"));?>
			</td>
		</tr>
		<?php } ?>
		<?php echo $this->Form->end();?>
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
	<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){?>
	 <?=$this->element('faulty_slide_menu');?>
	<?php }else{ ?>
	 <ul>
	   <li><?php echo $this->Html->link(__('Add Faulty'), array('controller' => 'defective_kiosk_products', 'action' => 'add')); ?></li>
	   <li><?php echo $this->Html->link(__('Faulty References'), array('controller' => 'defective_kiosk_products', 'action' => 'list_defective_references')); ?></li>
	   <li><?php echo $this->Html->link(__('View Faulty'), array('controller' => 'defective_kiosk_products', 'action' => 'view_faulty_products')); ?></li> 
	 </ul>
	<?php } ?>
</div>
<script>
	function update_transient_id(id) {
        $("#transient_id").val(id);
    }
</script>

<script>
	$(document).on('click', '.recive', function() {
       	 var targeturl = $("#ajax_url").val();
		 var checked_id = $("#transient_id").val();
		 var quantity = $("#DefectiveKioskProductQuantity_"+checked_id).val();
		 var product_id = $("#product_id_"+checked_id).val();
		 var reference = $("#reference").val();
		 var original_quantity = $("#original_quantity_"+checked_id).val();
		 var json = $("#json_"+checked_id).val();
		 var pass_id = $("#pass_id").val();
		 
		// alert(json);return false;
		 targeturl += "?checked_id="+checked_id;
		 targeturl += "&reference="+reference;
		 targeturl += "&quantity="+quantity;
		 targeturl += "&product_id="+product_id;
		 targeturl += "&original_quantity="+original_quantity;
		 targeturl += "&json="+json;
		 targeturl += "&pass_id="+pass_id;
		 targeturl += "&Receive=Receive";
		 $.blockUI({ message: 'Updating cart...' });
		 $.ajax({
            		type: 'get',
					url: targeturl,
					beforeSend: function(xhr) {
						xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
					},
					success: function(response) {
                      //  alert(response);return false;
                       	 $.unblockUI();
                          var objArr = $.parseJSON(response);
                        //  alert(objArr);return false;
						  if (objArr.hasOwnProperty('success')) {
							document.getElementById('error_for_alert').innerHTML = objArr.success;
								$( "#error_for_alert" ).dialog({
													resizable: false,
													height:140,
													modal: true,
													closeText: "Close",
													width:300,
													maxWidth:300,
													title: '!!! Recived!!!',
													buttons: {
														"OK": function() {
															$( this ).dialog( "close" );
															var redirect_url = $("#redirect_url").val();
															window.location.href = redirect_url;
														}
													}
												});
						  }else{
							document.getElementById('error_for_alert').innerHTML = objArr.error;
									$( "#error_for_alert" ).dialog({
													resizable: false,
													height:140,
													modal: true,
													closeText: "Close",
													width:300,
													maxWidth:300,
													title: '!!! Error!!!',
													buttons: {
														"OK": function() {
															$( this ).dialog( "close" );
															var redirect_url = $("#redirect_url").val();
															window.location.href = redirect_url;
														}
													}
												});
						  }
					},
					error: function(e) {
						 $.unblockUI();
						  var msg = "An error occurred: " + e.responseText.message;
						 document.getElementById('error_for_alert').innerHTML = msg;
						 $( "#error_for_alert" ).dialog({
													resizable: false,
													height:140,
													modal: true,
													closeText: "Close",
													width:300,
													maxWidth:300,
													title: '!!! Error!!!',
													buttons: {
														"OK": function() {
															$( this ).dialog( "close" );
															var redirect_url = $("#redirect_url").val();
															window.location.href = redirect_url;
														}
													}
												});
					}		
		});
		 return false;
	});
	
	$(document).on('click', '.move_to_bin', function() {
        var targeturl = $("#ajax_url").val();
        // alert(targeturl);
		 var checked_id = $("#transient_id").val();
        var quantity = $("#DefectiveKioskProductQuantity_"+checked_id).val();
        
		 var product_id = $("#product_id_"+checked_id).val();
        
		 var reference = $("#reference").val();
		 var original_quantity = $("#original_quantity_"+checked_id).val();
       //   alert('dd');  //alert(product_id);
        // return false;
		 var json = $("#json_"+checked_id).val();
		 var pass_id = $("#pass_id").val();
		 
		 
		 targeturl += "?checked_id="+checked_id;
		 targeturl += "&reference="+reference;
		 targeturl += "&quantity="+quantity;
		 targeturl += "&product_id="+product_id;
		 targeturl += "&original_quantity="+original_quantity;
		 targeturl += "&json="+json;
		 targeturl += "&pass_id="+pass_id;
		 targeturl += "&move_to_bin=move_to_bin";
       //  alert(targeturl);return false;
		 $.blockUI({ message: 'Updating cart...' });
		 $.ajax({
					type: 'get',
					url: targeturl,
					beforeSend: function(xhr) {
						xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
					},
					success: function(response) {
                       // alert(response);
						 $.unblockUI();
						  var objArr = $.parseJSON(response);
						  if (objArr.hasOwnProperty('success')) {
							document.getElementById('error_for_alert').innerHTML = objArr.success;
								$( "#error_for_alert" ).dialog({
													resizable: false,
													height:140,
													modal: true,
													closeText: "Close",
													width:300,
													maxWidth:300,
													title: '!!! Recived!!!',
													buttons: {
														"OK": function() {
															$( this ).dialog( "close" );
															var redirect_url = $("#redirect_url").val();
															window.location.href = redirect_url;
														}
													}
												});
						  }else{
							document.getElementById('error_for_alert').innerHTML = objArr.error;
									$( "#error_for_alert" ).dialog({
													resizable: false,
													height:140,
													modal: true,
													closeText: "Close",
													width:300,
													maxWidth:300,
													title: '!!! Error!!!',
													buttons: {
														"OK": function() {
															$( this ).dialog( "close" );
															var redirect_url = $("#redirect_url").val();
															window.location.href = redirect_url;
														}
													}
												});
						  }
					},
					error: function(e) {
						 $.unblockUI();
						  var msg = "An error occurred: " + e.responseText.message;
						 document.getElementById('error_for_alert').innerHTML = msg;
						 $( "#error_for_alert" ).dialog({
													resizable: false,
													height:140,
													modal: true,
													closeText: "Close",
													width:300,
													maxWidth:300,
													title: '!!! Error!!!',
													buttons: {
														"OK": function() {
															$( this ).dialog( "close" );
															var redirect_url = $("#redirect_url").val();
															window.location.href = redirect_url;
														}
													}
												});
					}		
		});
		 return false;
	});
	
</script>
<script>
	$(document).ready(function(){
	$('#error_for_alert').hide();
 });
</script>
<script>
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>