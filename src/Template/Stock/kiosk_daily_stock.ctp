<?php
 use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
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
///pr($totalCost);die;
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
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
	$displayType = 'more_than_zero';
	if(!empty($this->request->query['search_kw'])){$value = $this->request->query['search_kw'];}
    if(!empty($this->request->query['kiosk_id'])){$kiosk = $this->request->query['kiosk_id'];}
    if(!empty($this->request->query['start_date'])){$start_date = $this->request->query['start_date'];}
	if(!empty($this->request->query['display_type'])){$displayType = $this->request->query['display_type'];}
	?>
	<form action='<?php echo $this->request->webroot; ?>stock/kiosk_daily_stock' method = 'get' id="display_form">
		<fieldset>
			<legend>Search</legend>
			<div>
				<table>
					<tr>
						<td style="width: 10px;"><div id='remote'><input class="typeahead" type = "text" name = "search_kw" id ='search_kw' value ='<?= $value; ?>' placeholder = "product name/code" style = "width:180px" autofocus/></div></td>
						<td style="width: 10px;"><?=$this->Form->input(null,array('options' => $kiosks,'label' => false, 'empty' => 'Select Kiosk', 'style' => 'width:180px', 'id'=> 'kioskid', 'name' => 'kiosk_id', 'value' => $kiosk));?></td>
                        <td style="width: 10px;"><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "Date"  style = "width:80px;height: 25px;"value='<?php echo $start_date;?>' /></td>
                        <td style="width: 10px;"><input type='button' name='reset' value='Reset Search' style='padding:6px 8px;margin-top: 8px;color:#333;border:1px solid #bbb;border-radius:4px;width: 95px;' onClick='reset_search();'/></td>
						
						<td><input type = "submit" value = "Search" name = "submit1" style ="margin-top: 10px;width: 95px;" /></td>
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
<?php
	$screenHint = $hintId = "";
				if(!empty($hint)){
				   $screenHint = $hint["hint"];
				   $hintId = $hint["id"];
				}
				$updateUrl = "/img/16_edit_page.png";
?>
</script>
	<strong style="font-size: 20px; color: red;"><?php echo __('Daily Stock')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?></strong>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	<table>
	 <tr>
	  <td style="width: 25%;">Show items with zero quantity</td>
	  <td style="width: 7%;"><input type="radio" name="display_type" value="show_all" id="show_all" onclick="submitForm();" <?php echo $displayType=="show_all"? "checked":"" ?> form="display_form">&nbsp;Yes</td>
	  <td><input type="radio" name="display_type" value="more_than_zero" id="more_than_zero" onclick="submitForm();" <?php echo $displayType=="more_than_zero"? "checked":"" ?> form="display_form">&nbsp;No</td>
	 </tr>
	</table>
	<span><i>**The stock showing below is day closing time stock like </br> if you search 1 jan the result will be the end of 1 jan by closing time of 1 jan</i></span></br>
	<span><i>**Entire products of all kiosks are being saved post 4th May 2016</i></span>
    <?php //pr($totalCost);die; ?>
	<span style="float: right;margin-right: 218px;"><strong>Total Cost = &#163;<?php if(is_numeric($totalCost['total_cost'])){echo $totalCost['total_cost'];}else{echo "0";} ?></strong></span>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('product_id ','Product Name'); ?></th>
			<th><?php echo $this->Paginator->sort('product_id ','Product Code'); ?></th>
			<th><?php echo $this->Paginator->sort('cost_price','Cost Price'); ?></th>
			<th><?php echo $this->Paginator->sort('selling_price','Selling Price'); ?></th>
			<th><?php echo $this->Paginator->sort('quantity'); ?></th>
            <th><?php echo $this->Paginator->sort('created'); ?></th>
	</tr>
	</thead>
	<tbody>
        
	<?php
    
          
    foreach ($dailyStocks as $stock):
	//pr($stock);die;
	?>
	<tr>
        <td>
            <?php echo $product_name[$stock->product_id]; ?>
        </td>
        <td>
            <?php echo $product_code[$stock->product_id]; ?>
        </td>
        <td>
            <?php echo $stock->cost_price; ?>
        </td>
        <td>
            <?php echo $stock->selling_price; ?>
        </td>
        <td>
            <?php echo $stock->quantity; ?>
        </td>
        <td>
            <?php
												//$createdDate = 
												  $stock->created->i18nFormat(
																																																						[\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
																																																					);
															$created =  $stock->created->i18nFormat('dd-MM-yyyy HH:mm:ss');
			echo  date("jS M, Y h:i:s",strtotime($created));
			//echo $this->Time->format('jS M, Y',$stock->created,null,null); ?>
        </td>
    </tr>
		
<?php endforeach; ?>
	</tbody>
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
   
    suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:400px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{product_code}}</a></strong></div>'),
	header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------<?php echo ADMIN_DOMAIN;?>---------</b></div>"),
  }
});
</script>
</script>
 <script>
$(function() {
  $( document ).tooltip({
   content: function () {
    return $(this).prop('title');
   }
  });
 });

 function submitForm(){
	 document.getElementById("display_form").submit();
 }
</script>