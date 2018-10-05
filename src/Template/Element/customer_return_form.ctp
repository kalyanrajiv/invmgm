<?php
 extract($CustomerInfo);
 $url = $this->Url->build(array('controller' => 'customers', 'action' => 'get_address'));
?>  
<div id='cusomer_info'>
    <table cellspacing='2' cellpadding='2' width='100%'>
        <tr><th colspan='7'><h4>Customer Info &raquo;</h4></th></tr>
        <tr>
         <td>First Name</td>
            <td>
                <?php echo $this->Form->input(null,array(
                                        'type' => 'text',
                                        'name' => "data[customer][fname]",
                                        'value' => $fname,
                                        'label' => false,
                                        'style' => 'width:150px;'
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
                                        'style' => 'width:150px;'
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
                                        'style' => 'width:150px;'
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
                                        'style' => 'width:150px;'
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
                               echo $this->Form->input('null',array('placeholder' => 'Postcode', 'label'=>false, 'rel' => $url,'size'=>'10px','id'=>'CustomerZip','name' => "data[customer][zip]",'value' => $zip,));
                               echo "</td>";
                               echo "<td>";
                               echo "<button type='button' id='find_address' class='btn' style='margin-top: 6px;margin-left: -8px;width: 112px;height: 22px;'>Find my address</button>";
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
	
});
</script>