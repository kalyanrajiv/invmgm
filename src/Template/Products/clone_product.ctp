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
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php $currency = Configure::read('CURRENCY_TYPE');
?>
<div class="products form">
	<input type="hidden" id='vat' value="<?=$vat?>" />
<?php
$maximumDiscountedValue = "";
echo $this->Form->create($productEntity,array('enctype' => 'multipart/form-data')); ?>
	<fieldset>
		<legend><?php echo __('Clone Product'); ?></legend>
	<?php
		echo $this->Form->input('product');
		echo $this->Form->input('description');
		//echo $this->Form->input('quantity', array('type'=>'text','readonly' => 'readonly'));
		echo $this->Form->input('category_id');
		$radioAttributes = array( 'legend'=>false,
        'after'=>'</li>',
        'separator'=>'</li><li style="width: 33px;float: left;list-style: none">','onClick' => 'showhide_info();');
		echo "<table>
				<tr>
					<td>".$this->Form->input('cost_price', array('type'=>'text', 'style' => "width: 70px;"))."</td>
					<td>".$this->Form->input('null', array('type'=>'text', 'id' => 'warehouse_price_without_vat', 'label' => 'SP (Excl VAT)', 'style' => "width: 100px;"))."</td>
					<td>".$this->Form->input('selling_price', array('type'=>'text', 'style' => "width: 88px;", 'id' => 'ProductSellingPrice','label' => 'SP (Incls VAT)'))."</td>
					<td style='width: 1px;'>Enable Discnt:</td>
					<td style = 'width: 117px;'><li style='width: 35px;float: left;list-style: none'>".$this->Form->radio('discount_status',$yesNoOptions,$radioAttributes)."</li></td>
					<td>
						<div id='discount_div'>
							<table>
								<tr>
									<td>".$this->Form->input('null',array('type' => 'text', 'id' => 'min_warehouse_amount', 'label' => 'Min Sale Amount', 'style' => 'width: 100px;'))."</td>
									<td>".$this->Form->input('null',array('options' => $discountOptions, 'id' => 'warehouse_disc_dropdown', 'label' => 'Discount'))."</td>
									<td>".$this->Form->input('discount',array('type' => 'text', 'id' => 'net_warehouse_disc', 'label' => 'Final Discount', 'style' => "width: 77px;"))."</td>
									<td>".$this->Form->input('special_offer',array('type' => 'checkbox', 'id' => 'warehouse_offer'))."</td>
								</tr>
							</table>
						</div>
					</td>
				</	>
			</table>";
		$radioAttributes = array( 'legend'=>false,
        'after'=>'</li>',
        'separator'=>'</li><li style="width: 33px;float: left;list-style: none">','onClick' => 'showhide_retail_info();');
		echo "<table>
				<tr>
					<td>".$this->Form->input('retail_cost_price', array('type'=>'text', 'style' => "width: 99px;",'label' => 'Ret Cost Price'))."</td>
					<td style='font-weight: normal;'>".$this->Form->input('retail_selling_price', array('type'=>'text', 'id' => 'ProductRetailSellingPrice','style' => "width: 99px;font-weight: normal;",'label' => 'Ret SP (Incls VAT)'))."</td>
					<td>".$this->Form->input('null', array('type'=>'text', 'id' => 'retail_price_without_vat', 'label' => 'SP (Excl VAT)', 'style' => "width: 81px;"))."</td>
					<td style='width: 1px;'>Enable Retail Discnt:</td>
					<td style = 'width: 117px;'><li style='width: 35px;float: left;list-style: none'>".$this->Form->radio('rt_discount_status',$yesNoOptions,$radioAttributes)."</li></td>
					<td>
						<div id='rt_discount_div'>
							<table>
								<tr>
									<td>".$this->Form->input('null',array('type' => 'text', 'id' => 'min_retail_amount', 'label' => 'Min Sale Amount', 'style' => 'width: 100px;'))."</td>
									<td>".$this->Form->input('null',array('options' => $discountOptions, 'id' => 'retail_disc_dropdown', 'label' => 'Discount'))."</td>
									<td>".$this->Form->input('retail_discount',array('type' => 'text', 'id' => 'net_retail_disc', 'label' => 'Final Discount', 'style' => "width: 80px;"))."</td>
									<td>".$this->Form->input('retail_special_offer',array('type' => 'checkbox', 'id' => 'retail_offer'))."</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
			</table>";
			$url = $this->Url->build(array('action'=>'get-product-models'));
		echo $this->Form->input('brand_id',array('rel'=>$url));
		
		echo $this->Form->input('model_id',array(
												 'name'=>'model_id',
												 'options' => $mobileModels,
												 ));
		echo $this->Form->input('additional_model', array('type' => 'hidden', 'id' => 'additional_model_id'));
		if(count($mobileModels) >= 1 && !array_key_exists('-1',$mobileModels)){
			$chunks = array_chunk($mobileModels,6,true);
			if(count($chunks)){
				$colmnStr = "";
				foreach($chunks as $c => $chunk){
					$colmnStr.="<tr>";
					foreach($chunk as $ch => $condition){
						$modelName = strtolower($condition);
						if(!empty($this->request['data']) && array_key_exists('additional_model_id',$this->request['data'])){
							$existingModels = explode(',',$this->request['data']['additional_model_id']);
							if(in_array($ch,$existingModels)){
								$checked = "checked";
							}else{
								$checked = '';
							}
						}else{
							$checked = '';
						}
						$colmnStr.="<td>".$this->Form->input($modelName, array('type' => 'checkbox',
						  'name'=>'Product[additional_model_id][]',
						  'label' => array('style' => "color: blue;"),
						  'value' => $ch,
						  'hiddenField' => false,
						  'checked' => $checked
						  ))."</td>";
					}
					$colmnStr.="</tr>";        
				}
				echo $tblHTML = <<<TBL_HMTL
					<table id = 'additional_model'>
						<tr><td colspan='8'><h4>Additional Model</h4><hr/></td></tr>
						$colmnStr
						</tr>
					</table>
TBL_HMTL;
			}
		}
		echo $this->Form->input('manufacturing_date');
		//echo $this->Form->input('sku');
		echo $this->Form->input('country_make');
		echo $this->Form->input('product_code');
		echo $this->Form->input('weight', array('type'=>'text'));
		echo $this->Form->input('color', array('options'=>$colourOptions));
		echo $this->Form->input('featured',array('options' => $featuredOptions,'default' => 0));
		//$radioAttributes = array('legend' => 'Enable Discount','onClick' => 'showhide_info();');
		//echo $this->Form->radio('discount_status',$yesNoOptions,$radioAttributes);
		//echo "<div id='discount_div'>".$this->Form->input('discount',array('options' => $discountOptions))."</div>";
		//$radioAttributes = array('legend' => 'Enable Retail Discount','onClick' => 'showhide_retail_info();');
		//echo $this->Form->radio('rt_discount_status',$yesNoOptions,$radioAttributes);
		//echo "<div id='rt_discount_div'>";
			//echo $this->Form->input('retail_discount',array('options' => $discountOptions));
		//echo "</div>";
		$sellingPrice = $this->request['data']['selling_price'];
			if($this->request['data']['discount_status']){
				$arky = array_keys($discountOptions);
				$maximumPercentageDiscount = (int)end($arky);
				$maximumDiscountedValue = $this->request['data']['selling_price'] * ($maximumPercentageDiscount / 100);
			}
			$percentageDicount = (float)$this->request['data']['discount'];
			$discountGiven = $sellingPrice * ($percentageDicount / 100);
			#echo "Maximum Allowed Discount: <span id='max_discount' style='font-weight:bold;'>".$this->Number->currency($maximumDiscountedValue,'GBP')."</span><br/>Discount Given: <input style='width:50px;' type='text' name='data[Product][discount_value]' id='ProductDiscountValue' value='".$this->Number->currency($discountGiven,'GBP')."'/>";
		echo $this->Form->input('image', array('between' => '<br />','type' => 'file'));
		echo $this->Form->input('image_dir', array('type' => 'hidden'));
        
	?>
		
	<?php
		echo $this->Form->input('manufacturer');
		echo $this->Form->input('location', array('type'=>'text'));
		echo $this->Form->input('stock_level', array('type'=>'text'));
		#echo $this->Form->input('dead_stock_level', array('type'=>'text'));
		echo $this->Form->input('status',array('options' => $statusOptions));
	?>
	</fieldset>
<?php
echo $this->Form->Submit(__('Submit'));
echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Product.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('Product.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Products'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Categories'), array('controller' => 'categories', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Category'), array('controller' => 'categories', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
		<li><?php # echo $this->Html->link(__('List Images'), array('controller' => 'images', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Image'), array('controller' => 'images', 'action' => 'add')); ?> </li>
		<li><?php # echo $this->Html->link(__('List Reorder Levels'), array('controller' => 'reorder_levels', 'action' => 'index')); ?> </li>
		<li><?php # echo $this->Html->link(__('New Reorder Level'), array('controller' => 'reorder_levels', 'action' => 'add')); ?> </li>
	</ul>
</div>
<?php
$res = array_keys($discountOptions);
?>
<script>
	var maximumPercentageDiscount = parseInt('<?php echo end($res);?>');
	var maximumDiscountedValue = 0.0;
	 
    function showhide_info(){
		if(document.getElementById('discount-status-1').checked) {
			//document.getElementById('discount_div').style.display = 'block';
			$('#min_warehouse_amount').attr("readonly", false);
			$('#min_warehouse_amount').addClass('greenborder');
			$('#min_warehouse_amount').removeClass('redborder');
			$('#warehouse_disc_dropdown').attr("disabled", false);
			$('#warehouse_disc_dropdown').addClass('greenborder');
			$('#warehouse_disc_dropdown').removeClass('redborder');
			$('#net_warehouse_disc').attr("readonly", false);
			$('#net_warehouse_disc').addClass('greenborder');
			$('#net_warehouse_disc').removeClass('redborder');
			$('#warehouse_offer').attr('disabled',false);
		}else{
			//document.getElementById('discount_div').style.display = 'none';
			$('#min_warehouse_amount').attr("readonly", true);
			$('#min_warehouse_amount').addClass('redborder');
			$('#min_warehouse_amount').removeClass('greenborder');
			$('#warehouse_disc_dropdown').attr("disabled", true);
			$('#warehouse_disc_dropdown').addClass('redborder');
			$('#warehouse_disc_dropdown').removeClass('greenborder');
			$('#net_warehouse_disc').attr("readonly", true);
			$('#net_warehouse_disc').addClass('redborder');
			$('#net_warehouse_disc').removeClass('greenborder');
			$('#warehouse_offer').attr('disabled',true);
			$('#warehouse_offer').attr('checked',false);
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
	
	function show_min_amount() {
        var discount = $('#net_warehouse_disc').val();
		var salePrice = $('#warehouse_price_without_vat').val();
		if (isNaN(discount) == false && discount != '' && isNaN(salePrice) == false && salePrice != '') {
			var discValue = salePrice*discount/100;
			var minSaleAmount = parseFloat(salePrice-discValue);
			$('#min_warehouse_amount').val(minSaleAmount.toFixed(2));
		}
		
		var rdiscount = $('#net_retail_disc').val();
		var rsalePrice = $('#ProductRetailSellingPrice').val();
		if (isNaN(rdiscount) == false && rdiscount != '' && isNaN(rsalePrice) == false && rsalePrice != '') {
			var rdiscValue = rsalePrice*rdiscount/100;
			var rminSaleAmount = parseFloat(rsalePrice-rdiscValue);
			$('#min_retail_amount').val(rminSaleAmount.toFixed(2));
		}
    }
	
	function change_discount() {
        var minSaleAmnt = $('#min_warehouse_amount').val();
		var salePrice = $('#warehouse_price_without_vat').val();
		if (isNaN(minSaleAmnt) == false && minSaleAmnt != '' && isNaN(salePrice) == false && salePrice != '') {
			var discVal = salePrice-minSaleAmnt;
			var netWHdisc = parseFloat(discVal/salePrice*100);
			$('#net_warehouse_disc').val(netWHdisc.toFixed(2));
		}
		
		var rminSaleAmnt = $('#min_retail_amount').val();
		var rsalePrice = $('#ProductRetailSellingPrice').val();
		if (isNaN(rminSaleAmnt) == false && rminSaleAmnt != '' && isNaN(rsalePrice) == false && rsalePrice != '') {
			var rdiscVal = rsalePrice-rminSaleAmnt;
			var netRetDisc = parseFloat(rdiscVal/rsalePrice*100);
			$('#net_retail_disc').val(netRetDisc.toFixed(2));
		}
    }
	
	$('#warehouse_disc_dropdown').change(function(){
		var discount1 = $(this).val();
		var salePrice1 = $('#ProductSellingPrice').val();
		if (isNaN(discount1) == false && discount1 != '' && isNaN(salePrice1) == false && salePrice1 != '') {
			var discValue1 = salePrice1*discount1/100;
			var minSaleAmount1 = parseFloat(salePrice1-discValue1);
			$('#min_warehouse_amount').val(minSaleAmount1.toFixed(2));
			$('#net_warehouse_disc').val(discount1);
		}
	});
	
	$('#retail_disc_dropdown').change(function(){
		var discount2 = $(this).val();
		var salePrice2 = $('#ProductRetailSellingPrice').val();
		if (isNaN(discount2) == false && discount2 != '' && isNaN(salePrice2) == false && salePrice2 != '') {
			var discValue2 = salePrice2*discount2/100;
			var minSaleAmount2 = parseFloat(salePrice2-discValue2);
			$('#min_retail_amount').val(minSaleAmount2.toFixed(2));
			$('#net_retail_disc').val(discount2);
		}
	});
	
	$('#net_warehouse_disc').keyup(function(){
		show_min_amount();
	});
	
	$('#net_retail_disc').keyup(function(){
		show_min_amount();
	});
	
	$('#min_warehouse_amount').keyup(function(){
		change_discount();
	});
	
	$('#min_retail_amount').keyup(function(){
		change_discount();
	});
	
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
	
	$('#ProductSellingPrice').keyup(function(){
		priceWithoutVAT();
		show_min_amount();
	});
	
	$('#ProductRetailSellingPrice').keyup(function(){
		priceWithoutVAT();
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
	
	showhide_info();
	showhide_retail_info();
	show_min_amount();
	priceWithoutVAT();
	$( "#ProductSellingPrice" ).blur(function() { //change
	  maximumDiscountedValue = $(this).val() * (maximumPercentageDiscount / 100);// = 25 / 25
	  $('#max_discount').html('£ '+maximumDiscountedValue);
	  //$('#max_discount').html().replace('£','&amp;pound;'); //jquery
	});
</script>
<script>
	$('#brand-id').change(function(){
		var id = $(this).val();
		var targetUrl = $(this).attr('rel') + '?id=' + id;
		var additionalModelUrl = $(this).attr('rel') + '?id=' + id + '&model=additional';
		$.blockUI({ message: 'Just a moment...' });
		$.ajaxSetup({
		url: targetUrl,
			success: function(result){
				$.unblockUI();
			$('#model-id').empty();
			$('#model-id').append(result);
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