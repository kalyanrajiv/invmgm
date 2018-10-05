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
	<?php $add_2_cart_full = $this->Url->build(array('controller' => 'products', 'action' => 'read_from_file')); ?>
	<?php $import = $this->Url->build(array('controller' => 'products', 'action' => 'import')); ?>
	<input type='hidden' name='add_2_cart_full' id='add_2_cart_full' value='<?=$add_2_cart_full?>' />
	<input type='hidden' name='import' id='import' value='<?=$import?>' />
	<?=$this->Form->create('Product',array('enctype'=>'multipart/form-data'));?>
	<h2><?php echo __('Import Products')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	 <?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	</h2>
		
	
	<div id = "status_bar" style="width: 550px;height: 150px;overflow: scroll;display: none;">
		Import Status:
	</div>
	<h3>Changeable fields:</h3>
	<h5 style='color: blue;'>*Only selected fields will be imported</h5>
	<h4 ><b>All validations are by-passed here. Any validation rules by-passed would be on user behalf doing import.</b>
	</br></br>
	<span style="color: black;"><b>This script will update on Main site and All external sites.</b></span>
	</br>
	<span style="color: black;"><b>All Selling Prices Are Inclusive Vat.</b></span>
	<span style="float: right;color: black"><b>Estimated time per thousand records is 4 mins (with all checkboxes ticked)</b></span>
	</h4>
	<?php $fields = array_diff($productFields,array('id','quantity','qty_update_status','qty_update_time','back_stock_status','back_stock_time',"model_id","model","stock_level","qty_modified","last_import","last_updated","modified_by"));
	//pr($fields);
	$chunk_fields = array_chunk($fields,8,true);
	?>
	<table>
	<tr>
      <td><input type = 'checkbox'  name = 'selectall' id = 'selectall' >SelectAll</td>
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
			//pr($chunk_fields); 
			foreach($chunk_fields as $c => $chunk){
				echo "<td>";
					foreach($chunk as $ch => $field){
						// pr($field);//die;
						if(false){
							echo $this->Form->input($field, array('type' => 'checkbox',
								'name'=>'Product[import][]',
								'label' => array('style' => "color: blue;"),
								'id'=> $ch,
								'value' => $ch,
								'class' => 'checkbox1',
								'hiddenField' => false,
								'checked' => "checked"
								));
						}else{
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
			}
				echo "<td>";
		}
		echo "</tr>";
		echo "</table>";
   
   ?>
			
	<p><?//=implode(", ",array_diff($productFields,array('id')))?></p>
	<?=$this->Form->input('Product.import_data', array('type' => 'file'))?>
	<?=$this->Form->Submit('Upload',array('name'=>'submit'));?>
    <?=$this->Form->end();?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Product'), array('action' => 'add'),array('escape'=>false,'style'=>"width: 118px;")); ?></li>
		<li><?php echo $this->Html->link(__('List Categories'), array('controller' => 'categories', 'action' => 'index'),array('escape'=>false,'style'=>"width: 118px;")); ?> </li>
		<li><?php echo $this->Html->link(__('New Category'), array('controller' => 'categories', 'action' => 'add'),array('escape'=>false,'style'=>"width: 118px;")); ?> </li>
		<li><?php echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index'),array('escape'=>false,'style'=>"width: 118px;")); ?> </li>
		<li><?php echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add'),array('escape'=>false,'style'=>"width: 118px;")); ?> </li>
		<?php if($this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
		<li><?php echo $this->Html->link(__('Send new <br/>product notification'), array('controller' => 'products', 'action' => 'new_product_push_notification'),array('escape'=>false,'style'=>"width: 118px;")); ?> </li>
		<li><?php echo $this->Html->link(__('Send price <br/>change notification'), array('controller' => 'products', 'action' => 'product_price_change_push_notification'),
                                         array('escape'=>false,'style'=>"width: 118px;")); ?> </li>
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

<script>
	 function clicked() {
       if (confirm('Do you want to submit?')) {
           alert('hi');
		   return false;
       } else {
           alert('bye');
		   return false;
       }
    }
</script>
<script>
	
	function fetchdata(){
		$('#status_bar').show();
		//show_progress();
		var import_url = $('#import').val();
		
		$.ajax({
			url: import_url,
			type: 'post',
			success: function(data){
				
			},
			complete:function(data){
				if (jQuery.parseJSON(data) == "kiosk19qantity20") {
					;
				}else{
					;
				}
			}
		});
	}
	
	function show_progress() {
		var importCompleted = false;
        var url_to_go = $('#add_2_cart_full').val();
		$.ajax({
			url: url_to_go,
			type: 'post',
			success: function(response){
				alert("fdsaf");
				if (String(response) != "") {
                    var objArr = $.parseJSON(response);
					qryStatus = objArr.data
					importCompleted = objArr.all_processed;
					var msg = '';
					randomNumber = Math.random();
					$.each(qryStatus, function(key, kioskProcessedRecs){//alert(kioskProcessedRecs);
						var processedArr = String(kioskProcessedRecs).split(",");
						var kioskName = kioskProcessedRecs[0];
						var records = kioskProcessedRecs[1];
						msg += "<br/>For kiosk "+kioskName+":" + records+" records are processed";
						
					});
					//Perform operation on return value
					document.getElementById('status_bar').innerHTML = String(msg)+"<br/>------------"+randomNumber+"------------";
                }
				
			},
			complete:function(response){
				alert('fsaf');
				//var objArr = $.parseJSON(response);
				/*alert(objArr);
				if (objArr.all_processed == 1) {
					alert("Import finished")
				}else{
					setTimeout(show_progress,1000);
				}*/
				if (!importCompleted) {
                    setTimeout(show_progress,1000);
                }else{
					alert("Import completed successfully");
				}
			}
		});
    }
	//$(document).ready(function(){
	//	setTimeout(fetchdata,5000);
	//});
</script>

