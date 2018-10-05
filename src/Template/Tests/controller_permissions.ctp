<?php
/**
  * @var \App\View\AppView $this
  */
?>

<div class="groups index large-9 medium-8 columns content">
    <h3><?= __('Change Permission') ?></h3>
	<form action="/admin/AclManager/permissions" method="get">
    <table cellpadding="0" cellspacing="0">
        <tbody>
			<tr>
			<td>
			<input type="radio" name="model1" value="Users">BY User</td>
			<td>
			<input type="radio" name="model1" value="Groups" checked="checked">BY Group
			</td>
			</tr>
			<tr>
            <td  style="width: 10px;">
            <select name="controller">
            <?php
                foreach($allControllers as $Controller=> $allController){
					?>
                    <option value="<?= $Controller?>"><?= $allController ?></option>
					<?php
                }
            ?>
            </select>
			</td >
			<td id = "user_dropdown" style="width: 147px;">
			<select name="user_id[]" multiple>
            <?php
                foreach($users as $user_id=> $user_name){
					?>
                    <option value="<?= $user_id?>"><?= $user_name."(".$users_email[$user_id].")" ?></option>
					<?php
                }
            ?>
            </select>
			</td>
			<input type="hidden" name="show_me" value="1" />
            <td><input type="submit" name="submit" value="Submit" /></td>
            </tr>
        </tbody>
    </table>
    	
  </form>
</div>
<div class="actions">
 <h3><?php echo __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('New Group'), array('action' => 'add')); ?></li>
        <li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
    </ul>
</div>
<script>
$('input:radio').change(function() {
       var radio_val =  $(this).attr("value");
	   if (radio_val == "Users") {
        $("#user_dropdown").show();
       }else{
		$("#user_dropdown").hide();
	   }
	   
    });
$(document).ready(function() {
    $("#user_dropdown").hide();
});
</script>