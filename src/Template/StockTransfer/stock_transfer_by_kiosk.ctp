<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
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
<?php
	$currency = Configure::read('CURRENCY_TYPE');
	$adminDomainURL = URL_SCHEME.ADMIN_DOMAIN;
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<?php
	$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
	extract($this->request->query);
	if(!isset($search_kw)){$search_kw = "";}
	//pr($this->request);
	if((empty($this->request->query) || count($this->request->query) == 1) && !array_key_exists("display_type",$this->request->query)){
		$displayType = "more_than_zero";
		
	}else{
	   $displayType = $this->request->query['display_type'];
	}
	//pr($this->Session->read('Basket'));
?>
<div class="centralStocks index">
	<form action='<?php echo $this->request->webroot;?>stock-transfer/search_stock_transfer_by_kiosk' method = 'get'>
		<div class="search_div">
			<fieldset>
				<legend>Search</legend>
				<table>
					<tr>
						<td></td>
						<td><strong>Find by category &raquo;</strong></td>						
					</tr>
					<tr><td><div id='remote'><input class="typeahead" type = "text" value = '<?= $search_kw ?>' name = "search_kw" placeholder = "Search by product title or product code" style = "width:325px" autofocus/></div></td>
						<td rowspan="4"><select id='category_dropdown' name='category[]' multiple="multiple" size='6'onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select></td></tr>
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
		$sessionBaket = $this->request->Session()->read("stBasket");
		$kiosk_id = "";
		$s_kiosk_id = $this->request->Session()->read("kiosk_id");
		if( !empty($s_kiosk_id) ){
			$kiosk_id = $this->request->Session()->read("kiosk_id");
		}
	?>
	<table>
		<tr>
			<td><h2><?php echo __('Stock Transfer'); ?></h2></td>
			<td style="width: 25%;">Show items with zero quantity</td>
			<form name="display_form" id="display_form" method="get">
			<td style="width: 7%;"><input type="radio" name="display_type" value="show_all" id="show_all" onclick="submitForm();" <?php echo $displayType=="show_all"? "checked":"" ?>>&nbsp;Yes</td>
			<td><input type="radio" name="display_type" value="more_than_zero" id="more_than_zero" onclick="submitForm();" <?php echo $displayType=="more_than_zero"? "checked":"" ?>>&nbsp;No</td>
			</form>
		</tr>
	</table>
	
	<h4>You have <?php echo count($sessionBaket);?> item(s) in the cart</h4>
	
	<?php echo $this->Form->create(null,array('url' => array('controller' => 'stock_transfer','action' => 'update_center_stock'))); ?>
	
	
	<div class="submit">
	 <table>
	  <tr>
	   <td style='width: 30px';><input type="submit" name='basket' value="Add to Basket"/></td>
	   <td style='width:  5555px';><input type="submit" name='Dispatch' value="Dispatch"/></td>
	   <td><input type="submit" name='checkout' value="Checkout" id="checkout_top" onclick="return checksession1();"/></td>
	   <td style='width: 35px';><input type="submit" name='empty_basket' value="Clear the Basket"/></td>
	  </tr>
	 </table>

	</div>
	<br><br> <br>
	<span class='paging' style='text-align:right;float:right;margin-top: -50px;'>
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
			<th><?php echo $this->Paginator->sort('color');?></th>
			<th>Image</th>
			<th><?php echo $this->Paginator->sort('quantity','Current');?>&nbsp;<strong>Stock</strong></th>
			<th><?php echo $this->Paginator->sort('selling_price','Selling'); ?>&nbsp;<strong>Price</strong></th>
			
			<th>Quantity</th>			
			<th>Remarks</th>
		</tr>	
	<tbody>
	<?php $currentPageNumber = $this->Paginator->current();?>	
	<?php foreach ($centralStocks as $key => $centralStock): ?>
	<?php //pr($centralStock);
		$truncatedProduct = \Cake\Utility\Text::truncate(
															$centralStock->product,
															22,
															[
																	'ellipsis' => '...',
																	'exact' => false
															]
													);
		
		$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$centralStock->id.DS;
		$imageName = $centralStock->image;
		$absoluteImagePath = $imageDir.$imageName;
		$imageURL = "/thumb_no-image.png";
		if(!empty($imageName)){
			$imageURL = "{$adminDomainURL}/files/Products/image/".$centralStock->id."/thumb_"."$imageName"; //rasu
		}
		$productQuantity = "";
		$productPrice = $centralStock->selling_price;
		$productRemarks = "";
		//pr($sessionBaket);
		if( count($sessionBaket) >= 1){			
			if(array_key_exists($centralStock->id,$sessionBaket)){
				$productQuantity = $sessionBaket[$centralStock->id]['quantity'];
				$productPrice = $sessionBaket[$centralStock->id]['price'];
			}
		}
	?>
	<tr>
		<td><?php echo $centralStock->product_code; ?></td>
		<td>
		<?php
			echo $this->Html->link($truncatedProduct,
					array('controller' => 'products', 'action' => 'view', $centralStock->id),
					array('escapeTitle' => false, 'title' => $centralStock->product, 'id' => "tooltip_{$centralStock->id}")
				);
			?>
		</td>
		<td><?php echo $centralStock->color; ?></td>
		<td><?php
			echo $this->Html->link(
					$this->Html->image($imageURL, array('fullBase' => false,'width'=> '100px','height' => '100px')), //rasu
					array('controller' => 'products','action' => 'edit', $centralStock->id),
					array('escapeTitle' => false, 'title' => $centralStock->product)
				);
			?>
		</td>
		<td><?php echo h($centralStock->quantity); ?>&nbsp;</td>
		<td><?php echo $CURRENCY_TYPE.$centralStock->selling_price; ?>&nbsp;</td>
		
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
					'type' => 'hidden',
					'name' => "KioskStock[product_id][$key]",
					'value' => $centralStock->id
				     )
				);
			echo $this->Form->input(null,array(
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
		<td>
		<?php echo $this->Form->input(null,array(
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
	   <td style = 'width:30px';><input type="submit" name='basket' value="Add to Basket"/></td>
	   <td style = 'width:5555px';><?php		
		$options1 = array('label' => 'Dispatch','div' => false);		
		echo $this->Form->end($options1);		
	   ?></td>
	   <td><input type="submit" name='checkout' value="Checkout" id="checkout_bottom" onclick="return checksession2();"/></td>
	   <td style = 'width:30px';><input type="submit" name='empty_basket' value="Clear the Basket"/></td>
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
		<li><?php echo $this->Html->link(__('Transient Orders'), array('controller' => 'kiosk_orders', 'action' => 'transient_orders')); ?> </li>
		<li><?php echo $this->Html->link(__('Placed Order'), array('controller' => 'kiosk_orders', 'action' => 'placed_orders')); ?> </li>
		<li><?php echo $this->Html->link(__('Confirmed Orders'), array('controller' => 'kiosk_orders', 'action' => 'confirmed_orders')); ?> </li>		
	</ul>
</div>
<script>
	
	function submitForm(){
		document.getElementById("display_form").submit();
	}
	
</script>

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
	footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------<?php echo ADMIN_DOMAIN;?>---------</b></div>"),
  }
});
</script>
<script>
<?php
   foreach ($centralStocks as $key => $centralStock):
      $string = preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($centralStock['Product']['product']));
     if(empty($string)){
      $string = $centralStock->product;
      //htmlspecialchars($str, ENT_NOQUOTES, "UTF-8")
     }
      echo "jQuery('#tooltip_{$centralStock->id}').tooltip({content:\"".htmlspecialchars($string)."\",track:true});";
     endforeach;
    ?>
    
    function checksession1 ()
     {
      <?php if(empty($sessionBaket)){?>
      alert("Session basket is empty, please first add items to basket!");
	  //Bug by Inderpreet
      return false;
     <?php } ?>
     }
     
     function checksession2 ()
     {
      <?php if(empty($sessionBaket)){?>
      alert("Session basket is empty, please first add items to basket!");
	  //Bug by Inderpreet
      return false;
     <?php } ?>
     }
     
    
</script>