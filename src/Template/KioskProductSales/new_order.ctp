<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<head>
  <?php
  //pr($this->request->session()->read("Auth"));die;
  $siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
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


<?php //echo $javascript->link('jquery', false);
//$javascript->link(array('jquery/jquery', '/chat/js/chat.js'), false);
      //$html->css('/chat/css/chat.css', null, null, false);
	 // echo $this->Html->css('chat.css');
	 // echo $this->Html->script('chat.js');
	   //echo $ajaxChat->generate('chat1');
?>
</style>
<div id="no_item" title="No Item IN The Basket">No Item In The Basket</div>
<div id="more_qty" title="Quantity Not Sufficent">Quantity Not Sufficent</div>
<div id="zero_qty" title="Quantity Can't be zero">Quantity Can't be zero</div>
<div id="error_for_alert" title="error_for_alert">Error</div>
<?php

	$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
	$payment_url = $this->Url->build(["controller" => "kiosk_product_sales","action" => "make_payment_ajax"]);
	
?>
<input type='hidden' name='payment_url' id='payment_url' value='<?=$payment_url?>' />
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
    $webRoot = $this->request->webroot.'kiosk-product-sales/search'; //FULL_BASE_URL.
?>
<div id="flash_msg" style="clear: both;color: #fff;background: #c43c35;border: 1px solid rgba(0, 0, 0, 0.5);background-repeat: repeat-x;text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.3);font-size: 19px;"><?php //echo $this->Session->flash(''); ?></div>
<div id = "payment_div">
<?php echo $this->element('/kiosk_product_sale/payment',array(
															  'setting' => $setting,
															  )); ?>
</div>
<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>	
        <li><?php echo $this->Html->link(__('View Sale'), array('action' => 'index')); ?> </li>
	<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'stock', 'action' => 'index')); ?> </li>
    </ul>
</div>
<div class="kioskProductSales index" id = 'product_div'>
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
    <?php echo $this->Form->end($options); ?>

    <h2><?php echo __('New Order'); ?></h2>
	<div id="error_div"></div>
    <?php echo $this->Html->link('Restore Session', array('action' => 'restore_session', $this->request->params['controller'], "new_order", 'Basket', $kioskId), array('style' => "float: right;margin-right: 170px;"));?>
    <?php //echo $this->Form->create(null,array('url' => array('controller' => 'kiosk_product_sales','action' => 'sell_products'))); ?>
    <?php
	
	$custid = !isset($custid) ? "" : $custid;
	  $fname = !isset($customer_fname) ? "" : $customer_fname;
	  $lname = !isset($customer_lname) ? "" : $customer_lname;
	  $mobile = !isset($customer_mobile) ? "" : $customer_mobile;
	  $email = !isset($customer_email) ? "" :$customer_email;
	  $zip = !isset($customer_zip) ? "" :$customer_zip;
	  $address1= !isset($address_1) ? "" : $address_1;
	  $address2 = !isset($address_2) ? "" : $address_2;
	  $city = !isset($city) ? "" : $city;
	  $state = !isset($state) ? "" : $state;
	  $receipt_required = !isset($receipt_required) ? "" : $receipt_required;
    ?>
	
    <?php
		//$userId = $this->request->Session()->read('Auth.User.id');
	  if(!empty($this->request->query['customerId'])){
		echo $this->element('customer_form',array('CustomerInfo' => array(
										'custid' => $this->request->query['customerId'],
									    'fname' => $customerdetail['0']['fname'],
									    'lname' => $customerdetail['0']['lname'],
									    'mobile' => $customerdetail['0']['mobile'],
									    'email' => $customerdetail['0']['email'],
									    'zip' => $customerdetail['0']['zip'],
									    'receipt_required' => $receipt_required,
									    'address1' => $customerdetail['0']['address_1'],
									    'address2' => $customerdetail['0']['address_2'],
									    'city' => $customerdetail['0']['city'],
									    'state' => $customerdetail['0']['state'],
											//'created_by' => $userId
									    )
						)
			      );
	  }else{
		echo $this->element('customer_form',array('CustomerInfo' => array(
										'custid' => $custid,
									    'fname' => $fname,
									    'lname' => $lname,
									    'mobile' => $mobile,
									    'email' => $email,
									    'zip' => $zip,
									    'receipt_required' => $receipt_required,
									    'address1' => $address1,
									    'address2' => $address2,
									    'city' => $city,
									    'state' => $state
									    )
						)
			      );
	  }
	  
    ?>
    <div class="submit">
	  <table width = '100%'>
	   <tr>
		<td >
		  <?php
		  $sell_url = "http://".ADMIN_DOMAIN."/img/sell_button.png";
		  $clear_basket = "http://".ADMIN_DOMAIN."/img/clear_basket.png";
		  echo $this->Html->image($sell_url, array('alt' => 'Sell','class' => 'make_payment','style' => 'height: 36px;')); ?></td>
		
	<td style="float: right;"><?php echo $this->Html->image($clear_basket, array('alt' => 'Clear Basket','class' => 'clear_basket','style' => 'height: 36px;')); ?></td>
	   </tr>
	  </table>
    </div>
	<?php //die;?>
    <table cellpadding="0" cellspacing="0">
	  <thead>
		<tr>
		  <th><?php echo $this->Paginator->sort('product_code'); ?></th>
		  <th><?php echo $this->Paginator->sort('product_id'); ?></th>
		  <th><?php echo $this->Paginator->sort('color')?></th>
		  <th>Image</th>
		  <th><?php echo $this->Paginator->sort('quantity','Current Stock'); ?></th>
		  <th><?php echo $this->Paginator->sort('sale_price','Sale Price'); ?></th>            
		  <th>Discount</th>
		  <th>Discount Amount</th>
		  <th style="width: 60px;">Net Value</th>
		  <th>Quantity</th>			
		  <th>Remarks</th>		
		</tr>
	  </thead>
      <?php
	    $sessionBaket = $this->request->Session()->read("Basket");
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
                    
					echo $this->Html->link(
                                    $this->Html->image($imageURL, array('fullBase' => false,'width'=>'100px','height'=>'100px')), //rasu
                                    $largeImageURL,
                                    array('escapeTitle' => false, 'title' => $product->product,'class' => "group{$key}")
                            );
                    ?>
            </td>
            <td><?php echo h($product->quantity); ?>&nbsp;</td>
            <td><?php echo $this->Form->input(null,array(
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
	    
	    //echo h($product['Product']['selling_price']); ?>&nbsp;
            <?php
		    echo $this->Form->input(null,array(
									'id' => "product_$key",
                                    'type' => 'hidden',
                                    'name' => "KioskProductSale[product][$key]",
                                    'value' => $product->product
                                    )
                            );
			echo $this->Form->input(null,array(
									'id' => "product_code_$key",
                                    'type' => 'hidden',
                                    'name' => "KioskProductSale[product_code][$key]",
                                    'value' => $product->product_code
                                    )
                            );
			echo $this->Form->input(null,array(
							'id' => "p_org_quantity_$key",
							'type' => 'hidden',
							'name' => "KioskProductSale[p_quantity][$key]",
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
									'id' => "discount_status_$key",
                                    'type' => 'hidden',
                                    'name' => "KioskProductSale[discount_status][$key]",
                                    'value' => $discountStatus
                                    )
                            );
		    
		  
		    
		    echo $this->Form->input(null,array(
									'id' => "product_id_$key",
                                    'type' => 'hidden',
                                    'name' => "KioskProductSale[product_id][$key]",
                                    'value' => $product->id
                                 )
                            );
                ?>
	    <td><?php echo $this->Form->input(null,array(
                                    'type' => 'text',
                                    //'name' => "data[KioskProductSale][quantity][$key]",
                                    'value' => $disAmount,
                                    'label' => false,
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
										 'value' => $netAmount
						//'readonly' => true
		)
	    );?></td>
            <td style="width: 70px;"><?php
		    if($product->quantity){
			echo $this->Form->input(null,array(
									'id' => 'Qty_'.$key,
                                    'type' => 'text',
                                    'name' => "KioskProductSale[quantity][$key]",
                                    'value' => $productQuantity,
                                    'label' => false,
                                    'style' => 'width:39px; margin-top:8px;',
				    'div'=>false,
                                    'readonly' => false
                                    )
                            );
		    
		   echo "</td>\n<td cellspacing='1'>";
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
                                    'type' => 'text',
                                    'name' => "KioskProductSale[remarks][$key]",
                                    'value' => $productRemarks,
                                    'label' => false,
                                    'style' => 'width:80px;padding-top: 10px;margin-left: -36px;',
                                    'readonly' => false,
									'id' => "remarks_$key",
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
     $productID = $product->id;
     $string = preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($product->product));
     if(empty($string)){
      $string = $product->product;
      //htmlspecialchars($str, ENT_NOQUOTES, "UTF-8")
     }
      echo "jQuery('#tooltip_{$productID}').tooltip({content:\"".htmlspecialchars($string)."\",track:true});";
     endforeach;
    ?>
    </script>
    <div class="submit">
	 <table>
	  <tr>
	   
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
  display: 'product', //code
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
			var dis_status = $("<?php echo "#discount_status_".$key;?>" ).val();
			
			if (parseFloat($(this).val()) < parseFloat($('#hidden_dis_val_' + <?php echo $key;?>).val())) {
			  $(this).val("");
			  $('#disc_amnt_' + <?php echo $key;?>).val("");
			  $(this).val("");
			  $('#checked_qtt_' + <?php echo $key;?>).attr('checked', false);
			  $('#error_div').html('Price cannot be less than the minimum allowed price for <?php echo $product->product;?>!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			  var msg = "Price cannot be less than the minimum allowed price for <?php echo $product->product;?>!";
			  $('#error_for_alert').text(msg);			  
			  //document.getElementById('error').value= msg; 
			  $( "#error_for_alert" ).dialog({
						  resizable: false,
						  height:150,
						  modal: true,
						  closeText: "Close",
						  width:400,
						  maxWidth:300,
						  title: '!!! Error!!!',
						  buttons: {
							  "OK": function() {
								  $( this ).dialog( "close" );
							  }
						  }
					  });
			  
			  //alert('Price cannot be less than the minimum allowed price for <?php echo $product['Product']['product'];?>!');
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
		}else{ ?>
			  $("<?php echo "#net_val_".$key;?>").blur(function(){
				if (parseFloat($(this).val()) < parseFloat($('#selling_price_' + <?php echo $key;?>).val())) {
				  var msg = "Discount Not applicable for <?php echo $product->product;?>!";
				  $('#error_for_alert').text(msg);
				  $('#net_val_' + <?php echo $key;?>).val('');
				   $( "#error_for_alert" ).dialog({
						  resizable: false,
						  height:150,
						  modal: true,
						  closeText: "Close",
						  width:400,
						  maxWidth:300,
						  title: '!!! Error!!!',
						  buttons: {
							  "OK": function() {
								  $( this ).dialog( "close" );
							  }
						  }
					  });
				}else if (parseFloat($(this).val()) > parseFloat($('#selling_price_' + <?php echo $key;?>).val())) {
                    $('#disc_amnt_' + <?php echo $key;?>).val('0');
					$('#discount_' + <?php echo $key;?>).val('0');
					$('#selling_price_' + <?php echo $key;?>).val(parseFloat($(this).val()));
                }
			  });
	<?php }
	  }
?>
   
	$(document).ready(function(){
	 // $('#product_div').show();
	  $('#payment_div').hide();
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
		if(custEmail == ""){
		  alert("Please Enter Customer Email");
		  return false;
		}
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
				$("#CustomerId").val(obj.id);
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
			   // alert("Error:"+response);
			   // alert("An error occurred: " + e.responseText.message);
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
				$("#CustomerId").val('');
                }else{
				   $("#Customerfname").val('');
				  $("#Customerlname").val('');
				  $("#customer_mobile").val('');
				  $("#Customeremail").val('');
				  $("#CustomerZip").val('');
				  $("#CustomerAddress1").val('');
				  $("#CustomerAddress2").val('');
				  $("#CustomerCity").val('');
				  $("#CustomerState").val('');
				  $("#CustomerId").val('');
					//do nothing  
				}
			},
			error: function(e) {
			    $.unblockUI();
			    //alert("Error:"+response);
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
		//------------
	});
	
	//-------------------------
	  });
  
</script>

<script>
	$("input[id*='Qty_']").keydown(function (event) {
		
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
		
		//if($(this).val().indexOf('.') !== -1 && event.keyCode == 190){event.preventDefault();}
        });
	
	$(document).on('click', '.make_payment', function() {
	  var targeturl = $("#payment_url").val();
	  var customer_first_name = $('#Customerfname').val();
	  var customer_last_name = $('#Customerlname').val();
	  var customer_email = $('#Customeremail').val();
	  var customer_mobile = $('#customer_mobile').val();
	  var customer_pin_code = $('#CustomerZip').val();
	  var customer_add_1 = $('#CustomerAddress1').val();
	  var customer_add_2 = $('#CustomerAddress2').val();
	  var customer_town = $('#CustomerCity').val();
	  var customer_county = $('#CustomerState').val();
	  
	  targeturl = targeturl+"?fname="+customer_first_name;
	  targeturl += "&lname="+customer_last_name;
	  targeturl += "&email="+customer_email;
	  targeturl += "&mobile="+customer_mobile;
	  targeturl += "&zip="+customer_pin_code;
	  targeturl += "&add1="+customer_add_1;
	  targeturl += "&add2="+customer_add_2;
	  targeturl += "&city="+customer_town;
	  targeturl += "&state="+customer_county;
	  
		$.blockUI({ message: 'Updating cart...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
			  var objArr = $.parseJSON(response);
			  if (objArr.hasOwnProperty('final_price')) {
                var price = objArr.final_price
				document.getElementById('final_amount').value = price;
				$('#payment_method_0').val(price);
			   // $('#due_amount').text(price);
				$('#amount_pay').text(price);
				$('#product_div').hide();
				$('#payment_div').show();
              }else if (objArr.hasOwnProperty('error')) {
				 document.getElementById('no_item').value= objArr.error;  
				$( "#no_item" ).dialog({
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
	});
</script>
<?php $update_session_ajax = $this->Url->build(["controller" => "kiosk_product_sales","action" => "update_session_ajax"]);
$unset_session_ajax = $this->Url->build(["controller" => "kiosk_product_sales","action" => "unset_session_ajax"]);
$clear_session = $this->Url->build(["controller" => "kiosk_product_sales","action" => "clear_session"]);
?>

<input type='hidden' name='update_session_ajax' id='update_session_ajax' value='<?=$update_session_ajax?>' />
<input type='hidden' name='unset_session_ajax' id='unset_session_ajax' value='<?=$unset_session_ajax?>' />
<input type='hidden' name='clear_session' id='clear_session' value='<?=$clear_session?>' />

<script>
  $(document).ready(function() {
	  <?php
	  foreach($products as $s_key => $s_val){ ?>
		$('#checked_qtt_'+<?php echo $s_key?>).change(function() {
		   var old_msg = document.getElementById('flash_msg').innerHTML;
		  if($(this).is(":checked")) { // if checked
			var qty = $('#Qty_'+<?php echo $s_key?>).val();
			if (qty <= 0) {
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
			if($('#customer_required_1').is(':checked')) {
			  var cust_id = document.getElementById('CustomerId').value;
			}else{
			  var cust_id = "";
			}
			var selling_price = $('#selling_price_'+<?php echo $s_key?>).val();
			var dis_amt = $('#disc_amnt_'+<?php echo $s_key?>).val();
			
			
			
			var net_val = $('#net_val_'+<?php echo $s_key?>).val();
			
			
			var remarks = $('#remarks_'+<?php echo $s_key?>).val();
			var product_code = $('#product_code_'+<?php echo $s_key?>).val();
			var dis_status = $('#discount_status_'+<?php echo $s_key?>).val();
			if (dis_status == 1) {
               var discount_percentage = $('#discount_'+<?php echo $s_key?>).val();
            }else{
			  var discount_percentage = 0;
			  if(net_val >= selling_price){
			  }else{
				$('#net_val_'+<?php echo $s_key?>).val("");
			  }
			}
			var product = $('#product_'+<?php echo $s_key?>).val();
			var product_id = $('#product_id_'+<?php echo $s_key?>).val();
			
			var targeturl = $("#update_session_ajax").val();
			//alert(targeturl);
			targeturl += '?prod_id='+product_id;
		    targeturl += '&qty='+qty;
			targeturl += '&dis_status='+dis_status;
			targeturl += '&product_code='+product_code;
			targeturl += '&product='+product;
			targeturl += '&dis_amt='+dis_amt;
			targeturl += '&net_val='+net_val;
			targeturl += '&remarks='+remarks;
			targeturl += '&selling_price='+selling_price;
			targeturl += '&discount_percentage='+discount_percentage;
            targeturl += '&cust_id='+cust_id;
			   
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
				document.getElementById('flash_msg').innerHTML  = "";
				//alert(objArr.basket);
				if (objArr.hasOwnProperty('basket')) {
					document.getElementById('flash_msg').innerHTML  = objArr.basket;
					$('#net_val_'+<?php echo $s_key?>).attr("disabled", "disabled");
					$('#selling_price_'+<?php echo $s_key?>).attr("disabled", "disabled");
					$('#disc_amnt_'+<?php echo $s_key?>).attr("disabled", "disabled");
					$('#Qty_'+<?php echo $s_key?>).attr("disabled", "disabled");
					$('#remarks_'+<?php echo $s_key?>).attr("disabled", "disabled");
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
			var qty = $('#Qty_'+<?php echo $s_key?>).val();
			var selling_price = $('#selling_price_'+<?php echo $s_key?>).val();
			var dis_amt = $('#disc_amnt_'+<?php echo $s_key?>).val();
			
			var discount_percentage = $('#discount_'+<?php echo $s_key?>).val();
			var net_val = $('#net_val_'+<?php echo $s_key?>).val();
			var remarks = $('#remarks_'+<?php echo $s_key?>).val();
			var product_code = $('#product_code_'+<?php echo $s_key?>).val();
			var dis_status = $('#discount_status_'+<?php echo $s_key?>).val();
			var product = $('#product_'+<?php echo $s_key?>).val();
			var product_id = $('#product_id_'+<?php echo $s_key?>).val();
			
			var targeturl = $("#unset_session_ajax").val();
			targeturl += '?product_code='+product_code;
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
				document.getElementById('flash_msg').innerHTML  = ""
				document.getElementById('flash_msg').innerHTML  = objArr.basket;
				  $('#net_val_'+<?php echo $s_key?>).removeAttr("disabled");
				  $('#selling_price_'+<?php echo $s_key?>).removeAttr("disabled");
				  $('#disc_amnt_'+<?php echo $s_key?>).removeAttr("disabled");
				  $('#Qty_'+<?php echo $s_key?>).removeAttr("disabled");
				  $('#remarks_'+<?php echo $s_key?>).removeAttr("disabled");
				  $('#error_div').html("");
				  $('#disc_amnt_'+<?php echo $s_key?>).val("");
				  $('#net_val_'+<?php echo $s_key?>).val("");
				  $('#Qty_'+<?php echo $s_key?>).val("1");
				  $('#remarks_'+<?php echo $s_key?>).val("");
				  $('#selling_price_'+<?php echo $s_key?>).val('<?php echo $s_val->selling_price?>');
				  $('#discount_'+<?php echo $s_key?>).val(0);
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
  
  $(document).on('click', '.clear_basket', function() {
	   var targeturl = $("#clear_session").val();
	   $.blockUI({ message: 'Updating cart...' });
		$.ajax({
		  type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
			  var objArr = $.parseJSON(response);
			  document.getElementById('flash_msg').innerHTML  = "";
			  document.getElementById('flash_msg').innerHTML  = objArr.basket;
			  //clear_check_box();
			  $.unblockUI();
			  document.getElementById('error_for_alert').innerHTML = "No Item In The Basket";
			  $( "#error_for_alert" ).dialog({
						  resizable: false,
						  height:140,
						  modal: true,
						  closeText: "Close",
						  width:300,
						  maxWidth:300,
						  title: '!!! No Item In The Basket!!!',
						  buttons: {
							  "OK": function() {
								  $( this ).dialog( "close" );
								  var redirect_url = $("#redirect_url").val();
								  window.location.href = redirect_url;
							  }
						  }
					  });
			},
			error: function(e) {
				 $.unblockUI();
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
  });
  
  function clear_check_box() {
     <?php foreach($products as $s_key => $s_val){ ?>
			if($('#checked_qtt_'+<?php echo $s_key?>).is(':checked')){
				  $('#checked_qtt_'+<?php echo $s_key?>).removeAttr("checked");
				  $('#net_val_'+<?php echo $s_key?>).removeAttr("disabled");
				  $('#selling_price_'+<?php echo $s_key?>).removeAttr("disabled");
				  $('#disc_amnt_'+<?php echo $s_key?>).removeAttr("disabled");
				  $('#Qty_'+<?php echo $s_key?>).removeAttr("disabled");
				  $('#remarks_'+<?php echo $s_key?>).removeAttr("disabled");
				  
				   $('#net_val_'+<?php echo $s_key?>).val('');
				  $('#selling_price_'+<?php echo $s_key?>).val('');
				  $('#disc_amnt_'+<?php echo $s_key?>).val('');
				  $('#Qty_'+<?php echo $s_key?>).val('');
				  $('#remarks_'+<?php echo $s_key?>).val('');
			  }
	 <?php } ?>
  }
  
</script>
<script>
  $(document).ready(function(){
  $('#no_item').hide();
  $('#more_qty').hide();
  $('#zero_qty').hide();
  $('#error_for_alert').hide();
  $('#sale_done').hide();
 });
</script>