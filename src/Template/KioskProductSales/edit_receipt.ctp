<?php
	//pr($_SESSION);die;
	$bulkDiscountSession = $this->request->Session()->read('BulkDiscount');
	$receiptRequiredSession = $this->request->Session()->read('receipt_required');
	if(!empty($customerAccountDetails)){
		$business = $customerAccountDetails['business'];
		$customerId = $customerAccountDetails['id'];
		$fName = $customerAccountDetails['fname'];
		$lName = $customerAccountDetails['lname'];
	}else{
		$business =  $fName = $lName = "";
		$customerId = "";
	}
	$orderId = $orderDetails['id'];
	//$rootURL = Router::url('/', true);
?>
<style>
    #remote .tt-dropdown-menu {max-height: 250px;overflow-y: auto;}
    #remote .twitter-typehead {max-height: 250px;overflow-y: auto;}
    .tt-dataset, .tt-dataset-product {max-height: 250px;overflow-y: auto;}
    .row_hover:hover{color:blue;background-color:yellow;}
</style>

<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>	
        <li><?php echo  $this->Html->link(__('New Customer'), array('controller'=>'customers','action' => 'add'),array('style'=>'width: 92px;text-decoration: none;')); ?> </li>
	<li><?php echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index'),array('style'=>"width: 92px;text-decoration: none;")); ?> </li>
    </ul>
</div>
<div><?php //echo $this->request->Session()->flash(''); ?></div>
<div class="kioskProductSales index">
    <?php
		if(empty($business)){
			$customerLink = $this->Html->link($fName." ".$lName, array(
														'controller' => 'customers',
														'action' => 'view', $customerId
														), array('style' =>"text-decoration: none;"));
		}else{
			$customerLink =  $this->Html->link($business,array('controller'=>'customers','action'=>'view',$customerId),array('style'=>"text-decoration: none;"));
	   }
	   echo "<h3>Edit Invoice (<span style='font-size: 17px; font-weight: normal'>$customerLink</span>)</h3>";
	?>
	<div class="actions">
<?php
		$value = '';
		if(!empty($this->request->query['search_kw'])){$value = $this->request->query['search_kw'];} 
		extract($this->request->query);
		if(!isset($product)){$product = "";}
		if(!isset($product_code)){$product_code = "";}
		$webRoot = $this->request->webroot."kiosk-product-sales/search_edit_receipt/$orderId";
		
		//Start: Product search form
		echo $this->Form->create(null, array('url' => $webRoot,'type' => 'get'));
?>
		<fieldset>
			<legend>Search</legend>
			<table style="width: 862px;">
				<tr>
					<td></td>
					<td><strong>Find by category &raquo;</strong></td>
				</tr>
				<tr>		    
					<td><div id='remote'><input type = "text" value = '<?= $value ?>' name = "search_kw" placeholder = "Product Code,Title or Description" style = "width:300px;height:25px;" class="typeahead"/></div></td>
					<td rowspan='3' align='right'><select id='category_dropdown' name='category[]' multiple="multiple" size='6' onchange='update_hidden();' style = 'width:520px;'><option value="0">All</option><?php echo $categories;?></select></td>
				</tr>
				<tr>
					<td><h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4></td>
				</tr>
				<tr>
					<td colspan='2'><input type='submit' name='search' value='Search'</td>
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
		echo $this->Form->end($options);
		//End: Product search form
	?>
	</div>
<?php
	echo $this->Form->create(null, array('url' => array('controller' => 'kiosk_product_sales',
														'action' => 'save_updated_receipt'),'autocomplete'=>'off'));
		echo $this->Form->input('null',array('type' => 'hidden', 'name' => 'customerId','value' => $customerId));
		echo $this->Form->input('null',array('type' => 'hidden', 'name' => 'receiptId','value' => $orderId));
		if($oldBlkDiscount){
			echo $this->Form->input('null',array('type' => 'hidden', 'name' => 'bulk_discount', 'value' => $oldBlkDiscount));   
		}
    
		$receiptCheck ='';
		//By default oldBasket would work, as the values will get filled through database, once products are added, basket will work
		if(array_key_exists('Basket', $this->request->Session()->read())){
			$sessionBaket = $this->request->Session()->read("Basket");
		}
	   
		//once new items are added, bulk discount in session would work, else it will pick from the database through old session
	    if( array_key_exists('BulkDiscount', $this->request->Session()->read())){
			   $bulkDiscount = $bulkDiscountSession;
	    }
	    
	    if(count($receiptRequiredSession) >= 1){
			if( array_key_exists('receipt_required', $this->request->Session()->read())){
				$receiptCheck = $receiptRequiredSession;
			} 
	    }
	?>
    <table cellspacing='2' cellpadding='2' width='100%'>
        <tr>
			<td colspan='2'>Do you want to send email to the customer?</td>
			<td>Yes<input type='radio' name='receipt_required' value='1' <?php if($receiptCheck == 1){echo "CHECKED";}?>/></td>
			<td>No<input type='radio' name='receipt_required' value='0' <?php if($receiptCheck == 0){echo "CHECKED";}?>/></td>
	    </tr>
    </table>
    <div class="submit">
        <input type="submit" name='basket' value="Add to Basket / Calculate"/>
        <input type="submit" name='empty_basket' value="Clear the Basket"/>
		<input  type="submit" name='submit' value="Update Invoice"/>
		<strong>Bulk Discount</strong>
	<?php
		if($oldBlkDiscount){
			if(array_key_exists("BulkDiscount",$_SESSION)){
				$oldBlkDiscount = $_SESSION['BulkDiscount'];
			}
			echo "<input type = 'text' value = '$oldBlkDiscount' name = 'bulk_discount' style='width: 22px;'>"."%";
			//echo "<strong>$oldBlkDiscount%</strong>";
		}else{
			if(array_key_exists("BulkDiscount",$_SESSION)){
				$oldBlkDiscount = $_SESSION['BulkDiscount'];
			}else{
				$oldBlkDiscount = 0;
			}
			echo "<input type = 'text' name = 'bulk_discount' value = '$oldBlkDiscount' style='width: 22px;'>"."%";
			//echo "<strong>0%</strong>";	
		}
	?>
		 <input type="submit" name='check_out' value="Checkout" style="margin-left: 174px;"/>
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
				<th><?php echo $this->Paginator->sort('id','Product1'); ?></th>
				<th>Category</th>
				<th><?php echo $this->Paginator->sort('color')?></th>
				<th>Image</th>
				<th><?php echo $this->Paginator->sort('quantity','Current Stock'); ?></th>
				<th><?php echo $this->Paginator->sort('selling_price','Sale Price'); ?></th>            
				<th>Discount</th>
				<th>Discnt<br/>Amount</th>
				<th>Net Value</th>
				<th>Quantity</th>			
			</tr>
        </thead>
        <?php
			$sessionBaket = array();
			if(array_key_exists('Basket',$this->request->Session()->read())){
				$sessionBaket = $this->request->Session()->read("Basket");
			}
            $currentPageNumber = $this->Paginator->current();
        ?>
		<tbody>
	
    <?php
	$hiddenPageNum = ""; // sourabh
    //pr($products);die;
		foreach ($products as $key => $product){
			//pr($product['Product']['discount']);die;//pr($sessionBaket);
			$prodCat = $product->category_id;
			$catTitle = "";
			if(array_key_exists($prodCat,$categoryList)){
				$catTitle = $categoryList[$prodCat];			
			}
			$discountStatus = $product->discount_status;
			$productDiscount = 0;
			
			if($discountStatus == 1){
				$maxDiscount = $product->discount;//$discountOptions;
				//$maxDiscount = $maxDiscount+1;
				$allowedDiscount = array();
				foreach($newDiscountArr as $k => $value){
					if((int)$value > $maxDiscount)break;
					$allowedDiscount[$k] = $value;
				}
				$fieldHDscnt = $this->Form->input(null,array(
															'name' => "KioskProductSale[discount][$key]",
															'id' => "spinner_$key",
															'label'=> false,
															'value' => $productDiscount,
															'type' => 'hidden',
															'id' => "discount_$key",
															)
												);
				$colHash = "<span style='background: skyblue;' id = 'hidden_disc_{$key}' title = ''>##</span>
				<input type='hidden' name='KioskProductSale[minimum_discount][$key]' id='hidden_dis_val_{$key}' />";
			}else{
				$colHash = "N/A";
				$fieldHDscnt = $this->Form->input(null,array(
															'name' => "KioskProductSale[discount][$key]",
															'value' => 0,
															'type' => 'hidden',
															'label' => false
													)
										);
			}
			
			$discount = $product->discount;
			$truncatedProduct = \Cake\Utility\Text::truncate(
                                                                        $product->product,
                                                                        30,
                                                                        [
                                                                                'ellipsis' => '...',
                                                                                'exact' => false
                                                                        ]
                                                                );
			//String::truncate($product['product'], 30, array('ellipsis' => '...', 'exact' => false));
			$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product->id.DS;
			$imageName =  $product->image;
			$absoluteImagePath = $imageDir.$imageName;
			$imageURL = "/thumb_no-image.png";
                
			if(@readlink($absoluteImagePath) || file_exists($absoluteImagePath)){
				$imageURL = "/files/Products/image/".$product->id."/$imageName";
			}
			$productQuantity = 1;
			$sellingPrice = $product->selling_price;
			$quantityChecked = $productDiscount = 0;
			$checked = false;
			$netAmount = "";
			if(array_key_exists('Basket',$this->request->Session()->read())){
				$sessionBaket = $this->request->Session()->read("Basket");
				if( count($sessionBaket) >= 1){
					if(array_key_exists($product->id,$sessionBaket)){
						#echo "<pre>"; print_r($sessionBaket); echo "</pre>";
						$productQuantity = $sessionBaket[$product->id]['quantity'];
						$sellingPrice = $sessionBaket[$product->id]['selling_price'];
						$productDiscount = $sessionBaket[$product->id]['discount'];
						if(array_key_exists('net_amount',$sessionBaket[$product->id])){
							$netAmount = round($sessionBaket[$product->id]['net_amount'],2);//changed on 17th may 2016
						}
						$checked = true;
					}
				}
			}
			$fieldDAmt =  $this->Form->input(null,array( //added by rajju
                                                    'type' => 'text',
                                                    'label' => false,
                                                    'style' => 'width:45px; margin-top:8px;',
                                                    'id' => "disc_amnt_$key",
                                                    'div'=>false,
                                                    'readonly' => true //updated on 16 July
                                                    )
                                    );
			$fieldNetAmt = $this->Form->input(null,array(
                                                    'type' => 'text',
                                                    'label' => false,
                                                    'name' => "KioskProductSale[net_amount][$key]",
                                                    'style' => 'width:45px; margin-top:8px;',
                                                    'id' => "net_val_$key",
                                                    'div'=>false,
                                                    'value' => $netAmount,
                                                    'readonly' => false
                                                    )
                                        );
			$colProdCode = $product->product_code;
			$colProdTitle = $this->Html->link($truncatedProduct, array(
																	   'controller' => 'products',
																	   'action' => 'view', $product->id
																	   ),
																array(
																		'escapeTitle' => false,
																		'title' => $product->product,
																		'id' => "tooltip_{$product->id}"
																	)
											);
			$colColor = $product->color;
			$colImgLnk = $this->Html->link($this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')),
								array('controller' => 'products','action' => 'edit', $product->id),
								array('escapeTitle' => false, 'title' => $product->product)
							);
			$colQty = h($product->quantity);
			$colSP = h($product->selling_price);
			$numerator = $product->selling_price*100;
			$denominator = $vat+100;
			$priceWithoutVat = $numerator/$denominator;
			$priceWithoutVat = number_format($priceWithoutVat,2);
			//Start: Hidden fields
			$hiddenProdTitle = $this->Form->input(null,array(
																'type' => 'hidden',
																'name' => "KioskProductSale[product][$key]",
																'value' => $product->product
															)
											);
			$hiddenProdId = $this->Form->input(null,array(
															'type' => 'hidden',
															'name' => "KioskProductSale[product_id][$key]",
															'value' => $product->id
														 )
												);
			$hiddenProdQty = $this->Form->input(null,array(
															'type' => 'hidden',
															'name' => "KioskProductSale[p_quantity][$key]",
															'value' => $product->quantity
														)
												);
			
			$hiddenProdSP = $this->Form->input(null,array(
															'type' => 'hidden',
															'name' => "KioskProductSale[selling_price][$key]",
															'value' => $sellingPrice
														)
												);
			$hiddenProdSP = "";
			$hiddenProdDscnt = $this->Form->input(null,array(
																'type' => 'hidden',
																'name' => "KioskProductSale[discount_status][$key]",
																'value' => $discountStatus
															)
												);
			$hiddenFldStr = "{$hiddenProdTitle}{$hiddenProdId}{$hiddenProdQty}{$hiddenProdSP}{$hiddenProdDscnt}";
			
			$fieldHpWV = "<input type='hidden' id='price_without_vat_$key' value='$priceWithoutVat' name ='KioskProductSale[price_without_vat][$key]'>";
			//End: Hidden fields
			$fieldSP = $this->Form->input(null,array(
                                                    'type' => 'text',
                                                    'name' => "KioskProductSale[selling_price][$key]",
                                                    'value' => $sellingPrice,
                                                    'label' => false,
                                                    'style' => 'width:39px; margin-top:8px;',
                                                    'div' => false,
                                                    'readonly' => false,
                                                    'id' => "selling_price_$key"
                                                )
                                    );
			
			$hiddenPageNum = $this->Form->input(null,array('type' => 'hidden','value' => $currentPageNumber,'name' => 'current_page'));
			
			$fieldProdItem = $fieldProdQty = "";
			if($product->quantity){
				$fieldProdQty =	$this->Form->input(null,array(
																'type' => 'text',
																'name' => "KioskProductSale[quantity][$key]",
																'value' => $productQuantity,
																'label' => false,
																'style' => 'width:36px; margin-top:8px;',
																'div' => false,
																'readonly' => false,
																'id' => "sale_quantity_$key"
															)
									);
		    
		   
				$fieldChkBox = $this->Form->input(null,array(
																'type' => 'checkbox',
																'name' => "KioskProductSale[item][$key]",
																'value' => $product->id,
																'label' => false,
																'style' => 'height:18px; margin-top:8px; transform:scale(1.5);',
																'readonly' => false,
																'div' => false,
																'checked' => $checked
															)
                            );
		    }
			echo "
				<tr>
					<td>$colProdCode</td>
					<td>$colProdTitle</td>
					<td>$catTitle</td>
					<td>$colColor</td>
					<td>$colImgLnk</td>
					<td>{$colQty}&nbsp;</td>
					<td>$priceWithoutVat<br/>$fieldSP</td>
					<td>$colHash</td>
					<td>$fieldDAmt</td>\n
					<td>$fieldNetAmt</td>\n
					<td>{$fieldProdQty}{$fieldChkBox}</td>
				</tr>
				
				$hiddenFldStr{$fieldHpWV}{$fieldHDscnt}
				";
				//\n<!--Start:hidden fields-->\n
				//\n<!--End:hidden fields-->\n
		}
		echo $hiddenPageNum;
?>
   
</tbody>
    </table>
    <div class="submit">
        <input type="submit" name='basket' value="Add to Basket / Calculate"/>
        <input type="submit" name='empty_basket' value="Clear the Basket"/>
    <?php		
        $options1 = array('label' => 'Update Invoice','div' => false,'name' => 'submit');		
        echo $this->Form->end($options1);		
    ?>
    </div>
    </div>
    
    
    
    <div class="paging">
    <?php
        echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
        echo $this->Paginator->numbers(array('separator' => ''));
        echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
    ?>
    </div>
 

<div class="actions">
    
</div>
<script type="text/javascript">

    <?php
		foreach ($products as $key => $product):
			$string = preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($product['Product']['description']));
			if(empty($string)){
				$string = $product['Product']['product'];
			}
			echo "\njQuery('#tooltip_{$product['Product']['id']}').tooltip({content:\"".htmlspecialchars($string)."\",track:true});\n";
			echo '$(function() {$( "#hidden_disc_'.$key.'").tooltip();});';
		endforeach;
    ?>
</script>
<script>
    $(document).ready(function(){
      
        
<?php
 
    foreach ($products as $key => $product){
        $discountStatus = $product->discount_status;
        if($discountStatus == 1){
            $maxDiscount = $product->discount; //it was there
            $salePrice = $product->selling_price; //new
            $discountValue = $salePrice * $maxDiscount/100; //new
            $minPrice = round($salePrice-$discountValue,2); //new
			echo "\nvar discount = $maxDiscount;\n";
			echo 'var sellPrice = $("#selling_price_'.$key.'").val();'."\n";
			echo 'var priceWithoutVat = $("#price_without_vat_'.$key.'").val();'."\n";
			echo $js = <<<JS
			priceWithoutVat = parseFloat(priceWithoutVat).toFixed(2);
			var disValue = priceWithoutVat*discount/100;
			var netVal = priceWithoutVat-disValue;
			netVal = netVal.toFixed(2);
			document.getElementById('hidden_disc_{$key}').title = 'Minimum price: ' + netVal;
JS;
			echo "document.getElementById('hidden_dis_val_{$key}').value = netVal;\n";
        }
    }
?>
   });
</script>
<script>
	$("input[id*='sale_quantity_']").keydown(function (event) {
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||  event.keyCode == 183 ||
		event.keyCode == 110) {
			;
			//48-57 => 0..9
			//8 => Backspace; 9 => tab
			//event.keyCode == 46 for dot
			//event.keyCode == 190 for dot
		}else{
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
    //alert(multipleValues);
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
 
<?php
	foreach ($products as $key => $product){
        $discountStatus = $product->discount_status;
        if($discountStatus == 1){
            $maxDiscount = $product->discount;
?>

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
            //genuine case
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
	$(function() {$( '#hidden_disc_' + <?php echo $key;?> ).tooltip();});
<?php
        }else{
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
        }
	});
<?php
        }
	}//foreach
?>
   
   $(document).ready(function(){
<?php	
    foreach ($products as $key => $product){
        $discountStatus = $product->discount_status;
        if($discountStatus == 1){
            $maxDiscount = $product->discount; //it was there
            $salePrice = $product->selling_price; //new
            $discountValue = $salePrice * $maxDiscount/100; //new
            $minPrice = round($salePrice-$discountValue,2); //new
	   
?>
        //var discount = $("#spinner_<?php echo $key;?>").val();
        var discount = <?=$maxDiscount;?>;
        var sellPrice = $("#selling_price_<?php echo $key;?>").val();
        var priceWithoutVat = $("#price_without_vat_<?php echo $key;?>").val();
        priceWithoutVat = parseFloat(priceWithoutVat).toFixed(2);
        //var disValue = sellPrice*discount/100;
        var disValue = priceWithoutVat*discount/100;
        var netVal = priceWithoutVat-disValue;
        netVal = netVal.toFixed(2);
        document.getElementById('hidden_disc_' + <?php echo $key;?>).title = '<?php echo "Minimum price: ";?> ' + netVal;
<?php
            echo "document.getElementById('hidden_dis_val_{$key}').value = netVal;";
        }
    }
?>
   });
</script>