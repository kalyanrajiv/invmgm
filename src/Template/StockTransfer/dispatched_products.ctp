<div class="kioskOrders index">
     <?php
use Cake\Utility\Text;
use Cake\Routing\Router;
  
?>
	<?php
	// $siteUrl = Configure::read('SITE_BASE_URL');
	$value = '';
	if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
	}
	//echo $from_date;
	
	$from_date = date("d-m-Y");
	$to_date = date("d-m-Y");
	
	if(!empty($this->request->query['from_date'])){
		$from_date = $this->request->query['from_date'];
	}
	
	if(!empty($this->request->query['to_date'])){
		$to_date = $this->request->query['to_date'];
	}
	
	if(array_key_exists('forprint',$this->request->query)){
		$forprint = $this->request->query['forprint'];
	}else{
		$forprint = 'No';
	}
	?>
	<form action='<?php echo $this->request->webroot;?>stock-transfer/search_dispatched' method = 'get'>
	 
		<div class="search_div">
			<fieldset>
				<legend>Search</legend>
				<table style="margin-top: -13px;margin-bottom: -18px;">
					<tr>
						<td colspan='3'>
						</td>
						<td><strong>Find by category &raquo;</strong></td>
					</tr>
					<tr>
						<td>
							<input type = "text" name = "from_date" id = "datepicker_1" placeholder = "From date" value = '<?= $from_date;?>'style = "width:100px"/>
						</td>
						<td>
							<input type = "text" name = "to_date" id = "datepicker_2" placeholder = "To date" value = '<?= $to_date;?>'style = "width:100px"/>
						</td>
						<td style="width: 425px;">
						<div id='remote'>
							<input type = "text" class = "typeahead" name = "search_kw" placeholder = "Product code or product name" value = '<?= $value;?>'style = "width:200px" autofocus/>
						</div>
						</td>
						<td rowspan="3"><select id='category_dropdown' name='category[]' style='width: 264px; height: 82px;' multiple="multiple" size='6' onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select></td>
						<td>
							For printing?<br/>
							<input type = "radio" name = "forprint" value = 'Yes' <?php if($forprint=='Yes'){echo "checked";}?>/>Yes<br/><br/>
							<input type = "radio" name = "forprint" value = 'No' <?php if($forprint=='No'){echo "checked";}?>/>No
						</td>
						<td>
							<input type = "submit" name = "submit" value = "Search"/>
						</td>
					</tr>
				</table>
			</fieldset>	
		</div>
	</form>
	
	<?php
		
		$queryStr = "";
		  $rootURL = Router::url('/', true);
		if( isset($this->request->query['search_kw']) ){
			$queryStr.="search_kw=".$this->request->query['search_kw'];
		}
		
			//echo $queryStr;
		
		
	?>
	<?php
	
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
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
		$updateUrl = "/img/16_edit_page.png";
	?>
	<strong><?php #print_r($kioskOrders);
	echo __('<span style="font-size: 20px;color: red;">Dispatched Products</span> <span style="font-size: 17px;">(Warehouse to Kiosk)</span>'); ?></strong>
	<span style="background: skyblue;color: blue;" title='<?php echo $screenHint ?>'>?</span>
	<?php if(!empty($final_qty)){ ?>
	<span style="float: right;" >Total Qty = <?php echo $final_qty;?></span>
	<?php }else{ ?>
	<span style="float: right;" >Total Qty = <?php echo 0;?></span>
	<?php }?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	
	<table cellpadding="0" cellspacing="0">
	<thead>
	<?php if($forprint=='Yes'){ ?>
	<tr>
		<td colspan="5"><h4><?php echo "Accessory sale from date: $from_date to $to_date"?></h4></td>
	</tr>
	<?php } ?>
	<tr>
		<?php if($forprint!='Yes'){?>
			<th><?php echo $this->Paginator->sort('product_id','Product Id'); ?></th>
		<?php }else{ ?>
			<th><?php echo "Sr.No." ?></th>
		<?php } ?>
			<th>Product Code</th>
			<th><?php echo $this->Paginator->sort('created','Dispatch Date');  ?></th>
			<th>Category</th>
			<th>Product</th>
			<?php if($forprint!='Yes'){?>
			<th><?php echo $this->Paginator->sort('product_id','Image'); ?></th>
			<?php } ?>
			<th><?php echo $this->Paginator->sort('quantity'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	$i = $counter = 0;
	// pr($dispatchedProducts);
	$groupStr ="";
	foreach ($dispatchedProducts as $dispatchedProduct):
	
	?>
        <?php
	if($dispatchedProduct->totalquantity>0){
		$counter++;
		$category = $productCode = $productName = '';
      if(array_key_exists($dispatchedProduct->product_id,$productIdDetail)){
			$productCode = $productIdDetail[$dispatchedProduct->product_id]['product_code'];
			$productName = $productIdDetail[$dispatchedProduct->product_id]['product'];
			$category_id = $productIdDetail[$dispatchedProduct->product_id]['category_id'];
		}
		if(isset($category_id) && !empty($category_id)){
            if(array_key_exists($category_id,$categoryArr)){
				$category = $categoryArr[$category_id];
            }
		}
        $imageDir =  WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$dispatchedProduct->product_id.DS;
        $imageName = $productIdDetail[$dispatchedProduct->product_id]['image'];
				$absoluteImagePath = $imageDir.$imageName;
				$LargeimageURL = $imageURL = "/thumb_no-image.png";
				if(@readlink($absoluteImagePath) ||($absoluteImagePath)){
					$imageURL = "/files/Products/image/".$dispatchedProduct->product_id."/$imageName";
					$LargeimageURL = "/files/Products/image/".$dispatchedProduct->product_id."/vga_"."$imageName";
				}else{
                    $imageURL = "/img/images.png" ;
                }
        //   $sitePath = $siteUrl."files".DS."product".DS."image".DS.$dispatchedProduct->product_id.DS;
        // 
        //if(array_key_exists($dispatchedProduct->product_id,$productIdDetail)){
        //    $imageName = 'thumb_'.$productIdDetail[$dispatchedProduct->product_id]['image'];
        //}
        $dispatchDate = $dispatchedProduct->created;
        //$absoluteImagePath = $imageDir.$imageName;
        //$imageURL = "/thumb_no-image.png";
        //    if(file_exists($absoluteImagePath)){
        //        $imageURL = $sitePath.$imageName;
        //    }
            ?>
        <tr>
            <?php if($forprint!='Yes'){?>
                <td><?php echo $dispatchedProduct->product_id;?></td>
            <?php }else{?>
                <td><?php echo $counter;?></td>
            <?php }?>
            
            <td><?php echo $productCode;?></td>
            <td><?php echo $dispatchDate;?></td>
            <td><?php echo $category ?></td>
            <td><?php echo $productName;?></td>
            <?php if($forprint!='Yes'){?>
            <td><?php
//            echo $this->Html->link(
//							  $this->Html->image($imageURL, ['fullBase' => true,'width' => '100px','height' => '100px']), //rasu
//							  ['controller' => 'products','action' => 'edit', $product['id']],
//							  ['escapeTitle' => false, 'title' => $product['product']]
//							 );
			   $i++;
			   $groupStr.="\n$(\".group{$i}\").colorbox({rel:'group{$i}'});";
            echo  $this->Html->link(
			$this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')),
									$LargeimageURL,
									array('escapeTitle' => false, 'title' => $productName,'class' => "group{$i}")
									
									);?></td>
            <?php }?>
            <td><?php echo $dispatchedProduct->totalquantity;?></td>
             
        </tr>
<?php }
endforeach; ?>
	</tbody>
	</table>
</div>
	 
	 <div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
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
<script>
	function printDiv() {
		var printContents = document.getElementById("printit").innerHTML;
		var originalContents = document.body.innerHTML;
	   
		document.body.innerHTML = printContents;
	   
		window.print();
	   
		document.body.innerHTML = originalContents;
		location.reload();
	   }
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
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>