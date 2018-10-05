<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<div class="kioskOrders index">
	<?php
	$siteUrl = Configure::read('SITE_BASE_URL');
	$value = '';
	if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
	}
	$value1 = '';
    //pr($this->request->query);die;
	if(!empty($this->request->query['search_rcit'])){
		$value1 = $this->request->query['search_rcit'];
	}
    //echo $value1;die;
	//$from_date = date("d-m-Y");
	//$to_date = date("d-m-Y");
	
	if(!empty($this->request->query['from_date'])){
		$from_date = $this->request->query['from_date'];
	}else{
        $from_date = "";
    }
	
	if(!empty($this->request->query['to_date'])){
		$to_date = $this->request->query['to_date'];
	}else{
        $to_date = "";
    }
	//pr($catagory);
    //pr($this->request);die;
    //pr($categories);die;
	?>
	<form action='<?php echo $this->request->webroot;?>stock-transfer/search_lost_stock' method = 'get'>
		<div class="search_div">
			<fieldset>
				<legend>Search</legend>
				<table style="margin-top: -13px;margin-bottom: -18px;">
					<tr>
						<td colspan='3'>
						</td>
					</tr>
					<tr>
						<td>
							<table><tr>
							<td>
							<input type = "text" name = "from_date" id = "datepicker_1" placeholder = "From date" readonly = "readonly" value = '<?= $from_date;?>'style = "width:100px"/>
						</td>
							</tr>
							<tr>
							 
						<td style="width: 425px;"><div id='remote'>
							<input type = "text"  class="typeahead" name = "search_kw" id="searchKw" placeholder = "product name or code" value = '<?= $value;?>'style = "width:200px" autofocus/></div>
						</td>
						</tr></table>
						</td>
						<td>
							<table><tr>
							<td>
							<input type = "text" name = "to_date" id = "datepicker_2" placeholder = "To date" readonly = "readonly" value = '<?= $to_date;?>'style = "width:100px"/>
						</td>
						</tr>
							<tr>
						<td style="width: 425px;">
							<input type = "text" name = "search_rcit" id = "search_rcit"placeholder = "Invoice No." value = '<?= $value1;?>' style = "width:200px" autofocus/>
						</td>
						</tr></table>
						</td>
						<td>
                        <td rowspan="3"><select id='category_dropdown' name='category[]' style='width: 264px; height: 82px;' multiple="multiple" size='6'  onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select></td>
						</td>
						<td>
						<td>
							<input type = "submit" name = "submit" value = "Search"/>
							<td><input type='button' name='reset' value='Reset' style='padding:6px 8px;width:100px ;color:#333;border:1px solid #bbb;border-radius:4px;' onClick='reset_search();'/></td>
						</td>
						</td>
					</tr>
				</table>
			</fieldset>	
		</div>
	</form>
	
	<?php
		
		$queryStr = "";
		$rootURL = '';
        //$this->html->url('/', true);
		if( isset($this->request->query['search_kw']) ){
			$queryStr.="search_kw=".$this->request->query['search_kw'];
		}
	?>
	
<div id='printit'>
	<?php
		$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
		$updateUrl = "/img/16_edit_page.png";
	?>
	<strong><?php 
	echo __('<span style="font-size: 20px;color: red;">Lost Stock</span> <span style="font-size: 17px;"></span>'); ?></strong>
	<span style="background: skyblue;color: blue;" title='<?php echo $screenHint ?>'>?</span>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	<?php
	$total_Cost = $total_Sale =  0;
	if(!empty($totalCostNSalePrice)){
		$total_Cost = $totalCostNSalePrice['total_cost'];
		$total_Sale = $totalCostNSalePrice['total_sale'];
	}
	echo "<b> Total Sale = ".$total_Sale." And Total Cost = ".$total_Cost."</b>";
	?>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<?php if($searched == 0){?>
					<th><?php echo $this->Paginator->sort('invoice_reference','Identifier'); ?></th>
                    <th><?php echo $this->Paginator->sort('product_receipt_id','Recpt Id'); ?></th>
                    <th><?php echo $this->Paginator->sort('created','date');  ?></th>
					<th><?php echo $this->Paginator->sort('product_id','product code');  ?></th>
					<th><?php echo $this->Paginator->sort('product_id','product name'); ?></th>
					<th><?php echo $this->Paginator->sort('category_id','category'); ?></th>
					<th><?php echo $this->Paginator->sort('cost_price','cost price '); ?></th>
					<th><?php echo $this->Paginator->sort('sale_price','sale price'); ?></th>
                    <th><?php echo $this->Paginator->sort('quantity','qty'); ?></th>
		<?php }else{ ?>
					<th><?php echo "Identifier"; ?></th>
                    <th><?php echo "Recpt Id"; ?></th>
                    <th><?php echo "date";  ?></th>
					<th><?php echo "product code";  ?></th>
					<th><?php echo "product name"; ?></th>
					<th><?php echo "category"; ?></th>
					<th><?php echo "cost price"; ?></th>
					<th><?php echo "sale price"; ?></th>
                    <th><?php echo "qty"; ?></th>
		<?php }?>
		
	</tr>
	</thead>
	<tbody>
	<?php
    $total_sale_price = $total_cost_price = $total_qty = 0;
    //pr($surplusData);die;
    //pr($categories);die;
	foreach ($surplusData as $key => $value):?>
        <?php
        $catagoryName = $product = $productCode = "";
        $prodtID = $value['product_id'];
        $ctatgoryId = $value['category_id'];
        $costPrice = $value['cost_price'];
        $salePrice = $value['sale_price'];
            if(array_key_exists($prodtID,$productArr)){
                $product = $productArr[$prodtID]['product'];
                $productCode = $productArr[$prodtID]['product_code'];
            }
            //pr($catagory);die;
            if(array_key_exists($ctatgoryId,$catagory)){
                $catagoryName = $catagory[$ctatgoryId];
            }
			$Qty = "";
			$Qty = $value['quantity'];
            $total_sale_price += $salePrice*$Qty;
            $total_cost_price += $costPrice*$Qty;
			$total_qty += $Qty;
        ?>
	<tr>
        <td><?php echo $value['invoice_reference'];?></td>
        <td><?php echo $value['product_receipt_id'];?></td>
        <?php $Date = $value['created']; ?>
        <td><?php echo date('jS M, Y h:i A',strtotime($Date));//$this->Time->format('jS M, Y h:i A', $Date,null,null);?></td>
        <td><?php echo $productCode;?></td>
        <td><?php echo $product;?></td> 
        <td><?php echo $catagoryName; ?></td>
        <td><?php echo number_format($costPrice,2)?></td>
        <td><?php echo number_format($salePrice,2);?></td>
        <td><?php echo $value['quantity']; ?></td>
	</tr>
    <?php
endforeach; ?>
	</tbody>
    <?php if(!empty($surplusData)){ ?>
    <tbody>
        <tr>
            <td colspan=6 style="padding-left: 486px;"> <b> total =  </b></td>
            <td><b><?php echo number_format($total_cost_price,2);?></b></td>
            <td><b><?php echo number_format($total_sale_price,2);?></b></td>
			<td><b><?php echo  $total_qty;?></b></td>
        </tr>
    </tbody>
    <?php } ?>
	</table>
</div>
	<p>
		 <?php if($searched == 0){?>
	 <div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
	<?php } ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Dispatched Products'), array('controller' => 'stock_transfer', 'action' => 'dispatched_products')); ?> </li>
		<li><?php echo $this->Html->link(__('Sale Summary'), array('controller' => 'stock_transfer', 'action' => 'summary_sale')); ?> </li>
	</ul>
</div>
<script>
	$(function() {
		//var date = $('#datepicker_1').datepicker({ dateFormat: 'dd/mm/yy' }).val();
		$( "#datepicker_1" ).datepicker({ dateFormat: "dd-mm-yy" });
		//altFormat: 'dd/mm/yy'
		//$( "#datepicker_1" ).datepicker( "option", "altFormat", "dd/mm/yy" );
	});
	
	$(function() {
		//var date = $('#datepicker_2').datepicker({ dateFormat: 'dd/mm/yy' }).val();
		$( "#datepicker_2" ).datepicker({ dateFormat: "dd-mm-yy" });
		//$( "#datepicker_2" ).datepicker();
		//altFormat: 'dd/mm/yy'
		//$( "#datepicker_2" ).datepicker( "option", "altFormat", "dd/mm/yy" );
	});
</script>

</script>
<script>
$(function() {
  $( document ).tooltip({
  // content: function () {
   // return $(this).prop('title');
   }
  });
 });
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
					// alert($('#url_category').val());
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
	function reset_search(){
		jQuery( "#datepicker_1" ).val("");
		jQuery( "#datepicker_2" ).val("");
		jQuery("#searchKw").val("");
		jQuery("#search_rcit").val("");
        jQuery("#category_dropdown").val("");
		//jQuery("#kioskid").val("");
		//$('#cash_id').attr('checked', false)
		//$('#card_id').attr('checked', false)
		//$('#multiple_id').attr('checked', false)
	}
</script>