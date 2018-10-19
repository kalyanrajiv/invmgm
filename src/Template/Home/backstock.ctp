<style>
@-webkit-keyframes blink {
    50% {background: rgba(255, 0, 0, 0.5);}
}
@-moz-keyframes blink {
    50% {background: rgba(255, 0, 0, 0.5);}
}
@keyframes blink {
    50% {background: rgba(255, 0, 0, 0.5);}
}
.blink {
    -webkit-animation-direction: normal;
    -webkit-animation-duration: 5s;
    -webkit-animation-iteration-count: infinite;
    -webkit-animation-name: blink;
    -webkit-animation-timing-function: linear;
    -moz-animation-direction: normal;
    -moz-animation-duration: 5s;
    -moz-animation-iteration-count: infinite;
    -moz-animation-name: blink;
    -moz-animation-timing-function: linear;
    animation-direction: normal;
    animation-duration: 5s;
    animation-iteration-count: infinite;
    animation-name: blink;
    animation-timing-function: linear;
}
</style>
<div class="blink" style="width: 185px;">
<h3 >Back Stock(<?php echo $back_stock_count;?>) &raquo; </h3>
</div>
<?php
    use Cake\Core\Configure;
    use Cake\Core\Configure\Engine\PhpConfig;
    use Cake\I18n\Time;
    $siteBaseURL = $main_domain = Configure::read('SITE_BASE_URL');
    $current_url = "http://".$_SERVER['HTTP_HOST'];
	$path = realpath(dirname(__FILE__));
    $adminSite = false;
    if (strpos($path, ADMIN_DOMAIN) !== false) {$adminSite = TRUE;}
    $group1Str = $group2Str = "";
	//replace WWW_ROOT by this code because of sub-domain or add it to config
?>
<div id="flash_msg" style="clear: both;color: #fff;background: #c43c35;border: 1px solid rgba(0, 0, 0, 0.5);background-repeat: repeat-x;text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.3);font-size: 19px;"><?php if(!empty(trim($basketStr))){ echo $basketStr; } ?></div>
<div id="zero_qty" title="Quantity Can't be zero">Quantity Can't be zero</div>
<div id="error_for_alert" title="Error">Error</div>

<a href="#" id="clear" style="float: right;margin-bottom: 10px;margin-top: 13px;font-size: 18px;">Clear Session</a>&nbsp;&nbsp;
<a href="#" id="create" style="float: right;margin-bottom: 10px;margin-top: 13px;font-size: 18px;margin-right: 27px;">Create Demand Order</a> 
<?php
    $update_session_ajax = $this->Url->build(["controller" => "home","action" => "update_session_ajax_backstock"]);
    $unset_session_ajax = $this->Url->build(["controller" => "home","action" => "unset_session_ajax_backstock"]);
    $clear_cart = $this->Url->build(['controller' => 'home', 'action' => 'clear_cart_backstock'],true);
    $create_order = $this->Url->build(['controller' => 'home', 'action' => 'create_order_backstock'],true);
?>

<input type='hidden' name='update_session_ajax' id='update_session_ajax' value='<?=$update_session_ajax?>' />
<input type='hidden' name='unset_session_ajax' id='unset_session_ajax' value='<?=$unset_session_ajax?>' />
<input type='hidden' name='clear_cart' id='clear_cart' value='<?=$clear_cart?>' />
<input type='hidden' name='create_order' id='create_order' value='<?=$create_order?>' />

<?php
    if($productNofification){
        $tableHTML = "";
        $tableHTML1 = "";
        $count = count($productNofification);
        $halfCount = $count/2;
        $firstHalf = array_slice($productNofification,0,$halfCount,true);
        $secondHalf = array_slice($productNofification,$halfCount,$count,true);
        
        foreach($firstHalf as $key => $productNotice){
            $group1Str.="\n$(\".group1{$key}\").colorbox({rel:'group1{$key}'});";
            $selling_price = 0;
            $imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$productNotice['id'].DS;
            $imageName = $productNotice['image'];
            $largeImageName = 'vga_'.$imageName;
            
            $absoluteImagePath = $imageDir.$imageName;
            $imageURL = "/thumb_no-image.png";
            if(@readlink($absoluteImagePath) ||file_exists($absoluteImagePath)){
                $imageURL = "{$siteBaseURL}/files/Products/image/".$productNotice['id']."/$imageName";
            }
            $imageURL = "/thumb_no-image.png";
            $largeImageURL = $imageURL;    
            if(@readlink($absoluteImagePath) ||file_exists($absoluteImagePath)){
                $imageURL = "$siteBaseURL/files/Products/image/".$productNotice['id'].DS."thumb_".$imageName;
                $largeImageURL = "$siteBaseURL/files/Products/image/".$productNotice['id'].DS.$largeImageName; //rasu
            }
            $image =  $this->Html->link(
                                        $this->Html->image($imageURL, array(
                                                                            'fullBase' => true,
                                                                            'escapeTitle' => false,
                                                                            'style' => 'width:80px;height:80px;',
                                                                            'title' => $productNotice['Product'])
                                                                            ),
                                                            $largeImageURL,
                                                            array('escapeTitle' => false, 'title' => $productNotice['Product'],'class' => "group1{$key}")
                                        );
					
            $created =  $productNotice['back_stock_time'];
            $created = date("d-m-y h:i a",strtotime($created)); 
            $product_id = $productNotice['id'];
            $product_code = $productNotice['product_code'];
            $quantity = $productNotice['quantity'];
            $disable = $checked = "";
            $qty = 1;
            
            if(!empty($session_basket)){
                if(array_key_exists((int)$product_code,$session_basket)){
                    
                    $checked = "checked";
                    $disable = "disabled='disabled'";
                    $qty = $session_basket[trim($product_code)]['quantity'];
                }
            }
            
            $selling_price = $selling_price_arr[$productNotice['id']];
            $withVATSP = $selling_price;//The gross price, including VAT.
            $vatDivisor = 1 + ($vat / 100);	//Divisor (for our math).
            $priceBeforeVat = $withVATSP / $vatDivisor; //Determine the price before VAT.
            $vatAmount = $withVATSP - $priceBeforeVat;
            if(!$adminSite){$priceBeforeVat =$withVATSP;}
                
            $tableHTML .= <<<TABLE
                    <tr>
                        <td>&raquo; </td>
                        <td>$image</td>
                        <td valign='center'>
                              {$productNotice['Product']} with the product-code:{$productNotice['product_code']}<br/>
                            
                            Price : {$CURRENCY_TYPE}{$priceBeforeVat}<br/>
                            Restocked On: {$created}</br>
                            Qty : {$quantity}
                            </br></br>
                            <input type = "hidden" id ="price_$product_id" value={$selling_price} />
                            <input type = "hidden"  id ="code_$product_id" value={$productNotice['product_code']} />
                            <input type="text" name= "qty" id="qty_$product_id" style="width: 28px;height: 7px;" value=$qty $disable />
                            <input type="checkbox" name="check_box" id="check_$product_id" $checked style="transform: scale(1.5);" />
                        </td></td>
                    </tr>
TABLE;
			}
			
			foreach($secondHalf as $key => $productNotice){
                $group2Str.="\n$(\".group2{$key}\").colorbox({rel:'group2{$key}'});";
				$selling_price = 0;
				$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$productNotice['id'].DS;
				$imageName =  $productNotice['image'];
                $largeImageName = 'vga_'.$imageName;
                $absoluteImagePath = $imageDir.$imageName;
				$imageURL = "/thumb_no-image.png";
				if(@readlink($absoluteImagePath) ||file_exists($absoluteImagePath)){
					$imageURL = "{$siteBaseURL}/files/Products/image/".$productNotice['id']."/$imageName";
				}
                
				$imageURL = "/thumb_no-image.png";
				$largeImageURL = $imageURL;    
				if(@readlink($absoluteImagePath) ||file_exists($absoluteImagePath)){
                      $imageURL = "$siteBaseURL/files/Products/image/".$productNotice['id'].DS."thumb_".$imageName;
					  $largeImageURL = "$siteBaseURL/files/Products/image/".$productNotice['id'].DS.$largeImageName; //rasu
				}
				$image =  $this->Html->link(
                                            $this->Html->image($imageURL, array(
                                                                                'fullBase' => true,
                                                                                'escapeTitle' => false,
                                                                                'style' => 'width:80px;height:80px;',
                                                                                'title' => $productNotice['Product'])
                                                               ),
                                            $largeImageURL,
                                            array('escapeTitle' => false, 'title' => $productNotice['Product'],'class' => "group2{$key}")
                                        );
                
                $created =  $productNotice['back_stock_time'];
                $created = date("d-m-y h:i a",strtotime($created)); 
				$product_id = $productNotice['id'];
				$product_code = $productNotice['product_code'];
				$quantity = $productNotice['quantity'];
				$disable = $checked = "";
				$qty = 1;
                
				if(!empty($session_basket)){
					if(array_key_exists((int)$product_code,$session_basket)){
						$checked = "checked";
						$disable = "disabled='disabled'";
						$qty = $session_basket[$product_code]['quantity'];
					}
				}
                
				$selling_price = $selling_price_arr[$productNotice['id']];
				$withVATSP = $selling_price;//The gross price, including VAT.
				$vatDivisor = 1 + ($vat / 100);	//Divisor (for our math).
				$priceBeforeVat = $withVATSP / $vatDivisor; //Determine the price before VAT.
				$vatAmount = $withVATSP - $priceBeforeVat;
				if(!$adminSite){$priceBeforeVat =$withVATSP;}
                
				$tableHTML1 .= <<<TABLE1
						<tr>
							<td>&raquo; </td>
							<td>$image</td>
							<td valign='center'>
								  {$productNotice['Product']} with the product-code:{$productNotice['product_code']}<br/>
								
								Price : {$CURRENCY_TYPE}{$priceBeforeVat}<br/>
								Restocked On: {$created}</br>
								Qty : {$quantity}
								</br></br>
								
								<input type = "hidden" id ="price_$product_id" value={$selling_price} />
								<input type = "hidden" id ="code_$product_id" value={$productNotice['product_code']} />
								<input type="text" name= "qty" id="qty_$product_id" style="width: 28px;height: 7px;" value=$qty $disable />
								<input type="checkbox" name="check_box" id="check_$product_id" $checked style="transform: scale(1.5);" />
								
							</td></td>
						</tr>
TABLE1;
			}
		
		echo "<table>
		<form action
			<tr>
				<input type='hidden' id='kiosk_id' value=1 />
			    <td><table cellspacing='0' cellpadding='0' width ='600' style='width:700px;'>$tableHTML1</table></td>
			    <td><table cellspacing='0' cellpadding='0' width ='600' style='width:700px;'>$tableHTML</table></td>
			</tr>
		    </table>";
    }else{
		echo "<h4>No products!</h4>";
    }  
?>
<?php if($main_domain != $current_url){?>
<script>
	 $(document).ready(function() {
	  <?php
	  foreach($productNofification as $s_key => $s_val){ ?>
		$('#check_'+<?php echo $s_val['id']?>).change(function() {
		   var old_msg = document.getElementById('flash_msg').innerHTML;
		  if($(this).is(":checked")) { // if checked
			var qty = $('#qty_'+<?php echo $s_val['id']?>).val();
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
				$('#check_'+<?php echo $s_val['id']?>).removeAttr("checked");
				return false;
            }
			
			
			var product_id = <?php echo $s_val['id']?>;
			
			var product_price = $('#price_'+<?php echo $s_val['id']?>).val();
			var product_code = $('#code_'+<?php echo $s_val['id']?>).val();
			
			var kiosk_id = $('#kiosk_id').val();
			
			var targeturl = $("#update_session_ajax").val();
			//alert(targeturl);
			targeturl += '?prod_code='+product_code;
		    targeturl += '&qty='+qty;
			//targeturl += '&org_qty='+org_qty;
			
			targeturl += '&kiosk_id='+kiosk_id;
			targeturl += '&product_price='+product_price;
			targeturl += '&product_id='+product_id;
			
			   
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
					$('#qty_'+<?php echo $s_val['id']?>).attr("disabled", "disabled");
					$('#error_div').html("");
				}else if (objArr.hasOwnProperty('error')) {
                    document.getElementById('flash_msg').innerHTML  = old_msg;
					document.getElementById('more_qty').innerHTML  = objArr.error;
					$('#net_val_'+<?php echo $s_val['id']?>).val("");
					//$('#selling_price_'+<?php echo $s_key?>).val("");
					$('#disc_amnt_'+<?php echo $s_val['id']?>).val("");
					var org_qty = $('#p_org_quantity_'+<?php echo $s_val['id']?>).val();
					//alert(org_qty);
					$('#Qty_'+<?php echo $s_val['id']?>).val(org_qty);
					$('#remarks_'+<?php echo $s_val['id']?>).val("");
					
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
			var product_code = $('#code_'+<?php echo $s_val['id']?>).val();
			var qty = $('#qty_'+<?php echo $s_val['id']?>).val();
			var product_id = <?php echo $s_val['id']?>;
			var product_price = $('#price_'+<?php echo $s_val['id']?>).val();
			
			var targeturl = $("#unset_session_ajax").val();
			targeturl += '?prod_code='+product_code;
			targeturl += '&qty='+qty;
			targeturl += '&product_id='+product_id;
			targeturl += '&product_price='+product_price;
			
			
			$.blockUI({ message: 'Updating cart...' });
			$.ajax({
			   type: 'get',
			  url: targeturl,
			  beforeSend: function(xhr) {
				  xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			  },
			   success: function(response) {
				var objArr = $.parseJSON(response);
				
				document.getElementById('flash_msg').innerHTML  = ""
				document.getElementById('flash_msg').innerHTML  = objArr.basket;
				  $('#qty_'+<?php echo $s_val['id']?>).removeAttr("disabled");
				   $('#qty_'+<?php echo $s_val['id']?>).val(1);
				  $('#error_div').html("");
				  $.unblockUI();
			   },
			   error: function(e) {
				  $.unblockUI();
				  
				  var msg = "An error occurred: " + e.responseText.message;
				  document.getElementById('error_for_alert').innerHTML = msg;
				  
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
	$(document).on('click', '#clear', function() {
			var targeturl = $("#clear_cart").val();
			$.blockUI({ message: 'Updating cart...' });
			$.ajax({
				type: 'get',
				url: targeturl,
				beforeSend: function(xhr) {
					xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				},
				
				success: function(response) {
						var objArr = $.parseJSON(response);
						document.getElementById('flash_msg').innerHTML  = ""
						document.getElementById('flash_msg').innerHTML  = objArr.basket;
						$("input[name^='qty']").removeAttr("disabled");
						$("input[name^='qty']").val(1);
						$("input[name^='check_box']").removeAttr("checked");
						
						 
						  $.unblockUI();
			   },
			    error: function(e) {
					$.unblockUI();
					
					var msg = "An error occurred: " + e.responseText.message;
					document.getElementById('error_for_alert').innerHTML = msg;
					
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
</script>
<script>
	$(document).on('click', '#create', function() {
		var targeturl = $("#create_order").val();
		$.blockUI({ message: 'Updating cart...' });
		$.ajax({
				type: 'get',
				url: targeturl,
				beforeSend: function(xhr) {
					xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				},
				success: function(response) {
						var objArr = $.parseJSON(response);
						document.getElementById('flash_msg').innerHTML  = ""
						document.getElementById('flash_msg').innerHTML  = objArr.basket;
						$("input[name^='qty']").removeAttr("disabled");
						$("input[name^='qty']").val(1);
						$("input[name^='check_box']").removeAttr("checked");
						  $.unblockUI();
			   },
				 error: function(e) {
					$.unblockUI();
					
					var msg = "An error occurred: " + e.responseText.message;
					document.getElementById('error_for_alert').innerHTML = msg;
					
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
</script>
<?php }?>
<script>
	$(document).ready(function(){
  $('#no_item').hide();
  $('#more_qty').hide();
  $('#zero_qty').hide();
  $('#error_for_alert').hide();
  $('#reference').hide();
  
  var brand_val = $('#brand-id').val();
  if (brand_val == -1) {
	//$('#model-td').hide();  
  }
 });
</script>
<script>
	$(document).ready(function(){
	<?php echo $group1Str;?>
	<?php echo $group2Str;?>
	});
</script>