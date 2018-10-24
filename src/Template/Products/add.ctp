<style>
	/*form .required {font-weight: normal;}*/
	.greenborder{
		border: green solid;
	}
	.redborder{
		border: red solid;
	}
</style>
<?php
/**
  * @var \App\View\AppView $this
  */
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
<div class="products form large-9 medium-8 columns content">
	<input type="hidden" id='vat' value="<?=$vat?>" />
    <?= $this->Form->create($product,array('enctype' => 'multipart/form-data')) ?>
    <fieldset>
        <legend><?= __('Add Product') ?></legend>
        <?php
        $radioAttributes = array( 'legend'=>false,
        'after'=>'</li>',
        'separator'=>'</li><li style="width: 37px;float: left;list-style: none">','value' => 0,'onClick' => 'showhide_info();');
        echo "<div id='remote'>" ;
        echo $this->Form->input('product',array('id'=>"ProductProduct",'class'=>"typeahead","name"=>"Product[product]",'style'=>"position: relative;vertical-align: top;background-color: transparent;width: 952px;"));
        echo "</div>";
		echo $this->Form->input('description',array('id'=>'ProductDescription','name'=>'Product[description]')); 
		echo $this->Form->input('quantity', array('type'=>'text','type' => 'hidden',
												  'id' => 'ProductQuantity','name' => 'Product[quantity]',
												  'value' => 0));
		echo $this->Form->input('category_id',array('id'=>'ProductCategoryId','name'=>'Product[category_id]'));
		echo "<table>
				<tr>
					<td>".$this->Form->input('cost_price', array('type'=>'text', 'style' => "width: 55px;",'id'=>'ProductCostPrice','name'=>'Product[cost_price]'))."</td>
					<td>".$this->Form->input('null', array('type'=>'text', 'id' => 'warehouse_price_without_vat', 'label' => 'SP (Excl VAT)', 'style' => "width: 97px;",'name'=>'Product[null]'))."</td>
					<td>".$this->Form->input('selling_price', array('type'=>'text', 'style' => "width: 55px;", 'label' => 'SP (Incls VAT)','id'=>'ProductSellingPrice','name'=>'Product[selling_price]'))."</td>
					<td style='width: 1px;'>Enable Discnt:</td>
					<td style = 'width: 122px;'><li style='width: 35px;float: left;list-style: none'>".$this->Form->radio('discount_status',$yesNoOptions,$radioAttributes)."</li></td>
					<td>
						<div id='discount_div'>
							<table>
								<tr>
									<td>".$this->Form->input('Min Sale Amount',array('type'=>'text', 'id' => 'ProductMinimumSellingAmount','name'=>'Product[Min Sale Amount]'))."</td>
									<td>".$this->Form->input('discount',array('id' => 'ProductDiscount','name'=>'Product[discount]','options' => $discountOptions,'onChange' => 'change_selling_amount();'))."</td>
									<td>".$this->Form->input('Final Discount',array('id' =>'netdiscount','name'=>'Product[Final Discount]','type'=>'text'))."</td>
									<td>".$this->Form->input('special_offer',array('type' => 'checkbox', 'id' => 'warehouse_offer','name'=>'Product[special_offer]'))."</td>
								</tr>
							</table>
						</div>
					</td>	
				</tr>	
			</table>";
			//------------------------------------
			
				$radioAttributes = array( 'legend'=>false,
				'after'=>'</li>',
				'separator'=>'</li><li style="width: 33px;float: left;list-style: none">','value' => 0,'onClick' => 'showhide_retail_info();');
				echo "<table>
						<tr>
							<td>".$this->Form->input('retail_cost_price', array('type'=>'text', 'style' => "width: 99px;",'label' => 'Ret Cost Price','name'=>'Product[retail_cost_price]','id'=>'ProductRetailCostPrice'))."</td>
							<td>".$this->Form->input('retail_selling_price', array('type'=>'text', 'style' => "width: 99px;",'label' => 'Ret SP (Incls VAT)','id'=>'ProductRetailSellingPrice','name'=>'Product[retail_selling_price]'))."</td>
							<td>".$this->Form->input('null', array('type'=>'text', 'id' => 'retail_price_without_vat', 'label' => 'SP (Excl VAT)', 'style' => "width: 70px;",'name'=>'Product[null]'))."</td>
							<td style='width: 1px;'>Enable Retail Discnt:</td>
							<td style = 'width: 117px;'><li style='width: 35px;float: left;list-style: none'>".$this->Form->radio('rt_discount_status',$yesNoOptions,$radioAttributes,array('id'=>'ProductRtDiscountStatus1','name'=>'Product[rt_discount_status]'))."</li></td>
							<td>
								<div id='rt_discount_div'>
									<table>
										<tr>
											<td>".$this->Form->input('null',array('type' => 'text', 'id' => 'min_retail_amount', 'label' => 'Min Sale Amount', 'style' => 'width: 100px;','name'=>'Product[null]'))."</td>
											<td>".$this->Form->input('null',array('options' => $discountOptions, 'id' => 'retail_disc_dropdown', 'label' => 'Discount','name'=>'Product[null]'))."</td>
											<td>".$this->Form->input('retail_discount',array('type' => 'text', 'id' => 'net_retail_disc','name'=>'Product[retail_discount]', 'label' => 'Final Discount', 'style' => "width: 80px;"))."</td>
											<td>".$this->Form->input('retail_special_offer',array('type' => 'checkbox', 'id' => 'retail_offer','name'=>'Product[retail_special_offer]'))."</td>
										</tr>
									</table>
								</div>
							</td>
						</tr>
					</table>";
				
			
			
			//------------------------------------
		/*echo"<div style='width: 183px; display: inline-block;' class='input text required'>".$this->Form->input('retail_cost_price', array('type'=>'text'))."</div>";
		echo"<div style='width: 183px; display: inline-block;' class='input text required'>".$this->Form->input('retail_selling_price', array('type'=>'text'))."</div>";
		echo "<div style='display: inline-block; width: 57px;'>Enable Retail Discount</div>"; 
		$radioAttributes = array('legend' => false,'value' => 0, 'onClick' => 'showhide_retail_info();');
		echo "<div style='width: 183px; display: inline-flex;'>".$this->Form->radio('rt_discount_status',$yesNoOptions,$radioAttributes)."</div>";
		echo "<div id='rt_discount_div' style='display:inline-block;width: 102px;'>";
		echo $this->Form->input('retail_discount',array('options' => $discountOptions,'onChange' => 'change_retail_amount();'));
		echo "<div>".$this->Form->input('minimum selling amount',array('id' =>'retail_amount','type'=>'text'))."</div>";
		echo "<div>".$this->Form->input('net discount',array('id' =>'percentage','type'=>'text'))."</div>";
		echo "</div>";*/
		$url = $this->Url->build(array('action'=>'get-product-models'));
		echo $this->Form->input('brand_id',array('empty' => '--choose brand--','id'=>'ProductBrandId','name'=>'Product[brand_id]','rel'=>$url));
		
		echo $this->Form->input('model_id',array('empty' => '--choose model--','id'=>'ProductModelId','name'=>'Product[model_id]'));
		echo $this->Form->input('additional_model', array('type' => 'hidden', 'id' => 'additional_model_id'));
		echo $this->Form->input('manufacturing_date',array('id'=>'ProductManufacturingDateMonth','name'=>'Product[manufacturing_date]'));	
		//echo $this->Form->input('sku');
		echo $this->Form->input('country_make',array('id'=>'ProductCountryMake','name'=>'Product[country_make]'));
		echo $this->Form->input('product_code',array('id'=>'ProductProductCode','name'=>'Product[product_code]'));
		echo $this->Form->input('weight', array('type'=>'text','id'=>'ProductWeight','name'=>'Product[weight]'));
		echo $this->Form->input('color', array('options' => $colourOptions,'empty' => '--color--','name'=>'Product[color]','id'=>'ProductColor'));
		//echo '<div class="input number required">';
		//echo $this->Form->label('Product.featured', 'Featured',array('For' => 'ProductFeatured'));
		//echo $this->Form->select('featured',$featuredOptions,array('empty' => false));
		echo $this->Form->input('featured',array('options' => $featuredOptions,'default' => 0,'id'=>'ProductFeatured','name'=>'Product[featured]'));
		
		
		echo $this->Form->input('Product.image', array('between' => '<br />','type' => 'file','id'=>'ProductImage'));
		echo $this->Form->input('Product.image_dir', array('type' => 'hidden','name'=>'Product[image]'));
		echo $this->Form->input('manufacturer',array('id'=>'ProductManufacturer','name'=>'Product[manufacturer]'));
		echo $this->Form->input('location', array('type'=>'text','name'=>'Product[location]','id'=>'ProductLocation'));
		echo $this->Form->input('stock_level', array('type'=>'text','id'=>'ProductStockLevel','name'=>'Product[stock_level]'));		
		#echo $this->Form->input('dead_stock_level', array('type'=>'text'));
		echo $this->Form->input('status',array('options' => $statusOptions,'id'=>'ProductStatus','name'=>'Product[status]'));
		//echo $this->Form->label('Product.status', 'Status',array('required' => true));
        ?>
    </fieldset>
    <?= $this->Form->Submit(__('Submit'),array('name'=>'submit')) ?>
    <?= $this->Form->end() ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Products'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Categories'), array('controller' => 'categories', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Category'), array('controller' => 'categories', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
	
	$('#netdiscount').keyup(function(){
		show_min_amount();
	});
	
	$('input:radio[name=discount_status]').click(function(){
		showhide_info();
	});
	
	$('input:radio[name=rt_discount_status]').click(function(){
		showhide_retail_info();
	});
	
    function showhide_info(){
		if(document.getElementById('discount-status-1').checked) {
			//document.getElementById('discount_div').style.display = 'block';
			//document.getElementById('selling_amount_div').style.display = 'block';
			//document.getElementById('net_discount').style.display = 'block';
			$('#ProductMinimumSellingAmount').attr("readonly", false);
			$('#ProductMinimumSellingAmount').addClass('greenborder');
			$('#ProductMinimumSellingAmount').removeClass('redborder');ProductDiscount
			$('#ProductDiscount').attr("disabled", false);
			$('#ProductDiscount').addClass('greenborder');
			$('#ProductDiscount').removeClass('redborder');
			$('#netdiscount').attr("readonly", false);
			$('#netdiscount').addClass('greenborder');
			$('#netdiscount').removeClass('redborder');
			$('#warehouse_offer').attr('disabled',false);
		}else{
			//document.getElementById('discount_div').style.display = 'none';
			//document.getElementById('selling_amount_div').style.display = 'none';
			//document.getElementById('net_discount').style.display = 'none';
			$('#ProductMinimumSellingAmount').attr("readonly", true);
			$('#ProductMinimumSellingAmount').addClass('redborder');
			$('#ProductMinimumSellingAmount').removeClass('greenborder');
			$('#ProductDiscount').attr("disabled", true);
			$('#ProductDiscount').addClass('redborder');
			$('#ProductDiscount').removeClass('greenborder');
			$('#netdiscount').addClass('redborder');
			$('#netdiscount').removeClass('greenborder');
			$('#netdiscount').attr("readonly", true);
			$('#warehouse_offer').attr('disabled',true);
			$('#warehouse_offer').attr('checked',false);
		}
    }
	function change_selling_amount() {
		//alert(val);
			var a = document.getElementById("ProductDiscount");
				if (a.options[a.selectedIndex].value != 0) {
					if (document.getElementById("ProductSellingPrice").value != '') {	
						var discount = a.options[a.selectedIndex].value;
						var selling_price = document.getElementById("ProductSellingPrice").value;
						var per_discount = (selling_price*discount)/100;
						var new_selling_price = selling_price-per_discount;
						var ProductMinimumSellingAmount = document.getElementById("ProductMinimumSellingAmount");
						var net = document.getElementById("netdiscount");
						net.value = discount;
						ProductMinimumSellingAmount.value = new_selling_price.toFixed(2);
					}else{
						alert("please enter selling price");
					}
				}    
		}
		function change_retail_amount() {
            var a = document.getElementById("ProductRetailDiscount");
				if (a.options[a.selectedIndex].value != 0) {
					if (document.getElementById("ProductRetailSellingPrice").value != '') {	
						var discount = a.options[a.selectedIndex].value;
						var selling_price = document.getElementById("ProductRetailSellingPrice").value;
						var per_discount = (selling_price*discount)/100;
						var new_selling_price = selling_price-per_discount;
						var ProductMinimumSellingAmount = document.getElementById("min_retail_amount");
					
						ProductMinimumSellingAmount.value = new_selling_price.toFixed(2);
						Perentage.value = discount+'%';
						
					}else{
						alert("please enter retail selling price");
					}
				}   
        }
		function show_percentage() {
            var selling_amount = document.getElementById("min_retail_amount").value;
			var orignal_selling_price = document.getElementById("ProductRetailSellingPrice").value;
			if (parseFloat(selling_amount) > parseFloat(orignal_selling_price)) {
                return false;
            }
			if (isNaN(selling_amount) == false && selling_amount != '' && isNaN(orignal_selling_price) == false && orignal_selling_price != '') {
                var ans = orignal_selling_price-selling_amount;//(orignal_selling_price*$selling_amount)/100;
				var ans1 = ans/orignal_selling_price*100;
				var Perentage = document.getElementById("net_retail_disc");
				Perentage.value = ans1.toFixed(2);
            }
        }
		
		function show_min_amount() {
			//alert("hi");
			var discount = $('#netdiscount').val();
			var salePrice = $('#ProductSellingPrice').val();
			var discValue = salePrice*discount/100;
			var minSaleAmount = parseFloat(salePrice-discValue);
			$('#ProductMinimumSellingAmount').val(minSaleAmount.toFixed(2));
			
			var rdiscount = $('#net_retail_disc').val();
			var rsalePrice = $('#ProductRetailSellingPrice').val();
			var rdiscValue = rsalePrice*rdiscount/100;
			var rminSaleAmount = parseFloat(rsalePrice-rdiscValue);
			$('#min_retail_amount').val(rminSaleAmount.toFixed(2));
		}
	
	
		function priceWithoutVAT() {
			//formula price = total*100/vat+100
			var a = $('#ProductSellingPrice').val()*100;
			var b = parseFloat($('#vat').val())+parseFloat(100);
		    var price_without_vat_WH = a/b;
			$('#warehouse_price_without_vat').val(price_without_vat_WH.toFixed(2));
			
			var c = $('#ProductRetailSellingPrice').val()*100;
			var d = parseFloat($('#vat').val())+parseFloat(100);
			var price_without_vat_retail = c/d;
			$('#retail_price_without_vat').val(price_without_vat_retail.toFixed(2));
		}
		
		function show_net_discount(){
			//alert("hi");
			var orignal =  document.getElementById("ProductSellingPrice").value;
			var selling_amount = document.getElementById("ProductMinimumSellingAmount").value;
			if (parseFloat(selling_amount) > parseFloat(orignal)) {
                return false;
            }
			if (isNaN(orignal) == false && orignal != '' && isNaN(selling_amount) == false && selling_amount != '') {
				var dis_val = orignal-selling_amount;
			
				var dis_per = dis_val/orignal*100;
				var a = document.getElementById("netdiscount");
				a.value = dis_per.toFixed(2);
			}
		}
		
    function showhide_retail_info() {
		if(document.getElementById('rt-discount-status-1').checked) {
			//document.getElementById('rt_discount_div').style.display = 'block';
			$('#min_retail_amount').attr("readonly", false);
			$('#min_retail_amount').addClass('greenborder');
			$('#min_retail_amount').removeClass('redborder');
			$('#retail_disc_dropdown').attr("disabled", false);
			$('#retail_disc_dropdown').addClass('greenborder');
			$('#retail_disc_dropdown').removeClass('redborder');
			$('#net_retail_disc').attr("readonly", false);
			$('#net_retail_disc').addClass('greenborder');
			$('#net_retail_disc').removeClass('redborder');
			$('#retail_offer').attr('disabled',false);
		}else{
			//document.getElementById('rt_discount_div').style.display = 'none';
			$('#min_retail_amount').attr("readonly", true);
			$('#min_retail_amount').addClass('redborder');
			$('#min_retail_amount').removeClass('greenborder');
			$('#retail_disc_dropdown').attr("disabled", true);
			$('#retail_disc_dropdown').addClass('redborder');
			$('#retail_disc_dropdown').removeClass('greenborder');
			$('#net_retail_disc').attr("readonly", true);
			$('#net_retail_disc').addClass('redborder');
			$('#net_retail_disc').removeClass('greenborder');
			$('#retail_offer').attr('disabled',true);
			$('#retail_offer').attr('checked',false);
		}
    }
	
	$('#min_retail_amount').keyup(function(){
		show_percentage();
	});
	$('#ProductMinimumSellingAmount').keyup(function(){
		show_net_discount();
	});
	
	$('#ProductSellingPrice').keyup(function(){
		priceWithoutVAT();
		show_min_amount();
	});

	$('#ProductRetailSellingPrice').keyup(function(){
		priceWithoutVAT();
		show_min_amount();
	});	
	
	$('#net_retail_disc').keyup(function(){
		show_min_amount();
	});
	
	
	$('#warehouse_price_without_vat').keyup(function(){
		if (isNaN($(this).val()) == false && $(this).val() != '') {
            var sellPrice = parseFloat($(this).val())+parseFloat($('#vat').val()*$(this).val()/100);
			$('#ProductSellingPrice').val(sellPrice.toFixed(2));
			show_min_amount();
        }
	});
	
	$('#retail_price_without_vat').keyup(function(){
		if (isNaN($(this).val()) == false && $(this).val() != '') {
			var retSellPrice = parseFloat($(this).val())+parseFloat($('#vat').val()*$(this).val()/100);
			$('#ProductRetailSellingPrice').val(retSellPrice.toFixed(2));
			show_min_amount();
		}
	});
	
	
	$('#retail_disc_dropdown').change(function(){
		var discount2 = $(this).val();
		var salePrice2 = $('#ProductRetailSellingPrice').val();
		var discValue2 = salePrice2*discount2/100;
		var minSaleAmount2 = parseFloat(salePrice2-discValue2);
		$('#min_retail_amount').val(minSaleAmount2.toFixed(2));
		$('#net_retail_disc').val(discount2);
	});
	
	$("#ProductCostPrice").keydown(function (event) {  
	  if (event.shiftKey == true) {event.preventDefault();}
	  if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		 (event.keyCode >= 96 && event.keyCode <= 105) ||
		 event.keyCode == 8 || event.keyCode == 9 ||
		 event.keyCode == 37 || event.keyCode == 39 ||
		 event.keyCode == 46 || event.keyCode == 183 || event.keyCode == 190 || event.keyCode == 110) {
		   ;
	  }else{
		event.preventDefault();
	  }
	});
	
	$("#ProductRetailCostPrice").keydown(function (event) {  
	  if (event.shiftKey == true) {event.preventDefault();}
	  if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		 (event.keyCode >= 96 && event.keyCode <= 105) ||
		 event.keyCode == 8 || event.keyCode == 9 ||
		 event.keyCode == 37 || event.keyCode == 39 ||
		 event.keyCode == 46 || event.keyCode == 183 || event.keyCode == 190 || event.keyCode == 110) {
		   ;
	  }else{
		event.preventDefault();
	  }
	});
	
	$("#warehouse_price_without_vat").keydown(function (event) {  
	  if (event.shiftKey == true) {event.preventDefault();}
	  if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		 (event.keyCode >= 96 && event.keyCode <= 105) ||
		 event.keyCode == 8 || event.keyCode == 9 ||
		 event.keyCode == 37 || event.keyCode == 39 ||
		 event.keyCode == 46 || event.keyCode == 183 || event.keyCode == 190 || event.keyCode == 110) {
		   ;
	  }else{
		event.preventDefault();
	  }
	});
	
	$("#retail_price_without_vat").keydown(function (event) {  
	  if (event.shiftKey == true) {event.preventDefault();}
	  if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		 (event.keyCode >= 96 && event.keyCode <= 105) ||
		 event.keyCode == 8 || event.keyCode == 9 ||
		 event.keyCode == 37 || event.keyCode == 39 ||
		 event.keyCode == 46 || event.keyCode == 183 || event.keyCode == 190 || event.keyCode == 110) {
		   ;
	  }else{
		event.preventDefault();
	  }
	});
	
	
	$("#ProductRetailSellingPrice").keydown(function (event) {  
	  if (event.shiftKey == true) {event.preventDefault();}
	  if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		 (event.keyCode >= 96 && event.keyCode <= 105) ||
		 event.keyCode == 8 || event.keyCode == 9 ||
		 event.keyCode == 37 || event.keyCode == 39 ||
		 event.keyCode == 46 || event.keyCode == 183 || event.keyCode == 190 || event.keyCode == 110) {
		   ;
	  }else{
		event.preventDefault();
	  }
	});
	
	$("#ProductSellingPrice").keydown(function (event) {  
	  if (event.shiftKey == true) {event.preventDefault();}
	  if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		 (event.keyCode >= 96 && event.keyCode <= 105) ||
		 event.keyCode == 8 || event.keyCode == 9 ||
		 event.keyCode == 37 || event.keyCode == 39 ||
		 event.keyCode == 46 || event.keyCode == 183 || event.keyCode == 190 || event.keyCode == 110) {
		   ;
	  }else{
		event.preventDefault();
	  }
	});
	
	$("#ProductMinimumSellingAmount").keydown(function (event) {  
	  if (event.shiftKey == true) {event.preventDefault();}
	  if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		 (event.keyCode >= 96 && event.keyCode <= 105) ||
		 event.keyCode == 8 || event.keyCode == 9 ||
		 event.keyCode == 37 || event.keyCode == 39 ||
		 event.keyCode == 46 || event.keyCode == 183 || event.keyCode == 190 || event.keyCode == 110) {
		   ;
	  }else{
		event.preventDefault();
	  }
	});
	
	$("#netdiscount").keydown(function (event) {  
	  if (event.shiftKey == true) {event.preventDefault();}
	  if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		 (event.keyCode >= 96 && event.keyCode <= 105) ||
		 event.keyCode == 8 || event.keyCode == 9 ||
		 event.keyCode == 37 || event.keyCode == 39 ||
		 event.keyCode == 46 || event.keyCode == 183 || event.keyCode == 190 || event.keyCode == 110) {
		   ;
	  }else{
		event.preventDefault();
	  }
	});
	
	$("#min_retail_amount").keydown(function (event) {  
	  if (event.shiftKey == true) {event.preventDefault();}
	  if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		 (event.keyCode >= 96 && event.keyCode <= 105) ||
		 event.keyCode == 8 || event.keyCode == 9 ||
		 event.keyCode == 37 || event.keyCode == 39 ||
		 event.keyCode == 46 || event.keyCode == 183 || event.keyCode == 190 || event.keyCode == 110) {
		   ;
	  }else{
		event.preventDefault();
	  }
	});
	
	$("#net_retail_disc").keydown(function (event) {  
	  if (event.shiftKey == true) {event.preventDefault();}
	  if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		 (event.keyCode >= 96 && event.keyCode <= 105) ||
		 event.keyCode == 8 || event.keyCode == 9 ||
		 event.keyCode == 37 || event.keyCode == 39 ||
		 event.keyCode == 46 || event.keyCode == 183 || event.keyCode == 190 || event.keyCode == 110) {
		   ;
	  }else{
		event.preventDefault();
	  }
	});
	      
	
showhide_info();
showhide_retail_info();
change_selling_amount();
</script>

<input type='hidden' name='url_category' id='url_category' value=''/>
<script type="text/javascript">
 function update_hidden(){
   
  //var singleValues = $( "#single" ).val();
  var multipleValues = $( "#category_dropdown" ).val() || [];
    //alert(multipleValues);
  $('#url_category').val(multipleValues.join( "," ));
   
 }
</script>
<script>
 var product_dataset = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  //prefetch: "/products/data",
  remote: {
    url: "/stock/admin-Data?category=%CID&search=%QUERY",
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
	footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------hpwaheguru.co.uk---------</b></div>"),
  }
});
</script>
<script>
	$('#ProductBrandId').change(function(){
		var id = $(this).val();
		var targetUrl = $(this).attr('rel') + '?id=' + id;
		var additionalModelUrl = $(this).attr('rel') + '?id=' + id + '&model=additional';
		$.blockUI({ message: 'Just a moment...' });
		$.ajaxSetup({
		url: targetUrl,
			success: function(result){
				$.unblockUI();
			$('#ProductModelId').empty();
			$('#ProductModelId').append(result);
			}
		});
		$.ajax();
		$.ajaxSetup({
		url: additionalModelUrl,
			success: function(response){
			console.log(response);
			//$('#existing_additional_model').empty();
			$('#additional_model').empty();
			$('#additional_model_id').after(response);
			}
		});
		$.ajax();
	});
</script>