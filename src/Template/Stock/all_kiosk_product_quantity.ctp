<?php
    use Cake\Core\Configure;
    use Cake\Core\Configure\Engine\PhpConfig;
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
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
<?php
	//$kiosks = "";
	if(!isset($status)){$status = "";}
	if(!isset($start_date)){$start_date = "";}
	if(!isset($end_date)){$end_date = "";}
	if(!isset($kioskId )){$kioskId = "";}
	if(!empty($cookieKioskId)){$kioskId = $cookieKioskId;}
	//pr($this->Session->read());
	$kiosks['-1'] = 'All';
?>
<meta http-equiv="expires" content="0">
<div class="mobileRepairs index">
	<?php
	 $start_date = $kiosk = $value = '';
	if(!empty($this->request->query['search_kw'])){$value = $this->request->query['search_kw'];}
    if(!empty($this->request->query['kiosk_id'])){$kiosk = $this->request->query['kiosk_id'];}
    if(!empty($this->request->query['start_date'])){$start_date = $this->request->query['start_date'];}
	?>
	<form action='<?php echo $this->request->webroot; ?>stock/kiosk_daily_stock' method = 'get'>
		<fieldset>
			<legend>Search</legend>
			<div>
				<table>
					<tr>
						<td><div id='remote'><input class="typeahead" type = "text" name = "search_kw" id ='search_kw' value ='<?= $value; ?>' placeholder = "product name/code" style = "width:130px" autofocus/></div></td>
						<td><?=$this->Form->input(null,array('options' => $kiosks,'label' => false, 'empty' => 'Select Kiosk', 'style' => 'width:180px', 'id'=> 'kioskid', 'name' => 'kiosk_id', 'selected' => $kiosk, 'value' => '-1'));?></td>
                        <td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "Date"  style = "width:80px;height: 25px;"value='<?php echo $start_date;?>' /></td>
                        <td><input type='button' name='reset' value='Reset Search' style='padding:6px 8px;color:#333;border:1px solid #bbb;border-radius:4px;width: 95px;' onClick='reset_search();'/></td>
						<td>&nbsp;</td>
						<td><input type = "submit" value = "Search" name = "submit"/></td>
					</tr>
				</table>
				
				
			</div>
		</fieldset>	
	</form>
<script>
	
	function reset_search(){
		jQuery( "#repair_id" ).val("");
		jQuery( "#search_kw" ).val("");
		jQuery("#imei").val("");
		jQuery("#datepicker1").val("");
		jQuery("#datepicker2").val("");
		jQuery("#kioskid").val("");
		jQuery("#status").val("");
	}

</script>
	<strong style="font-size: 20px; color: red;"><?php echo __('Daily Stock'); ?></strong>
	<span><i>**Entire products of all kiosks are being saved post 4th May 2016</i></span>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th>Product</th>
			<th>Product Code</th>
			<th>Quantity</th>
	</tr>
	</thead>
	<tbody>
		<?php 
			$totalQuantity = 0;
			foreach($finalQuantityArr as $product_id => $quantity){
				$totalQuantity+=$quantity;
				?>
				<tr>
					<td><?=$productDetArr[$product_id]['Product']['product'];?></td>
					<td><?=$productDetArr[$product_id]['Product']['product_code'];?></td>
					<td><?=$quantity;?></td>
				</tr>
		<?php }
		?>
		<tr>
			<td><strong>Total: </strong></td>
			<td>&nbsp;</td>
			<td><strong><?=$totalQuantity;?></strong></td>
		</tr>
	</tbody>
	</table>
	
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
	jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
	});
</script>
<script>
 var product_dataset = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  //prefetch: "/products/data",
  remote: {
    url: "/stock/admin_data?search=%QUERY",
                    replace: function (url,query) {
					 return url.replace('%QUERY', query);
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