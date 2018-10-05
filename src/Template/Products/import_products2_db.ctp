<?php
    use Cake\Core\Configure;
    use Cake\Core\Configure\Engine\PhpConfig;
	$siteBaseUrl = Configure::read('SITE_BASE_URL');
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<?php
	$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
		  $updateUrl = "/img/16_edit_page.png";
?>
<div class="products index">
	<form method="POST" action="">
	<?php if(!empty($last_updated)){?>
		<b>last updated </b><?=date('d-m-y h:i:s',strtotime($last_updated));?>
		<input type='submit' value="Truncate table" onclick="return confirm('Are you sure to delete table?');"	 name="truncate" /></br>
		<?php }else{?>
		No database table To Import
		<?php } ?>
	</form>
		
	<?=$this->Form->create('Product',array('enctype'=>'multipart/form-data')); //,'onSubmit' => 'return validateForm();' ?>
	<?php if(!empty($last_updated)){?>
	<input type='submit' name='import_table' id='import_table' value='Import CSV Data From DB (csv_products)' style='margin-top:21px;'/>
	<?php } ?>
	<h2 style='margin-top:21px;'><?php echo __('Import CSV Product Data 2 DB')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	
	 <?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	</h2>
	<?php if(!empty($last_updated)){
		echo "<h3 style='word-wrap: break-word;'><b>Selected Fields : </b>".$selected_fields."</h3>";
		} ?>
	
	<?php if(empty($last_updated)){ ?>
	<div id="error_div" tabindex='1'></div>
	<h3>Changeable fields:</h3>
	<h5 style='color: blue;'>*Only selected fields will be imported</h5>
	<?php
	$fields = array_diff($productFields,array('id'));
	if($idxFld1 = array_search('stock_level', $productFields)){
		unset($idxFld1[$idxFld1]);
	}
	$chunk_fields = array_chunk($fields,8,true);
	?>
	<table>
	<tr>
      <td><input type = 'checkbox'  name = 'selectall' id = 'selectall' >Select All</td>
  </tr>
	</table>
	<?php
		if(count($chunk_fields)){
			echo "<table id = 'import_product_table'>";
			echo "<tr>";
			echo "<td colspan='8'>";
			echo ("<h4>Import Product</h4><hr/>");
			echo "</td>";
			echo "</tr>";
			echo "<tr>";
			//pr($chunks); 
			foreach($chunk_fields as $c => $chunk){
				echo "<td>";
					foreach($chunk as $ch => $field){
						// pr($field);//die;
						echo $this->Form->input($field, array('type' => 'checkbox',
						'name'=>'Product[import][]',
						'label' => array('style' => "color: blue;"),
						'id'=> $ch,
						'value' => $ch,
						'class' => 'checkbox1',
						'hiddenField' => false,
						));
					}
			}
				echo "<td>";
		}
		echo "</tr>";
		echo "</table>";
   
   ?>
			
	<p><?//=implode(", ",array_diff($productFields,array('id')))?></p>
	<?=$this->Form->input('Product.import_data', array('type' => 'file'))?>
	<?=$this->Form->Submit('Upload',array('onSubmit' => 'return validateForm();'));?>
    <?=$this->Form->end();?>
	<?php } ?>
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
		<li><?php echo $this->Html->link(__('Send new <br/>product notification'), array('controller' => 'products', 'action' => 'new_product_push_notification'),array('escape' => false)); ?> </li>
		<li><?php echo $this->Html->link(__('Send price <br/>change notification'), array('controller' => 'products', 'action' => 'product_price_change_push_notification'),array('escape' => false)); ?> </li>
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
</script>
<script>
	function validateForm(){
		if ($('#ProductImportData').val() == '') {
			$('#error_div').html("Please Choose File").css({"background-color": "yellow", "color": "red", "font-size": "20px"}).focus();
			alert("Please Choose File");
			return false;
		}
	}
</script>
<script type="text/javascript">
 $(document).ready(function() {
     $('#selectall').click(function(event) {  
  if(this.checked) { 
      $('.checkbox1').each(function() { 
   this.checked = true;               
      });
  }else{
      $('.checkbox1').each(function() { 
   this.checked = false;                       
      });        
  }
     });
      
 });
</script>

