<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
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
	<?php if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
	}else{
		$value = '';
	}
	if(empty($this->request->query)){
		$displayType = "show_all";
		
	}
	?>
	<form action="<?php echo $this->request->webroot; ?>Stock/search_selling_price" method = 'get'>
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
						<input type="hidden" name="display_type"     value="<?php echo $displayType;?> "  >
						<input type = "submit" value = "Search Product" name = "submit"/></td>
					</tr>					
				</table>			
		</fieldset>	
	</form>
	<?php
		
		$rootURL = $queryStr = "";
		//$rootURL = $this->html->url('/', true);
		if( isset($this->request->query['search_kw']) ){
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
			<td><h2><?php echo __('Product Stock'); ?>&nbsp;<a href="<?php echo $rootURL;?>export/?<?php echo $queryStr;?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png', array('fullBase' => true));?></a></h2></td>
			<td style="width: 25%;">Show items with zero quantity</td>
			<form name="display_form" id="display_form" method="get">
			<td style="width: 7%;"><input type="radio" name="display_type" value="show_all" id="show_all" onclick="submitForm();" <?php echo $displayType=="show_all"? "checked":"" ?>>&nbsp;Yes</td>
			<td><input type="radio" name="display_type" value="more_than_zero" id="more_than_zero" onclick="submitForm();" <?php echo $displayType=="more_than_zero"? "checked":"" ?>>&nbsp;No</td>
			</form>
		</tr>
	</table>
	<?php echo $this->Form->create('Stock',array('url' => array('action' => 'update_stock'))); ?>
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
					
				</tr>
			</thead>
			<tbody>
				<?php $currentPageNumber = $this->Paginator->current();?>
				<?php foreach ($product as $key => $product):
				if(array_key_exists($product->category_id,$categoryName)){
					$catName = $categoryName[$product->category_id];
				}else{
					$catName = "--";
				}
				
				?>
				<?php				
					$truncatedProduct =  \Cake\Utility\Text::truncate(
                                                                        $product->product,
                                                                        50,
                                                                        [
                                                                                'ellipsis' => '...',
                                                                                'exact' => false
                                                                        ]
                                                                );
					

				?>
				<tr>
					<td>
						<?php if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
						<?php echo $this->Html->link(
									     $product->product_code,
									     array('controller' => 'products', 'action' => 'edit', $product->id),
									     array('escapeTitle' => false, 'title' => $product->product)
									     ); ?>
						<?php }else{?>
						<?php echo $product->product_code; ?>
						<?php } ?>
					</td>
					<td>
						<?php echo $this->Html->link(
									     $truncatedProduct,
									     array('controller' => 'products', 'action' => 'view', $product->id),
									     array('escapeTitle' => false, 'title' => $product->product)
									     ); ?>
					</td>
					<td><?php echo $catName; ?></td>
					<td><?php echo $product->color; ?></td>
					<td><?php echo $product->selling_price; ?></td>
					<td><?php echo $this->Form->input(null,array(
											'type' => 'text',
											'name' => "data[Stock][quantity][$key]",
											'value' => $product->quantity,
											'label' => false,
											'style' => 'width:80px;',
											'readonly' => true
									  )
							      ); ?>
							      <?php echo $this->Form->input(null,array(
										     'type' => 'hidden',
											'name' => "data[Stock][product_id][$key]",
											'value' => $product->id
									  )
							      ); ?>&nbsp;</td>
					<td>
			<?php
				$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product->id.DS;
				$imageName = $product->image;
				$absoluteImagePath = $imageDir.$imageName;
				$imageURL = "/thumb_no-image.png";
				if(@readlink($absoluteImagePath) || file_exists($absoluteImagePath)){
					//$applicationURL = $this->html->url('/', true);
					//$imageURL = $applicationURL."files/product/image/".$product['Product']['id']."/thumb_$imageName";
					$imageURL = "$siteBaseURL/files/Products/image/".$product->id."/$imageName"; //rasu
				}
					
					echo $this->Html->link(
							  $this->Html->image($imageURL, array('fullBase' => false,'width' => '100px','height' => '100px')), //rasu
							  array('controller' => 'products','action' => 'edit', $product->id),
							  array('escapeTitle' => false, 'title' => $product->product)
							 );

?>&nbsp;</td>
					
				</tr>
				<?php endforeach; ?>
				<?php echo $this->Form->input('null',array('type' => 'hidden','value' => $currentPageNumber,'name' => 'current_page'));?>
			</tbody>
		</table>
	<?php if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER): ?>
	<?php
	echo $this->Form->submit("Update Stock");
	echo $this->Form->end(); ?>
	<?php endif; ?>
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
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
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
    url: "/stock/admin_data?category=%CID&search=%QUERY",
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