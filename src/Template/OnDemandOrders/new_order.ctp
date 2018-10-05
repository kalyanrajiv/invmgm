<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<head>
  <?php echo $this->Html->script('smoothness-jquery-ui.min.css');
  //pr($this->Session->read());
  ?>
</head>

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
//pr($_SESSION);
	$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
  $currencySymbol = Configure::read('CURRENCY_TYPE');	
?>
<?php
//pr($this->request['data']);
//pr($_SESSION);die;
$kioskId = $this->request->Session()->read('kiosk_id');
 if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
	}else{
		$value = '';
	}
	//pr($this->request->query);
    extract($this->request->query);
    if(!isset($product)){$product = "";}
    if(!isset($product_code)){$product_code = "";}
    $webRoot = $this->request->webroot.'OnDemandOrders/search';
	
	//pr($_SESSION);die;
?>
<div><?php //echo $this->Session->flash(''); ?></div>
<div class="kioskProductSales index">
    <?php echo $this->Form->create(null, array('url' => $webRoot,'type' => 'get'));?>
	<fieldset>	    
	    <legend>Search</legend>
	    <table>
		<tr>
		    <td></td>
		    <td><strong>Find by category &raquo;</strong></td>
		</tr>
		<tr>		    
		    <td>
			 <div id='remote'><input class="typeahead" type = "text" value = '<?= $value ?>' name = "search_kw" placeholder = "Product Code, Product Title or Product Description" autofocus style = "width:500px;height:35px;"/></div></td>
		    <td rowspan="3"><select id='category_dropdown' name='category[]' multiple="multiple" size='6' onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select></td>		    
		</tr>
		<tr>
		    <td><h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4></td>
		</tr>
		<tr>
		    <td colspan='2'><input type='submit' name='submit' value='Search'></td>
		</tr>		
	    </table>
	</fieldset>
    <?php
	$options = array(
	    'label' => '',//Search Product
	    'div' => false,
	    'name' => 'submit1',
	    'style' => 'display:none;'
	);
    ?>
    <?php echo $this->Form->end($options);
	$session_basket = $this->request->Session()->read('on_demand_basket');
	if(!empty($session_basket)){   ?>
	  <input type="hidden" id="seesion_basket_id" value=1 />
	<?php }else{  ?>
	  <input type="hidden" id="seesion_basket_id" value=0 />
	<?php }
	?>

    <h2><?php echo __('Extra Stock Required'); ?></h2>
	<div id="error_div"></div>
    <?php echo $this->Html->link('Restore Session', array('action' => 'restore_session', $this->request->params['controller'], "new_order", 'on_demand_basket', $kioskId), array('style' => "float: right;margin-right: 170px;"));?>
    <?php echo $this->Form->create(null,array('url' => array('controller' => 'OnDemandOrders','action' => 'sell_products'))); ?>
    <div class="submit">
	  <table width = '100%'>
		<tr><td colspan='4'>
		Extra stock order should be created in the end of day. <br/>
User can add items to order anytime in a day and to check what user have added, hit “Restore Session” Link.<br/>
Once user are sure, no more extra items are required for the day; <br/> Restore Session and than submit order by hitting Create Extras Stock Order button
</br><b>Please don't add more than 500 products in extra stock order (It might timeout)</b>
	  </td></tr>
	   <tr>
		  <td style='width:30px;'><input type="submit" name='basket'    value="Add to Basket / Calculate"/></td>
		  <td style='width:5500px;'><input  type="submit" name='submit'  id="create"   value="Create Demand Order"/></td>
		  <td><input type="submit" name='checkout' value="Checkout" id="checkout_top" onclick="return checksession1();"/></td>
		  <td style='width:550px;'><input type="submit" name='empty_basket'   value="Clear the Basket"/></td>
	   </tr>
	  </table>
    </div>
	
    <table cellpadding="0" cellspacing="0">
	  <thead>
		<tr>
		  <th><?php echo $this->Paginator->sort('product_code'); ?></th>
		  <th><?php echo $this->Paginator->sort('product_id'); ?></th>
		  <th><?php echo $this->Paginator->sort('color')?></th>
		  <th>Image</th>
		  <th><?php echo $this->Paginator->sort('quantity','Current Stock'); ?></th>
		<?php    $external_sites = Configure::read('external_sites');
				$external_site_status = 0;
				$path = dirname(__FILE__);
				foreach($external_sites as $key => $val){
				  if($external_site_status == 1){
					continue;
				  }
				  if(strpos($path,$val)){
					$external_site_status = 1;
				  }
				}
				if($external_site_status == 1){
		   ?>
		   <th><?php echo $this->Paginator->sort('cost_price','Cost Price'); ?></th>
		   <?php }else{ ?>
		  <th><?php echo $this->Paginator->sort('sale_price','Sale Price'); ?></th>
		  <?php }?>
		  <th>Quantity</th>
		  
		  <th colspan=2 style="padding-left: 66px;">Remarks</th>		
		</tr>
	  </thead>
      <?php
	    $sessionBaket = $this->request->Session()->read("on_demand_basket");
        $currentPageNumber = $this->Paginator->current();
      ?>
	<tbody>
      <?php
		//pr($products);
		$groupStr = "";
		foreach ($products as $key => $product):
		  $groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
		  $discountStatus = $product->discount_status;
		  $discount = $product->discount;
		  $truncatedProduct =  \Cake\Utility\Text::truncate(
                                                                        $product->product,
                                                                        30,
                                                                        [
                                                                                'ellipsis' => '...',
                                                                                'exact' => false
                                                                        ]
                                                                );
		
		  $imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product->id.DS;
		  $imageName =  $product->image;
		  $largeImageName = 'vga_'.$product->image;
		  $absoluteImagePath = $imageDir.$imageName;
		  $imageURL = "/thumb_no-image.png";
		  $largeImageURL = $imageURL;         
		  if(@readlink($absoluteImagePath) || file_exists($absoluteImagePath)){
			$imageURL = "$siteBaseURL/files/Products/image/".$product->id."/$imageName";
			$largeImageURL = "{$siteBaseURL}/files/Products/image/".$product->id."/$largeImageName"; //rasu
		  }
                
		  $productQuantity = 1;
		  $sellingPrice = $product->selling_price;
		  $productRemarks = "";
		  $productDiscount = 0;
		  $quantityChecked = 0;
		  $disAmount = "";
		  $netAmount = "";
                
		  $checked = false;
		  if( count($sessionBaket) >= 1){
            if(array_key_exists($product->product_code,$sessionBaket)){
			  //echo "<pre>"; print_r($sessionBaket); echo "</pre>";
			  $productQuantity = $sessionBaket[$product->product_code]['quantity'];
			  $sellingPrice = $sessionBaket[$product->product_code]['selling_price'];
			  $productRemarks = $sessionBaket[$product->product_code]['remarks'];
			  $sessDiscount = $sessionBaket[$product->product_code]['discount'];
			  $disAmount = round($sellingPrice*$sessDiscount/100,2);
			  $netAmount = round($sellingPrice-$disAmount,2);
			  $productDiscount = $sessionBaket[$product->product_code]['discount'];
			  $checked = true;
            }
		  }
	?>
	<tr>
	    <td><?php
		#echo $product['Product']['product_code'];
		echo $this->Html->link($product->product_code,//rasu
                                    array('controller' => 'products','action' => 'edit', $product->id),
                                    array('escapeTitle' => false, 'title' => $product->product));
		?></td>
            <td>
            <?php
                echo $this->Html->link($truncatedProduct,
                                    array('controller' => 'products', 'action' => 'view', $product->id),
                                    array(
					  'escapeTitle' => false,
					  'title' => $product->product,
					  'id' => "tooltip_{$product->id}"
					  )
                        );
            ?>
            </td>
	    <td><?php echo $product->color;?></td>
            <td><?php
                    /*echo $this->Html->link(
                                    $this->Html->image($imageURL, array('fullBase' => false)),
                                    array('controller' => 'products','action' => 'edit', $product['Product']['id']),
                                    array('escapeTitle' => false, 'title' => $product['Product']['product'])
                            );*/
					echo $this->Html->link(
                                    $this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')), //rasu
                                    $largeImageURL,
                                    array('escapeTitle' => false, 'title' => $product->product,'class' => "group{$key}")
                            );
                    ?>
            </td>
            <td><?php echo h($product->quantity); ?>&nbsp;</td>
			<?php
			  $path = dirname(__FILE__);
			  $isboloRam = strpos($path,"mbwaheguru");
			  $fonerevive = strpos($path,"fonerevive");
			  $sell_price = 0;
			  if($isboloRam != false){
				if(!empty($product->retail_selling_price)){
				  $sell_price = $product->retail_selling_price;  
				}
				
			  }elseif($fonerevive != false){
				$sell_price = $product->selling_price/1.2;
				}else{
				$sell_price = $product->selling_price;
			  }
			  
			?>
            <td><?php echo $this->Form->input(null,array(
                                    'type' => 'hidden',
                                    'name' => "OnDemandOrders[selling_price][$key]",
                                    'value' => $sell_price,
                                    'label' => false,
                                    'style' => 'width:39px; margin-top:8px;',
				    'div'=>false,
                                    'readonly' => true,
				    'id' => "selling_price_$key"
                                    )
                            );
	    
	    echo h($sell_price); ?>&nbsp;
		
		 <?php
		   $selling_price = $product->selling_price;
			 //formula price = total*100/vat+100
			 $ans = $selling_price;
			 if(isset($vat)){
			  $ans = ($selling_price*100)/($vat+100);
			 }
			 //echo $currencySymbol.round($ans,2);echo "</br>";
			 //echo "(".$currencySymbol.$selling_price.")";
		  
		  	    
	    //echo h($product['Product']['selling_price']); ?>&nbsp; </td>
            <?php
		    echo $this->Form->input(null,array(
                                    'type' => 'hidden',
                                    'name' => "OnDemandOrders[product][$key]",
                                    'value' => $product->product
                                    )
                            );
			echo $this->Form->input(null,array(
                                    'type' => 'hidden',
                                    'name' => "OnDemandOrders[product_code][$key]",
                                    'value' => $product->product_code
                                    )
                            );
			echo $this->Form->input(null,array(
							'type' => 'hidden',
							'name' => "OnDemandOrders[p_quantity][$key]",
							'value' => $product->quantity
							)
					);
		    
//		      echo $this->Form->input(null,array(
//                                    'type' => 'hidden',
//                                    'name' => "data[KioskProductSale][selling_price][$key]",
//                                    'value' => $sellingPrice
//                                    )
//                            );
		    
		    echo $this->Form->input(null,array(
                                    'type' => 'hidden',
                                    'name' => "OnDemandOrders[p_quantity][$key]",
                                    'value' => $product->quantity
                                    )
                            );
		    
		if($discountStatus == 1){
		    $maxDiscount = $product->discount;//$discountOptions;
		    //$maxDiscount = $maxDiscount+1;
		    $allowedDiscount = array();
		    foreach($newDiscountArr as $k => $value){
			if((int)$value > $maxDiscount)break;
			$allowedDiscount[$k] = $value;
		    }
		/*    commented on 15.04.2016, added a new field below
		echo "<td style='padding-top: 10px;'>".$this->Form->input(null,array(
				    'name' => "data[KioskProductSale][discount][$key]",
				    //'options' => $allowedDiscount,
				    //'value' => $allowedDiscount,
				    'label'=> false,
				    'value' => $productDiscount,
				    'id' => "spinner_$key",
				    'div' => false,
				    'style'=> "width: 18px;"
				    )
				)."</td>";*/
		echo $this->Form->input(null,array(
				    'name' => "OnDemandOrders[discount][$key]",
				    //'options' => $allowedDiscount,
				    //'value' => $allowedDiscount,
				    'label'=> false,
				    'value' => $productDiscount,
				    'id' => "discount_$key",
				    'div' => false,
				    'style'=> "width: 18px;",
					'type' => 'hidden'
				    )
				)."
		<input type='hidden' id=\"hidden_dis_val_{$key}\" value = \"\" >";
		//<span style=\"background: skyblue;\"  id = \"hidden_disc_{$key}\" title = \"\">##</span>
		}else{
		    //echo "<td>N/A</td>";
		    
		    echo $this->Form->input(null,array(
				    'name' => "OnDemandOrders[discount][$key]",
				    'value' => 0,
				    'type' => 'hidden',
				    'label' => false
				    )
				);
		}
		    
                    echo $this->Form->input(null,array(
                                    'type' => 'hidden',
                                    'name' => "OnDemandOrders[discount_status][$key]",
                                    'value' => $discountStatus
                                    )
                            );
		    
		  
		    
		    echo $this->Form->input(null,array(
                                    'type' => 'hidden',
                                    'name' => "OnDemandOrders[product_id][$key]",
                                    'value' => $product->id
                                 )
                            );
                ?>
	    <?php echo $this->Form->input(null,array(
                                    'type' => 'hidden',
                                    //'name' => "data[KioskProductSale][quantity][$key]",
                                    'value' => $disAmount,
                                    'label' => false,
                                    'style' => 'width:45px; margin-top:8px;',
				    'id' => "disc_amnt_$key",
				    'div'=>false,
                                    'readonly' => true
                                    )
                            );?>
		<?php echo $this->Form->input(null,array(
						'type' => 'hidden',
						'label' => false,
						'name' => "OnDemandOrders[net_amount][$key]",
						'style' => 'width:45px; margin-top:8px;',
										'id' => "net_val_$key",
										 'div'=>false,
										 'value' => $netAmount
						//'readonly' => true
		)
	    );?>
            <td style="width: 70px;"><?php
	
			echo $this->Form->input(null,array(
                                    'type' => 'text',
									'id' => 'qantity',
                                    'name' => "OnDemandOrders[quantity][$key]",
                                    'value' => $productQuantity,
                                    'label' => false,
                                    'style' => 'width:39px; margin-top:8px;',
				    'div'=>false,
                                    'readonly' => false
                                    )
                            );
		    
		   echo "</td><td>";
			echo $this->Form->input(null,array(
                                    'type' => 'checkbox',
                                    'name' => "OnDemandOrders[item][$key]",
                                    'value' => $product->id,
                                    'label' => false,
                                    'style' => 'height:18px; margin-top:8px; transform:scale(1.5);',
                                    'readonly' => false,
									'id' => "checked_qtt_$key",
				    'div'=>false,
				    'checked' => $checked
                                    )
                            );	    
		    
                    ?>
		    
	    
            <td><?php
		
		    //if($product['Product']['quantity']){
			echo $this->Form->input(null,array(
                                    'type' => 'text',
                                    'name' => "OnDemandOrders[remarks][$key]",
                                    'value' => $productRemarks,
                                    'label' => false,
                                    'style' => 'width:80px;',
                                    'readonly' => false
                                    )
                            );
		    //} ?>
            </td>	    
	</tr>
        <?php endforeach; ?>
	<?php echo $this->Form->input(null,array('type' => 'hidden','value' => $currentPageNumber,'name' => 'current_page'));?>
	</tbody>
    </table>
    <script type="text/javascript">
    <?php
     foreach ($products as $key => $product):
     $string = preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($product->description));
     if(empty($string)){
      $string = $product['Product']['product'];
      //htmlspecialchars($str, ENT_NOQUOTES, "UTF-8")
     }
      echo "jQuery('#tooltip_{$product->id}').tooltip({content:\"".htmlspecialchars($string)."\",track:true});";
     endforeach;
    ?>
    </script>
    <div class="submit">
	 <table>
	  <tr>
	    <td style='width:30px;'><input type="submit" name='basket'    value="Add to Basket / Calculate"/></td>
		 
		<td style='width:5550px;'><?php		
        $options1 = array('label' => 'Create Demand Order','div' => false,'name' => 'submit'   );		
        echo $this->Form->end($options1);		
    ?></td>
		<td><input type="submit" name='checkout' value="Checkout" id="checkout_bottom" onclick="return checksession2();"/></td>
		<td style='width:50px;'> <input type="submit" name='empty_basket'    value="Clear the Basket"/></td>
	  </tr>
	 </table> 
         
       
   
    </div>
    
    <div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>

<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>	
        <li><?php echo $this->Html->link(__('View Sale'), array('action' => 'index')); ?> </li>
	<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'stock', 'action' => 'index')); ?> </li>
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
    url: "/OnDemandOrders/admin_data?category=%CID&search=%QUERY",
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
	  foreach ($products as $key => $product){
		$discountStatus = $product->discount_status;
		if($discountStatus == 1){
		  $maxDiscount = $product->discount;
	?>
		  /*$("#spinner_<?php echo $key;?>").spinner({
			min: 0, max: <?php echo $maxDiscount;?>,
			stop: function (){
			  var sellPrice = $("#selling_price_<?php echo $key;?>").val();
			  var discount = $(this).spinner("value");
			  var disValue = sellPrice*discount/100;
			  var netVal = sellPrice - disValue;
			  $("#net_val_<?php echo $key;?>").val(netVal);
			  $("#disc_amnt_<?php echo $key;?>").val(disValue);
			}
		 });*/
		  
		  $("<?php echo "#net_val_".$key;?>").blur(function(){
			//checking if the input value is lesser than the allowed discount
			if (parseFloat($(this).val()) < parseFloat($('#hidden_dis_val_' + <?php echo $key;?>).val())) {
			  $(this).val("");
			  $('#disc_amnt_' + <?php echo $key;?>).val("");
			  $(this).val("");
			  $('#checked_qtt_' + <?php echo $key;?>).attr('checked', false);
			  $('#error_div').html('Price cannot be less than the minimum allowed price for <?php echo $product->product;?>!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			  alert('Price cannot be less than the minimum allowed price for <?php echo $product->product;?>!');
            } else if (parseFloat($(this).val()) < parseFloat($('#selling_price_' + <?php echo $key;?>).val())) {
			  //if value is lesser than the selling price, then populating the discount amount and assigning discount percent in hidden
			  var discountedAmount = parseFloat($('#selling_price_' + <?php echo $key;?>).val())-parseFloat($(this).val());
			  $('#disc_amnt_' + <?php echo $key;?>).val(parseFloat(discountedAmount).toFixed(2));
			  var discountPercentage = discountedAmount/parseFloat($('#selling_price_' + <?php echo $key;?>).val())*100;
			  $('#discount_' + <?php echo $key;?>).val(parseFloat(discountPercentage));  //.toFixed(2)
			} else if (parseFloat($(this).val()) > parseFloat($('#selling_price_' + <?php echo $key;?>).val())) {
              //if value is greater than the selling price
			  $('#disc_amnt_' + <?php echo $key;?>).val('0');
			  $('#discount_' + <?php echo $key;?>).val('0');
			  $('#selling_price_' + <?php echo $key;?>).val(parseFloat($(this).val()));
            }
		  });
		  
		  //written on 15.04.2016
		  //below is for tool tip
		  $(function() {
			$( '#hidden_disc_' + <?php echo $key;?> ).tooltip();
		  });
<?php
		}
	  }
?>
   
	$(document).ready(function(){
	  var status = $('#seesion_basket_id').val();
	  if (status == 1) {
		$('#create').show();
	  }else{
		$('#create').hide();
	  }
<?php
	  echo $groupStr;
	  foreach ($products as $key => $product){
		//for tool tip written on 15.04.2016
		$disPercent = $product->discount;
		$salePrice = $product->selling_price;
		$discountValue = $salePrice * $disPercent/100;
		$minPrice = round($salePrice-$discountValue,2);
		//till here
		
		$discountStatus = $product->discount_status;
		if($discountStatus == 1){
		  $maxDiscount = $product->discount;
?>
		  var discount = $("#spinner_<?php echo $key;?>").val();
		  var sellPrice = $("#selling_price_<?php echo $key;?>").val();
		  var disValue = sellPrice*discount/100;
		  var netVal = sellPrice - disValue;
		  //commented on 15.04.2016
		  //$("#net_val_<?php echo $key;?>").val(netVal);
		  //$("#disc_amnt_<?php echo $key;?>").val(disValue);
		  
		  //written on 15.04.2016
		  //sending below title for tool tip
		  document.getElementById('hidden_disc_' + <?php echo $key;?>).title = '<?php echo "Minimum price: ".$minPrice;?>';
		  //assigning the bellow values for comparing purpose on key up for box discount amount
		  
		  <?php echo "document.getElementById('hidden_dis_val_{$key}').value = '{$minPrice}';\n";?>
<?php
		}
     }
?>
   });
   
   function checksession1 ()
     {
      <?php if(empty($sessionBaket)){?>
      alert("Please add items to the session first!");
      return false;
     <?php } ?>
     }
     
     function checksession2 ()
     {
      <?php if(empty($sessionBaket)){?>
      alert("Please add items to the session first!");
      return false;
     <?php } ?>
     }
	 
	 $(function() {
			//-------------------------
	
	$("#check_existing").click(function() {
		var custEmail = $("#cust_email").val();
		var cutomerURL = $("#check_existing").attr('rel') + '?cust_email=' + escape(custEmail);
		//------------
		$.blockUI({ message: 'Just a moment...' });
		$.ajax({
			type: 'get',
			url: cutomerURL,
			beforeSend: function(xhr) {
			    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				$.unblockUI();
				
				var obj = jQuery.parseJSON( response);
				$("#Customerfname").val(obj.fname);
				$("#Customerlname").val(obj.lname);
				$("#customer_mobile").val(obj.mobile);
				$("#Customeremail").val(obj.email);
				$("#CustomerZip").val(obj.zip);
				$("#CustomerAddress1").val(obj.address_1);
				$("#CustomerAddress2").val(obj.address_2);
				$("#CustomerCity").val(obj.city);
				$("#CustomerState").val(obj.state);
				
				if (response) {
					if (obj.ErrorNumber == 0) {
						
					}					
				}
			},
			error: function(e) {
			    $.unblockUI();
			    alert("Error:"+response);
			    alert("An error occurred: " + e.responseText.message);
			    console.log(e);
			}
		});
		//------------
	});
	
	//-------------------------
	  });
	 
  </script>
<script>
   $(function() {
			//-------------------------
	
	$("#clear_customer").click(function() {
		var cutomerURL = $("#clear_customer").attr('rel');
		//------------
		$.blockUI({ message: 'Just a moment...' });
		$.ajax({
			type: 'get',
			url: cutomerURL,
			beforeSend: function(xhr) {
			    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				$.unblockUI();
				
				var obj = jQuery.parseJSON( response);
				if (obj  == 'success') {
                $("#Customerfname").val('');
				$("#Customerlname").val('');
				$("#customer_mobile").val('');
				$("#Customeremail").val('');
				$("#CustomerZip").val('');
				$("#CustomerAddress1").val('');
				$("#CustomerAddress2").val('');
				$("#CustomerCity").val('');
				$("#CustomerState").val('');
                }else{
					//do nothing  
				}
			},
			error: function(e) {
			    $.unblockUI();
			    alert("Error:"+response);
			    alert("An error occurred: " + e.responseText.message);
			    console.log(e);
			}
		});
		//------------
	});
	
	//-------------------------
	  });
  
</script>
<script>
  $("#qantity").keydown(function (event) {  
  if (event.shiftKey == true) {event.preventDefault();}
  if ((event.keyCode >= 48 && event.keyCode <= 57) ||
  (event.keyCode >= 96 && event.keyCode <= 105) ||
  event.keyCode == 8 || event.keyCode == 9 ||
  event.keyCode == 37 || event.keyCode == 39 ||
  event.keyCode == 46 || event.keyCode == 183) {
   ;
   //event.keyCode == 190 || event.keyCode == 110 for dots
   //48-57 => 0..9
   //8 => Backspace; 9 => tab 
  } else {
   event.preventDefault();
  }
  //if($(this).val().indexOf('.') !== -1 && event.keyCode == 190){event.preventDefault();}
        });
</script>