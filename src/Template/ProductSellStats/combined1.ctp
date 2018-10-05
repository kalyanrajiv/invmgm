<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
 <?php
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
	<?php
    $loggedInUser = $this->request->session()->read('Auth.User.username');
    if(!isset($type)){
	  $type = "";
	}
	if(array_key_exists("bulk_invoice",$this->request->query)){
		$bulk_invoice = $this->request->query["bulk_invoice"];
	}
	if(!isset($bulk_invoice)){
	  $bulk_invoice = 0;
	}
	if(array_key_exists("type",$this->request->query)){
			$type1 = $this->request->query['type'];
			if($type1 == "special"){
				$type = 1;
			}elseif($type1 == "normal"){
				$type = 0;
			}elseif($type1 == "both"){
				//$conditionArr['status'] = 0;
			}
	}
		$kioskProductSales = array();
		if(!isset($start_date)){$start_date = date('d M Y');}
		if(!isset($end_date)){
			$end_date = date('d M Y');
			}
		if(!isset($search_kw)){$search_kw = "";}
		if(!isset($receipt_id)){$receipt_id = "";}
		
		if(!isset($kioskId)){$kioskId = -1;}
		
		if(!empty($kiosks)){
			$kiosks[-1] = "All";
			ksort($kiosks);
			//pr($kiosks);
		}
		$cat_str = "";
		if(array_key_exists("category_id",$this->request->query)){
		 $cat_str = $this->request->query['category_id'];
		}
	//pr($kiosks);
		$queryStr = "";
			$rootURL = "";//$this->html->url('/', true);
			if( isset($this->request->query['search_kw']) ){
				//$queryStr.="?search_kw=".$this->request->query['search_kw'];
				$queryStr.="?search_kw=".$search_kw."&start_date=".$start_date."&end_date=".$end_date."&type=".$type."&category_id=".$cat_str;
			}
	?>
<div class="kioskProductSales index">
	<fieldset>
		<legend>Search</legend>
		<form name= "search_form" id="search_form" action='<?php echo $this->request->webroot; ?>ProductSellStats/search' method='get'>
		<table>
			<tr>
				<td>				
						<input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date" style = "width:106px;height: 27px;" value='<?php echo $start_date;?>' />
				</td>
				<td><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date" style = "width:106px;height: 27px;" value='<?php echo $end_date;?>' />
				
				</td>
				<td>
					<div id='remote'>
						<input type="text" class="typeahead" name="search_kw" id = 'search_kw' placeholder="product or product_code" autofocus style = "width:200" value="<?php echo $search_kw;?>"
				</div>
				<table>
				  <tr>
					 <td><input type="radio" name="bulk_invoice" <?php if($bulk_invoice == 1){ echo "checked=checked"; }?> value="1"> Bulk Invoice sale</td>
					 <td><input type="radio" name="bulk_invoice" <?php if($bulk_invoice == 0){ echo "checked=checked"; }?> value="0">All sale</td>
				  </tr>
				</table>
				</td>
                <td>
                    <?php
								if(!empty($kioskId)){
									echo $this->Form->input(null, array(
										'options' => $kiosks,
										 'label' => false,
										 'div' => false,
										       'name' => 'ProductSale[kiosk_id]',
										      'id'=> 'kioskid',
										      'selected' => $kioskId,
										      //'empty' => 'Select Kiosk',
										      'style' => 'width:125px'
											)
										);
								}else{
										echo $this->Form->input(null, array(
											'options' => $kiosks,
											'label' => false,
											'div' => false,
											 'name' => 'ProductSale[kiosk_id]',
											'id'=> 'kioskid',
                                            'selected' => -1,
											//'empty' => $kiosks[-1],
											'style' => 'width:185px'
												)
											);
								      }
								?>
                </td>
                <td rowspan="3"><select id='category_dropdown' name='category[]' style="height: 82px;width: 177px;" multiple="multiple" size='6' onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select></td>
				<td><input type="submit" name="submit1" value="Search Sales"></td>
				<td><input type='button' name='reset' value='Reset Search' style='padding:4px 8px;background:#dcdcdc;color:#333;border:1px solid #bbb;border-radius:4px;' onClick='reset_search();'/></td>
			</tr>
            <?php if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){ ?>
		<tr>
		 <td>
			<input type="radio" name="type" class = "radio1" value="special" <?php if($type == 1){echo "checked";}?>>Quotation </td>
			<td><input type="radio" name="type" class = "radio1" value="normal" <?php if($type === 0){echo "checked";}?>>Normal</td>
			<td><input type="radio" name="type" class = "radio1" value="both" <?php if($type === ""){echo "checked";}?>>Both</td>
		</tr>
		<?php }?>
		</table>
		</form>
	</fieldset>
	<?php
		$screenHint = $hintId = "";
			if(!empty($hint)){
			   $screenHint = $hint["hint"];
			   $hintId = $hint["id"];
			}
		$updateUrl = "/img/16_edit_page.png";
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){ ?>
			<h2><?php echo __('Sales Stat')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>&nbsp;<a href='<?php echo $this->request->webroot;?>ProductSellStats/export/<?php echo $queryStr;?>' target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png',array('fullBase' => true));?>
			<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));
			echo"</a></h2>";
			echo "<br>";
			echo " <div style=font-size:14px;>For product sale stat: On this screen, actual sale of product on specific date is recorded irrespective of invoice date.<br>
For example actual invoice date is 1st January and we edit invoice on 3rd January adding few more products in it, then on this screen for 3rd January it will show all products sold in actual.
<br>
 <b>When running filter for 3rd January, it will show products of invoices created on 3rd January in addition to invoice created on 1st January and edited on 3rd January for additional products.</b></div>";
			echo"<br><br>";
			?>
			
				
	<?php }else{?>
		<h2><?php echo __('Kiosk Product Sales'); ?>&nbsp;<a href='<?php echo $this->request->webroot;?>kiosk_product_sales/export/<?php echo $queryStr;?>' target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png',array('fullBase' => true));?></a></h2>
	<?php }?>
	<span style="float: right;">
	  <?php $gross_sale = $final_sale_price + $final_vat_price;?>
	  <strong>Gross sale</strong><?=round($gross_sale,2);?>
        <strong>Net Sale : </strong><?=round($final_sale_price,2);?>
        <strong>Total Cost : </strong><?=round($final_cost_price,2);?>
        <strong>Vat : </strong><?=round($final_vat_price,2);?>
    </span>
	<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th><?php echo $this->Paginator->sort('product_id', 'Product'); ?></th>
			<th>Product Code</th>
			<th>Quantity</th>
			<th>Cost Price</th>
			<th>Gross Sale</th>
			<th>Net Sale</th>
			<th>Vat</th>
			<th><?php echo $this->Paginator->sort('created','Last Sold On'); ?></th>
		</tr>
	</thead> 
	<body>
		<?php
		  //pr($product_name);die;
		$total_gross_sale = $Gross_sale = $run_time_quantity = $run_time_cost = $run_time_selling_price = $run_time_vat = 0;
		//pr($result);die;
        if(!empty($result)){
			 foreach($result as $key => $value){
                //pr($value);die;
				//$id =  $value['ProductSaleStat']['id'];
                  // $kiosk_name = $value['ProductSaleStat']['kiosk_name'];
                   $product_code = $value->product_code;
                   $cost_price = $value->cost_price;
                   $selling_price_without_vat = $value->selling_price;
                   $vat =  $value->vat;
                   $created =  $value->created;
                   $quantity =  $value->quantity;
				   $product_id =  $value->product_id;
				   
				   
				   $product = $product_name[$product_id];
                   $run_time_quantity += $quantity;
                   $run_time_cost += $cost_price;
                   $run_time_selling_price += $selling_price_without_vat;
                   $run_time_vat += $vat;
                   $Gross_sale = $selling_price_without_vat + $vat;
                   $total_gross_sale += $Gross_sale;
                   ?>
                   <tr>
                       <td><?=$product;?></td>
                        <td><?=$product_code;?></td>
                        <td><?=$quantity;?></td>
                        <td><?=$CURRENCY_TYPE.number_format($cost_price,2);?></td>
                        <td><?=$CURRENCY_TYPE.number_format($Gross_sale,2);?></td>
                        <td><?=$CURRENCY_TYPE.number_format($selling_price_without_vat,2);?></td>
                        <td><?=$CURRENCY_TYPE.number_format($vat,2);?></td>
                        <td nowrap><?=date('d-m-y g:i A',strtotime($created));//$this->Time->format('d-m-y g:i A', $created,null,null);;?></td>
                   </tr>
            <?php  } ?>
                <tr>
                    <td colspan=2></td>
                    <td><b><?=$run_time_quantity;?></b></td>
                    <td><b><?=$CURRENCY_TYPE.number_format($run_time_cost,2);?></b></td>
                    <td><b><?=$CURRENCY_TYPE.number_format($total_gross_sale,2);?></b></td>
                    <td><b><?=$CURRENCY_TYPE.number_format($run_time_selling_price,2);?></b></td>
                    <td><b><?=$CURRENCY_TYPE.number_format($run_time_vat,2);?></b></td>
                </tr>
		<?php }else{
			 echo "No Record Found";
		}?>
	</body>
	</table>
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
		
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'stock', 'action' => 'index')); ?> </li>
		<?php
        $loggedInUser = $this->request->session()->read('Auth.User.username');
        if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering?>
        <li><?=$this->element('tempered_side_menu')?></li>
        <?php }?>
		<li><?php echo $this->Html->link(__('View Invoices'), array('controller' => 'product_receipts', 'action' => 'all_invoices')); ?> </li>
		<li><?php echo $this->Html->link(__('View Sales'), array('controller' => 'kiosk_product_sales', 'action' => 'index')); ?> </li>
	</ul>
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
	function reset_search(){
		jQuery( "#datepicker1" ).val("");
		jQuery( "#datepicker2" ).val("");
		jQuery("#search_kw").val("");
		jQuery("#receipt_id").val("");
        jQuery("#category_dropdown").val("");
        jQuery("#kioskid").val("");
	}
	jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
	});
</script>
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
   $('#kioskid').change(function(){
	  $.blockUI({ message: 'Loading ...' });
	document.getElementById("search_form").submit();
	  });
    $('.radio1').change(function(){
	  $.blockUI({ message: 'Loading ...' });
	document.getElementById("search_form").submit();
	  });
</script>