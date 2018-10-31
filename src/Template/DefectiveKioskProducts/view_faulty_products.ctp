<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\I18n\Time;
$currency = Configure::read('CURRENCY_TYPE');
$adminDomainURL = URL_SCHEME.ADMIN_DOMAIN;
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
$siteBaseURL = Configure::read('SITE_BASE_URL');
$status = array('0' => 'Not Moved', '1' => 'Transient', '2' => 'Moved to Bin');
if(!isset($search_kw)){$search_kw = "";}
?>
<div class="mobileUnlocks index">
	<h2>Faulty Products of <?=ucfirst($kiosks[$this->request->Session()->read('kiosk_id')]);?></h2>
	<form action='<?php echo $this->request->webroot;?>defective-kiosk-products/search_faulty' method = 'get'>
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
	<table>
	<thead>
	<tr>
		
		<th><?=$this->Paginator->sort('user_id');?></th>
		<th>Product Code</th>
		<th><?=$this->Paginator->sort('product_id');?></th>
		<th>Color</th>
		<th>Image</th>
		<th><?=$this->Paginator->sort('quantity');?></th>
		<th><?=$this->Paginator->sort('remarks');?></th>
		<th><?=$this->Paginator->sort('date_of_movement');?></th>
		<th><?=$this->Paginator->sort('created');?></th>
		<th>Actions</th>
	</tr>
	</thead>
	<tbody>
		<?php
		$groupStr = "";
		foreach($defectiveKioskProduct as $key => $defectiveKiosk){
			//pr($defectiveKiosk);die;
			$groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
            $date_of_movement = $defectiveKiosk->date_of_movement;
            if(!empty($date_of_movement)){
                 $date_of_movement->i18nFormat(
                                                                   [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                                                   );
				$date_of_movement_date =  $date_of_movement->i18nFormat('dd-MM-yyyy HH:mm:ss');
            }else{
                $date_of_movement_date = "";
            }
            
            $created = $defectiveKiosk->created;
            $created_date = $created->i18nFormat(
                                                                   [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                                                   );
			$created_date =  $created->i18nFormat('dd-MM-yyyy HH:mm:ss');
            if(!empty($date_of_movement_date)){
                $date = date('d-m-Y',strtotime($date_of_movement_date));    
            }else{
                $date = "";
            }
			
			$created = date('d-m-Y',strtotime($defectiveKiosk->created));
			if(empty($date)){
				$date = '--';
			}
			
			if(array_key_exists($defectiveKiosk->user_id,$users)){
				$username = $users[$defectiveKiosk->user_id];
			}else{
				$username = '--';
			}
			
			if(array_key_exists($defectiveKiosk->remarks,$faulty_conditions)){
				$remark = $faulty_conditions[$defectiveKiosk->remarks];
			}else{
				$remark = '--';
			}
			$productCode = $truncatedProduct = $color = '';
			$LargeimageURL = $imageURL = "/thumb_no-image.png";
			if(array_key_exists($defectiveKiosk->product_id,$productArr)){
				$productData = $productArr[$defectiveKiosk->product_id];
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
				$color = $productData['color'];
				$productCode = $productData['product_code'];
			}			
		?>
		<tr>
			
			<td><?=$username;?></td>
			<td><?=$productCode;?></td>
			<td><?=$truncatedProduct;?></td>
			<td><?=$color;?></td>
			<td><?php
				echo $this->Html->link(
					$this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')),
					$LargeimageURL,
					array('escapeTitle' => false, 'title' => $productData['product'],'class' => "group{$key}")
				);
			?></td>
			 <?php //$this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px'));?> 
			<td><?=$defectiveKiosk->quantity;?></td>
			<td><?=$remark;?></td>
			<td><?=$date;?></td>
			<td><?=$created;?></td>
			<td><?php
			if($defectiveKiosk->status == 0){
				echo $this->Form->Html->link($this->Form->button('Back to stock', array('style' => "width: 105px;background: #62af56;background-image: -webkit-linear-gradient(top, #76BF6B, #3B8230); background-color: #2d6324;color: #fff;text-shadow: rgba(0, 0, 0, 0.5) 0px -1px 0px;padding: 8px 10px;border: 1px solid #bbb;border-radius: 4px;")), array('action' => 'restore', $defectiveKiosk->id,$this->request->Session()->read('kiosk_id'),'view_faulty_products'), array('escape'=>false,'title' => "Back to stock"));
			}else{
				echo $status[$defectiveKiosk->status];
			}?>
			</td>
		</tr>
		<?php } ?>
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
	   <li><?php echo $this->Html->link(__('Add Faulty'), array('controller' => 'defective_kiosk_products', 'action' => 'add')); ?></li>
	   <li><?php echo $this->Html->link(__('Faulty References'), array('controller' => 'defective_kiosk_products', 'action' => 'list_defective_references')); ?></li>
	   <li><?php echo $this->Html->link(__('View Faulty'), array('controller' => 'defective_kiosk_products', 'action' => 'view_faulty_products')); ?></li> 
	</ul>
</div>
<script type="text/javascript">
 function update_hidden(){
  //var singleValues = $( "#single" ).val();
  var multipleValues = $( "#category_dropdown" ).val() || [];
  $('#url_category').val(multipleValues.join( "," ));
 }
</script>
<input type='hidden' name='url_category' id='url_category' value=''/>
<script>
	var product_dataset = new Bloodhound({
	datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	//prefetch: "/products/data",
	remote: {
    url: "/products/admin_data?category=%CID&search=%QUERY",
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
	function reset_search(){
		jQuery( ".typeahead" ).val("");
		jQuery( "#category_dropdown" ).val("");
	}
</script>
<script>
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>