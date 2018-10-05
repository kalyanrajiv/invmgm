
<div class="settings form">
<?php
//pr($this->request);
$attribute_value = $this->request->data['attribute_value'];
$attribute_name = $this->request->data['attribute_name'];
$comment = $this->request->data['comment'];
?>
<?php if($attribute_name=="logo_image"){?>
<?php echo $this->Form->create('Setting',array('enctype'=>'multipart/form-data')); ?>
<?php }else{?>
<?php echo $this->Form->create('Setting',array('inputDefaults' => array( 'label' => false,'div' => true))); ?>
<?php } ?>

	<fieldset>
		<legend><?php echo __('Edit Setting'); ?></legend>
	<?php
		
		echo $this->Form->input('id',array('type' => 'hidden'));
		?>
		
		<div class="input text"><span style="margin-right: 77px;font-size: 15px;"><b>Attribute Name:</b></span><span style="font-size: 16px;"><?php echo $attribute_name; ?></span></div>
		
		<?php
		echo $this->Form->input('attribute_name',array('type' => 'hidden'));
		if(
			$attribute_name=='terms_repair' ||
			$attribute_name=='terms_unlock' ||
			$attribute_name=='terms_resale' ||
			$attribute_name=='unlock_email_message' ||
			$attribute_name=='phone_resale_email_message' ||
			$attribute_name=='repair_email_message' ||
			$attribute_name=='invoice_terms_conditions' ||
			$attribute_name=='grades_description' ||
			$attribute_name=='mobile_purchase_terms' ||
			$attribute_name=='terms_bulk_resale' ||
			$attribute_name=='receipt_terms_conditions'
		){
			
			echo $this->Ck->input('attribute_value',array('value' => $attribute_value));
			
		}elseif($attribute_name=="phone_condition_notification" || $attribute_name == "function_test_notification"){
			echo $this->Form->input('attribute_value', array('type' => 'checkbox',  'label' => 'checkbox' ,  'value' =>'active'));
		}elseif($attribute_name=="product_request_users"){
			$attArray = array();
			if(!empty($this->request->data['attribute_value'])){
				$attArray = explode('|',$this->request->data['attribute_value']);
			}
			?>
			<select name = "data[Message][sent_to_id][]"  multiple="multiple" size='6' style='width: 200px;'>
				<?php
				   foreach($userEmails as $m => $user)
				   {
					   $checked = '';
					   if(in_array($m,$attArray)){
						   $checked = 'selected';
					   }
					   echo "<option value =".$m." ".$checked.">".$user."</option>";
				   }
			   ?>
			</select>
		<?php }else{
			echo $this->Form->input('attribute_value',array('value'=>$attribute_value));
		}
		echo $this->Form->input('comment',array('value'=>$comment));
		
		echo $this->Form->input('status',array('type' => 'hidden'));
		if($attribute_name=="logo_image"){
			echo $this->Form->input('upload', array('type'=>'file'));
		}
		if($attribute_name=="product_request_email"){?>
			<span><i>**Please use comma(,) as separator between emails<br/>**No blank spaces are allowed</i></span>
		<?php }
	?>
	</fieldset>
<?php
echo $this->Form->submit("submit",array('name'=>'submit'));
echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php #echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Setting.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('Setting.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Settings'), array('action' => 'index')); ?></li>
	</ul>
</div>
