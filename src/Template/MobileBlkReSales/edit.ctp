<div class="mobileReSales form">
<?php
	$url = $this->Html->link(array('controller' => 'customers', 'action' => 'get_address'));
	echo $this->Form->create($mobile_re_sale_entity ,array('onSubmit' => 'return validateForm();',));
?>
	<fieldset>
		<legend><?php echo __('Edit Mobile Re Sale'); ?></legend>
		<div id="error_div" tabindex='1'></div>
	<?php
		echo $this->Form->input('id',array('type' => 'hidden'));
		
		//customer details
		echo ('<h4>Customer Details</h4><hr/>');
		
		echo "<table>";
			echo "<tr>";
				echo "<td>";
					echo $this->Form->input('customer_fname', array('label' => 'First Name','id' => 'MobileBlkReSaleCustomerFname'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('customer_lname', array('label' => 'Last Name'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('customer_email',array('id' =>'MobileBlkReSaleCustomerEmail'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('customer_contact',array('maxLength'=>11,'id'=>'MobileBlkReSaleCustomerContact'));
				echo "</td>";
			echo "</tr>";
		echo "</table>";
		
		echo "<table>";
			echo "<tr>";
				echo "<td>";
					echo "<table>";
						echo "<tr>";
							echo "<td>";
							echo $this->Form->input('zip',array('placeholder' => 'Postcode', 'label'=>false, 'rel' => $url,'size'=>'10px','style'=>'width: 120px;'));
							echo "</td>";
							echo "<td>";
							echo "<button type='button' id='find_address' class='btn' style='margin-top: 6px;margin-left: -8px;width: 130px;height: 29px;'>Find my address</button>";
							echo "</td>";
						echo "</tr>";
					echo "</table>";
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('customer_address_1', array('placeholder' => 'property name/no. and street name'));
	?>
				<select name='street_address' id='street_address'><option>--postcode--</option></select>
				<span id='address_missing'><br/><a href='#-1'>Address is not on the list</a></span>
				
				</td>
	<?php
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('customer_address_2', array('placeholder' => "further address details (optional)"));
				echo "</td>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td>";
					echo $this->Form->input('city',array('label' => 'Town/City','placeholder' => "name of town or city"));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('state',array('label'=>'County', 'placeholder' => "name of county (optional)"));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('country',array('options'=>$countryOptions));
				echo "</td>";
			echo "</tr>";
		echo "</table>";
		
		//phone details
		echo ('<h4>Mobile Details</h4><hr/>');
		echo "<table>";
			echo "<tr>";
				echo "<td>";
					echo $this->Form->input('brand_id',array('disabled'=>'disabled'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('mobile_model_id',array('disabled'=>'disabled'));
				echo "</td>";
				echo "<td>";
                //pr($this->request->data);die;
				if($this->request->data['custom_grade'] == 1){
					echo $this->Form->input('grade',array('disabled'=>'disabled'));
				}else{
					echo $this->Form->input('grade',array('options'=>$gradeType,'empty'=>'Choose','disabled'=>'disabled'));
				}
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('type',array('options'=>array('1'=>'locked','0'=>'unlocked'),'empty'=>'-Choose-','disabled'=>'disabled'));
				echo "</td>";
				echo "<td>";
					echo $this->Form->input('color',array('options'=>$colorOptions,'disabled'=>'disabled'));
				echo "</td>";
			echo "<tr>";
		echo "</table>";
		echo "<table>";
			echo "<tr>";
				echo "<td style='width: 100px'>";
				 $imei = $this->request->data['imei'];
					 $imei1 = substr($imei, -1);
					 $imei2 = substr_replace($imei,'',-1) ;
					  
					echo $this->Form->input('imei',array('label' => 'IMEI', 'maxlength'=>16,'value'=>$imei2,'readonly'=>'readonly','style'=>"width: 130px;"));
					echo "</td>";echo "<td>";
					echo $this->Form->input('imei1',array('type' => 'text','label' => false, 'id' =>'imei1','readonly'=>'readonly','style'=>"width: 30px;margin-top: 15px", 'value' => $imei1));
				 
				//echo $this->Form->input('imei',array('label' => 'IMEI', 'maxlength'=>16,'readonly'=>'readonly'));
				echo "</td>";
				
				echo "<td>";
				if(!empty($staticSP)){
					echo $this->Form->input('static_selling_price',array('label' => 'Recommended SP', 'type' => 'text','value'=> $staticSP, 'style' => 'width: 100px;', 'div' => false,'disabled' => true));
				}
				echo $this->Form->input('cost_price',array('type'=>'hidden','label' => 'Cost Price', 'readonly'=>'readonly'));
				echo "</td>";
				echo "<td>";
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){
					echo $this->Form->input('selling_price',array(
																  'label' => 'Actual Selling Price',
																  'type' => 'text',
																  'div' => false,
																  'style' => 'width:100px;',
																  'id' => 'admin_s_price',
																  ));
					if($this->request->data['custom_grade'] == 1){
						echo "<a href='#-1' title='Lowest Selling Price: $lowestSalePrice', alt ='Lowest Selling Price: $lowestSalePrice' id='lowest_selling_price' style='padding-left:15px;'>##</a>";
					}
				}else{
					//if(array_key_exists('MobileBlkReSale',$this->request->data)){
					//	if(array_key_exists('selling_price',$this->request->data['MobileBlkReSale'])){
					//		echo $this->request->data['MobileBlkReSale']['selling_price'];
					//	}else{
					//		echo 0;
					//	}
					//}else{
					//	echo 0;
					//}
					
					echo $this->Form->input('selling_price',array(
																  'label' => 'Actual Selling Price',
																  'div' => false,
																  'type' => 'text',
																  //'readonly' => 'readonly',
																  'style' => 'width:100px;',
                                                                  'id'=>'MobileBlkReSaleSellingPrice'
																  ));
					if($this->request->data['custom_grade'] == 1){
						echo "<a href='#-1' title='Lowest Selling Price: $lowestSalePrice', alt ='Lowest Selling Price: $lowestSalePrice' id='lowest_selling_price' style='padding-left:15px;'>##</a>";
					}
				}
				if($this->request->data['custom_grade'] == 1){
				echo $gradePriceJS = <<<GRADE_JS
			<script>
			\$(document).ready(function(){
				\$(function() {\$( '#lowest_selling_price' ).tooltip();});
					});
					</script>
GRADE_JS;
				}
				echo "</td>";
               
                
                 
              

				if($this->request->data['custom_grade'] == 1){
                   // echo "hi";die;
					echo $this->Form->input('custom_grade',array('type'=>'hidden','id'=>"MobileBlkReSaleCustomGrade" ,'value' =>$this->request->data['custom_grade']));
					echo $this->Form->input('lowest_selling_price',array('type'=>'hidden', 'id'=>"MobileBlkReSaleLowestSellingPrice",'value' => $lowestSalePrice));
					echo $this->Form->input('discount',array('type'=>'hidden', 'id'=>"MobileBlkReSaleDiscount",'value' => 0));
					echo $this->Form->input('discounted_price',array('type'=>'hidden','id'=>"MobileBlkReSaleDiscountedPrice",'value'=>$this->request->data['selling_price']));
					echo $this->Form->input('hidden_discount',array('value'=>0,'id'=>"MobileBlkReSaleHiddenDiscount",'type' => 'hidden'));
					echo $this->Form->input('hidden_discounted_price',array('type'=>'hidden','id'=>"MobileBlkReSaleHiddenDiscountedPrice",'value'=>$this->request->data['discounted_price']));
				}else{
                    //echo "hieeee";die;
					echo $this->Form->input('custom_grade',array('type'=>'hidden', 'value' =>$this->request->data['custom_grade']));
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
					}else{
						echo $this->Form->input('discount',array('type'=>'hidden', 'value' => 0));
						echo $this->Form->input('discounted_price',array('type'=>'hidden'));
					}
					echo $this->Form->input('hidden_discount',array('value'=>$this->request->data['discount'],'type' => 'hidden'));
					echo $this->Form->input('hidden_discounted_price',array('type'=>'hidden','value'=>$this->request->data['discounted_price']));
				}
				echo $this->Form->input('hidden_selling_price',array('id'=>"MobileBlkReSaleHiddenSellingPrice",'value'=>$this->request->data['selling_price'],'type' => 'hidden'));
			echo "<tr>";
		echo "</table>";
		 
		
		echo ('<h4>Miscellaneous</h4><hr/>');
		echo $this->Form->input('brief_history', array('id'=>"MobileBlkReSaleBriefHistory",'label' => 'Resale History(For Internal Use)'));	
		//echo $this->Form->input('status',array('options' => $resaleOptions));
	?>
	</fieldset>
<?php
echo $this->Form->Submit(__('Submit'),array('name'=>'submit'));
echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		
		<li><?php echo $this->Html->link(__('List Mobile Re Sales'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('View Mobile Sale'), array('controller' => 'mobile_re_sales', 'action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('Buy Mobile Phones'), array('controller' => 'mobile_purchases', 'action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('Mobile Stock/Sell'), array('controller' => 'mobile_purchases', 'action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('Global Mobile Search'), array('controller' => 'mobile_purchases', 'action' => 'global_search')); ?></li>
	</ul>
</div>
<script>
function validateForm(){
	var price = $("#MobileBlkReSaleSellingPrice").val();
	var minprice = '<?php echo $lowestSalePrice;?>';
	<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
	}else{
		?>
	if (price < minprice) {
		alert("Please enter sale price heigher than lowest allowed selling price: " + minprice);
		$('#error_div').html("Please enter sale price heigher than lowest allowed selling price: " + minprice).css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		$('#MobileBlkReSaleSellingPrice').val('');
		//$("#MobileReSaleDiscountedPrice" ).focus();
		return false;
	}
	<?php }?>
	//valdating first name
	if ($('#MobileBlkReSaleCustomerFname').val() == '') {
		$('#error_div').html("Please Enter First Name").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert("Please Enter First Name");
		return false;
	}
	
	//valdating email	
	if ($('#MobileBlkReSaleCustomerEmail').val() == '') {
		$('#error_div').html("Please input the customer's email").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert("Please input the customer's email");
		return false;
	}else if (!isValidEmailAddress($('#MobileBlkReSaleCustomerEmail').val())) {
		$('#error_div').html("Please input valid email address!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert('Please input valid email address!');
		return false;
	}
	
	//valdating mobile
	if ($('#MobileBlkReSaleCustomerContact').val() == '') {
		$('#error_div').html("Please input the phone number").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert("Please input the phone number");
		return false;
	}else if ($('#MobileReSaleCustomerContact').val().length < 11) {
		$('#error_div').html("Phone number should be minimum 11 characters long!").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert('Phone number should be minimum 11 characters long!');
		return false;
	}
	
	//valdating description
	if ($('#MobileBlkReSaleDescription').val() == "") {
		$('#error_div').html('Please input the fault description!').css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert("Please input the fault description!");
		return false;
	}
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
	$("#MobileBlkReSaleCustomerContact, #MobileBlkReSaleSellingPrice, #admin_s_price").keydown(function (event) {  
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
</script>
<script>
	$('#MobileBlkReSaleDiscount').change(function(){
		var discount = $("#MobileBlkReSaleDiscount").val();
		var sellingPrice = $("#MobileBlkReSaleSellingPrice").val();
		var costPrice = $("#MobileBlkReSaleCostPrice").val();
		var result = sellingPrice - discount * sellingPrice / 100;
		if(result < costPrice){
			alert("Discounted price cannot be lesser than the cost price");
			return false;
		} else {
			$('#MobileBlkReSaleDiscountedPrice').val(result);
		}
	})
	
	$('#MobileBlkReSaleSellingPrice').keyup(function(){
		//for updating the discounted price as per the change in selling price
		var sellPrice = $(this).val();
		var dis = $('#MobileBlkReSaleDiscount').val();
		var disctPrice = sellPrice - sellPrice*dis/100;
		$('#MobileBlkReSaleDiscountedPrice').val(disctPrice);
	});
	
	$('#admin_s_price').change(function(){
		var discount = $("#MobileBlkReSaleDiscount").val();
		var sellingPrice = $("#admin_s_price").val();
		var costPrice = $("#MobileBlkReSaleCostPrice").val();
		var result = sellingPrice - discount * sellingPrice / 100;
		$('#MobileBlkReSaleDiscountedPrice').val(result);
	})
	
	$('#admin_s_price').keyup(function(){
		//for updating the discounted price as per the change in selling price
		var sellPrice = $(this).val();
		var dis = $('#MobileBlkReSaleDiscount').val();
		var disctPrice = sellPrice - sellPrice*dis/100;
		$('#MobileBlkReSaleDiscountedPrice').val(disctPrice);
	});
	
	
</script>
<script>
	 $('#MobileBlkReSaleSellingPrice').blur(function(){
		<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS ){
				}else{?>
		var price = $("#MobileBlkReSaleSellingPrice").val();
		var minprice = '<?php echo $lowestSalePrice;?>';
		//alert(price);alert(minprice);
		if (price < minprice) {
			$('#MobileBlkReSaleSellingPrice').val('');
            alert("Selling amount cannot be less then minimum selling price");
        }
		<?php } ?>
	 });
</script>
