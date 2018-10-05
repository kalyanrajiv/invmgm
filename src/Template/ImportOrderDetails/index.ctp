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
<?php	$sessionKioskId = $this->request->Session()->read('kiosk_id');
	extract($this->request->query);
	if(!isset($search_kw)){$search_kw = "";}
	$searchKw = $categoryQuery = $categoryqryStr = $reference = '';
	if(!empty($this->request->query)){
		if(array_key_exists('search_kw',$this->request->query)){
			$searchKw = $this->request->query['search_kw'];
		}
		 
		
		if(array_key_exists('category',$this->request->query)){
			$categoryQuery = $this->request->query['category'];
			$categoryqryStr = "";
			foreach($categoryQuery as $categoryqry){
				$categoryqryStr.="&category%5B%5D=$categoryqry";
			}
		}
		
	}
	if($this->request->Session()->read('session_reference')){
		  $reference = $this->request->Session()->read('session_reference');
		} 
?>
<div class="centralStocks index">
	<form action='<?php echo $this->request->webroot;?>ImportOrderDetails/search' method = 'get'>
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
					</tr>
					<tr>
						<td><h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4></td>
					</tr>
					<tr>
						<td>
							<input type = "submit" name = "submit" value = "Search Product"/></p></td>
					</tr>					
				</table>
			</fieldset>	
		</div>
	</form>
	<?php
		$importBaket = $this->request->Session()->read("import");
		//pr($importBaket);
	?>
	<?php
		$screenHint = $hintId = "";
        //pr($hint);die;
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
		  $updateUrl = "/img/16_edit_page.png";
	?>
	<table>
		<tr>
			
			 <td><h2><?php echo __('Send for Replacement')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
			 <?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
			 </h2></td>
		</tr>
	</table>
	
	<h4>You have <?php echo count($importBaket);?> item(s) in the cart</h4>
	
	<?php echo $this->Form->create(null,array('url' => array('controller' => 'import_order_details','action' => 'import_stock'))); ?>
	<span>&nbsp;</span>
	<table>
		<tr>
			<td>
				 <strong>Reference Number</strong> <?php echo $this->Form->input(null,array(
					 'type' => 'text',
					 'name' => "Product[reference]",
					 'label' => false,
					 'style' => 'width:100px;height:15px',
					 'readonly' => false,
					 'div' => false,
					 'id' => 'reference',
					 'value' => $reference
					 ));
				 ?></td>
			<td>
			 <?=$this->Html->link('Restore Session', array('action' => 'restore_session', $this->request->params['controller'], 'index', 'import', $sessionKioskId));?>
			</td>
		</tr>
	</table>
	<div class="submit">
	 <table>
		 <tr>
			 <td style='width:30px;'><input type="submit" name="basket" id="basket"  onclick = "return inputReference();" value="Add to Basket" , /></td>
			 <td style='width:30px;'><input type="submit" name='check_out' id='check_out' onclick = "return inputReference();" value="Check out"/></td>
			 <td style='width:5500px;'><input type='submit' name='Placed Order' id='Placed_Order' value='Place Order'
			 onclick  = 'return inputReference();'/></td>
			 <td style='width:30px;'><input type="submit" name='empty_basket' value="Clear the Basket"/></td>
			 <td style='width:30px;'><input type="submit" name='move_to_bin' id='move_to_bin' onclick = "return inputReference();" value="Move to bin"/></td>
		 </tr> 
	 </table>
	</div>
	<span class='paging' style='text-align:right;float:right;margin-top: -116px;'>
			<?php
				echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
				echo $this->Paginator->numbers(array('separator' => ''));
				echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
			?>
	</span>
	<table cellpadding="0" cellspacing="0">	
		<tr><td colspan='8'><hr></td></tr>
		<tr>			
			<th><?php echo $this->Paginator->sort('product_code'); ?></th>
			<th><?php echo $this->Paginator->sort('product_id'); ?></th>
			<th><?php echo $this->Paginator->sort('category_id'); ?></th>
			<th><?php echo $this->Paginator->sort('color');?></th>
			<th>Image</th>
			<th colspan=2 style="text-align: center;">Quantity</th>
          	<th>Remarks</th>
		</tr>	
	<tbody>
	<?php $currentPageNumber = $this->Paginator->current();?>	
	<?php  //pr($products);die;
	 $groupStr = "";
		foreach ($products as $key => $product):
		$groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
        //pr($product);die; ?>
		 <?php 
			if(array_key_exists($product->category_id,$categoryName)){
				$catName = $categoryName[$product->category_id];
			}else{
				$catName = "--";
			}
			$truncatedProduct = \Cake\Utility\Text::truncate(
                                                    $product->product,
                                                    30,
                                                    [
                                                        'ellipsis' => '...',
                                                        'exact' => false
                                                    ]
                                                );
			$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product->id.DS;
			$imageName = $product->image;
			$absoluteImagePath = $imageDir.$imageName;
			$LargeimageURL = $imageURL = "/thumb_no-image.png";
            //echo $absoluteImagePath;die;
			if(@readlink($absoluteImagePath) || file_exists($absoluteImagePath)){
				$imageURL = "/files/Products/image/".$product->id."/$imageName";
				$LargeimageURL = "/files/Products/image/".$product->id."/vga_"."$imageName";
			}
			$original_quantity = '';
            //pr($product->id);
            //pr($original_quantities);die;
            //pr($original_quantities[$product->id]);die;
			if(array_key_exists($product->id,$original_quantities)){
			     $original_quantity = $original_quantities[$product->id];
			}
			$productQuantity = $original_quantity;
			$productPrice = $product->selling_price;
			$productRemarks = "";
			$checked = '';
			//pr($sessionBaket);
			if( count($importBaket) >= 1){			
				if(array_key_exists($product->id,$importBaket)){
					$productQuantity = $importBaket[$product->id]['quantity'];
					//$productPrice = $sessionBaket[$product['Product']['id']]['price'];
					$productRemarks = $importBaket[$product->id]['remarks'];
					$checked = "checked";
				}
			}
		?>
		<tr>
		<td>
		   <?php echo $product->product_code; ?>
	   </td>
	   <td>
		 	<?php
            $product_id = $product->id;
			echo $this->Html->link($truncatedProduct,
					array('controller' => 'products', 'action' => 'view', $product->id),
					array('escapeTitle' => false,
						  'title' => $product->product,
						  'id' => "tooltip_{$product_id}"
						  )
				);
			?>
		</td>
		<td><?php echo $catName; ?></td>
		<td><?php echo $product->color; ?></td>
		<td><?php
			echo $this->Html->link(
					$this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')),
					$LargeimageURL,
					array('escapeTitle' => false, 'title' => $product->product,'class' => "group{$key}")
				);
			?>
		</td>
	   <td><?php
       //pr($productQuantity);die;
       //pr($original_quantity);die;
	   echo $this->Form->input(null,array(
					       'type' => 'hidden',
					       'name' => "Product[p_quantity][$key]",
					       'value' => $original_quantity,
					       'label' => false,
					       'style' => 'width:80px;'
					     )
					  );
	   echo $this->Form->input(null,array(
					'type' => 'hidden',
					'name' => "Product[product_id][$key]",
					'value' => $product->id
				     )
				);
	   echo $this->Form->input(null,array(
					'type' => 'checkbox',
					'name' => "Product[checked][$key]",
					 'value' => $productQuantity,
					'label' => false,
					'style' => 'width:80px;',
					'readonly' => false,
					'div' => false,
					'style' => "margin-top: 6px;width: 17px;height: 17px;",
					'checked' => $checked
					)
				);
       echo "</td><td>";
	   echo $this->Form->input(null,array(
					'type' => 'text',
					'name' => "Product[quantity][$key]",
					 'value' => $productQuantity,
					'label' => false,
					'style' => 'width:80px;',
					'readonly' => false,
					'div' => false
					)
				);
	    ?>
		</td>
		<td><?php echo $this->Form->input(null,array(
					'type' => 'text',
					'name' => "Product[remarks][$key]",
					'value' => $productRemarks,
					'label' => false,
					'style' => 'width:80px;',
					'readonly' => false
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
		   <td style='width:30px;'><input type="submit" name='basket' id ='basket1'  onclick = "return inputReference();"  value="Add to Basket"/></td>
		   <td style='width:30px;'><input type="submit" name='check_out' id='check_out'  onclick = "return inputReference();" value="Check out"/></td>
		   <td style='width:5500px;'><?php
					      $options1 = array('label' => 'Place Order',
						'div' => false,
						'name'=>'Placed Order',
						'id'=>'Placed_Order1',
						'onclick'=>' return inputReference();'
						);		
					echo $this->Form->end($options1);		
			 ?>
		   </td>
		   <td style='width:30px;'><input type="submit" name='empty_basket' value="Clear the Basket"/></td>
			 <td style='width:30px;'><input type="submit" name='move_to_bin' id='move_to_bin' onclick = "return inputReference();" value="Move to bin"/></td>
	  </tr>
	 </table>
	 
	
	</div>
	<p>
	<?php
	//echo $this->Paginator->counter(array(
	//'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	//));
	?></p>
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
       <?=$this->element('faulty_slide_menu');?>
</div>
 
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
<script type="text/javascript">
    <?php
	  foreach ($products as $key => $product):
      $string = preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($product['Product']['product']));
     if(empty($string)){
      $string = $product['Product']['product'];
      //htmlspecialchars($str, ENT_NOQUOTES, "UTF-8")
     }
      echo "jQuery('#tooltip_{$product['Product']['id']}').tooltip({content:\"".htmlspecialchars($string)."\",track:true});";
     endforeach;
    ?>
    </script>
 
 <script>
  
  function inputReference(){
		 var reference = $('#reference').val();
		  if(reference ==  null || reference == ""){
			alert("Please input the reference number!");
 				return false;
		}
   }
//  $(document).ready(function() {
//	    $('#Placed_Order').click(function(event){  
//		if(document.getElementById("reference").value == null || document.getElementById("reference").value == ""){
//				alert("Please input the reference number!");
//				return false;
//			}
//	    });
//	     $('#basket').click(function(){
//			if(document.getElementById("reference").value == null || document.getElementById("reference").value == ""){
//				alert("Please input the reference number!");
//				return false;
//			}
//	    });
//		 $('#Placed_Order1').click(function(){
//			if(document.getElementById("reference").value == null || document.getElementById("reference").value == ""){
//				alert("Please input the reference number!");
//				return false;
//			}
//	    });
//		 $('#basket1').click(function(){
//			if(document.getElementById("reference").value == null || document.getElementById("reference").value == ""){
//				alert("Please input the reference number!");
//				return false;
//			}
//	    });
//	 
 	//});
	 
	
</script>
 <script>
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
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
 