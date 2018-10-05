<?php #pr($centralStocks);
	extract($this->request->query);
	if(!isset($search_kw)){$search_kw = "";}
?>
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

<div class="centralStocks index">
	<form name= "search_form" id = "search_form" action='<?php echo $this->request->webroot;?>stock-initializers/search' method = 'get'>
		<div class="search_div">
			<fieldset>
				<legend>Search</legend>
				<table>
					<tr>
						<td></td>
						<td><strong>Find by category &raquo;</strong></td>						
					</tr>
					<tr><td><div id='remote'><input class="typeahead" type = "text" value = '<?= $search_kw ?>' name = "search_kw" placeholder = "Product title or product code" style = "width:254px"  autofocus/></div></td>
						<td rowspan="4"><select id='category_dropdown' name='category[]' multiple="multiple" size='6'><option value="0" <?php if(!empty($this->request->query) && isset($this->request->query['category']) && in_array('0', $this->request->query['category'])){echo "selected";}?>>All</option><?php echo $categories;?></select></td></tr>
					<tr>
						<td><h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4></td>
					</tr>
					<tr>
						<td><input type = "submit" name = "submit1" value = "Search Product"/></p></td>
					</tr>					
				</table>
			</fieldset>	
		</div>
	</form>
	<?php
	
	echo $this->Form->create('HiddenKioskForm', array('url' => array('controller' => 'stock_initializers', 'action' => 'add_to_kiosk'), 'id' => 'hiddenForm'));
	echo $this->Form->input('selectedKiosk',array('type' => 'hidden', 'id' => 'hidden_kiosk'));
	echo $this->Form->end(); ?>
	<?php
		$sessionBaket = $this->request->Session()->read("StockInitBasket");
		$kiosk_id = "";
		$s_kiosk_id = $this->request->Session()->read("kioskID");
		if( !empty($s_kiosk_id) ){
			$kiosk_id = $this->request->Session()->read("kioskID");
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
	<h2><?php echo __('Stock Initializer')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>				
	</h2>
	<h4>You have <?php echo count($sessionBaket);?> item(s) in the cart</h4>
	
	<?php echo $this->Form->create(null,array('url' => array('controller' => 'stock_initializers','action' => 'initialize_stock'))); ?>
	<span><strong>Kiosk</strong><span style='color:red'><sup>*</sup></span> <?php
	if( $this->request->session()->read('Auth.User.group_id')== MANAGERS){
		echo $this->Form->input(null, array(
											   'options' => $kiosks,
											   'label' => false,
											   'div' => false,
											   'name' => 'KioskStock[kiosk_id]',
											   'value' => $kiosk_id,
											   'empty' => 'Select Kiosk',
											   'id' => 'Product'
											   //'onchange'=>"this.form.submit()"
											   )
									   );
	}else{
		echo $this->Form->input(null, array(
											   'options' => $kiosks,
											   'label' => false,
											   'div' => false,
											   'name' => 'KioskStock[kiosk_id]',
											   'value' => $kiosk_id,
											   'empty' => 'Select Kiosk',
											   'id' => 'Product'
											   //'onchange'=>"this.form.submit()"
											   )
									   );
	}
	?></span>
	<span>&nbsp;</span>
	
	<div class="submit">
		<table style="width:100%">
			<tr>
				<td>
					<input type="submit" name='basket' value="Add to Kiosk" id="add_upper"/>
					<input type="submit" name='Dispatch' value="Initialize Stock"/>
					<?php
						//if($_SERVER['REMOTE_ADDR'] == '124.253.58.119'){
							echo "<input type='submit' name='checkout' value='Checkout' id='upper_checkout'/>";
						//}
					?>
				</td>
				<td>
					<div class="paging" style="margin-right: -307px;margin-top: -70px;">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
				</td>
				<td>
					<input type="submit" name='empty_basket' value="Clear the Kiosk"/>
				</td>
			</tr>
			<tr><td colspan='2' style="padding-top: 10px;"><h4 style="color: blue;font-style: italic;">***To View kiosk current quantities select kiosk and press add to basket</h4></td>
			<td><?= $this->Html->link('Restore Session', array('action' => 'restore_session', "StockInitializers", "add_to_kiosk", 'StockInitBasket', ''));?>
			</td>
			</tr>
		</table>
	</div>
	
	<table cellpadding="0" cellspacing="0">	
		<tr><td colspan='8'><hr></td></tr>
		<tr>
                        <th><?php echo $this->Paginator->sort('product_code'); ?></th>
			<th><?php echo $this->Paginator->sort('product_id'); ?></th>
                        <th><?php echo $this->Paginator->sort('color'); ?></th>
			<th>Image</th>
			
			
			<th><?php echo $this->Paginator->sort('selling_price','Selling'); ?>&nbsp;<strong>Price</strong></th>
			<th>Current <br/>Quantity</th>
			
			<th>New <br/>Quantity</th>			
			
		</tr>	
	<tbody>
	<?php $currentPageNumber = $this->Paginator->current();?>	
	<?php
	$groupStr = "";
	foreach ($centralStocks as $key => $centralStock):
	$groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
	?>
	<?php
		$truncatedProduct =
									\Cake\Utility\Text::truncate(
                                                                        $centralStock->product,
                                                                        30,
                                                                        [
                                                                                'ellipsis' => '...',
                                                                                'exact' => false
                                                                        ]
                                                                );
		
		$imageDir = WWW_ROOT.DS."files".DS.'Products'.DS.'image'.DS.$centralStock->id.DS;
		$imageName = $centralStock->image;
		$absoluteImagePath = $imageDir.$imageName;
		$LargeimageURL = $imageURL = "/thumb_no-image.png";
		if(@readlink($absoluteImagePath) || file_exists($absoluteImagePath)){
			$imageURL = "/files/Products/image/".$centralStock->id."/$imageName";
			$LargeimageURL = "/files/Products/image/".$centralStock->id."/vga_"."$imageName";
		}
		$sellingPrice = $centralStock->selling_price;
		$productQuantity = '';
		//$productPrice = $centralStock['Product']['cost_price'];
		//$productRemarks = "";
		if( count($sessionBaket) >= 1){
			if(array_key_exists($centralStock->id,$sessionBaket)){                            
				$productQuantity = $sessionBaket[$centralStock->id]['quantity'];
				$sellingPrice = $sessionBaket[$centralStock->id]['selling_price'];
				//$productPrice = $sessionBaket[$centralStock['Product']['id']]['price'];
				//$productRemarks = $sessionBaket[$centralStock['Product']['id']]['remarks'];
			}
		}
	?><tr>
                <td>
                        <?php echo $centralStock->product_code;?>
                </td>
		<td>
		<?php
			echo $this->Html->link($truncatedProduct,
					array('controller' => 'products', 'action' => 'view', $centralStock->id),
					array('escapeTitle' => false, 'title' => $centralStock->product, 'id' => "tooltip_{$centralStock->id}")
				);
			?>
		</td>
                <td><?php echo $centralStock->color;?></td>
		<td><?php
			echo $this->Html->link(
					$this->Html->image($imageURL, array('fullBase' => true,'width'=>'100px','height'=>'100px')),
					$LargeimageURL,
					array('escapeTitle' => false, 'title' => $centralStock->product,'class' => "group{$key}")
				);
			?>
		</td>
		
		<td><?php echo $this->Form->input(null,array(
					'type' => 'text',
					'name' => "KioskStock[selling_price][$key]",
					'value' => number_format($sellingPrice,2),
					'label' => false,
					'style' => 'width:50px;',
					'readonly' => false
					)
				);?>&nbsp;</td>		
		<td><?php
                        echo $this->Form->input(null,array(
					'type' => 'hidden',
					'name' => "KioskStock[p_quantity][$key]",
					'value' => $centralStock->quantity,
					'label' => false,
					'style' => 'width:80px;'
				)
					       );
						echo $codeArr[$centralStock->product_code];
                        echo $this->Form->input(null,array(
					'type' => 'hidden',
					'name' => "KioskStock[product_id][$key]",
					'value' => $centralStock->id
				     )
				);
						echo "</td>";
						echo "<td>";
			echo $this->Form->input(null,array(
					'id' => 'in_qty_'.$key, 
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
		
	</tr>
<?php endforeach; ?>
	<?php echo $this->Form->input(null,array('type' => 'hidden','value' => $currentPageNumber,'name' => 'current_page'));?>
	</tbody>
	</table>
	<div class="submit">
		<input type="submit" name='basket' value="Add to Kiosk" id="add_lower"/>
		<input type='submit' name='checkout' value='Checkout' id='lower_checkout'/>
	<?php		
		$options1 = array('label' => 'Initialize Stock','div' => false);		
		echo $this->Form->end($options1);		
	?>
	<input type="submit" name='empty_basket' value="Clear the Kiosk"/>
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
		<li><?php echo $this->Html->link(__('Transient Orders'), array('controller' => 'kiosk_orders', 'action' => 'transient_orders')); ?> </li>
		<li><?php echo $this->Html->link(__('Placed Order'), array('controller' => 'kiosk_orders', 'action' => 'placed_orders')); ?> </li>
		<li><?php echo $this->Html->link(__('Confirmed Orders'), array('controller' => 'kiosk_orders', 'action' => 'confirmed_orders')); ?> </li>		
	</ul>
</div>
<input type='hidden' name='url_category' id='url_category' value=''/>
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
    url: "/Products/admin_data?category=%CID&search=%QUERY",
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
	footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------hpwaheguru.co.uk---------</b></div>"),
  }
});
</script>
<script>
	$("#add_upper").click(function(){
		var kiosk = $("#Product").val();
		if (kiosk == "") {
			alert("Please choose kiosk");
			return false;
		}
	});
	
	$("#add_lower").click(function(){
		var kiosk = $("#Product").val();
		if (kiosk == "") {
			alert("Please choose kiosk");
			return false;
		}
	});
	
	$("#upper_checkout").click(function(){
		<?php $session_basket = $this->request->Session()->read('StockInitBasket');
		if(empty($session_basket)){ ?>
			
			var bkt = 0;
		<?php }else{ ?>
			var bkt = 1;
		<?php } ?>
		
		if (!bkt) {
			alert("Please add the items to kiosk");
			return false;
		}
	});
	
	$("#lower_checkout").click(function(){
		<?php $session_basket = $this->request->Session()->read('StockInitBasket');
		if(empty($session_basket)){ ?>
			
			var bkt = 0;
		<?php }else{ ?>
			var bkt = 1;
		<?php } ?>
		
		if (!bkt) {
			alert("Please add the items to kiosk");
			return false;
		}
	});
</script>
<script type="text/javascript">
<?php
   foreach ($centralStocks as $centralStock):
		$string = preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($centralStock->product));
		if(empty($string)){
			$string = $centralStock->product;
		}
		echo "jQuery('#tooltip_{$centralStock->id}').tooltip({content:\"".htmlspecialchars($string)."\",track:true});";
    endforeach;
?>
$('#Product').change(function(){
	if ($(this).val() != '') {
		$.blockUI({ message: 'Loading ...' });
		$("#hidden_kiosk").val($(this).val());
		$("#hiddenForm").submit();
    }
});
</script>
<script>
	$("input[id*='in_qty_']").keydown(function (event) {
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||
		event.keyCode == 46  || event.keyCode == 183
		) {
			;
			//48-57 => 0..9
			//8 => Backspace; 9 => tab 
		} else {
			event.preventDefault();
		}
    });
</script>
<script>
$(function() {
  $( document ).tooltip({
   //content: function () {
    //return $(this).prop('title');
   //}
  });
 });
</script>
<script>
   $('#Product').change(function(){
	//$.blockUI({ message: 'Loading ...' });
	//document.getElementById("search_form").submit();
	  }); 
</script>
<script>
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>