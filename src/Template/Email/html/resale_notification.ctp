<?php
    $kioskaddress1 = $kioskaddress2 = $kioskstate = $kioskcity = $kioskcountry = $kioskzip = "";
    //pr($kioskaddress);
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
 
?>
Dear <?php echo $mobileresaledata['customer_fname'];?> <?php echo $mobileresaledata['customer_lname'].",";
				echo "<br/><br/>";
				echo "We are pleased with your recent purchase of mobile\t";
				echo $resaleDetails;
				echo "<br/>";
				echo "<br/>";
				echo "Thank you for using our services.";
				echo "<br/>";
				echo "<br/>";
				echo "Regards,";
				echo "<br/>";
				if($kioskaddress){
				   echo $kioskaddress['name'].","; 
				    echo "<br/>". $kioskaddress1;
				    echo  "<br/>". $kioskaddress2."\t".$kioskcity;
				    echo "<br/>".$kioskstate;
				    echo "<br/>".$kioskcountry;
				    echo "<br/>". $kioskzip; 
				}else{
				    echo "www.bolowaheguru.co.uk";
				}
				echo "<br/>";
				echo "<br/>";
				echo $phone_resale_email_message;
?>