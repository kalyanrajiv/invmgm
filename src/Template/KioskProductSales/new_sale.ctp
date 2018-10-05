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
	$specialInvoice = $this->request->Session()->read('special_invoice');
	if(empty($specialInvoice)){
	 $specialInvoice = 0;
	}
	$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
?>
<?php
 if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
	}else{
		$value = '';
	}   
    extract($this->request->query);
    if(!isset($product)){$product = "";}
    if(!isset($product_code)){$product_code = "";}
    
      $business = $customerAccountDetails['business'];
      $customerId = $customerAccountDetails['id'];
      $fName = $customerAccountDetails['fname'];
      $lName = $customerAccountDetails['lname'];
      
    $webRoot = $this->request->webroot."kiosk-product-sales/search_new_sale/$customerId";
?>
<div><?php //echo $this->Session->flash(''); ?></div>
<div id="payment_div"><?php echo $this->Element('kiosk_product_sale/sale_payment');?></div>
<div class="kioskProductSales index" id="main_div">
    <?php echo $this->Form->create(null, array('url' => $webRoot,'type' => 'get'));?>
	<fieldset>	    
	    <legend>Search</legend>
	    <table>
		<tr>
		    <td></td>
		    <td><strong>Find by category &raquo;</strong></td>
		</tr>
		<tr>		    
		    <td><div id='remote'><input class="typeahead" type = "text" value = '<?= $value ?>' name = "search_kw" placeholder = "Product Code, Product Title or Product Description" style = "width:500px;height:25px;" autofocus/></div></td>
		    <td rowspan="3"><select id='category_dropdown' name='category[]' multiple="multiple" size='6'><?php echo $categories;?></select></td>		    
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
    <?php
	echo $this->Form->submit("search",$options);
	echo $this->Form->end();
    $kiosk_id = $this->request->Session()->read('kiosk_id');
    if(array_key_exists('kiosk_id', $this->request->Session()->read()) && (int)$this->request->Session()->read('kiosk_id')){
     $kiosk_id = $this->request->Session()->read('kiosk_id');
    }else{
     $kiosk_id = 10000;
    }
    
    ?>

    <h2><?php echo __('New Sale'); ?></h2>
	<div id="error_div"></div>
    <?php
    $returnAction = $customerId;
    
    echo $this->Html->link('Restore Session', array('action' => 'restore_session', 'KioskProductSales', 'new_sale', 'new_sale_basket', $kiosk_id, $returnAction), array('style' => "float: right;margin-right: 170px;"));?>
    <?php echo $this->Form->create(null,array('url' => array('controller' => 'kiosk_product_sales','action' => 'products_selling',$customerId),'autocomplete'=>"off"));
		$loggedInUser = $this->request->session()->read('Auth.User.username');
 if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering
		?>
 <table>
	<tr>
	 <td style="width: 222px;">Quotation?</td>
	 <td style="width: 77px;">Yes<input type='radio' name='special_invoice' value='1' <?=($specialInvoice==1) ? "checked" : "" ; ?>/></td>
	 <td>No<input type='radio' name='special_invoice' value='0' <?=($specialInvoice==0) ? "checked" : "" ; ?>/></td>
	</tr>
 </table>
    <?php
		}
	echo $this->Form->input('null',array('type'=>'hidden','name'=>'customerId','value'=>$customerId));
    ?>
    
    <?php
	    $receiptCheck = '';
	    $bulkDiscount = '';
	    $sessionBaket = $this->request->Session()->read("new_sale_basket");
	    $receiptCheck = $this->request->Session()->read('receipt_required');
	    $bulkDiscount = $this->request->Session()->read('new_sale_bulk_discount');
			?>
    <table cellspacing='2' cellpadding='2' width='100%'>
            <tr>
		<td colspan='2'>Do you need customer receipt?</td>
                <td>Yes<input type='radio' name='receipt_required' value='1' <?php if($receiptCheck == 1){echo "CHECKED";}?>/></td>
                <td>No<input type='radio' name='receipt_required' value='0' <?php if($receiptCheck == 0){echo "CHECKED";}?>/></td>
	    </tr>
	  <tr>
          <td style="width: 100px"><strong>Customer Id</strong></td>
          <td style="width: 115px">
              <?php echo $customerId;?>
          </td>
	  <?php if(empty($business)){?>
	     <td style="width: 100px"><strong>Name</strong></td>
	     <td>
		 <?php echo $fName." ".$lName;?>
	     </td>
	  <?php }else{?>
	     <td style="width: 100px"><strong>Business</strong></td>
	     <td >
		 <?php echo $business;?>
	     </td>
	  <?php } ?>
	  </tr>
    </table>
    <div class="submit">
	 <table width='100%'>
	  <tr>
	   <td style='width:195px'>
	   <input type="submit" name='basket' value="Add to Basket / Calculate"/>
	  </td>
	   
	  <td style='width:90px'>
	   <input type="submit" name='check_out' value="Checkout"/>
	  </td>
	  <?php if(array_key_exists('new_sale_basket',$_SESSION) && !empty($_SESSION['new_sale_basket'])){?>
	  <td style='width:90px'><input  type="submit" name='submit' id="show_payment" value="Sell"/></td>
	  <?php } ?>
	  <td style='width:200px'><strong>Bulk Discount</strong>
	  <input  type="text" name='bulk_discount' style="width: 24%" value='<?php if(!empty($bulkDiscount)){echo $bulkDiscount;}else{echo "";}?>'/></td>
	  <td><input type="submit" name='empty_basket' value="Clear the Basket"/></td></tr>
	 </table>
	
    </div>
    <div class="paging">
    <?php
        echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
        echo $this->Paginator->numbers(array('separator' => ''));
        echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
    ?>
    </div>
    <table cellpadding="0" cellspacing="0">
        <thead>
        <tr>
	    <th><?php echo $this->Paginator->sort('product_code'); ?></th>
            <th><?php echo $this->Paginator->sort('product_id'); ?></th>
	    <th><?php echo $this->Paginator->sort('category_id'); ?></th>
	    <th><?php echo $this->Paginator->sort('color')?></th>
            <th>Image</th>
            <th><?php echo $this->Paginator->sort('quantity','Current Stock'); ?></th>
            <th><?php echo $this->Paginator->sort('sale_price','Sale Price'); ?></th>            
	    <th>Discount</th>
	    <th style="width: 60px;">Discount Amount</th>
	    <th style="width: 60px;">Net Value</th>
            <th>Quantity</th>			
        </tr>
        </thead>
        <?php
	    $sessionBaket = $this->request->Session()->read("new_sale_basket");
            $currentPageNumber = $this->Paginator->current();
        ?>
	<tbody>
  <?php
  $groupStr = "";
  foreach ($products as $key => $product):
   $groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
  ?>
	<?php if(array_key_exists($product->category_id,$categoryName)){
					$catName = $categoryName[$product->category_id];
				}else{
					$catName = "--";
				}
				#pr($product['Product']['quantity']);die;?>
	<?php 
		$discountStatus = $product->discount_status;
		$discount = $product->discount;
		$truncatedProduct =	\Cake\Utility\Text::truncate(
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
		    $imageURL = "{$siteBaseURL}/files/Products/image/".$product->id."/$imageName"; //rasu
			$largeImageURL = "{$siteBaseURL}/files/Products/image/".$product->id."/$largeImageName"; //rasu
		}
		//echo "<a href='$largeImageURL'>$largeImageURL</a>";
                
		$productQuantity = 1;
		$sellingPrice = $product->selling_price;
		$productRemarks = "";
		$productDiscount = 0;
		$quantityChecked = 0;
		$disAmount = "";
		$netAmount = "";
                
		$checked = false;
		if( count($sessionBaket) >= 1){
                    if(array_key_exists($product->id,$sessionBaket)){
			#echo "<pre>"; print_r($sessionBaket); echo "</pre>";
                        $productQuantity = $sessionBaket[$product->id]['quantity'];
                        $sellingPrice = $sessionBaket[$product->id]['selling_price'];
                        $productRemarks = $sessionBaket[$product->id]['remarks'];
						$sessDiscount = $sessionBaket[$product->id]['discount'];
						$disAmount = round($sellingPrice*$sessDiscount/100,2);
						//$netAmount = round($sellingPrice-$disAmount,2);
						$netAmount = round($sessionBaket[$product->id]['net_amount'],2);//changed on 17th may 2016
						
			$productDiscount = $sessionBaket[$product->id]['discount'];
			$checked = true;
                    }
		}
	?>
	<tr>
	    <td><?php echo $this->Html->link($product->product_code,//rasu
                                    array('controller' => 'products','action' => 'edit', $product->id),
                                    array('escapeTitle' => false, 'title' => $product->product));?></td>
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
	    <td><?php echo $catName;?></td>
	    <td><?php echo $product->color;?></td>
            <td><?php
                    /*echo $this->Html->link(
                                    $this->Html->image($imageURL, array('fullBase' => false)), //rasu
                                    array('controller' => 'products','action' => 'edit', $product['Product']['id']),
                                    array('escapeTitle' => false, 'title' => $product['Product']['product'])
                            );*/
					//echo $this->Html->image($imageURL, array('fullBase' => false));
					echo $this->Html->link(
                                    $this->Html->image($imageURL, array('fullBase' => false,'width' => '100px','height' => '100px')), //rasu
                                    $largeImageURL,
                                    array('escapeTitle' => false, 'title' => $product->product,'class' => "group{$key}")
                            );
                    ?>
            </td>
            <td><?php echo h($product->quantity); ?>&nbsp;</td>
            <td><?php
			//formula for price without vat price = total*100/vat+100
			$numerator = $product->selling_price*100;
			$denominator = $vat+100;
			$priceWithoutVat = $numerator/$denominator;
			$priceWithoutVat = number_format($priceWithoutVat,2);
			
			echo $priceWithoutVat."<br/>";
			echo "<input type=\"hidden\" id=\"price_without_vat_$key\" value=\"$priceWithoutVat\" name =\"KioskProductSale[price_without_vat][$key]\">";
			echo $this->Form->input(null,array(
                                    'type' => 'text',
                                     'name' => "KioskProductSale[selling_price][$key]",
                                    'value' => $sellingPrice,
                                    'label' => false,
                                     'style' => 'width:39px; margin-top:8px;',
 				    'div'=>false,
                                    'readonly' => true,
 				    'id' => "selling_price_$key"
                                    )
                             );
			 //echo h($product['Product']['selling_price']);
			?>
			
			&nbsp;
            <?php
		    echo $this->Form->input(null,array(
                                    'type' => 'hidden',
                                    'name' => "KioskProductSale[product][$key]",
                                    'value' => $product->product
                                    )
                            );
                    echo $this->Form->input(null,array(
                                    'type' => 'hidden',
                                    'name' => "KioskProductSale[p_quantity][$key]",
                                    'value' => $product->quantity,
									'id' => "hidden_qty_$key"
                                    )
                            );
		    
		      echo $this->Form->input(null,array(
                                    'type' => 'hidden',
                                    'name' => "KioskProductSale[selling_price][$key]",
                                    'value' => $sellingPrice
                                    )
                            );
		    
		    echo $this->Form->input(null,array(
                                    'type' => 'hidden',
                                    'name' => "KioskProductSale[p_quantity][$key]",
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
		   // pr($productDiscount);
		   
			echo "<td style='padding-top: 10px;'>".$this->Form->input(null,array(
				    'name' => "KioskProductSale[discount][$key]",
				    //'options' => $allowedDiscount,
				    //'value' => $allowedDiscount,
				    'label'=> false,
				    'value' => $productDiscount,
				    'id' => "discount_$key",
				    'div' => false,
				    'style'=> "width: 18px;",
					'type' => 'hidden'
				    )
				)."<span style=\"background: skyblue;\"  id = \"hidden_disc_{$key}\" title = \"\">##</span>
		<input type='hidden' id=\"hidden_dis_val_{$key}\" value = \"\" ></td>";
		}else{
		    echo "<td>N/A</td>";
		    	    echo $this->Form->input(null,array(
				    'name' => "KioskProductSale[discount][$key]",
				    'value' => 0,
				    'type' => 'hidden',
				    'label' => false
				    )
				);
		}
		      echo $this->Form->input(null,array(
                                    'type' => 'hidden',
                                    'name' => "KioskProductSale[discount_status][$key]",
                                    'value' => $discountStatus
                                    )
                            );
		    
		  
		    
		    echo $this->Form->input(null,array(
                                    'type' => 'hidden',
                                    'name' => "KioskProductSale[product_id][$key]",
                                    'value' => $product->id
                                 )
                            );
                ?>
            </td>
	    <td><?php echo $this->Form->input(null,array(
			'type' => 'text',
			'label' => false,
			'value' => $disAmount,
			'style' => 'width:45px; margin-top:8px;',
							'id' => "disc_amnt_$key",
							 'div'=>false,
			'readonly' => true
			)
	    );?></td>
	    <td><?php echo $this->Form->input(null,array(
						'type' => 'text',
						'label' => false,
						'name' => "KioskProductSale[net_amount][$key]",
						'style' => 'width:45px; margin-top:8px;',
										'id' => "net_val_$key",
										 'div'=>false,
										 'value' => $netAmount,
						'readonly' => false
		)
	    );?></td>
            <td><?php
		    if($product->quantity){
			echo $this->Form->input(null,array(
                                    'type' => 'text',
                                    'name' => "KioskProductSale[quantity][$key]",
                                    'value' => $productQuantity,
                                    'label' => false,
                                    'style' => 'width:36px; margin-top:8px;',
				    'div'=>false,
                                    'readonly' => false,
				    'id'=>"sale_quantity_$key"
                                    )
                            );
		    
		   echo "</td><td>";
			echo $this->Form->input(null,array(
                                    'type' => 'checkbox',
                                    'name' => "KioskProductSale[item][$key]",
                                    'value' => $product->id,
                                    'label' => false,
                                    'style' => 'height:18px; margin-top:8px; transform:scale(1.5);',
                                    'readonly' => false,
									'id' => "checked_qtt_$key",
				    'div'=>false,
				    'checked' => $checked
                                    )
                            );
		    }		    
		    
                    ?>
		    
            </td>
	    
            <td><?php
		
		    if($product->quantity){
			echo $this->Form->input(null,array(
                                    'type' => 'hidden',
                                    'name' => "KioskProductSale[remarks][$key]",
                                    'value' => $productRemarks,
                                    'label' => false,
                                    'style' => 'width:80px;',
                                    'readonly' => false
                                    )
                            );
		    }?>
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
      $string = $product->product;
      //htmlspecialchars($str, ENT_NOQUOTES, "UTF-8")
     }
      echo "jQuery('#tooltip_{$product->id}').tooltip({content:\"".htmlspecialchars($string)."\",track:true});";
     endforeach;
    ?>
    </script>
    <div class="submit">
        <input type="submit" name='basket' value="Add to Basket / Calculate"/>
        <input type="submit" name='empty_basket' value="Clear the Basket"/>
	<input type="submit" name='check_out' value="Checkout"/>
    <?php		
       // $options1 = array('label' => 'Sell','div' => false,'name' => 'submit');
		//echo $this->Form->submit("Sell",$options1);
        echo $this->Form->end();		
    ?>
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
 <script>
	$("input[id*='sale_quantity_']").keydown(function (event) {
		
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||  event.keyCode == 183
		) { //event.keyCode == 110
			;
			//48-57 => 0..9
			//8 => Backspace; 9 => tab
			//event.keyCode == 46 for dot
			//event.keyCode == 190 for dot
		} else {
			event.preventDefault();
		}
		
		if($(this).val().indexOf('.') !== -1 && event.keyCode == 190){event.preventDefault();}
        });
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
	 foreach ($products as $key => $product){
	  ?>
	  $("<?php echo "#sale_quantity_".$key;?>").blur(function(){
	    var org_qty = parseFloat($("<?php echo "#hidden_qty_".$key;?>").val());
		var req_qty = parseFloat($("<?php echo "#sale_quantity_".$key;?>").val());
		if (req_qty > org_qty) {
            alert("Not sufficient Quantity.Available Quantity: "+org_qty);
			$("<?php echo "#sale_quantity_".$key;?>").val(org_qty);
			return false;
        }
	  });
	    <?php
     $discountStatus = $product->discount_status;
     if($discountStatus == 1){
      $maxDiscount = $product->discount;
	 ?>
	  /*$("#spinner_<?php echo $key;?>").spinner({
	   min: 0, max: <?php echo $maxDiscount;?>,
	   stop: function ()
	   {
		var sellPrice = $("#selling_price_<?php echo $key;?>").val();
		var discount = $(this).spinner("value");
		var disValue = sellPrice*discount/100;
		var netVal = sellPrice-disValue;
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
	   } else if (parseFloat($(this).val()) < parseFloat($('#price_without_vat_' + <?php echo $key;?>).val())) {
		 //if value is lesser than the selling price without vat, then populating the discount amount and assigning discount percent in hidden
		 var discountedAmount = parseFloat($('#price_without_vat_' + <?php echo $key;?>).val())-parseFloat($(this).val());
		 $('#disc_amnt_' + <?php echo $key;?>).val(parseFloat(discountedAmount).toFixed(2));
		 var discountPercentage = discountedAmount/parseFloat($('#price_without_vat_' + <?php echo $key;?>).val())*100;
		 $('#discount_' + <?php echo $key;?>).val(parseFloat(discountPercentage).toFixed(2));
	   } else if (parseFloat($(this).val()) >= parseFloat($('#price_without_vat_' + <?php echo $key;?>).val())) {
              //if value is greater than the selling price
			  $('#disc_amnt_' + <?php echo $key;?>).val('0');
			  $('#discount_' + <?php echo $key;?>).val('0');
			  //$('#selling_price_' + <?php echo $key;?>).val(parseFloat($(this).val()));
       }
	 });
	 
	 //below is for tool tip
	  $(function() {
		$( '#hidden_disc_' + <?php echo $key;?> ).tooltip();
	  });
     <?php }
	 else{
	  ?>
	  $("<?php echo "#net_val_".$key;?>").blur(function(){
	   //checking if the input value is lesser than the allowed discount
	   if (parseFloat($(this).val()) < parseFloat($('#price_without_vat_' + <?php echo $key;?>).val())) {
		 $(this).val("");
		 $('#disc_amnt_' + <?php echo $key;?>).val("");
		 $(this).val("");
		 $('#checked_qtt_' + <?php echo $key;?>).attr('checked', false);
		 $('#error_div').html('Price cannot be less than the minimum allowed price for <?php echo $product->product;?>!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		 alert('Price cannot be less than the minimum allowed price for <?php echo $product->product;?>!');
	   } else if (parseFloat($(this).val()) < parseFloat($('#price_without_vat_' + <?php echo $key;?>).val())) {
		 //if value is lesser than the selling price without vat, then populating the discount amount and assigning discount percent in hidden
		 var discountedAmount = parseFloat($('#price_without_vat_' + <?php echo $key;?>).val())-parseFloat($(this).val());
		 $('#disc_amnt_' + <?php echo $key;?>).val(parseFloat(discountedAmount).toFixed(2));
		 var discountPercentage = discountedAmount/parseFloat($('#price_without_vat_' + <?php echo $key;?>).val())*100;
		 $('#discount_' + <?php echo $key;?>).val(parseFloat(discountPercentage).toFixed(2));
	   } else if (parseFloat($(this).val()) >= parseFloat($('#price_without_vat_' + <?php echo $key;?>).val())) {
              //if value is greater than the selling price
			  $('#disc_amnt_' + <?php echo $key;?>).val('0');
			  $('#discount_' + <?php echo $key;?>).val('0');
			  //$('#selling_price_' + <?php echo $key;?>).val(parseFloat($(this).val()));
       }
	 });
	  <?php
	 }
    }
     ?>
   
   $(document).ready(function(){
	 
     <?php
	  echo $groupStr;
	 foreach ($products as $key => $product){
     $discountStatus = $product->discount_status;
      if($discountStatus == 1){
      $maxDiscount = $product->discount;
	  $salePrice = $product->selling_price;
	  $discountValue = $salePrice * $maxDiscount/100;
	  $minPrice = round($salePrice-$discountValue,2);
	  ?>
	 var discount = <?=$maxDiscount;?>;
	 var sellPrice = $("#selling_price_<?php echo $key;?>").val();
	 var priceWithoutVat = $("#price_without_vat_<?php echo $key;?>").val();
	 priceWithoutVat = parseFloat(priceWithoutVat).toFixed(2);
	 var disValue = priceWithoutVat*discount/100;
	 var netVal = priceWithoutVat-disValue;
	 netVal = netVal.toFixed(2);
	 //$("#net_val_<?php echo $key;?>").val(netVal);//this needs to be commented
	 //$("#disc_amnt_<?php echo $key;?>").val(disValue);//this needs to be commented
	 
	 //written on 15.04.2016
	 //sending below title for tool tip
	 document.getElementById('hidden_disc_' + <?php echo $key;?>).title = '<?php echo "Minimum price: ";?> ' + netVal;
	 //assigning the bellow values for comparing purpose on key up for box discount amount
	 
	 <?php echo "document.getElementById('hidden_dis_val_{$key}').value = netVal;";?>
<?php }
     }
     ?>
   });
  </script>
<script>
 $(document).ready(function(){
  $('#payment_div').hide();
 });
 $(document).on('click','#show_payment',function(){
  $('#full_or_part_1').prop('checked', true);
  $('#full_or_part_2').removeAttr("checked");
  $('#payment_div').show();
  $('#main_div').hide();
  return false;
  });
</script>