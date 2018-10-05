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
    <div id='remote'>
        <input type="text" id="search_customer" class="typeahead" placeholder="search customer by email/phone" style="width: 250px;"/>
    </div>
	<input type="button" id="search_customer_button" value="Search" style="width:86px;margin-top:-19px;margin-left:273px;"/>
<?php
	$url = $this->Url->build(array('controller' => 'customers', 'action' => 'get_address'));
	echo $this->Form->create($resaleEntity, array(
													'id' => 'MobileReSaleAddForm',
												   'onSubmit' => 'return validateForm();',
												   
												   ));
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
	$lowestSPIfTopup = $lowestSP = "";
	if($mobilePurchaseData['purchase_status'] == 1 && $mobilePurchaseData['custom_grades'] == 1){
		$customGrade = $mobilePurchaseData['grade'];
		$gradeType[$customGrade] = $customGrade;
		$customGradeMob = true;
		$lowestSP = $mobilePurchaseData['lowest_selling_price'];
		if(empty($lowestSP)){
			$lowestSP = $mobilePurchaseData['selling_price'];
		}
	}
	if(!empty($mobilePurchaseData['topedup_price']) && is_numeric($mobilePurchaseData['topedup_price'])){
		$lowestSPIfTopup = $mobilePurchaseData['topedup_price'];
	}else{
		$lowestSPIfTopup = $lowestSP;
	}
	
	
?>
	<fieldset>
		<legend><?php echo __('Add Mobile Re Sale'); ?></legend>
		<div id="error_div" tabindex='1'></div>
	<?php
		echo $this->Form->input('kiosk_id',array('id' => 'MobileReSaleKioskId','name' => 'MobileReSale[kiosk_id]','type'=>'hidden','value'=>$kiosk_id));
		
		//customer details
		echo ('<h4>Customer Details</h4><hr/>');
		echo "<table>";
			echo "<tr>";
				echo "<td>";
					echo $this->Form->input('customer_fname', array('id' => 'MobileReSaleCustomerFname','name' => 'MobileReSale[customer_fname]','label' => 'First Name'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('customer_lname', array('id' => 'MobileReSaleCustomerLname','name' => 'MobileReSale[customer_lname]','label' => 'Last Name'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('customer_email',array('id' => 'MobileReSaleCustomerEmail','name' => 'MobileReSale[customer_email]'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('customer_contact',array('maxLength'=>11,'id' => 'MobileReSaleCustomerContact','name' => 'MobileReSale[customer_contact]'));
				echo "</td>";
			echo "<tr>";
		echo "<table>";		
		
		echo "<table>";
			echo "<tr>";
				echo "<td>";
					echo "<table>";
						echo "<tr>";
							echo "<td>";
							echo $this->Form->input('zip',array('id' => 'MobileReSaleZip','name' => 'MobileReSale[zip]','placeholder' => 'Postcode', 'label'=>false, 'rel' => $url,'size'=>'10px','style'=>'width: 120px;'));
							echo "</td>";
							echo "<td>";
							echo "<button type='button' id='find_address' class='btn' style='margin-top: 6px;margin-left: -8px;width: 130px;height: 29px;'>Find my address</button>";
							echo "</td>";
						echo "</tr>";
					echo "</table>";
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('customer_address_1', array('id' => 'MobileReSaleCustomerAddress1','name' => 'MobileReSale[customer_address_1]','placeholder' => 'property name/no. and street name'));
	?>
				<select name='street_address' id='street_address'><option>--postcode--</option></select>
				<span id='address_missing'><br/><a href='#-1'>Address is not on the list</a></span>
				
				</td>
	<?php
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('customer_address_2', array('id' => 'MobileReSaleCustomerAddress2','name' => 'MobileReSale[customer_address_2]','placeholder' => "further address details (optional)"));
				echo "</td>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td>";
					echo $this->Form->input('city',array('id' => 'MobileReSaleCity','name' => 'MobileReSale[city]','label' => 'Town/City','placeholder' => "name of town or city"));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('state',array('id' => 'MobileReSaleState','name' => 'MobileReSale[state]','label'=>'County', 'placeholder' => "name of county (optional)"));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('country',array('id' => 'MobileReSaleCountry','name' => 'MobileReSale[country]','options'=>$countryOptions));
				echo "</td>";
			echo "</tr>";
		echo "</table>";
		//phone details
		echo ('<h4>Mobile Details</h4><hr/>');
		echo "<table>";
			echo "<tr>";
				echo "<td>";
					echo $this->Form->input('brand_id',array('id' => 'MobileReSaleBrandId','name' => 'MobileReSale[brand_id]','options'=>$brands,'value'=>$brand_id,'disabled'=>'disabled'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('mobile_model_id',array('id' => 'MobileReSaleMobileModelId','name' => 'MobileReSale[mobile_model_id]','value'=>$mobile_model_id,'disabled'=>'disabled'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('grade',array('id' => 'MobileReSaleGrade','name' => 'MobileReSale[grade]','options' => $gradeType,'empty'=>'Choose Grade','value' => $grade,'disabled'=>'disabled'));
					echo $this->Form->input('custom_grade',array('id' => 'MobileReSaleCustomGrade','name' => 'MobileReSale[custom_grade]','value' => $custumGrade,'type' => 'hidden'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('type',array('id' => 'MobileReSaleType','name' => 'MobileReSale[type]','options'=>array('1'=>'locked','0'=>'unlocked'),'empty'=>'-Choose-','value'=>$type,'disabled'=>'disabled'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('network_id',array('id' => 'MobileReSaleNetworkId','name' => 'MobileReSale[network_id]','options'=>$networks,'empty'=>'-Choose-','value'=>$network_id,'disabled'=>'disabled'));
				echo "</td>";
			echo "<tr>";
		echo "</table>";
		echo $this->Form->input('retail_customer_id',array('type'=>'hidden','value'=>0,'id'=>'retail_customer_id','name' => 'MobileReSale[retail_customer_id]'));
		echo $this->Form->input('brand_id',array('type'=>'hidden','value'=>$brand_id,'id' => 'MobileReSaleBrandId','name' => 'MobileReSale[brand_id]'));
		echo $this->Form->input('network_id',array('type'=>'hidden','value'=>$network_id,'id' => 'MobileReSaleNetworkId','name' => 'MobileReSale[network_id]'));
		echo $this->Form->input('mobile_model_id',array('type'=>'hidden','value'=>$mobile_model_id,'id' => 'MobileReSaleMobileModelId','name' => 'MobileReSale[mobile_model_id]'));
		echo $this->Form->input('grade',array('type'=>'hidden','value'=>$grade,'id' => 'MobileReSaleGrade','name' => 'MobileReSale[grade]'));
		echo $this->Form->input('type',array('type'=>'hidden','value'=>$type,'id' => 'MobileReSaleType','name' => 'MobileReSale[type]'));
		echo $this->Form->input('color',array('type'=>'hidden','value'=>$color,'id' => 'MobileReSaleColor','name' => 'MobileReSale[color]'));
		
		$fieldIMEI = $this->Form->input('imei',array('id' => 'MobileReSaleImei','name' => 'MobileReSale[imei]','label' => 'IMEI', 'maxlength'=>14,'value'=>$imei2,'readonly'=>'readonly','style'=>"width: 115px;"));
		$fieldIMEI1 = $this->Form->input('imei1',array('name' => 'MobileReSale[imei1]','type' => 'text','label' => false, 'id' =>'imei1','readonly'=>'readonly','style'=>"width: 30px;margin-top: 18px", 'value' => $imei1));
		$fieldColor = $this->Form->input('color',array('id' => 'MobileReSaleColor','name' => 'MobileReSale[color]','options'=>$colorOptions,'value'=>$color,'disabled'=>'disabled'));
		$fieldSP = $this->Form->input('selling_price',array(
															'label' => 'Recommended Sale Price',
															'type' => 'text',
															'value'=> $mobileSalePrice,
															'style' => 'width: 70px;',
															'div' => false,
															'id' => 'MobileReSaleSellingPrice',
															'name' => 'MobileReSale[selling_price]',
															'readonly' => 'readonly',
															));
		$doubleHash = "";
		if($customGradeMob){
			$doubleHash = "<a href='#-1' title='Lowest Selling Price: $lowestSP', alt ='Lowest Selling Price: $lowestSP' id='lowest_selling_price' style='padding-left:15px;'>##</a>";
			echo $gradePriceJS = <<<GRADE_JS
			<script>
			\$(document).ready(function(){
				\$(function() {\$( '#lowest_selling_price' ).tooltip();});
			});
			</script>
GRADE_JS;
		}
		$fieldHiddenCP = $this->Form->input('cost_price',array('id' =>'MobileReSaleCostPrice','name' => 'MobileReSale[cost_price]','type'=>'hidden','label' => 'Cost Price', 'value' => $mobileCostPrice,'readonly'=>'readonly'));
		$discountedFields = "";
		$discountHash = "";
		$lowestAllowedSP = $mobileSalePrice;
		$allowedDiscount = array();
		if($maximum_discount > 0){
			$maxDiscntPercen = 0;
			foreach($discountOptions as $value => $percentage){
				if($value > $maximum_discount)break;
				$allowedDiscount[$value] = $percentage;
				$maxDiscntPercen = $value;
			}
			$lowestAllowedSP = $mobileSalePrice - (($maxDiscntPercen/100) * $mobileSalePrice);
			$lowestAllowedSP = round($lowestAllowedSP,2);
			$discountHash = "<a href='#-1' title='Lowest Selling Price: $lowestAllowedSP', alt ='Lowest Selling Price: $lowestAllowedSP' id='lowest_SP'>##</a>";
			
			$fieldAllowedDiscnt = $this->Form->input('discount',array(
																		'id' => 'MobileReSaleDiscount',
																		'name' => 'MobileReSale[discount]',
																	  'options' => $allowedDiscount,
																	  'style' => 'display:none;'
																	  ));
			
			$fieldDiscntPrice = $this->Form->input('discounted_price',array(
																			'id' => 'MobileReSaleDiscountedPrice',
																			'name' =>'MobileReSale[discounted_price]', 
																			'type'=>'text',
																			//'readonly'=>'readonly',
																			'style' => 'width:80px;',
																			'onblur'=>'updateprice();',
																			'label' => 'Selling Price',
																			));
			
			$discountedFields = "<td>{$fieldAllowedDiscnt}{$discountHash}</td><td>$fieldDiscntPrice<td>";
		}else{
			$lowestAllowedSP = round($mobileSalePrice,2);
			$discountHash = "<a href='#-1' title='Lowest Selling Price: $lowestAllowedSP', alt ='Lowest Selling Price: $lowestAllowedSP' id='lowest_SP'>##</a>";
			$fieldAllowedDiscnt = $this->Form->input('discount',array(
																		'id' => 'MobileReSaleDiscount',
																		'name' => 'MobileReSale[discount]',
																	  'options' => $allowedDiscount,
																	  'style' => 'display:none;'
																	  ));
			$fieldDiscntPrice = $this->Form->input('discounted_price',array(
																			'type'=>'text',
																			'id' => 'MobileReSaleDiscountedPrice',
																			'name' =>'MobileReSale[discounted_price]', 
																			//'readonly'=>'readonly',
																			'style' => 'width:80px;',
																			'onblur'=>'updateprice();',
																			'label' => 'Selling Price',
																			'value' => $mobileSalePrice,
																			));
			$discountedFields = "<td>{$fieldAllowedDiscnt}{$discountHash}</td><td>$fieldDiscntPrice<td>";
		}
		echo "
			<table>
				<tr>
					<td style='width: 134px'>$fieldIMEI</td>
					<td>$fieldIMEI1</td>
					<td>$fieldColor</td>
					<td>{$fieldSP}{$doubleHash}{$fieldHiddenCP}</td>
					$discountedFields
				<tr>
			</table>";
		#echo $this->Form->input('selling_date',array('label' => 'Selling Date')); discountOptions
		echo $this->Form->input('description',array('id' => 'MobileReSaleDescription','name' => 'MobileReSale[description]'));
		
		echo ('<h4>Miscellaneous</h4><hr/>');
		echo $this->Form->input('brief_history', array('id' => 'MobileReSaleBriefHistory','name' => 'MobileReSale[brief_history]','label' => 'Resale History(For Internal Use'));		
		#echo $this->Form->input('status',array('options' => $resaleOptions));
		echo "<input type='hidden' name='lowest_allowed_sp' id = 'lowest_allowed_sp' value = '$lowestAllowedSP' />";
		echo "<input type='hidden' name='lowest_allowed_topup_sp' id = 'lowest_allowed_topup_sp' value = '$lowestSPIfTopup' />";
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
		$('#MobileReSaleCustomerAddress1').show("");
		$('#MobileReSaleCustomerAddress1').val("");
		$('#MobileReSaleCustomerAddress2').val("");
		$('#MobileReSaleCity').val("");
		$('#MobileReSaleState').val("");		
		$(this).hide();
	});
	$( "#street_address" ).select(function() {
		alert($( "#street_address" ).val());
		$('#MobileReSaleCustomerAddress1').val($( "#street_address" ).val());
		$('#MobileReSaleCustomerAddress1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$( "#street_address" ).change(function() {
		$('#MobileReSaleCustomerAddress1').val($( "#street_address" ).val());
		$('#MobileReSaleCustomerAddress1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$("#find_address").click(function() {
		var zipCode = $("#MobileReSaleZip").val();
		//$.blockUI({ message: 'Just a moment...' });
		//focus++;
		//alert("focusout:"+zipCode);
		//$( "#focus-count" ).text( "focusout fired: " + focus + "x" );	
		var zipCode = $("#MobileReSaleZip").val();
		
		if (zipCode == "") {
            alert("Please Input Postcode");
			return false;
        }
		var targeturl = $("#MobileReSaleZip").attr('rel') + '?zip=' + escape(zipCode);		
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
						$('#MobileReSaleCustomerAddress1').hide("");
						$('#address_missing').show();
						var toAppend = '';
						$('#street_address').find('option').remove().end();
						$.each(obj.Street, function( index, value ) {
							//alert( index + ": " + value );
							toAppend += '<option value="'+value+'">'+value+'</option>';
						});
						$('#street_address').append(toAppend);
						$('#MobileReSaleCustomerAddress2').val(obj.Address2);
						$('#MobileReSaleCity').val(obj.Town);
						$('#MobileReSaleState').val(obj.County);
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
	$('#MobileReSaleCustomerAddress1').show();
	$('#street_address').hide();
	$('#address_missing').hide();
	$("#MobileReSaleCustomerContact").keydown(function (event) {  
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
	
	$("#MobileReSaleSellingPrice").keydown(function (event) {		
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||
		event.keyCode == 46 || event.keyCode == 190 || event.keyCode == 183) {
			//190 for dots
			;
		}  else {
			event.preventDefault();
		}
	});
	
	$('#MobileReSaleImei').keyup(function(event){
        if ($('#MobileReSaleImei').val().length == 14 && ((event.keyCode >= 48 && event.keyCode <= 57 ) ||
		( event.keyCode >= 96 && event.keyCode <= 105))) {
            var i;
            var singleNum;
            var finalStr = 0;
            var total = 0;
            var numArr = $('#MobileReSaleImei').val().split('');
            
            for (i = 0; i < $('#MobileReSaleImei').val().length; i++) {
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
            var newNumb = $('#MobileReSaleImei').val() + Dnum;
             $('#imei1').val(Dnum);
        }
    });
});
function validateForm(){
//$('#MobileReSaleAddForm').submit(function(event){
	var lowestAllowedSP = parseFloat($('#lowest_allowed_sp').val());
	var discountedPrice = parseFloat($('#MobileReSaleDiscountedPrice').val());
	if(discountedPrice < lowestAllowedSP){
	  alert("Please enter sale price higher than lowest allowed selling price" + lowestAllowedSP);
	  $('#error_div').html("Please enter sale price higher than lowest allowed selling price" + lowestAllowedSP).css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
	  $('#MobileReSaleDiscountedPrice').val('');
	  //$("#MobileReSaleDiscountedPrice" ).focus();
	  return false;
	}
	if ($('#MobileReSaleCustomerFname').val() == '') {
		$('#error_div').html("Please Enter First Name").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert("Please Enter First Name");
		return false;
	} 
	if ($('#MobileReSaleCustomerEmail').val() == '') {
          // $('#error_div').html("Please input the customer's email").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
          //alert("Please input the customer's email");
          //return false;
      }else if (!isValidEmailAddress($('#MobileReSaleCustomerEmail').val())) {
           $('#error_div').html("Please input valid email address!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
          alert('Please input valid email address!');
          return false;
      }
	 
    if ($('#MobileReSaleCustomerContact').val() == '') {
		$('#error_div').html("Please input the phone number").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert("Please input the phone number");
		return false;
	}else if ($('#MobileReSaleCustomerContact').val().length < 11) {
		$('#error_div').html("Phone number should be minimum 11 characters long!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert('Phone number should be minimum 11 characters long!');
		return false;
	}
	if ($('#MobileReSaleDescription').val() == "") {
		$('#error_div').html('Please input the fault description!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert("Please input the fault description!");
		return false;
	}
}

</script>
<script>
	$('#MobileReSaleDiscount').change(function(){
		var discount = $("#MobileReSaleDiscount").val();
		var sellingPrice = $("#MobileReSaleSellingPrice").val();
		var costPrice = $("#MobileReSaleCostPrice").val();
		var result = sellingPrice - discount * sellingPrice / 100;
		if(result<costPrice){
			alert("Discounted price cannot be lesser than the cost price");
			return false;
		} else {
			$('#MobileReSaleDiscountedPrice').val(result);
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
						$('#MobileReSaleCustomerFname').val('');
						$('#MobileReSaleCustomerLname').val('');
						$('#MobileReSaleCustomerEmail').val($('#search_customer').val());
						$('#MobileReSaleCustomerContact').val('');
						$('#MobileReSaleZip').val('');
						$('#MobileReSaleCustomerAddress1').val('');
						$('#MobileReSaleCustomerAddress2').val('');
						$('#MobileReSaleCity').val('');
						$('#MobileReSaleState').val('');
						$('#MobileReSaleCountry').val('');
					} else {
						$('#MobileReSaleCustomerFname').val(obj.fname);
						$('#MobileReSaleCustomerLname').val(obj.lname);
						$('#MobileReSaleCustomerEmail').val(obj.email);
						$('#MobileReSaleCustomerContact').val(obj.mobile);
						$('#MobileReSaleZip').val(obj.zip);
						$('#MobileReSaleCustomerAddress1').val(obj.address_1);
						$('#MobileReSaleCustomerAddress2').val(obj.address_2);
						$('#MobileReSaleCity').val(obj.city);
						$('#MobileReSaleState').val(obj.state);
						if (obj.country != "") {
                            $('#MobileReSaleCountry').val(obj.country);
                        }
						
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
	 $('#MobileReSaleSellingPrice').blur(function(){
		var price = $("#MobileReSaleSellingPrice").val();
		var minprice = '<?php echo $lowestSP;?>';
		if (price < minprice) {
			$('#MobileReSaleSellingPrice').val('');
            alert("Selling amount cannot be less then minimum selling price");
        }
	 });

	$(document).ready(function(){
		$(function() {
			$( '#lowest_SP' ).tooltip();
		  });
	});
	$("#MobileReSaleDiscountedPrice").keydown(function (event) {		
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||
		event.keyCode == 46 || event.keyCode == 190 || event.keyCode == 183 || event.keyCode == 110) {
			;
			//48-57 => 0..9
			//8 => Backspace; 9 => tab 
		} else {
			event.preventDefault();
		}
	});
	function updateprice(){
	  var lowestAllowedSP = parseFloat($('#lowest_allowed_sp').val());
	  var lowestAllowedSPtopup = parseFloat($('#lowest_allowed_topup_sp').val());
	  if (lowestAllowedSPtopup > lowestAllowedSP) {
        var compare_type = lowestAllowedSPtopup;
      }else{
		var compare_type =lowestAllowedSP;
	  }
	  
	  var discountedPrice = parseFloat($('#MobileReSaleDiscountedPrice').val());
	  var orignal_price = parseFloat($('#MobileReSaleSellingPrice').val());
	  if(discountedPrice < compare_type){
		alert("Please enter price higher than " + lowestAllowedSP);
		$('#MobileReSaleDiscountedPrice').val(orignal_price);
		//$("#MobileReSaleDiscountedPrice" ).focus();
	  }
	}
</script>