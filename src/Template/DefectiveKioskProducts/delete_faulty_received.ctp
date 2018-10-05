<div class="mobileUnlocks index">
	<td><h2><?php echo __('Delete Faulty Received Items'); ?></h2></td>
	<fieldset>
		<legend>Choose date range to delete data</legend>
	<?=$this->Form->create();?>
	<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){
		?>
		<span style="display: flex;margin-left: 324px;margin-top: -16px;margin-bottom: -7px;">
			<input type="radio" name="date_type" style="margin-left: -313px;" value="date_of_movement" />Date of Movement&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="date_type" value="created_date" checked/>Created Date
		</span>
		<table>
		<td style="width: 10px;">
		<?php
		if( $this->request->session()->read('Auth.User.group_id')!= MANAGERS){
			echo $this->Form->input('selectKiosk', array('options' => $kiosks, 'id' => 'selectKiosk','div' => false, 'empty' => 'All'));
		}else{
			echo $this->Form->input('selectKiosk', array('options' => $kiosks, 'id' => 'selectKiosk','div' => false,'empty' => 'All'));
		}
		?>
	<?php } ?></td>
		<td style="width: 10px;"><input type="text" placeholder="from date" name="from_date" style="width: 107px;margin-top: 25px;height: 19px;" id="datepicker1"></td>
		<td style="width: 10px;"><input type="text" placeholder="to date" name="to_date" style="width: 107px;margin-top: 25px;height: 19px;" id="datepicker2"></td>
		<td style="width: 10px;"><input type="submit" name="submit" value="Delete" id="delete_button" style="margin-top: 25px;height: 30px;"></td>
		<td><input type="button" value="Reset" style="border-radius: 4px;padding: 4px;width: 65px;color: currentColor;margin-top: 25px;height: 30px;" onClick='reset_search();'></td>
		</table>
	</fieldset>
	
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<?=$this->element('faulty_slide_menu');?>
</div>
<script>
	jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
	});
	
	function reset_search(){
		jQuery( "#DefectiveKioskProductSelectKiosk" ).val("");
		jQuery( "#reference_id" ).val("");
		jQuery("#datepicker1").val("");
		jQuery("#datepicker2").val("");
        jQuery("#selectKiosk").val("");
	}
</script>

<script>
$(function() {
  $( document ).tooltip({
   content: function () {
    return $(this).prop('title');
   }
  });
 });

	$('#delete_button').click(function(ev){
		msgStr = "Are you sure you want to delete the data between the selected date range";
		if ($('#datepicker1').val() == '' || $('#datepicker2').val() == '') {
            alert('Please select from and to date');
			ev.preventDefault();
        } else if (!confirm(msgStr)) {
            ev.preventDefault();
        }
	});
</script>
