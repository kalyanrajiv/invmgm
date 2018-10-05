<tr>
			  <td colspan=2>
				<?php
				$kiosk_show_info = array();
				$kiosk_id = $kiosk_id;
				foreach($kiosk_info as $key => $info){
				  if($info->id == $kiosk_id){
					$kiosk_show_info['address1'] = $info->address_1;
					$kiosk_show_info['address_2'] = $info->address_2;
					$kiosk_show_info['city'] = $info->city;
					$kiosk_show_info['state'] = $info->state;
					$kiosk_show_info['zip'] = $info->zip;
					$kiosk_show_info['email'] = $info->email;
					$kiosk_show_info['contact'] = $info->contact;
				  }
				}
               $address2 =  $address = "";
				if(!empty($kiosk_show_info)){ ?>
				  <table>
				 <?php $address .= "<tr><td><span style='font-size: 12px;'>";
                    if(!empty($kiosk_show_info['address1'])){
                        $address .= $kiosk_show_info['address1'];    
                    }
                    if(!empty($kiosk_show_info['address_2'])){
                        $address .= ", ".$kiosk_show_info['address_2'];    
                    }
                    if(!empty($kiosk_show_info['city'])){
                        $address .= ", ".$kiosk_show_info['city'];    
                    }
                    if(!empty($kiosk_show_info['zip'])){
                        $address .= ", ".$kiosk_show_info['zip'];    
                    }
               $address .= "</span></td></tr>";
               echo $address;
               ?>
					<?php $address2 = "<tr><td><span style='font-size: 12px;'>Tel :  ";
                    if(!empty($kiosk_show_info['contact'])){
                        $address2 .= $kiosk_show_info['contact'];
                    }
                    if(!empty($kiosk_show_info['email'])){
                        $address2 .= ", ".$kiosk_show_info['email'];
                    }
                    
                    if(!empty($kiosk_show_info['website'])){
                        $address2 .= " ".$kiosk_show_info['website'];
                    }
                    
                    ?>
					<?php $address2 = "<tr><td><span style='font-size: 12px;'>Tel :  ";
                    if(!empty($kiosk_show_info['contact'])){
                        $address2 .= $kiosk_show_info['contact'];
                    }
                    if(!empty($kiosk_show_info['email'])){
                        $address2 .= ", ".$kiosk_show_info['email'];
                    }
                    
                    if(!empty($settingArr['website'])){
                        $address2 .= " ".$settingArr['website'];
                    }
                    $address2 .= "</span></td></tr>";
                    echo $address2;
                    ?>
					
				  </table>
				<?php }
				
				?>
			  </td>
			 </tr>