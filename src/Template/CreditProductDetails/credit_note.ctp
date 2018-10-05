<style>
    #remote .tt-dropdown-menu {max-height: 250px;overflow-y: auto;}
    #remote .twitter-typehead {max-height: 250px;overflow-y: auto;}
    .tt-dataset, .tt-dataset-product {max-height: 250px;overflow-y: auto;}
    .row_hover:hover{color:blue;background-color:yellow;}
</style>
<?php
    $value = '';
    if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
	}  
	extract($this->request->query);
	if(!isset($product)){$product = "";}
	if(!isset($product_code)){$product_code = "";}
    $business = $customerAccountDetails['business'];
    $customerId = $customerAccountDetails['id'];
    $fName = $customerAccountDetails['fname'];
    $lName = $customerAccountDetails['lname'];
	$webRoot = $this->request->webroot."credit-product-details/search_credit_note/$customerId";
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
                <td><div id='remote'><input class="typeahead" type = "text" value = '<?= $value ?>' autofocus="autofocus" name = "search_kw" placeholder = "Product Code, Product Title or Product Description" style = "width:500px;height:25px;"/></div></td>
                <td rowspan="3"><select id='category_dropdown' name='category[]' multiple="multiple" size='6' onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select></td>		    
            </tr>
            <tr>
                <td><h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4></td>
            </tr>
            <tr>
                <td colspan='2'><input type='submit' name='submit' value='Search' ></td>
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
	echo $this->Form->submit("Search",$options);
    echo $this->Form->end();
    
    echo "<h2>Credit Note</h2>";
    echo $this->Form->create(null,array('url' => array(
                                                       'controller' => 'credit_product_details',
                                                       'action' => 'generate_credit_note',
                                                       $customerId
                                                       ),'autocomplete' => "off")
                             ); 
	
	echo $this->Form->input('null',array('type'=>'hidden','name'=>'customerId','value' => $customerId));
    $receiptCheck = '';
    $bulkDiscount = '';
    $sessionBaket = $this->request->Session()->read("Basket");
    $receiptCheck = $this->request->Session()->read('receipt_required');
    $bulkDiscount = $this->request->Session()->read('bulk_discount');
	$special = $this->request->Session()->read('special_invoice');
?>
    <table cellspacing='2' cellpadding='2' width='100%'>
		<?php $loggedInUser =  $this->request->session()->read('Auth.User.username');
		if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
		?>
		<tr>
            <td colspan='2'>Credit Quotation?</td>
            <td>Yes<input type='radio' name='special_invoice' value='1' <?php if($special == 1){echo "CHECKED";}?>/></td>
            <td>No<input type='radio' name='special_invoice' value='0' <?php if($special == 0){echo "CHECKED";}?>/></td>
		</tr>
		 <?php }?>
		<tr>
            <td colspan='2'>Do you need customer receipt?</td>
            <td>Yes<input type='radio' name='receipt_required' value='1' <?php if($receiptCheck == 1){echo "CHECKED";}?>/></td>
            <td>No<input type='radio' name='receipt_required' value='0' <?php if($receiptCheck == 0){echo "CHECKED";}?>/></td>
		</tr>
        <tr>
            <td style="width: 100px"><strong>Customer Id</strong></td>
            <td style="width: 115px"><?php echo $customerId;?></td>
            <?php if(empty($business)){?>
                <td style="width: 100px"><strong>Name</strong></td>
                <td><?php echo $fName." ".$lName;?></td>
            <?php }else{?>
            <td style="width: 100px"><strong>Business</strong></td>
            <td><?php echo $business;?></td>
            <?php } ?>
        </tr>
    </table>
    <?php $bulkDiscount = !empty($bulkDiscount)?$bulkDiscount:"";?>
    <div class="submit">
		<table>
            <tr>
                <td style= 'width:25px;'><input type="submit" name='basket' value="Add to Basket / Calculate"/></td>
                <td style= 'width:25px;'><input type="submit" name='check_out' value="Check Out"/></td>
                <td style= 'width:25px;'><input  type="submit" name='submit' value="Generate Credit"/></td>
                <td style= 'width:1000px;'><strong>Bulk Discount</strong>
				<input  type="text" name='bulk_discount' style="width: 4%" value='<?php echo $bulkDiscount;?>'/></td>
				<td style= 'width:25px;'><input type="submit" name='empty_basket' value="Clear the Basket"/></td>
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
<?php
    $sessionBaket = $this->request->Session()->read("Basket");
    $currentPageNumber = $this->Paginator->current();
?>
    <table cellpadding="0" cellspacing="0">
		<thead>
            <tr>
                <th><?php echo $this->Paginator->sort('product_code'); ?></th>
                <th><?php echo $this->Paginator->sort('product_id'); ?></th>
                <th><?php echo $this->Paginator->sort('color')?></th>
                <th>Image</th>
                <th><?php echo $this->Paginator->sort('quantity','Current Stock'); ?></th>
                <th><?php echo $this->Paginator->sort('sale_price','Sale Price'); ?></th>            
                <th>Dcnt</th>
                <th>Discnt Amt</th>
                <th style="width: 60px;">Net Value</th>
                <th colspan=2>Qty</th>
                <th>Type</th>
            </tr>
		</thead>
	<tbody>
<?php
	$i =   0;
	$groupStr = "";
    foreach ($products as $key => $product):
		$discountStatus = $product->discount_status;
		$discount = $product->discount;
		$truncatedProduct = \Cake\Utility\Text::truncate(
                                                                        $product->product,
                                                                        22,
                                                                        [
                                                                                'ellipsis' => '...',
                                                                                'exact' => false
                                                                        ]
                                                                );
		$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product->id.DS;
		$imageName = $product->image;
		$largeImageName = 'vga_'.$product->image;
		$absoluteImagePath = $imageDir.$imageName;
		$LargeimageURL = $imageURL = "/thumb_no-image.png";
				
		$LargeimageURL = $imageURL = "/thumb_no-image.png";
		if(@readlink($absoluteImagePath) || file_exists($absoluteImagePath)){
			$imageURL = "$siteBaseURL/files/Products/image/".$product->id."/$imageName";
			$LargeimageURL = "$siteBaseURL/files/Products/image/".$product->id."/"."$largeImageName";
			
			
		}
		 $i++;
		$groupStr.="\n$(\".group{$i}\").colorbox({rel:'group{$i}'});";		
		$productQuantity = 1;
		$sellingPrice = $product->selling_price;
		$productRemarks = "";
		$productDiscount = 0;
		$quantityChecked = 0;
		$refundType = "Normal";
		$checked = false;
		$netAmount = "";
		if( count($sessionBaket) >= 1){
            if(array_key_exists($product->id,$sessionBaket)){
                #echo "<pre>"; print_r($sessionBaket); echo "</pre>";
                $productQuantity = $sessionBaket[$product->id]['quantity'];
                $sellingPrice = $sessionBaket[$product->id]['selling_price'];
                $productRemarks = $sessionBaket[$product->id]['remarks'];
                $productDiscount = $sessionBaket[$product->id]['discount'];
                $refundType = $sessionBaket[$product->id]['type'];
                $checked = true;
                $netAmount = round($sessionBaket[$product->id]['net_amount'],2);//changed on 17th may 2016
            }
		}
        $colProdTitle = $this->Html->link($truncatedProduct,
                                                array('controller' => 'products', 'action' => 'view', $product->id),
                                                array(
                                                    'escapeTitle' => false,
                                                    'title' => $product->product,
                                                    'id' => "tooltip_{$product->id}"
                                                )
                                            );
        $colProdCode = $product->product_code;
        $colColor = $product->color;
        $colImgLnk = $this->Html->link(
									$this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')),
									$LargeimageURL,
									array('escapeTitle' => false, 'title' => $product->product,'class' => "group{$i}")
							);
        $colQty = h($product->quantity);
        
        $numerator = $product->selling_price*100;
        $denominator = $vat+100;
        $priceWithoutVat = $numerator/$denominator;
        $priceWithoutVat = number_format($priceWithoutVat,2);
        $fieldHpWV = "<input type='hidden' id='price_without_vat_$key' value='$priceWithoutVat' name ='KioskProductSale[price_without_vat][$key]'>";
        
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
        $fieldHProd = $this->Form->input(null,array(
									'type' => 'hidden',
									'name' => "CreditProductDetail[product][$key]",
									'value' => $product->product
									)
							);
			
		$fieldHPQ = $this->Form->input(null,array(
									'type' => 'hidden',
									'name' => "CreditProductDetail[p_quantity][$key]",
									'value' => $product->quantity
									)
							);
			
		$fieldHSP = $this->Form->input(null,array(
									'type' => 'hidden',
									'name' => "CreditProductDetail[selling_price][$key]",
									'value' => $sellingPrice
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
			$fieldHDscnt = $this->Form->input(null,array(
                                                        'name' => "CreditProductDetail[discount][$key]",
                                                        'id' => "spinner_$key",
                                                        'label'=> false,
                                                        'value' => $productDiscount,
                                                        'style' => "width: 18px;",
                                                        'type' => 'hidden',
                                                        'id' => "discount_$key",
                                                        )
                                            );
            $colHash = "<span style='background: skyblue;' id = 'hidden_disc_{$key}' title = ''>##</span>
            <input type='hidden' name='CreditProductDetail[minimum_discount][$key]' id='hidden_dis_val_{$key}' />";
        }else{
            $colHash = "N/A";
            $fieldHDscnt = $this->Form->input(null,array(
                                                        'name' => "CreditProductDetail[discount][$key]",
                                                        'value' => 0,
                                                        'type' => 'hidden',
                                                        'label' => false,
                                                        'type' => 'hidden',
                                                )
                                    );
			echo "<input type='hidden' name='CreditProductDetail[minimum_discount][$key]' id='hidden_dis_val_{$key}' value = '$priceWithoutVat' />";
        }
        $fieldHDS = $this->Form->input(null,array(
                                                    'type' => 'hidden',
                                                    'name' => "CreditProductDetail[discount_status][$key]",
                                                    'value' => $discountStatus
                                                )
                                    );
			
		$fieldHPid = $this->Form->input(null,array(
                                                    'type' => 'hidden',
                                                    'name' => "CreditProductDetail[product_id][$key]",
                                                    'value' => $product->id
                                                    )
                                    );
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
        $fieldPQty = $this->Form->input(null,array(
                                                    'type' => 'text',
                                                    'name' => "CreditProductDetail[quantity][$key]",
                                                    'value' => $productQuantity,
                                                    'label' => false,
                                                    'style' => 'width:36px; margin-top:8px;display: inline;',
                                                    'div'=>false,
                                                    'readonly' => false,
                                                    'id'=>"sale_quantity_$key"
                                                    )
                                        );
        $fieldChkBox = $this->Form->input(null,array(
                                                    'type' => 'checkbox',
                                                    'name' => "CreditProductDetail[item][$key]",
                                                    'value' => $product->id,
                                                    'label' => false,
                                                    'style' => 'height:18px; margin-top:8px; transform:scale(1.5);display: inline;',
                                                    'readonly' => false,
                                                    'div'=>'display: inline;',
                                                    'checked' => $checked
                                                    )
                                        );
        $options = array('Normal'=>'Normal','Faulty'=>'Faulty');
        $fieldType = $this->Form->input('type',array(
                                                    'options' => $options,
                                                    'label'=>false,
                                                    'name' => "CreditProductDetail[type][$key]",
                                                    'value' => $refundType
                                                    )
                                        );
        $fieldHRem = $this->Form->input(null,array(
                                                    'type' => 'hidden',
                                                    'name' => "CreditProductDetail[remarks][$key]",
                                                    'value' => $productRemarks,
                                                    'label' => false,
                                                    'style' => 'width:80px;',
                                                    'readonly' => false
                                                )
                                        );
        
        echo "<tr>
                <td>$colProdCode</td>\n 
                <td>$colProdTitle</td>\n 
                <td>$colColor</td>\n 
                <td>$colImgLnk</td>\n 
                <td>$colQty</td>\n 
                <td>$priceWithoutVat<br/>$fieldSP</td>\n 
                <td>$colHash</td>\n
                <td>$fieldDAmt</td>\n
                <td>$fieldNetAmt</td>\n
                <td>{$fieldPQty}</td>\n
                <td>{$fieldChkBox}</td>\n 
                <td>$fieldType</td>\n 
                {$fieldHDscnt}\n{$fieldHpWV}\n{$fieldHProd}\n{$fieldHPQ}\n{$fieldHSP}\n{$fieldHDS}\n{$fieldHPid}\n{$fieldHRem}\n 
                </tr>
                ";
    endforeach;
    echo $this->Form->input(null,array('type' => 'hidden','value' => $currentPageNumber,'name' => 'current_page'));
?>
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
        <table>
            <tr>
                <td style= 'width:25px;'><input type="submit" name='basket' value="Add to Basket / Calculate"/></td>
                <td style= 'width:25px;'><input type="submit" name='check_out' value="Check Out"/></td>
                <td style= 'width:5555px;'>
                    <?php
                        $options1 = array('label' => 'Generate Credit','div' => false,'name' => 'submit');		
                        echo $this->Form->end($options1);		
                    ?>
                </td>
                <td style= 'width:30px;'><input type="submit" name='empty_basket' value="Clear the Basket"/></td>
            </tr>
        </table>
    </div>
    <p>
        <?php
            echo $this->Paginator->counter(array(
            'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
            ));
        ?>
    </p>
	
    <div class="paging">
    <?php
        echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
        echo $this->Paginator->numbers(array('separator' => ''));
        echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
    ?>
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
		if (
            (event.keyCode >= 48 && event.keyCode <= 57) ||
            (event.keyCode >= 96 && event.keyCode <= 105) ||
            event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 37 ||
			event.keyCode == 39 ||  event.keyCode == 183 || event.keyCode == 110){  //|| event.keyCode == 110
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
        $('#url_category').val(multipleValues.join( "," ));
    }

    var product_dataset = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    //prefetch: "/products/data",
    remote: {
        url: "/kiosk-product-sales/admin_data?category=%CID&search=%QUERY",
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

<?php
	foreach ($products as $key => $product){
        $discountStatus = $product->discount_status;
        if($discountStatus == 1){
            $maxDiscount = $product->discount;
?>
    $("<?php echo "#net_val_".$key;?>").blur(function(){
        //checking if the input value is lesser than the allowed discount
        if (parseFloat($(this).val()) < parseFloat($('#hidden_dis_val_' + <?php echo $key;?>).val())) {
            if (parseFloat($(this).val()) < 0) {
                $(this).val("");
                $('#disc_amnt_' + <?php echo $key;?>).val("");
                $(this).val("");
                $('#checked_qtt_' + <?php echo $key;?>).attr('checked', false);
                $('#error_div').html('Price cannot be less than the Zero for <?php echo $product->product;?>!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
            alert('Price cannot be less than Zero for <?php echo $product->product;?>!');
            }else{
                var discountedAmount = parseFloat($('#price_without_vat_' + <?php echo $key;?>).val())-parseFloat($(this).val());
				$('#disc_amnt_' + <?php echo $key;?>).val(parseFloat(discountedAmount));
				var discountPercentage = discountedAmount/parseFloat($('#price_without_vat_' + <?php echo $key;?>).val())*100;
				$('#discount_' + <?php echo $key;?>).val(parseFloat(discountPercentage));   
            }
            
        } else if (parseFloat($(this).val()) < parseFloat($('#price_without_vat_' + <?php echo $key;?>).val())) {
            //genuine case
            //if value is lesser than the selling price without vat, then populating the discount amount and assigning discount percent in hidden
            var discountedAmount = parseFloat($('#price_without_vat_' + <?php echo $key;?>).val())-parseFloat($(this).val());
            $('#disc_amnt_' + <?php echo $key;?>).val(parseFloat(discountedAmount));
            var discountPercentage = discountedAmount/parseFloat($('#price_without_vat_' + <?php echo $key;?>).val())*100;
            $('#discount_' + <?php echo $key;?>).val(parseFloat(discountPercentage));
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
            
            if (parseFloat($(this).val()) < 0) {
                $(this).val("");
                $('#disc_amnt_' + <?php echo $key;?>).val("");
                $(this).val("");
                $('#checked_qtt_' + <?php echo $key;?>).attr('checked', false);
                $('#error_div').html('Price cannot be less than the Zero for <?php echo $product->product;?>!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
            alert('Price cannot be less than Zero for <?php echo $product->product;?>!');
            }else{
                var discountedAmount = parseFloat($('#price_without_vat_' + <?php echo $key;?>).val())-parseFloat($(this).val());
				$('#disc_amnt_' + <?php echo $key;?>).val(parseFloat(discountedAmount));
				var discountPercentage = discountedAmount/parseFloat($('#price_without_vat_' + <?php echo $key;?>).val())*100;
				$('#discount_' + <?php echo $key;?>).val(parseFloat(discountPercentage));   
            }
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
<script>
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>