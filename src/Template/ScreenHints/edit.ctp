
<div class="settings form">
<?php
	$hintvalue = $result['hint'];
	$hintValue = $this->request->data['hint'];
?>

	<fieldset>
		<legend><?php echo __('Edit Screen Hints'); ?></legend>
	<?php
		echo $this->Form->create('ScreenHint');
		echo $this->Form->input('controller',array('disabled' => true,'options' => $controllersDropDown,'empty' => '(choose one)','value' => $result["controller"]));
		echo $this->Form->input('id',array('value' => $result["id"],'type' => 'hidden'));
		echo $this->Form->input('action',array('readonly' => true,'value' => $result["action"]));
        
		echo $this->Ck->input('hint', array('value' => $hintValue));
		echo $this->Form->input('description',array('label' => 'Screen URL','value' => $result["description"]));
	?>
	</fieldset>
<?php echo $this->Form->submit(__('Submit'),array('name'=>'submit')); ?>
<?php echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php //echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('ScreenHint.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('ScreenHint.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Screen Hint'), array('action' => 'index')); ?></li>
	</ul>
</div>
<?php $textHTML = str_replace("\n","<br>",nl2br($hintvalue));?>
<script>
      // CKEDITOR.replace( 'hint');
</script>