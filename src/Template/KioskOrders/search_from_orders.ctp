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
.label_radio{
	height: 31px;
}
</style>
<?php
 
use Cake\View\Helper\UrlHelper;
use Cake\View\Helper\FormHelper;
?>
<div class="mobilePurchases index">
	<h2><?php 
	if(!isset($start_date)){$start_date = "";}
	if(!isset($end_date)){$end_date = "";}
	$selectedcase = "";
	$searchKeyword = "";
	$selectedKiosk = 0;
	$chosenType = "all";
    //pr($this->request->query);die;
	if(!empty($this->request->query['cases'])){
		$selectedcase = $this->request->query['cases'];
	}
	if(!empty($this->request->query['search_kw'])){
		$searchKeyword = $this->request->query['search_kw'];
	}
	if(!empty($this->request->query['kiosks'])){
		$selectedKiosk = $this->request->query['kiosks'];
	}
	if(!empty($this->request->query['type'])){
		$chosenType = $this->request->query['type'];
	}
	$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
      $updateUrl = "/img/16_edit_page.png";	
	echo __('Global Orders Search')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>";?>
	<?php  echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
</h2>
     
	<?php
    
    echo $this->Form->create('GlobalOrderSearch',[
                                                        'url' => ['controller'=>'kiosk_orders','action' => 'search_globally'],
                                                        'type' => 'get'
                                                        ]
                                   );
	$options = array(
		'all' => 'All',
		'out_of_stock' => 'Out of stock items',
		'received_less' => 'Received less items',
		'received_more' => 'Received more items',
		'on_demand' => 'Extra Stock Required(On Demand Orders)',
	);
	
	$attributes = array(
		'legend' => false,
		'value' => $chosenType,
		'label' => ['style'=>'height:32px;']
	);
	
	if(!empty($kioskName)){
		$kioskName = array(0 => "All") + $kioskName;
	}
	echo "<table>
		<tr>
			<td>".$this->Form->input('cases',array('options'=>$cases,'label'=>false,'default'=>$selectedcase))."</td>
			<td rowspan='2'>".$this->Form->radio('type', $options, $attributes)."</td>
		</tr>";
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
		$this->request->session()->read('Auth.User.group_id') == MANAGERS ||
		$this->request->session()->read('Auth.User.group_id') == inventory_manager){
		echo "<tr>
			<td>".$this->Form->input('kiosks',array('options'=>$kioskName,'label'=>'Kiosks','default'=>$selectedKiosk))."</td>
		</tr>";
		}
		echo "<tr>
        <td><div id='remote'><input class='typeahead' type = 'text' name = 'search_kw' value = '$searchKeyword' style = 'width:343px' autofocus= 'ture' placeholder ='product,product-code' /></div></td>
			 
			<input type = 'text' id='datepicker1' readonly='readonly' name = 'start_date' placeholder = 'From Date' style = 'width:100px;margin-top: 5px;' value='".$start_date."' />
			<input type = 'text' id='datepicker2' readonly='readonly' name = 'end_date' placeholder = 'To Date' style = 'width:100px' value='".$end_date."' />	
			</div></td>";?>
      
	<?php
    //echo $import =
	$import = $this->Url->build(["controller" => "kiosk-orders","action" => "search_cancel"]);
	
	   $import .= "?search_kw=".$searchKeyword;
	   
	   $import .= "&start_date=".$start_date;
	   
	   $import .= "&end_date=".$end_date;
	   
	   $import .= "&cancel[kiosk_id]=".$selectedKiosk;
	
    // $import = $this->Html->url(array('controller' => 'kiosk_orders', 'action' => 'cancel_product')); ?>
	<td><a href="<?=$import;?>" >Cancel Products</a></td>
		<?php echo "</tr>";?>
	<td></td>
	<td><span id="show_link"><a href="#-1">Help</a></span>
	<span id="show_data">
	 <div>
	  <b>Out of stock items: </b> Items which are transfered with zero quantity. </br>
	  <b>Received less items: </b>Received less products are showing those records, which warehouse have dispatched less than placed/requested quantity by kiosk.</br>
	  <b>Received more items : </b>Received more products are showing those records, which warehouse have dispatched more than placed/requested quantity by kiosk.</br>
	  <b>Extra Stock Required: </b> On Demand Order items.
	  </div>
	</span>
	</td>	 
	<tr>
		<td colspan="2">
			<table>
				<tr>
					<td style="width: 1px;"><input type='button' name='reset' value='Reset Search' style='padding:4px 8px;background:#dcdcdc;color:#333;border:1px solid #bbb;border-radius:4px;width: 120px; ' onClick='reset_search();'/></td>
					 
                    <td > <?= $this->Form->button(__('Search',['style'=>"margin-top: 24px;height: 30px;width: 69px;"]),array('name'=>'submit')) ?>
    <?= $this->Form->end() ?></td>
				</tr>
			</table>
	
	<?php echo "</td></tr></table>";
	?>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th>Product Code</th>
		<th>Product Title</th>
		<th><?php echo $this->Paginator->sort('kiosk_order_id'); ?></th>
		<th>Placed By</th>
		<th><?php echo $this->Paginator->sort('created'); ?></th>
		<th>Kiosk Name</th>
		<th>Ordered Quantity</th>
		<th>Dispatched Quantity</th>
		<th>Remarks</th>
	</tr>
	</thead>
	<tbody><?php $total_quantity = 0;
	foreach($stockTransfer as $key=>$stockTransferDetails){
       
			if(!array_key_exists($stockTransferDetails['product_id'],$transientW2KproductCodeArr)){
				$productCode = "--";
			}else{
				$productCode = $transientW2KproductCodeArr[$stockTransferDetails['product_id']];
			}
			
			if(!array_key_exists($stockTransferDetails['product_id'],$transientW2KproductTitleArr)){
				$productName = "--";
			}else{
				$productName = $transientW2KproductTitleArr[$stockTransferDetails['product_id']];
			}
			 
			if(array_key_exists($stockTransferDetails['kiosk_order_id'],$relatedKioskPlacedOrder )){
				if(array_key_exists($stockTransferDetails['kiosk_order_id'],$kioskOrderedProductDetail) && array_key_exists($stockTransferDetails['product_id'],$kioskOrderedProductDetail[$stockTransferDetails['kiosk_order_id']])){
					$quantityRequested = $kioskOrderedProductDetail[$stockTransferDetails['kiosk_order_id']][$stockTransferDetails['product_id']];
				}else{
				  $quantityRequested = "--";
				}
				
				if(array_key_exists($relatedKioskId[$stockTransferDetails['kiosk_order_id']],$kioskName)){
				  $kiosk = $kioskName[$relatedKioskId[$stockTransferDetails['kiosk_order_id']]];
				}else{
				 $kiosk = "--";
				}
			}else{
				$quantityRequested = "--";
			}
			if($selectedcase == 1 || $selectedcase == 2 || $selectedcase == 5){
			 if(array_key_exists($stockTransferDetails['kiosk_order_id'], $kioskOrder_placedOrderList)){
			  if(array_key_exists($kioskOrder_placedOrderList[$stockTransferDetails['kiosk_order_id']], $kiosk_placed_user_id_list)){
			   if(array_key_exists($kiosk_placed_user_id_list[$kioskOrder_placedOrderList[$stockTransferDetails['kiosk_order_id']]],$users)){
			    $placedBy = $users[$kiosk_placed_user_id_list[$kioskOrder_placedOrderList[$stockTransferDetails['kiosk_order_id']]]];
			   }else{
			    $placedBy = '--';
			   }
			  }else{
			   $placedBy = '--';
			  }
			 }else{
			  $placedBy = '--';
			 }
			}else{
			 $placedBy = '--';
			}
			//if(array_key_exists($stockTransferDetails['StockTransfer']['kiosk_order_id'],$relatedKioskPlacedOrder &&)){
			//	if(array_key_exists($stockTransferDetails['StockTransfer']['product_id'],$kioskOrderedProductDetail[$stockTransferDetails['StockTransfer']['kiosk_order_id']])){
			//		$quantityRequested = $kioskOrderedProductDetail[$stockTransferDetails['StockTransfer']['kiosk_order_id']][$stockTransferDetails['StockTransfer']['product_id']];
			//	}
			//	$kiosk = $kioskName[$relatedKioskId[$stockTransferDetails['StockTransfer']['kiosk_order_id']]];
			//}else{
			//	$quantityRequested = "--";
			//}
	//		pr($kioskOrder_placedOrderList);
	//pr($kiosk_placed_user_id_list);
		?>
		<?php
		
		$total_quantity += $stockTransferDetails['quantity'];
		?>
		<tr>
			<td><?php echo $productCode;?></td>
			<td><?php echo $productName;?></td>
			<td><?php echo $stockTransferDetails['kiosk_order_id'];?></td>
			<td><?php echo $placedBy;?>&nbsp;</td>
            <?php
                $created_on = $stockTransferDetails['created'];
                if(!empty($created_on)){
                     $created_on->i18nFormat(
                                                        [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                                );
					$created_on_date =  $created_on->i18nFormat('dd-MM-yyyy HH:mm:ss');
                    $created_on_date = date("d-m-y h:i a",strtotime($created_on_date)); 
                }else{
                    $created_on_date = "--";
                }
            ?>
			<td><?php echo $created_on_date;?></td>
			<td><?php echo $kioskName[$relatedKioskId[$stockTransferDetails['kiosk_order_id']]];?></td>
			<td><?php echo $quantityRequested;?></td>
			<td><?php echo $stockTransferDetails['quantity'];?></td>
			<td><?php echo $stockTransferDetails['remarks'];?></td>
		</tr>
	<?php } ?>
	<tr>
	 <td colspan=6></td>
	 <td><b>Total Quantity</b></td>
	 <td><?=$total_quantity;?></td>
	</tr>
	</tbody>
	</table>
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
		<?php  if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ||
					$this->request->session()->read('Auth.User.group_id') == MANAGERS ||
					$this->request->session()->read('Auth.User.group_id') == inventory_manager){
					echo $this->element('sidebar/order_menus');
				}else{
					echo $this->element('sidebar/kiosk_order_menus');
		}?>

	 
</div>
<script>
	function reset_search(){
		jQuery( "#datepicker1" ).val("");
		jQuery( "#datepicker2" ).val("");
		jQuery("#keyword").val("");
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
    url: "/products/admin_data?search=%QUERY",
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
 $("#show_link").click(function(){
  $("#show_data").toggle();
  });
</script>
<script>
 $(window).on('load', function() {
    $("#show_data").hide();
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