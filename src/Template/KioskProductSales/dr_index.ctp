<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php
	$currency = Configure::read('CURRENCY_TYPE');
	 //echo"hi";die;
?>
<?php
if(!isset($kiosk_id)){
	$kiosk_id = 10000;
	}else{
		if($kiosk_id == 0){
			$kiosk_id = 10000;
		}
	}
if(!isset($start_date)){
  
    }
if(!isset($end_date)){
	 
	}
if(!isset($search_kw)){$search_kw = "";}
if(!isset($receipt_id)){$receipt_id = "";}
if(array_key_exists("category",$this->request->query)){
	$cat_arr = $this->request->query['category'];
	$cat_str = implode("_",$cat_arr);
}
$queryStr = "";
		$rootURL = "";//$this->html->url('/', true);
		if( isset($this->request->query['search_kw']) || isset($this->request->query['kiosk_id'])){
			//$queryStr.="?search_kw=".$this->request->query['search_kw'];
			if(isset($this->request->query['search_kw'])){
				$queryStr.="?search_kw=".$this->request->query['search_kw']."&start_date=".$start_date."&end_date=".$end_date."&receipt_id=".$receipt_id."&kiosk_id=".$kiosk_id."&type=1";
			}else{
				$queryStr.="?start_date=".$start_date."&end_date=".$end_date."&receipt_id=".$receipt_id."&kiosk_id=".$kiosk_id."&type=1";		
			}
			
		}
		
		if(!empty($cat_str)){
				$queryStr.= "&category=".$cat_str;
			}
		 
		?>
<div class="kioskProductSales index">
	<fieldset>
		<legend>Search</legend>
		<form name= "search_form" id="search_form" action='<?php echo $this->request->webroot; ?>KioskProductSales/dr_searchsale' method='get'>
		<table>
			<tr>
				<td>
					<table>
						<tr>
							<td>
								<div id='remote'>
									<input class="typeahead" type = "text" name = "search_kw" id = "search_kw" placeholder = "product name or code" autofocus style = "width:200px;margin-top: -25px;"value='<?php echo $search_kw;?>'/>
								</div>
							</td></tr>
							
							<tr><td><input type="text" name="receipt_id" id = 'receipt_id' placeholder="receipt id" autofocus style = "width:150px;margin-top: -9px;" value="<?php echo $receipt_id;?>" /></td></tr>
							
					</table>
				</td>
				<td>
					<select id='category_dropdown' name='category[]' style="width:200px; height: 82px;" multiple="multiple" size='6' onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select>
				</td>
				<td>
					<table>
						<tr>
							<td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date" style = "width:106px;height: 27px;" value='<?php echo $start_date;?>' /></td>
							<td><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date" style = "width:106px;height: 27px;" value='<?php echo $end_date;?>' /></td>
						</tr><tr>
							<td colspan='2'>
								<?php
									$loggedInUser = $this->request->session()->read('Auth.User.username') ;
									if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering
										echo $this->Html->link(__('View normal Invoices'), array('controller' => 'kiosk_product_sales', 'action' => 'search_wholsale'),array('id' => 'invoice','onclick' => 'addURL()')); 
									}
								?>
							</td>
						</tr>
					</table>
				</td>
						<td colspan='2'>
							<?php
							if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == SALESMAN){
								if(!empty($kiosk_id)){
										echo $this->Form->input(null, array(
											'options' => $kiosks,
											 'label' => false,
											 'div' => false,
												   'name' => 'kiosk_id',
												  'id'=> 'kioskid',
												  'value' => $kiosk_id,
												  //'empty' => 'Select Kiosk',
												  'style' => 'width:185px'
												)
											);
									}else{
											echo $this->Form->input(null, array(
												'options' => $kiosks,
												'label' => false,
												'div' => false,
												 'name' => 'kiosk_id',
												'id'=> 'kioskid',
												//'empty' => 'Select Kiosk',
												'style' => 'width:185px'
													)
												);
										  }
							}
					?>
						</td>
						
					
				<td><table>
					<tr><td><input type="submit" name="Submit" value="Search Sales"></td></tr>
					<tr><td><input type='button' name='reset' value='Reset Search' style='padding:4px 8px;background:#dcdcdc;color:#333;border:1px solid #bbb;border-radius:4px;' onClick='reset_search();'/></td></tr></table>
				</td>
			</tr>
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
	?>
	<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
				$kiosk_name = $kiosks[$kiosk_id];	
		?>
	
		<h2><?php echo __("$kiosk_name Quotation Sales")."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>&nbsp;<a href='<?php echo $this->request->webroot;?>KioskProductSales/export/<?php echo $queryStr;?>' target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png',array('fullBase' => true));?>
		<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
		</a></h2>
				
	<?php }else{?>
		<h2><?php echo __('Quotation Sales'); ?>&nbsp;<a href='<?php echo $this->request->webroot;?>KioskProductSales/export/<?php echo $queryStr;?>' target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png',array('fullBase' => true));?></a></h2>
	<?php }?>
	<?php
			$totalAmount = 0;
			foreach($orignal_amount as $id => $amount){
				$totalAmount = $amount+$totalAmount;
			}
	?>
	
	<span style="float: right;margin-top: -30px;margin-right: 25px;">
		 
		<strong>Total Sale</strong>&nbsp;<?=$CURRENCY_TYPE.number_format($sumTotal,2);?><br/>
	</span>
	<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
            <th>Kiosk</th>
			<th><?php echo $this->Paginator->sort('product_id'); ?></th>
			<th><?php echo $this->Paginator->sort('sale_price'); ?></th>
			<th>Blk Dist</th>
			<th>After Blk </br> Dist value</th>
            <th>Billed Amount</th>
			<th><?php echo $this->Paginator->sort('sold_by'); ?></th>
			<th><?php echo $this->Paginator->sort('product_receipt_id','Recpt ID'); ?></th>
			<th><?php echo $this->Paginator->sort('created','Sold On'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	//pr($products);die;
	
//	 	foreach($products as $productID => $productTitle){
//            //pr($productTitle);die;
//		  $products[$productTitle['id']] = $productTitle['product'];
//		  $code[$productTitle['id']] = $productTitle['product_code'];
//		}
	//echo"hi";die;
	?>
	
	<?php $total_qantity = $totalOriginalAmount = 0;
		$receiptArray = array();
		$netAmountArr = array();
		//pr($recepitTableData);die;
		foreach ($kioskProductSales as $kioskProductSale): ?>
	<?php  
			if(array_key_exists($kioskProductSale->product_id,$products)){
				$product_title =  $products[$kioskProductSale->product_id];		
			}else{
				$product_title = "";
			}
	    $total_qantity += $kioskProductSale->quantity;
        //pr($recepitTableData);die;
	 	//$receiptArray[$kioskProductSale['ProductReceipt']['id']] = $kioskProductSale['ProductReceipt']['bill_amount'];
        $receiptArray[$kioskProductSale->product_receipt_id]['id'] = $recepitTableData[$kioskProductSale->product_receipt_id]['bill_amount'];
		$vat = $recepitTableData[$kioskProductSale->product_receipt_id]['vat'];
		$vatItem = $vat/100;
		$netAmount = $recepitTableData[$kioskProductSale->product_receipt_id]['bill_amount']/(1+$vatItem);
		$netAmountArr[$kioskProductSale->product_receipt_id['id']] = $netAmount;
		if(array_key_exists($kioskProductSale->product_id,$products)){
			$truncatedProduct = 
							\Cake\Utility\Text::truncate(
							 $products[$kioskProductSale->product_id],
							 50,
							 [ 'ellipsis' => '...', 'exact' => false ]
							 );
			
		}else{
			$truncatedProduct = "";
		}
		
		$discntPrice = $kioskProductSale->sale_price-$kioskProductSale->sale_price*$kioskProductSale->discount/100;
		$originalAmount = $discntPrice*$kioskProductSale->quantity;
		$totalOriginalAmount+=$originalAmount;
	?>
	<tr>
		<td><?php echo h($kioskProductSale->id); ?>&nbsp;</td>
        <?php if($kioskProductSale->kiosk_id == 0){ 
					$kiosk_id = 10000;
		}else{
			$kiosk_id = $kioskProductSale->kiosk_id;
		}?>
        	<td><?php echo $kiosks[$kiosk_id];?></td>
		<td>
		<?php
		if(array_key_exists($kioskProductSale->product_id,$products)){
			echo $this->Html->link($truncatedProduct, array('controller' => 'products', 'action' => 'view', $kioskProductSale->product_id),
										array('escapeTitle' => false,
											   'title' => $products[$kioskProductSale->product_id] ,
												'id' => "tooltip_{$kioskProductSale->id}"
											 )
									 );
		}else{
			echo"--";
		}
			?>
		</td>
		 
		 
		<td><?php
		//echo $vat;die;
		//if($vat=='0'){
		//	$chargedAmount = round($discntPrice/(1+$actualVat/100),2);
		//}else{
			$chargedAmount = $discntPrice;
		//}
		//pr($users);die;
		$amt_S = $chargedAmount*$kioskProductSale->quantity;
		$amt_S = number_format($amt_S,2);
		echo  $CURRENCY_TYPE.number_format($chargedAmount,2)." (X".$kioskProductSale->quantity.") =".$CURRENCY_TYPE.$amt_S;
		//$this->Number->currency($kioskProductSale['KioskProductSale']['sale_price'], '$');?>&nbsp;</td>
		<td><?php
		$without_bulk_dis = $chargedAmount;
			$blk_dis = $recepitTableData[$kioskProductSale->product_receipt_id]['bulk_discount'];
			$amt_to_show = $without_bulk_dis - ($without_bulk_dis * ($blk_dis/100));
		$blk_dis =  $without_bulk_dis-$amt_to_show;//$recepitTableData[$kioskProductSale->product_receipt_id]['bulk_discount'];
		if(empty($blk_dis)){
			$blk_dis = 0;
		}
		$blk_dis = $blk_dis*$kioskProductSale->quantity;
		echo number_format($blk_dis,2);
		?>
		</td>
		<?php
			//$without_bulk_dis = $chargedAmount;
			//$blk_dis = $recepitTableData[$kioskProductSale->product_receipt_id]['bulk_discount'];
			//$amt_to_show = $without_bulk_dis - ($without_bulk_dis * ($blk_dis/100));
			
		?>
		<td><?php
		$amt_to_show = $amt_to_show*$kioskProductSale->quantity;
		echo number_format($amt_to_show,2);?></td>
		<td style="padding-left: 32px;">&#163;<?=number_format($recepitTableData[$kioskProductSale->product_receipt_id]['bill_amount'],2);?></td>
		
		<td><?php if(array_key_exists($recepitTableData[$kioskProductSale->product_receipt_id]['processed_by'],$users)){
				echo $users[$recepitTableData[$kioskProductSale->product_receipt_id]['processed_by']];
			}else{
				echo "--";
			} ?>&nbsp;</td>
		 
		<td>
			<?php
            //pr($kioskProductSale);die;
			if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
			$this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
			$this->request->session()->read('Auth.User.user_type')=='wholesale'
			){
			     echo $this->Html->link($kioskProductSale->product_receipt_id, array('controller' => 'product_receipts', 'action' => 'dr_generate_receipt', $kioskProductSale->product_receipt_id));
			}elseif($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
			$this->request->session()->read('Auth.User.user_type')=='retail'){
			     echo $this->Html->link($kioskProductSale->product_receipt_id, array('controller' => 'product_receipts', 'action' => 'dr_view', $kioskProductSale->product_receipt_id, $kiosk_id));
			}
			
			
			?>
		</td>
		<td><?php echo date('d-m-y h:i:s',strtotime($kioskProductSale->created));//$this->Time->format('jS M, Y h:i A', $kioskProductSale['created'],null,null);?>&nbsp;</td>		
	</tr>
<?php
endforeach; ?>
	<?php
	$totalBillAmount = 0;
	foreach($receiptArray as $receiptId => $billAmount){
		$totalBillAmount+=$billAmount['id'];
	}
	$grandNetAmount = 0;
	foreach($netAmountArr as $receiptId => $netAmountValue){
		$grandNetAmount+=$netAmountValue;
	}
	$totalVat = $totalBillAmount-$grandNetAmount;
		if(
		(array_key_exists('start_date',$this->request->query) && !empty($this->request->query['start_date']) &&
		 array_key_exists('end_date',$this->request->query) && !empty($this->request->query['end_date'])) ||
		(array_key_exists('search_kw',$this->request->query) && !empty($this->request->query['search_kw']))
		){?>
		
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td style="float: right;">&nbsp;</td>
			<td></td>
			<td style="border-top: 1px solid;border-bottom: 1px solid;padding-left: 152px;" colspan="5"><strong>Total =</strong><?php echo $CURRENCY_TYPE.number_format($totalBillAmount,2);?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td style="float: right;">&nbsp;</td>
			<td></td>
			<td style="border-top: 1px solid;border-bottom: 1px solid;padding-left: 126px;" colspan="5"><strong>Total Qty=</strong><?=$total_qantity;?></td>
		</tr>
	<?php } ?>
	
	</tbody>
	</table>
	<div>*To view the detail of refund amount,  search from Product Payment screen for same date range</div>
	<div>** After refund value is sale amount after adjustment of refunds for displayed transactions</div>
	<div>***Duplicate receipt id amount is added to total only once</div></br></br>
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
        if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering
		?>
        <li><?=$this->element('tempered_side_menu')?></li>
        <?php }?>
		<li><?php echo $this->Html->link(__('View Invoices'), array('controller' => 'product_receipts', 'action' => 'all_invoices')); ?> </li>
		<li><?php echo $this->Html->link(__('View Sales'), array('controller' => 'kiosk_product_sales', 'action' => 'index')); ?> </li>
	</ul>
</div>
<script>
	function reset_search(){
		jQuery( "#datepicker1" ).val("");
		jQuery( "#datepicker2" ).val("");
		jQuery("#search_kw").val("");
		jQuery("#receipt_id").val("");
	}
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
    url: "/kioskProductSales/adminData?search=%QUERY",
     //url: "/products/admin-Data?category=%CID&search=%QUERY",
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
	  foreach ($kioskProductSales as $key => $kioskProductSale):
      $string = preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($products[$kioskProductSale->product_id]));
     if(empty($string)){
      $string = $products[$kioskProductSale->product_id];
      //htmlspecialchars($str, ENT_NOQUOTES, "UTF-8")
     }
      echo "jQuery('#tooltip_{$kioskProductSale->id}').tooltip({content:\"".htmlspecialchars($string)."\",track:true});";
     endforeach;
    ?>
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
	function addURL()
	{
		var href = document.getElementById("invoice").href;
		var k_id = $('#kioskid').val();
		var date1 = $('#datepicker1').val();
		var date2 = $('#datepicker2').val();
		var cat = $('#category_dropdown').val();
		var prod_code_or_name = $('#search_kw').val();
		var receipt_id = $('#receipt_id').val();
		var link = "";
		if (k_id == "" || k_id == null) {
            k_id = 10000;
        }
		if (date1 == "" || date1 == null) {
            date1 = "";
        }
		if (date2 == "" || date2 == null) {
            date2 = "";	
        }
		if (cat == "" || cat == null) {
            cat = "";
        }
		if (prod_code_or_name == "" || prod_code_or_name == null) {
            prod_code_or_name = "";
        }
		
		if (k_id) {
            link += "?ProductSale[kiosk_id]="+k_id;
        }
		if (date1) {
            link += "&start_date="+date1;
        }
		if (date2){ 
            link += "&end_date="+date2;
        }
		if (cat) {
			link += "&category[]="+cat;
        }
		if (prod_code_or_name) {
            link += "&search_kw="+prod_code_or_name;
        }
		if (receipt_id) {
            link += "&receipt_id="+receipt_id;
        }
		
		if (href) {
            var final_link = href + link;
            //alert(final_link);
			//href.setAttribute('href', link);
			document.getElementById("invoice").href=final_link;
			//alert(link);return false;
        }
		
		//var link = "?search_kw=&start_date="+date1+"&end_date="+date1+"&data[ProductSale][kiosk_id]="+k_id+"&submit=Search";
	}
</script>
<script>
   $('#kioskid').change(function(){
	$.blockUI({ message: 'Loading ...' });
	document.getElementById("search_form").submit();
	  }); 
</script>
<?php
//echo"hi";die;
?>