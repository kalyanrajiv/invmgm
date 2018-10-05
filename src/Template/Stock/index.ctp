<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
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
</style>

<?php
	$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
?>
<div class="stock index">
	 
	<?php
	if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
	}else{
		$value = '';
	}
	if(empty($this->request->query)){
		$displayType = "more_than_zero";
		
	}
	?>
	<form action="<?php echo $this->request->webroot; ?>stock/search" method = 'get'>
		<fieldset>
			<legend>Search</legend>			
				<table>
					<tr>
						<td>
						</td>
						<td colspan='2'><strong>Find by category &raquo;</strong></td>
					</tr>
					<tr>
						<td><div id='remote'><input class="typeahead" type = "text" name = "search_kw" value = '<?=$value;?>' placeholder = "product,product-code" style = "width:343px" autofocus= 'ture'/></div></td>
						<td rowspan="3"><select id='category_dropdown' name='category[]' multiple="multiple" size='6'onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select></td>
					<tr>
						<td><h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4></td>
					</tr>					
					<tr>
						<td colspan='2'>
						<input type="hidden" name="display_type" value="<?php echo $displayType;?>">
						<input type = "submit" value = "Search Product" name = "submit"/></td>
					</tr>					
				</table>			
		</fieldset>	
	</form>
	<?php
		$queryStr = "";
		$rootURL = "";//$this->html->url('/', true);
		if( isset($this->request->query['search_kw']) ){
			//$queryStr.="search_kw=".$this->request->query['search_kw'];
			$queryStr.="search_kw=".$this->request->query['search_kw'];
		}
		
		if( isset($this->request->query['category']) ){
			foreach($this->request->query['category'] as $key => $categoryID){
				if(!empty($queryStr))
					 $queryStr.="&category[$key] = $categoryID";
				else
					$queryStr.="&category[$key] = $categoryID";
			}
		}
	?>
	<table>
		<tr>
			<?php
            //pr($this->request->query);die;
					$screenHint = $hintId = "";
					if(!empty($hint)){
					   $screenHint = $hint["hint"];
					   $hintId = $hint["id"];
					}
					$updateUrl = "/img/16_edit_page.png";
			?>
			<td><h2><?php echo __('Product Stock')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>";  ?>&nbsp;
            <?php if(array_key_exists('submit',$this->request->query)){?>
            <a href="<?php echo $rootURL;?>export/?<?php echo $queryStr;?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png',
                                                    ['fullBase' => true]);?></a>
            <?php }else{ ?>
            <a href="<?php echo $rootURL;?>stock/export/?<?php echo $queryStr;?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png',
                                                    ['fullBase' => true]);?></a>
            <?php  } ?>
			<?php echo $this->Html->link($this->Html->image($updateUrl,
                                                            ['fullBase' => true]),
                                         ['controller' => 'screen_hints', 'action' => 'edit',$hintId],
                                         ['escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank']);?>				
			</h2></td>
			<td style="width: 25%;">Show items with zero quantity</td>
			<form name="display_form" id="display_form" method="get">
			<td style="width: 7%;"><input type = "radio" name="display_type" value="show_all" id="show_all" onclick="submitForm();" <?php echo $displayType =="show_all"? "checked":"" ?>>&nbsp;Yes</td>
			<td><input type ="radio" name ="display_type" value="more_than_zero" id="more_than_zero" onclick="submitForm();" <?php echo $displayType =="more_than_zero"? "checked":"" ?>>&nbsp;No</td>
			</form>
		</tr>
	</table>
	<table><tr><td>
	<?php //echo $this->Form->create('Stock',['url' => ['action' => 'update_stock']]); ?><?php ?>
		<table cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<th><?php echo $this->Paginator->sort('product_code'); ?></th>
					<th><?php echo $this->Paginator->sort('product_id'); ?></th>
					<th><?php echo $this->Paginator->sort('category_id'); ?></th>
					<th><?php echo $this->Paginator->sort('color');?></th>
					<th><?php echo $this->Paginator->sort('selling_price');?></th>
					<th><?php echo $this->Paginator->sort('quantity'); ?></th>
					<th><?php echo $this->Paginator->sort('image'); ?></th>
					<?php if($this->request->session()->read('Auth.User.group_id') != REPAIR_TECHNICIANS){?>
					<th class="actions"><?php echo __('Actions'); ?></th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
				<?php $currentPageNumber = $this->Paginator->current();?>
				<?php
				$groupStr = "";
				foreach ($products as $key => $product):
				$groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
                //pr($categoryName);die;
				if(array_key_exists($product['category_id'],$categoryName)){
					$catName = $categoryName[$product['category_id']];
				}else{
					$catName = "--";
				}
				
				?>
				<?php				
					$truncatedProduct = \Cake\Utility\Text::truncate(
                                                                        $product['product'],
                                                                        50,
                                                                        [
                                                                            'ellipsis' => '...',
                                                                            'exact' => false
                                                                        ]
                                                                    );
				?>
				<tr style="height: 121px;">
					<td>
						<?php if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
						<?php echo $this->Html->link($product['product_code'],
									     ['controller' => 'products',
                                          'action' => 'edit', $product['id']],
									     ['escapeTitle' => false, 'title' => $product['product']]); ?>
						<?php }else{?>
						<?php echo $product['product_code']; ?>
						<?php } ?>
					</td>
					<td>
                        
    
                        
						<?php echo $this->Html->link($truncatedProduct,
									     ['controller' => 'products',
                                          'action' => 'view', $product['id']],
									     ['escapeTitle' => false, 'title' => $product['product'],
                                          'id' => "tooltip_{$product['id']}"]); ?>
					</td>
					<td><?php echo $catName; ?></td>
					<td><?php echo $product['color']; ?></td>
					<td><?php
					if($this->request->session()->read('Auth.User.user_type') == 'retail'){
						echo $CURRENCY_TYPE.$product['selling_price'];
					}else{
						$ans = ($product['selling_price']*100)/($vat+100);
						echo $CURRENCY_TYPE.$ans;echo "</br>";		
						echo "(".$CURRENCY_TYPE.$product['selling_price'].")";
					}
					?></td>
					<td><?php echo $this->Form->input(null,array(
											'type' => 'text',
											'name' => "Stock[quantity][$key]",
											'value' => $product['quantity'],
											'label' => false,
											'style' => 'width:80px;',
											'readonly' => true
									  )
							      ); ?>
							      <?php echo $this->Form->input(null,array(
										     'type' => 'hidden',
											'name' => "Stock[product_id][$key]",
											'value' => $product['id']
									  )
							      ); ?>&nbsp;</td>
					<td>
			<?php
				$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product['id'].DS;
				$imageName = $product['image'];
				$absoluteImagePath = $imageDir.$imageName;
				$imageURL = "/thumb_no-image.png";
                $product_id = $product['id'];
				
				$LargeimageURL = "";
				if(@readlink($absoluteImagePath) || file_exists($absoluteImagePath)){
				
					//$applicationURL = $this->html->url('/', true);
					//$imageURL = $applicationURL."files/product/image/".$product['Product']['id']."/thumb_$imageName";
					$imageURL = "$siteBaseURL/files/Products/image/".$product['id']."/$imageName"; //rasu
					$LargeimageURL = "$siteBaseURL/files/Products/image/".$product['id']."/vga_"."$imageName"; //rasu
				}
					echo $this->Html->link(
							  $this->Html->image($imageURL, ['fullBase' => true,'width' => '100px','height' => '100px']), //rasu
							  $LargeimageURL,
							  ['escapeTitle' => false, 'title' => $product['product'],'class' => "group{$key}"]
							 );

?>&nbsp;</td>
					
					<td class="actions">
						<?php if($this->request->session()->read('Auth.User.group_id') != REPAIR_TECHNICIANS){?>
						<?php echo $this->Html->link(__('View'), ['controller' => 'products','action' => 'view', $product['id']]); ?>
						<?php } ?>
						
					<?php if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER): ?>
						<?php echo $this->Html->link(__('Edit'), ['controller' => 'products','action' => 'edit', $product['id']]); ?>
					<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
				<?php echo $this->Form->input(null,array('type' => 'hidden','value' => $currentPageNumber,'name' => 'current_page'));?>
			</tbody>
		</table>
	<?php if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER): ?>
	<?php //echo $this->Form->submit(__('Update Stock'),array('name'=>'submit')); ?>
    <?php //echo $this->Form->end(); ?>
	<?php endif; ?>
	</td><td>
	<table>
	 <th style="height: 36px;">Print</br>&nbsp;</th>
	 <tr style="display: none">
	   <td>
		<form target="_blank" method="post" action="/products/print_label">
						<input type="text" name="print_label_price" value="<?php echo "1";?>" style="width: 29px;" />
						<input type="submit" name="print" value="Print Label" />
						<input type="hidden" name="id" value="<?php echo 1;?>" />
						<input type="hidden" name="selling_price_for_label" value="<?php echo "1";?>" />
					</form>
	   </td>
	  </tr>
	 <?php
	 foreach($products as $k1 => $v1){ ?>
	  <tr style="height: 122px;">
	   
	   <td>
		<form target="_blank" method="post" action="/products/print_label">
						<input type="text" name="print_label_price" value="<?php echo $v1['selling_price'];?>" style="width: 29px;" />
						<input type="submit" name="print" value="Print Label" />
						<input type="hidden" name="id" value="<?php echo $v1['id'];?>" />
						<input type="hidden" name="selling_price_for_label" value="<?php echo $v1['selling_price'];?>" />
					</form>
	   </td>
	  </tr>
	 
	 <?php }?>
	 </table>
	</td>
	</tr></table>
	
	 
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
		<li><?php echo $this->Html->link(__('List Products'),
                                         ['controller' => 'products',
                                          'action' => 'index']); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'),
                                         ['controller' => 'products',
                                          'action' => 'add']); ?> </li>
	</ul>
</div>
<script>
	
	function submitForm(){
		document.getElementById("display_form").submit();
	}
	
	 window.onload
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
     url: "/stock/admin-Data?category=%CID&search=%QUERY",
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
</script>
<script type="text/javascript">
<?php
	foreach ($products as $sngProduct):
      $string = preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($sngProduct['Product']['product']));
	  if(empty($string)){
	   echo  $string = $sngProduct['Product']['product'];
	  }
      echo "jQuery('#tooltip_{$sngProduct['Product']['id']}').tooltip({content:\"".htmlspecialchars($string)."\",track:true});";
    endforeach;
?>
</script>
<script>
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>

<script>
$(function() {
  $( document ).tooltip({
   //content: function () {
    //return $(this).prop('title');
   });
  });
 
</script>