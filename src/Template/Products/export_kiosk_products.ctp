 
<div class="products index">
	 <div id="error_div" tabindex='1'></div>
	 <?php
	  $screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
      $updateUrl = "/img/16_edit_page.png";
	 ?>
	<?=$this->Form->create('Product',array('enctype'=>'multipart/form-data','onSubmit' => 'return validateForm();'));?>
	<h2><?php echo __('Export Kiosk Products')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>  
	</h2>
	 <?php
	 	echo "Choose Kiosk Name:";
		if( $this->request->session()->read('Auth.User.group_id')!= MANAGERS){
			echo $this->Form->input(null, array(
										'options' => $kiosks,
										'label' => false,
										'div' => false,
										'id'=> 'kioskid',
										'name' => 'Product[kiosk_id]',
										'empty' => 'Select Kiosk',
										'style' => 'width:170px;margin-top: 12px;'
										)
									);
		}else{
			echo $this->Form->input(null, array(
										'options' => $kiosks,
										'label' => false,
										'div' => false,
										'id'=> 'kioskid',
										'name' => 'Product[kiosk_id]',
										'empty' => 'Select Kiosk',
										'style' => 'width:170px;margin-top: 12px;'
										)
									);
		}
       
      echo "<table>";
	   
	 	  $options = array(
						'label' => 'Export',
						'name'  =>'submit',
						'id'    => 'Export',
						'div' => array(
							'class' => 'submit',
						)
					);
		  echo $this->Form->submit("Export",$options);
		echo $this->Form->end()."</td></tr></table>";?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Product'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Categories'), array('controller' => 'categories', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Category'), array('controller' => 'categories', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
		<?php if($this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
		<li><?php echo $this->Html->link(__('Send new product <br/> notification'), array('controller' => 'products', 'action' => 'new_product_push_notification'),['escape'=>false]); ?> </li>
		<li><?php echo $this->Html->link(__('Send price change <br/> notification'), array('controller' => 'products', 'action' => 'product_price_change_push_notification'),['escape'=>false]); ?> </li>
		<?php } ?>
	</ul>
</div>
<script>
$(function() {
  $( document ).tooltip({
   content: function () {
    return $(this).prop('title');
   }
  });
 });
function validateForm(){
	 
	var kioskid = $('#kioskid').prop("selectedIndex");
	if (kioskid == 0) {
		$('#error_div').html("Please choose kiosk ").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
		alert('Please choose kiosk');
		return false;
	}
	
	$('input[name^="import_data"]').each(function () {
		$(this).rules('add', {
			required: true,
			accept: "image/jpeg, image/pjpeg"
		})
	}) 
	 
	 
}
  
</script>