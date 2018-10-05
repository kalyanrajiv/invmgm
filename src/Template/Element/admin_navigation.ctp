<?php //$siteUrl = Configure::read('SITE_BASE_URL');
$siteUrl = '';
   $image = "new_blinking.gif";
   //return;
?>
<?php
	$notificationsMenu = "";
	  if($this->request->session()->read('Auth.User.id')){
       
        //if(AuthComponent::user('id')){
       $count_unlock_Prices = $count_unlock_Prices + $count_unlock_status_change;
	//echo $this->request->session()->read('Auth.User.group_id');die;
	if($this->request->session()->read('Auth.User.group_id') == UNLOCK_TECHNICIANS){
	    $comments = $this->Html->link(__('Kiosk Comments('.$comments4UnlockCentCount.')'), array('plugin' => null,'controller' => 'home', 'action' => 'comments_4_unlock_center'));
	    $totalNotifications = $count_Prices+$count_unlock_Prices+$count_new_Orders+$newEmailCount+$count_prdct_pr_change+$comments4UnlockCentCount;
	    //$total ignoring products, as they get updated frequently, and will keep blinking
	    if($totalNotifications>0){
		$totalNotifications = $this->Html->image($image,array('alt'=>'new notifications','title'=>'new notifications'));
	    }else{
		$totalNotifications = "(".$totalNotifications.")";
	    }
	}elseif($this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
	    $comments = $this->Html->link(__('Kiosk Comments('.$comments4ServCentCount.')'), array('plugin' => null,'controller' => 'home', 'action' => 'comments_4_service_center'));
	    $totalNotifications = $count_Prices+$count_unlock_Prices+$count_new_Orders+$newEmailCount+$count_prdct_pr_change+$comments4ServCentCount;
	    //$total ignoring products, as they get updated frequently, and will keep blinking
	    if($totalNotifications>0){
		$totalNotifications = $this->Html->image($image,array('alt'=>'new notifications','title'=>'new notifications'));
	    }else{
		$totalNotifications = "(".$totalNotifications.")";
	    }
	}else{
	    $repair_comments = $this->Html->link(__('Repair Comments('.$commentsRepair4KioskCount.')'), array('plugin' => null,'controller' => 'home', 'action' => 'repair_comments_4_kiosk'));
	    $unlock_comments = $this->Html->link(__('Unlock Comments('.$commentsUnlock4KioskCount.')'), array('plugin' => null,'controller' => 'home', 'action' => 'unlock_comments_4_kiosk'));
	    
	    $comments = "<li>".$repair_comments."</li><li>".$unlock_comments."</li>";
				
	    $totalNotifications = $count_Prices+$count_unlock_Prices+$count_new_Orders+$newEmailCount+$count_prdct_pr_change+$commentsRepair4KioskCount+$commentsUnlock4KioskCount+$count_offer_notice;
	    //$total ignoring products, as they get updated frequently, and will keep blinking
	    if($totalNotifications>0){
		$totalNotifications = $this->Html->image($image,array('alt'=>'new notifications','title'=>'new notifications'));
	    }else{
		$totalNotifications = "(".$totalNotifications.")";
	    }
	}
	
		$notificationsMenu = "<li class='has-sub'><a href='/home/mobile-rpr-price-ntfctn' id='notification'><span>Notifications".$totalNotifications."</span></a>
			<ul>
			   <li>".$this->Html->link(__('Mobile Repair Prices('.$count_Prices.')'), array('plugin' => null,'controller' => 'home', 'action' => 'mobile_rpr_price_ntfctn'))."</li>
				<li>".$this->Html->link(__('Mobile Unlock Prices('.$count_unlock_Prices.')'), array('plugin' => null,'controller' => 'home', 'action' => 'mobile_unlock_price_notification'))."</li>
				<li>".$this->Html->link(__('Upcoming Products('.$total.')'), array('plugin' => null,'controller' => 'home', 'action' => 'new_products_notification'))."</li>
				<li>".$this->Html->link(__('Products Price Change('.$count_prdct_pr_change.')'), array('plugin' => null,'controller' => 'home', 'action' => 'products_price_change_notification'))."</li>
				<li>".$this->Html->link(__('Special Offer('.$count_offer_notice.')'), array('plugin' => null,'controller' => 'home', 'action' => 'special_offer_notification'))."</li>
				<li>".$comments."</li>
				<li class='last'>".$this->Html->link(__('New orders('.$count_new_Orders.')'), array('plugin' => null,'controller' => 'home', 'action' => 'new_orders_notification'))."</li>
				<li class='last'>".$this->Html->link(__('Mail ('.$newEmailCount.')'), array('plugin' => null,'controller' => 'messages', 'action' => 'inbox'))."</li>
				<li>".$this->Html->link(__('New Arrivals ('.$new_arrival_count.')'), array('plugin' => null,'controller' => 'home', 'action' => 'freshArrival'))."</li>
				<li>".$this->Html->link(__('Back In Stock ('.$back_stock_count.')'), array('plugin' => null,'controller' => 'home', 'action' => 'backstock'))."</li>
			</ul>
		</li>";
	$kioskNotifications = $count_Prices+$count_unlock_Prices+$count_new_Orders+$newEmailCount+$count_prdct_pr_change+$commentsRepair4KioskCount+$commentsUnlock4KioskCount+$count_offer_notice;
	
	if($kioskNotifications>0){
	    $kioskNotifications = $this->Html->image($image,array('alt'=>'new notifications','title'=>'new notifications'));
	}else{
	    $kioskNotifications = "(".$kioskNotifications.")";
	}
	
		$kioskNotificationsMenu = "<li class='has-sub'><a href='/home/mobile-rpr-price-ntfctn' id='notification'><span>Notifications".$kioskNotifications."</span></a>
			<ul>
			   <li>".$this->Html->link(__('Mobile Repair Prices('.$count_Prices.')'), array('plugin' => null,'controller' => 'home', 'action' => 'mobile_rpr_price_ntfctn'))."</li>
				<li>".$this->Html->link(__('Mobile Unlock Prices('.$count_unlock_Prices.')'), array('plugin' => null,'controller' => 'home', 'action' => 'mobile_unlock_price_notification'))."</li>
				<li>".$this->Html->link(__('Upcoming Products('.$total.')'), array('plugin' => null,'controller' => 'home', 'action' => 'new_products_notification'))."</li>
				<li>".$this->Html->link(__('Products Price Change('.$count_prdct_pr_change.')'), array('plugin' => null,'controller' => 'home', 'action' => 'products_price_change_notification'))."</li>
				<li>".$this->Html->link(__('Special Offer('.$count_offer_notice.')'), array('plugin' => null,'controller' => 'home', 'action' => 'special_offer_notification'))."</li>
				<li>".$comments."</li>
				<li class='last'>".$this->Html->link(__('New orders('.$count_new_Orders.')'), array('plugin' => null,'controller' => 'home', 'action' => 'new_orders_notification'))."</li>
				<li>".$this->Html->link(__('Mail ('.$newEmailCount.')'), array('plugin' => null,'controller' => 'messages', 'action' => 'inbox'))."</li>
				<li>".$this->Html->link(__('New Arrivals ('.$new_arrival_count.')'), array('plugin' => null,'controller' => 'home', 'action' => 'freshArrival'))."</li>
				<li>".$this->Html->link(__('Back In Stock ('.$back_stock_count.')'), array('plugin' => null,'controller' => 'home', 'action' => 'backstock'))."</li>
			</ul>
		</li>";
    }
	//<li class='last'>".$this->Html->link(__('New orders('.$count_new_Orders.')'), array('plugin' => null,'controller' => 'home', 'action' => 'new_orders_notification'))."</li>
  	//Reference: http://cssmenumaker.com/menu/textured-responsive-drop-down-menu
    $kioskTitle = "";
    $kiosk_title = $this->request->session()->read('kiosk_title');
   // $kiosk_title = $this->Session->read("kiosk_title");
    if(!empty($kiosk_title)){
		$kioskTitle = "<tr style='background-color:yellow;'><td colspan='12' align='center' style='text-align:center;font-size:32px;font-weight:bold;color:blue;padding-top:20px;padding-bottom:20px;'>$kiosk_title</td></tr>";
    }
?>
<table>
        
<?php
//echo $this->request->session()->read('Auth.User.group_id'); echo "</br>";
//echo $this->request->session()->read('Auth.User.user_type');die;
if ($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
          $this->request->session()->read('Auth.User.user_type') == 'retail'):
?>
	<?php echo $kioskTitle;?>
	<tr>
	    <td colspan='12'>
	    	<?php echo $this->element("navigation_kiosk_retail",array('notificationsMenu'=>$kioskNotificationsMenu)); ?>
	    </td>
	</tr>
<?php elseif ($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&  $this->request->session()->read('Auth.User.user_type') =='wholesale'):?>
    <?php echo $kioskTitle;?>
    <tr>
	    <td colspan='12'>
			<?php echo $this->element("navigation_kiosk_wholesale", array('notificationsMenu'=>$kioskNotificationsMenu)); ?>
	    </td>
    </tr>
<?php elseif ($this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS):?>
    <tr>
	    <td colspan='12'>
	    	<?php echo $this->element("navigation_repair",array('notificationsMenu'=>$notificationsMenu)); ?>
	    </td>
    </tr>
<?php elseif ($this->request->session()->read('Auth.User.group_id') == UNLOCK_TECHNICIANS):?>
	<tr>
		<td colspan='12'>
			<?php echo $this->element("navigation_unlock",array('notificationsMenu'=>$notificationsMenu)); ?>
		</td>
	</tr>
<?php elseif ( $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
			  $this->request->session()->read('Auth.User.group_id') == SALESMAN ||
			  $this->request->session()->read('Auth.User.group_id') == MANAGERS ||
              $this->request->session()->read('Auth.User.group_id') ==  	inventory_manager ||
			   $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER
			  ):?>
	<tr><td colspan='12'>
		<?php echo $this->element("navigation_admin",array('notificationsMenu'=>$notificationsMenu,'totalNotifications'=>$totalNotifications)); ?>
	</td></tr>
<?php endif;?>
</table>