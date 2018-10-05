<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Utility\Text;
use Cake\Routing\Router;
?>
<div class="kioskOrders index">
	<?php
	if(!empty($sites)){
		$sites[0] = "All";
	}
	if(!isset($selected_site_id)){
		$selected_site_id = 0;
	}
	if(!isset($ex_kiosk_id)){
		$ex_kiosk_id = -1;
	}
	//pr($this->request);die;
	$siteUrl = Configure::read('SITE_BASE_URL');
	$value = '';
	if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
	}
	
	$from_date = $to_date = "";
	
	if(!empty($this->request->query['from_date'])){
		$from_date = $this->request->query['from_date'];
	}
	
	if(!empty($this->request->query['to_date'])){
		$to_date = $this->request->query['to_date'];
	}
	
	$forprint = 'No';
	if(array_key_exists('forprint',$this->request->query)){
		$forprint = $this->request->query['forprint'];
	}
	
	$rootURL = Router::url('/', true);
	$queryStr1 = "";
	if( isset($this->request->query['search_kw']) ){
		$queryStr1.="search_kw=".$this->request->query['search_kw'];
	}
	if(array_key_exists('from_date',$this->request->query)){
		$from_date1 = $this->request->query['from_date'];
		if(empty($queryStr1)){
			$queryStr1.="start_date=".$from_date1;
		}else{
			$queryStr1.="&start_date=".$from_date1;	
		}
		
	}
	if(array_key_exists('to_date',$this->request->query)){
		$to_date1 = $this->request->query['to_date'];
		if(empty($queryStr1)){
			$queryStr1.="end_date=".$to_date1;	
		}else{
			$queryStr1.="&end_date=".$to_date1;
		}
		
	}
	if( isset($this->request->query['category']) ){
		foreach($this->request->query['category'] as $key => $categoryID){
			if(!empty($queryStr1))
				$queryStr1.="&category[$key] = $categoryID";
			else
				$queryStr1.="&category[$key] = $categoryID";
		}
	}
	
	
	$url = $this->Url->build(['controller' => 'stockTransfer', 'action' => 'get_cat_price'],true);
	$discard_url = $this->Url->build(['controller' => 'stockTransfer', 'action' => 'discard'],true);
	?>
	
	<input type="hidden" value="<?=$url;?>" name="url" id="cat_price_url" />
	<input type="hidden" value="<?=$discard_url;?>" name="url" id="cat_discard_url" />
	<form action='<?php echo $this->request->webroot;?>stock-transfer/search_transferred_stock' method = 'get'>

		<div class="search_div">
			<fieldset>
				<legend>Search</legend>
				<table style="margin-top: -13px;margin-bottom: -18px;">
					<tr>
						<td colspan='1'>
						</td>
						<td><strong>Find by category &raquo;</strong></td>
					</tr>
					<tr>
						<td>
							<table>
								<tr>
									<td>
										<input type = "text" name = "from_date" id = "datepicker_1" placeholder = "From date" value = '<?= $from_date;?>'style = "width:100px"/>
									</td>
									<td>
										<input type = "text" name = "to_date" id = "datepicker_2" placeholder = "To date" value = '<?= $to_date;?>'style = "width:100px"/>
									</td>
								</tr>
								<td>
									<div id='remote'>
								<input type = "text" class="typeahead" name = "search_kw" id="search_kw" placeholder = "Product code or product name" value = '<?= $value;?>'style = "width:200px" autofocus/>
							</div>
								</td>
								<td>
									<select id = "site_id" name= "site" onchange="test()">
							<?php foreach($sites as $site_id => $site_name){ ?>
								<option <?php if($selected_site_id == $site_id){echo "selected=selected";} ?> value="<?php echo $site_id;?>"><?php echo $site_name;?></option>
							<?php }?>
							</select>
								</td>
								<td id="extenal_site_kiosk">
									
							<?php foreach($external_site_kiosk as $external_site_name => $sk_val){ ?>
							<div id="<?php echo $external_site_name;?>">
								<select id = "ex_site_id" name= "<?php echo $external_site_name;?>_kiosk" onchange="form_submit();" >
										<?php foreach($sk_val as $ex_site_id => $ex_site_name){
								?>
								<option <?php if($ex_kiosk_id == $ex_site_id){echo "selected=selected";}?> value="<?php echo $ex_site_id;?>"><?php echo $ex_site_name;?></option>
							<?php } ?>
							</select>
								</div>
							<?php }
							?>
								</td>
								<tr></tr>
							</table>
						</td>
						<td rowspan="3"><select id='category_dropdown' name='category[]' style='width: 264px; height: 145px;' multiple="multiple" size='6' onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select></td>
						<td>
							For printing?<br/>
							<input type = "radio" name = "forprint" value = 'Yes' <?php if($forprint=='Yes'){echo "checked";}?>/>Yes<br/><br/>
							<input type = "radio" name = "forprint" value = 'No' <?php if($forprint=='No'){echo "checked";}?>/>No
						</td>
						<td>
							<table><tr><td>
								<input type = "submit" id="submit_search" name = "submit1" value = "Search"/>
							</td></tr>
							<tr>
								<td><input type='button' name='reset' value='Reset' style='padding:6px 8px;width:100px ;color:#333;border:1px solid #bbb;border-radius:4px;' onClick='reset_search();'/></td>	
							</tr>
							</table>
							
						</td>
					</tr>
				</table>
			</fieldset>	
		</div>
	</form>
	
	<?php
	$fianlQty = 0;
		$queryStr = "";
		//$rootURL = $this->html->url('/', true);
		if( isset($this->request->query['search_kw']) ){
			$queryStr.="search_kw=".$this->request->query['search_kw'];
		}
	
		if($forprint == "Yes"){
			$style = "'width: 136px;float: right;margin-right: 402px;'";
		}else{
			$style = "'display: none;'";
		}
		
	?>
	<input type = "button" onclick="printDiv('printit')" value = "Print" style=<?=$style;?>/>
<div id='printit'>
	<?php
		$screenHint = $hintId = "";
        //pr($hint);die;
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
           //echo $hintId;die;
		}
		$updateUrl = "/img/16_edit_page.png";
		
	?>
	<strong>
	<div style="background-color: yellow;width: 571px;">**Bulk Invoice Can Process Max. 1000 Product(not more then that).Please Keep The Number less then or equal to 1000</br>
	**Please update quantity before performing export operation</br>
	***Products transferred from main site to its kiosks will not appear here
	</div>
	<?php 
	echo __('<span style="font-size: 20px;color: red;">Dispatched Products</span> <span style="font-size: 17px;">(Warehouse to Kiosk)</span>'); ?></strong>
	<span style="background: skyblue;color: blue;" title='<?php echo $screenHint ?>'>?</span>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));
	
	?>
	
	<?php
		if($searched == 1){
			if($todaysearched == 0){
				if(array_key_exists('category',$this->request->query)){
					$categroy_id_arr = $this->request->query['category'];
					if(!empty($categroy_id_arr)){
						$categroy_id_str = implode(",",$categroy_id_arr);	
					}else{
						$categroy_id_str = "";
					}	
				}else{
					$categroy_id_str = "";
				}
				
				echo "<form action='".$this->request->webroot."stock-transfer/create_invoice' id='myform' method = 'post'>";
				echo "<input type='hidden' id='cat_value' value='$categroy_id_str' name ='transfer_stock[cat_value]'>";		
				if(!empty($from_date)){
				 echo "<input type='hidden' id='from_date' value='$from_date' name ='transfer_stock[from_date]'>";		
				} 
				if(!empty($to_date)){
					echo "<input type='hidden' id='to_date' value='$to_date' name ='transfer_stock[to_date]'>";		
				}
					echo "<input type='hidden' id='external_site_id_hidden' value='$selected_site_id' name ='transfer_stock[external_site_id_hidden]'>";
					echo "<input type='hidden' id='site_id_hidden' value='$ex_kiosk_id' name ='transfer_stock[site_id_hidden]'>";		
				
				echo "<div style='display: inline-block;'>";
				echo $this->Form->input('bulk_discount',array('type' => 'text',
																		  'label' => 'Bulk Discount',
																		  'div' => false,
																		  'style' => "width: 100px;height: 14px;",
																		  'id' => 'blk_discount',
																		  ));  
				foreach($customers as $key => $customersid){
					$custArry[$customersid['id']] = $customersid['fname']."(".$customersid['business'].")";
				}
				//echo $this->Form->input('customer_Id', array('options' => $custArry,
				//											 'default' => '--select--',
				//											 'label' => false,
				//											 'div' => false,
				//											 'style' => "width: 303px;"
				//											 ));
					echo "<div id='remote1' '>";
				echo "<input name='search_kw' class='typeahead1' id='cust_email' placeholder='Customer email, mobile or business' style = 'width:148px;height: 50px;margin-bottom: 10px;'   autofocus/>";;
				echo "</div>";
				echo "<input type = text id = custId name = customer_Id placeholder = Customer id style = width:145px;height: 20px;   autofocus/><br/><br/>";
				if($notAllow != 1){
					echo "<input type='submit' name = 'submit1' value='create invoice' onclick = \"return confirm('Are you sure you want to create invoice for this amount?');\">"; ?>
					<a href="<?php echo $rootURL;?>stock-transfer/export1/?<?php echo $queryStr1;?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png', array('fullBase' => true));?></a>
					<?php
				}
				
				?>
				</div>
				<div id= "cat_div" style="max-height: 227px;overflow-y: scroll; display: inline-block;width: 432px;">
				</div>
				<div style="float: right;">
				<input type="button" name="get_cat_price" id="get_cat_price" value="Get Catagory Price" />
				<input type="submit" name="update_qty_new" id="update_qty_new" value="Update Qty"  onclick="return confirm('This Operation Is Not Reversible.. Are you sure you want to Update?');" />
				</div>
				<div style="float: right;">
				<?php	if($cat_serach == 1){ ?>
					<input type="button" name="discard_cat" id="discard_cat" value="Discard Cat"  />
				<?php } ?>
				</div>
			<?php
			echo "<div style='float:right'><b>Total Products :</b> <span id='kiosk_total_product' style='background:yellow;'>calculating...</span></div>";
				echo "<div style='float:right'><b>Total Quantity :</b> <span id='kiosk_total_qty' style='background:yellow;'>calculating...</span></div>";
				echo "<div style='float:right'><b>Total Cost :</b> <span id='kiosk_total_cost' style='background:yellow;'>calculating...</span></div>";
				echo "<div style='float:right'><b>Total Invoice Value(INC VAT):</b> <span id='kiosk_total_bill' style='background:yellow;'>calculating...</span></div>";
			} 
		}
	?>
				
	<table cellpadding="0" cellspacing="0">
	<thead>
	<?php
	if($forprint=='Yes'){ ?>
	<tr>
		<td colspan="5"><h4><?php echo "Accessory sale from date: $from_date to $to_date"?></h4></td>
	</tr>
	<?php } ?>
	<tr>
		<?php
		if($searched == 1){?>
		<th><?php echo "Sr.No." ?></th>
					<?php //if($forprint!='Yes'){?>
					<th><?php echo 'Product Id'; ?></th>
				<?php //}else{ ?>
					
				<?php //} ?>
					<th>Product Code</th>
					<th><?php echo 'Dispatch Date';  ?></th>
					<th>Category</th>
					<th>Product</th>
					<th>Cost Price</th>
					<th>sale price</th>
					<?php if($forprint =='Yes'){?>
					<th><?php echo 'Image'; ?></th>
					<?php } ?>
					<th><?php echo 'quantity'; ?></th>
					<?php if($todaysearched == 0){?> <th>Trans Qty</th>
					<?php } ?>
	<?php }else{ ?>
	<th><?php echo "Sr.No." ?></th>
			<?php //if($forprint!='Yes'){?>
					<th><?php echo $this->Paginator->sort('product_id','Product Id'); ?></th>
				<?php //}else{ ?>
					
				<?php //} ?>
					<th>Product Code</th>
					<th><?php echo $this->Paginator->sort('created','Dispatch Date');  ?></th>
					<th>Category</th>
					<th>Product</th>
					<th>Cost Price</th>
					<th>sale price</th>
					<?php if($forprint!='Yes'){?>
					<th><?php echo $this->Paginator->sort('product_id','Image'); ?></th>
					<?php } ?>
					<th><?php echo $this->Paginator->sort('quantity'); ?></th>
		<?php }
		?>
		
		
		
	</tr>
	</thead>
	<tbody>
	<?php
	$id = '';
	$counter = 0;
	$checkQty = array();
	//pr($dispatchedProducts);die;
	foreach($dispatchedProducts as $dispatchedProduct){
		if(array_key_exists($dispatchedProduct->product_id,$productIdDetail)){
			if($dispatchedProduct->status == 0){
				if(array_key_exists($dispatchedProduct->product_id,$checkQty)){
					$checkQty[$dispatchedProduct->product_id] = $checkQty[$dispatchedProduct->product_id]+$dispatchedProduct->quantity;
				}else{
						$checkQty[$dispatchedProduct->product_id] = $dispatchedProduct->quantity;		
				}
			}
		}
	}
	
	$alreadyDone = array();
	//$alreadyIn
	//pr($dispatchedProducts);die;
	$groupStr = "";
	foreach ($dispatchedProducts as $key => $dispatchedProduct):
	$groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
	?>
        <?php
		if($dispatchedProduct->status == 1){
			continue;
		}
		if(in_array($dispatchedProduct->product_id,$alreadyDone)){
			continue;
		}
		if(array_key_exists($dispatchedProduct->product_id,$checkQty)){
			$totalquantity = $checkQty[$dispatchedProduct->product_id];
			$alreadyDone[] = $dispatchedProduct->product_id;
		}
		$counter++;
		$category = $productCode = $productName = '';
		if(array_key_exists($dispatchedProduct->product_id,$productIdDetail)){
			$id = $dispatchedProduct->id;
			$productCode = $productIdDetail[$dispatchedProduct->product_id]['product_code'];
			$productName = $productIdDetail[$dispatchedProduct->product_id]['product'];
			$category_id = $productIdDetail[$dispatchedProduct->product_id]['category_id'];
			$costPrice = $productIdDetail[$dispatchedProduct->product_id]['cost_price'];
			$salePrice = $productIdDetail[$dispatchedProduct->product_id]['selling_price'];
		}
		if(isset($category_id) && !empty($category_id)){
			//echo $category_id;echo "</br>";
			if(array_key_exists($category_id,$categoryArr)){
				$category = $categoryArr[$category_id];
			}else{
				$category = "";
			}
		}
	$imageDir = WWW_ROOT.DS."files".DS."Products".DS."image".DS.$dispatchedProduct->product_id.DS;
	$sitePath = $siteUrl.DS."files".DS."Products".DS."image".DS.$dispatchedProduct->product_id.DS;
	if(array_key_exists($dispatchedProduct->product_id,$productIdDetail)){
		$imageName = $productIdDetail[$dispatchedProduct->product_id]['image'];
	}
	$dispatchDate = $dispatchedProduct->created;
	$absoluteImagePath = $imageDir.$imageName;
	$LargeimageURL = $imageURL = "/thumb_no-image.png";
		if(@readlink($absoluteImagePath) ||($absoluteImagePath)){
			$imageURL = $sitePath.$imageName;
			$LargeimageURL = $sitePath."vga_".$imageName;
		}
        ?>
	<tr>
		<input name="_csrfToken" autocomplete="off" value="<?php echo $token = $this->request->getParam('_csrfToken');?>" type="hidden">
		<?php if(!empty($id)){ echo "<input type='hidden' id='id_$counter' value='$id' name ='transfer_stock[id][$counter]'>";} ?>
		<?php  $proID = $dispatchedProduct->product_id;?>
		<td><?php echo $counter;?></td>
		<td><?php echo $proID = $dispatchedProduct->product_id;?></td>
		<?php echo "<input type='hidden' id='product_id_$counter' value='$proID' name ='transfer_stock[proID][$counter]'>";?>
		<td><?php echo $productCode;?></td>
		<?php echo "<input type='hidden' id='product_Code_$counter' value='$productCode' name ='transfer_stock[productCode][$counter]'>";?>
		<td><?php
		echo date("jS M, Y h:i A",strtotime($dispatchDate));
		//echo $this->Time->format('jS M, Y h:i A', $dispatchDate,null,null);?></td>
		<td><?php echo $category ?></td>
		<td><?php echo $productName;?></td>
		<td><?php echo $costPrice;?></td>
		<td><?php echo $salePrice;?></td>
		<?php
			echo "<input type='hidden' name='unit_sale_price' value='$salePrice' id='unit_sale_price_{$counter}' />";
			echo "<input type='hidden' name='unit_cost_price' value='$costPrice' id='unit_cost_price_{$counter}' />";
			if($forprint == 'Yes'){?>
			<td><?php 
            echo $this->Html->link(
								   $this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')),
								   $LargeimageURL,
					array('escapeTitle' => false, 'title' => $productName,'class' => "group{$key}"));
            //$this->Html->image($imageURL, array('alt' => $productName,'width' => '100px','height' => '100px'));?></td>
		<?php }?>
		<td><?php echo $totalquantity;?></td>
		<?php $fianlQty += $totalquantity;?>
		<?php
			echo "<input type='hidden' id='orgQty_$counter' value='$totalquantity' name ='transfer_stock[origQty][$counter]'>";
			if($searched == 1){
				if($todaysearched == 0){
					if($dispatchedProduct->status == 0){
						echo "<td>";
						$options = array();
						for($i= 0; $i <= $totalquantity; $i++){
							$options[$i] = $i;
						}
			
						echo $this->Form->input("selected_qty_{$counter}", array('options' => $options,
																	 'default' => $totalquantity,
																	 'label' => false,
																	 'name' => "transfer_stock[selectedQty][$counter]",
																	 'onChange' => 'calculate_invoice_bill();',
																	 'id' => "quantity_number_{$counter}"
																	 ));
						echo "</td>";
					}
				}
			 }
		?>
		
	</tr>
<?php 
endforeach; ?>
	</tbody>
	</table>
</div>
	<p>
		<?php if($searched == 1){?>
		</form>
		<?php 	
} ?>
<p> total Qantity : <?php echo $fianlQty;?></p>
<?php if($searched != 1){?>
	<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
	<?php 	
} ?>
</div>

<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Dispatched Products'), array('controller' => 'stock_transfer', 'action' => 'dispatched_products')); ?> </li>
		<li><?php echo $this->Html->link(__('Sale Summary'), array('controller' => 'stock_transfer', 'action' => 'summary_sale')); ?> </li>
	</ul>
</div>
<input type='hidden' name='url_category' id='url_category' value=''/>
<script type="text/javascript">
	$(function() {
		$( "#datepicker_1" ).datepicker({ dateFormat: "dd-mm-yy" });
		$( "#datepicker_2" ).datepicker({ dateFormat: "dd-mm-yy" });
	});

	function printDiv() {
		var printContents = document.getElementById("printit").innerHTML;
		var originalContents = document.body.innerHTML;
	   
		document.body.innerHTML = printContents;
	   
		window.print();
	   
		document.body.innerHTML = originalContents;
		location.reload();
	}
	   
	function update_hidden(){
		var multipleValues = $( "#category_dropdown" ).val() || [];
		$('#url_category').val(multipleValues.join( "," ));
	}
 
	function calculate_invoice_bill() {
		var invoiceBill = 0;
		var costBill = 0;
		var total_quantity = 0;
		var unitCostPrice, unitSalePrice, quantity;
		var product_count = <?php echo $counter;?>;
		for(var i = 1; i <= <?php echo $counter;?>; i++){
		   unitSalePrice = parseFloat($('#unit_sale_price_'+i).val());
		   quantity = parseInt($('#quantity_number_'+i).val());
		   invoiceBill += unitSalePrice * quantity;
		}
		for(var i = 1; i <= <?php echo $counter;?>; i++){
		   unitCostPrice = parseFloat($('#unit_cost_price_'+i).val());
		   quantity = parseInt($('#quantity_number_'+i).val());
		   //if (i <= 2) {
			//alert(quantity);
			total_quantity = parseInt(total_quantity)+parseInt(quantity); 
           //}
		   
		   costBill += unitCostPrice * quantity;
		}
		//alert(total_quantity);
		if ($('#blk_discount').val()) {
            var blk_discount = $('#blk_discount').val();
			var dis_val = invoiceBill * (blk_discount/100);
			invoiceBill = invoiceBill - dis_val;
        }
		$("#kiosk_total_product").html(product_count)
		$('#kiosk_total_cost').html(costBill.toFixed(2));
		$('#kiosk_total_bill').html(invoiceBill.toFixed(2));
		$('#kiosk_total_qty').html(total_quantity);
	}
	calculate_invoice_bill();
</script>

<script>
	$('#blk_discount').bind('input', function() {
		calculate_invoice_bill();
} );
</script>
<script>
		var product_dataset = new Bloodhound({
		 datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
		 queryTokenizer: Bloodhound.tokenizers.whitespace,
		 //prefetch: "/products/data",
		 remote: {
		   url: "/Products/admin_data?category=%CID&search=%QUERY",
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
	$("#blk_discount").keydown(function (event) {		
		if (event.shiftKey == true) {event.preventDefault();}
		if ((event.keyCode >= 48 && event.keyCode <= 57) ||
		(event.keyCode >= 96 && event.keyCode <= 105) ||
		event.keyCode == 8 || event.keyCode == 9 ||
		event.keyCode == 37 || event.keyCode == 39 ||
		event.keyCode == 46 ||  event.keyCode == 183) {
			;
		}  else {
			event.preventDefault();
		}
	});
</script>

<script>
	$(document).on('click', '#get_cat_price', function() {
		var date_1 = $("#datepicker_1").val();
		var date_2 = $("#datepicker_2").val();
		var site_id = $("#site_id").val();
		var kiosk_id = 0;
		if (site_id == 1) {
			var kiosk_id = $('[name="mbwaheguru_kiosk"]').val();
			if (kiosk_id == -1) {
                kiosk_id = 0
            }
        }else if(site_id == 2){
			var kiosk_id = $('[name="fonerevive_kiosk"]').val();
			if (kiosk_id == -1) {
                kiosk_id = 0
            }
		}
		
		var target_url = $("#cat_price_url").val();
		target_url += "?start_date="+date_1;
		target_url += "&end_date="+date_2;
		target_url += "&site_id="+site_id;
		target_url += "&kiosk_id="+kiosk_id;
		
		$.blockUI({ message: 'Updating cart...' });
		$.ajax({
				type: 'get',
				url: target_url,
				beforeSend: function(xhr) {
					xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				},
				success: function(response) {
					var objArr = $.parseJSON(response);
					if(typeof objArr.data == "undefined"){
						
					}else{
						var innerHTMLStr = "<table><tr><th>Cat Name</th><th>Total Cost</th><th>Total Amt</th><th>Total Product</th><th>Total Quantity</th><th>action</th></tr>";
						var value = $("#site_id").val();
						var kiosk_id = 0;
						if (site_id == 1) {
							var kiosk_id = $('[name="mbwaheguru_kiosk"]').val();
							if (kiosk_id == -1) {
								kiosk_id = 0
							}
						}else if(site_id == 2){
							var kiosk_id = $('[name="fonerevive_kiosk"]').val();
							if (kiosk_id == -1) {
								kiosk_id = 0
							}
						};
						$.each(objArr.data, function(key, obj){
							var link ="";
							var link = "<a id=discard_link href=/stock-transfer/discard?start_date=";
							link += date_1+"&end_date=";
							link += date_2+"&cat_val=";
							link += obj.cat_id+"&site_id=";
							link += obj.site_id+"&kiosk_id=";
							link += obj.kiosk_id+">Discard</a>";
							//link = 
							innerHTMLStr += "<tr><td>"+key+"</td><td>"+obj.cost_price+"</td><td>"+obj.sale_price+"</td><td>"+obj.product_count+"</td><td>"+obj.total_qty+"</td><td>"+link+"</td></tr>";
							});
						innerHTMLStr += "</table>";
					}
					$("#cat_div").html(innerHTMLStr);
					$("#cat_div").show();
					$.unblockUI();
				},
				error: function(e) {
					$.unblockUI();
					alert("An error occurred: " + e.responseText.message);
					console.log(e);
				}
			});
		
	});
	
	$(document).on('click', '#update_qty_new', function() {
		$('#myform').attr('action', "/stock-transfer/update_qty");
	});
	
	$(document).on('click', '#discard_link', function() {
		if (confirm('This Operation Is Not Reversible.. Are you sure you want to Discard?')) {
			var target_url = $(this).attr("href");
			var site_id = $("#site_id").val();
			target_url += "&site_id="+site_id;
			$(this).attr("href", "#");
			$.blockUI({ message: 'Updating cart...' });
			$.ajax({
					type: 'get',
					url: target_url,
					beforeSend: function(xhr) {
						xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
					},
					success: function(response) {
						var objArr = $.parseJSON(response);
						if (objArr.key == "success") {
							location.reload();
						}else{
							alert(objArr.key);
						}
						$.unblockUI();
						return false;
					},
					error: function(e) {
						$.unblockUI();
						alert("An error occurred: " + e.responseText.message);
						console.log(e);
						return false;
					}
				});	
		}else{
			
		}
	});
	
	
	$(document).on('click', '#discard_cat', function() {
		if (confirm('This Operation Is Not Reversible.. Are you sure you want to Discard?')) {
			var date_1 = $("#datepicker_1").val();
			var date_2 = $("#datepicker_2").val();
			var cat_val = $("#category_dropdown").val();
			
			var site_id = $("#site_id").val();
			var kiosk_id = 0;
			if (site_id == 1) {
				var kiosk_id = $('[name="mbwaheguru_kiosk"]').val();
				if (kiosk_id == -1) {
					kiosk_id = 0
				}
			}else if(site_id == 2){
				var kiosk_id = $('[name="fonerevive_kiosk"]').val();
				if (kiosk_id == -1) {
					kiosk_id = 0
				}
			}
			
			var target_url = $("#cat_discard_url").val();
			target_url += "?start_date="+date_1;
			target_url += "&end_date="+date_2;
			target_url += "&cat_val="+cat_val;
			target_url += "&site_id="+site_id;
			target_url += "&kiosk_id="+kiosk_id;
			
			$.blockUI({ message: 'Updating cart...' });
			$.ajax({
					type: 'get',
					url: target_url,
					beforeSend: function(xhr) {
						xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
					},
					success: function(response) {
						var objArr = $.parseJSON(response);
						if (objArr.key == "success") {
							location.reload();
						}else{
							alert(objArr.key);
						}
						$.unblockUI();
					},
					error: function(e) {
						$.unblockUI();
						alert("An error occurred: " + e.responseText.message);
						console.log(e);
					}
				});	
		}else{
			
		}
	});
	 $(document).ready(function(){
		$("#cat_div").hide();
		var value = $("#site_id").val();
		var option_text = $("#site_id option:selected").text();
		var test_array = <?php echo json_encode($external_sites);?>;
		$.each(test_array, function (index, value_to_hide) {
				var id_to_hide = "#"+value_to_hide;
				$(id_to_hide).hide();
		});
		if (test_array.hasOwnProperty(value)) {
			if (option_text == "hpwaheguru") {
                option_text = "default";
            }
			var id = "#"+option_text;
			
			$(id).show();
				$("#extenal_site_kiosk").show();	
		}else{
			
			$("#ex_site_id").val("-1");
			$("#extenal_site_kiosk").hide();
		}
	});
</script>
	

<script>
	function reset_search(){
		jQuery( "#datepicker_1" ).val("");
		jQuery( "#datepicker_2" ).val("");
		jQuery("#search_kw").val("");
		jQuery("#search_rcit").val("");
		jQuery("#category_dropdown").val("");
		//jQuery("#kioskid").val("");
		//$('#cash_id').attr('checked', false)
		//$('#card_id').attr('checked', false)
		//$('#multiple_id').attr('checked', false)
	}
</script>
<script>
 var user_dataset = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  //prefetch: "/products/data",
  remote: {
    url: "/retail-customers/custemail_w?search=%QUERY",
    wildcard: "%QUERY"
  }
});

$('#remote1 .typeahead1').typeahead(null, {
  name: 'email',
  display: 'email',
  source: user_dataset,
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
    suggestion: Handlebars.compile('<div id="cust_id" style="background-color:lightgrey;width:550px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{fname}}</a>  <a class="row_hover" href="#-1">{{lname}}</a>  <a class="row_hover" href="#-1">{{business}}</a>  <a id="cust" rel={{id}} class="row_hover" href="#-1">{{email}}</a></strong></div>'),
	header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------hpwaheguru.co.uk---------</b></div>"),
  }
}).bind("typeahead:selected", function(obj, datum, name) {
$("#custId").val(datum.id);
});

</script>
		<script>
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>
<script>
	function test() {
		var value = $("#site_id").val();
		var option_text = $("#site_id option:selected").text();
		var test_array = <?php echo json_encode($external_sites);?>;
		$.each(test_array, function (index, value_to_hide) {
				var id_to_hide = "#"+value_to_hide;
				$(id_to_hide).hide();
		});
		if (test_array.hasOwnProperty(value)) {
			if (option_text == "hpwaheguru") {
                option_text = "default";
            }
			var id = "#"+option_text;
			$(id).show();
				$("#extenal_site_kiosk").show();	
		}else{
			$("#ex_site_id").val("-1");
			$("#extenal_site_kiosk").hide();
		}
    }
</script>
<script>
	function form_submit() {
		 $.blockUI({
			message: 'Loading ...'
		  });
        $( "#submit_search" ).trigger( "click" );
    }
</script>