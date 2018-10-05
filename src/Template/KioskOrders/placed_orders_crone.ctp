<?php
use Cake\I18n\Time;
?>
<div class="groups index">
   <strong><?php echo '<span style="font-size: 20px; color: red;">Order Placed By Crone</span> '; ?></strong>
   <form action="/kiosk-orders/placed-orders-crone" method="post">
	<table style="margin-top: 42px;width: 428px;">
       
        <tr>
            <td>
                <?php
                echo  $this->Form->input('kiosk',array('options' => $kiosks, 'label' => false, 'div'=> false));
                ?>
            </td>
            <td>
                <input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "Date"  style = "width:80px;height: 25px;" />
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" value="submit" name="submit" />
            </td>
        </tr>
    </table>
   
   	</form>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<?php  if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
					$this->request->session()->read('Auth.User.group_id') == MANAGERS ||
					$this->request->session()->read('Auth.User.group_id') == inventory_manager){
					echo $this->element('sidebar/order_menus');
				}else{
					echo $this->element('sidebar/kiosk_order_menus');
		}?>
</div>
<script>
	jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
	});
</script>