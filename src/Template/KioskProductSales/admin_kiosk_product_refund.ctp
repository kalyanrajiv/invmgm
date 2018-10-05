<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<div class="productReceipts form">
<?php
	$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
	$url = $this->Url->build(array('controller' => 'customers', 'action' => 'get_address'));

	echo $this->Form->create('KioskProductSale');
    //pr($this->request->data);die;
    //pr($product_receipt_data);die;
	if(array_key_exists('street_address',$this->request->data)){
		$fname = $this->request->data['fname'];
		$lname = $this->request->data['lname'];
		$email = $this->request->data['email'];
		$mobile = $this->request->data['mobile'];
		$zip = $this->request->data['zip'];
		$address_1 = $this->request->data['address_1'];
		$address_2 = $this->request->data['address_2'];
		$city = $this->request->data['city'];
		$state = $this->request->data['state'];
	}else{
		$fname = $product_receipt_data[0]['fname'];
		$lname = $product_receipt_data[0]['lname'];
		$email = $product_receipt_data[0]['email'];
		$mobile = $product_receipt_data[0]['mobile'];
		$zip = $product_receipt_data[0]['zip'];
		$address_1 = $product_receipt_data[0]['address_1'];
		$address_2 = $product_receipt_data[0]['address_2'];
		$city = $product_receipt_data[0]['city'];
		$state = $product_receipt_data[0]['state'];
	}
	
	
	if(isset($request_data)){
		//pr($request_data);
		$fname = $request_data['KioskProductSale']['fname'];
		$lname = $request_data['KioskProductSale']['lname'];
		$email = $request_data['KioskProductSale']['email'];
		$mobile = $request_data['KioskProductSale']['mobile'];
		$zip = $request_data['KioskProductSale']['zip'];
		$address_1 = $request_data['KioskProductSale']['address_1'];
		$address_2 = $request_data['KioskProductSale']['address_2'];
		$city = $request_data['KioskProductSale']['city'];
		$state = $request_data['KioskProductSale']['state'];
		$refund_state = $request_data['KioskProductSale']['refund_status'];
		$refund_remarks = $request_data['KioskProductSale']['refund_remarks'];
		$refund_price = $request_data['KioskProductSale']['refund_price'];
	}
	
?>
	<fieldset>
		<legend><?php echo __('Refund Product'); ?></legend>
		<div>
		<?php //pr($products);
		
		echo "<table>";
		echo "<tr>";
		echo "<th>Product code";
		echo "<th>Product Title";
		echo "<th>Product Image";
		echo "</tr>";
		//pr($products);die;
        foreach($products as $product ){
			//pr($product);die;
			echo "<tr>";
				echo "<td>".$product['product_code']."</td>";
				echo "<td>".$product ['product']."</td>";
				echo "<td>";
					$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product['id'].DS;
					$imageName =  $product['image'];
					$absoluteImagePath = $imageDir.$imageName;
					$imageURL = "$siteBaseURL/thumb_no-image.png";
					if(@readlink($absoluteImagePath) || file_exists($absoluteImagePath)){
						$imageURL = "$siteBaseURL/files/Products/image/".$product['id']."/$imageName"; //rasu
					}
					echo $this->Html->link(
						$this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')), //rasu
						array('controller' => 'products','action' => 'edit', $product ['id']),
						array('escapeTitle' => false, 'title' => $product['product'])
					);
            echo "</td>";
            echo "</tr>";
		}
		
		echo "</table>";
		?>
        <?php //pr($this->request['data']);die; ?>
		<?php $discountedPrice = $this->request['data']['sale_price']-($this->request['data']['sale_price']*$this->request['data']['discount']/100);
		
		if($discountedPrice<$this->request['data']['sale_price']){?>
			Item sold on <?php echo date('M jS, Y',strtotime($this->request->data['created']));//$this->Time->format('M jS, Y', $this->request->data['created'],null,null); ?>
			at <b><span style="color: red;"><?php echo $CURRENCY_TYPE; echo $discountedPrice ;?></span></b> with a discount of <?php echo ( number_format($this->request['data']['discount'],2));?>% (Actual Price <?php echo $CURRENCY_TYPE; echo $this->request['data']['sale_price'] ;?>).</br></br>
			<h4><b>Note:</b> The above price is for one item only.</h4>
		<?php }else{ ?>
	 
			Item sold on <?php  echo date('M jS, Y',strtotime($this->request->data['created']));//echo $this->Time->format('M jS, Y', $this->request->data['created'],null,null); ?> at  <?php echo $CURRENCY_TYPE; echo ( $this->request['data']['sale_price']) ?> each</br></br>
			<h4><b>Note:</b> The above price is for one item only.</h4>
		<?php } ?>
		
		</div> 
		
		
		<legend><?php echo __('Details of customer'); ?></legend>
	<?php
	$customerData = $this->Url->build(array('controller' => 'retail_customers', 'action' => 'get_customer_ajax'));
//	echo "<div id='remote'>";
//	echo "<input name='cust_email' class='typeahead' id='cust_email' placeholder='check existing customer email' style='width:250px;padding-right:10px;'/>";echo "&nbsp;&nbsp;<a href='#' id='check_existing' rel = '$customerData'>Check Existing</a>";
//    echo "</div>";
	  echo "<table>";
		//customer details
		echo "<tr>";
		echo "<td colspan='3'>";
		echo ('<h4>Customer Details</h4><hr/>');
		echo "</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>".$this->Form->input('fname', array('id' => 'KioskProductSaleFname','name' => 'KioskProductSale[fname]','label' => 'First Name','placeholder' => 'First name','value' => $fname))."</td>";
		 	echo "<td>".$this->Form->input('lname', array('id' => 'KioskProductSaleLname','name' => 'KioskProductSale[lname]','label' => 'Last Name','placeholder' => 'Last name', 'value' => $lname))."</td>";
			echo "<td>".$this->Form->input('email',array('id' => 'KioskProductSaleEmail','name' => 'KioskProductSale[email]','label' => 'Email','placeholder' => 'Enter email', 'value' => $email))."</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td>".$this->Form->input('mobile',array('id' => 'KioskProductSaleMobile','name' => 'KioskProductSale[mobile]','label' => 'Mobile No','placeholder' => 'Enter Mobile no','maxLength'=>11, 'value' => $mobile))."</td>";
			echo "<td><div class='input text'>".$this->Form->input('zip',array(
                                                        'id' => 'KioskProductSaleZip',
                                                        'name' => 'KioskProductSale[zip]',
													   'placeholder' => 'Postcode',
													   'label' => 'Postcode',
													   'rel' => $url,
													   'style'=>'width:70px',
													   'div' => false,
													   'value' => $zip
													   ));
			echo "<button type='button' id='find_address' class='btn' style='margin-top: 6px;margin-left: 8px;width: 112px;height: 22px;'>Find my address</button>"."</div></td>";
			echo "<td>".$this->Form->input('address_1', array('id' => 'KioskProductSaleAddress1','name' => 'KioskProductSale[address_1]','placeholder' => 'property name/no. and street name', 'value' => $address_1)) ;
		?>
			<select name='street_address' id='street_address'><option>--postcode--</option></select>
				<span id='address_missing'><br/><a href='#-1'>Address is not on the list</a></span>
		<?php
			echo "</td>";
		echo "</tr>";
		echo "<tr>";
				echo "<td>";
						echo $this->Form->input('address_2', array('id' => 'KioskProductSaleAddress2','name' => 'KioskProductSale[address_2]','placeholder' => "further address details (optional)", 'value' => $address_2));
						echo "</td>";
						echo "<td>";
								echo $this->Form->input('city',array('id' => 'KioskProductSaleCity','name' => 'KioskProductSale[city]','label' => 'Town/City','placeholder' => "name of town or city", 'value' => $city));
						echo "</td>";
						 echo "<td>";
								echo $this->Form->input('state',array('id' => 'KioskProductSaleState','name' => 'KioskProductSale[state]','label' => 'state','placeholder' => "name of State", 'value' => $state));
						echo "</td>";
		echo "</tr>";
		echo "</table>";
		echo "<table>";
        //pr($this->request->data);die;
						$quantityArr = array();
						$quantity = $this->request->data['quantity'];
						for($i=1;$i<=$quantity;$i++){
							$quantityArr[$i] = $i;
						}
				echo "<tr>";
				echo "<td>";
				echo $this->Form->input('quantity_returned', array('id' => 'KioskProductSaleQuantityReturned','name' => 'KioskProductSale[quantity_returned]','options' => $quantityArr));
				echo "</td>";
			 
				echo $this->Form->input('id', array('id' => 'KioskProductSaleId','name' => 'KioskProductSale[id]','type' => 'hidden'));
				echo $this->Form->input('kiosk_id', array('id' => 'KioskProductSaleKioskId','name' => 'KioskProductSale[kiosk_id]','type' => 'hidden'));
				echo $this->Form->input('sold_by', array('id' => 'KioskProductSaleSoldBy','name' => 'KioskProductSale[sold_by]','type' => 'hidden'));
				echo $this->Form->input('refund_by', array('id' => 'KioskProductSaleRefundBy','name' => 'KioskProductSale[refund_by]','type' => 'hidden'));
				echo $this->Form->input('status', array('id' => 'KioskProductSaleStatus','name' => 'KioskProductSale[status]','type' => 'hidden'));
				echo $this->Form->input('discount_status', array('id' => 'KioskProductSaleDiscountStatus','name' => 'KioskProductSale[discount_status]','type' => 'hidden'));
				echo $this->Form->input('sale_price', array('id' => 'KioskProductSaleSalePrice','name' => 'KioskProductSale[sale_price]','type' => 'hidden'));
				echo $this->Form->input('quantity', array('id' => 'KioskProductSaleQuantity','name' => 'KioskProductSale[quantity]','type' => 'hidden'));
				echo $this->Form->input('discount', array('id' => 'KioskProductSaleDiscount','name' => 'KioskProductSale[discount]','type' => 'hidden'));
				echo $this->Form->input('product_receipt_id',array('id' => 'KioskProductSaleProductReceiptId','name' => 'KioskProductSale[product_receipt_id]','type' => 'hidden'));
			    echo $this->Form->input('product_id',array('id' => 'KioskProductSaleProductId','name' => 'KioskProductSale[product_id]','type' => 'hidden'));
				 echo $this->Form->input('created',array('id' => 'KioskProductSaleCreated','name' => 'KioskProductSale[created]','type' => 'hidden'));
				 
					//value=" $this->request['data']['KioskProductSale']['sale_price']"; //bug by inderpreet
				 //pr($refundOptions);die;
				 if(isset($refund_price)){
						echo "<td>";	echo $this->Form->input('refund_price', array('id' => 'KioskProductSaleRefundPrice','name' => 'KioskProductSale[refund_price]','type'=>'text','value' => $refund_price));echo "</td>";	
				 }else{
					echo "<td>";	echo $this->Form->input('refund_price', array('id' => 'KioskProductSaleRefundPrice','name' => 'KioskProductSale[refund_price]','type'=>'text'));echo "</td>";
				 }
				 
				 if(isset($refund_state)){
						echo "<td>";	echo $this->Form->input('refund_status',array('id' => 'KioskProductSaleRefundStatus','name' => 'KioskProductSale[refund_status]','label' => 'Refund Reason', 'options' => $refundOptions,'value' => $refund_state));echo "</td>";	
				 }else{
					echo "<td>";	echo $this->Form->input('refund_status',array('id' => 'KioskProductSaleRefundStatus','name' => 'KioskProductSale[refund_status]','label' => 'Refund Reason', 'options' => $refundOptions));echo "</td>";
				 }
				 
				 if(isset($refund_remarks)){
					echo "<td>";	echo $this->Form->input('refund_remarks',array('id' => 'KioskProductSaleRefundRemarks','name' => 'KioskProductSale[refund_remarks]','value' => $refund_remarks));	
				 }else{
					echo "<td>";	echo $this->Form->input('refund_remarks',array('id' => 'KioskProductSaleRefundRemarks','name' => 'KioskProductSale[refund_remarks]'));
				 }
				echo "</table>";
	?>
		
	</fieldset>
<?php
echo $this->Form->Submit(__('Refund'));
echo $this->Form->end();
?>
</div>
<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('View Sale'), array('action' => 'index')); ?></li>        
        <li><?php echo $this->Html->link(__('New Sale'), array('action' => 'new_order')); ?> </li>
            
    </ul>
</div>
<script>
	$(function() {
		$('#address_missing').click(function(){
			$('#street_address').hide();
			$('#KioskProductSaleAddress1').show("");
			$('#KioskProductSaleAddress1').val("");
			$('#KioskProductSaleAddress2').val("");
			$('#KioskProductSaleCity').val("");
			$('#KioskProductSaleState').val("");		
			$(this).hide();
		});
		
		$( "#street_address" ).select(function() {
			alert($( "#street_address" ).val());
			$('#KioskProductSaleAddress1').val($( "#street_address" ).val());
			$('#KioskProductSaleAddress1').show();
			$('#address_missing').hide();
			$(this).hide();
		});
		
		$( "#street_address" ).change(function() {
			$('#KioskProductSaleAddress1').val($( "#street_address" ).val());
			$('#KioskProductSaleAddress1').show();
			$('#address_missing').hide();
			$(this).hide();
		});
		
		$("#find_address").click(function() {
			var zipCode = $("#KioskProductSaleZip").val();
			//$.blockUI({ message: 'Just a moment...' });
			//focus++;
			//alert("focusout:"+zipCode);
			//$( "#focus-count" ).text( "focusout fired: " + focus + "x" );	
			var zipCode = $("#KioskProductSaleZip").val();
			var targeturl = $("#KioskProductSaleZip").attr('rel') + '?zip=' + escape(zipCode);		
			$.blockUI({ message: 'Just a moment...' });
			$.ajax({
				type: 'get',
				url: targeturl,
				beforeSend: function(xhr) {
					xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				},
				success: function(response) {
					$.unblockUI();
					var obj = jQuery.parseJSON( response);			
					if (response) {
						if (obj.ErrorNumber == 0) {
							$('#street_address').show();
							$('#KioskProductSaleAddress1').hide("");
							$('#address_missing').show();
							var toAppend = '';
							$('#street_address').find('option').remove().end();
							$.each(obj.Street, function( index, value ) {
								//alert( index + ": " + value );
								toAppend += '<option value="'+value+'">'+value+'</option>';
							});
							$('#street_address').append(toAppend);
							$('#KioskProductSaleAddress2').val(obj.Address2);
							$('#KioskProductSaleCity').val(obj.Town);
							$('#KioskProductSaleState').val(obj.County);
						}else{
							alert("Error Code: "+obj.ErrorNumber+ ", Error Message: "+ obj.ErrorMessage);
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
		});
		$('#KioskProductSaleAddress1').show();
		$('#street_address').hide();
		$('#address_missing').hide();
		
		//------------------
		$("#KioskProductSaleMobile").keydown(function (event) {  
			if (event.shiftKey == true) {event.preventDefault();}
			if ((event.keyCode >= 48 && event.keyCode <= 57) ||
				(event.keyCode >= 96 && event.keyCode <= 105) ||
				event.keyCode == 8 || event.keyCode == 9 ||
				event.keyCode == 37 || event.keyCode == 39 ||
				event.keyCode == 46 || event.keyCode == 183 || event.keyCode == 110) {
				;
			}else{
			 event.preventDefault();
			}
		});
		
	});
</script>
<script>
 var user_dataset = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  //prefetch: "/products/data",
  remote: {
    url: "/retail-customers/custemail?search=%QUERY",
    wildcard: "%QUERY"
  }
});

$('#remote .typeahead').typeahead(null, {
  name: 'email',
  display: 'email',
  source: user_dataset,
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
    suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:400px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{email}}</a></strong></div>'),
	header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------hpwaheguru.co.uk---------</b></div>"),
  }
});
</script>
<script>
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
				$("#KioskProductSaleFname").val(obj.fname);
				$("#KioskProductSaleLname").val(obj.lname);
				$("#KioskProductSaleContact").val(obj.mobile);
				$("#KioskProductSaleEmail").val(obj.email);
				$("#KioskProductSaleZip").val(obj.zip);
				$("#MobilePurchaseCustomerAddress1").val(obj.address_1);
				$("#MobilePurchaseCustomerAddress2").val(obj.address_2);
				$("#KioskProductSaleCity").val(obj.city);
				$("#KioskProductSaleState").val(obj.state);
				var country = obj.country;
				if (country != "") {
					if (country) {
                     // alert(obj.country);
					   $("#KioskProductSaleCountry").val(obj.country);
                    } 
                }
				
				
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
</script>