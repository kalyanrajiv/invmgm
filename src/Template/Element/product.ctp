<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="http://<?php echo ADMIN_DOMAIN;?>/js/handlebars-v3.0.3.js"></script>
<script type="text/javascript" src="http://<?php echo ADMIN_DOMAIN;?>s/js/typeahead.bundle.js"></script>
<style>
    #remote .tt-dropdown-menu {max-height: 250px;overflow-y: auto;}
    #remote .twitter-typehead {max-height: 250px;overflow-y: auto;}
    .tt-dataset, .tt-dataset-product {max-height: 250px;overflow-y: auto;}
    .row_hover:hover{color:blue;background-color:yellow;}
</style>
<?php
use Cake\Utility\Text;
?>
<h2>Replace Item with product code:<?php echo $prodCode;?> or Add New Product to this order</h2>

<fieldset>	    
    <legend>Search</legend>
	<?php echo $this->Form->create(null, array('type' => 'Get', 'id' => 'search_form')); ?>
    <table>
    <tr>
        <td></td>
        <td colspan='2'><strong>Find by category &raquo;</strong></td>
    </tr>
    <?php if(!isset($searchKW))$searchKW="";?>
    <tr>
        <td><div id='remote'><input class="typeahead" type = "text" value = '<?= $searchKW ?>' name = "search_kw" placeholder = "Product Code or Product Title" style = "width:500px;height:25px;"/></div></td>
        <td rowspan="3"><select id='category_dropdown' name='category[]' multiple="multiple" size='6' onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select>
        </td>
    </tr>
    <tr>
        <td><h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4></td>
    <tr>
        <td colspan='2'><input type='submit' name='search' value='Search'</td>
    </tr>		
    </table>
</fieldset>
 
 
    <?= $this->Form->end() ?>	
<?php //echo $this->Form->end($options);
?>
<?php


//$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
?>
<?php echo $this->Form->create(null,array('type' => 'Post', 'id' => 'replace_item_form')); ?>
<?php
    if(count($products) > 0){
?>
<table cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th>Product <br/>Code</th>
        <th><?php echo $this->Paginator->sort('product_id'); ?></th>
        <th><?php echo $this->Paginator->sort('color');?></th>
        <th>Image</th>
        <th>
            <?php echo $this->Paginator->sort('quantity','Current Stock'); ?>
        </th>
        <th>Action</th>
    </tr>
    </thead>
    <?php	    
        $currentPageNumber = $this->Paginator->current();
    ?>
    <tbody>
    
<?php    //pr($products);
    foreach ($products as $key => $product):
      $text =  $product->product;
               $truncatedProduct =  Text::truncate(
                    $text,
                    40,
                    [
                        'ellipsis' => '...',
                        'exact' => false
                    ]
                );
       
				
        $imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product->id.DS;
        $imageName = $product->image;
        $absoluteImagePath = $imageDir.$imageName;
        $imageURL = "/thumb_no-image.png";
        
        if(@readlink($absoluteImagePath) || file_exists($absoluteImagePath)){
            $imageURL = $siteBaseURL."/files/Products/image/".$product->id.DS."thumb_".$imageName;
        }
        
        $productQuantity = null;
        $sellingPrice = $product->selling_price;
        $productRemarks = "";
				
		$checked = false;
?>
    <tr>
        <td><?=$product->product_code?></td>
        <td>
            <?php
            echo $this->Html->link($truncatedProduct,
                        array('controller' => 'products', 'action' => 'view', $product->id),
                        array('escapeTitle' => false, 'title' => $product->product)
                );
            ?>
		</td>
        <td><?php echo $product->color; ?></td>
        <td><?php
            echo $this->Html->link(
                    $this->Html->image($imageURL, array('fullBase' =>  true,'width' => '100px','height' => '100px')),
                    array('controller' => 'products','action' => 'edit', $product->id),
                    array('escapeTitle' => false, 'title' => $product->product)
                );
            ?>
        </td>
        <td><?php echo h($product->quantity); ?>&nbsp;</td>
        <td>
            <?php
            $productID = $product->id;
            $replacementPrdct = $product->id;
                if($product->quantity){
                    echo "<table><tr><td>";
					 echo $this->Form->input('Add',array(
                        'type' => 'submit',
                        'name' => "new_product_id",
                        'value' => $product->id,
                        'label' => false,
                        'style' => 'width:50px;',
                        'readonly' => false,
                        'onClick' => "add_new_product($productID);"
                        )
                    );
                    
                    echo "</td><td>";
                   echo $this->Form->input('Replace',array(
                        'type' => 'submit',
                        'name' => "PartsReplaced[replacement][$replacementPrdct]",
                        'value' => $product->id,
                        'label' => false,
                        'style' => 'width:70px;',
                        'readonly' => false,
                        'onClick' => "replace_product($productID);"
                        )
                    );
                    echo "</td></tr></table>";
                }
            ?>
        </td>  	    
    </tr>
<?php
    endforeach;
?>
<?php echo $this->Form->input(null,array('type' => 'hidden','value' => $currentPageNumber,'name' => 'current_page'));?>
			 
	</tbody>
</table>
<?php
    }else{
?>
    <table cellpadding="0" cellspacing="0">
        <tr><td>No Product found for search keyword "<strong><?php echo $searchKW;?></strong>". Please try different search criteria!</td></tr>
    </table>
<?php
    }
?>
<input type='hidden' name='add_product' id='add_product' value='0'/>
<input type='hidden' name='newly_added_product' id='newly_added_product' value=''/>
 
    <?= $this->Form->end() ?>	
<?php // echo $this->Form->end(null);?>
 <div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
<script>
    $(document).ready(function() {
        $('#cancel_item_1').hide();
        $('#Dispatch').hide();
    });
</script>


<input type='hidden' name='replace_product' id='replace_product' value='0'/>
<input type='hidden' name='replaced_by' id='replaced_by' value=''/>
<input type='hidden' name='url_category' id='url_category' value=''/>
<script type="text/javascript">
	function add_new_product(productID) {
		//alert(productID);
        $('#add_product').val(1);
        $('#newly_added_product').val(productID);
		//alert($('#newly_added_product').val());
        $('#replace_item_form').submit();
    }
    function replace_product(productID) {
		//alert(productID);
        $('#replace_product').val(1);
        $('#replaced_by').val(productID);
        $('#replace_item_form').submit();
    }
    
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
			footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------<?php echo ADMIN_DOMAIN;?>---------</b></div>"),
		  }
	});
</script>
