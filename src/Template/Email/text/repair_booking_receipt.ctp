<?php $repairDays = array();
			
			if(!empty($repairBookingData['repair_days_a'])){
				$repairDays[] = $repairBookingData['repair_days_a'];
			}
			if(!empty($repairBookingData['repair_days_b'])){
				$repairDays[] = $repairBookingData['repair_days_b'];
			}
			if(!empty($repairBookingData['repair_days_c'])){
				$repairDays[] = $repairBookingData['repair_days_c'];
			}
?>
			
			
                        
Hi <?php echo $repairBookingData['customer_fname'];?> <?php echo $repairBookingData['customer_lname'];?>,<br/><br/>

<?php $repairStatus = $repairBookingData['status'];
switch($repairStatus){
			case "1":?>
The repair has been booked for your <?php echo $mobileModels[$repairBookingData['mobile_model_id']];?> phone &#40;IMEI: <?php echo $repairBookingData['imei'];?>&#41;. Your repair is expected to get done within <?php echo (max($repairDays));?> day&#40;s&#41;.
			<?php break; ?>
			
			<?php case "2": ?>
The repair has been rebooked for your <?php echo $mobileModels[$repairBookingData['mobile_model_id']];?> phone &#40;IMEI: <?php echo $repairBookingData['imei'];?>&#41;. Your repair is expected to get done within <?php echo (max($repairDays));?> days.
			<?php break; ?>
			
			<?php case "6": ?>
Your <?php echo $mobileModels[$repairBookingData['mobile_model_id']];?> phone &#40;IMEI: <?php echo $repairBookingData['imei'];?>&#41; has been succesfully repaired.<br/><br/>
Thank you for using our repair services.
			<?php break; ?>
			
			<?php case "7": ?>
Your <?php echo $mobileModels[$repairBookingData['mobile_model_id']];?> phone &#40;IMEI: <?php echo $repairBookingData['imei'];?>&#41; could not be repaired and has been delivered back to you.<br/><br/>
We regret for the inconvenience.
			<?php break; ?>
			
			<?php case "8": ?>
Your <?php echo $mobileModels[$repairBookingData['mobile_model_id']];?> phone &#40;IMEI: <?php echo $repairBookingData['imei'];?>&#41; has been succesfully repaired.<br/><br/>
Thank you for using our repair services.
			<?php break; ?>
			
			<?php case "9": ?>
Your <?php echo $mobileModels[$repairBookingData['mobile_model_id']];?> phone &#40;IMEI: <?php echo $repairBookingData['imei'];?>&#41; could not be repaired and has been delivered back to you.<br/><br/>
We regret for the inconvenience.
			<?php break; ?>
			
			<?php case "16": ?>
Your <?php echo $mobileModels[$repairBookingData['mobile_model_id']];?> phone &#40;IMEI: <?php echo $repairBookingData['imei'];?>&#41; has been received by the concerned technician. Your repair will start very soon.
			<?php break; ?>
			
			<?php case "18": ?>
Your <?php echo $mobileModels[$repairBookingData['mobile_model_id']];?> phone &#40;IMEI: <?php echo $repairBookingData['imei'];?>&#41; has been successfully repaired and will soon be available at <?php echo $kiosks[$repairBookingData['kiosk_id']];?> for you to pick up.
			<?php break; ?>
			
			<?php case "19": ?>
Your <?php echo $mobileModels[$repairBookingData['mobile_model_id']];?> phone &#40;IMEI: <?php echo $repairBookingData['imei'];?>&#41; could not be repaired. It will soon be available at <?php echo $kiosks[$repairBookingData['kiosk_id']];?> for you to pick up.<br/><br/>
We regret for the inconvenience.
			<?php break; ?>
			
			<?php case "20": ?>
Your <?php echo $mobileModels[$repairBookingData['mobile_model_id']];?> phone &#40;IMEI: <?php echo $repairBookingData['imei'];?>&#41; has been successfully repaired. It will soon be sent to <?php echo $kiosks[$repairBookingData['kiosk_id']];?> for you to pick up.<br/><br/>
			<?php break; ?>
<?php } ?>
<br/><br/>
Regards,<br/>
<?php echo $kiosks[$repairBookingData['kiosk_id']];?>
