<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
$currency = Configure::read('CURRENCY_TYPE');
//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
$siteBaseURL = Configure::read('SITE_BASE_URL');
$adminDomainURL = URL_SCHEME.ADMIN_DOMAIN;
if(!isset($search_kw)){$search_kw = "";}
?>
<div class="mobileUnlocks index">
	<h2><?php //echo __('Faulty Bin'); ?></h2>
	<?php
	$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
		  $updateUrl = "/img/16_edit_page.png";
	?>
	<td><h2><?php echo __('Bin Items')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	</h2></td>
	<form action='<?php echo $this->request->webroot;?>DefectiveKioskProducts/search_bin' method = 'get'>
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
							<input type='button' name='reset' value='Reset' style="width: 100px;" onClick='reset_search();'/>
							</p></td>
					</tr>					
				</table>
			</fieldset>	
		</div>
	</form>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th>Product Code</th>
		<th><?php echo $this->Paginator->sort('product_id'); ?></th>
		<th>Color</th>
		<th>Image</th>
		<th><?php echo $this->Paginator->sort('quantity'); ?></th>
		<th><?php echo $this->Paginator->sort('modified'); ?></th>
	</tr>
	</thead>
	<tbody>
		<?php
		//pr($defectiveBin);die;
		$groupStr = "";
		foreach($defectiveBin as $key => $defectiveBinDetail){
			$groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
            //pr($productArr);die;
			if(!array_key_exists($defectiveBinDetail->product_id,$productArr)){
				continue;
			}
			$truncatedProduct = \Cake\Utility\Text::truncate(
				
				$productArr[$defectiveBinDetail->product_id]['product'],
				30,
				[
				    'ellipsis' => '...',
				    'exact' => false
				]
			);
		
			$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$defectiveBinDetail->product_id.DS;
			$imageName =  $productArr[$defectiveBinDetail->product_id]['image'];
			$absoluteImagePath = $imageDir.$imageName;
			$LargeimageURL = $imageURL = "/thumb_no-image.png";
			if($imageName){
				$imageURL = "{$adminDomainURL}/files/Products/image/".$defectiveBinDetail->product_id."/thumb_"."$imageName";
				$LargeimageURL = "{$adminDomainURL}/files/Products/image/".$defectiveBinDetail->product_id."/vga_"."$imageName";
			}
			?>
		<tr>
			<td><?=$productArr[$defectiveBinDetail->product_id]['product_code'];?></td>
			<td><?=$truncatedProduct;?></td>
			<td><?=$productArr[$defectiveBinDetail->product_id]['color'];?></td>
			<td><?php
				echo $this->Html->link(
					$this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')),
					$LargeimageURL,
					array('escapeTitle' => false, 'title' => $productArr[$defectiveBinDetail->product_id]['product'],'class' => "group{$key}")
				);;?></td>
			<td><?=$defectiveBinDetail->quantity;?></td>
			<td><?=date('M jS, Y g:i A',strtotime($defectiveBinDetail->modified));//$this->Time->format('M jS, Y g:i A',$defectiveBinDetail->modified,null,null);?></td>
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
	 <?=$this->element('faulty_slide_menu');?>
</div>
<script>
	jQuery(function() {
		jQuery( "#datepicker1" ).datepicker({ dateFormat: "d M yy" });
		jQuery( "#datepicker2" ).datepicker({ dateFormat: "d M yy " });
	});
	
	function reset_search(){
		jQuery( "#selectKiosk" ).val("");
		jQuery( "#reference_id" ).val("");
		jQuery("#datepicker1").val("");
		jQuery("#datepicker2").val("");
	}
</script>

<script>
$(function() {
  $( document ).tooltip({
 //  content: function () {
  //  return $(this).prop('title');
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
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>