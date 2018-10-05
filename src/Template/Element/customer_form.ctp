<?php
 extract($CustomerInfo);
 $url = $this->Url->build(array('controller' => 'customers', 'action' => 'get_address'));
?>

<table cellspacing='2' cellpadding='2' style='width:400px'>
    <tr>
        <td colspan='2'>Do you need customer receipt?</td>
        <td colspan='2'>
            <table cellspacing='0' cellpadding='0' width='75%'>
            <tr>
                <td>Yes</td>
                <td><input type='radio' name='receipt_required' value='1' onClick='showhide_info(1);' <?php if($receipt_required == 1){echo "checked = 'checked'";} ?>></td>
                <td>No </td>
                <td><input type='radio' name='receipt_required' value='0' onClick='showhide_info(0);' <?php if($receipt_required == 0){echo "checked = 'checked'";} ?>/></td></tr>
            </table>
        </td></tr>
</table>
<div id='cusomer_info'>
   <table cellspacing='2' cellpadding='2' style='width:600px'>
<tr>
   <td>
         <?php $customerData = $this->Url->build(array('controller' => 'retail_customers', 'action' => 'get_customer_ajax'));
               $clearData = $this->Url->build(array('controller' => 'retail_customers', 'action' => 'clear_customer_ajax'));
         ?>
   </td>
     <td height: 48px; padding: 10px;">
     <?php
     echo "<div id='remote1' style='display: contents;'>";
	echo "<input name='cust_email' class='typeahead1' id='cust_email' placeholder='check existing customer email' style='width:250px;padding-right:10px;'/>";
    echo "</div>";
      //echo "<input name='cust_email' id='cust_email' placeholder='check existing customer email' style='width:250px;padding-right:10px;'/>";
      echo "&nbsp;&nbsp;<a href='#' id='check_existing' rel = '$customerData'>Check Existing</a>";
      echo "&nbsp;&nbsp;|&nbsp;&nbsp;<a href='#' id='clear_customer' rel = '$clearData'>Clear Customer</a>";
     ?>
   </td>	
        </tr>
</table>
    <table cellspacing='2' cellpadding='2' width='100%'>
        <tr><th colspan='7'><h4>Customer Info &raquo;</h4></th></tr>
        
         <td>First Name</td>
            <td>
             <?php echo $this->Form->input(null,array(
                                        'type' => 'hidden',
                                        'name' => "data[customer][custId]",
                                        'value' => $custid,
                                        'label' => false,
                                        //'style' => 'width:150px;',
                                        'id' => 'CustomerId',
                                        )
                                ); ?>
                <?php echo $this->Form->input(null,array(
                                        'type' => 'text',
                                        'name' => "data[customer][fname]",
                                        'value' => $fname,
                                        'label' => false,
                                        'style' => 'width:150px;',
                                        'id' => 'Customerfname',
                                        )
                                ); ?>
            </td>
         <td>Last Name</td>
            <td>
                <?php echo $this->Form->input(null,array(
                                        'type' => 'text',
                                        'name' => "data[customer][lname]",
                                        'value' => $lname,
                                        'label' => false,
                                        'style' => 'width:150px;',
                                        'id' => 'Customerlname',
                                        )
                                ); ?>
            </td>
            <td>Email</td>
            <td>
                <?php echo $this->Form->input(null,array(
                                        'type' => 'text',
                                        'name' => "data[customer][email]",
                                        'value' => $email,
                                        'label' => false,
                                        'style' => 'width:150px;',
                                        'id' => 'Customeremail',
                                        )
                                ); ?>
            </td>
        </tr>
        <tr>
            <td>Mobile</td>
            <td>
                <?php echo $this->Form->input(null,array(
                                        'type' => 'text',
                                        'name' => "data[customer][mobile]",
                                        'value' => $mobile,
                                        'label' => false,
                                        'style' => 'width:150px;',
                                        'id' => 'customer_mobile',
                                        'maxlength' => '11'
                                        )
                                ); ?>
            </td>
            <td>Postal Code</td>
                <?php #echo $this->Form->input(null,array(
                //                        'type' => 'text',
                //                        'name' => "data[customer][zip]",
                //                        'value' => $zip,
                //                        'label' => false,
                //                        'style' => 'width:150px;'
                //                        )
                //                );
               echo "<td>";
                       echo "<table>";
                       echo "<tr>";
                               echo "<td>";
                               echo $this->Form->input('null',array('placeholder' => 'Postcode', 'label'=>false, 'rel' => $url,'size'=>'10px','id'=>'CustomerZip','name' => "data[customer][zip]",'value' => $zip,'style' => 'width:117px;'));
                               echo "</td>";
                               echo "<td>";
                               echo "<button type='button' id='find_address' class='btn' style='margin-top: 6px;margin-left: -8px;width: 130px;height: 29px;'>Find my address</button>";
                               echo "</td>";
                       echo "</tr>";
                       echo "</table>";
               echo "</td>"; 
                ?>
            
            <td>Address 1</td>
            <td>
                <?php echo $this->Form->input(null,array(
                                        'type' => 'text',
                                        'name' => "data[customer][address_1]",
                                        'value' => $address1,
                                        'label' => false,
                                        'style' => 'width:150px;',
                                        'placeholder' => 'property name/no. and street name',
                                        'id'=>'CustomerAddress1'
                                        )
                                ); ?>
                        <select name='street_address' id='street_address'><option>--postcode--</option></select>
			<span id='address_missing'><br/><a href='#-1'>Address is not on the list</a></span>
            </td>        
        </tr>
        
        <tr>
            <td>Address 2</td>
            <td>
                <?php echo $this->Form->input(null,array(
                                        'type' => 'text',
                                        'name' => "data[customer][address_2]",
                                        'value' => $address2,
                                        'label' => false,
                                        'style' => 'width:150px;',
                                        'placeholder' => "further address details (optional)",
                                        'id'=>'CustomerAddress2'
                                        )
                                ); ?>
            </td>
            
            <td>Town/City</td>
            <td>
                <?php echo $this->Form->input(null,array(
                                        'type' => 'text',
                                        'name' => "data[customer][city]",
                                        'value' => $city,
                                        'label' => false,
                                        'style' => 'width:150px;',
                                        'placeholder' => "name of town or city",
                                        'id'=>'CustomerCity'
                                        )
                                ); ?>
            </td>
            <td>County</td>
            <td>
                <?php echo $this->Form->input(null,array(
                                        'type' => 'text',
                                        'name' => "data[customer][state]",
                                        'value' => $state,
                                        'label' => false,
                                        'style' => 'width:150px;',
                                        'placeholder' => "name of county (optional)",
                                        'id'=>'CustomerState'
                                        )
                                ); ?>
            </td>        
        </tr>
    </table>
</div>
<script type='text/javascript'>
    var optVal = 0;
    function showhide_info(optVal){
        if (optVal == 0){
            document.getElementById('cusomer_info').style.display = 'none';
        }else{
            document.getElementById('cusomer_info').style.display = 'block';
        }
    }
</script>
<script type='text/javascript'>    
    <?php
        if($receipt_required){
            echo "showhide_info(1);\n";
        }else{
            echo "showhide_info(0);\n";
        }
    ?>
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

$('#remote1 .typeahead1').typeahead(null, {
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
	$('#address_missing').click(function(){
		$('#street_address').hide();
		$('#CustomerAddress1').show("");
		$('#CustomerAddress1').val("");
		$('#CustomerAddress2').val("");
		$('#CustomerCity').val("");
		$('#CustomerState').val("");		
		$(this).hide();
	});
	$( "#street_address" ).select(function() {
		alert($( "#street_address" ).val());
		$('#CustomerAddress1').val($( "#street_address" ).val());
		$('#CustomerAddress1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$( "#street_address" ).change(function() {
		$('#CustomerAddress1').val($( "#street_address" ).val());
		$('#CustomerAddress1').show();
		$('#address_missing').hide();
		$(this).hide();
	});
	$("#find_address").click(function() {
		var zipCode = $("#CustomerZip").val();
		//$.blockUI({ message: 'Just a moment...' });
		//focus++;
		//alert("focusout:"+zipCode);
		//$( "#focus-count" ).text( "focusout fired: " + focus + "x" );	
		var zipCode = $("#CustomerZip").val();
        if (zipCode == "") {
            alert("Please Input Postcode");
			return false;
        }
		var targeturl = $("#CustomerZip").attr('rel') + '?zip=' + escape(zipCode);		
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
						$('#CustomerAddress1').hide("");
						$('#address_missing').show();
						var toAppend = '';
						$('#street_address').find('option').remove().end();
						$.each(obj.Street, function( index, value ) {
							//alert( index + ": " + value );
							toAppend += '<option value="'+value+'">'+value+'</option>';
						});
						$('#street_address').append(toAppend);
						$('#CustomerAddress2').val(obj.Address2);
						$('#CustomerCity').val(obj.Town);
						$('#CustomerState').val(obj.County);
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
	$('#CustomerAddress1').show();
	$('#street_address').hide();
	$('#address_missing').hide();
	//------------------
    $("#customer_mobile").keydown(function (event) {  
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
    //------------------
});
</script>