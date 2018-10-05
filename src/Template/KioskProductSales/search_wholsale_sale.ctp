<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<?php 
if(!isset($start_date)){$start_date = "";}
	if(!isset($end_date)){$end_date = "";}
if(!isset($search_kw)){$search_kw = "";}
if(!isset($receipt_id)){$receipt_id = "";}
$cat_str = "";
if(array_key_exists("category",$this->request->query)){
	$cat_arr = $this->request->query['category'];
	$cat_str = implode("_",$cat_arr);
}
$kiosks['-1'] = 'All';
$queryStr = "";
		//$rootURL = $this->request->webroot;
		if( isset($this->request->query['search_kw']) || (isset($this->request->query["ProductSale"]) && (isset($this->request->query["ProductSale"]["kiosk_id"])))){
			//$queryStr.="?search_kw=".$this->request->query['search_kw'];
			if(isset($this->request->query['search_kw'])){
				$queryStr.="?search_kw=".$this->request->query['search_kw']."&start_date=".$start_date."&end_date=".$end_date."&receipt_id=".$receipt_id."&kiosk_id=".$kiosk_id."&type=0";
			}else{
				$queryStr.="?start_date=".$start_date."&end_date=".$end_date."&receipt_id=".$receipt_id."&kiosk_id=".$kiosk_id."&type=0";		
			}
			//$queryStr.="?search_kw=".$this->request->query['search_kw']."&start_date=".$start_date."&end_date=".$end_date."&receipt_id=".$receipt_id;
		}
		if(!empty($queryStr)){
			if(!empty($cat_str)){
				$queryStr.= "&category=".$cat_str;
			}	
		}else{
			if(!empty($cat_str)){
				$queryStr= "?category=".$cat_str;
			}
		}
		
		?>
        
<div class="kioskProductSales index">
    <form name= "search_form" id="search_form" action='<?php echo $this->request->webroot; ?>KioskProductSales/search_wholsale' method = 'get'>
	<fieldset>
			<legend>&raquo;Search</legend>
			<div style="height: 69px;">
				<table>
					<tr>
						<td>
							<table>
								<tr><td><div id='remote'><input class="typeahead" type = "text" name = "search_kw" id = "search_kw" placeholder = "product name or code" autofocus style = "width:150px;height: 25px;"value='<?php echo $search_kw;?>'/></div></td></tr>
								<tr><td><input type = "text" name = "receipt_id" id = "receipt_id" placeholder = "receipt id" autofocus style = "width:150px;height: 25px;"value='<?php echo $receipt_id;?>'/></td></tr>
							</table>
						</td>
						<td rowspan="3"><select id='category_dropdown' name='category[]' style="height: 97px;width: 151px;" multiple="multiple" size='6' onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select></td>
						
						<td>
							<table>
								<tr><td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date"  style = "width:90px;height: 25px;"value='<?php echo $start_date;?>' /></td>
						<td><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date"  style = "width:90px;height: 25px;" value='<?php echo $end_date;?>'  /></td></tr>
								<?php $loggedInUser = $this->request->session()->read('Auth.User.username');
									if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering
										echo "<tr><td colspan='2'>";
										echo $this->Html->link(__('View Quotation sales'), array('controller' => 'kiosk_product_sales', 'action' => 'dr_searchsale'),array('id' => 'quotation_link'));
										echo "</br>(Right click will not work for above link.)";
										echo "</td></tr>";
									 }
								 ?>
							</table>
						</td>product name or code
						
	<?php	
	if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == SALESMAN){?>
						<td>
							<?php
								if(!empty($kiosk_id)){
									echo $this->Form->input(null, array(
										'options' => $kiosks,
										 'label' => false,
										 'div' => false,
										       'name' => 'ProductSale[kiosk_id]',
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
											 'name' => 'ProductSale[kiosk_id]',
											'id'=> 'kioskid',
											//'empty' => 'Select Kiosk',
											'style' => 'width:185px'
												)
											);
								      }
								?></span>
						</td>
							<?php  }  ?>
	 			
						<td>
							<table>
									<tr><td><input type = "submit" value = "Search" name = "Submit" 'style' ='width:185px'/></td></tr>
									<tr><td><input type='button' name='reset' value='Reset' style='padding:6px 8px;color:#333; border:1px solid #bbb;border-radius:4px;' onClick='reset_search();'/></td></tr>
							</table>
						
						
					</tr>
				</table>
			
			</div></br>
		</fieldset>
    </form>
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
    <h2><?php echo __("$kiosk_name Product Sales");?>&nbsp;<a href='<?php echo $this->request->webroot;?>kiosk-product-sales/export/<?php echo $queryStr;?>' target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png',array('fullBase' => true));?></a></h2>
				
	<?php }else{?>
		<h2><?php echo __('Kiosk Product Sales'); ?>&nbsp;<a href='<?php echo $this->request->webroot;?>kiosk-product-sales/export/<?php echo $queryStr;?>' target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png',array('fullBase' => true));?></a></h2>
	<?php }?>
	<?php
	$qantity_total = $totalAmount = 0;
			foreach($orignal_amount as $id => $amount){
				$totalAmount = $amount+$totalAmount;
			}
	?>
	<span style="float: right;margin-top: -30px;margin-right: 25px; text-align: center;">
		<strong>Gross Sale</strong>&nbsp;<?=$CURRENCY_TYPE.number_format($totalAmount,2);?>
	<?php	$netSale = $totalAmount-$refundData;?>
	
		<strong>Net Sale</strong>&nbsp;<?=$CURRENCY_TYPE.number_format($netAmount,2);?>
        <strong>Vat</strong>&nbsp;<?=$CURRENCY_TYPE.number_format($totalVat,2);?>
	</span>
	<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('product_id'); ?></th>
			<th><?php echo $this->Paginator->sort('product_id','Code'); ?></th>
			<th>Org Inv <br/>Amt</th>
			<th><?php echo $this->Paginator->sort('sale_price','SP exec VAT'); ?></th>
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
	// pr($products);die;
    
	 	foreach($products as $productID => $productTitle){
		  $products[$productTitle['id']] = $productTitle['product'];
		  $code[$productTitle['id']] = $productTitle['product_code'];
	}
	 
	?>
	
	<?php $totalOriginalAmount = 0;
		$receiptArray = array();
		$netAmountArr = array();
		// pr($kioskProductSales);
		foreach ($kioskProductSales as $kioskProductSale): ?>
	<?php //pr($kioskProductSale['ProductReceipt']);
    //pr($kioskProductSale);die;
			if(array_key_exists($kioskProductSale->product_id,$products)){
				$product_title =  $products[$kioskProductSale->product_id];
			}else{
				$product_title = "";
			}
	    
	 	//$receiptArray[$kioskProductSale['ProductReceipt']['id']] = $kioskProductSale['ProductReceipt']['bill_amount'];
        $receiptArray[$kioskProductSale->product_receipt_id] = $recepitTableData[$kioskProductSale->product_receipt_id]['bill_amount'];
		$vat = $recepitTableData[$kioskProductSale->product_receipt_id]['vat'];
		$vatItem = $vat/100;
		$netAmount = $recepitTableData[$kioskProductSale->product_receipt_id]['bill_amount']/(1+$vatItem);
		$netAmountArr[$kioskProductSale->product_receipt_id] = $netAmount;
		if(array_key_exists($kioskProductSale['product_id'],$products)){
			$truncatedProduct = \Cake\Utility\Text::truncate(
				$products[$kioskProductSale->product_id],
				50,['ellipsis' => '...','exact' => false]
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
				if(array_key_exists($kioskProductSale->product_id,$code)){
					echo $code[$kioskProductSale->product_id];
				}?>
		</td>
		<td><?php  echo number_format($orignal_amount[$kioskProductSale->product_receipt_id],2); ?></td>
		<td><?php
		//echo $discntPrice;
		if($vat=='0'){
			//$chargedAmount = round($discntPrice/(1+$actualVat/100),2);
			$chargedAmount = $discntPrice;
		}else{
			//echo $discntPrice;
			$chargedAmount = number_format($discntPrice/(1+$actualVat/100),2);
			//$chargedAmount = $discntPrice;
		}
		
		$qantity_total += $kioskProductSale->quantity;
		
		//echo /*"&#163;".*/$this->Number->currency($chargedAmount,'BRL')." (X".$kioskProductSale['KioskProductSale']['quantity'].") =/* &#163;*/".$this->Number->currency($chargedAmount*$kioskProductSale['KioskProductSale']['quantity'],'BRL');
		//$chargedAmount*$kioskProductSale['KioskProductSale']['quantity'];
		$amt_s = $discntPrice*$kioskProductSale->quantity;
		$amt_s = number_format($amt_s,2);
		echo   $CURRENCY_TYPE.number_format($discntPrice,2)." (X".$kioskProductSale->quantity.") =".$CURRENCY_TYPE.$amt_s;
		//$this->Number->currency($kioskProductSale['KioskProductSale']['sale_price'], '$');?>&nbsp;</td>
		
		<td><?php
		$without_bulk_dis = $discntPrice;
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
		<td><?php
		$amt_to_show = $amt_to_show*$kioskProductSale->quantity;
		echo number_format($amt_to_show,2);?></td>
		
		
		<td>&#163;<?=number_format($recepitTableData[$kioskProductSale->product_receipt_id]['bill_amount'],2);?></td>
		<td><?php if(array_key_exists($recepitTableData[$kioskProductSale->product_receipt_id]['processed_by'],$users)){
           // echo $recepitTableData[$kioskProductSale->product_receipt_id]['processed_by'];die;
           //pr($users);die;
				echo $users[$recepitTableData[$kioskProductSale->product_receipt_id]['processed_by']];
			}else{
				echo "--";
			} ?>&nbsp;</td>
		 
		<td>
			<?php
			if($this->request->session()->read('Auth.User.group_id') ||
			$this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
			$this->request->session()->read('Auth.User.user_type')=='wholesale'
			){ if($kiosk_id == 10000){
				$kiosk_id = -1;
			}
			     echo $this->Html->link($kioskProductSale->product_receipt_id, array('controller' => 'product_receipts', 'action' => 'generate-receipt', $kioskProductSale->product_receipt_id,$kiosk_id));
			}elseif($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
			$this->request->session()->read('Auth.User.user_type')=='retail'){
			     echo $this->Html->link($kioskProductSale->product_receipt_id, array('controller' => 'product_receipts', 'action' => 'view', $kioskProductSale->product_receipt_id, $kiosk_id));
			}
			
			
			?>
		</td>
		<td><?php echo date('d-m-y h:i:s',strtotime($kioskProductSale->created)); //$this->Time->format('jS M, Y h:i A', $kioskProductSale->created,null,null);?> &nbsp;</td>		
	</tr>
<?php endforeach; ?>
	<?php
	$totalBillAmount = 0;
	//pr($receiptArray);
	foreach($receiptArray as $receiptId => $billAmount){
		$totalBillAmount+=$billAmount;
	}
	$grandNetAmount = 0;
	foreach($netAmountArr as $receiptId => $netAmountValue){
		$grandNetAmount+=$netAmountValue;
	}
    //pr($grandNetAmount);die;
	$totalVat = $totalBillAmount-$grandNetAmount;
    //pr($totalVat);die;
    //pr($totalBillAmount);die;
		if(
		(array_key_exists('start_date',$this->request->query) && !empty($this->request->query['start_date']) &&
		 array_key_exists('end_date',$this->request->query) && !empty($this->request->query['end_date'])) ||
		(array_key_exists('search_kw',$this->request->query) && !empty($this->request->query['search_kw']))
		){?>
		<tr>
			<td>&nbsp;</td><td>&nbsp;</td><td style="background-color: yellow;"> <b>Total orig <br>invoice amt</b></td>
			<td style="background-color: yellow;" colspan="1"> 
            <?php
           // 
            echo number_format($totalAmount,2);?>
            </td>
			<td>&nbsp;</td>
			<td style="border-top: 1px solid;border-bottom: 1px solid;padding-left: 74px;" colspan="5"><strong>VAT =</strong><?=number_format($totalVat,2);?></td>
		</tr>
		<tr>
			<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
			<td style="float: right;" colspan="2">&nbsp;</td>
			<td>These figer are per Page :-</td>
			<td style="border-top: 1px solid;border-bottom: 1px solid;padding-left: 2px;" colspan="6"><strong>Ex Vat amount =</strong> <?=number_format($grandNetAmount,2);?> </td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
			<td style="float: right;">&nbsp;</td>
			<td style="border-top: 1px solid;border-bottom: 1px solid;padding-left: 7px;" colspan="6"><strong>Gross Amount =</strong><?=number_format($totalBillAmount,2) ;?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
			<td style="float: right;">&nbsp;</td>
			<td style="border-top: 1px solid;border-bottom: 1px solid;padding-left: 6px;" colspan="6"><strong>Total Quantity=</strong><?=$qantity_total;?></td>
		</tr>
	<?php } ?>
	
	</tbody>
	</table>
	 <?php if($this->request->session()->read('Auth.User.user_type') == 'retail'){?>
	<div>*To view the detail of refund amount,  search from Product Payment screen for same date range</div>
	<div>** After refund value is sale amount after adjustment of refunds for displayed transactions</div>
	<div>***Duplicate receipt id amount is added to total only once</div></br></br>
	<p>
        <?php }?>
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
		 	
            <li><?php echo $this->Html->link(__('All Kiosk Sale'), array('controller' => 'kiosk_product_sales', 'action' => 'all_kiosk_sale')); ?></li>
            <li><?php echo $this->Html->link(__('All WholeSale Sale'), array('controller' => 'kiosk_product_sales', 'action' => 'all_wholesale_kiosk_sale')); ?></li>
            <li><?php echo $this->Html->link(__('View Sales'), array('controller' => 'kiosk_product_sales', 'action' => 'index')); ?></li>
             <li><?php echo $this->Html->link(__('Kiosk Sale Stat'), array('controller' => 'ProductSellStats', 'action' => 'index')); ?></li> 
		<?php
        $loggedInUser = $this->request->session()->read('Auth.User.username');
        if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering
		?>
        <li><?=$this->element('tempered_side_menu')?></li>
        <?php }?>
		<li><?php echo $this->Html->link(__('View Invoices'), array('controller' => 'product_receipts', 'action' => 'all_invoices')); ?> </li>
		 </ul>
</div>
<input type='hidden' name='url_category' id='url_category' value=''/>
<script>
	function reset_search(){
		jQuery( "#datepicker1" ).val("");
		jQuery( "#datepicker2" ).val("");
		jQuery("#search_kw").val("");
		jQuery("#receipt_id").val("");
		jQuery("#category_dropdown").val("");
		jQuery("#kioskid").val("-1");
	}
jQuery(function() {
	jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
	jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
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
		remote: {
			url: "/kioskProductSales/adminData?category=%CID&search=%QUERY",
			replace: function (url,query) {
				var multipleValues = $( "#category_dropdown" ).val() || [];
				$('#url_category').val(multipleValues.join( "," ));
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
   $('#kioskid').change(function(){
	$.blockUI({ message: 'Loading ...' });
	document.getElementById("search_form").submit();
	  }); 
</script>
<script>
	$(document).on('click', '#quotation_link', function() {
		var url = $(this).attr('href');
		var search_kw = $("#search_kw").val();
		url += "?"
		if (search_kw != "") {
         url +="search_kw="+search_kw;    
        }
		var datepicker1 = $("#datepicker1").val();
		if (datepicker1 != "") {
            url +="&start_date="+datepicker1;    
        }else{
			url +="&start_date=";    
		}
		var datepicker2 = $("#datepicker2").val();
		if (datepicker2 != "") {
            url +="&end_date="+datepicker2;    
        }else{
			url +="&end_date=";    
		}
		var kioskid = $("#kioskid").val();
		if (kioskid != "") {
			if (kioskid == -1) {
                kioskid = 10000;
            }
            url +="&kiosk_id="+kioskid;    
        }
		var receipt_id = $("#receipt_id").val();
		if (receipt_id != "") {
            url +="&receipt_id="+receipt_id;    
        }
		var drop_down = $('#category_dropdown').val();
		
		if (drop_down != "" && drop_down != null) {
			var cat_val = drop_down.toString().split(',');
			$.each( cat_val, function( key, value ) {
				url += "&category[]="+value;
			});    
        }
		$('#quotation_link').attr('href',url);
		});
</script>