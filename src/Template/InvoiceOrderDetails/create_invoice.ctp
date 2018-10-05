<style>
    #remote .tt-dropdown-menu {max-height: 250px;overflow-y: auto;}
    #remote .twitter-typehead {max-height: 250px;overflow-y: auto;}
    .tt-dataset, .tt-dataset-product {max-height: 250px;overflow-y: auto;}
    .row_hover:hover{color:blue;background-color:yellow;}
</style>
<?php
	$bulkDiscountSession = $this->request->Session()->read('BulkDiscount');
	$receiptRequiredSession = $this->request->Session()->read('receipt_required');
	$business = $customerAccountDetails['business'];
	$customerId = $customerAccountDetails['id'];
	$fName = $customerAccountDetails['fname'];
	$lName = $customerAccountDetails['lname'];
?>
<div><?php //echo $this->Session->flash(''); ?></div>
<div class="kioskProductSales index">

<div id="idVal">
    
</div>
<?php
	$webRoot = $this->request->webroot."invoice-order-details/search_performa/$customerId";
	$value = '';
	if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
	}  
	extract($this->request->query);
	if(!isset($product)){$product = "";}
	if(!isset($product_code)){$product_code = "";}
	
    echo $this->Form->create(null, array('url' => $webRoot,'type' => 'get'));
?>
	<fieldset>	    
		<legend>Search</legend>
	    <table cellspacing='1'  cellpadding='2'>
			<tr>
				<td>&nbsp;</td>
				<td><strong>Find by category &raquo;</strong></td>
			</tr>
			<tr>		    
				<td><div id='remote'><input class="typeahead" type = "text" value = '<?= $value ?>' name = "search_kw" placeholder = "Product Code,Title or Description" autofocus style = "width:215px;height:25px;"/></div></td>
				<td rowspan="3"><select id='category_dropdown' name='category[]' multiple="multiple" size='6' onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select></td>	
			</tr>
			<tr>
                <td><h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4></td>
            </tr>
			<tr>
				<td><input type='submit' name='submit' value='Search'></td>
			</tr>		
	    </table>
	</fieldset>
<?php $options = array('label' => '','div' => false, 'name' => 'submit1', 'style' => 'display:none;');?>
<?php echo $this->Form->end($options);?>

    <h3>Create Performa (<span style="font-size: 17px; font-weight: normal">
    <?php if(empty($business)){?>
		 <?php echo $this->Html->link($fName." ".$lName,array('controller'=>'customers','action'=>'view',$customerId),array('style'=>"text-decoration: none;"));?>
	  <?php }else{?>
		 <?php echo $this->Html->link($business,array('controller'=>'customers','action'=>'view',$customerId),array('style'=>"text-decoration: none;"));?>
	  <?php } ?>
		       </span>)
    </h3>
	
<?php
	echo $this->Form->create(null, array('url' => array('controller' => 'invoice-order-details',
														'action' => 'save_invoice'),'autocomplete' => "off")); 
	echo $this->Form->input('null',array('type'=>'hidden','name'=>'customerId','value'=>$customerId));
    $receiptCheck ='';
	$sessionBaket = $this->request->Session()->read("Basket");
	
	if( count($sessionBaket) >= 1){
		foreach($sessionBaket as $ki => $productDetail){
			if(array_key_exists('receipt_required',$productDetail)){
				$receiptCheck = $productDetail['receipt_required'];
			} 
			
	   }
	}
	
	if(count($bulkDiscountSession) >= 1){
		if( array_key_exists('BulkDiscount',$this->request->Session()->read())){
			$bulkDiscount = $bulkDiscountSession;
		} 
	}
	    
	if(count($receiptRequiredSession) >= 1){
		if( array_key_exists('receipt_required',$this->request->Session()->read())){
			$receiptCheck = $receiptRequiredSession;
		} 
	}
?>
    <table cellspacing='2' cellpadding='2' style='width:400px'>
		<tr>
			<td colspan='2'>Do you want to send email to the customer?</td>
			<td>Yes<input type='radio' name='receipt_required' value='1' <?php if($receiptCheck == 1){echo "CHECKED";}?>/></td>
			<td>No<input type='radio' name='receipt_required' value='0' <?php if($receiptCheck == 0){echo "CHECKED";}?>/></td>
			<td></td>
		</tr>
    </table>
    <div class="submit">
		<table>
			<tr>
				<td> <input type="submit" name='basket' style="width: 100%; "value="Add to Basket / Calculate"/></td>
				<td><input  type="submit" name='submit' style="width: 100%; " value="Create Performa"/></td>
				 
				<td><strong>Bulk Discount</strong>
					<input  type="text" name='bulk_discount' style="width: 15%;height: 12px;" value='<?php if(!empty($bulkDiscount)){echo $bulkDiscount;}else{echo "";}?>'/>
				</td>
				<td> <input type="submit" name='empty_basket' value="Clear the Basket"/></td>
			</tr>
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
			<th><?php echo $this->Paginator->sort('product_code','Prod Code'); ?></th>
            <th><?php echo $this->Paginator->sort('product_id'); ?></th>
			<th><?php echo $this->Paginator->sort('color')?></th>
            <th>Image</th>
            <th><?php echo $this->Paginator->sort('quantity','Curr Stock'); ?></th>
            <th><?php echo $this->Paginator->sort('sale_price','Sale Price'); ?></th>            
			<th>Dscnt</th>
			<th>Dscnt <br/>Amt</th>
			<th>Net<br/>Value</th>
            <th>Quantity</th>			
        </tr>
        </thead>
        
	<tbody>
	
<?php
	$sessionBaket = $this->request->Session()->read("performa_basket");
	$currentPageNumber = $this->Paginator->current();
	
	foreach ($products as $key => $product){
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
		
		//String::truncate($product['Product']['product'],30, array('ellipsis' => '...','exact' => false));
		$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product->id.DS;
		$imageName =  $product->image;
		$absoluteImagePath = $imageDir.$imageName;
		$imageURL = "/thumb_no-image.png";
			
		if(@readlink($absoluteImagePath) ||file_exists($absoluteImagePath)){
			$imageURL = "/files/Products/image/".$product->id."/$imageName";
		}
			
		$productQuantity = 1;
		$sellingPrice = $product->selling_price;
		$quantityChecked = $productDiscount = 0;
		$netAmount = "";
		$checked = false;
		if( count($sessionBaket) >= 1){
			if(array_key_exists($product->id,$sessionBaket)){
				$productQuantity = $sessionBaket[$product->id]['quantity'];
				$sellingPrice = $sessionBaket[$product->id]['selling_price'];
				$productDiscount = $sessionBaket[$product->id]['discount'];
				$checked = true;
				$netAmount = round($sessionBaket[$product->id]['net_amount'],2);//changed on 17th may 2016
			}
		}
	
		$colProdCode = $product->product_code;
		
		$colProdTitleLink = $this->Html->link($truncatedProduct,
											array('controller' => 'products', 'action' => 'view', $product->id),
											array(
												  'escapeTitle' => false,
												  'title' => $product->product,
												  'id' => "tooltip_{$product->id}")
										);
		$colColor = $product->color;
	
		$colProdImgEditLink = $this->Html->link(
							$this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')),
							array('controller' => 'products','action' => 'edit', $product->id),
							array('escapeTitle' => false, 'title' => $product->product)
						);
		
		$colQty = $product->quantity;
		
		$colSP = $this->Form->input(null,array(
											'type' => 'text',
											'name' => "InvoiceOrderDetail[selling_price][$key]",
											'value' => $sellingPrice,
											'label' => false,
											'style' => 'width:39px; margin-top:8px;',
											'div' => false,
											'readonly' => false,
											'id' => "selling_price_$key"
										)
								);
		//******** Hidden fields ***********
		$fieldHProd = $this->Form->input(null, array(
														'type' => 'hidden',
														'name' => "InvoiceOrderDetail[product][$key]",
														'value' => $product->product
													)
										);
		$fieldHPQ = $this->Form->input(null, array(
													'type' => 'hidden',
													'name' => "InvoiceOrderDetail[p_quantity][$key]",
													'value' => $product->quantity
												)
										);
				
		$fieldHSP = $this->Form->input(null,array(
													'type' => 'hidden',
													'name' => "InvoiceOrderDetail[selling_price][$key]",
													'value' => $sellingPrice
												)
										);
				
		$fieldHPQ = $this->Form->input(null,	array(
														'type' => 'hidden',
														'name' => "InvoiceOrderDetail[p_quantity][$key]",
														'value' => $product->quantity
													)
										);
		//******** End: Hidden fields ***********
		if($discountStatus == 1){
			$maxDiscount = $product->discount;//$discountOptions;
			//$maxDiscount = $maxDiscount+1;
			$allowedDiscount = array();
			foreach($newDiscountArr as $k => $value){
				if((int)$value > $maxDiscount)break;
				$allowedDiscount[$k] = $value;
			}
			$fieldHDscnt = $this->Form->input(null,array(
															'name' => "InvoiceOrderDetail[discount][$key]",
															'id' => "spinner_$key",
															'label'=> false,
															'value' => $productDiscount,
															'style' => "width: 18px;",
															'type' => 'hidden', //temporary
															'id' => "discount_$key",
															)
												);
			$colHash = "
						<span style='background: skyblue;' id = 'hidden_disc_{$key}' title = ''>##</span>
						<input type='hidden' name='InvoiceOrderDetail[minimum_discount][$key]' id='hidden_dis_val_{$key}' />";
		}else{
			$colHash = "N/A";
			$fieldHDscnt = $this->Form->input(null,array(
														'name' => "InvoiceOrderDetail[discount][$key]",
														'value' => 0,
														'type' => 'hidden',
														'label' => false,
												)
									);
		}
		//******** Start: Hidden fields ***********
		$fieldHDS = $this->Form->input(null,array(
													'type' => 'hidden',
													'name' => "InvoiceOrderDetail[discount_status][$key]",
													'value' => $discountStatus
												)
										);
		$fieldHPid = $this->Form->input(null,array(
													'type' => 'hidden',
													'name' => "InvoiceOrderDetail[product_id][$key]",
													'value' => $product->id
												)
										);
		
		$numerator = $product->selling_price*100;
        $denominator = $vat+100;
        $priceWithoutVat = $numerator/$denominator;
        $priceWithoutVat = number_format($priceWithoutVat,2);
		$fieldHpWV = "<input type='hidden' id='price_without_vat_$key' value='$priceWithoutVat' name ='InvoiceOrderDetail[price_without_vat][$key]'>";
		//******** End: Hidden fields ***********
		$fieldDAmt =  $this->Form->input(null,array(
													'type' => 'text',
													'label' => false,
													'style' => 'width:45px; margin-top:8px;',
													'id' => "disc_amnt_$key",
													'div' => false,
													'readonly' => true //updated on 16 July
													)
										);
		$fieldNetAmt = $this->Form->input(null,array(
                                                    'type' => 'text',
                                                    'label' => false,
                                                    'name' => "InvoiceOrderDetail[net_amount][$key]",
                                                    'style' => 'width:45px; margin-top:8px;',
                                                    'id' => "net_val_$key",
                                                    'div'=>false,
                                                    'value' => $netAmount,
                                                    'readonly' => false
                                                    )
                                        );
		if($product->quantity){
			$fieldPQty = $this->Form->input(null,	array(
														'type' => 'text',
														'name' => "InvoiceOrderDetail[quantity][$key]",
														'value' => $productQuantity,
														'label' => false,
														'style' => 'width:36px; margin-top:8px;',
														'div'=>false,
														'readonly' => false,
														'id'=>"sale_quantity_$key"
													)
										);
				
			   
			$fieldChkBox = $this->Form->input(null,	array(
														'type' => 'checkbox',
														'name' => "InvoiceOrderDetail[item][$key]",
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
			<td>$colProdTitleLink</td>
			<td>$colColor</td>
			<td>$colProdImgEditLink</td>
			<td>$colQty</td>
			<td>$priceWithoutVat<br/>{$colSP}</td>
			<td>{$colHash}</td>
			<td>$fieldDAmt</td>
			<td>$fieldNetAmt</td>
			<td>{$fieldPQty}</td><td>{$fieldChkBox}</td>
		</tr>
		
		 {$fieldHDscnt}\n{$fieldHpWV}\n{$fieldHProd}\n{$fieldHPQ}\n{$fieldHSP}\n{$fieldHDS}\n{$fieldHPid}\n\n 
		";
	}
	echo $this->Form->input(null,array('type' => 'hidden','value' => $currentPageNumber,'name' => 'current_page'));
?>
		</tbody>
	</table>
    <div class="submit">
		<table>
			<tr>
				<td style='width:30px;'><input type="submit" name='basket' value="Add to Basket / Calculate"/></td>
				<td style='width: 5500px';>
			<?php		
				$options1 = array('label' => 'Create Performa','div' => false,'name' => 'submit' );
				echo $this->Form->submit("Create Performa",$options1);
				echo $this->Form->end();		
			?>
				</td>
				<td style='width: 40px';><input type="submit" name='empty_basket' value="Clear the Basket"/></td>
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
		<li><?php echo $this->Html->link(__('New Customer'), array('controller'=>'customers','action' => 'add'),array('style'=>"text-decoration: none;")); ?></li>
        <li><?php echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index'),array('style'=>"text-decoration: none;")); ?></li>
	</ul>
</div>

<script type="text/javascript">
    <?php
		foreach ($products as $key => $product):
			$string = preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($product->description));
			if(empty($string)){
				$string = $product->product;
			}
			echo "jQuery('#tooltip_{$product->id}').tooltip({content:\"".htmlspecialchars($string)."\",track:true});";
		endforeach;
    ?>
</script>
<script type="text/javascript">
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
		} else {
			event.preventDefault();
		}
		
		if($(this).val().indexOf('.') !== -1 && event.keyCode == 190){event.preventDefault();}
    });
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

	var product_dataset = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		//prefetch: "/products/data",
		remote: {
			 url: "/products/admin-Data?category=%CID&search=%QUERY",
			replace: function (url,query) {
				var multipleValues = $( "#category_dropdown" ).val() || [];
				$('#url_category').val(multipleValues.join( "," ));
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
<script>
<?php
	foreach ($products as $key => $product){
		$discountStatus = $product->discount_status;
		if($discountStatus == 1){
			$maxDiscount = $product->discount;
			//here was spinner code
		}
	}
?>
   
   $(document).ready(function(){
<?php
	foreach ($products as $key => $product){
		$discountStatus = $product->discount_status;
			if($discountStatus == 1){
				$maxDiscount = $product->discount;
?>
				/*var discount = $("#spinner_<?php echo $key;?>").val();
				var sellPrice = $("#selling_price_<?php echo $key;?>").val();
				var disValue = sellPrice*discount/100;
				$("#disc_amnt_<?php echo $key;?>").val(disValue);*/
<?php
			}
	}
?>
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
            $('#discount_' + <?php echo $key;?>).val(parseFloat(discountPercentage)); <?php //.toFixed(2) ?>
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
            $('#discount_' + <?php echo $key;?>).val(parseFloat(discountPercentage)); <?php //.toFixed(2) ?>
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