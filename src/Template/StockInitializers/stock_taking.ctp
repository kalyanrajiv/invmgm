<?php
	use Cake\Core\Configure;
	use Cake\Core\Configure\Engine\PhpConfig;
	$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
	$adminDomainURL = URL_SCHEME.ADMIN_DOMAIN;
	extract($this->request->query);
	if(!isset($search_kw)){$search_kw = "";}
	$sessionBaket = $this->request->Session()->read("stock_taking_basket");
	$current_page = '';
	$stockReference = $this->request->Session()->read("stock_taking_reference");
	$s_kiosk_id = $this->request->Session()->read("stock_taking_kiosk_id");
	if( !empty($s_kiosk_id) ){
		$kiosk_id = $this->request->Session()->read("stock_taking_kiosk_id");
	}else{
		$kiosk_id = $kioskId;//$kioskId is the first kiosk in the active kiosk list, defined in controller
	}
	$update_session_ajax = $this->Url->build(["controller" => "stock-initializers","action" => "update_session_ajax"]);
	$unset_session_ajax = $this->Url->build(["controller" => "stock-initializers","action" => "unset_session_ajax"]);
?>
<style>
 #remote .tt-dropdown-menu {max-height: 250px;overflow-y: auto;}
 #remote .twitter-typehead {max-height: 250px;overflow-y: auto;}
.tt-dataset, .tt-dataset-product {max-height: 250px;overflow-y: auto;}
.row_hover:hover{color:blue;background-color:yellow;}
</style>
<input type='hidden' name='update_session_ajax' id='update_session_ajax' value='<?=$update_session_ajax?>' />
<input type='hidden' name='unset_session_ajax' id='unset_session_ajax' value='<?=$unset_session_ajax?>' />

<div id="zero_qty" title="Quantity Can't be zero">Quantity Can't be zero</div>
<div id="reference" title="Please Add Reference">Please Add Reference</div>
<div id="flash_msg" style="clear: both;color: #fff;background: #c43c35;border: 1px solid rgba(0, 0, 0, 0.5);background-repeat: repeat-x;text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.3);font-size: 19px;"><?php //echo $this->Session->flash(''); ?></div>
<div class="centralStocks index">
	<form  action="<?php echo $this->request->webroot;?>stock-initializers/search_stock_taking/<?php echo $kiosk_id;?>" method = 'get'>
		<div class="search_div">
			<fieldset>
				<legend>Search</legend>
				<table>
					<tr>
						<td>
							<?php
							//$productModels = array(0=>"test");
							if(!isset($model_id)){
								$model_id = "";
							}
							if(!isset($brand_id)){
								$brand_id = "";
							}
							
							echo $this->element('stock_taking/search',array('brands' => $brands,
																			'ProductModels' => $mobileModels,
																			'model_id' => $model_id,
																			'brand_id' => $brand_id,
														)
												  );
							?>
							</br>
							<h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4>
						<h4>
							Based on model, you might get quite few records </br> as we don't have models added for most of the products.</br> Please do search with  All  options unless models are updated for products
						</h4>
						</td>
						<td><strong>Find by category &raquo;</strong></br>
						<select id='category_dropdown' name='category[]' multiple="multiple" size='6'>
								<option value="0" <?php //if($category){ echo 'selected'; } ?>>All</option>
								<?php echo $categories;?>
							</select>
						</td>						
					</tr>
					<tr><td><div id='remote' style="width: 296px;display: inline-block;"><input class="typeahead" id = "search_kw" type = "text" value = '' name = "search_kw" placeholder = "Product title or product code" style = "width:254px"  autofocus/></div>
					<input type = "submit" name = "submit" value = "Search Product"/>
					</td>
						<td rowspan="2">
							
							<input type='button' name='reset' value='Reset' style='padding:4px 8px;background:#dcdcdc;color:#333;border:1px solid #bbb;border-radius:4px;width: 120px;height: 30px;' onClick='reset_search();'/>
						</td></tr>					
				</table>
			</fieldset>	
		</div>
	</form>
	
	<?php
	echo $this->Form->create('KioskChange',array('type'=>'post','id'=>'KioskChangeStockTakingForm','url'=>array('controller'=>'stock_initializers','action'=>'stock_taking')));
		echo $this->Form->input('kiosk',array('type'=>'hidden','value'=>$kioskId));
		echo $this->Form->input('current_page',array('type'=>'hidden','value'=>$current_page));
	echo $this->Form->end();
	?>
	<?php
			$screenHint = $hintId = "";
					if(!empty($hint)){
						
					   $screenHint = $hint["hint"];
					   $hintId = $hint["id"];
					}
					$updateUrl = "/img/16_edit_page.png";
	?>
	<h2><?php echo __('Stock Taking')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>				
	</h2>
	<h4>You have <?php echo count($sessionBaket);?> item(s) in the cart</h4>
	
	<?php echo $this->Form->create(null,array('url' => array('controller' => 'stock_initializers','action' => 'record_stock'))); ?>
	<table>
		<tr>
			<td style="width: 25%;"><span><strong>Kiosk</strong><span style='color:red'><sup>*</sup></span> <?php
			//if( $this->request->session()->read('Auth.User.group_id')== MANAGERS){
			//	echo $this->Form->input(null, array(
			//						       'options' => $manager_kiosks,
			//						       'label' => false,
			//						       'div' => false,
			//						       'name' => 'KioskStock[selected_kiosk]',
			//						       'value' => $kioskId,
			//						       'onChange' => "select_change();",
			//							   'id' => 'Product'
			//							   //'onchange'=>"this.form.submit()"
			//						       )
			//					   );	
			//}else{
				echo $this->Form->input(null, array(
									       'options' => $kiosks,
									       'label' => false,
									       'div' => false,
									       'name' => 'KioskStock[selected_kiosk]',
									       'value' => $kioskId,
									       'onChange' => "select_change();",
										   'id' => 'Product'
										   //'onchange'=>"this.form.submit()"
									       )
								   );
			//}
			
			?></span></td>
			<td style="width: 25%;margin-top: -22px;float: left;">
				<span><?php echo $this->Form->input('stock_taking_reference',array('type'=>'text','value'=>$stockReference))?></span>
			</td>
			<td><?=$this->Html->link('Restore Session', array('action' => 'restore_session',"StockInitializers", "stock_taking", 'stock_taking_basket', ''));?></td>
		</tr>
	</table>
	
	<span>&nbsp;</span>
	
	<div class="submit">
		<table style="width:100%">
			<tr>
				<td>
					
					<input type="submit" name='Dispatch1' value="Process Stock" onclick="return inputReference();"/>
					<?php
						//if($_SERVER['REMOTE_ADDR'] == '124.253.58.119'){
							echo "<input type='submit' name='checkout' value='Checkout' />";
						//}
					?>
				</td>
				<td>
					<span class='paging'>
						<?php
							echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
							echo $this->Paginator->numbers(array('separator' => ''));
							echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
						?>
					</span>
				</td>
				<td>
					<input type="submit" name='empty_basket' value="Clear the Kiosk"/>
				</td>
			</tr>
		</table>
	</div>
	
	<table cellpadding="0" cellspacing="0" onLoad="window.scrollBy(0,100)">	
		<tr><td colspan='8'><hr></td></tr>
		<tr>
                        <th><?php echo $this->Paginator->sort('product_code'); ?></th>
			<th><?php echo $this->Paginator->sort('product_id'); ?></th>
                        <th><?php echo $this->Paginator->sort('color'); ?></th>
			<th>Image</th>
			
			
			<th><?php echo $this->Paginator->sort('quantity','Current'); ?>&nbsp;<strong>Quantity</strong></th>
			
			<th>Quantity</th>			
			
		</tr>	
	<tbody>
	<?php $currentPageNumber = $this->Paginator->current();?>
	<a href="#start">access within the same page</a>
	<?php
	
	$groupStr = "";
	foreach ($centralStocks as $key => $centralStock):
	$checked = false;
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
		$largeImageName = 'vga_'.$imageName;
		$largeImageURL = $imageURL = "/thumb_no-image.png";
		if(!empty($imageName)){
			$imageURL = $adminDomainURL.'/files/Products/image/'.$centralStock->id.DS."thumb_".$imageName;
			$largeImageURL = $adminDomainURL.'/files/Products/image/'.$centralStock->id.DS.$largeImageName;
		}
		$sellingPrice = $centralStock->selling_price;
		$productQuantity = '';
		//$difference = 'less';
		//$productPrice = $centralStock['Product']['cost_price'];
		//$productRemarks = "";
		if( count($sessionBaket) >= 1){
			
			if(array_key_exists($centralStock->product_code,$sessionBaket)){
				$checked = true;
				$productQuantity = $sessionBaket[$centralStock->product_code]['quantity'];
				//$difference = $sessionBaket[$centralStock['Product']['id']]['difference'];
				//$productRemarks = $sessionBaket[$centralStock['Product']['id']]['remarks'];
				//if($productQuantity<0){
				//	$productQuantity = -$productQuantity;
				//}
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
					array('escapeTitle' => false, 'title' => $centralStock->product,'id' => "tooltip_{$centralStock->id}")
				);
			?>
		</td>
                <td><?php echo $centralStock->color;?></td>
		<td><?php
			echo $this->Html->link(
					$this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')),
					$largeImageURL,
					array('escapeTitle' => false, 'title' => $centralStock->product,"class" => "group{$key}")
				);
			?>
		</td>
	
		
		<td><?php
                        echo $this->Form->input(null,array(
							'id' => "org_qty_".$key,
					'type' => 'hidden',
					'name' => "KioskStock[p_quantity][$key]",
					'value' => $centralStock->quantity,
					'label' => false,
					'style' => 'width:80px;'
				)
					       );
						echo $codeArr[$centralStock->product_code];
                        echo $this->Form->input(null,array(
							'id' => "code_".$key,
					'type' => 'hidden',
					'name' => "KioskStock[product_code][$key]",
					'value' => $centralStock->product_code
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
					'value' => 1,
					'readonly' => false
					)
				);
			?>
                </td>
		<td>
			<?php
			echo $this->Form->input(null,array(
                                    'type' => 'checkbox',
                                    'name' => "KioskStock[item][$key]",
                                    'value' => $centralStock->id,
                                    'label' => false,
                                    'style' => 'height:18px; margin-top:8px; transform:scale(1.5);',
                                    'readonly' => false,
									'id' => "checked_qtt_$key",
				    'div'=>false,
				    'checked' => $checked
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
		
		<input type='submit' name='checkout' value='Checkout' />
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

<script>
	function inputReference(){
		if(document.getElementById("stock-taking-reference").value == null || document.getElementById("stock-taking-reference").value == ""){
			alert("Please input the reference number!");
			return false;
		}else{
		 $.blockUI({ message: 'Just a moment...' });
		}
	}
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
    
    suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:400px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{product_code}}</a></strong></div>'),
	header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------<?php echo ADMIN_DOMAIN;?>---------</b></div>"),
  }
});
</script>
<script>
	$('#Product').change(function(){
		<?php $session_basket = $this->request->Session()->read('stock_taking_basket');
		if(empty($session_basket)){ ?>
			var bkt = 0;
		<?php }else{ ?>
			var bkt = 1;
		<?php } ?>
		
		if (bkt) {
			//alert("Are you sure you want to change kiosk? Changing kiosk will empty the current basket");
		}
	});
	
	$("#add_upper").click(function(){
		var reference = $("#stock_taking_reference").val();
		if (reference == "") {
			alert("Please add stock taking reference");
			return false;
		}
	});
	
	$("#add_lower").click(function(){
		var reference = $("#stock_taking_reference").val();
		if (reference == "") {
			alert("Please add stock taking reference");
			return false;
		}
	});
	
	$("#upper_checkout").click(function(){
		<?php $session_basket = $this->request->Session()->read('stock_taking_basket');
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
		<?php $session_basket = $this->request->Session()->read('stock_taking_basket');
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
<script>
	
	function select_change() {
		var z = document.getElementById("Product").value;

		var y = document.getElementById("KioskChangeStockTakingForm").action;
		//alert(y);
		var newAction = y+'/'+z;
		document.getElementById("KioskChangeStockTakingForm").action = newAction;
		document.getElementById("KioskChangeStockTakingForm").submit();
	}
	
</script>
<script type="text/javascript">
<?php
   foreach ($centralStocks as $centralStock):
      $string = preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($centralStock->product));
     if(empty($string)){
     echo  $string = $centralStock->product;
     }
      echo "jQuery('#tooltip_{$centralStock->id}').tooltip({content:\"".htmlspecialchars($string)."\",track:true});";
    endforeach;
?>
</script>
<script>
	$("#stock-taking-reference").keydown(function (event) {
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
			(event.keyCode >= 65 && event.keyCode <= 90) || 
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||
		event.keyCode == 46  || event.keyCode == 183 ||
		event.keyCode == 110 || event.keyCode == 32 ||
		event.keyCode == 173 || event.keyCode == 190
		) {
			if (event.shiftKey == true) {
                if ((event.keyCode >= 65 && event.keyCode <= 90) || (event.keyCode == 173)
					) {
                    
                }else{
					event.preventDefault();
				}
            }
			//
			;
			//48-57 => 0..9
			//8 => Backspace; 9 => tab 
		} else {
			event.preventDefault();
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
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>

<script>
   $('#Product').change(function(){
	$.blockUI({ message: 'Loading ...' });
	document.getElementById("KioskChangeStockTakingForm").submit();
	  }); 
</script>
<script>
	 $(document).ready(function() {
	  <?php
	  foreach($centralStocks as $s_key => $s_val){ ?>
		$('#checked_qtt_'+<?php echo $s_key?>).change(function() {
			var product_id = $(this).val();
			var product_name = ($("td #tooltip_"+product_id).attr('title'));
		   var old_msg = document.getElementById('flash_msg').innerHTML;
		  if($(this).is(":checked")) { // if checked
			var qty = $('#in_qty_'+<?php echo $s_key?>).val();
			if (qty == "") {
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
			var reference = $('#stock-taking-reference').val();
			if (reference == "") {
                 $( "#reference" ).dialog({
						  resizable: false,
						  height:140,
						  modal: true,
						  closeText: "Close",
						  width:300,
						  maxWidth:300,
						  title: '!!! Please Add Reference!!!',
						  buttons: {
							  "OK": function() {
								  $( this ).dialog( "close" );
							  }
						  }
					  });
				 $('#checked_qtt_'+<?php echo $s_key?>).removeAttr("checked");
				 return false;
            }
			
			
			var org_qty = $('#org_qty_'+<?php echo $s_key?>).val();
			var product_code = $('#code_'+<?php echo $s_key?>).val();
			var kiosk_id = $('#Product').val();
			
			var targeturl = $("#update_session_ajax").val();
			//alert(targeturl);
			targeturl += '?prod_code='+product_code;
		    targeturl += '&qty='+qty;
			targeturl += '&org_qty='+org_qty;
			targeturl += '&reference='+reference;
			targeturl += '&kiosk_id='+kiosk_id;
			
			   
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
				$.unblockUI();
				//alert(objArr.basket);
				if (objArr.hasOwnProperty('basket')) {
					document.getElementById('flash_msg').innerHTML  = objArr.basket;
					$('#in_qty_'+<?php echo $s_key?>).attr("disabled", "disabled");
					//$('#search_kw').focus();
					$('#error_div').html("");
					$.blockUI({ css:{fontSize:'20px'}, message: product_name+' is successfully added to your cart' });
					setTimeout(function(){
						$.unblockUI();
					}, 1200);
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
					//$('#search_kw').focus();
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
				  //$.unblockUI();
			  },
			  error: function(e) {
				  $.unblockUI();
				  //alert("An error occurred: " + e.responseText.message);
				  //$('#search_kw').focus();
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
			var org_qty = $('#org_qty_'+<?php echo $s_key?>).val();
			var product_code = $('#code_'+<?php echo $s_key?>).val();
			var kiosk_id = $('#Product').val();
			var reference = $('#stock-taking-reference').val();
			var qty = $('#in_qty_'+<?php echo $s_key?>).val();
			
			var targeturl = $("#unset_session_ajax").val();
			targeturl += '?prod_code='+product_code;
			targeturl += '&qty='+qty;
			targeturl += '&org_qty='+org_qty;
			targeturl += '&reference='+reference;
			targeturl += '&kiosk_id='+kiosk_id;
			
			
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
				//$('#search_kw').focus();
				document.getElementById('flash_msg').innerHTML  = ""
				document.getElementById('flash_msg').innerHTML  = objArr.basket;
				$('#in_qty_'+<?php echo $s_key?>).removeAttr("disabled");
				$('#error_div').html("");
				$.unblockUI();
				$.blockUI({ css:{fontSize:'20px'}, message: product_name+' is successfully removed from your cart' });
				setTimeout(function(){
					$.unblockUI();
				}, 1200);
			   },
			   error: function(e) {
				  $.unblockUI();
				  //$('#search_kw').focus();
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
</script>
<script>
	$('#brand-id').change(function(){
		var id = $(this).val();
		var targetUrl = $(this).attr('rel') + '?id=' + id;
		$.blockUI({ message: 'Just a moment...' });
		if (id == -1) {
			//$('#model-td').hide();
			var append_txt = "<option value=0>No Option Available</option>";
			$('#model-id').empty();
			$('#model-id').append(append_txt);
			$.unblockUI();
            return false;
        }
		$.ajaxSetup({
		url: targetUrl,
			success: function(result){
				$.unblockUI();
				$('#model-td').show();
			$('#model-id').empty();
			$('#model-id').append(result);
			}
		});
		$.ajax();
	});
</script>
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
	function reset_search(){
		jQuery( "#category_dropdown" ).val("0");
		jQuery( "#brand-id" ).val("-1");
		jQuery("#model-id").val("-1");
		jQuery('#model-td').hide();
		jQuery("#search_kw").val("");
	}
</script>