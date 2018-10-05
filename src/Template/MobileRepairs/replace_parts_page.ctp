<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
	$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
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
    //print_r($products);die;
    extract($this->request->query);
    if(!isset($product)){$product = "";}
    if(!isset($product_code)){$product_code = "";}
    $webRoot = $this->request->webroot.'mobile_repairs/search';//FULL_BASE_URL.
?>
<?php
	$repairID = 0;
	if(isset($repair_id) && !empty($repair_id)){
	    $repairID = $repair_id;
	}
	
    ?>
<div><?php //echo $this->Session->flash(''); ?></div>
<div class="kioskProductSales index">
	<h2><?php echo __('Add parts to repair'); ?></h2>
    <?php echo $this->Form->create(null, array('url' => array('controller' => 'mobile_repairs','action' => 'search_repair_parts',$repairID)));?>
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
    
    <?php echo $this->Form->create(null,array('url' => array('controller' => 'mobile_repairs','action' => 'view_repair_parts',$repairID),'type' => 'Get')); ?>
    <input type='hidden' name='kioskID' value='<?=$kioskID;?>'/>
    
   <div class="submit">
		<table>
			<tr>
				<td style='width:30px;'><input type="submit" name='add_2_basket' value="Add parts to Basket"/></td>

			<td style='width:5550px;'>
			<?php if($this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
					echo "<input  type='submit' name='submit_repair' value='Submit parts'/>";
				}else{
					echo "<input  type='submit' name='submit_repair' value='Submit parts'/>";
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
	    $sessionBaket = $this->request->Session()->read("view_parts_basket");	    
	?>
        <?php foreach ($products as $key => $product):
		?>
	<?php //pr();?>
	<?php		
		$truncatedProduct =  \Cake\Utility\Text::truncate(
                                                                        $product->product,
                                                                        30,
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
		    $imageURL = $siteBaseURL."/files/Products/image/".$product->id."/$imageName";
		}
                
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
                                    'name' => "data[PartsRepaired][quantity][$key]",
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
					'name' => "data[PartsRepaired][item][$key]",
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
                                    'name' => "data[PartsRepaired][remarks][$key]",
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
				<td style='width:30px;'> <input type="submit" name='add_2_basket' value="Add parts to Basket"/></td>
				<td style='width:5550px;'> <?php
				if($this->request->session()->read('Auth.User.group_id') == REPAIR_TECHNICIANS){
					$options1 = array('label' => 'Submit parts','div' => false,'name' => 'submit_repair');
				}else{
					$options1 = array('label' => 'Submit parts','div' => false,'name' => 'submit_repair');
				}
					echo $this->Form->submit("Submit parts",$options1);
					echo $this->Form->end();		
				?></td>
				<td style='width:30px;'> <input type="submit" style="margin-top: -24px;" name='empty_basket' value="Clear the Basket"/></td>
			</tr>
		</table>

    </div>
    
    
    
    <div class="paging">
    <?php
        echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
        echo $this->Paginator->numbers(array('separator' => ''));
        echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
    ?>
    <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>

    </div>
</div>

<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>	
        <li><?php echo $this->Html->link(__('View Repair Parts'), array('action' => 'view_repair_parts',$repair_id)); ?> </li>
	
    </ul>
</div>
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
    url: "/mobile-repairs/admin_data?category=%CID&search=%QUERY",
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
	$('input[name = "submit_repair"]').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	});
</script>