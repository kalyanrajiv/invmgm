<?php
//echo'hi';die;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<style>
 #remote .tt-dropdown-menu {max-height: 250px;overflow-y: auto;}
 #remote .twitter-typehead {max-height: 250px;overflow-y: auto;}
.tt-dataset, .tt-dataset-product {max-height: 250px;overflow-y: auto;}
.row_hover:hover{color:blue;background-color:yellow;}
</style>
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
		<li><?php echo $this->Html->link(__('View Sales'), array('controller' => 'kiosk_product_sales', 'action' => 'index')); ?> </li>
	</ul>
</div>
<?php
	$currency = Configure::read('$CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<?php 
	if(!isset($start_date)){$start_date = "";}
	if(!isset($end_date)){$end_date = "";}
	if(!isset($search_kw)){$search_kw = "";}
	if(!isset($receipt_id)){$receipt_id = "";}
	if(!isset($catagoryIds)){$catagoryIds = "";}
	$kiosks['-1'] = 'All';

//$queryStr = "";
//		$rootURL = $this->html->url('/', true);
//		if( isset($this->request->query['search_kw']) ){
//			//$queryStr.="?search_kw=".$this->request->query['search_kw'];
//			$queryStr.="?search_kw=".$this->request->query['search_kw']."&start_date=".$start_date."&end_date=".$end_date."&receipt_id=".$receipt_id;
//		}
		?>
        
<div class="kioskProductSales index">
    <form name= "search_form" id="search_form" action='<?php echo $this->request->webroot; ?>KioskProductSales/search_kiosk_sale' method = 'get'>
	<fieldset>
			<legend>Search</legend>
			<div style="height: 69px;">
				<table>
					<tr>
						<td><div id='remote'><input class="typeahead" type = "text" name = "search_kw" id = "search_kw" placeholder = "product name or code" autofocus style = "width:150px;height: 25px;"value='<?php echo $search_kw;?>'/></div></td>
						<td rowspan="3"><select id='category_dropdown' name='category[]' style="height: 97px;width: 151px;" multiple="multiple" size='6' onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select></td>
						<td><input type = "text" name = "receipt_id" id = "receipt_id" placeholder = "receipt id" autofocus style = "width:150px;height: 25px;"value='<?php echo $receipt_id;?>'/></td>
						<td><input type = "text" id='datepicker1' readonly='readonly' name = "start_date" placeholder = "From Date"  style = "width:90px;height: 25px;"value='<?php echo $start_date;?>' /></td>
						<td><input type = "text" id='datepicker2' readonly='readonly' name = "end_date" placeholder = "To Date"  style = "width:90px;height: 25px;" value='<?php echo $end_date;?>'  /></td>
	<?php	
	if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){?>
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
	 			
						<td><input type = "submit" value = "Search" name = "Submit" 'style' ='width:185px'/>
						<td><input type='button' name='reset' value='Reset' style='padding:6px 8px;color:#333; border:1px solid #bbb;border-radius:4px;' onClick='reset_search();'/></td>
						
					</tr>
				</table>
			
			</div></br>
			<?php /*if(count($this->request->query)){
				$netSale = $saleSum-$refundSum;
				$netSale = round($netSale,2);
				$saleSum = round($saleSum,2);
				$refundSum = round($refundSum,2);
				?>
			<?php }*/ ?>
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
	<?php
	
	if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){ ?>
    <h2><?php echo __('Kiosk Product Sales');?></h2>
	 **On mouse over of Refund Amt, we can  view user who have processed refunds </br>
	**On mouse over of Qty, we can view refund date 
		<?php //echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
		</a></h2>
				
	<?php }else{
	if(!isset($queryStr)){
	$queryStr = "";
	}?>
		<h2><?php echo __('Kiosk Product Sales'); ?>&nbsp;<a href='<?php echo $this->request->webroot;?>kiosk_product_sales/export/<?php echo $queryStr;?>' target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png',array('fullBase' => true));?></a></h2>
	<?php }?>
	<?php
	$qantity_total = $totalAmount = 0;
			foreach($orignal_amount as $id => $amount){
				$totalAmount = $amount+$totalAmount;
			}
	?>
	<span style="float: right;margin-top: -30px;margin-right: 25px; text-align: center;">
		<strong>Gross Sale</strong>&nbsp;<?=number_format($totalAmount,2);?>
		<strong>refund</strong>&nbsp;<?=number_format($refundData,2);?>
	<?php	$netSale = $totalAmount-$refundData; ?>
	
		<strong>after refund Sale</strong>&nbsp;<?=number_format($netSale,2);?><br/>
		<strong>Net Sale</strong>&nbsp;<?=number_format($netAmount,2);?>
        <strong>Vat</strong>&nbsp;<?=number_format($totalVat,2);?>
	</span>
	<table cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('product_id'); ?></th>
			<th><?php echo $this->Paginator->sort('product_id','Product Code'); ?></th>
			<th>Org Inv <br/>Amt</th>
			<th><?php echo $this->Paginator->sort('sale_price'); ?></th>
			<th>Billed Amount</th>
			
			<th>Refund </br>Amt</th>
			<th>Refund</br>Qty</th>
			
			<th><?php echo $this->Paginator->sort('sold_by'); ?></th>
			<th><?php echo $this->Paginator->sort('product_receipt_id','Recpt ID'); ?></th>
			<th><?php echo $this->Paginator->sort('created','Sold On'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	
	// pr($products);die;
	 	foreach($products as $productID => $productTitle){
            //pr($productTitle);die;
		  $products[$productTitle['id']] = $productTitle['product'];
		  $code[$productTitle['id']] = $productTitle['product_code'];
	}
	 
	?>
	
	<?php $totalOriginalAmount = 0;
		$receiptArray = array();
		$netAmountArr = array();
		
        //pr($recepitTableData);die;
		foreach ($kioskProductSales as $kioskProductSale): ?>
	<?php //pr($kioskProductSale['ProductReceipt']);
    //pr($kioskProductSale);die;
            if(!array_key_exists($kioskProductSale->product_receipt_id,$recepitTableData)){
                continue;
            }else{
                
            }
			if(array_key_exists($kioskProductSale->product_id,$products)){
				$product_title =  $products[$kioskProductSale->product_id];		
			}else{
				$product_title = "";
			}
            
	    $receiptArray[$kioskProductSale->product_receipt_id] = $recepitTableData[$kioskProductSale->product_receipt_id]['bill_amount'];
	 	//$receiptArray[$kioskProductSale['ProductReceipt']['id']] = $kioskProductSale['ProductReceipt']['bill_amount'];
		//$vat = $kioskProductSale['ProductReceipt']['vat'];
        $vat = $recepitTableData[$kioskProductSale->product_receipt_id]['vat'];
		$vatItem = $vat/100;
		//$netAmount = $kioskProductSale['ProductReceipt']['bill_amount']/(1+$vatItem);
        $netAmount = $recepitTableData[$kioskProductSale->product_receipt_id]['bill_amount']/(1+$vatItem);;
		$netAmountArr[$recepitTableData[$kioskProductSale->product_receipt_id]['id']] = $netAmount;
		if(array_key_exists($kioskProductSale->product_id,$products)){
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
		<td style="text-align: center;width: 83px;"><?php echo $orignal_amount[$kioskProductSale->product_receipt_id]; ?></td>
		<td><?php
		
		if($vat=='0'){
			$chargedAmount = round($discntPrice/(1+$actualVat/100),2);
		}else{
			$chargedAmount = $discntPrice;
		}
		$qantity_total += $kioskProductSale->quantity;
		
		//echo /*"&#163;".*/$this->Number->currency($chargedAmount,'BRL')." (X".$kioskProductSale['KioskProductSale']['quantity'].") =/* &#163;*/".$this->Number->currency($chargedAmount*$kioskProductSale['KioskProductSale']['quantity'],'BRL');
		//$chargedAmount*$kioskProductSale['KioskProductSale']['quantity'];
		echo  $CURRENCY_TYPE.$chargedAmount." (X".$kioskProductSale->quantity.") =".$CURRENCY_TYPE.$chargedAmount*$kioskProductSale->quantity;
		//$this->Number->currency($kioskProductSale['KioskProductSale']['sale_price'], '$');?>&nbsp;</td>
		
		
		<td>&#163;<?=$recepitTableData[$kioskProductSale->product_receipt_id]['bill_amount'];;?></td>
		<?php //---mycode-- ?>
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
						<?php
						$refund_date = "";
						if(array_key_exists($kioskProductSale->product_receipt_id,$new_refund_data)){
						 if(array_key_exists($kioskProductSale->product_id,$new_refund_data[$kioskProductSale->product_receipt_id])){
						  $refund_date =  $new_refund_data[$kioskProductSale->product_receipt_id][$kioskProductSale->product_id]['refund_date'];
						  $res_date = explode(";",$refund_date);
						  $refund_date1 = "";
						  $counter = 0;
						  foreach($res_date as $res_key => $res_value){
						   $counter ++;
						   if($counter == 1){
							$refund_date1 .= date("d-m-Y h:i:s",strtotime($res_value)); 
						   }else{
							$refund_date1 .= " , ".date("d-m-Y h:i:s",strtotime($res_value)); 
						   }
						  }
						  
						 }else{
						   $refund_date1 = "";
						 }
						}else{
						  $refund_date1 = "";
						}
						?>
						<td title="<?php echo $refund_date1;?>">
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
		<?php //--mycode-- ?>
		<td><?php if(array_key_exists($recepitTableData[$kioskProductSale->product_receipt_id]['processed_by'],$users)){
				echo $users[$recepitTableData[$kioskProductSale->product_receipt_id]['processed_by']];
			}else{
				echo "--";
			} ?>&nbsp;</td>
		 
		<td>
			<?php
			if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
			$this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
			$this->request->session()->read('Auth.User.user_type')=='wholesale'
			){
			     echo $this->Html->link($kioskProductSale->product_receipt_id, array('controller' => 'product_receipts', 'action' => 'generate_receipt_kiosk_sale', $kioskProductSale->product_receipt_id,$kiosk_id));
			}elseif($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS &&
			$this->request->session()->read('Auth.User.user_type')=='retail'){
			     echo $this->Html->link($kioskProductSale->product_receipt_id, array('controller' => 'product_receipts', 'action' => 'view', $kioskProductSale->product_receipt_id, $kiosk_id));
			}
			
			
			?>
		</td>
		<td><?php echo date('d-m-y h:i:s',strtotime($kioskProductSale->created));//$this->Time->format('jS M, Y h:i A', $kioskProductSale->created,null,null);?>&nbsp;</td>		
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
	$totalVat = $totalBillAmount-$grandNetAmount;
		if(
		(array_key_exists('start_date',$this->request->query) && !empty($this->request->query['start_date']) &&
		 array_key_exists('end_date',$this->request->query) && !empty($this->request->query['end_date'])) ||
		(array_key_exists('search_kw',$this->request->query) && !empty($this->request->query['search_kw']))
		){ ?>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td style="background-color: yellow;text-align: center;" colspan="1"><?php echo "total = ".$totalAmount;?></td>
			
			<td style="border-top: 1px solid;border-bottom: 1px solid;" colspan="5"><strong>VAT =</strong><?=round($totalVat,2);?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td style="float: right;" colspan="2">&nbsp;</td>
			<td>These figer are per Page :-</td>
			<td style="border-top: 1px solid;border-bottom: 1px solid;" colspan="5"><strong>Net Amount =</strong><?=round($grandNetAmount,2);?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td style="float: right;">&nbsp;</td>
			<td style="border-top: 1px solid;border-bottom: 1px solid;" colspan="5"><strong>Total =</strong><?=$totalBillAmount ;?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td style="float: right;">&nbsp;</td>
			<td style="border-top: 1px solid;border-bottom: 1px solid;" colspan="5"><strong>Total Quantity=</strong><?=$qantity_total;?></td>
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
</div>

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
			url: "/kiosk_product_sales/admin_data_with_qty?category=%CID&search=%QUERY",
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