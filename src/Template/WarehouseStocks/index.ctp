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
<?php
		$path = dirname(__FILE__);
		$isMainDomain = false;
		$isMainDomain = strpos($path,ADMIN_DOMAIN);

//$currentAction = $this->request->action;
//	$curentController = $this->request->params['controller'];
//	$currentUrl = $curentController.'/'.$currentAction;
//	echo $currentUrl;die;
echo $this->Html->script('jquery.blockUI');
	extract($this->request->query);
	if(!isset($search_kw)){$search_kw = "";}
	$warehouseBasket = $this->request->Session()->read('WarehouseBasket');
	if($this->request->Session()->read('reference_number')){
		$reference_number = $this->request->Session()->read('reference_number');
	}else{
		$reference_number = "";
	}
	$searchQueryUrl = "";
	
    //pr($this->request->query);
	if(empty($this->request->query) || count($this->request->query) == 1){
	  if(empty($this->request->query)){
		$displayType = "show_all";
	  }else{
	   if(array_key_exists('display_type',$this->request->query)){
		 $displayType = $this->request->query['display_type'];
	   }else{
		$displayType = "show_all";
	   }
	  }
	}else{
        $displayType = "show_all";
    }
	$searchKw = $categoryQuery = $categoryqryStr = '';
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
		
		$searchQueryUrl = "/search?search_kw=$searchKw$categoryqryStr&display_type=$displayType&submit=Search+Product";
	}
	$selectedVendor = "";
	if((int)$this->request->Session()->read('warehouse_vendor_id')){
		$selectedVendor = $this->request->Session()->read('warehouse_vendor_id');
	}
	
	
?>

<div class="warehouseStocks index">
<div id="idVal">
   
</div>
	<form action='<?php echo $this->request->webroot;?>warehouse-stocks/search' method = 'get'>
		<div class="search_div">
			<fieldset>
				<legend>Search</legend>
				<table>
					<tr>
						<td></td>
						<td><strong>Find by category &raquo;</strong></td>						
					</tr>
					<tr>
                        <td>
                    <div id='remote'><input class="typeahead" type = "text" value = '<?= $search_kw ?>' name = "search_kw" placeholder = "Product title or product code" style = "width:254px;"  autofocus/>
                    </div></td>
                       
                   		<td rowspan="2">
                            <select id='category_dropdown' name='category[]' multiple="multiple" size='6'><option value="0">All</option><?php echo $categories;?>
                            </select>
                        </td>
                    </tr>
					<tr>
						<td><h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4></td>
					</tr>
					<tr>
						<td>
							<input type="hidden" name="display_type" value="<?php echo $displayType;?>">
							<input type = "submit" name = "submit" value = "Search Product"/></p></td>
					</tr>					
				</table>
				
			</fieldset>	
		</div>
	</form>
    
	<?php
	//echo $displayType;die;
	 $screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
		$updateUrl = "/img/16_edit_page.png";
	?>
	<table>
		<tr>
			<td><h2><?php echo __('Stock (In/Out)')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
			<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
			</h2></td>
			<td style="width: 25%;">Show items with zero quantity</td>
		<form name="display_form" id="display_form" method="get">
		<td style="width: 7%;"><input type="radio" name="display_type" value="show_all" id="show_all" onclick="submitForm();" <?php echo $displayType=="show_all"? "checked":"" ?>>&nbsp;Yes</td>
		<td><input type="radio" name="display_type" value="more_than_zero" id="more_than_zero" onclick="submitForm();" <?php echo $displayType=="more_than_zero"? "checked":"" ?>>&nbsp;No</td>
		</form>
		</tr>
	</table>
	
	<?php echo $this->Form->create('WarehouseStock',['url' => ['action' => 'update_stock']]); ?>
	<table>
		<tr>
			<td> 
                    <strong>Vendor</strong>
                    <?php echo $this->Form->input('warehouse_vendor_id',array(
                                                                              'options' => $warehouseVendors,
                                                                              'label' => false,
                                                                              'default'=>$selectedVendor,
                                                                              //'div' => false,
                                                                              'name' => 'WarehouseStock[warehouse_vendor_id]'
                                                                              )
                                                  );
                    ?>
            </td><td>    
                <strong>Reference Number</strong>
                    <?php echo $this->Form->input(null,array(
                                                    'type' => 'text',
                                                    'name' => "WarehouseStock[reference_number]",
                                                    'label' => false,
                                                    'style' => 'width:100px;height:15px',
                                                    'readonly' => false,
                                                    //'div' => false,
                                                    'id' => 'reference_number',
                                                    'value' => $reference_number
                                                    ));
                                                ?>
                                                </td>
		<td>
			<?=$this->Html->link('Restore Session', array('action' => 'restore_session', $this->request->params['controller'], $this->request->params['action'], 'WarehouseBasket', 10000));?>
		</td>
		</tr>
	</table>
	
	<span>&nbsp;</span>
	<span class="submit">
		<table>
			<tr>
				<td style='width:30px;'><input type="submit" name='add_2_basket' value="Add to basket"/></td>
				<td style='width:30px;'><input type="submit" name='check_out' value="Check out" onclick="return inputReference();"/></td>
				<td style='width:5500px;'><input type="submit" name='Dispatch' value="Update Stock" id="update_stock" onclick="return inputReference();"/></td>
				<td style='width:30px;'><input type="submit" name='clear_basket' value="Clear basket"/></td>
			</tr>
		</table>
	 
	</span>
	<span style="float: left;color: blue;"><i style="position: relative;bottom: -30px;">**Highlighted rows are for retail cost and selling price**</i></span></br>
		<span style="float: left;color: blue;"><i style="position: relative;bottom: -30px;">**Any change in prices on this stock-in screen will effect prices of all external sites (including their kiosks)**</i></span>
		</br>
		<span style="float: left;color: blue;"><i style="position: relative;bottom: -30px;">**External sites can override only quantities  from their stock in screen.**</i></span>
	
	<span class='paging' style='text-align:right;float:right;'>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
	</span>
    
	<table cellpadding="0" cellspacing="0">	
		<tr><td colspan='8'><hr></td></tr>
		<tr>			
			
			<th><?php echo $this->Paginator->sort('product_code'); ?></th>
			<th><?php echo $this->Paginator->sort('product_id'); ?></th>
			<th><?php echo $this->Paginator->sort('color');?></th>
			<th>Image</th>
			<th><?php echo $this->Paginator->sort('quantity','Current Stock'); ?></th>
			<th><?php echo $this->Paginator->sort('cost_price','CP'); ?></th>
			<th>New</br>CP</th>
			<th><?php echo $this->Paginator->sort('selling_price','SP'); ?></th>
			<th>New</br>SP</th>
			<th>Qty</th>
			<th>In/Out</th>
			<th>Remarks</th>
		</tr>	
	<tbody>
	<?php $currentPageNumber = $this->Paginator->current();
	$groupStr = "";
	?>
	<?php foreach ($warehouseStocks as $key => $warehouseStock):
	$groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
		if($warehouseStock->retail_cost_price){
			$retail_cost_price = $warehouseStock->retail_cost_price;
			$rcp = $warehouseStock->retail_cost_price;
		}else{
			$retail_cost_price = '';
			$rcp = '--';
		} 
		
		if($warehouseStock->retail_selling_price){
			$retail_selling_price = $warehouseStock->retail_selling_price;
			$rsp = $warehouseStock->retail_selling_price;
		}else{
			 $retail_selling_price = '';
			$rsp = '--';
		}
	?>
	<?php
									
	
		$truncatedProduct = \Cake\Utility\Text::truncate(
																$warehouseStock->product,
																30,
																[
																		'ellipsis' => '...',
																		'exact' => false
																]
														);
		
		$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$warehouseStock->id.DS;
		$imageName = $warehouseStock->image;
		$absoluteImagePath = $imageDir.$imageName;
		$LargeimageURL = $imageURL = "/thumb_no-image.png";
		
		if(@readlink($absoluteImagePath) || file_exists($absoluteImagePath)){
			$imageURL = "/files/Products/image/".$warehouseStock->id."/$imageName";
			$LargeimageURL = "/files/Products/image/".$warehouseStock->id."/vga_"."$imageName";
		}
		
		$warehouseQuantity = '';
		$warehouseRemarks = '';
		$warehouseNewprice = $warehouseStock->cost_price;
		$warehouseNewSellingPrice = $warehouseStock->selling_price/(1+$vat/100);
		$inOutValue = 1;
		if(count($warehouseBasket)>=1){
			if(array_key_exists($warehouseStock->id,$warehouseBasket)){
				$warehouseQuantity = $warehouseBasket[$warehouseStock->id]['quantity'];
				$warehouseRemarks = $warehouseBasket[$warehouseStock->id]['remarks'];
				$warehouseNewprice = $warehouseBasket[$warehouseStock->id]['price'];
				$warehouseNewSellingPrice = $warehouseBasket[$warehouseStock->id]['new_selling_price'];
				$inOutValue = $warehouseBasket[$warehouseStock->id]['in_out'];
				$retail_cost_price = $warehouseBasket[$warehouseStock->id]['new_rcp'];
				$retail_selling_price = $warehouseBasket[$warehouseStock->id]['new_rsp'];
			}
		}
	?>
	<tr>
		<td>
		<input type="hidden" name="searchQueryUrl" value="<?=$searchQueryUrl;?>"/>
		<?php echo $warehouseStock->product_code;?></td>
		<td>
		<?php
			echo $this->Html->link($truncatedProduct,
					array('controller' => 'products', 'action' => 'view', $warehouseStock->id),
					array('escapeTitle' => false, 'title' => $warehouseStock->product,'id' => "tooltip_{$warehouseStock->id}")
				);
			?>
		</td>		
		<td><?php echo $warehouseStock->color;?></td>
		<td><?php
			echo $this->Html->link(
					$this->Html->image($imageURL, array('fullBase' => true,'width'=>'100px','height'=>'100px')),
					$LargeimageURL,
					array('escapeTitle' => false, 'title' => $warehouseStock->product, 'class' => "group{$key}")
				);
			?>
		</td>
		<td><?php echo h($warehouseStock->quantity); ?>&nbsp;</td>
		<td><?php echo "<span id = 'org_c_p_$key' style='position: relative;top: 7px;'>".$warehouseStock->cost_price."</span><br/><br/><br/>";
			echo "<span id = 'org_cp_$key' style= 'color: blue;'>".$rcp."</span>";?>&nbsp;</td>
		<td><?php
		if($isMainDomain){
		  echo $this->Form->input(null,array(
					'type' => 'text',
					'name' => "WarehouseStock[price][$key]",
					'value' => $warehouseNewprice,
					'id' => "c_p_$key",
					'onblur' => "price_check_cp($key)",
					'label' => false,
					'style' => 'width:48px;'
					)
				);
		}else{
		 echo $this->Form->input(null,array(
					'type' => 'text',
					'name' => "WarehouseStock[price][$key]",
					'value' => $warehouseNewprice,
					'id' => "c_p_$key",
					'onblur' => "price_check_cp($key)",
					'label' => false,
					'style' => 'width:48px;',
					'readonly' => 'readonly'
					)
				);
		}
		
		if($isMainDomain){
			echo $this->Form->input(null,array(
					'type' => 'text',
					'name' => "WarehouseStock[new_rcp][$key]",
					'value' => $retail_cost_price,
					'id' => "cp_$key",
					'onblur' => "price_check_cp_new($key)",
					'placeholder'=> '--',
					'label' => false,
					'style' => 'width:48px;margin-top: 4px;color: blue;'
					)
				);
		}else{
		   echo $this->Form->input(null,array(
					'type' => 'text',
					'name' => "WarehouseStock[new_rcp][$key]",
					'value' => $retail_cost_price,
					'id' => "cp_$key",
					'onblur' => "price_check_cp_new($key)",
					'placeholder'=> '--',
					'label' => false,
					'style' => 'width:48px;margin-top: 4px;color: blue;',
					'readonly' => 'readonly'
					)
				);
		}
			
			//$path = dirname(__FILE__);
			//$isboloRam = strpos($path,"boloram");
			//if($isboloRam != false){
			//	echo $this->Form->input(null,array(
			//		'type' => 'text',
			//		'name' => "WarehouseStock[new_rcp][$key]",
			//		'value' => $retail_cost_price,
			//		'id' => "cp_$key",
			//		//'onblur' => "price_check_cp_new($key)",
			//		'placeholder'=> '--',
			//		'label' => false,
			//		'style' => 'width:48px;margin-top: 4px;color: blue;'
			//		)
			//	);
			//}else{
			//	echo $this->Form->input(null,array(
			//		'type' => 'text',
			//		'name' => "WarehouseStock[new_rcp][$key]",
			//		'value' => $retail_cost_price,
			//		'id' => "cp_$key",
			//		'onblur' => "price_check_cp_new($key)",
			//		'placeholder'=> '--',
			//		'label' => false,
			//		'style' => 'width:48px;margin-top: 4px;color: blue;'
			//		)
			//	);
			//}
			
			echo $this->Form->input(null,array(
					'type' => 'hidden',
					'name' => "WarehouseStock[product_id][$key]",
					'value' => $warehouseStock->id
				     )
				);
			?>
		</td>
		<td><?php echo "<span id = 'org_s_p_$key' style='position: relative;top: 7px;'>".$warehouseNewSellingPrice."</span><br/><br/><br/>";
			echo "<span id = 'org_sp_$key' style= 'color: blue;'>".$rsp."</span>";?>&nbsp;</td>
		<td><?php
		echo "<div style='display: flex;'>";
		if($isMainDomain){
		  echo $this->Form->input(null,array(
					'type' => 'text',
					'name' => "WarehouseStock[new_selling_price][$key]",
					'value' => $warehouseNewSellingPrice,
					'id' => "s_p_$key",
					'onblur' => "price_check_sp($key)",
					'label' => false,
					'style' => 'width:48px;'
					)
				);
		}else{
		   echo $this->Form->input(null,array(
					'type' => 'text',
					'name' => "WarehouseStock[new_selling_price][$key]",
					'value' => $warehouseNewSellingPrice,
					'id' => "s_p_$key",
					'onblur' => "price_check_sp($key)",
					'label' => false,
					'style' => 'width:48px;',
					'readonly' => 'readonly'
					)
				);
		}
			
			echo "<div title='Without vat price' style='margin-left: -11px;'><b>*</b></div>";
			echo "</div>";
			if($isMainDomain){
			   echo $this->Form->input(null,array(
					'type' => 'text',
					'name' => "WarehouseStock[new_rsp][$key]",
					'value' => $retail_selling_price,
					'id' => "sp_$key",
					'onblur' => "price_check_sp_new($key)",
					'placeholder'=> '--',
					'label' => false,
					'style' => 'width:48px;margin-top: 4px;color: blue;'
					)
				);
			}else{
			   echo $this->Form->input(null,array(
					'type' => 'text',
					'name' => "WarehouseStock[new_rsp][$key]",
					'value' => $retail_selling_price,
					'id' => "sp_$key",
					'onblur' => "price_check_sp_new($key)",
					'placeholder'=> '--',
					'label' => false,
					'style' => 'width:48px;margin-top: 4px;color: blue;',
					'readonly' => 'readonly',
					)
				);
			}
			
			
			//$path = dirname(__FILE__);
			//$isboloRam = strpos($path,"boloram");
			//if($isboloRam != false){
			//	echo $this->Form->input(null,array(
			//		'type' => 'text',
			//		'name' => "WarehouseStock[new_rsp][$key]",
			//		'value' => $retail_selling_price,
			//		'placeholder'=> '--',
			//		'label' => false,
			//		'style' => 'width:48px;margin-top: 4px;color: blue;'
			//		)
			//	);
			//}else{
			// echo "<div style='display: flex;'>";
			//	echo $this->Form->input(null,array(
			//		'type' => 'text',
			//		'name' => "WarehouseStock[new_rsp][$key]",
			//		'value' => $retail_selling_price,
			//		'id' => "sp_$key",
			//		'onblur' => "price_check_sp_new($key)",
			//		'placeholder'=> '--',
			//		'label' => false,
			//		'style' => 'width:48px;margin-top: 4px;color: blue;'
			//		)
			//	);
			//	echo "<div title='With vat price' style='margin-left: -11px;'><b>**</b></div>";
			//	echo "</div>";
			//}
			
			?>
		</td>
		<td><?php
			echo $this->Form->input(null,array(
					'type' => 'text',
					'name' => "WarehouseStock[quantity][$key]",
					'value' => $warehouseQuantity,
					'label' => false,
					'style' => 'width:48px;',
					'readonly' => false
					)
				);
			?>
	<div>	</div></td>
		<td style='width:100px;'><?php
			echo $this->Form->radio("WarehouseStock[in_out][$key]",
				array('1' => 'In','0'=>'Out'),
				array(
					'before' => '',
					'after' => '',
					'type' => 'radio',
					'name' => "WarehouseStock[in_out][{$key}]",
					'label' => false,
					'separator' => "<span style='width:10px'>&nbsp;&nbsp;</span><br />",
					'between' => '',
					'hiddenField' => false,
					'div' => false,
					'legend' => false,
					'value' => $inOutValue
				)
			);
			?>
		</td>
		<td><?php echo $this->Form->input(null,array(
					'type' => 'text',
					'name' => "WarehouseStock[remarks][$key]",
					'value' => $warehouseRemarks,
					'label' => false,
					'style' => 'width:48px;',
					'readonly' => false
					)
				); ?>
		</td>
	</tr>
<?php endforeach; ?>
	<?php echo $this->Form->input(null,array('type' => 'hidden','value' => $currentPageNumber,'name' => 'current_page'));?>
	</tbody>
	</table>
	<span class="submit">
		<table>
			<td style='width:30px;'><input type="submit" name='add_2_basket' value="Add to basket"/></td>
			<td style='width:30px;'><input type="submit" name='check_out' value="Check out" onclick="return inputReference();"/></td>
			<td style='width:5500px;'><input type="submit" name='Dispatch' value="Update Stock" onclick="return inputReference();"/></td>
			<td style='width:30px;'><input type="submit" name='clear_basket' value="Clear basket"/></td>
		</table>
		
		
		 
	</span>
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
	<ul>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div>
<input type='hidden' name='url_category' id='url_category' value=''/>
<script type="text/javascript">
     $(document).ready(function(){
         var divLoc = $('#idVal').offset();
         $('html, body').animate({scrollTop: divLoc.top-130}, "slow");
     });
</script>



<script type="text/javascript">
<?php
	foreach ($warehouseStocks as $warehouseStock):
		$string = preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($warehouseStock->product));
		if(empty($string)){
			$string = $warehouseStock->product;
			//htmlspecialchars($str, ENT_NOQUOTES, "UTF-8")
		}
		echo "jQuery('#tooltip_{$warehouseStock->id}').tooltip({content:\"".htmlspecialchars($string)."\",track:true});";
    endforeach;
?>
    </script>

<input type='hidden' name='url_category' id='url_category' value=''/>
<script type="text/javascript">
 function update_hidden(){
   
  //var singleValues = $( "#single" ).val();
  var multipleValues = $( "#category_dropdown" ).val() || [];
    alert(multipleValues);
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
					
	/*filter: function(x) {
                            return $.map(x, function(item) {
                                return {value: item.product};
                            });
                        },*/
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
    /*empty: [
      '<div class="empty-message">',
        'unable to find matching product',
      '</div>'
    ].join('\n'),*/
    suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:400px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{product_code}}</a></strong></div>'),
	header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------<?php echo ADMIN_DOMAIN;?>---------</b></div>"),
  }
});
</script>

<script>
	$('#cp_2').change(function(){
		// var cp = $('#cp_2').val();
		// if (cp <= 0) {
		//	 alert('Cost Price Should Be More Then Zero');
		//	 $( "#cp_2" ).val('');
		//	 $( "#cp_2" ).focus();
		//	 return;
		// }
	});
	$('#cp_1').change(function(){
		// var cp = $('#cp_1').val();
		// if (cp <= 0) {
		//	 alert('Cost Price Should Be More Then Zero');
		//	 $( "#cp_1" ).val('');
		//	 $( "#cp_2" ).focus();
		//	 return;
		// }
	});
	function price_check_cp(key) {
        var cp = $('#c_p_'+key).val();
		 if (cp <= 0) {
			 alert('Cost Price Should Be More Then Zero');
			 var org_cp = $('#org_c_p_'+key ).text();
			 $( '#c_p_'+key ).val(org_cp);
			 $( '#org_c_p_'+key ).focus();
			 return;
		 }
    }
	function price_check_cp_new(key) {
        var cp = $('#cp_'+key).val();
		 if (cp <= 0) {
			 alert('Cost Price Should Be More Then Zero');
			 var org_cp = $('#org_cp_'+key ).text();
			 $( '#cp_'+key).val(org_cp);
			 $('#org_cp_'+key ).focus();
			 return;
		 }
    }
	
	function price_check_sp_new(key) {
        var sp = $('#sp_'+key).val();
		 if (sp <= 0) {
			 alert('Selling Price Should Be More Then Zero');
			 var org_sp = $('#org_sp_'+key ).text();
			 $( '#sp_'+key).val(org_sp);
			 $('#org_sp_'+key ).focus();
			 return;
		 }
    }
	
	function price_check_sp(key) {
        var sp = $('#s_p_'+key).val();
		 if (sp <= 0) {
			 alert('Selling Price Should Be More Then Zero');
			 var org_sp = $('#org_s_p_'+key ).text();
			 $( '#s_p_'+key ).val(org_sp);
			 $( '#org_s_p_'+key ).focus();
			 return;
		 }
    }
</script>
<script>
	function inputReference(){
		if(document.getElementById("reference_number").value == null || document.getElementById("reference_number").value == ""){
			alert("Please input the reference number!");
			return false;
		}else{
		 $.blockUI({ message: 'Just a moment...' });
		}
	}
	
	function submitForm(){
		document.getElementById("display_form").submit();
	}
</script>
<script>
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>