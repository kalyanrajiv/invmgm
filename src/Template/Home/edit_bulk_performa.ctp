<style>
    .ui-tooltip{max-width: 800px !important;width: auto !important;overflow:auto !important;}
    .ui-tooltip-content{background-color: #fdf8ef;}
    #remote .tt-dropdown-menu {max-height: 250px;overflow-y: auto;}
    #remote .twitter-typehead {max-height: 250px;overflow-y: auto;}
    .tt-dataset, .tt-dataset-product {max-height: 250px;overflow-y: auto;}
    .row_hover:hover{color:blue;background-color:yellow;}
</style>
<style>
	/*.ui-draggable {width: 500px !important;}*/
	.ui-dialog .ui-dialog-content {
		height: auto !important;
	}
	.ui-dialog-titlebar-close {
		visibility: hidden;
	      }
</style>
<div id="dialog-confirm1" title="Special Note For Customer" style="width: 500px !important;">
 <?php echo $customer['memo'] ;?>
</div>
<?php
	 use Cake\Core\Configure;
    use Cake\Core\Configure\Engine\PhpConfig;
    $currency = Configure::read('CURRENCY_TYPE');
   // $siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
    $webRoot = $this->request->webroot;
     $priceURL = $this->Url->build(["controller" => "home","action" => "get_products_json_by_title"],true);
	//$priceURL = $this->Html->url(array('controller' => 'home', 'action' => 'get_products_json_by_title'));
    $priceTitleURL = $this->Url->build(['controller' => 'home', 'action' => 'get_products_json_by_title'],true);
	$save_invoice = $this->Url->build(['controller' => 'home', 'action' => 'pf_save_invoice'],true);
	$restore_session_db = $this->Url->build(['controller' => 'home', 'action' => 'pf_restore_session_db'],true);
	$update_bulk = $this->Url->build(['controller' => 'home', 'action' => 'pf_update_bulk'],true);
	
	$add_2_cart_full = $this->Url->build(['controller' => 'home', 'action' => 'pf_add_2_cart_full'],true);
	$update_cart = $this->Url->build(['controller' => 'home', 'action' => 'pf_update_cart'],true);
	$delte_cart = $this->Url->build(['controller' => 'home', 'action' => 'pf_delete_from_cart'],true);
	$cartURL = $add_2_cart_short = $this->Url->build(['controller' => 'home', 'action' => 'pf_add_2_cart_short'],true);
	$restore_cart = $this->Url->build(['controller' => 'home', 'action' => 'pf_restore_cart'],true);
	$clear_cart = $this->Url->build(['controller' => 'home', 'action' => 'pf_clear_cart'],true);
?>
<div class="index" style='width: 97%; background-color: #F0F8FF; padding-left: 10px;'>
    <h1 style='color:blue;  font-size: 250%; background-color: #F0F8FF;'>&raquo;Edit Performa</h1>
    <table width='100%'>
    <tr>
        <td width='50%'>
		<table width='100%'>
			<tr><td style='width=290px;' nowrap='nowrap'><a href='#-1' id='rest_sess'>Restore Session</a> | <a href='#-1' id ='rest_sess_db' title='Are you sure you want to session From Db?'>Restore Session From Db</a></td><td style='text-align: right;'><strong>Invoice Amt(Excl VAT): </strong><span style='background-color: yellow;' id='invoice_amount'>#####</span></td></tr><tr><td></td><td style="padding-left: 138px;"><strong>Amt(With Bulk Discnt): </strong><span style='background-color: yellow;' id='invoice_amount_with_bulk'>#####</span></td></tr>
		</table>
        <table width='100%' style="font-size: 10px;">
            <tr>
                <td>
                    <fieldset>	    
                    <legend>Search</legend>
						<?php
							$customerFullName = implode(" ", array($customer['fname'], $customer['lname']));
							
						?>
						<table style="font-size: 10px;">
							<tr>
								<td style="width: 83px"><strong>Customer Id</strong></td>
								<td style="width: 95px"><?= $customer['id'] ?></td>
								<td style="width: 200px" colspan='1'>
									<strong>
									<?php
										if(!empty($customerFullName)){
											echo $customerFullName;
										}
										if(!empty($customer['business'])){
											echo "[".$customer['business']."]";
										}
									?>
									</strong>
								</td>
								<td><a id='openwindow' style="cursor: pointer;"><i>Special Note For Customer</i>.</a></td>
							</tr>
							<tr>
								<td><b>Bulk Discnt</b></td>
								<td>
									<input type='text' name='bulk_discount' id='bulk_discount' value='' placeholder='bulk discnt' style='width:70px;'/></br>
									<a class='update_bulk_discount' href=#>update_bulk_discount</a>
								</td>
								<?php 
								$loggedInUser = $this->request->session()->read('Auth.User.username');
								 if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
									if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){ ?>
										<?php }
								}else{
									$type = $this->request->session()->read('Auth.User.user_type');
									if($type == "wholesale"){
										if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){ ?>
										<?php }
									}
								}?>
								<td>
									<table style='width:190px;'>
										<tr>
											<td style="width: 100px;">Receipt Required?</td>
											<td style="width: 40px;">Yes <input type="radio" name="receipt_required" id='receipt_required_1' value="1"></td>
											<td style="width: 40px;">No <input type="radio" name="receipt_required" id='receipt_required_0' value="0" checked='checked'></td>
										</tr>
									</table>
								</td>
							 </tr>
						</table>
						
                        <table>
                            
						<table>
							<tr>
								<td style="width: 30px"><b>Qty:</b></td>
								<td style="width: 74px"><input type = "text" value = '1' name = "quantity" id='scanner_qty' placeholder = "Quantity" style = "width:29px;height:20px;" autofocus/></td>
								<td style="width: 165px">
									<form id = 'scanner_form' onsubmit='return false;' method='post'>
										<div id='remote_cart'><input rel = '<?php echo $cartURL;?>' id='scanner_input' class="typeahead" type="text" value='' name = "search_kw" placeholder = "Product Code or Product Title" style="width:160px;height:20px;" autofocus/></div>
										<input type="submit" value='submit' name='submit2' style='display: none;'/>
									</form>
								</td>
								<td><a href='#-1' id='Scan_Code' title='Product Code or Product Title'>Scan Code</a></td>
								<td><?php
								if($customer['agent_id'] !=0){
								echo "<b style=background-color:yellow;font-size:17px;>".$agents[$customer['agent_id']]."</b>";
								}?>	
								</td>
							</tr>
						</table>
							
						
							<table>
								<tr>
									<td style='width:300px;'>
										<form id = 'search_form' onsubmit='return false;' method='post'>
											<div id='remote'><input class="typeahead" id='search_prod' type="text" value = '' name = "search_kw" placeholder = "Product Code or Product Title" style = "width:165px;height:20px;" autofocus/><span style="padding-left: 10px;padding-top: 5px"><a href='#-1' id='check_price' title='Product Code or Product Title'>Check Price</a></span></div>
											<td style="width: 74px" style='margin-top: 15px;'></td>
											<input type="submit" value='submit' name='submit2' style='display: none;'/>
										</form>
									</td>
									<td><a href='#-1' id='get_result' rel='<?php echo $priceURL;?>' style='display: none;'>Get Price</a>
									<a href='#-1' id='get_result_by_title' rel='<?php echo $priceTitleURL;?>' style='display: none;'>Get Price</a></td>
									<td rowspan="3"> <strong>Find by category &raquo;</strong><select style="font-size: 9px;" id='category_dropdown' name='category[]' multiple="multiple" size='6'><?=$categories?></select></td>
								</tr>
								<tr> <td rowspan="2"><h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4></td></tr>
								<tr><td colspan='2'><span style='padding-left: 5px;'><a href='#-1' id='clear_search'>Clear Search</a></span></td></tr>
							</table>
                    </fieldset>
                </td>
            </tr>
        </table>
		<div id='searchOuterDiv' style='display: none;'>
			<table style='width:100%'>
				<tr>
					<td>
						<fieldset>	    
							<legend>Search Results</legend>
							<div id='searchDiv' style="overflow: scroll; width: 580px; height: 200px; font-size: 9px;">
								<table width='100%'>
									<tr>
										<th>Code</th>
										<th>Title</th>
										<th>Colour</th>
										<th>Image</th>
										
										<th>Av Qty</th>
										<th>SP</th>
										<th>Lowest SP</th>
										<th>Inp SP</th>
										<th>Inp Qty</th>
										<th>Action</th>
									</tr>
								</table>
							</div>
						</fieldset>
					</td></tr>
			</table>
		</div>
		<?php echo $this->element('/home/pf_edit_payment'); ?>
        <td width='50%'>
			<a href='#-1' id='clea_sess' title='Are you sure you want to empty cart session?' style="margin-left: 496px;">Clear Session</a>
			
			<fieldset>	    
				<legend>Cart</legend>
				<div id='cartDiv' style="overflow: scroll; width: 580px; height: 700px; font-size: 9px;">
					<table width='100%'>
						<tr>
							<th style='width:30px;'>Del</th>
							<th>Code</th>
							<th>Title</th>
							<th>Unit Price</th>
							<th>Qty</th>
							<th>Amount</th>
							<th>Edit</th>
						</tr>
					</table>
				</div>
			</fieldset>
        </td>
		
    </tr>
    </table>
</div>
<input type='hidden' name='update_bulk' id='update_bulk' value='<?=$update_bulk?>' />
<input type='hidden' name='restore_session_db' id='restore_session_db' value='<?=$restore_session_db?>' />
<input type='hidden' name='save_invoice' id='save_invoice' value='<?=$save_invoice?>' />
<input type='hidden' name='country' id='country' value='<?= $customer['country'] ?>'/>
<input type='hidden' name='vat' id='vat' value='<?= $vat ?>'/>
<input type='hidden' name='url_category' id='url_category' value=''/>
<input type='hidden' name='prod_url' id='prod_url' value='/home/get_products_json'/>
<input type='hidden' name='add_2_cart_full' id='add_2_cart_full' value='<?=$add_2_cart_full?>' />
<input type='hidden' name='update_cart' id='update_cart' value='<?=$update_cart?>' />
<input type='hidden' name='delte_cart' id='delte_cart' value='<?=$delte_cart?>' />
<input type='hidden' name='add_2_cart_short' id='add_2_cart_short' value='<?=$add_2_cart_short?>' />
<input type='hidden' name='restore_cart' id='restore_cart' value='<?=$restore_cart?>' />
<input type='hidden' name='clear_cart' id='clear_cart' value='<?=$clear_cart?>' />
<a href='#-1' id='del_item' class='del_from_cart' rel="temp" title='Are you sure you want to delete item' style='display: none;'>Del</a>
<div id="dialog-confirm" title="Delete item from cart?" style="width: 500px !important;">
	Are you sure you want to delete item from cart?
</div>

<div id="dialog-pmt" title="Please select payment method">Please select payment method</div>
<div id="dialog-pmt-not-equal" title="Please select payment method">Total is not equal to amount</div>
<div id="combination" title="Please select payment method">Either amount or payment-mode are not selected correctly</div>
<div id="dialog-pmt-exceeding" title="Please select payment method">Amount is exceeding due amount</div>
<div id="dialog-selling_amount" title="Please select payment method">selling amount cannot be less the minimum selling amount</div>
<div id="Wrong-input" title="Please enter valid code">Please enter valid code</div>
<div id="out-of-stock" title="Out of Stock">Either Product is out of stock Or Invalid Code!!!</div>
<div id="invalid" title="Product code is invalid" id='qtyAdjusted'>Quantity Adjusted</div>
<div id="nothing-to-restore" title="Nothing to restore. Cart is empty!" id='qtyAdjusted'>Nothing to restore. Cart is empty!</div>

<script type="text/javascript">
	function update_hidden(){
		var multipleValues = $( "#category_dropdown" ).val() || [];
		$('#url_category').val(multipleValues.join( "," ));
	}
 
	var product_dataset = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		//prefetch: "/products/data",
		remote: {
		 url: "/home/admin_data?category=%CID&search=%QUERY",
						  replace: function (url,query) {
						   var multipleValues = $( "#category_dropdown" ).val() || [];
						   $('#url_category').val(multipleValues.join( "," ));
						   return url.replace('%QUERY', query).replace('%CID', $('#url_category').val());
						  },
						  wildcard: "%QUERY"
		  
		}
	});

	$('#remote_cart .typeahead').typeahead(null, {
		name: 'product',
		display: 'code',
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
			footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>--<?php echo ADMIN_DOMAIN;?> [Search Results]--</b></div>"),
		}
	});
	
	$('#clear_search').click(function(){
		$('#category_dropdown option:selected').removeAttr("selected");
		$('#search_prod').val("");
	});
	
	$('#search_form').submit(function( event ) {
		get_results();
		$('#search_prod').val("");
	});
	
	function get_results() {
		var searchKW = $("#search_prod").val();
		if ($.trim(searchKW) != "") {
			var targeturl = $("#get_result_by_title").attr('rel')+ '?search_kw=' + searchKW;
			$.blockUI({ message: 'Just a moment...' });
			$.ajax({
				type: 'get',
				url: targeturl,
				beforeSend: function(xhr) {
					xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				},
				success: function(response) {
					$('#searchOuterDiv').show();
					$.unblockUI();
					var objArr = $.parseJSON(response);
					var innerHTMLStr = "<table width='100%' cellspacing='2' cellpadding='2'><tr><th>Code</th><th>Title</th><th>Colour</th><th>Image</th><th>Av Qty</th><th>SP</th><th>Lowest SP</th><th>Inp SP</th><th>Inp Qty</th><th>Action</th></tr>";
					var resultFound = false;
					$.each(objArr, function(key, obj){ //str.concat();
						resultFound = true;
						innerHTMLStr += "<tr><td><span id='code_"+obj.id+"'>"+obj.product_code+"</span></td>";
						innerHTMLStr += "<td><a href='#-1' title='Category:"+obj.category_title+"'>"+obj.product+"</a></td>";
						innerHTMLStr += "<td>"+obj.color+"</td>";
						innerHTMLStr += "<td width='45'><a href='"+obj.image_url+"' target='_blank' title='Click on image to open in new tab'><img src='"+obj.image_url+"'title='Click on image to open in new tab'></a></td>";
					 
						//innerHTMLStr += "<td width='45'><a href='"+obj.image_url+"' target='_blank' title='Click on image to open in new tab'>##</a><a href='"+obj.image_url+"' target='_blank' title='Click on image to open in new tab'><img scr='"+obj.image_url+"' width = '35' height='35'  title='Image'/></a></td>";
						//innerHTMLStr += "<td>"+obj.category_title+"</td>";
						innerHTMLStr += "<td>"+obj.quantity+"</td>";
						innerHTMLStr += "<td>"+obj.price_without_vat+"</td>";
						var dis_status = obj.discount_status;
						if (dis_status == 1) {
							innerHTMLStr += "<input type='hidden' id='dis_"+obj.id+"' value="+parseFloat(obj.discounted_value).toFixed(2)+" />";
							innerHTMLStr += "<td><span id = dis_"+obj.id+" title ="+parseFloat(obj.discounted_value).toFixed(2)+">####</span></td>";
							innerHTMLStr += "<td><input type='text' name='sp' value='"+obj.price_without_vat+"' style='width:35px;' id='sp_"+obj.id+"' onchange='check_sp("+obj.id+")'/></td>";
                        }else{
							innerHTMLStr += "<input type='hidden' id='dis_"+obj.id+"' value="+parseFloat(obj.price_without_vat).toFixed(2)+" />";
							innerHTMLStr += "<td>N/A</span></td>";
							innerHTMLStr += "<td style='width:35px;'><input type='text' name='sp' value='"+obj.price_without_vat+"' style='width:35px;'  id='sp_"+obj.id+"' onchange='check_sp_na("+obj.id+")'/></td>";
						}
						
						//innerHTMLStr += "<td><input type='text' name='sp' value='"+obj.selling_price+"' style='width:50px;' id='sp_"+obj.id+"' onchange='check_sp("+obj.id+")'/></td>";
						innerHTMLStr += "<td style='width:35px;'><input type='text' pattern='^[0-9]{10}' title='Only Number' class='validate_qty' name='qty' value='1' onkeydown='validateNumber(event);' style='width:35px;' id='qty_"+obj.id+"'/></td>";
						//oninput='this.value = this.value.replace(/[^0-9]{10}/g, \"\"); this.value = this.value.replace(/(\..*)\./g, \"$1\");'
						//onkeypress='return /\d/.test(String.fromCharCode(((event||window.event).which||(event||window.event).which)));'
						innerHTMLStr += "<td><a href='#-1' class = 'search_anchor' rel='"+obj.id+"'>Add</a></td></tr>";
					});
					
					if (!resultFound) {
						innerHTMLStr += "<tr><td colspan='11' align='center' style='text-align:center;'><span style='color:red; font-size:14px;'>!!!No Record Found!!!</span></td></tr>";
					}
					
					innerHTMLStr += "</table>";
					//1. http://ADMIN_DOMAIN.co.uk//files/product/image/7026/thumb_thumb_brown.JPG
					//2. http://ADMIN_DOMAIN.co.uk//files/product/image/7026/thumb_thumb_brown.JPG
					$("#searchDiv").html(innerHTMLStr);
					/*if (response.error) {
						alert(response.error);
						console.log(response.error);
					}*/		
				},
				error: function(e) {
					$.unblockUI();
					alert("An error occurred: " + e.responseText.message);
					console.log(e);
				}
			});
        }else{
			$( "#out-of-stock" ).dialog({
								resizable: false,
								height:140,
								modal: true,
								closeText: "Close",
								width:300,
								maxWidth:300,
								title: '!!! Out of Stock!!!',
								buttons: {
									"OK": function() {
										$( this ).dialog( "close" );
									}
								}
							});
			return false;
		}
    }
	
	$( "#get_result" ).click(function() {
		get_results();
		$('#search_prod').val("");
	});
	
	$('#scanner_form').submit(function( event ) {
		var product_code = $('#scanner_input').val();
		var qty = $('#scanner_qty').val();
		event.preventDefault();
		if ($.trim(qty) == "") {
            qty = 1;
        }else{
			qty = parseInt(qty);
		}
		if (qty == 0) {
            alert("Please input valid quantity");
			return;
        }
		var bulk_discount = $('#bulk_discount').val();
		var receptReq = $("input[name='receipt_required']:checked"). val();
		var special_invoice = $("input[name='special_invoice']:checked"). val();
		var targeturl = $("#scanner_input").attr('rel')+ '?product_code=' + product_code+"&quantity="+parseInt(qty);
		targeturl += "&bulk_discount="+bulk_discount;
		targeturl += "&recept_req="+receptReq;
		targeturl += "&special_invoice="+special_invoice;
		$.blockUI({ message: 'Updating cart...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				var objArr = $.parseJSON(response);
				if(typeof objArr.new_sale_basket == "undefined"){
					var errorMsg = objArr.prodError
					var position = errorMsg.indexOf("out of stock");
					var invalidCodePos = errorMsg.indexOf("adjusted");
					//alert("Failed to process :scanner case");
					if (position >= 0) {
                            $( "#out-of-stock" ).dialog({
								resizable: false,
								height:140,
								modal: true,
								closeText: "Close",
								width:300,
								maxWidth:300,
								title: '!!! Out of Stock!!!',
								buttons: {
									"OK": function() {
										$( this ).dialog( "close" );
									}
								}
							});
                        }else if (invalidCodePos > 0) {
							//var errorMsg = $('#qtyAdjusted').text(objArr.prodError);
                            $( "#invalid" ).dialog({
								resizable: false,
								height:140,
								modal: true,
								closeText: "Close",
								width:300,
								maxWidth:300,
								title: 'Quantity Adjusted',
								buttons: {
									"OK": function() {
										$( this ).dialog( "close" );
									}
								}
							});
							show_cart(objArr);
                        }else{
							$( "#Wrong-input" ).dialog({
								resizable: false,
								height:140,
								modal: true,
								closeText: "Close",
								width:300,
								maxWidth:300,
								title: '!!! Wrong Input!!!',
								buttons: {
									"OK": function() {
										$( this ).dialog( "close" );
									}
								}
							});
						}
				}else{
					if (typeof objArr.prodError == "undefined") {
                        show_cart(objArr);
                    }else{
						var errorMsg = objArr.prodError
						var position = errorMsg.indexOf("out of stock");
						var invalidCodePos = errorMsg.indexOf("adjusted");
						if (position >= 0) {
                            $( "#out-of-stock" ).dialog({
								resizable: false,
								height:140,
								modal: true,
								closeText: "Close",
								width:300,
								maxWidth:300,
								title: '!!! Out of Stock!!!',
								buttons: {
									"OK": function() {
										$( this ).dialog( "close" );
									}
								}
							});
                        }else if (invalidCodePos > 0) {
							//var errorMsg = $('#qtyAdjusted').text(objArr.prodError);
                            $( "#invalid" ).dialog({
								resizable: false,
								height:140,
								modal: true,
								closeText: "Close",
								width:300,
								maxWidth:300,
								title: 'Quantity Adjusted',
								buttons: {
									"OK": function() {
										$( this ).dialog( "close" );
									}
								}
							});
							show_cart(objArr);
                        }else{
							//default case
							show_cart(objArr);
						}
					}
					$('#scanner_input').val("");
				}
				$.unblockUI();
			},
			error: function(e) {
				$.unblockUI();
				alert("An error occurred: " + e.responseText.message);
				console.log(e);
			}
		});
	});
	
	$(".del_from_cart").easyconfirm({locale: { title: 'Delete Item from Cart?', button: ['No','Yes']}});
	
	$(document).on('click', '.del_from_cart', function() {
		var product_id = $(this).attr('rel');
		var alertTitle = $(this).attr('title');
		//var response = confirm("Are You Sure You Want To Delete");
		response = true;
		if (response == true) {
			var targeturl = $("#delte_cart").val();
			targeturl += '?prod_id='+product_id;
			//----------------
			$( "#dialog-confirm" ).dialog({
				resizable: false,
				height:140,
				modal: true,
				buttons: {
					"Agree": function() {
						//----------------------------------------
						$.blockUI({ message: 'Updating cart...' });
						$.ajax({
							type: 'get',
							url: targeturl,
							beforeSend: function(xhr) {
								xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
							},
							success: function(response) {
								var objArr = $.parseJSON(response);
								if(typeof objArr.new_sale_basket == "undefined"){
									alert("Failed to process :scanner case");
								}else{
									show_cart(objArr);
									$('#scanner_input').val("");
								}
								$.unblockUI();
							},
							error: function(e) {
								$.unblockUI();
								alert("An error occurred: " + e.responseText.message);
								console.log(e);
							}
						});
						//----------------------------------------
						$( this ).dialog( "close" );
					},
					Cancel: function() {
						$( this ).dialog( "close" );
					}
				}
			});
		}
	});
	
	function show_cart(objArr) {
        var itemObj = objArr.new_sale_basket;
		var innerHTMLStr = "<table width='100%' cellspacing='2' cellpadding='2'><tr><th style='width:30px;'>Del</th><th>Code</th><th>Title</th><th>Unit Price</th><th>Qty</th><th>Amount</th><th>Edit</th></tr>";
		var resultFound = false;
		var total_amount = 0
		
		var posArr = [];
		var counter = 0;
		$.each(itemObj, function(key, obj){
			posArr[counter++] = obj.position;
		});
		posArr.sort(function(a, b){return b-a});
		var position;
		for (i = 0; i < posArr.length; i++) {
			position = parseInt(posArr[i]);
			$.each(itemObj, function(key, obj){
				resultFound = true;
				//alert(position);
				if (position == parseInt(obj.position)) {
					var amount = parseInt(obj.quantity) * parseFloat(obj.selling_price);
					var short_qty = obj.qantity_short;
					if (parseInt(short_qty) == 1) {
                        var style_short = "color:red;background-color: yellow;"; 
                    }else{
						var style_short = "";
					}
					//alert(short_qty);
					total_amount += amount;
					innerHTMLStr += "<tr style = '"+style_short+"'><td><a href='#-1' id='del_item' class='del_from_cart' rel="+key+" title='Are you sure you want to delete "+ obj.product+" from cart?'>Del</a></td>";
					innerHTMLStr += "<td>"+obj.product_code+"</td>";
					innerHTMLStr += "<td>"+obj.product+"</td>";
					var min_dis = obj.minimum_selling_price;
					//innerHTMLStr += "<input type='hidden' id='cart_dis_"+key+"' value="+min_dis+" />";
					if (min_dis == 0) {
						innerHTMLStr += "<input type='hidden' id='cart_dis_"+key+"' value="+parseFloat(obj.selling_price).toFixed(2)+" />";
						var title = "N/A";
						innerHTMLStr += "<td><input type='text' title = '"+title+"' name='sp' value='"+obj.selling_price+"' style='width:50px;' id='cart_sp_"+key+"'  onchange='check_sp_cart_na("+key+")'/></td>";
					}else{
						innerHTMLStr += "<input type='hidden' id='cart_dis_"+key+"' value="+parseFloat(min_dis).toFixed(2)+" />";
						var title = "Minimum Price: "+ parseFloat(min_dis).toFixed(2);
						innerHTMLStr += "<td><input type='text' title = '"+title+"' name='sp' value='"+obj.selling_price+"' style='width:50px;' id='cart_sp_"+key+"' onchange='check_sp_cart("+key+")'/></td>";
					}
					innerHTMLStr += "<td style='width:40px;'><input onkeydown='validateNumber(event);' type='text'  title = 'Quantity Available: "+obj.available_qantity+"' name='qty' value='"+obj.quantity+"' style='width:40px;' id='cart_qty_"+key+"'/></td>";
					innerHTMLStr += "<td style='width:50px;'><a href='#-1' title='"+amount.toFixed(2)+"'>"+amount.toFixed(2)+"</a></td>";
					innerHTMLStr += "<td><a href='#-1' class = 'update_cart' rel='"+key+"'>Update</a></td></tr>";
                }
			});
		}
		var bulk_amt = $('#bulk_discount').val();
		if (bulk_amt != "") {
            var bulk_dis_val = total_amount * (bulk_amt/100);
			//alert(bulk_dis_val);
			var invoice_bulk_amt = total_amount - bulk_dis_val;
        }else{
			var invoice_bulk_amt = total_amount;
		}
		$("#invoice_amount_with_bulk").text(parseFloat(invoice_bulk_amt).toFixed(2));
		
		var country = $("#country").val();
		var vat = $("#vat").val();
		var vat_amt = total_amount*(vat/100);
		var special_invoice = $("input[name='special_invoice']:checked"). val();
		if (special_invoice == 1) {
			var final_amt = total_amount;  // with vat final_amt  // without vat total_amount
		}else{
			if (country == "OTH") {
				var final_amt = total_amount;  // with vat final_amt  // without vat total_amount
			}else{
				var final_amt = total_amount+vat_amt;  // with vat final_amt  // without vat total_amount
			}
			
		}
		
		if (parseFloat(total_amount) > 0) {
			if (country == "OTH") {
				innerHTMLStr += "<tr><td><a href='#-1' class = 'make_payment' rel='"+total_amount+"'>make performa</a></td></tr>";
            }else{
				innerHTMLStr += "<tr><td><a href='#-1' class = 'make_payment' rel='"+final_amt+"'>make performa</a></td></tr>";
			}
            
        }
		if (!resultFound) {
            innerHTMLStr += "<tr><td colspan='7' align='center' style='text-align:center;'><span style='color:red; font-size:14px;'>!!!No Record Found!!!</span></td></tr>";
        }
		innerHTMLStr += "</table>";
		$("#cartDiv").html(innerHTMLStr);
		$("#invoice_amount").text(parseFloat(total_amount).toFixed(2));
//		if (country == "OTH") {
//			document.getElementById('total_hidden_amount').value = total_amount;
//			$("#invoice_amt").text(parseFloat(total_amount).toFixed(2));
//        }else{
//			document.getElementById('total_hidden_amount').value = final_amt;
//			$("#invoice_amt").text(parseFloat(final_amt).toFixed(2));
//		}
		$('#scanner_qty').val('1');
		$('#paymentDiv').hide();
		$( "#scanner_input" ).focus();
    }
	
	$(document).on('click', '.make_payment', function() {
		$('#paymentDiv').show();
	});
	
//	$(document).on('click', '.submit_payment', function() {
//		var amt = $(this).attr('rel');
//		if ($('#full_or_part_2').is(':checked')) {
//			total = 0;
//			for(var i = 0; i < <?php echo 3;?>; i++){
//				var amt = $('#payment_method_'+i).val();
//				if (amt != "") {
//					total += parseFloat($('#payment_method_'+i).val());
//				}						
//			}
//			if (total == amt) {
//                for(var i = 0; i < <?php echo 3;?>; i++){
//				var amt = $('#payment_method_'+i).val();
//				if (amt != "") {
//					
//				}						
//			}
//            }else{
//				alert("amount and total are not equalent");
//			}
//		}else{
//			var total = parseFloat($('#payment_method_0').val());
//			if (amt == total) {
//                var targeturl = $("#save_invoice").val();
//				$.blockUI({ message: 'Updating cart...' });
//				$.ajax({
//						type: 'get',
//						url: targeturl,
//						beforeSend: function(xhr) {
//							xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
//						},
//						success: function(response) {
//							$('#search_prod').val("");
//							$('#searchOuterDiv').hide(1000);
//							var objArr = $.parseJSON(response);
//							if(typeof objArr.new_sale_basket == "undefined"){
//								alert("Failed to process for search case!");
//							}else{
//								show_cart(objArr);
//							}
//							$.unblockUI();
//						},
//						error: function(e) {
//							$.unblockUI();
//							alert("An error occurred: " + e.responseText.message);
//							console.log(e);
//						}
//					});
//            }else{
//				alert("total is not equal to amount");
//			}
//		}
//	});
	
	$(document).on('click', '.pay_cancel_button', function() {
		$('#paymentDiv').hide(1000);
	});
	
	$(document).on('click', '#rest_sess_db', function() {
		var targeturl = $("#restore_session_db").val();
		$.blockUI({ message: 'Updating cart...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				$('#search_prod').val("");
				$('#searchOuterDiv').hide(1000);
				var objArr = $.parseJSON(response);
				if (typeof objArr.msg != "undefined") {
					$.unblockUI();
                    $( "#nothing-to-restore" ).dialog({
						resizable: false,
						height:140,
						modal: true,
						closeText: "Close",
						width:300,
						maxWidth:300,
						title: 'Nothing to restore from session',
						buttons: {
							"OK": function() {
								$( this ).dialog( "close" );
							}
						}
					});
                }else{
					if(typeof objArr.new_sale_basket == "undefined"){
						alert("Failed to process for search case!");
					}else{
						show_cart(objArr);
					}
				}
				$.unblockUI();
			},
			error: function(e) {
				$.unblockUI();
				alert("An error occurred: Please Log In to Continue");
				console.log(e);
			}
		});
	});
	
	
	$(document).on('click', '.update_cart', function() {
		var product_id = $(this).attr('rel');
		var quantity = $('#cart_qty_'+product_id).val();
		var selling_price = $('#cart_sp_'+product_id).val();
		var bulk_discount = $('#bulk_discount').val();
		var recit_req = 0;
		if ($('#receipt_required_1').is(':checked')) {
            recit_req = 1;
        }
		var special_invoice = $("input[name='special_invoice']:checked"). val();
		//alert(special_invoice);
		var targeturl = $("#update_cart").val();
		targeturl += '?prod_id='+product_id;
		targeturl += '&qty='+quantity;
		targeturl += '&sp='+selling_price;
		targeturl += '&bulk='+bulk_discount;
		targeturl += '&recit_req='+recit_req;
		targeturl += "&special_invoice="+special_invoice;
		$.blockUI({ message: 'Updating cart...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				$('#search_prod').val("");
				$('#searchOuterDiv').hide(1000);
				var objArr = $.parseJSON(response);
				if(typeof objArr.new_sale_basket == "undefined"){
					alert("Failed to process for search case!");
				}else{
					show_cart(objArr);
				}
				$.unblockUI();
			},
			error: function(e) {
				$.unblockUI();
				alert("An error occurred: " + e.responseText.message);
				console.log(e);
			}
		});
	});
	
	
	$(document).on('click', '.search_anchor', function() {
		//This function is used for firing events on elements loaded by ajax.
		//live is deprecated. other one which we can use is delegate
		var product_id = $(this).attr('rel');
		var quantity = $('#qty_'+product_id).val();
		var selling_price = $('#sp_'+product_id).val();
		var productTitle = $("#title_"+product_id).html();
		var productCode = $("#code_"+product_id).html();
		var maximum_dis = $("#dis_"+product_id).val();
		var bulk_discount = $('#bulk_discount').val();
		if (maximum_dis == null) {
            maximum_dis = 0;
        }
		//alert(maximum_dis);
		//Creating URL
		var targeturl = $("#add_2_cart_full").val();
		targeturl += '?prod_id='+product_id;
		targeturl += '&qty='+quantity;
		targeturl += '&sp='+selling_price;
		targeturl += '&prod_title='+productTitle;
		targeturl += '&prod_code='+productCode;
		targeturl += '&min_dis='+maximum_dis;
		var receptReq = $("input[name='receipt_required']:checked"). val();
		var special_invoice = $("input[name='special_invoice']:checked"). val();
		targeturl += "&bulk_discount="+bulk_discount;
		targeturl += "&recept_req="+receptReq;
		targeturl += "&special_invoice="+special_invoice;
		
		$.blockUI({ message: 'Updating cart...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				$('#search_prod').val("");
				$('#searchOuterDiv').hide(1000);   
				var objArr = $.parseJSON(response);
				if(typeof objArr.new_sale_basket == "undefined"){
					alert("Failed to process for search case!");
				}else{
					show_cart(objArr);
				}
				$.unblockUI();
			},
			error: function(e) {
				$.unblockUI();
				alert("An error occurred: " + e.responseText.message);
				console.log(e);
			}
		});
	});
	
	$(document).on('click', '.update_bulk_discount', function() {
		var bulk_dis = $('#bulk_discount').val();
		var targeturl = $('#update_bulk').val();
		targeturl += '?bulk='+bulk_dis;
		$.blockUI({ message: 'Updating Bulk Discount...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				var objArr = $.parseJSON(response);
				if(typeof(objArr.new_sale_basket) == "object"){
					show_cart(objArr);
					$('#bulk_discount').val(objArr.new_sale_bulk_discount);
					if (parseInt(objArr.receipt_required) == 1) {
						$('input:radio[name="receipt_required"][value="1"]').attr('checked',true);
                        //$('#receipt_required_1').attr('checked',true);
                    }else if (parseInt(objArr.receipt_required) == 0) {
						$('#receipt_required_0').attr('checked',true);
                        //$('input:radio[name=receipt_required]:nth(1)').attr('checked',true);
						//$('input:radio[name=receipt_required]')[0].checked = true;
                    }else{
						$('input:radio[name=receipt_required]').attr('checked',false);
					}
					
				}else{
					alert(objArr.msg+' - update bulk case');
				}
				$.unblockUI();
			},
			error: function(e) {
				$.unblockUI();
				alert("An error occurred: " + e.responseText.message);
				console.log(e);
			}
		});
	});
	
	
	$(document).on('click', '#rest_sess', function() {
		var targeturl = $("#restore_cart").val();
		$.blockUI({ message: 'Resoring session...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				$('#invoice_amount').val('#####');
				var objArr = $.parseJSON(response);
				if(typeof(objArr.new_sale_basket) == "object"){
					show_cart(objArr);
					$('#bulk_discount').val(objArr.new_sale_bulk_discount);
					if (parseInt(objArr.receipt_required) == 1) {
						$('input:radio[name="receipt_required"][value="1"]').attr('checked',true);
                        //$('#receipt_required_1').attr('checked',true);
                    }else if (parseInt(objArr.receipt_required) == 0) {
						$('#receipt_required_0').attr('checked',true);
                        //$('input:radio[name=receipt_required]:nth(1)').attr('checked',true);
						//$('input:radio[name=receipt_required]')[0].checked = true;
                    }else{
						$('input:radio[name=receipt_required]').attr('checked',false);
					}
					
				}else{
					//rasu
					//alert(objArr.msg+' - case restore session');
					$( "#nothing-to-restore" ).dialog({
						resizable: false,
						height:140,
						modal: true,
						closeText: "Close",
						width:300,
						maxWidth:300,
						title: 'Nothing to restore from session',
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
				alert("An error occurred: Please Log In to Continue");
				console.log(e);
			}
		});
	});
	$("#clea_sess").easyconfirm({locale: { title: 'Empty Cart?', button: ['No','Yes']}});
	$("#clea_sess").click(function() {
		$.blockUI({ message: 'Clearing cart...' });
		var targeturl = $("#clear_cart").val();
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				var innerHTMLStr = "<table width='100%' cellspacing='2' cellpadding='2'><tr><th style='width:30px;'>Del</th><th>Code</th><th>Title</th><th>Unit Price</th><th>Qty</th><th>Amount</th><th>Edit</th></tr></table>";
				$("#cartDiv").html(innerHTMLStr);
				$("#invoice_amount").text("#####");
				$("#invoice_amount_with_bulk").text("#####");
				$("#bulk_discount").val("");
				$('#paymentDiv').hide();
				$('#paymentDiv').hide(100);
				$.unblockUI();
			},
			error: function(e) {
				$.unblockUI();
				alert("An error occurred: " + e.responseText.message);
				console.log(e);
			}
		});
	});
</script>
<script>
	
	function check_sp_na(prod_id) {
		var dis_val = document.getElementById('dis_'+prod_id).value;
		var sell_p_val = document.getElementById('sp_'+prod_id).value;
		if (sell_p_val == "") {
            sell_p_val = 0;
        }
		if (parseFloat(sell_p_val) < parseFloat(dis_val)) {
			document.getElementById('sp_'+prod_id).value = parseFloat(dis_val).toFixed(2);
			//alert("selling amount cannot be less the minimum selling amount");
			$( "#dialog-selling_amount" ).dialog({
				resizable: false,
				height:140,
				modal: true,
				closeText: "Close",
				width:300,
				maxWidth:300,
				title: '!!! selling amount cannot be less the minimum selling price!!!',
				buttons: {
					"OK": function() {
						$( this ).dialog( "close" );
					}
				}
			});
		}
    }
	
	
	
	function check_sp(prod_id) {
		var dis_val = document.getElementById('dis_'+prod_id).value;
		var sell_p_val = document.getElementById('sp_'+prod_id).value;
		if (parseFloat(sell_p_val) < parseFloat(dis_val)) {
			document.getElementById('sp_'+prod_id).value = parseFloat(dis_val).toFixed(2);
			//alert("selling amount cannot be less the minimum selling amount");
			$( "#dialog-selling_amount" ).dialog({
				resizable: false,
				height:140,
				modal: true,
				closeText: "Close",
				width:300,
				maxWidth:300,
				title: '!!! selling amount cannot be less the minimum selling price!!!',
				buttons: {
					"OK": function() {
						$( this ).dialog( "close" );
					}
				}
			});
		}
    }
	
	function check_sp_cart_na(prod_id) {
		var dis_val = document.getElementById('cart_dis_'+prod_id).value; 		var sell_p_val = document.getElementById('cart_sp_'+prod_id).value;
		if (sell_p_val == "") {
            sell_p_val = 0;
        }
		//return false;
		if (parseFloat(sell_p_val) < parseFloat(dis_val)) {
			document.getElementById('cart_sp_'+prod_id).value = parseFloat(dis_val).toFixed(2);
			//alert("selling amount cannot be less the minimum selling amount");
			$( "#dialog-selling_amount" ).dialog({
				resizable: false,
				height:140,
				modal: true,
				closeText: "Close",
				width:300,
				maxWidth:300,
				title: '!!! selling amount cannot be less the minimum selling price!!!',
				buttons: {
					"OK": function() {
						$( this ).dialog( "close" );
					}
				}
			});
		}
    }
	
	
	
	function check_sp_cart(prod_id) {
		var dis_val = document.getElementById('cart_dis_'+prod_id).value; 		var sell_p_val = document.getElementById('cart_sp_'+prod_id).value;
		if (parseFloat(sell_p_val) < parseFloat(dis_val)) {
			document.getElementById('cart_sp_'+prod_id).value = parseFloat(dis_val).toFixed(2);
			//alert("selling amount cannot be less the minimum selling amount");
			$( "#dialog-selling_amount" ).dialog({
				resizable: false,
				height:140,
				modal: true,
				closeText: "Close",
				width:300,
				maxWidth:300,
				title: '!!! selling amount cannot be less the minimum selling price!!!',
				buttons: {
					"OK": function() {
						$( this ).dialog( "close" );
					}
				}
			});
		}
    }
	$("#scanner_input").focus(function() { $(this).select(); } );
	$("#search_prod").focus(function() { $(this).select(); } );
	
	$(function() {
		$( document ).tooltip({
			content: function () {
				return $(this).prop('title');
			}
		});
		
		$(".validate_qty").keydown(function (event) {
			if (event.shiftKey == true) {event.preventDefault();}
			if ((event.keyCode >= 48 && event.keyCode <= 57) ||
			   (event.keyCode >= 96 && event.keyCode <= 105) ||
			   event.keyCode == 8 || event.keyCode == 9 ||
			   event.keyCode == 37 || event.keyCode == 39 ||
			   event.keyCode == 46 || event.keyCode == 183) {
				 ;
			}else{
			   event.preventDefault();
			}
		});
	});
	
	$("#bulk_discount, #scanner_qty").keydown(function (event) {			
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		   (event.keyCode >= 96 && event.keyCode <= 105) ||
		   event.keyCode == 8 || event.keyCode == 9 ||
		   event.keyCode == 37 || event.keyCode == 39 ||
		   event.keyCode == 46 || event.keyCode == 183) {
			 ;
		}else{
		   event.preventDefault();
		}
	});
	$(document).on('click', '.validate_qty', function() {
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		   (event.keyCode >= 96 && event.keyCode <= 105) ||
		   event.keyCode == 8 || event.keyCode == 9 ||
		   event.keyCode == 37 || event.keyCode == 39 ||
		   event.keyCode == 46 || event.keyCode == 183) {
			 ;
		}else{
		   event.preventDefault();
		}
	});
	
	$( function() {
		//$( "#dialog-confirm" ).dialog({});
	});
	function validateNumber(evt) {
		/*
		 var charCode = (evt.which) ? evt.which : evt.keyCode
		if (charCode > 31 && (charCode < 48 || charCode > 57))
			return false;
		return true;
		 **/
		var e = evt || window.event;
		var key = e.keyCode || e.which;
	
		if (!e.shiftKey && !e.altKey && !e.ctrlKey &&
		// numbers   
		key >= 48 && key <= 57 ||
		// Numeric keypad
		key >= 96 && key <= 105 ||
		// Backspace and Tab and Enter
		key == 8 || key == 9 || key == 13 ||
		// Home and End
		key == 35 || key == 36 ||
		// left and right arrows
		key == 37 || key == 39 ||
		// Del and Ins
		key == 46 || key == 45) {
			// input is VALID
		}
		else {
			// input is INVALID
			e.returnValue = false;
			if (e.preventDefault) e.preventDefault();
		}
	}
</script>
<script>
	
	$(document).on('click', '#Scan_Code', function() {
		var product_code = $('#scanner_input').val();
		var qty = $('#scanner_qty').val();
		//event.preventDefault();
		if ($.trim(qty) == "") {
            qty = 1;
        }else{
			qty = parseInt(qty);
		}
		if (qty == 0) {
            alert("Please input valid quantity");
			return;
        }
		var bulk_discount = $('#bulk_discount').val();
		var receptReq = $("input[name='receipt_required']:checked"). val();
		var special_invoice = $("input[name='special_invoice']:checked"). val();
		var targeturl = $("#scanner_input").attr('rel')+ '?product_code=' + product_code+"&quantity="+parseInt(qty);
		targeturl += "&bulk_discount="+bulk_discount;
		targeturl += "&recept_req="+receptReq;
		targeturl += "&special_invoice="+special_invoice;
		$.blockUI({ message: 'Updating cart...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				var objArr = $.parseJSON(response);
				if(typeof objArr.new_sale_basket == "undefined"){
					var errorMsg = objArr.prodError
					var position = errorMsg.indexOf("out of stock");
					var invalidCodePos = errorMsg.indexOf("adjusted");
					//alert("Failed to process :scanner case");
					if (position >= 0) {
                            $( "#out-of-stock" ).dialog({
								resizable: false,
								height:140,
								modal: true,
								closeText: "Close",
								width:300,
								maxWidth:300,
								title: '!!! Out of Stock!!!',
								buttons: {
									"OK": function() {
										$( this ).dialog( "close" );
									}
								}
							});
                        }else if (invalidCodePos > 0) {
							//var errorMsg = $('#qtyAdjusted').text(objArr.prodError);
                            $( "#invalid" ).dialog({
								resizable: false,
								height:140,
								modal: true,
								closeText: "Close",
								width:300,
								maxWidth:300,
								title: 'Quantity Adjusted',
								buttons: {
									"OK": function() {
										$( this ).dialog( "close" );
									}
								}
							});
							show_cart(objArr);
                        }else{
							$( "#Wrong-input" ).dialog({
								resizable: false,
								height:140,
								modal: true,
								closeText: "Close",
								width:300,
								maxWidth:300,
								title: '!!! Wrong Input!!!',
								buttons: {
									"OK": function() {
										$( this ).dialog( "close" );
									}
								}
							});
						}
				}else{
					if (typeof objArr.prodError == "undefined") {
                        show_cart(objArr);
                    }else{
						var errorMsg = objArr.prodError
						var position = errorMsg.indexOf("out of stock");
						var invalidCodePos = errorMsg.indexOf("adjusted");
						if (position >= 0) {
                            $( "#out-of-stock" ).dialog({
								resizable: false,
								height:140,
								modal: true,
								closeText: "Close",
								width:300,
								maxWidth:300,
								title: '!!! Out of Stock!!!',
								buttons: {
									"OK": function() {
										$( this ).dialog( "close" );
									}
								}
							});
                        }else if (invalidCodePos > 0) {
							//var errorMsg = $('#qtyAdjusted').text(objArr.prodError);
                            $( "#invalid" ).dialog({
								resizable: false,
								height:140,
								modal: true,
								closeText: "Close",
								width:300,
								maxWidth:300,
								title: 'Quantity Adjusted',
								buttons: {
									"OK": function() {
										$( this ).dialog( "close" );
									}
								}
							});
							show_cart(objArr);
                        }else{
							//default case
							show_cart(objArr);
						}
					}
					$('#scanner_input').val("");
				}
				$.unblockUI();
			},
			error: function(e) {
				$.unblockUI();
				alert("An error occurred: " + e.responseText.message);
				console.log(e);
			}
		});
	});
	$(document).on('click', '#check_price', function() {
		var searchKW = $("#search_prod").val();
		if ($.trim(searchKW) != "") {
			var targeturl = $("#get_result_by_title").attr('rel')+ '?search_kw=' + searchKW;
			$.blockUI({ message: 'Just a moment...' });
			$.ajax({
				type: 'get',
				url: targeturl,
				beforeSend: function(xhr) {
					xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				},
				success: function(response) {
					$('#searchOuterDiv').show();
					$.unblockUI();
					var objArr = $.parseJSON(response);
					var innerHTMLStr = "<table width='100%' cellspacing='2' cellpadding='2'><tr><th>Code</th><th>Title</th><th>Colour</th><th>Image</th><th>Av Qty</th><th>SP</th><th>Lowest SP</th><th>Inp SP</th><th>Inp Qty</th><th>Action</th></tr>";
					var resultFound = false;
					$.each(objArr, function(key, obj){ //str.concat();
						resultFound = true;
						innerHTMLStr += "<tr><td><span id='code_"+obj.id+"'>"+obj.product_code+"</span></td>";
						innerHTMLStr += "<td><a href='#-1' title='Category:"+obj.category_title+"'>"+obj.product+"</a></td>";
						innerHTMLStr += "<td>"+obj.color+"</td>";
						innerHTMLStr += "<td width='45'><a href='"+obj.image_url+"' target='_blank' title='Click on image to open in new tab'><img src='"+obj.image_url+"'title='Click on image to open in new tab'></a></td>";
					 
						//innerHTMLStr += "<td width='45'><a href='"+obj.image_url+"' target='_blank' title='Click on image to open in new tab'>##</a><a href='"+obj.image_url+"' target='_blank' title='Click on image to open in new tab'><img scr='"+obj.image_url+"' width = '35' height='35'  title='Image'/></a></td>";
						//innerHTMLStr += "<td>"+obj.category_title+"</td>";
						innerHTMLStr += "<td>"+obj.quantity+"</td>";
						innerHTMLStr += "<td>"+obj.price_without_vat+"</td>";
						var dis_status = obj.discount_status;
						if (dis_status == 1) {
							innerHTMLStr += "<input type='hidden' id='dis_"+obj.id+"' value="+parseFloat(obj.discounted_value).toFixed(2)+" />";
							innerHTMLStr += "<td><span id = dis_"+obj.id+" title ="+parseFloat(obj.discounted_value).toFixed(2)+">####</span></td>";
							innerHTMLStr += "<td><input type='text' name='sp' value='"+obj.price_without_vat+"' style='width:35px;' id='sp_"+obj.id+"' onchange='check_sp("+obj.id+")'/></td>";
                        }else{
							innerHTMLStr += "<input type='hidden' id='dis_"+obj.id+"' value="+parseFloat(obj.price_without_vat).toFixed(2)+" />";
							innerHTMLStr += "<td>N/A</span></td>";
							innerHTMLStr += "<td style='width:35px;'><input type='text' name='sp' value='"+obj.price_without_vat+"' style='width:35px;'  id='sp_"+obj.id+"' onchange='check_sp_na("+obj.id+")'/></td>";
						}
						
						//innerHTMLStr += "<td><input type='text' name='sp' value='"+obj.selling_price+"' style='width:50px;' id='sp_"+obj.id+"' onchange='check_sp("+obj.id+")'/></td>";
						innerHTMLStr += "<td style='width:35px;'><input type='text' pattern='^[0-9]{10}' title='Only Number' class='validate_qty' name='qty' value='1' onkeydown='validateNumber(event);' style='width:35px;' id='qty_"+obj.id+"'/></td>";
						//oninput='this.value = this.value.replace(/[^0-9]{10}/g, \"\"); this.value = this.value.replace(/(\..*)\./g, \"$1\");'
						//onkeypress='return /\d/.test(String.fromCharCode(((event||window.event).which||(event||window.event).which)));'
						innerHTMLStr += "<td><a href='#-1' class = 'search_anchor' rel='"+obj.id+"'>Add</a></td></tr>";
					});
					
					if (!resultFound) {
						innerHTMLStr += "<tr><td colspan='11' align='center' style='text-align:center;'><span style='color:red; font-size:14px;'>!!!No Record Found!!!</span></td></tr>";
					}
					
					innerHTMLStr += "</table>";
					//1. http://ADMIN_DOMAIN.co.uk//files/product/image/7026/thumb_thumb_brown.JPG
					//2. http://ADMIN_DOMAIN.co.uk//files/product/image/7026/thumb_thumb_brown.JPG
					$("#searchDiv").html(innerHTMLStr);
					/*if (response.error) {
						alert(response.error);
						console.log(response.error);
					}*/		
				},
				error: function(e) {
					$.unblockUI();
					alert("An error occurred: " + e.responseText.message);
					console.log(e);
				}
			});
        }else{
		$( "#out-of-stock" ).dialog({
								resizable: false,
								height:140,
								modal: true,
								closeText: "Close",
								width:300,
								maxWidth:300,
								title: '!!! Out of Stock!!!',
								buttons: {
									"OK": function() {
										$( this ).dialog( "close" );
									}
								}
							});
			return false;
		}
	});
	
</script>


<script>
 $(document).ready(function(){
  $('#scanner_input').focus();
  $('#dialog-confirm').hide();
  $('#dialog-pmt').hide();
  $('#dialog-pmt-not-equal').hide();
  $('#combination').hide();
  $('#dialog-pmt-exceeding').hide();
  $('#dialog-selling_amount').hide();
  $('#out-of-stock').hide();
  $('#nothing-to-restore').hide();
  $('#invalid').hide();
  $('#Wrong-input').hide();
  $('#paymentDiv').hide();
   $("#rest_sess").trigger('click');
 });
</script>

<script>
function roundNumber(rnum, rlength) { // Arguments: number to round, number of decimal places
  var newnumber = Math.round(rnum*Math.pow(10,rlength))/Math.pow(10,rlength);
  return parseFloat(newnumber); // Output the result to the form field (change for your purposes)
}
</script>
<script>
	$('#openwindow').each(function() {
	  var $link = $(this);
	  var $dialog = $('#dialog-confirm1')
		  .load($link.attr('href'))
		  .dialog({
			  autoOpen: false,
			  title: $link.attr('title'),
			  width: 500,
			  height: 300,
			  buttons: {
                        'OK': function(){
                            $(this).dialog("close");
                        }   
                    }
		  });

	  $link.click(function() {
		  $dialog.dialog('open');
		  return false;
	  });
  });
</script>