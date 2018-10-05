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
<?php
$check_price = $this->Url->build(['controller' => 'stock-transfer', 'action' => 'check_price'],true);
	extract($this->request->query);
	if(!isset($search_kw)){$search_kw = "";}
	if(empty($this->request->query)){
		$displayType = "more_than_zero";
		
	}
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
<input type='hidden' name='check_price' id='check_price' value='<?=$check_price?>' />
<div class="centralStocks index">

   <div id="idVal">
  
</div>
</script>
	<form action='<?php echo $this->request->webroot;?>stock-transfer/search' method = 'get'>
		<div class="search_div">
			<fieldset>
				<legend>Search</legend>
				<table>
					<tr>
						<td></td>
						<td><strong>Find by category &raquo;</strong></td>						
					</tr> <?php //' $search_kw ' ?>
					<tr><td><div id='remote'><input class="typeahead" type = "text" value = '<?= $search_kw ?>' name = "search_kw"  placeholder = "Search by product title or product code"  autofocus style = "width:325px"/></div></td>
					
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
			</fieldset>	
		</div>
	</form>
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
			 <?=$this->Html->link('Restore Session', array('action' => 'restore_session', $this->request->params['controller'], $this->request->params['action'], 'Basket', $sessionKioskId));?>
				
			</td>
		</tr>
	</table>
	
	<h4>You have <?php echo count($sessionBaket);?> item(s) in the cart</h4>
	
	<?php echo $this->Form->create(null,array('url' => array('controller' => 'stock_transfer','action' => 'update_stock'))); ?>
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
	   <td style='width:30px;'><input type="submit" name='basket' value="Add to Basket"/></td>
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
			<th>New Selling<br/>Price(Inc. Vat)</th>
			<th>Quantity</th>			
			<th>Remarks</th>
		</tr>	
	<tbody>
	<?php $currentPageNumber = $this->Paginator->current();?>	
	<?php
	$groupStr = "";
	foreach ($centralStocks as $key => $centralStock):
	$groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
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
		$LargeimageURL = "/vga_thumb_no-image.png";
		if(@readlink($absoluteImagePath) ||file_exists($absoluteImagePath)){
			$imageURL = "/files/Products/image/".$centralStock->id."/$imageName";
			$LargeimageURL = "/files/Products/image/".$centralStock->id."/vga_"."$imageName";
		}
		$productQuantity = "";
		$productPrice = $centralStock->selling_price;
		$productRemarks = "";
		//pr($sessionBaket);
		if( count($sessionBaket) >= 1){			
			if(array_key_exists($centralStock->id,$sessionBaket)){
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
					$LargeimageURL,
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
					'style' => 'width:80px;',
					'id' => "price_$key",
					'old_price' => $productPrice,
					)
				);
			echo $this->Form->input(null,array(
					'type' => 'hidden',
					'name' => "KioskStock[product_id][$key]",
					'value' => $centralStock->id
				     )
				);
			?>
		</td>
		<td><?php
			echo $this->Form->input(null,array(
					'id' => 'quantity_check',
					'type' => 'text',
					'name' => "KioskStock[quantity][$key]",
					'value' => $productQuantity,
					'label' => false,
					'style' => 'width:50px;',
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
	</tr>
<?php endforeach; ?>
	<?php echo $this->Form->input(null,array('type' => 'hidden','value' => $currentPageNumber,'name' => 'current_page'));?>
	</tbody>
	</table>
	<div class="submit">
	 <table>
	  <tr>
		   <td style='width:30px;'><input type="submit" name='basket' value="Add to Basket"/></td>
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
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Kiosk 2 WH <br>Trnsient Ordrs'), array('controller' => 'kiosk_orders','action' => 'transient_orders'),array('escape' => false)); ?></li>
		<li><?php echo $this->Html->link(__('Placed Order'), array('controller' => 'kiosk_orders', 'action' => 'placed_orders')); ?> </li>
		<li><?php echo $this->Html->link(__('WH 2 Kiosk <br>Confmd Orders'), array('controller' => 'kiosk_orders', 'action' => 'confirmed_orders'),array('escape' => false)); ?> </li>
	</ul>
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
<script>
$(function() {
  $( document ).tooltip({
   //content: function () {
    //return $(this).prop('title');
   }
  });
 });
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
<?php
foreach ($centralStocks as $key => $centralStock){
?>
$("#price_<?php echo $key;?>").blur(function(){
	 var price = $("#price_<?php echo $key;?>").val();
	 var old_price = $("#price_<?php echo $key;?>").attr("old_price");
	 
	 var product_id = <?php echo $centralStock->id;?>;
	 var targeturl = $("#check_price").val();
	 targeturl += "?id="+product_id;
	 targeturl += "&price="+price;
	 $.blockUI({ message: 'Just a moment...' });
	 $.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			
			success: function(response) {
			 $.unblockUI();
			  var objArr = $.parseJSON(response);
			  if (objArr.msg == "ok") {
                
              }else if(objArr.msg == "error"){
				 alert("price is less then cost price");
				 $("#price_<?php echo $key;?>").val(old_price);
			  }
			},
			error: function(e) {
			 $.unblockUI();
				$.unblockUI();
				alert("An error occurred.");
				console.log(e);
			}
	 });
 });

<?php
}
?> 
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