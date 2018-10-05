<style>
	.ui-draggable {
		width: 500px !important;
	}
	.ui-dialog .ui-dialog-content {
		height: auto !important;
	}
	.ui-dialog-titlebar-close {
		visibility: hidden;
	      }
</style>
<div id="dialog-confirm" title="Sale Terms">
	<?php echo $terms_resale;?>
</div>
<div class="mobileReSales form">
    <div id='remote'><input type="text" class="typeahead" id="search_customer" placeholder="search customer by email/phone" style="width: 250px;"/></div>
	<input type="button" id="search_customer_button" value="Search" style="width: 86px;"/>
<?php
	$url = $this->Url->build(array('controller' => 'customers', 'action' => 'get_address'));
	echo $this->Form->create($MobileBlkReSalesEntity, array(
													  'id' => 'MobileBlkReSaleAddForm',
													  'onSubmit' => 'return validateForm();'));
	$brand_id = $mobilePurchaseData['brand_id'];
	$mobile_model_id = $mobilePurchaseData['mobile_model_id'];
	$grade = $mobilePurchaseData['grade'];
	$type = $mobilePurchaseData['type'];
	$custumGrade = $mobilePurchaseData['custom_grades'];
	$network_id = $mobilePurchaseData['network_id'];
	$color = $mobilePurchaseData['color'];
	$imei = $mobilePurchaseData['imei'];
	$imei1 = substr($imei, -1);
	$imei2 = substr_replace($imei,'',-1) ;
	$kiosk_id = $this->request->Session()->read('kiosk_id');
	$customGradeMob = false;
	if($mobilePurchaseData['purchase_status'] == 1 && $mobilePurchaseData['custom_grades'] == 1){
		$customGrade = $mobilePurchaseData['grade'];
		$gradeType[$customGrade] = $customGrade;
		$customGradeMob = true;
		$lowestSP = $mobilePurchaseData['lowest_selling_price'];
		if(empty($lowestSP)){
			$lowestSP = $mobilePurchaseData['selling_price'];
		}
		$staticSP = $mobilePurchaseData['static_selling_price'];
		if(empty($lowestSP)){
			$staticSP = $mobilePurchaseData['static_selling_price'];
		}
	}
?>
	<fieldset>
		<legend><?php echo __('Add Mobile Re Sale'); ?></legend>
		<div id="error_div" tabindex='1'></div>
	<?php
		echo $this->Form->input('kiosk_id',array('id' => 'MobileBlkReSaleKioskId','name' => 'MobileBlkReSale[kiosk_id]','type'=>'hidden','value'=>$kiosk_id));
		
		//customer details
		echo ('<h4>Customer Details</h4><hr/>');
		echo "<table>";
			echo "<tr>";
				echo "<td>";
					echo $this->Form->input('customer_fname', array('id' => 'MobileBlkReSaleCustomerFname','name' => 'MobileBlkReSale[customer_fname]','label' => 'First Name'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('customer_lname', array('id' => 'MobileBlkReSaleCustomerLname','name' => 'MobileBlkReSale[customer_lname]','label' => 'Last Name'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('customer_email',array('id' => 'MobileBlkReSaleCustomerEmail','name' => 'MobileBlkReSale[customer_email]'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('customer_contact',array('id' => 'MobileBlkReSaleCustomerContact','name' => 'MobileBlkReSale[customer_contact]','maxLength'=>11));
				echo "</td>";
			echo "<tr>";
		echo "<table>";		
		
		echo "<table>";
			echo "<tr>";
				echo "<td>";
					echo "<table>";
						echo "<tr>";
							echo "<td>";
							echo $this->Form->input('zip',array('id' => 'MobileBlkReSaleZip','name' => 'MobileBlkReSale[zip]','placeholder' => 'Postcode', 'label'=>false, 'rel' => $url,'size'=>'10px','style'=>'width: 120px;'));
							echo "</td>";
							echo "<td>";
							echo "<button type='button' id='find_address' class='btn' style='margin-top: 6px;margin-left: -8px;width: 130px;height: 29px;'>Find my address</button>";
							echo "</td>";
						echo "</tr>";
					echo "</table>";
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('customer_address_1', array('id' => 'MobileBlkReSaleCustomerAddress1','name' => 'MobileBlkReSale[customer_address_1]','placeholder' => 'property name/no. and street name'));
	?>
				<select name='street_address' id='street_address'><option>--postcode--</option></select>
				<span id='address_missing'><br/><a href='#-1'>Address is not on the list</a></span>
				
				</td>
	<?php
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('customer_address_2', array('id' => 'MobileBlkReSaleCustomerAddress2','name' => 'MobileBlkReSale[customer_address_2]','placeholder' => "further address details (optional)"));
				echo "</td>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td>";
					echo $this->Form->input('city',array('id' => 'MobileBlkReSaleCity','name' => 'MobileBlkReSale[city]','label' => 'Town/City','placeholder' => "name of town or city"));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('state',array('id' => 'MobileBlkReSaleState','name' => 'MobileBlkReSale[state]','label'=>'County', 'placeholder' => "name of county (optional)"));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('country',array('id' => 'MobileBlkReSaleCountry','name' => 'MobileBlkReSale[country]','options'=>$countryOptions));
				echo "</td>";
			echo "</tr>";
		echo "</table>";
		//phone details
		echo ('<h4>Mobile Details</h4><hr/>');
		echo "<table>";
			echo "<tr>";
				echo "<td>";
					echo $this->Form->input('brand_id',array('id' => 'MobileBlkReSaleBrandId','name' => 'MobileBlkReSale[brand_id]','options'=>$brands,'value'=>$brand_id,'disabled'=>'disabled'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('mobile_model_id',array('id' => 'MobileBlkReSaleMobileModelId','name' => 'MobileBlkReSale[mobile_model_id]','value'=>$mobile_model_id,'disabled'=>'disabled'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('grade',array('id' => 'MobileBlkReSaleGrade','name' => 'MobileBlkReSale[grade]','options' => $gradeType,'empty'=>'Choose Grade','value' => $grade,'disabled'=>'disabled'));
					echo $this->Form->input('custom_grade',array('id' => 'MobileBlkReSaleCustomGrade','name' => 'MobileBlkReSale[custom_grade]','value' => $custumGrade,'type' => 'hidden'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('type',array('id' => 'MobileBlkReSaleType','name' => 'MobileBlkReSale[type]','options'=>array('1'=>'locked','0'=>'unlocked'),'empty'=>'-Choose-','value'=>$type,'disabled'=>'disabled'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('network_id',array('id' => 'MobileBlkReSaleNetworkId','name' => 'MobileBlkReSale[network_id]','options'=>$networks,'empty'=>'-Choose-','value'=>$network_id,'disabled'=>'disabled'));
				echo "</td>";
			echo "<tr>";
		echo "</table>";
		echo $this->Form->input('retail_customer_id',array('type'=>'hidden','value'=>0,'id'=>'retail_customer_id','name' => 'MobileBlkReSale[retail_customer_id]'));
		echo $this->Form->input('brand_id',array('type'=>'hidden','value'=>$brand_id,'id' => 'MobileBlkReSaleBrandId','name' => 'MobileBlkReSale[brand_id]'));
		echo $this->Form->input('network_id',array('type'=>'hidden','value'=>$network_id,'id' => 'MobileBlkReSaleNetworkId','name' => 'MobileBlkReSale[network_id]'));
		echo $this->Form->input('mobile_model_id',array('type'=>'hidden','value'=>$mobile_model_id,'id' => 'MobileBlkReSaleMobileModelId','name' => 'MobileBlkReSale[mobile_model_id]'));
		echo $this->Form->input('grade',array('type'=>'hidden','value'=>$grade,'id' => 'MobileBlkReSaleGrade','name' => 'MobileBlkReSale[grade]'));
		echo $this->Form->input('type',array('type'=>'hidden','value'=>$type,'id' => 'MobileBlkReSaleType','name' => 'MobileBlkReSale[type]'));
		echo $this->Form->input('color',array('type'=>'hidden','value'=>$color,'id' => 'MobileBlkReSaleColor','name' => 'MobileBlkReSale[color]'));
		
		echo "<table>";
		echo "<tr>";
		echo "<td style='width: 134px'>";
		echo $this->Form->input('imei',array('id' => 'MobileBlkReSaleImei','name' => 'MobileBlkReSale[imei]','label' => 'IMEI', 'maxlength'=>14,'value'=>$imei2,'readonly'=>'readonly','style'=>"width: 115px;"));
		echo "</td>";
		echo "<td>";
		echo $this->Form->input('imei1',array('name' => 'MobileBlkReSale[imei1]','type' => 'text','label' => false, 'id' =>'imei1','readonly'=>'readonly','style'=>"width: 30px;margin-top: 18px", 'value' => $imei1));
		echo "</td>";
		echo "<td>";
		echo $this->Form->input('color',array('id' => 'MobileBlkReSaleColor','name' => 'MobileBlkReSale[color]','options'=>$colorOptions,'value'=>$color,'disabled'=>'disabled'));
		echo "</td>";
		echo "<td>";
		if(!empty($staticSP)){
			echo $this->Form->input('static_selling_price',array('id' => 'MobileBlkReSaleStaticSellingPrice','name' => 'MobileBlkReSale[static_selling_price]','label' => 'Recommended SP', 'type' => 'text','value'=> $staticSP, 'style' => 'width: 100px;', 'div' => false,'disabled' => true));
		}
		echo $this->Form->input('cost_price',array('id' => 'MobileBlkReSaleCostPrice','name' => 'MobileBlkReSale[cost_price]','type'=>'hidden','label' => 'Cost Price', 'value'=>$mobileCostPrice,'readonly'=>'readonly'));
		echo "</td>";
		echo "<td>";
		echo $this->Form->input('selling_price',array('id' => 'MobileBlkReSaleSellingPrice','name' => 'MobileBlkReSale[selling_price]','label' => 'Actual Selling Price', 'type' => 'text','value'=> $mobileSalePrice, 'style' => 'width: 100px;', 'div' => false));
		if($customGradeMob){
			echo "<a href='#-1' title='Lowest Selling Price: $lowestSP', alt ='Lowest Selling Price: $lowestSP' id='lowest_selling_price' style='padding-left:15px;'>##</a>";
			echo $gradePriceJS = <<<GRADE_JS
			<script>
			\$(document).ready(function(){
				\$(function() {\$( '#lowest_selling_price' ).tooltip();});
					});
					</script>
GRADE_JS;
				}
		echo "</td>";
		
				if($maximum_discount>0){
					$allowedDiscount = array();
					foreach($discountOptions as $value=>$percentage){
						if($value>$maximum_discount)break;
						$allowedDiscount[$value] = $percentage;
					}
					echo "<td>";
						echo $this->Form->input('discount',array('options'=>$allowedDiscount));
					echo "</td>";
					echo "<td>";
						echo $this->Form->input('discounted_price',array('type'=>'text','readonly'=>'readonly'));
					echo "<td>";
				}
			echo "<tr>";
		echo "</table>";
		#echo $this->Form->input('selling_date',array('label' => 'Selling Date')); discountOptions
		echo $this->Form->input('description',array('id' => 'MobileBlkReSaleDescription','name' => 'MobileBlkReSale[description]'));
		
		echo ('<h4>Miscellaneous</h4><hr/>');
		echo $this->Form->input('brief_history', array('id' => 'MobileBlkReSaleBriefHistory','name' => 'MobileBlkReSale[brief_history]','label' => 'Resale History(For Internal Use)'));		
		#echo $this->Form->input('status',array('options' => $resaleOptions));
	?>
	</fieldset>
<?php
echo $this->Form->submit("Submit",['name'=>'submit']);
echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Mobile Sales'), array('action' => 'index')); ?></li>
		 
		<li><?php echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		 
	</ul>
</div>
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
  $(function() {
    $( "#dialog-confirm" ).dialog({
      resizable: false,
      height:140,
      modal: true,
      buttons: {
        "Agree": function() {
          $( this ).dialog( "close" );
        },
        Cancel: function() {
          document.location.href = "<?php echo $this->Url->build(array('controller'=>'mobile_purchases','action'=>'index'));?>";
        }
      }
    });
  });
  </script>
<script>
	function isValidEmailAddress(emailAddress) {
		var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
		return pattern.test(emailAddress);
	}
$(function() {
	$('#address_missing').click(function(){
		$('#street_address').hide();
		$('#MobileBlkReSaleCustomerAddress1').show("");
		$('#MobileBlkReSaleCustomerAddress1').val("");
		$('#MobileBlkReSaleCustomerAddress2').val("");
		$('#MobileBlkReSaleCity').val("");
		$('#MobileBlkReSaleState').val("");		
		$(this).hide();
	});
	$( "#street_address" ).select(function() {
		alert($( "#street_address" ).val());
		$('#MobileBlkReSaleCustomerAddress1').val($( "#street_address" ).val());
		$('#MobileBlkReSaleCustomerAddress1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$( "#street_address" ).change(function() {
		$('#MobileBlkReSaleCustomerAddress1').val($( "#street_address" ).val());
		$('#MobileBlkReSaleCustomerAddress1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$("#find_address").click(function() {
		var zipCode = $("#MobileBlkReSaleZip").val();
		//$.blockUI({ message: 'Just a moment...' });
		//focus++;
		//alert("focusout:"+zipCode);
		//$( "#focus-count" ).text( "focusout fired: " + focus + "x" );	
		var zipCode = $("#MobileBlkReSaleZip").val();
		if (zipCode == "") {
            alert("Please Input Postcode");
			return false;
        }
		
		
		var targeturl = $("#MobileBlkReSaleZip").attr('rel') + '?zip=' + escape(zipCode);		
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
						$('#MobileBlkReSaleCustomerAddress1').hide("");
						$('#address_missing').show();
						var toAppend = '';
						$('#street_address').find('option').remove().end();
						$.each(obj.Street, function( index, value ) {
							//alert( index + ": " + value );
							toAppend += '<option value="'+value+'">'+value+'</option>';
						});
						$('#street_address').append(toAppend);
						$('#MobileBlkReSaleCustomerAddress2').val(obj.Address2);
						$('#MobileBlkReSaleCity').val(obj.Town);
						$('#MobileBlkReSaleState').val(obj.County);
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
	$('#MobileBlkReSaleCustomerAddress1').show();
	$('#street_address').hide();
	$('#address_missing').hide();
	$("#MobileBlkReSaleCustomerContact").keydown(function (event) {  
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
	
	$('#MobileBlkReSaleImei1').keyup(function(event){ // changed name MobileBlkReSaleImei to MobileBlkReSaleImei1 on 20 jan 
        if ($('#MobileBlkReSaleImei').val().length == 14 && ((event.keyCode >= 48 && event.keyCode <= 57 ) ||
		( event.keyCode >= 96 && event.keyCode <= 105))) {
            var i;
            var singleNum;
            var finalStr = 0;
            var total = 0;
            var numArr = $('#MobileBlkReSaleImei').val().split('');
            
            for (i = 0; i < $('#MobileBlkReSaleImei').val().length; i++) {
                if (i%2 != 0) {
                    //since array starts with 0 key, multiplying the key which is not divisible by 2 with 2 ie. 1,3,5 etc till 13
                    singleNum = 2*numArr[i];
                } else {
                    singleNum = numArr[i];
                }
                finalStr+=singleNum;
            }
            
            //below creating the array from string and applying foreach to sumup all the values
            var finalArr = finalStr.split('');
            $.each(finalArr, function(key,numb){
                total+=parseInt(numb);
            });
            
            //now for example the total is 52, we need to add 8 to make it 60 ie. divisible by 10. Then 8 will be the next number in imei
            var Dnum = parseInt(Math.ceil(total/10)*10-total);//this is the required number
            var newNumb = $('#MobileBlkReSaleImei').val() + Dnum;
             $('#imei1').val(Dnum);
        }
    });
});
function validateForm(){
	  if ($('#MobileBlkReSaleCustomerFname').val() == '') {
		$('#error_div').html("Please Enter First Name").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert("Please Enter First Name");
		return false;
	} 
	if ($('#MobileBlkReSaleCustomerEmail').val() == '') {
          // $('#error_div').html("Please input the customer's email").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
        //  alert("Please input the customer's email");
         // return false;
      }else if (!isValidEmailAddress($('#MobileBlkReSaleCustomerEmail').val())) {
           $('#error_div').html("Please input valid email address!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
          alert('Please input valid email address!');
          return false;
      }
	 
    if ($('#MobileBlkReSaleCustomerContact').val() == '') {
		$('#error_div').html("Please input the phone number").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert("Please input the phone number");
		return false;
	}else if ($('#MobileBlkReSaleCustomerContact').val().length < 11) {
		$('#error_div').html("Phone number should be minimum 11 characters long!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert('Phone number should be minimum 11 characters long!');
		return false;
	}
	if ($('#MobileBlkReSaleDescription').val() == "") {
		$('#error_div').html('Please input the fault description!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert("Please input the fault description!");
		return false;
	}
}

</script>
<script>
	$('#MobileBlkReSaleDiscount').change(function(){
		var discount = $("#MobileBlkReSaleDiscount").val();
		var sellingPrice = $("#MobileBlkReSaleSellingPrice").val();
		var costPrice = $("#MobileBlkReSaleCostPrice").val();
		var result = sellingPrice - discount * sellingPrice / 100;
		if(result<costPrice){
			alert("Discounted price cannot be lesser than the cost price");
			return false;
		} else {
			$('#MobileBlkReSaleDiscountedPrice').val(result);
		}
	})
</script>
<script>
	$('#search_customer_button').click(function(){
		if ($('#search_customer').val() == '') {
            alert('Please fill customer email/phone to search!');
        } else {
			$.blockUI({ message: 'Just a moment...' });
			var mainUrl = '<?php echo $this->Url->build(array('controller' => 'retail_customers', 'action' => 'get_customer_details_ajax'));?>';
			var targetUrl = mainUrl + '?search_kw=' + $('#search_customer').val();
				$.ajaxSetup({url: targetUrl, success: function(result){
					$.unblockUI();
					var obj = JSON.parse(result);
					if (obj.error == 1) {
						alert('No data found!');
						$('#MobileBlkReSaleCustomerFname').val('');
						$('#MobileBlkReSaleCustomerLname').val('');
						$('#MobileBlkReSaleCustomerEmail').val($('#search_customer').val());
						$('#MobileBlkReSaleCustomerContact').val('');
						$('#MobileBlkReSaleZip').val('');
						$('#MobileBlkReSaleCustomerAddress1').val('');
						$('#MobileBlkReSaleCustomerAddress2').val('');
						$('#MobileBlkReSaleCity').val('');
						$('#MobileBlkReSaleState').val('');
						$('#MobileBlkReSaleCountry').val('');
					} else {
						$('#MobileBlkReSaleCustomerFname').val(obj.fname);
						$('#MobileBlkReSaleCustomerLname').val(obj.lname);
						$('#MobileBlkReSaleCustomerEmail').val(obj.email);
						$('#MobileBlkReSaleCustomerContact').val(obj.mobile);
						$('#MobileBlkReSaleZip').val(obj.zip);
						$('#MobileBlkReSaleCustomerAddress1').val(obj.address_1);
						$('#MobileBlkReSaleCustomerAddress2').val(obj.address_2);
						$('#MobileBlkReSaleCity').val(obj.city);
						$('#MobileBlkReSaleState').val(obj.state);
						$('#MobileBlkReSaleCountry').val(obj.country);
						$('#retail_customer_id').val(obj.id);
					}
				},
				error: function(e) {
					$.unblockUI();
					alert("An error occurred: " + e.responseText.message);
					console.log(e);
				}
			});
			$.ajax();
		}
	});
</script>
<script>
	 $('#MobileBlkReSaleSellingPrice').blur(function(){
		var price = $("#MobileBlkReSaleSellingPrice").val();
		var orginal_val = $('#MobileBlkReSaleStaticSellingPrice').val();
		var minprice = '<?php echo $lowestSP;?>';
		if (parseInt(price) < parseInt(minprice)) {
			$('#MobileBlkReSaleSellingPrice').val(orginal_val);
            alert("Selling amount cannot be less then minimum selling price");
			
        }
	 });
</script>