<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\I18n\Time;
$adminDomainURL = URL_SCHEME.ADMIN_DOMAIN;
?>
<div class="mobilePurchases index">
	<?php 
	$reference = '';
	if(array_key_exists('reference',$this->request->Session()->read())){
		$reference = $this->request->Session()->read('reference');
	}
	$session_basket = $this->request->Session()->read('consolidate_faulty');
	if(!isset($search_kw)){$search_kw = "";}
	$siteBaseURL = Configure::read('SITE_BASE_URL');?>
	<h2><?php //echo __('Manage Kiosk Faulty Products'); ?></h2>
	<?php
	$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
		  $updateUrl = "/img/16_edit_page.png";
	?>
	<td><h2><?php echo __('Manage Kiosk Faulty Products')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	</h2></td>
	<form name= "search_form" id = "search_form" action='<?php echo $this->request->webroot;?>defective-kiosk-products/search_raw_faulty' method = 'get'>
	<?php
	//below kioskid is being used in kioskdropdown and the hidden field is being used in search for sending the chosen kioskid
	if(count($this->request->query) && array_key_exists('kiosk-dropdown',$this->request->query) && is_numeric($this->request->query['kiosk-dropdown'])){
		$kioskId = $this->request->query['kiosk-dropdown'];?>
		<input type="hidden" name="kiosk-dropdown" value="<?=$kioskId;?>">
	<?php }
	$returnAction = "consolidate_faulty?formchange=0&kiosk-dropdown=$kioskId";
	?>
		<div class="search_div">
			<fieldset>
				<legend>Search</legend>
				<table>
					<tr>
						<td></td>
						<td><strong>Find by category &raquo;</strong></td>						
					</tr>
					<tr><td><div id='remote'><input class="typeahead" type = "text" value = '<?= $search_kw ?>' name = "search_kw" placeholder = "Search by product title or product code" style = "width:325px" autofocus/></div></td>
						<td rowspan="4"><select id='category_dropdown' name='category[]' multiple="multiple" size='6'onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select></td></tr>
					<tr>
						<td><h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4></td>
					</tr>
					<tr>
						<td>
							<input type = "submit" name = "submit" value = "Search Product"/>
						<input type='button' name='reset' value='Reset' style="width: 100px;" onClick='reset_search();'/></p></td>
					</tr>					
				</table>
			</fieldset>	
		</div>
	</form>
	<span><strong><i>*In case of warehouse, products moved to transient will automatically be received</i></strong></span></br>
	<span><strong><i>**This screen is showing consolidated products received from warehouse or kiosk separately</i></strong></span>
	<br/><span><strong><i>***When Items from this sceen will be moved to transitent, reference will be added for each item moved in <strong><span style="background-color: yellow;">defective_kiosk_products</span></strong> table and status will be updated</i></strong></span>
	<br/><span><strong><i>****Items are also stored by summing up quanitity for same product code in <span style="background-color: yellow;">defective_kiosk_transients</span> with reference_id</i></strong></span>
	<br/><span><strong><i>****Items on this screen are rendered from <span style="background-color: yellow;">defective_kiosk_products</span></i></strong></span>
	<?=$this->Form->create(null, array('id' => 'consolidate_faulty_form', 'type' => 'get', 'url' => array('controller' => 'defective_kiosk_products', 'action' => 'consolidate_faulty')));
    //pr($this->request->params['paging']['DefectiveKioskProducts']);die;
		if(array_key_exists('page', $this->request->params['paging']['DefectiveKioskProducts'])){
			$page = $this->request->params['paging']['DefectiveKioskProducts']['page'];
			?>
			<input type="hidden" name="page" value="<?=$page;?>">
		<?php }else{
			$page = '';
		}
	?>
	<input type="hidden" value=0 name="formchange" id='form_change'>
	<?php
	if( $this->request->session()->read('Auth.User.group_id')!= MANAGERS){
		echo $this->Form->input('kioskDropdown', array('name' => 'kiosk-dropdown','options' => $kiosks, 'label' => 'Choose Kiosk','id' => 'kiosk_dropdown', 'default' => $kioskId, 'div' => false));
	}else{
		echo $this->Form->input('kioskDropdown', array('name' => 'kiosk-dropdown','options' => $kiosks, 'label' => 'Choose Kiosk','id' => 'kiosk_dropdown', 'default' => $kioskId, 'div' => false));
	}
	
	?> 
	<div style="margin-block-start: -43px;"><?=$this->Form->input('reference', array('type' => 'text', 'div' => false, 'label' => false, 'placeholder' => 'Enter a reference', 'style' => "width: 130px;margin-left: 214px;margin-top: -30px;", 'value' => $reference))?></div>
	<?=$this->Html->link('Restore Session', array('action' => 'restore_session', $this->request->params['controller'], $this->request->params['action'], 'consolidate_faulty', $kioskId, $returnAction), array('style' => "float: right;margin-right: 170px;"));?>
	<div class="submit">
	 <table>
	  <tr>
	   <td style='width: 30px';><input type="submit" name='basket' value="Add to Basket"/></td>
	   <td style='width:  5555px';><input type="submit" name='Dispatch' value="Move to Transient"/></td>
	   <td><input type="submit" name='checkout' value="Checkout" id="checkout_top" onclick="return checksession1();"/></td>
	   <td style='width: 35px';><input type="submit" name='empty_basket' value="Clear the Basket"/></td>
	   <td><input type="submit" name='bin' value="Move to Bin" /></td>
	  </tr>
	 </table>
	</div>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('kiosk_id'); ?></th>
			<th><?php echo $this->Paginator->sort('product_id'); ?></th>
			<th>Product Code</th>
			<th>Image</th>
			<th><?php echo $this->Paginator->sort('quantity'); ?></th>
			<th><?php echo $this->Paginator->sort('user_id', 'Added By'); ?></th>
			<th><?php echo $this->Paginator->sort('remarks'); ?></th>
		
			<th><?php echo $this->Paginator->sort('created','Added On'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
		<?php
		//pr($rawFaultyProduct);die;
		if(!empty($rawFaultyProduct)){
         //pr($productArray);die;
		 $groupStr = "";
		foreach($rawFaultyProduct as $key => $rawFaulty){
			$groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
			if(array_key_exists($rawFaulty->remarks,$faulty_conditions)){
				$faultyRemark = $faulty_conditions[$rawFaulty->remarks];
			}else{
				$faultyRemark = '';
			}
            
			$product_id = $rawFaulty->product_id;
			$primary_id = $rawFaulty->id;
			$quantity = $rawFaulty->quantity;
           //pr($productArray);
		   if(array_key_exists($rawFaulty->product_id,$productArray)){
				$productData = $productArray[$rawFaulty->product_id]; //$product_id;
		   }else{
			continue;
		   }
			if(is_array($session_basket) && array_key_exists($product_id,$session_basket) && array_key_exists($primary_id,$session_basket[$product_id])){
				$checked = 'checked';
			}else{
				$checked = '';
			}
            
			$truncatedProduct = \Cake\Utility\Text::truncate(
                                                                $productData['product'],
                                                                30,
                                                                [
                                                                    'ellipsis' => '...',
                                                                    'exact' => false
                                                                ]
                                                        );
		
			$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$productData['id'].DS;
			$imageName =  $productData['image'];
			$absoluteImagePath = $imageDir.$imageName;
			$LargeimageURL = $imageURL = "/thumb_no-image.png";
			if(!empty($imageName)){
				$imageURL = "{$adminDomainURL}/files/Products/image/".$productData['id']."/thumb_"."$imageName";
				$LargeimageURL = "{$adminDomainURL}/files/Products/image/".$productData['id']."/vga_"."$imageName";
			}
			
			$productCode = $productData['product_code'];
		?>
	<tr>
		<td><?= $kiosks[$rawFaulty->kiosk_id];?></td>
		<td><?= $truncatedProduct;?></td>
		<td><?= $productCode." &raquo".$this->Form->Html->link('Back to stock', array('action' => 'restore', $rawFaulty->id,$kioskId,$page), array('escape'=>false,'title' => "Back to stock"));?></td>
		 
		<td><?php
		echo $this->Html->link(
					$this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')),
					$LargeimageURL,
					array('escapeTitle' => false, 'title' => $productData['product'],'class' => "group{$key}")
				);
			?>
		</td>
		<td><?= $rawFaulty['quantity'];?></td>
        <?php //pr($users[$rawFaulty['user_id']]);die; ?>
		<td><?php if(array_key_exists($rawFaulty->user_id,$users)){
            echo   $users[$rawFaulty->user_id];
        }else{
            echo "--";
        }?></td>
		<td><?=$faultyRemark;?></td>
		 
         <?php
         $created = $rawFaulty->created ;
             $created->i18nFormat(
                                       [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                               );
			$created_date =  $created->i18nFormat('dd-MM-yyyy HH:mm:ss');
         ?>
		<td><?php  echo   date("d-m-y h:i:s",strtotime($created_date)) ;?></td>
		<?php //echo $this->Html->link($this->Html->image($editUrl, array('fullBase' => true)), array('action' => 'edit', $mobilePurchase['MobilePurchase']['id']),array('escapeTitle' => false, 'title' => 'Edit', 'alt' => 'Edit')); ?>&nbsp;
		
		<td><?=$this->Form->input('selected_quantity', array('type' => 'checkbox', 'name' => "data[ConsolidateFaulty][$product_id][$primary_id]", 'value' => $quantity, 'label' => false, 'checked' => $checked));?></td>
	</tr>
		<?php }}else{echo " <b>NO RECORD FOUND!</b> ";} //pr($rawFaultyProduct);die;?>
	</tbody>
	</table>
	<div class="submit">
	 <table>
	  <tr>
	   <td style = 'width:30px';><input type="submit" name='basket' value="Add to Basket"/></td>
	   <td style = 'width:5555px';><?php		
		$options1 = array('label' => 'Move to Transient','div' => false, 'name' => 'Dispatch');		
		echo $this->Form->end($options1);		
	   ?></td>
	   <td><input type="submit" name='checkout' value="Checkout" id="checkout_bottom" onclick="return checksession2();"/></td>
	   <td style = 'width:30px';><input type="submit" name='empty_basket' value="Clear the Basket"/></td>
	   <td><input type="submit" name='bin' value="Move to Bin" /></td>
	  </tr>
	 </table>
	</div>
	<?=$this->Form->end();?>
	<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<?=$this->element('faulty_slide_menu');?>
</div>
<script>
	$('#kiosk_dropdown').change(function(){
		//document.getElementById("consolidate_faulty_form").action = 
		//alert(document.getElementById("consolidate_faulty_form").action);
		$("#form_change").val('1');
		document.getElementById('consolidate_faulty_form').submit();
	});
	
	function checksession1 ()
	{
	<?php if(empty($session_basket)){?>
		alert("Session basket is empty, please first add items to basket!");
		return false;
	<?php } ?>
	}
	
	function checksession2 ()
	{
	<?php if(empty($session_basket)){?>
		alert("Session basket is empty, please first add items to basket!");
		return false;
	<?php } ?>
	}
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
	function reset_search(){
		jQuery( ".typeahead" ).val("");
		jQuery( "#category_dropdown" ).val("");
	}
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
	  footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------hpwaheguru.co.uk.co.uk---------</b></div>"),
	}
});
</script>
<script>
   $('#kiosk_dropdown').change(function(){
	$.blockUI({ message: 'Loading ...' });
	document.getElementById("search_form").submit();
	  }); 
</script>
<script>
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>