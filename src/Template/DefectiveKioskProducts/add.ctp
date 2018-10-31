<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$adminDomainURL = URL_SCHEME.ADMIN_DOMAIN;
//echo ADMINISTRATORS;die;
?>
<style>
 #remote .tt-dropdown-menu {
  max-height: 250px;
  overflow-y: auto;
}
 #remote .twitter-typehead {
  max-height: 250px;
  overflow-y: auto;
}
.tt-dataset, .tt-dataset-product {
  max-height: 250px;
  overflow-y: auto;
}
.row_hover:hover{
 color:blue;
 background-color:yellow;
}
</style>
<?php //echo $hint;die;?>
<?php
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<?php
	$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
	extract($this->request->query);
	if(!isset($search_kw)){$search_kw = "";}
	if(empty($this->request->query)){
		$displayType = "show_all";
		
	}
	//pr($this->Session->read('Basket'));
?>
<div class="centralStocks index">
	<form action='<?php echo $this->request->webroot;?>DefectiveKioskProducts/search' method = 'get'>
		<div class="search_div">
			<fieldset>
				<legend>Search</legend>
				<table>
					<tr>
						<td></td>
						<td><strong>Find by category &raquo;</strong></td>						
					</tr>
					<tr><td><div id='remote'><input class="typeahead" type = "text" value = '<?= $search_kw ?>' name = "search_kw" placeholder = "Search by product title or product code" style = "width:325px" autofocus/></div></td>
						<td rowspan="4"><select id='category_dropdown' name='category[]' multiple="multiple" size='6'onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select></td></tr>
					<tr>
						<td><h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4></td>
					</tr>
					<tr>
						<td>
							<input type = "submit" name = "submit" value = "Search Product"/>
							<input type='button' name='reset' value='Reset' style="width: 100px;" onClick='reset_search();'/>
							</p></td>
					</tr>					
				</table>
			</fieldset>	
		</div>
	</form>
	<?php
		$sessionBaket = $this->request->Session()->read("ch_raw_faulty_product_basket");//change rajju 13/12/17
		
		$kiosk_id = "";
		$s_kiosk_id = $this->request->Session()->read("kiosk_id");
		if( !empty($s_kiosk_id) ){
			$kiosk_id = $this->request->Session()->read("kiosk_id");
		}
	?>
	<?php
	$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
		  $updateUrl = "/img/16_edit_page.png";
	?>
	<table>
		<tr>
			<td><h2><?php echo __('Add Faulty Products')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
			<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
			</h2></td>
			
			<td><?=$this->Html->link('Restore Session', array('action' => 'restore_session', $this->request->params['controller'], $this->request->params['action'], 'ch_raw_faulty_product_basket', $kiosk_id));//change rajju 13/12/17?></td>
		</tr>
	</table>
	
	<h4>You have <?php echo count($sessionBaket);?> item(s) in the cart</h4>
	
	<?php echo $this->Form->create(null,array('url' => array('controller' => 'defective_kiosk_products','action' => 'add_raw_data'))); ?>
	<input type='hidden' id='hidden_button_val' value=''/>
    
	
	<div class="submit">
	 <table>
	  <tr>
	   <td style='width: 30px';><input type="submit" name='basket' value="Add to Basket" id="add_1" onclick="updateHiddenButton(this.id);"/></td>
	   <td style='width:  5555px';><input type="submit" name='Dispatch' value="Mark Faulty" id="dispatch_1" onclick="updateHiddenButton(this.id);"/></td>
	   <td><input type="submit" name='checkout' value="Checkout" id="checkout_top" onclick="return checksession1();"/></td>
	   <td style='width: 35px';><input type="submit" name='empty_basket' value="Clear the Basket" onclick="updateHiddenButton();"/></td>
	  </tr>
	 </table>

	</div>
	<br><br> <br>
	<span class='paging' style='text-align:right;float:right;margin-top: -50px;'>
			<?php
				echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
				echo $this->Paginator->numbers(array('separator' => ''));
				echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
			?>
	</span>
	<span><?php echo "**This screen is for Warehouse and admin, kiosk both can add faulty products";?></span>
	<table cellpadding="0" cellspacing="0">	
		<tr><td colspan='8'><hr></td></tr>
		<tr>			
			<th><?php echo $this->Paginator->sort('product_code'); ?></th>
			<th><?php echo $this->Paginator->sort('product_id'); ?></th>
			<th><?php echo $this->Paginator->sort('color');?></th>
			<th>Image</th>
			<th><?php echo $this->Paginator->sort('quantity','Current');?>&nbsp;<strong>Stock</strong></th>
			<th><?php echo $this->Paginator->sort('selling_price','Selling'); ?>&nbsp;<strong>Price</strong></th>
			<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){ ?>
			<th>Quantity</th>			
			<?php }else{ ?>
			<th>Add Quantity</th>
			<?php } ?>
			<th>Remarks</th>
		</tr>	
	<tbody>
	<?php $currentPageNumber = $this->Paginator->current();?>	
	<?php
	$groupStr = "";
	foreach ($centralStocks as $key => $centralStock):
	$groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
	?>
	<?php 
    
		 $truncatedProduct = \Cake\Utility\Text::truncate(
				$centralStock->product,
				30,
				[
				    'ellipsis' => '...',
				    'exact' => false
				]
			);
		
		$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$centralStock->id.DS;
		$imageName =  $centralStock->image;
		$absoluteImagePath = $imageDir.$imageName;
		$LargeimageURL = $imageURL = "/thumb_no-image.png";
		if(!empty($imageName)){
			$imageURL = "{$adminDomainURL}/files/Products/image/".$centralStock->id."/thumb_"."$imageName"; //rasu
			$LargeimageURL = "{$adminDomainURL}/files/Products/image/".$centralStock->id."/vga_"."$imageName"; //rasu
		}
		$productQuantity = "";
		$productPrice = $centralStock->selling_price;
		$productRemarks = "";
		//pr($sessionBaket);
		if( count($sessionBaket) >= 1){
			if(array_key_exists($centralStock->id,$sessionBaket)){
				$productQuantity = $sessionBaket[$centralStock->id]['quantity'];
				$productPrice = $sessionBaket[$centralStock->id]['price'];
				$productRemarks = $sessionBaket[$centralStock->id]['remarks'];
			}
		}
		
	?>
	<tr>
		<td><?php echo $centralStock->product_code; ?></td>
		<td>
		<?php
			echo $this->Html->link($truncatedProduct,
					array('controller' => 'products', 'action' => 'view', $centralStock->id),
					array('escapeTitle' => false, 'title' => $centralStock->product, 'id' => "tooltip_{$centralStock['Product']['id']}")
				);
			?>
		</td>
		<td><?php echo $centralStock->color; ?></td>
		<td><?php
			echo $this->Html->link(
					$this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')), //rasu
					$LargeimageURL,
					array('escapeTitle' => false, 'title' => $centralStock->product,'class' => "group{$key}")
				);
			?>
		</td>
		<td><?php echo h($centralStock->quantity); ?>&nbsp;</td>
		<td><?php echo $CURRENCY_TYPE.$centralStock->selling_price; ?>&nbsp;</td>
		<?php
			
			////echo $this->Form->input(null,array(
			////		'type' => 'text',
			////		'name' => "data[DefectiveKioskProduct][price][$key]",
			////		'value' => $productPrice,
			////		'label' => false,
			////		'style' => 'width:80px;'
			////		)
			////	);
			
			?>
		<td><?php
			echo $this->Form->input(null,array(
					'type' => 'hidden',
					'value' => $centralStock->product_code,
					'label' => false,
					'id' => 'product_code_'.$centralStock->id,
					'name' => "DefectiveKioskProduct[product_code][$key]"
				)
								   );
			echo $this->Form->input(null,array(
					'type' => 'hidden',
					'name' => "DefectiveKioskProduct[p_quantity][$key]",
					'value' => $centralStock->quantity,
					'label' => false,
					'style' => 'width:80px;',
					'id' => 'original_qtt_'.$centralStock->id
				)
					       );
			echo $this->Form->input(null,array(
					'type' => 'hidden',
					'name' => "DefectiveKioskProduct[current_price][$key]",
					'value' => $centralStock->selling_price,
					'label' => false,
					'style' => 'width:80px;'
				)
					       );
			echo $this->Form->input(null,array(
					'type' => 'hidden',
					'name' => "DefectiveKioskProduct[product_id][$key]",
					'value' => $centralStock->id
				     )
				);
			if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
			   $this->request->session()->read('Auth.User.group_id') == MANAGERS ||
			    $this->request->session()->read('Auth.User.group_id') == SALESMAN ||
				$this->request->session()->read('Auth.User.group_id') == inventory_manager
			   ){
					echo $this->Form->input(null,array(
					'type' => 'text',
					'name' => "DefectiveKioskProduct[quantity][$key]",
					'value' => $productQuantity,
					'label' => false,
					'style' => 'width:50px;',
					'readonly' => false,
					'id' => $centralStock->id
					)
				);
			}else{
				       if((int)$productQuantity){
					$checked = "checked";
				       }else{
					$checked = "";
				       }
					echo $this->Form->input(null,array(
					'type' => 'checkbox',
					'name' => "DefectiveKioskProduct[quantity][$key]",
					'value' => 1,
					'label' => false,
					'checked' => $checked,
					'readonly' => false,
					'id' => $centralStock->id
					)
				);
			}
			
			?>
		</td>		
		<td>
		<?php echo $this->Form->input(null,array(
					'name' => "DefectiveKioskProduct[remarks][$key]",
					'value' => $productRemarks,
					'label' => false,
					'empty' => 'None',
					//'style' => 'width:80px;',
					'options' => $faulty_conditions,
					'readonly' => false,
					'id' => 'remark_'.$centralStock->id
					)
				); ?>
		</td>
	</tr>
<?php endforeach; ?>
	<?php echo $this->Form->input(null,array('type' => 'hidden','value' => $currentPageNumber,'name' => 'current_page'));?>
	</tbody>
	</table>
	<div class="submit">
	 <table>
	  <tr>
	   <td style = 'width:30px';><input type="submit" name='basket' value="Add to Basket" id="add_2" onclick="updateHiddenButton(this.id);"/></td>
	   <td style = 'width:5555px';><?php		
		$options1 = array('label' => 'Mark Faulty','div' => false, 'name' => 'Dispatch', 'id' => 'dispatch_2', 'onClick' => "updateHiddenButton(this.id);");
		echo $this->Form->end($options1);		
	   ?></td>
	   <td><input type="submit" name='checkout' value="Checkout" id="checkout_bottom" onclick="return checksession2();"/></td>
	   <td style = 'width:30px';><input type="submit" name='empty_basket' value="Clear the Basket" onclick="updateHiddenButton('');"/></td>
	  </tr>
	 </table>
		
		
	 
	</div>
	<p>
	<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){?>
	 <?=$this->element('faulty_slide_menu');?>
	<?php }else{ ?>
	 <ul>
	   <li><?php echo $this->Html->link(__('Add Faulty'), array('controller' => 'defective_kiosk_products', 'action' => 'add')); ?></li>
	   <li><?php echo $this->Html->link(__('Faulty References'), array('controller' => 'defective_kiosk_products', 'action' => 'list_defective_references')); ?></li>
	   <li><?php echo $this->Html->link(__('View Faulty'), array('controller' => 'defective_kiosk_products', 'action' => 'view_faulty_products')); ?></li> 
	 </ul>
	<?php } ?>
</div>
<script>
	
	function submitForm(){
		document.getElementById("display_form").submit();
	}
	
</script>

<input type='hidden' name='url_category' id='url_category' value=''/>
<script type="text/javascript">
 function update_hidden(){
  //var singleValues = $( "#single" ).val();
  var multipleValues = $( "#category_dropdown" ).val() || [];
  $('#url_category').val(multipleValues.join( "," ));
 }
</script>
<script>
 var product_dataset = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  //prefetch: "/products/data",
  remote: {
    url: "/products/admin-Data?category=%CID&search=%QUERY",
                    replace: function (url,query) {
					 var multipleValues = $( "#category_dropdown" ).val() || [];
                     $('#url_category').val(multipleValues.join( "," ));
					 //alert($('#url_category').val());
					 return url.replace('%QUERY', query).replace('%CID', $('#url_category').val());
					},
					
                        wildcard: "%QUERY"
    
  }
});

$('#remote .typeahead').typeahead(null, {
  name: 'product',
  display: 'product',
  source: product_dataset,
  limit:120,
  minlength:3,
  classNames: {
    input: 'Typeahead-input',
    hint: 'Typeahead-hint',
    selectable: 'Typeahead-selectable'
  },
  highlight: true,
  hint:true,
  templates: {
    suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:400px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{product_code}}</a></strong></div>'),
	header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------<?php echo ADMIN_DOMAIN;?>---------</b></div>"),
  }
});
</script>
<script>
<?php
   foreach ($centralStocks as $key => $centralStock):
      $string = preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($centralStock['Product']['product']));
     if(empty($string)){
      $string = $centralStock['Product']['product'];
      //htmlspecialchars($str, ENT_NOQUOTES, "UTF-8")
     }
      echo "jQuery('#tooltip_{$centralStock['Product']['id']}').tooltip({content:\"".htmlspecialchars($string)."\",track:true});";
     endforeach;
    ?>
    
    function checksession1 ()
     {
      <?php if(empty($sessionBaket)){?>
      alert("Session basket is empty, please first add items to basket!");
      return false;
     <?php } ?>
     }
     
     function checksession2 ()
     {
      <?php if(empty($sessionBaket)){?>
      alert("Session basket is empty, please first add items to basket!");
      return false;
     <?php } ?>
     }
	 
     //$('.Checkbox:checked').map(function() {return this.value;}).get().join(',')
	 
	 /*$('#inactivate_button').click(function(ev){
	 var checkedVals = $('.stock_activate:checkbox:checked').map(function() {
	   return this.value;
	  }).get();
	  var checkedVals = checkedVals.join(",");
	 msgStr = "Are you sure you want to deactivate products with product code: "+checkedVals;
	 if(!confirm(msgStr)){
	  ev.preventDefault();
	 }
	});*/
    
</script>

<script>
  function validationQuantity() {
   var hiddenbutton = $('#hidden_button_val').val();
   if (hiddenbutton == 'dispatch_1' || hiddenbutton == 'add_1' || hiddenbutton == 'dispatch_2' || hiddenbutton == 'add_2') {
	 var nonEmptyTextBoxes = $('input:text').filter(function() { return this.value != ""; });
	 //alert(nonEmptyTextBoxes.length);
	 console.log(nonEmptyTextBoxes)
	 var emptyRemarkString = "Please choose remarks for - \n";
	 var quantityErrorString = "Please choose less than/equal to the quantity for - \n";
 
	 var countEachRemark = 0;
	 var count_each_quantity = 0;
	 var thisId = this.id;
	 nonEmptyTextBoxes.each(function() {
	  if ($('#remark_' + thisId ).val() == '' || $('#remark_' + thisId ).val() == 0) {
		 countEachRemark+=1;
	  }
	  
	  if (parseInt($('#original_qtt_' + thisId ).val()) < parseInt($('#' + thisId).val())) {
		 count_each_quantity+=1;
	  }
	 });
	 
	 var countRemark = 0;
	 var countQuantity = 0;
	 nonEmptyTextBoxes.each(function() {
	  if ($('#remark_' + thisId ).val() == '' || $('#remark_' + thisId ).val() == 0) {
	   countRemark+=1;
		 if (countEachRemark == countRemark) {
			emptyRemarkString += $('#product_code_' + thisId).val();
		 } else {
			emptyRemarkString += $('#product_code_' + thisId).val() + ' ,';
		 }
	  }
	 
	  if (parseInt($('#original_qtt_' + thisId ).val()) < parseInt($('#' + thisId).val())) {
	   countQuantity+=1;
		 if (count_each_quantity == countQuantity) {
			quantityErrorString += $('#product_code_' + thisId).val();
		 } else {
			quantityErrorString += $('#product_code_' + thisId).val() + ' ,';
		 }
	  }
	 });
	 
	 if (countRemark > 0) {
		 alert(emptyRemarkString);
	 }
	 
	 if (countQuantity > 0) {
		 alert(quantityErrorString);
	 }
	 var countTotalError = countRemark+countQuantity;
	 if (countTotalError > 0) {
		 return false;
	 } else {
		$('form').submit();
	 }
	}
  }
  
  function updateHiddenButton(id) {
    $('#hidden_button_val').val(id);
  }
</script>
<script>
$(function() {
  $( document ).tooltip({
   //content: function () {
    //return $(this).prop('title');
   }
  });
 });
</script>
<script>
	function reset_search(){
		jQuery( ".typeahead" ).val("");
		jQuery( "#category_dropdown" ).val("");
	}
</script>
<input type='hidden' name='url_category' id='url_category' value=''/>
<script type="text/javascript">
 function update_hidden(){
  //var singleValues = $( "#single" ).val();
  var multipleValues = $( "#category_dropdown" ).val() || [];
  $('#url_category').val(multipleValues.join( "," ));
 }
</script>
<script>
	var product_dataset = new Bloodhound({
	datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	//prefetch: "/products/data",
	remote: {
    url: "/products/admin_data?category=%CID&search=%QUERY",
                    replace: function (url,query) {
					 var multipleValues = $( "#category_dropdown" ).val() || [];
                     $('#url_category').val(multipleValues.join( "," ));
					 return url.replace('%QUERY', query).replace('%CID', $('#url_category').val());
					},
                    wildcard: "%QUERY"
    
	}
});
	
$('#remote .typeahead').typeahead(null, {
	name: 'product',
	display: 'product',
	source: product_dataset,
	limit:120,
	minlength:3,
	classNames: {
		input: 'Typeahead-input',
		hint: 'Typeahead-hint',
		selectable: 'Typeahead-selectable'
	},
	highlight: true,
	hint:true,
	templates: {
	  suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:400px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{product_code}}</a></strong></div>'),
	  header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	  footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------<?php echo ADMIN_DOMAIN;?>---------</b></div>"),
	}
});
</script>
<script>
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>