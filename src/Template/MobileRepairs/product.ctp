<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<style>
 #remote .tt-dropdown-menu {max-height: 250px; overflow-y: auto;}
 #remote .twitter-typehead {max-height: 250px;overflow-y: auto;}
.tt-dataset, .tt-dataset-product {max-height: 250px;overflow-y: auto;}
.row_hover:hover{color:blue;background-color:yellow;}
</style>
<?php
  //pr($_SESSION);die;
	if(array_key_exists('parts_basket',$_SESSION)){
		$div_show_status = 0;
	 }elseif(array_key_exists('search',$this->request->data)){
		$div_show_status = 0;
	}else{  
		$div_show_status = 1;
	}
	
	if(array_key_exists('MobileRepair',$this->request->data)){ 
		$s_rebook_status = $this->request->data['MobileRepair']['status_rebooked'];
		if($s_rebook_status != 1){ 
			$div_show_status = 0;
		}
	}
	$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
?>

	<input type="hidden" id = "show_div" value='<?php echo $div_show_status;?>' />
<?php
    extract($this->request->query);
    if(!isset($product)){$product = "";}
    if(!isset($product_code)){$product_code = "";}
    $webRoot = $this->request->webroot.'mobile_repairs/search';
	//$status_rebooked = 0;
	if(!isset($status_rebooked)){
		if(count($this->request->data)){
			if(array_key_exists('MobileRepair',$this->request->data)){
				$status_rebooked = $this->request->data['MobileRepair']['status_rebooked'];
			}else{
			   if(array_key_exists('status_rebooked',$this->request->data)){
				 $status_rebooked = $this->request->data['status_rebooked'];
			   }
			}
		}else{
			$status_rebooked = 0;
		}
	}
	
	$repairID = 0;
	if(isset($repair_id) && !empty($repair_id)){
	    $repairID = $repair_id;
	}else{
	    $repairID = $this->request['data']['MobileRepair']['id'];
	}
	?>
	 <input type="hidden" id="repair_id" value='<?php echo $repairID;?>' />
	 <?php
    $redirect_url = $this->Url->build(array('controller' => 'mobile_repairs', 'action' => 'index')); ?>
   <input type='hidden' name='redirect_url' id='redirect_url' value='<?php echo $redirect_url; ?>'/>
	
	<?php 
	$back_stock = $this->Url->build(array('controller' => 'mobile_repairs', 'action' => 'back_inventory'));
	$move_faulty = $this->Url->build(array('controller' => 'mobile_repairs', 'action' => 'move_faulty'));
?>

<?php $update_payment = $this->Url->build(array('controller' => 'mobile_repairs', 'action' => 'calculate_payment_ajax')); ?>
<input type='hidden' name='update_payment_ajax' id='update_payment_ajax' value='<?=$update_payment?>' />
	<input type='hidden' name='back_stock' id='back_stock' value='<?=$back_stock?>' />
	<input type='hidden' name='move_faulty' id='move_faulty' value='<?=$move_faulty?>' />
<div><?php //echo $this->Session->flash(''); ?></div>

<div id="remove-product" class="kioskProductSales index">
	<h2><?php echo __("Manage Previous Repair Parts"); ?></h2>
	<table cellpadding="0" cellspacing="0">	
		<tr><td colspan='13'><hr></td></tr>
		<tr>
			<th>Id</th>
			<th>Repair Id</th>
			<th>Product</th>
			<th>Product code</th>
			<th>User name</th>
			<th>Kiosk</th>
			<th>Date</th>
			<th>opperation <br/>status </th>
			<th>Opperation Date</th>
			<th>Replace</th>
		</tr>	
		<tbody>
		<?php
		//pr($viewRepairParts);die;
			if(!empty($viewRepairParts)){
			 //pr()
				foreach($viewRepairParts as $viewRepairPart){
				 //pr($viewRepairPart);die;
					$id = $viewRepairPart['id'];
					$mobile_repair_id = $viewRepairPart['mobile_repair_id'];
					$product_id = $viewRepairPart['product_id'];
					 
					$user_id = $viewRepairPart['user_id'];
                    if(!empty($viewRepairPart['created'])){
                        $date = date('d-m-Y',strtotime($viewRepairPart['created']));   
                    }else{
                        $date = "";
                    }
					
					$opp_status = $viewRepairPart['opp_status'];
                    if(!empty($viewRepairPart['opp_date'])){
                        $opp_date = date('d-m-Y',strtotime($viewRepairPart['opp_date']));    
                    }else{
                        $opp_date = ""; 
                    }
                    
					
					$move_stock =  $this->Html->link(__('Back 2 Stock'), array('controller' => 'mobile_repairs', 'action' => 'back_inventory',$mobile_repair_id,$id));
					$move_faulty =  $this->Html->link(__('Move 2 Faulty'), array('controller' => 'mobile_repairs', 'action' => 'move_faulty',$mobile_repair_id,$id));
				 
			 ?>
				 <input type="hidden" id="repair_id" value='<?php echo $repairID;?>' />
				<tr id="row_<?php echo $id;?>">
					<td><?php echo $id;?></td>
					<td><?php echo $mobile_repair_id;?></td>
					<td><?php echo $productName[$product_id];?></td>
					<td><?php echo $productcode[$product_id];?></td>
					<td><?php if(array_key_exists($user_id,$users)){
						echo $users[$user_id];
						}else{
							echo "--";
						}?></td>
					<td><?php echo $kiosks[$viewRepairPart['kiosk_id']];?></td>
					<td><?php echo $date;?></td>
					<?php
					if($opp_status == 1){
						$opp_sts = "Moved to stock";
					}elseif($opp_status == 2){
						$opp_sts = "Moved to Faulty";
					}else{
						$opp_sts = "";
					}
					
					?>
					<td><?php echo $opp_sts;?></td>
					<td><?php echo $opp_date;?></td>
					<td><?php if($opp_status == 0){
						echo "<a href='#-1' class = 'move_2_stock' rel='$id'><input type='button' name='move_2_stock' value='Move 2 Stock' style='width:90px;background-color: #4CAF50;color: white;' /></a>"."&nbsp;|&nbsp;"."<a href='#-1' class = 'move_2_faulty' rel='$id'><input type='button' name='move_2_faulty' value='Move 2 Faulty' style='width:90px;background-color: #008CBA;color: white;' /></a>";
						}?></td>
				</tr>
	 <?php }
				}?> 
	</tbody>
	</table>
			<a href='#-1' class = 'update_cart'><input type='button' name='move_2_stock' value='Add Part' style='width:90px;background-color: #4CAF50;color: white;' /></a>
			<a href="/mobile-repairs"><input type='button' name='cancel' value='Cancel' style='width:90px;background-color: #4CAF50;color: white;' /></a><?php #echo $this->Html->link(__('Cancel'), array('action' => 'index')); ?>
</div>

<div id="product" class="kioskProductSales index">
	<h2><?php echo __('Add parts to repair'); ?></h2>
    <?php echo $this->Form->create(null, array('url' => array('controller' => 'mobile_repairs','action' => 'search_product',$repairID,'method' => 'Get')));?>
	<fieldset>	    
	    <legend>Search</legend>
	    <table>
		<tr>
		    <td></td>
		    <td colspan='2'><strong>Find by category &raquo;</strong></td>
		</tr>
		    <td><div id='remote'><input class="typeahead" type = "text" value = '<?= $product_code ?>' name = "search_kw" placeholder = "Product Code or Product Title" style = "width:500px;height:25px;"/></div></td>
		    <td rowspan="3"><select id='category_dropdown' name='category[]' multiple="multiple" size='6' onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select></td>
		</tr>
		<tr>
		    <td><h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4></td>
		<tr>
		    <td colspan='2'><input type='submit' name='search' value='Search'</td>
		</tr>		
	    </table>
	</fieldset>
    <?php
	$options = array(
	    'label' => '',//Search Product
	    'div' => false,
	    'name' => 'submit1',
	    'style' => 'display:none;'
	);
    ?>
    <?php
	echo $this->Form->submit("submit",$options);
	echo $this->Form->end(); ?>
	<?php
	if(array_key_exists('parts_basket',$_SESSION)){
	  if(!empty($_SESSION['parts_basket'])){ ?>
	   
	  <?php }
	}
	?>
    
    <?php echo $this->Form->create(null,array('url' => array('controller' => 'mobile_repairs','action' => 'edit',$repairID))); ?>
    
    
   <div class="submit">
		<table>
			<tr>
				<td style='width:30px;'><input type="submit" name='add_2_basket' value="Add parts to Basket"/></td>

			<td style='width:5550px;'>
			<?php #pr($this->request->params['action']);
			if($this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
					echo "<input  type='submit' name='submit_repair' value='Dispatch to Kiosk'/>";
				}else{
					//if(array_key_exists('MobileRepair', $this->request['data']) && $this->request['data']['MobileRepair']['status_rebooked'] == 1){
					//	//we also need to change this text for internal rpair
					//	echo "<input  type='submit' name='submit_repair' value='Submit parts'/>";
					//}else{
					//	echo "<input  type='submit' name='submit_repair' value='Go to Payment'/>";
					//}
					if($status_rebooked == 1){
						//we also need to change this text for internal rpair
						echo "<input  type='submit' name='submit_repair' value='Submit parts'/>";
					}else{
					 if(array_key_exists('parts_basket',$_SESSION)){
					   if(!empty($_SESSION['parts_basket'])){ ?>
					    <?php echo $this->Html->image('http://'.ADMIN_DOMAIN.'/img/make_payment.png', array('alt' => 'Make Payment','class' => 'do_payment','style' => 'height: 36px;')); ?>
						  
					   <?php }
					 }
						//echo "<input  type='submit' name='submit_repair' value='Go to Payment'/>";
					}
				}
				?></td>
				<td style='width:30px;'> <input type="submit" name='empty_basket' value="Clear the Basket"/></td>
			</tr>
		</table>

    </div>	
    <table cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <th><?php echo $this->Paginator->sort('product_id'); ?></th>
			<th>Product Code</th>
	    <th><?php echo $this->Paginator->sort('color');?></th>
            <th>Image</th>
            <th><?php echo $this->Paginator->sort('quantity','Current Stock'); ?></th>	    
            
            <th>Item</th>
            
        </tr>
        </thead>
        <?php	    
            $currentPageNumber = $this->Paginator->current();
        ?>
	<tbody>
	
	<?php	   
	    $sessionBaket = $this->request->Session()->read("parts_basket");	    
	?>
        <?php foreach ($products as $key => $product):
		
		if($product->quantity == 0){
		 continue;
		}
		?>
	<?php //pr();?>
	<?php		
		$truncatedProduct =
							 \Cake\Utility\Text::truncate(
                                                                        $product->product,
                                                                        22,
                                                                        [
                                                                                'ellipsis' => '...',
                                                                                'exact' => false
                                                                        ]
                                                                );
		
		$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product->id.DS;
		$imageName = $product->image;
		$absoluteImagePath = $imageDir.$imageName;
		$imageURL = "/thumb_no-image.png";
        //echo $absoluteImagePath;die;        
		if(@readlink($absoluteImagePath) || file_exists($absoluteImagePath)){
            //echo "hi";die;
		    $imageURL = $siteBaseURL."/files/Products/image/".$product->id."/$imageName";
		}
        //echo $imageURL;
          //die;      
		$productQuantity = null;
		$sellingPrice = $product->selling_price;
		$productRemarks = "";
		
                $checked = false;
		if( count($sessionBaket) > 1){
                    if(array_key_exists($product->id,$sessionBaket)){
			#echo "<pre>"; print_r($sessionBaket); echo "</pre>";
                        //$productQuantity = $sessionBaket[$product['Product']['id']]['quantity'];			
                       $checked = true;
                    }
		}
	?>
	<tr>
            <td>
            <?php
                echo $this->Html->link($truncatedProduct,
                                    array('controller' => 'products', 'action' => 'view', $product->id),
                                    array('escapeTitle' => false, 'title' => $product->product)
                        );
            ?>
            </td>
			<td><?php  echo $product->product_code; ?></td>
	    <td><?php echo $product->color; ?></td>
            <td><?php
                    echo $this->Html->link(
                                    $this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')),
                                    array('controller' => 'products','action' => 'edit', $product->id),
                                    array('escapeTitle' => false, 'title' => $product->product)
                            );
                    ?>
            </td>
            <td><?php echo h($product->quantity); ?>&nbsp;</td>            
            <?php		                    
                    echo $this->Form->input(null,array(
                                    'type' => 'hidden',
                                    'name' => "PartsRepaired[quantity][$key]",
                                    'value' => 1,
                                    'label' => false,
                                    'style' => 'width:80px;',
                                    'readonly' => false
                                    )
                            );
                    ?>            
            <td>
                <?php
			if($product->quantity){
			    echo $this->Form->input(null,array(
					'type' => 'checkbox',
					'name' => "PartsRepaired[item][$key]",
					'value' => $product->id,
					'label' => false,
					'style' => 'width:80px;',
					'readonly' => false,
					'checked' => $checked,
					)
				);
			}
		?>
            </td>
            <td>
            <?php echo $this->Form->input(null,array(
                                    'type' => 'hidden',
                                    'name' => "PartsRepaired[remarks][$key]",
                                    'value' => $productRemarks,
                                    'label' => false,
                                    'style' => 'width:80px;',
                                    'readonly' => false
                                    )
                            ); ?>
            </td>	    
	</tr>
        <?php endforeach; ?>
	<?php echo $this->Form->input(null,array('type' => 'hidden','value' => $currentPageNumber,'name' => 'current_page'));?>
        
	</tbody>
    </table>
    
   <div class="submit">
		<table>
			<tr>
				<td style='width:30px;'> <input type="submit" name='add_2_basket', style ="margin-top: 20px;", value="Add parts to Basket"/></td>
				<td style='width:5550px;'> <?php
                $options1 = array();$lable = "";
				if($this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
					$options1 = array('label' => 'Dispatch to Kiosk','style'=>'margin-top: 4px;','div' => false,'name' => 'submit_repair');
					$lable = "Dispatch to Kiosk";
					 echo $this->Form->submit($lable,$options1);
					echo $this->Form->end();
				}
				?></td>
				<td style='width:30px;'> <input type="submit" name='empty_basket'  style ="margin-top: 21px;" value="Clear the Basket"/></td>
			</tr>
		</table>

    </div>
    
    
    
    <div class="paging">
    <?php
        echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
         $res = $this->Paginator->numbers(array('separator' => ''));
         echo $res1 = str_replace("/$repairID?", "/$repairID/page?", $res);
        echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
    ?>
     <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>

<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>	
        <li><?php echo $this->Html->link(__('View Sale'), array('action' => 'index')); ?> </li>
	<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'stock', 'action' => 'index')); ?> </li>
    </ul>
</div>
<input type='hidden' name='url_category' id='url_category' value=''/>


<div id="dialog-stock" title="Move part back 2 stock?" style="width: 500px !important;">
	Are you sure you want to move part back to stock?
</div>
<div id="dialog-faulty" title="Move part back to faulty?" style="width: 500px !important;">
	Are you sure you want to move part to faulty stock?
</div>
<div id="payment_screen">
<?php
	  echo $this->element('/MobileRepairs/payment',array(
														  'setting' => $setting,
														 ));
	  ?>
</div>
<div id="error_for_alert" title="error_for_alert">Error</div>
<div id="out-of-stock" title="Operation Performed">Operation Performed Successfully!</div>
<script type="text/javascript">
$(document).ready(function(){
  $('#dialog-stock').hide();
  $('#dialog-faulty').hide();
  $('#out-of-stock').hide();
});
$(document).on('click', '.move_2_stock', function() {
  var part_id = $(this).attr('rel');
	$( "#dialog-stock" ).dialog({
				resizable: false,
				height:140,
				modal: true,
				buttons: {
					"Agree": function() {
						//----------------------------------------
						$.blockUI({ message: 'Updating cart...' });
						var repair_id = $("#repair_id").val();
						var targeturl = $("#back_stock").val();
						targeturl += '/'+repair_id;
						targeturl += '/'+part_id;
                        //alert(targeturl);
                        //return false;
						$( this ).dialog( "close" );
						//Start:fire ajax
						$.ajax({
							type: 'get',
							url: targeturl,
							beforeSend: function(xhr) {
								xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
							},
							success: function(response) {
								var objArr = $.parseJSON(response);
								if(typeof objArr.status == 0){
									alert("Failed to process ");
									$.unblockUI();
								}else{
								 var tr = $('#row_'+part_id);
								 var newRow = "<tr id='row_"+part_id+"'>";
								 newRow += "<td>"+objArr.part_id+"</td>";
								 newRow += "<td>"+objArr.repair_id+"</td>";
								 newRow += "<td>"+objArr.product+"</td>";
								 newRow += "<td>"+objArr.productCode+"</td>";
								 newRow += "<td>"+objArr.username+"</td>";
								 newRow += "<td>"+objArr.kioskName+"</td>";
								 newRow += "<td>"+objArr.partDate+"</td>";
								 newRow += "<td>"+objArr.operation+"</td>";
								 newRow += "<td>"+objArr.opTime+"</td>";
								 newRow += "<td></td>";
								 newRow += "</tr>";
								 tr.replaceWith(newRow);
								 $.unblockUI();
								 $( "#out-of-stock" ).dialog({
									 resizable: false,
									 height:140,
									 modal: true,
									 closeText: "Close",
									 width:300,
									 maxWidth:300,
									 title: '!!! Operation Peformed Successfully!!!',
									 buttons: {
										 "OK": function() {
											 $( this ).dialog( "close" );
										 }
									 }
								 });
								}
							},
							error: function(e) {
								$.unblockUI();
								alert("An error occurred: " + e.responseText.message);
								console.log(e);
							}
						});
						//End:fire ajax
					},
					Cancel: function() {
						$( this ).dialog( "close" );
					}
				}
	});
});

$(document).on('click', '.move_2_faulty', function() {
 var part_id = $(this).attr('rel');
 //----------
 $( "#dialog-faulty" ).dialog({
  resizable: false,
  height:140,
  modal: true,
  buttons: {
   "Agree": function() {
	 //----------------------------------------
	 $.blockUI({ message: 'Moment moving to faulty...' });
	 var repair_id = $("#repair_id").val();
	 var targeturl = $("#move_faulty").val();
	 targeturl += '/'+repair_id;
	 targeturl += '/'+part_id;
	 $( this ).dialog( "close" );
	 //Start:fire ajax
	 $.ajax({
			 type: 'get',
			 url: targeturl,
			 beforeSend: function(xhr) {
			  xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			 },
			 success: function(response) {
			  var objArr = $.parseJSON(response);
			  if(typeof objArr.status == 0){
				  alert("Failed to process ");
				  $.unblockUI();
			  }else{
			   var tr = $('#row_'+part_id);
			   var newRow = "<tr id='row_"+part_id+"'>";
			   newRow += "<td>"+objArr.part_id+"</td>";
			   newRow += "<td>"+objArr.repair_id+"</td>";
			   newRow += "<td>"+objArr.product+"</td>";
			   newRow += "<td>"+objArr.productCode+"</td>";
			   newRow += "<td>"+objArr.username+"</td>";
			   newRow += "<td>"+objArr.kioskName+"</td>";
			   newRow += "<td>"+objArr.partDate+"</td>";
			   newRow += "<td>"+objArr.operation+"</td>";
			   newRow += "<td>"+objArr.opTime+"</td>";
			   newRow += "<td></td>";
			   newRow += "</tr>";
			   tr.replaceWith(newRow);
			   $.unblockUI();
			   $( "#out-of-stock" ).dialog({
				   resizable: false,
				   height:140,
				   modal: true,
				   closeText: "Close",
				   width:300,
				   maxWidth:300,
				   title: '!!! Operation Peformed Successfully!!!',
				   buttons: {
					   "OK": function() {
						   $( this ).dialog( "close" );
					   }
				   }
			   });
			  }
			 },
			 error: function(e) {
			  $.unblockUI();
			  alert("An error occurred: " + e.responseText.message);
			  console.log(e);
			 }
			 });
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		   }
  });
 //----------
});
</script>


<script type="text/javascript">
 function update_hidden(){
  var multipleValues = $( "#category_dropdown" ).val() || [];
  $('#url_category').val(multipleValues.join( "," ));
 }
 var product_dataset = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  remote: {
    url: "/products/admin_data?category=%CID&search=%QUERY",
                    replace: function (url,query) {
					 var multipleValues = $( "#category_dropdown" ).val() || [];
                     $('#url_category').val(multipleValues.join( "," ));
					 //alert($('#url_category').val());
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

 $(document).ready(function(){
  var show = $('#show_div').val();
  if (show == 1) {
    $('#product').hide();
  }else{
	$('#remove-product').hide();
  }
  
 });
 
 $(document).on('click', '.update_cart', function() {
	$('#remove-product').hide();
	$('#product').show();
 });
 
 $(document).on('click', '.move_faulty', function() {
	var part_id = $(this).attr('rel');
	var repair_id = $("#repair_id").val();
	var targeturl = $("#move_faulty").val();
	targeturl += '/'+repair_id;
	targeturl += '/'+part_id;
	$.blockUI({ message: 'Updating...' });
		$.ajax({
			type: 'get',
			url: targeturl,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			},
			success: function(response) {
				var objArr = $.parseJSON(response);
				if(typeof objArr.status == 0){
					alert("Failed to process ");
				}else{
					 $('#row_'+part_id).hide();
				}
				$.unblockUI();
			},
			error: function(e) {
				$.unblockUI();
				alert("An error occurred: " + e.responseText.message);
				console.log(e);
			}
		});
 });
</script>
<script>
 $(document).ready(function(){
  $('#payment_screen').hide();
  //$('#rebook_payment_screen').hide();
 });
</script>
<script>
 $(document).on('click','.do_payment',function(){
  targeturl = $("#update_payment_ajax").val();
  $.blockUI({ message: 'Updating cart...' });
  $.ajax({
			 type: 'get',
			  url: targeturl,
			  beforeSend: function(xhr) {
				  xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			 },
			 success: function(response) {
			   var objArr = $.parseJSON(response);
			   if (objArr.hasOwnProperty('error')) {
				//document.getElementById('error_for_alert').innerHTML = objArr.error;
				document.getElementById('error_for_alert').innerHTML = objArr.error;
				$( "#error_for_alert" ).dialog({
					  resizable: false,
					  height:140,
					  modal: true,
					  closeText: "Close",
					  width:300,
					  maxWidth:300,
					  title: '!!! Error!!!',
					  buttons: {
						  "OK": function() {
							  $( this ).dialog( "close" );
						  }
					  }
				  }); 
			   } else if (objArr.hasOwnProperty('total_cost') || objArr.hasOwnProperty('repair_id')) {
					document.getElementById('final_amount').value  = objArr.total_cost;
					document.getElementById('invoice_amount').innerHTML  = objArr.total_cost;
					document.getElementById('due_amount').value  = objArr.total_cost;
					document.getElementById('payment_method_0').value  = objArr.total_cost;
					document.getElementById('repiar_id').value  = objArr.repair_id;
					$('#payment_screen').show();
					$('#product').hide();
			   }
			   $.unblockUI();
			   return false;
			 },
			 error: function(e) {
			   $.unblockUI();
			   //alert("An error occurred: " + e.responseText.message);
			  document.getElementById('error_for_alert').innerHTML = "An error occurred: " + e.responseText.message;
			   $( "#error_for_alert" ).dialog({
					  resizable: false,
					  height:140,
					  modal: true,
					  closeText: "Close",
					  width:300,
					  maxWidth:300,
					  title: '!!! Error!!!',
					  buttons: {
						  "OK": function() {
							  $( this ).dialog( "close" );
						  }
					  }
				  }); 
			   console.log(e);
			   return false;
			 }
  })
  
  });
 
$(document).ready(function(){
  $('#error_for_alert').hide();
 });
 
</script>