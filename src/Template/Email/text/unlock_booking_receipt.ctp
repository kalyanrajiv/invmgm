Hi <?php echo ucfirst($unlockBookingData['customer_fname']);?> <?php echo ucfirst($unlockBookingData['customer_lname']);?>,<br/><br/>
<?php echo $unlockStatusStatement; ?><br/><br/>
Regards,<br/>
<?php echo $kiosks[$unlockBookingData['kiosk_id']];?>
<?php echo $unlock_email_message;?>