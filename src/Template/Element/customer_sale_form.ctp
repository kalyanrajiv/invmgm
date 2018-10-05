<?php
 extract($CustomerInfo);
?>
<table cellspacing='2' cellpadding='2' style='width:400px'>
    <tr>
        <td colspan='2'>Do you need cutomer receipt?</td>
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
  <table cellspacing='2' cellpadding='2' width='100%'>
      <tr><th colspan='7'><h4>Customer Info &raquo;</h4></th></tr>
      <tr>
          <td>Business</td>
          <td colspan="3">
              <?php echo $this->Form->input(null,array(
                                      'type' => 'text',
                                      'name' => "data[customer][business]",
                                      //'value' => $business,
                                      'label' => false,
                                      'style' => 'width: 417px;'
                                      )
                              ); ?>
          </td>
          <td>Email</td>
          <td>
              <?php echo $this->Form->input(null,array(
                                      'type' => 'text',
                                      'name' => "data[customer][email]",
                                      //'value' => $email,
                                      'label' => false,
                                      'style' => 'width:150px;'
                                      )
                              ); ?>
          </td>
      </tr>
      <tr>
          <td>First Name</td>
          <td>
              <?php echo $this->Form->input(null,array(
                                      'type' => 'text',
                                      'name' => "data[customer][fname]",
                                      //'value' => $fname,
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
                                      //'value' => $lname,
                                      'label' => false,
                                      'style' => 'width:150px;'
                                      )
                              ); ?>
          </td>
          <td>Date of Birth</td>
          <td>
              <?php echo $this->Form->input(null,array(
                                      'type' => 'text',
                                      'name' => "data[customer][date_of_birth]",
                                      //'value' => $zip,
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
                                      //'value' => $mobile,
                                      'label' => false,
                                      'style' => 'width:150px;'
                                      )
                              ); ?>
          </td>
          <td>Landline</td>
          <td>
              <?php echo $this->Form->input(null,array(
                                      'type' => 'text',
                                      'name' => "data[customer][landline]",
                                      //'value' => $landline,
                                      'label' => false,
                                      'style' => 'width:150px;'
                                      )
                              ); ?>
          </td>
          <td>Vat Number</td>
          <td>
              <?php echo $this->Form->input(null,array(
                                      'type' => 'text',
                                      'name' => "data[customer][vat_number]",
                                      //'value' => $vat,
                                      'label' => false,
                                      'style' => 'width:150px;'
                                      )
                              ); ?>
          </td>
      </tr>
      <!--<tr><td>Delivery Address details:</h4></td></tr><h4>-->
      <tr>
          <td>Delivery Address</td>
          <td colspan=3>
              <?php echo $this->Form->input(null,array(
                                      'type' => 'text',
                                      'name' => "data[customer][delivery_address_1]",
                                      //'value' => $deliveryAddress1,
                                      'label' => false,
                                      'style' => 'width:417px;'
                                      )
                              ); ?>
          </td>
          <td colspan="2">
              <?php echo $this->Form->input(null,array(
                                      'type' => 'text',
                                      'name' => "data[customer][delivery_address_1]",
                                      //'value' => $deliveryAddress2,
                                      'label' => false,
                                      'style' => 'width:247px;'
                                      )
                              ); ?>
          </td>
      </tr>
      <tr>
          <td>City</td>
          <td>
              <?php echo $this->Form->input(null,array(
                                      'type' => 'text',
                                      'name' => "data[customer][city]",
                                      //'value' => $mobile,
                                      'label' => false,
                                      'style' => 'width:150px;'
                                      )
                              ); ?>
          </td>
          <td>State</td>
          <td>
              <?php echo $this->Form->input(null,array(
                                      'type' => 'text',
                                      'name' => "data[customer][state]",
                                      //'value' => $landline,
                                      'label' => false,
                                      'style' => 'width:150px;'
                                      )
                              ); ?>
          </td>
          <td>Postal Code</td>
          <td>
              <?php echo $this->Form->input(null,array(
                                      'type' => 'text',
                                      'name' => "data[customer][zip]",
                                      //'value' => $vat,
                                      'label' => false,
                                      'style' => 'width:150px;'
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