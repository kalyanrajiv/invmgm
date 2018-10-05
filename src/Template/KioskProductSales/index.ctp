<?php
//echo'hi';die;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<?php
	//echo"hi";die;
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<?php 
if(!isset($start_date)){$start_date = date('d M Y');}
if(!isset($end_date)){
	$end_date = date('d M Y');
	}
if(!isset($search_kw)){$search_kw = "";}
if(!isset($receipt_id)){$receipt_id = "";}
$queryStr = "";
		$rootURL = "";//$this->html->url('/', true);
		if( isset($this->request->query['search_kw']) ){
			//$queryStr.="?search_kw=".$this->request->query['search_kw'];
			$queryStr.="?search_kw=".$this->request->query['search_kw']."&start_date=".$start_date."&end_date=".$end_date."&receipt_id=".$receipt_id;
		}
		?>
<div class="kioskProductSales index">
	<fieldset>
		<legend>Search</legend>
		<form action='<?php echo $this->request->webroot; ?>KioskProductSales/searchsale' method='get'>
		<table>
			<tr>
				<td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date" style = "width:106px;height: 27px;" value='<?php echo $start_date;?>' /></td>
				<td><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date" style = "width:106px;height: 27px;" value='<?php echo $end_date;?>' /></td>
				<td><div id='remote'><input type="text" class="typeahead" name="search_kw" id = 'search_kw' placeholder="product or product_code" autofocus style = "width:200" value="<?php echo $search_kw;?>" /></div> </td>
				<td><input type="text" name="receipt_id" id = 'receipt_id' placeholder="receipt id" autofocus style = "width:150px" value="<?php echo $receipt_id;?>"</td>
				<td><input type="submit" name="submit" value="Search Sales"></td>
				<td><input type='button' name='reset' value='Reset Search' style='padding:4px 8px;background:#dcdcdc;color:#333;border:1px solid #bbb;border-radius:4px;' onClick='reset_search();'/></td>
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
		if(array_key_exists($kiosk_id,$kiosks)){
			$kiosk_name = $kiosks[$kiosk_id];	
		}else{
			$kiosk_name = "";
		}
		
		?>
		<h2><?php echo __("$kiosk_name Sales")."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>&nbsp;<a href='<?php echo $this->request->webroot;?>KioskProductSales/export/<?php echo $queryStr;?>' target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png',array('fullBase' => true));?>
		<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
		</a></h2>
				
	<?php }else{?>
		<h2><?php echo __('Kiosk Product Sales'); ?>&nbsp;<a href='<?php echo $this->request->webroot;?>KioskProductSales/export/<?php echo $queryStr;?>' target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png',array('fullBase' => true));?></a></h2>
	<?php }?>
	<?php
	
            //pr($orignal_amount);//die;
			$totalAmount = 0;
			foreach($orignal_amount as $id => $amount){
				$totalAmount = $amount+$totalAmount;
			}
			
	?>
	
	<span style="float: right;margin-top: -30px;margin-right: 25px; text-align: center;">
		<strong>Gross Sale</strong>&nbsp;<?=$CURRENCY_TYPE.number_format($totalAmount,2);?>
		<strong>refund</strong>&nbsp;<?=$CURRENCY_TYPE.number_format($refundData,2);?>
	<?php	$netSale = $totalAmount-$refundData; ?>
	<strong>Vat</strong>&nbsp;<?=$CURRENCY_TYPE.number_format($totalVat,2);?>
		<strong>after refund Sale</strong>&nbsp;<?=$CURRENCY_TYPE.number_format($netSale,2);?><br/>
		<strong>Net Sale</strong>&nbsp;<?=$CURRENCY_TYPE.number_format($netAmount,2);?>
	</span>
	<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('product_id'); ?></th>
			<th><?php echo $this->Paginator->sort('product_id','Code'); ?></th>
			<th>Org Inv <br/>Amt</th>
			<th><?php echo $this->Paginator->sort('sale_price'); ?></th>
			<?php if((
				$this->request->session()->read('Auth.User.user_type') != 'retail')
			   ){ ?>
			<th>Blk Dist</th>
			<th>After Blk </br> Dist value</th>
			<?php } ?>
			<th>Billed </br>Amt</th>
			<?php if((
				$this->request->session()->read('Auth.User.user_type') == 'retail')
			   ){ ?>
			<th>Refund </br>Amt</th>
			<th>Refund</br>Qty</th>
			<?php } ?>
			<th><?php echo $this->Paginator->sort('sold_by'); ?></th>
			<th><?php echo $this->Paginator->sort('product_receipt_id','Recpt ID'); ?></th>
			<th><?php echo $this->Paginator->sort('created','Sold On'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	
//	$products1 = array();
//	 	foreach($products as $productID => $productTitle){
//            //pr($productTitle);die;
//			//unset($products[$productID]);
//		  $products1[$productTitle['id']] = $productTitle['product'];
//		  $code[$productTitle['id']] = $productTitle['product_code'];
//		  //unset($products[$productID]['product_code']);
//		  //unset($products[$productID]['product']);
//	}
	 
	?>
	
	<?php $total_qantity = $totalOriginalAmount = 0;
		$receiptArray = array();
		$netAmountArr = array();
		foreach ($kioskProductSales as $kioskProductSale): ?>
	<?php //pr($kioskProductSale);die;
    //pr($kioskProductSale);die;
			if(array_key_exists($kioskProductSale->product_id,$products)){
				$product_title =  $products[$kioskProductSale->product_id];		
			}else{
				$product_title = "";
			}
	    $total_qantity += $kioskProductSale->quantity;
        //pr($recepitTableData);die;
	 	//$receiptArray[$kioskProductSale['ProductReceipt']['id']] = $kioskProductSale['ProductReceipt']['bill_amount'];
        $receiptArray[$kioskProductSale->product_receipt_id] = $recepitTableData[$kioskProductSale->product_receipt_id]['bill_amount'];
		$vat = $recepitTableData[$kioskProductSale->product_receipt_id]['vat'];
		$vatItem = $vat/100;
		$netAmount = $recepitTableData[$kioskProductSale->product_receipt_id]['bill_amount']/(1+$vatItem);
		$netAmountArr[$kioskProductSale->product_receipt_id] = $netAmount;
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
	<?php
	if(array_key_exists($kioskProductSale->product_receipt_id,$new_refund_data)){
		
		if(array_key_exists($kioskProductSale->product_id,$new_refund_data[$kioskProductSale->product_receipt_id])){
			echo "<tr style='background-color: yellow;'>";	
		}else{
			echo "<tr>";
		}
		
	}else{
		echo "<tr>";
	}
	?>
		<td><?php echo h($kioskProductSale->id); ?>&nbsp;</td>		
		<td>
		<?php
		//echo "hi".$kioskProductSale->product_id;die;
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
		<td><?php
		if(array_key_exists($kioskProductSale->product_receipt_id,$orignal_amount)){
			echo number_format($orignal_amount[$kioskProductSale->product_receipt_id],2);
		}
		?></td>
		<td><?php
		if($vat=='0'){
			$chargedAmount = number_format($discntPrice/(1+$actualVat/100),2);
		}else{
			$chargedAmount = $discntPrice;
		}
		//pr($users);die;
		$amt = $chargedAmount*$kioskProductSale->quantity;
		$amt = number_format($amt,2);
		echo  $CURRENCY_TYPE.number_format($chargedAmount,2)." (X".$kioskProductSale->quantity.") =".$CURRENCY_TYPE.$amt;
		//$this->Number->currency($kioskProductSale['KioskProductSale']['sale_price'], '$');?>&nbsp;</td>
		<?php if((
				$this->request->session()->read('Auth.User.user_type') != 'retail')
			   ){ ?>
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
		<td><?php
		$amt_to_show = $amt_to_show*$kioskProductSale->quantity;
		echo number_format($amt_to_show,2);?></td>
		<?php } ?>
		<td>&#163;<?=number_format($recepitTableData[$kioskProductSale->product_receipt_id]['bill_amount'],2);?></td>
		<?php if((
				$this->request->session()->read('Auth.User.user_type') == 'retail')
			   ){  ?>
		
		<?php 	if(array_key_exists($kioskProductSale->product_receipt_id,$new_refund_data)){
		
					if(array_key_exists($kioskProductSale->product_id,$new_refund_data[$kioskProductSale->product_receipt_id])){
						$refund_by =  $new_refund_data[$kioskProductSale->product_receipt_id][$kioskProductSale->product_id]['refund_by'];	
					}else{
						$refund_by =  "--";
					}
					
				}else{
					$refund_by =  "--";
				}?>
		<td title="<?php echo $refund_by;?>">
		<?php 	if(array_key_exists($kioskProductSale->product_receipt_id,$new_refund_data)){
		
					if(array_key_exists($kioskProductSale->product_id,$new_refund_data[$kioskProductSale->product_receipt_id])){
						echo "<b>".$new_refund_data[$kioskProductSale->product_receipt_id][$kioskProductSale->product_id]['refund_price']."<b>";	
					}else{
						echo "--";
					}
					
				}else{
					echo "--";
				}?>
		 </td>
		<td>
		<?php 	if(array_key_exists($kioskProductSale->product_receipt_id,$new_refund_data)){
		
					if(array_key_exists($kioskProductSale->product_id,$new_refund_data[$kioskProductSale->product_receipt_id])){
						echo $new_refund_data[$kioskProductSale->product_receipt_id][$kioskProductSale->product_id]['quantity'];	
					}else{
						echo "--";
					}
					
				}else{
					echo "--";
				}?>
		 </td>
		
		<?php } ?>
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
			     echo $this->Html->link($kioskProductSale->product_receipt_id, array('controller' => 'product_receipts', 'action' => 'generate_receipt', $kioskProductSale->product_receipt_id));
			}elseif($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
			$this->request->session()->read('Auth.User.user_type')=='retail'){
			     echo $this->Html->link($kioskProductSale->product_receipt_id, array('controller' => 'product_receipts', 'action' => 'view', $kioskProductSale->product_receipt_id, $kiosk_id));
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
		$totalBillAmount+=$billAmount;
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
			<td style="background-color: yellow;">total = </td>
			<td style="float: left;background-color: yellow;" colspan="2"><?php echo $totalAmount;?></td>
			<td style="border-top: 1px solid;border-bottom: 1px solid;" colspan="4"><strong>VAT =</strong><?=round($totalVat,2);?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td style="float: right;" colspan="2">&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td style="border-top: 1px solid;border-bottom: 1px solid;" colspan="5"><strong>Net Amount =</strong><?=round($grandNetAmount,2);?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td style="float: right;">&nbsp;</td>
			<td style="border-top: 1px solid;border-bottom: 1px solid;" colspan="5"><strong>Total =</strong><?=$totalBillAmount."(after refund value)";?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td style="float: right;">&nbsp;</td>
			<td style="border-top: 1px solid;border-bottom: 1px solid;" colspan="5"><strong>Total Qty=</strong><?=$total_qantity;?></td>
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
	 <?php  if($this->request->session()->read('Auth.User.group_id')== ADMINISTRATORS){?>	 	
	 <li><?php echo $this->Html->link(__('All Kiosk Sale'), array('controller' => 'kiosk_product_sales', 'action' => 'all_kiosk_sale')); ?></li>
	 <li><?php echo $this->Html->link(__('All WholeSale Sale'), array('controller' => 'kiosk_product_sales', 'action' => 'all_wholesale_kiosk_sale')); ?></li>
	<li><?php echo $this->Html->link(__('Kiosk Sale Stat'), array('controller' => 'ProductSellStats', 'action' => 'index')); ?></li>
    <?php } ?>
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
    //url: "/kiosk_product_sales/admin_data?search=%QUERY",
     url: "/products/admin-Data?category=%CID&search=%QUERY",
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