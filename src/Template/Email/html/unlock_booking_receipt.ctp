<?php
    $kioskaddress1 = $kioskaddress2 = $kioskstate = $kioskcountry = $kioskzip = $kioskcontact = "";
    if(!empty($kioskaddress['address_1'])){
        $kioskaddress1 = $kioskaddress['address_1'].", ";
    }
    if(!empty($kioskaddress['address_2'])){
        $kioskaddress2 = $kioskaddress['address_2'].", " ;
    }
    if(!empty($kioskaddress['city'])){
        $kioskcity = $kioskaddress['city'].", ";
    }
    if(!empty($kioskaddress['state'])){
       $kioskstate =  $kioskaddress['state'].", ";
    }
    if(!empty($kioskaddress['country'])){
         $kioskcountry = $countryOptions[$kioskaddress['country']].", ";
    }
    if(!empty($kioskaddress['zip'])){
         $kioskzip =  $kioskaddress['zip'] ;
    }
    if(!empty($kioskaddress['contact'])){
         $kioskcontact =  "Contact: ".$kioskaddress['contact'];
    }
?>
Dear <?php echo ucfirst($unlockBookingData['customer_fname']);?> <?php echo ucfirst($unlockBookingData['customer_lname']);?>,<br/><br/>
<?php echo $unlockStatusStatement; ?>
Regards,<br/>
<?php echo $kiosks[$unlockBookingData['kiosk_id']];?>
<?php echo "<br/>". $kioskaddress1;
        echo  "<br/>". $kioskaddress2."\t".$kioskcity;
        echo "<br/>".$kioskstate;
        echo "<br/>".$kioskcountry;
        echo "<br/>". $kioskzip;
        echo "<br/>". $kioskcontact;
    ?><br/><br/>
<?php echo $unlock_email_message; ?>

