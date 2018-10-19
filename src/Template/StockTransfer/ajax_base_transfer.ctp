<?php
 use Cake\Core\Configure;
 use Cake\Core\Configure\Engine\PhpConfig;
 $siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
?>
<style>
 #remote .tt-dropdown-menu {max-height: 250px;overflow-y: auto;}
 #remote .twitter-typehead {max-height: 250px;overflow-y: auto;}
.tt-dataset, .tt-dataset-product {max-height: 250px;overflow-y: auto;}
.row_hover:hover{color:blue;background-color:yellow;}
</style>

<fieldset id="cart_fieldset">	    
				<legend>Cart</legend>
				<div id='cartDiv' style="overflow: scroll; width: 100%; height: 200px; font-size: 9px;"">
					<table width='100%'>
						<tr>
							<th style='width:30px;'>Del</th>
							<th>Code</th>
							<th>Title</th>
							<th>Unit Price</th>
							<th>Qty</th>
							<th>Edit</th>
						</tr>
					</table>
				</div>
			</fieldset>
<?php
	extract($this->request->query);
	if(!isset($search_kw)){$search_kw = "";}
	if(empty($this->request->query)){$displayType = "more_than_zero";}
	$searchQueryUrl = "";
	$searchKw = $categoryQuery = $categoryqryStr = '';
	if(!empty($this->request->query)){
		if(array_key_exists('search_kw',$this->request->query)){
			$searchKw = $this->request->query['search_kw'];
		}
		if(array_key_exists('category',$this->request->query)){
			$categoryQuery = $this->request->query['category'];
			$categoryqryStr = "";
			foreach($categoryQuery as $categoryqry){
				$categoryqryStr.="&category%5B%5D=$categoryqry";
			}
		}
		$searchQueryUrl = "/search?search_kw=$searchKw$categoryqryStr&display_type=$displayType&submit=Search+Product";
	} 
?>
<div id="dialog-confirm" title="Delete item from cart?" style="width: 500px !important;">
	Are you sure you want to delete item from cart?
</div>

<?php
  $delte_cart = $this->Url->build(['controller' => 'stock-transfer', 'action' => 'delete_from_cart'],true);
  $update_cart = $this->Url->build(['controller' => 'stock-transfer', 'action' => 'update_cart'],true);
  $cartURL = $add_2_cart_short = $this->Url->build(['controller' => 'stock-transfer', 'action' => 'add_2_cart_short'],true);
  $restore_cart = $this->Url->build(['controller' => 'stock-transfer', 'action' => 'restore_cart'],true);
?>
<div id="nothing-to-restore" title="Nothing to restore. Cart is empty!" id='qtyAdjusted'>Nothing to restore. Cart is empty!</div>
<input type='hidden' name='restore_cart' id='restore_cart' value='<?=$restore_cart?>' />
<div id="zero_qty" title="Quantity Can't be zero">Quantity Can't be zero</div>
<input type='hidden' name='delte_cart' id='delte_cart' value='<?=$delte_cart?>' />
<input type='hidden' name='update_cart' id='update_cart' value='<?=$update_cart?>' />
<input type='hidden' name='add_to_cart_short' id='add_to_cart_short' value='<?=$cartURL?>' />
<div id="out-of-stock" title="Out of Stock">Either Product is out of stock Or Invalid Code!!!</div>
<div class="centralStocks index" style="width: 98% !important;">
   <div id="idVal"></div>
   <div class="search_div">
			<fieldset >
				<legend>Search</legend>
				<table>
					<tr>
					 <td style="width: 0px"><input type = "text" value = '1' name = "quantity" id='scanner_qty' placeholder = "Quantity" style = "width:29px;height:20px;" autofocus/></td>
						<td>
						 <form id = 'scanner_form' onsubmit='return false;' method='post'>
							 <div id='remote_cart'><input rel = '<?php echo $cartURL;?>' id='scanner_input' class="typeahead" type="text" value='' name = "search_kw" placeholder = "Product Code or Product Title" style="width:160px;height:20px;" autofocus/></div>
							 <input type="submit" value='submit' name='submit2' style='display: none;'/>
						 </form>
						 
						</td>
						<td><strong>Find by category &raquo;</strong></td>						
					</tr> <?php //' $search_kw ' ?>
				</table>
				<form action='<?php echo $this->request->webroot;?>stock-transfer/ajaxBaseSearch' method = 'get'>
				<table>
					<tr><td><div id='remote'><input class="typeahead" type = "text" value = '<?= $search_kw ?>' name = "search_kw1"  placeholder = "Search by product title or product code"  autofocus style = "width:325px"/></div></td>
					
						<td rowspan="4"><select id='category_dropdown' name='category[]' multiple="multiple" size='6'onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select></td></tr>
					</tr>
					<tr>
						<td><h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4></td>
					</tr>
					<tr>
						<td>
							<input type="hidden" name="display_type" value="<?php echo $displayType;?>">
							<input type = "submit" name = "submit" value = "Search Product"/></p></td>
					</tr>
					
				</table>
				</form>
			</fieldset>	
		</div>
	
	<?php
	 $sessionKioskId = $this->request->Session()->read('kiosk_id');
		$sessionBaket = $this->request->Session()->read("Basket");
		$kiosk_id = "";
		$s_kiosk_id = $this->request->Session()->read("kioskId");
		if( !empty($s_kiosk_id) ){
			$kiosk_id = $this->request->Session()->read("kioskId");
		}
	?>
	<?php
		 $screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
		$updateUrl = "/img/16_edit_page.png";
	?>
	
	
	
	<table>
		<tr>
			<td>
				
				<h2><?php echo __('Stock Transfer')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
				<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
				</h2>
			</td>
			<td style="width: 25%;">Show items with zero quantity</td>
			<form name="display_form" id="display_form" method="get">
			<td style="width: 7%;"><input type="radio" name="display_type" value="show_all" id="show_all" onclick="submitForm();" <?php echo $displayType=="show_all"? "checked":"" ?>>&nbsp;Yes</td>
			<td><input type="radio" name="display_type" value="more_than_zero" id="more_than_zero" onclick="submitForm();" <?php echo $displayType=="more_than_zero"? "checked":"" ?>>&nbsp;No</td>
			</form>
			<td>
			 <?php //echo $this->Html->link('Restore Session', array('action' => 'restore_session', $this->request->params['controller'], $this->request->params['action'], 'Basket', $sessionKioskId));?>
				
			</td>
			<td><a href='#-1' id='rest_sess'>Restore Session By Ajax</a></td>
		</tr>
	</table>
	
	<h4>You have <?php echo count($sessionBaket);?> item(s) in the cart</h4>
	
	<?php echo $this->Form->create(null,array('url' => array('controller' => 'stock_transfer','action' => 'updateStockAjaxBase'),'style' => array('width:100%'))); ?>
	<span><strong>Kiosk</strong><span style='color:red'><sup>*</sup></span> <?php
	
	if( $this->request->session()->read('Auth.User.group_id')== MANAGERS){
				echo $this->Form->input(null, array(
									       'options' => $kiosks,
									       'label' => false,
									       'div' => false,
									       'name' => 'KioskStock[kiosk_id]',
									       'value' => $kiosk_id,
									       'empty' => 'Select Kiosk'
									       )
								   );
	}else{
				echo $this->Form->input(null, array(
									       'options' => $kiosks,
									       'label' => false,
									       'div' => false,
									       'name' => 'KioskStock[kiosk_id]',
									       'value' => $kiosk_id,
									       'empty' => 'Select Kiosk'
									       )
								   );
	}
	?></span>
	<span>&nbsp;</span>
	
	<div class="submit">
	 <table>
	  <tr>
	   
	   <td style='width:30px;'><input type="submit" name='check_out' value="Check out"/></td>
	   <td style='width:5500px;'><input type="submit" name='Dispatch' value="Dispatch"/></td>
	   <td style='width:30px;'><input type="submit" name='empty_basket' value="Clear the Basket"/></td>
	  </tr>
	 </table>
	 
		
	</div>
	<span class='paging' style='text-align:right;float:right;margin-top: -100px;'>
			<?php
				echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
				echo $this->Paginator->numbers(array('separator' => ''));
				echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
			?>
	</span>
	<table cellpadding="0" cellspacing="0">	
		<tr><td colspan='8'><hr></td></tr>
		<tr>			
			<th><?php echo $this->Paginator->sort('product_code'); ?></th>
			<th><?php echo $this->Paginator->sort('product_id'); ?></th>
			<th><?php echo $this->Paginator->sort('category_id'); ?></th>
			<th><?php echo $this->Paginator->sort('color');?></th>
			<th>Image</th>
			<th><?php echo $this->Paginator->sort('quantity','Current');?>&nbsp;<strong>Stock</strong></th>
			<th><?php echo $this->Paginator->sort('selling_price','Selling'); ?>&nbsp;<strong>Price</strong></th>
			<th>New Selling<br/>Price</th>
			<th>Quantity</th>			
			<th>Remarks</th>
		</tr>	
	<tbody>
	<?php $currentPageNumber = $this->Paginator->current();?>	
	<?php
	$groupStr = "";
	foreach ($centralStocks as $key => $centralStock):
	$groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
	 $checked = false;
	?>
	<?php //pr($categoryName);die;
		if(array_key_exists($centralStock->category_id,$categoryName)){
			$catName = $categoryName[$centralStock->category_id];
		}else{
			$catName = "--";
		}
		$truncatedProduct =  \Cake\Utility\Text::truncate(
																$centralStock->product,
																30,
																[
																		'ellipsis' => '...',
																		'exact' => false
																]
														);
		
		$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$centralStock->id.DS;
		$imageName =  $centralStock->image;
		$absoluteImagePath = $imageDir.$imageName;
		$imageURL = "/thumb_no-image.png";
		$largeImageURL = "/vga_thumb_no-image.png";
		if(@readlink($absoluteImagePath) ||file_exists($absoluteImagePath)){
			$imageURL = "{$siteBaseURL}/files/Products/image/".$centralStock->id."/thumb_".$imageName;
			$largeImageURL = "{$siteBaseURL}/files/Products/image/".$centralStock->id."/vga_"."$imageName";
		}
		$productQuantity = "";
		$productPrice = $centralStock->selling_price;
		$productRemarks = "";
		//pr($sessionBaket);
		if( count($sessionBaket) >= 1){			
			if(array_key_exists($centralStock->id,$sessionBaket)){
			 $checked = false;
				$productQuantity = $sessionBaket[$centralStock->id]['quantity'];
				$productPrice = $sessionBaket[$centralStock->id]['price'];
				$productRemarks = $sessionBaket[$centralStock->id]['remarks'];
			}
		}
	?>
	<tr>
		<td>
		<input type="hidden" name="searchQueryUrl" value="<?=$searchQueryUrl;?>"/>
		<?php echo $centralStock->product_code; ?></td>
		<td>
		<?php
			echo $this->Html->link($truncatedProduct,
					array('controller' => 'products', 'action' => 'view', $centralStock->id),
					array('escapeTitle' => false, 'title' => $centralStock->product,'id' => "tooltip_{$centralStock->id}")
				);
			?>
		</td>
		<td><?php echo $catName; ?></td>
		<td><?php echo $centralStock->color; ?></td>
		<td><?php
			echo $this->Html->link(
					$this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')),
					$largeImageURL,
					array('escapeTitle' => false, 'title' => $centralStock->product,'class' => "group{$key}")
				);
			?>
		</td>
		<td><?php echo h($centralStock->quantity); ?>&nbsp;</td>
		<td><?php echo h($centralStock->selling_price); ?>&nbsp;</td>
		<td><?php
			echo $this->Form->input(null,array(
					'type' => 'hidden',
					'name' => "KioskStock[p_quantity][$key]",
					'value' => $centralStock->quantity,
					'label' => false,
					'style' => 'width:80px;'
				)
					       );
			echo $this->Form->input(null,array(
					'type' => 'hidden',
					'name' => "KioskStock[current_price][$key]",
					'value' => $centralStock->selling_price,
					'label' => false,
					'style' => 'width:80px;'
				)
					       );
			echo $this->Form->input(null,array(
					'type' => 'text',
					'name' => "KioskStock[price][$key]",
					'value' => $productPrice,
					'label' => false,
					'style' => 'width:80px;'
					)
				);
			echo $this->Form->input(null,array(
					'id' => 'product_id_'.$key,
					'type' => 'hidden',
					'name' => "KioskStock[product_id][$key]",
					'value' => $centralStock->id
				     )
				);
			echo $this->Form->input(null,array(
					'id' => 'code_'.$key,
					'type' => 'hidden',
					'name' => "KioskStock[product_code][$key]",
					'value' => $centralStock->product_code
				     )
				);
			?>
		</td>
		<td><?php
			echo $this->Form->input(null,array(
					'id' => 'quantity_check_'.$key,
					'type' => 'text',
					'name' => "KioskStock[quantity][$key]",
					'value' => $productQuantity,
					'label' => false,
					'style' => 'width:50px;',
					'value' => 1,
					'readonly' => false
					)
				);
			?>
		</td>		
		<td><?php echo $this->Form->input(null,array(
					'type' => 'text',
					'name' => "KioskStock[remarks][$key]",
					'value' => $productRemarks,
					'label' => false,
					'style' => 'width:80px;',
					'readonly' => false
					)
				); ?>
		</td>
		<td>
			<?php
			echo $this->Form->input(null,array(
                                    'type' => 'checkbox',
                                    'name' => "KioskStock[item][$key]",
                                    'value' => $centralStock->id,
                                    'label' => false,
                                    'style' => 'height:18px; margin-top:8px; transform:scale(1.5);',
                                    'readonly' => false,
									'id' => "checked_qtt_$key",
				    'div'=>false,
				    'checked' => $checked
                                    )
                            );
			?>
		</td>
		
	</tr>
<?php endforeach; ?>
	<?php echo $this->Form->input(null,array('type' => 'hidden','value' => $currentPageNumber,'name' => 'current_page'));?>
	</tbody>
	</table>
	<div class="submit">
	 <table>
	  <tr>
		   
		   <td style='width:30px;'><input type="submit" name='check_out' value="Check out"/></td>
		   <td style='width:5500px;'><?php	$options1 = array('label' => 'Dispatch','div' => false);		
					echo $this->Form->end($options1);		
			 ?>
		   </td>
		   <td style='width:30px;'><input type="submit" name='empty_basket' value="Clear the Basket"/></td>
	  </tr>
	 </table>
	 
	
	</div>
	<p>
<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>

<script>
	
	function submitForm(){
		document.getElementById("display_form").submit();
	}
	
</script>
<input type='hidden' name='url_category' id='url_category' value=''/>
<script type="text/javascript">
     $(document).ready(function(){
         var divLoc = $('#idVal').offset();
         $('html, body').animate({scrollTop: divLoc.top-130}, "slow");
     });
</script>
<script type="text/javascript">
 function update_hidden(){
  //var singleValues = $( "#single" ).val();
  var multipleValues = $( "#category_dropdown" ).val() || [];
  $('#url_category').val(multipleValues.join( "," ));
 }
</script>
<script>
 var product_dataset = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  //prefetch: "/products/data",
  remote: {
    url: "/stock_transfer/admin_data?category=%CID&search=%QUERY",
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
</script>
<script type="text/javascript">
 <?php
   foreach ($centralStocks as $key => $centralStock):
      $string = preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($centralStock->product));
     if(empty($string)){
      $string = $centralStock->product;
     }
      echo "jQuery('#tooltip_{$centralStock->id}').tooltip({content:\"".htmlspecialchars($string)."\",track:true});";
     endforeach;
    ?>
</script>


<input type='hidden' name='url_category' id='url_category' value=''/>
<script type="text/javascript">
 function update_hidden(){
   
  //var singleValues = $( "#single" ).val();
  var multipleValues = $( "#category_dropdown" ).val() || [];
  //  alert(multipleValues);
  $('#url_category').val(multipleValues.join( "," ));
   
 }
</script>
<script>
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
					
	/*filter: function(x) {
                            return $.map(x, function(item) {
                                return {value: item.product};
                            });
                        },*/
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
    /*empty: [
      '<div class="empty-message">',
        'unable to find matching product',
      '</div>'
    ].join('\n'),*/
    suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:400px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{product_code}}</a></strong></div>'),
	header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------<?php echo ADMIN_DOMAIN;?>---------</b></div>"),
  }
});
</script>

<script>
	$("input[id*='quantity_check']").keydown(function (event) {
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||
		event.keyCode == 46  || event.keyCode == 183 ||
		event.keyCode == 110) {
			;
			//48-57 => 0..9
			//8 => Backspace; 9 => tab 
		} else {
			event.preventDefault();
		}
		
        });
</script>
<script>
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>

<script>
 var product_dataset = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		//prefetch: "/products/data",
		remote: {
		  url: "/home/admin_data?category=%CID&search=%QUERY",
						  replace: function (url,query) {
						   var multipleValues = $( "#category_dropdown" ).val() || [];
						   $('#url_category').val(multipleValues.join( "," ));
						   return url.replace('%QUERY', query).replace('%CID', $('#url_category').val());
						  },
						  wildcard: "%QUERY"
		  
		}
	});

	$('#remote_cart .typeahead').typeahead(null, {
		name: 'product',
		display: 'code',
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
</script>
<script>
 $('#scanner_form').submit(function( event ) {
		var product_code = $('#scanner_input').val();
		var qty = $('#scanner_qty').val();
		event.preventDefault();
		if ($.trim(qty) == "") {
            qty = 1;
        }else{
			qty = parseInt(qty);
		}
		if (qty == 0) {
            alert("Please input valid quantity");
			return;
        }
		
		var targeturl = $("#scanner_input").attr('rel')+ '?product_code=' + product_code+"&quantity="+parseInt(qty);
		
		$.blockUI({ message: 'Updating cart...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				var objArr = $.parseJSON(response);
				
					if (typeof objArr.prodError == "undefined") {
                        show_cart(objArr);
                    }else{
						var errorMsg = objArr.prodError
						var position = errorMsg.indexOf("out of stock");
						var invalidCodePos = errorMsg.indexOf("adjusted");
						if (position >= 0) {
                            $( "#out-of-stock" ).dialog({
								resizable: false,
								height:140,
								modal: true,
								closeText: "Close",
								width:300,
								maxWidth:300,
								title: '!!! Out of Stock!!!',
								buttons: {
									"OK": function() {
										$( this ).dialog( "close" );
									}
								}
							});
                        }else if (invalidCodePos > 0) {
							//var errorMsg = $('#qtyAdjusted').text(objArr.prodError);
                            $( "#invalid" ).dialog({
								resizable: false,
								height:140,
								modal: true,
								closeText: "Close",
								width:300,
								maxWidth:300,
								title: 'Quantity Adjusted',
								buttons: {
									"OK": function() {
										$( this ).dialog( "close" );
									}
								}
							});
							show_cart(objArr);
                        }else{
							//default case
							show_cart(objArr);
						}
					}
					$('#scanner_input').val("");
				
				$.unblockUI();
			},
			error: function(e) {
				$.unblockUI();
				alert("An error occurred: " + e.responseText.message);
				console.log(e);
			}
		});
	});
 
 function show_cart(objArr) {
        var itemObj = objArr;
		var innerHTMLStr = "<table width='100%' cellspacing='2' cellpadding='2'><tr><th style='width:30px;'>Del</th><th>Code</th><th>Title</th><th>Unit Price</th><th>Qty</th><th>Edit</th></tr>";
		var resultFound = false;
		var total_amount = 0
		
		var posArr = [];
		var counter = 0;
		$.each(itemObj, function(key, obj){
			posArr[counter++] = obj.position;
		});
		
		posArr.sort(function(a, b){return b-a});
		var position;
		
		for (i = 0; i < posArr.length; i++) {
		 
			position = parseInt(posArr[i]);
			$.each(itemObj, function(key, obj){
				resultFound = true;
				
				if (position == parseInt(obj.position)) {
				  	
					var short_qty = obj.qantity_short;
					if (parseInt(short_qty) == 1) {
                        var style_short = "color:red;background-color: yellow;"; 
                    }else{
						var style_short = "";
					}
					//alert(short_qty);
					
					innerHTMLStr += "<tr style = '"+style_short+"'><td><a href='#-1' id='del_item' class='del_from_cart' rel="+key+" title='Are you sure you want to delete "+ obj.product+" from cart?'>Del</a></td>";
					innerHTMLStr += "<td>"+obj.product_code+"</td>";
					innerHTMLStr += "<td>"+obj.product+"</td>";
					var min_dis = obj.price;
					//innerHTMLStr += "<input type='hidden' id='cart_dis_"+key+"' value="+min_dis+" />";
					if (min_dis == 0) {
						innerHTMLStr += "<input type='hidden' id='cart_dis_"+key+"' value="+obj.selling_price+" />";
						var title = "N/A";
						innerHTMLStr += "<td><input type='text' title = '"+title+"' name='sp' value='"+obj.selling_price+"' style='width:50px;' id='cart_sp_"+key+"'  onchange='check_sp_cart_na("+key+")'/></td>";
					}else{
						innerHTMLStr += "<input type='hidden' id='cart_dis_"+key+"' value="+min_dis+" />";
						var title = "Minimum Price: "+ parseFloat(min_dis).toFixed(2);
						innerHTMLStr += "<td><input type='text' title = '"+title+"' class='validate_qty1' name='sp' value='"+obj.price+"' style='width:50px;' id='cart_sp_"+key+"' onchange='check_sp_cart("+key+")'/></td>";
					}
					innerHTMLStr += "<td style='width:40px;'><input onkeydown='validateNumber(event);' type='text'  title = 'Quantity Available: "+obj.available_qantity+"' name='qty' value='"+obj.quantity+"' style='width:40px;' id='cart_qty_"+key+"'/></td>";
					
					innerHTMLStr += "<td><a href='#-1' class = 'update_cart' rel='"+key+"'>Update</a></td></tr>";
                }
			});
		}
	
		
		
		
		
		if (!resultFound) {
            innerHTMLStr += "<tr><td colspan='7' align='center' style='text-align:center;'><span style='color:red; font-size:14px;'>!!!No Record Found!!!</span></td></tr>";
        }
		innerHTMLStr += "</table>";
		$("#cartDiv").html(innerHTMLStr);
		$("#cart_fieldset").show();
		$('#scanner_qty').val('1');
		$( "#scanner_input" ).focus();
    }
 
 $(".del_from_cart").easyconfirm({locale: { title: 'Delete Item from Cart?', button: ['No','Yes']}});
 
 $(document).on('click', '.del_from_cart', function() {
		var product_id = $(this).attr('rel');
		var alertTitle = $(this).attr('title');
		//var response = confirm("Are You Sure You Want To Delete");
		response = true;
		if (response == true) {
			var targeturl = $("#delte_cart").val();
			targeturl += '?prod_id='+product_id;
			//----------------
			$( "#dialog-confirm" ).dialog({
				resizable: false,
				height:140,
				modal: true,
				buttons: {
					"Agree": function() {
						//----------------------------------------
						$.blockUI({ message: 'Updating cart...' });
						$.ajax({
							type: 'get',
							url: targeturl,
							beforeSend: function(xhr) {
								xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
							},
							success: function(response) {
								var objArr = $.parseJSON(response);
									show_cart(objArr);
									$('#scanner_input').val("");
								$.unblockUI();
							},
							error: function(e) {
								$.unblockUI();
								alert("An error occurred: " + e.responseText.message);
								console.log(e);
							}
						});
						//----------------------------------------
						$( this ).dialog( "close" );
					},
					Cancel: function() {
						$( this ).dialog( "close" );
					}
				}
			});
		}
	});
 
 $(document).on('click', '.update_cart', function() {
		var product_id = $(this).attr('rel');
		var quantity = $('#cart_qty_'+product_id).val();
		var selling_price = $('#cart_sp_'+product_id).val();
		//alert(special_invoice);
		var targeturl = $("#update_cart").val();
		targeturl += '?prod_id='+product_id;
		targeturl += '&qty='+quantity;
		targeturl += '&sp='+selling_price;
		
		$.blockUI({ message: 'Updating cart...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				$('#search_prod').val("");
				$('#searchOuterDiv').hide(1000);
				var objArr = $.parseJSON(response);
					show_cart(objArr);
				$.unblockUI();
			},
			error: function(e) {
				$.unblockUI();
				alert("An error occurred: " + e.responseText.message);
				console.log(e);
			}
		});
	});
 
</script>
<script>
	 $(document).ready(function() {
	  <?php
	  foreach($centralStocks as $s_key => $s_val){ ?>
		$('#checked_qtt_'+<?php echo $s_key?>).change(function() {
		 
		  if($(this).is(":checked")) { // if checked
			var qty = $('#quantity_check_'+<?php echo $s_key?>).val();
			if (qty <= 0 || qty == "") {
                $( "#zero_qty" ).dialog({
						  resizable: false,
						  height:140,
						  modal: true,
						  closeText: "Close",
						  width:300,
						  maxWidth:300,
						  title: '!!! Quantity Cannot Be Zero!!!',
						  buttons: {
							  "OK": function() {
								  $( this ).dialog( "close" );
							  }
						  }
					  });
				$('#checked_qtt_'+<?php echo $s_key?>).removeAttr("checked");
				return false;
            }
			
			
			var product_code = $('#code_'+<?php echo $s_key?>).val();
			var kiosk_id = $('#Product').val();
			
			var targeturl = $("#add_to_cart_short").val();
			//alert(targeturl);
			targeturl += '?product_code='+product_code;
		    targeturl += '&quantity='+qty;
			
			   
			//alert(targeturl);return false;
			$.blockUI({ message: 'Updating cart...' });
			$.ajax({
			  type: 'get',
			  url: targeturl,
			  beforeSend: function(xhr) {
				  xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			  },
			  success: function(response) {
				var objArr = $.parseJSON(response);
				
				//alert(objArr.basket);
				if (objArr.hasOwnProperty('position')) {
					$('#quantity_check_'+<?php echo $s_key?>).attr("disabled", "disabled");
					show_cart(objArr);
					$('#checked_qtt_'+<?php echo $s_key?>).removeAttr("checked");
					$('#error_div').html("");
				}else if (objArr.hasOwnProperty('error')) {
                    document.getElementById('flash_msg').innerHTML  = old_msg;
					document.getElementById('more_qty').innerHTML  = objArr.error;
					$('#net_val_'+<?php echo $s_key?>).val("");
					//$('#selling_price_'+<?php echo $s_key?>).val("");
					$('#disc_amnt_'+<?php echo $s_key?>).val("");
					var org_qty = $('#p_org_quantity_'+<?php echo $s_key?>).val();
					//alert(org_qty);
					$('#Qty_'+<?php echo $s_key?>).val(org_qty);
					$('#remarks_'+<?php echo $s_key?>).val("");
					
					$( "#more_qty" ).dialog({
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
							  }
						  }
					  });
					//alert(objArr.error);
					//$('#flash_msg').append(objArr.error);
					//$('#flash_msg').append(objArr.error);
					//document.getElementById('flash_msg').innerHTML  = objArr.error;
                }
				  $.unblockUI();
			  },
			  error: function(e) {
				  $.unblockUI();
				  //alert("An error occurred: " + e.responseText.message);
				  var msg = "An error occurred: " + e.responseText.message;
				  document.getElementById('error_for_alert').innerHTML = msg;
				 //alert("An error occurred: " + e.responseText.message);
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
							  }
						  }
					  });
				 //console.log(e);
				  console.log(e);
			  }
			});
			//alert(remarks);alert(net_val);alert(dis_amt);alert(selling_price);alert(qty);
			
		  }else{ // if unchecked
			var product_code = $('#product_id_'+<?php echo $s_key?>).val();
			var qty = $('#quantity_check_'+<?php echo $s_key?>).val();
			
			var targeturl = $("#delte_cart").val();
			targeturl += '?prod_id='+product_code;
			
			
			
			
			$.blockUI({ message: 'Updating cart...' });
			$.ajax({
			   type: 'get',
			  url: targeturl,
			  beforeSend: function(xhr) {
				  xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			  },
			   success: function(response) {
				var objArr = $.parseJSON(response);
				//alert(objArr.basket);
				show_cart(objArr);
				  $('#quantity_check_'+<?php echo $s_key?>).removeAttr("disabled");
				  $('#error_div').html("");
				  $.unblockUI();
			   },
			   error: function(e) {
				  $.unblockUI();
				  //alert("An error occurred: " + e.responseText.message);
				  var msg = "An error occurred: " + e.responseText.message;
				  document.getElementById('error_for_alert').innerHTML = msg;
				  //alert("An error occurred: " + e.responseText.message);
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
							  }
						  }
					  });
				  console.log(e);
			  }
			});
			  
		  }
		});
	 <?php }
	  ?>
  });
</script>
<script>
 $(document).on('click', '#rest_sess', function() {
		var targeturl = $("#restore_cart").val();
		//$.blockUI({ message: 'Resoring session...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				$('#invoice_amount').val('#####');
				var objArr = $.parseJSON(response);
				if (objArr.hasOwnProperty('position')) {
					show_cart(objArr);
					$('#cart_fieldset').show();
				}else{
					//rasu
					//alert(objArr.msg+' - case restore session');
					//$( "#nothing-to-restore" ).dialog({
					//	resizable: false,
					//	height:140,
					//	modal: true,
					//	closeText: "Close",
					//	width:300,
					//	maxWidth:300,
					//	title: 'Nothing to restore from session',
					//	buttons: {
					//		"OK": function() {
					//			$( this ).dialog( "close" );
					//		}
					//	}
					//});
				}
				//$.unblockUI();
			},
			error: function(e) {
				$.unblockUI();
				alert("An error occurred: Please Log In to Continue ");
				console.log(e);
			}
		});
	});
</script>
<script>
 $(document).ready(function(){
  $('#scanner_input').focus();
  $('#dialog-confirm').hide();
  $('#dialog-pmt').hide();
  $('#dialog-pmt-not-equal').hide();
  $('#zero_qty').hide();
  $('#dialog-pmt-exceeding').hide();
  $('#dialog-selling_amount').hide();
  $('#out-of-stock').hide();
  $('#nothing-to-restore').hide();
  $('#invalid').hide();
  $('#Wrong-input').hide();
  $("#cart_fieldset").hide();
  $("#rest_sess").trigger("click")
 });
</script>