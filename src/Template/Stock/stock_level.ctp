<style>
 #remote .tt-dropdown-menu {
  max-height: 250px;
  overflow-y: auto;
}
 #remote .twitter-typehead {
  max-height: 250px;
  overflow-y: auto;
}
.tt-dataset, .tt-dataset-product {
  max-height: 250px;
  overflow-y: auto;
}
.row_hover:hover{
 color:blue;
 background-color:yellow;
}
</style>
<?php #echo $this->Html->script('jquery.printElement');
$checked = '';//for showing checkbox checked below
if(array_key_exists('product_status',$this->request->Session()->read())){
	$product_status = $this->request->Session()->read('product_status');
}
if(empty($product_status)){
	$product_status = 'All';
}
$stock_level_session = $this->request->Session()->read('stock_level_session');
if(!$stock_level_session){
	$stock_level_session = array();
}
?>

<?php if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
	}else{
		$value = '';
	}?>
<form action='<?php echo $this->request->webroot; ?>stock/search_stock_level_main' method = 'get'>
		<fieldset>
			<legend>Search</legend>			
				<table>
					<tr>
						<td>
						</td>
						<td colspan='2'><strong>Find by category &raquo;</strong></td>
					</tr>
					<tr>
						<td><div id='remote'><input class="typeahead" type = "text" name = "search_kw" value = '<?=$value;?>' placeholder = "product, product code or description" style = "width:343px" autofocus/></div></td>
						<td rowspan="3"><select id='category_dropdown' name='category[]' multiple="multiple" size='6' onchange = 'update_hidden();'><option value="0">All</option><?php echo $categories;?></select></td>
					<tr>
						<td><h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4></td>
					</tr>					
					<tr>
						<td colspan='2'><input type = "submit" value = "Search Product" name = "submit"/></td>
					</tr>					
				</table>
		</fieldset>
	<fieldset>
	<table>
		<tr>
			<td><strong style="font-size: 20px;color: red;"><?php echo __('Stock Below Level'); ?></strong>
			&nbsp;&nbsp;&nbsp;<?php echo $this->Html->link('Warehouse Placed Orders', array('action' => 'viewStockLevel'));?>
			<?php
				$screenHint = $hintId = "";
                //pr($hint);die;
				if(!empty($hint)){
				   $screenHint = $hint["hint"];
				   $hintId = $hint["id"];
				}
				$updateUrl = "/img/16_edit_page.png";
			?>
			<h2><?php echo "<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
			<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
			</h2></td>
			<td><input type="radio" name="product_status" value="All" id="all" <?= $checked = ($product_status == 'All') ? 'checked' : 'ho';?> onclick = "showhide(this.id);">All</td>
			<td><input type="radio" name="product_status" value="New" id="new" <?= $checked = ($product_status == 'New') ? 'checked' : 'vo';?> onclick = 'showhide(this.id);'>New</td>
			<td><input type="radio" name="product_status" value="Processed" id="processed" <?= $checked = ($product_status == 'Processed') ? 'checked' : '';?> onclick = 'showhide(this.id);'>Processed</td>
		</tr>
	</table>
	</fieldset>
</form>
<div id='printDiv' style="text-align: center;">
	<?php echo $this->Form->create('Stock',array('url' => array('action' => 'stock_level', 'controller' => 'stock'),
												 'onSubmit' => 'return validateForm(this.id);','id' => 'StockStockLevelForm')); ?>
												 <input type="hidden" id = "submit_form" name="submit_form" value="0">
		<table cellpadding="0" cellspacing="0">
			<tr>
			 <td><?=$this->Html->link('Restore Session', array('action' => 'restore_session', $this->request->params['controller'], "stock_level", 'stock_level_session', ''));?></td>
				<td colspan = '9'>
					<div class="paging" style='float: right;'>
						<?php
							echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
							echo $this->Paginator->numbers(array('separator' => ''));
							echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
						?>
					</div>
				</td>
			</tr>
			<div class="submit">
				<?php if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER): ?>
					<input type="hidden" name="product_status">
					<tr>
						<td colspan = '10'>
							<table style = 'margin-top: -22px;'>
								<tr>
									<td style = 'width: 138px;'>
										<?php echo $this->Form->input('Add to basket', array('type' => 'submit', 'label' => false, 'name' => 'add_to_basket'))?>
									</td>
									<td style = 'width: 107px;'>
							<?php echo $this->Form->input('Checkout', array('type' => 'submit', 'label' => false, 'name' => 'checkout', 'id' => 'check_out'))?>
									</td>
									<td>
										<?php echo $this->Form->input('Save listing', array('type' => 'submit', 'label' => false, 'name' => 'save_listing'))?>
									</td>
									<td>
										<?php echo $this->Form->input('Inactivate Products', array(
																								   'type' => 'submit',
																								   'label' => false,
																								   'name' => 'inactivate_products',
																								   'onClick' => 'return validateForm();',
																								   'id' => 'inactivate_button'
																								   )
																	  )?>
									</td>
									<td>
										<?php echo $this->Form->input('Clear basket', array('type' => 'submit', 'label' => false, 'name' => 'clear_basket', 'style' => 'float: right;'))?>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				<?php endif; ?>
			</div>
				<tr>
					<th><?php echo $this->Paginator->sort('product_code'); ?></th>
					<th><?php echo $this->Paginator->sort('product_id'); ?></th>
					<th><?php echo $this->Paginator->sort('category_id'); ?></th>
					<th>Kiosk Stock</th>
					<th>Order Id</th>
					<th><?php echo $this->Paginator->sort('image'); ?></th>
					<th><?php echo $this->Paginator->sort('color');?></th>
					<th>Inactivate</th>
					<th><?php echo $this->Paginator->sort('quantity','Warehouse'); ?><br/>Quantity</th>
					<th><?php echo $this->Paginator->sort('quantity'); ?></th>
				</tr>
				<?php $currentPageNumber = $this->Paginator->current();
				$product_order_str_arr = array();
					foreach($product_order_arr as $prdctId => $product_order_Inf){
						$newArray = array();
						foreach($product_order_Inf as $key => $product_order){
                            $date_to_show = $product_date_arr[$product_order][0];
							$newArray[] = $this->Html->link($date_to_show,array('action' => 'datewise_stock_level', $product_order),array('target' => '_blank'));
						}
						$product_order_str_arr[$prdctId] = implode(',</br>',$newArray);
					}
				?>
				<?php
				$groupStr = "";
				foreach ($products as $key => $product):
				$groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
                //pr($product);die;
					if(array_key_exists($product->id,$product_order_str_arr)){
						$orderIds = $product_order_str_arr[$product->id];
					}else{
						$orderIds = '--';
					}
					
					if(!empty($productId_arr[$product->id])){
						$productStockPerKiosk = $productId_arr[$product->id];
					}else{
						$productStockPerKiosk = "--";
					}
					
					if(!empty($underProcessProducts[$product->id])){
						$currentStatus = $underProcessProducts[$product->id];
					}else{
						$currentStatus = "";
					}
					$stockLevel = $product->quantity-$product->stock_level;
			if($stockLevel<0){?>
				
				<?php
					$stockLevel = -$stockLevel;//made it +tive on client's request
					$truncatedProduct = \Cake\Utility\Text::truncate(
							$product->product,
							50,
							[
							    'ellipsis' => '...',
							    'exact' => false
							]
						);
					if($currentStatus=="In process"){
					?>
					<tr style="background-color: darkkhaki;" class="in_progress">
				<?php	}else{?>
					<tr class="others">
				<?php }?>
						<td>
							<?php if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
							<?php echo $this->Html->link(
										     $product->product_code,
										     array('controller' => 'products', 'action' => 'edit', $product->id),
										     array('escapeTitle' => false, 'title' => $product->product)
										     ); ?>
							<?php }else{?>
							<?php echo $product->product_code; ?>
							<?php } ?>
						</td>
						<td>
							<?php echo $this->Html->link(
										     $truncatedProduct,
										     array('controller' => 'products', 'action' => 'view', $product->id),
										     array('escapeTitle' => false, 'title' => $product->product,'id' => "tooltip_{$product->id}")
										     );
							?>
						</td>
						<td>
							<?php echo $categoryNames[$product->category_id]; ?>
						</td>
						<td>
							<?php
                            $product_id = $product->id;
							echo $this->Html->link($productStockPerKiosk,
								array('controller' => 'stock', 'action' => 'stock_per_kiosk', $product->id),
								array(
								      'escapeTitle' => false,
								      'title' => 'Stock per kiosk',
								      'id' => "tooltip_1_{$product_id}"
								      )
								);				
							?>
						</td>
						<td>
							<?=$orderIds;?>
						</td>
						<td>
				<?php
					$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product->id.DS;
					$imageName =  $product->image;
					$absoluteImagePath = $imageDir.$imageName;
					$LargeimageURL = $imageURL = "/thumb_no-image.png";
					if(@readlink($absoluteImagePath) || file_exists($absoluteImagePath)){
						//$applicationURL = $this->html->url('/', true);
						//$imageURL = $applicationURL."files/product/image/".$product['Product']['id']."/thumb_$imageName";
						$imageURL = "/files/Products/image/".$product->id."/$imageName";
						$LargeimageURL = "/files/Products/image/".$product->id."/vga_"."$imageName";
					}
						
						echo $this->Html->link(
								  $this->Html->image($imageURL, array('fullBase' => true, 'style' => 'width: 40px; height: 40px;')),
								  $LargeimageURL,
								  array('escapeTitle' => false, 'title' => $product->product,'class' => "group{$key}")
								 );
	
	?>&nbsp;</td>
						<td><?php echo $product->color; ?></td>
						<td><?php
						echo $this->Form->input(null,
													 [
													  'type' => 'hidden',
													  'name' => "Stock[activate_value][$key]",
													  'value' => $product->product_code
													 ]);
                        
                        echo $this->Form->input(null,array(
												'type' => 'checkbox',
												'name' => "Stock[activate][$key]",
                                                'id' => "data_stock_activate_{$key}",
												'label' => false,
                                                'class' => 'stock_activate',
												'style' => 'width:80px;',
												 'div'=>false,
												'checked' => ""//
										  )
								      );
                       ?></td>
						<td>
							<?=$product->quantity;?>
						</td>
						<td><?php
									if(array_key_exists($product->id,$stock_level_session)){
										$chcked = 'checked';
										$stockLevel = $stock_level_session[$product->id];
									}else{
										$chcked = "";
									}
									echo $this->Form->input(null,array(
												'type' => 'text',
												'name' => "Stock[quantity][$key]",
												'value' => $stockLevel,
												'label' => false,
												'style' => 'width:52px; margin-top:8px;',
												'div'=>false,
												'readonly' => false
										  )
								      );
                                    echo "</td>";
									echo "<td>";
									echo $this->Form->input(null,array(
												'type' => 'checkbox',
												'name' => "Stock[checked_quantity][$key]",
												'label' => false,
												'style' => 'width:80px;',
												'readonly' => false,
												//'div'=>false,
												'style'=>"height:18px; margin-top:8px; transform:scale(1.5);",
												'checked' => $chcked
										  )
								      );
									?>
								      <?php echo $this->Form->input(null,array(
											     'type' => 'hidden',
												'name' => "Stock[product_id][$key]",
												'value' => $product->id
										  )
								      ); ?>&nbsp;</td>
					</tr>
					<?php
				} endforeach;?>
				<input type='hidden' name='url_category' id='url_category' value=''/>
				<?php echo $this->Form->input(null,array('type' => 'hidden','value' => $currentPageNumber,'name' => 'current_page'));?>
		</table>
</div>
 
	 <div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
<script>
	
	function showhide(id) {
		if (id == 'new') {
			var elements = document.getElementsByClassName('in_progress');
			for (var i = 0; i < elements.length; i++){
				elements[i].style.display = 'none';
			}
			
			var elements = document.getElementsByClassName('others');
			for (var i = 0; i < elements.length; i++){
				elements[i].style.display = 'table-row';
			}
			$('input[name="product_status"]').val('New');
		}
		if (id == 'processed') {
			var elements = document.getElementsByClassName('others');
			for (var i = 0; i < elements.length; i++){
				elements[i].style.display = 'none';
			}
			
			var elements = document.getElementsByClassName('in_progress');
			for (var i = 0; i < elements.length; i++){
				elements[i].style.display = 'table-row';
			}
			$('input[name="product_status"]').val('Processed');
		}
		if (id == 'all') {
			var elements = document.getElementsByClassName('others');
			for (var i = 0; i < elements.length; i++){
				elements[i].style.display = 'table-row';
			}
			
			var elements = document.getElementsByClassName('in_progress');
			for (var i = 0; i < elements.length; i++){
				elements[i].style.display = 'table-row';
			}
			$('input[name="product_status"]').val('All');
		}
	}
	
	$( document ).ready(function(){
		var val = $('input[name=product_status]:checked').attr('id');
		showhide(val);
	});
</script>
<script type="text/javascript">
<?php
 
	foreach ($products as $product):
    
		$string = preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($product->product));
		if(empty($string)){
			$string = $product->product;
		}
		echo "$('#tooltip_{$product->id}').tooltip({content:\"".htmlspecialchars($string)."\",track:true});";
		echo "$('#tooltip_1_{$product->id}').tooltip({content:\"".htmlspecialchars($string)."\",track:true});";
	endforeach;
?>
</script>
<input type='hidden' name='url_category' id='url_category' value=''/>
<script type="text/javascript">
function update_hidden(){
  //var singleValues = $( "#single" ).val();
  var multipleValues = $( "#category_dropdown" ).val() || [];
  $('#url_category').val(multipleValues.join( "," ));
 }
 </script>
<script type="text/javascript">
var product_dataset = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  //prefetch: "/products/data",
  remote: {
         url: "/products/admin-Data?category=%CID&search=%QUERY",
                    replace: function (url,query) {
					 var multipleValues = $( "#category_dropdown" ).val() || [];
                     $('#url_category').val(multipleValues.join( "," ));
					 //alert($('#url_category').val());
					 return url.replace('%QUERY', query).replace('%CID', $('#url_category').val());
					},
                        wildcard: "%QUERY"
    
  }
});

$('#remote .typeahead').typeahead(null, {
  name: 'product',
  display: 'product',
  source: product_dataset,
  limit:120,
  minlength:3,
  classNames: {
    input: 'Typeahead-input',
    hint: 'Typeahead-hint',
    selectable: 'Typeahead-selectable'
  },
  highlight: true,
  hint:true,
  templates: {
    suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:400px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{product_code}}</a></strong></div>'),
	header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------<?php echo ADMIN_DOMAIN;?>---------</b></div>"),
  }
});

	$('#check_out').click(function(){
		<?php if(!(array_key_exists('stock_level_session', $this->request->Session()->read())) || count($this->request->Session()->read('stock_level_session')) == 0){?>
			alert('Please add items to basket before checkout!');
			return false;
		<?php } ?>
	});
	
 function validateForm(){
  if ($("#submit_form").val() == 1){
   $( "#StockStockLevelForm" ).submit();
 }
   var checkedVals = $('.stock_activate:checkbox:checked').map(function() {
    return this.value;
   }).get();
   //alert(checkedVals);
   var checkedVals = checkedVals.join(",");
   if($.trim(checkedVals) == ""){
	$("#submit_form").val(1);
	$( "#StockStockLevelForm" ).submit();
   }else{
	msgStr = "Are you sure you want to deactivate products with product code: "+checkedVals;
	 if(confirm(msgStr)){
	  $("#submit_form").val(1);
	  $( "#StockStockLevelForm" ).submit();
	 }else{
	  return false;
	 }
   }
   $('id').submit(function(){
     msgStr = "Are you sure you want to deactivate products with product code: "+checkedVals;
	 if(confirm(msgStr)){
	  $("#submit_form").val(1);
	  $( "#StockStockLevelForm" ).submit();
	 }
    })
  return false;
 }

</script>
</script>
 <script>
$(function() {
  $( document ).tooltip({
   //content: function () {
    //return $(this).prop('title');
  
  });
 });
</script>
 <script>
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>