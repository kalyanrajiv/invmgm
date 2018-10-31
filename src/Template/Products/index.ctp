 <?php
use Cake\Utility\Text;
use Cake\Routing\Router;
?>
 
<div class="products index large-9 medium-8 columns content">
    
    <h2><?= __('Products') ?></h2>
    
    <?php if(!empty($this->request->query['search_kw'])){
			  $value = $this->request->query['search_kw'];
		  }else{
			$value = '';
		  }
		  //pr($this->request->query);die;
		  if(empty($this->request->query)){
			 $displayType = "show_all"; 
			 $discount = "all";
		   }
		   if(empty($displayType)){
				$displayType = "show_all"; 
		   }
		   if(empty($discount)){
				$discount = "all";
		   }
	?>
    <?php
  
        $rootURL = Router::url('/', true);
		$adminDomainURL = URL_SCHEME.ADMIN_DOMAIN;
		$queryStr = "";
       // echo $rootURL = $this->Html->link('home', '/');
       //  $rootURL =  $this->Html->link('/');
 
		// $rootURL = $this->html->url['/'];
		//pr($this->request->query);
        //pr($categories);
        if( isset($this->request->query['search_kw']) ){
			$queryStr.="search_kw=".$this->request->query['search_kw'];
		}
         $category = false;
		if( isset($this->request->query['category']) ){
			foreach($this->request->query['category'] as $key => $categoryID){
                if($categoryID == 0){
                    $category = true;
                }
				if(!empty($queryStr))
					$queryStr.="&category[$key] = $categoryID";
				else
					$queryStr.="&category[$key] = $categoryID";
			}
		}
	?>
    <form action='<?php echo $this->request->webroot; ?>products/search' method = 'get'>
		<fieldset>
			<legend>Search</legend>			
				<table>
					<tr>
						<td>
						</td>
						<td colspan='2'><strong>Find by category &raquo;</strong></td>
					</tr>
					<tr>
						<td><div id='remote'><input class="typeahead" type = "text" name = "search_kw" value = '<?=$value;?>' placeholder = "product, product code or description" style = "width:343px" autofocus/></div></td>
						<td rowspan="3"><select id='category_dropdown' name='category[]' multiple="multiple" size='6' onchange='update_hidden();'><option value="0" <?php if($category){ echo 'selected'; } ?>>All</option><?php echo $categories;?></select></td>
					<tr>
						<td><h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4></td>
					</tr>					
					<tr>
						<input type = "hidden" name = "discount" value = "<?php echo $discount;?>" /> 
						<input type="hidden" name="display_type" value = "<?php echo $displayType;?>" />
						
						<td colspan='2'><input type = "submit" value = "Search Product" name = "submit"/></td>
					</tr>					
				</table>
		</fieldset>	
	</form>
    	<table>
	 <tr>
	  <td>
		 <h2><?php echo __('Products');    ?>&nbsp;
          <a href="<?php echo $rootURL;?>products/export/?<?php echo $queryStr;?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png', array('fullBase' => true));?></a></h2> 
	  </td>
	    <td style="width: 25%;"><b>Show items with zero quantity</b></td>
		<form name = "display_form" id="display_form" method="get">
			<?php //echo'yamini'.$displayType;die;  ?>
			   <td style="width: 7%;"><input type = "radio" name="display_type" value="show_all" id="show_all" onclick="submitForm();" <?php echo $displayType =="show_all"? "checked":"" ?>>&nbsp;Yes</td>
			  <td><input type = "radio" name ="display_type" value="more_than_zero" id="more_than_zero" onclick="submitForm();" <?php echo $displayType =="more_than_zero"? "checked":"" ?>>&nbsp;No</td>
			 <?php if(!empty($value)){ ?>
			 <input name="search_kw" type="hidden" id="search_kw" value='<?php echo $value;?>'/>
 <?php } ?>

			 <td style = "width: 8%;"><b>Discount</b></td>
			  <td style = "width: 7%;"><input type = "radio" name = "discount" value = "discount" id = "discount" onclick="submitForm();"<?php echo $discount == "discount"? "checked":"" ?>>&nbsp;Yes</td>
			 
			<td><input type ="radio" name ="discount" value="not_discount" id="not_discount" onclick="submitForm();" <?php echo $discount =="not_discount"? "checked":"" ?>>&nbsp;No</td>
			
			<td><input type ="radio" name ="discount" value="all" id="all" onclick="submitForm();" <?php echo $discount =="all"? "checked":"" ?>>&nbsp;All</td>
			
			</form>
	 </tr>
	  
	
	</table>
          
        	<?php echo $this->Form->create(null,['method' => 'post', 'url' => ['controller' => 'products', 'action' => 'index'], 'onSubmit' => 'return validateForm();','id' => 'ProductIndexForm']);?>
	<input type="hidden" id = "submit_form" name="submit_form" value="0">
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th><?php echo $this->Paginator->sort('created','d'); ?> </th>
                <th><?= $this->Paginator->sort('product_code') ?></th>
                <th><?= $this->Paginator->sort('product') ?></th>
                <th><?= $this->Paginator->sort('category_id') ?></th>
                <?php if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER): ?>
				<th><?php echo $this->Paginator->sort('cost_price'); ?> 
			</th><?php endif;?>
                <th><?= $this->Paginator->sort('selling_price','Sale Price') ?></th>
                <th><?= $this->Paginator->sort('brand_id') ?></th> 
                <th><?= $this->Paginator->sort('color') ?></th>
                <th><?php echo $this->Paginator->sort('image'); ?></th>
                <?php if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER): ?>
				 <th><?php echo $this->Paginator->sort('discount','Discnt'); ?></th>
				 <th><?php echo $this->Paginator->sort('stock_level'); ?></th>
				 <th><?php echo $this->Paginator->sort('quantity','Qtt'); ?></th>
				 <th><?php echo $this->Paginator->sort('status'); ?></th>
				 <th><?php echo "Activate"; ?></th>
				 <th><?php echo "Deactivate"; ?></th>
			 	
				<th class="actions"><?php echo __('Actions'); ?></th>
			<?php endif;?>
                
            </tr>
        </thead>
        <tbody>
			
            <?php $groupStr = "";
			foreach ($products as $key => $product): ?>
            <?php $currentPageNumber = $this->Paginator->current();?>
            <tr>
                <td> &nbsp;</td>
                <td>
			<?php
			 $groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
			$featured = $product->featured ;
			$style = "";
			if($featured == 1){$style = 'color:red';}
			if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER): 
				echo $this->Html->link($product->product_code, ['action' => 'edit', $product->id],['style' => $style]);
			else:
				echo $product->product_code;
			endif;
			?>&nbsp;</td>
                
            <td>
                <?php  $text = $product->product;
                        $truncatedCategory =  Text::truncate(
                             $text,
                             50,
                             [
                                 'ellipsis' => '...',
                                 'exact' => false
                             ]
                         );
                         echo $this->Html->link(
									     $truncatedCategory,
									     ['controller' => 'products', 'action' => 'view',$product->id],
									    [ 
												'escapeTitle' => false,
												'title' => $text,
												'id' => "tooltip_".$product->id]
											);
                   
                 ?>
             &nbsp;</td>
             <td>
                <?= $product->has('category') ? $this->Html->link($category_list[$product->category->id], ['controller' => 'Categories', 'action' => 'view', $product->category->id]) : '' ?>
             </td>
			 <?php if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER):
			 ?>
            <td> 
                  <?php   echo $cost_price = $CURRENCY_TYPE.$product->cost_price; ?>
                 
            &nbsp;</td>
			<?php endif;?>
               <td style="width: 56px;"><?php $selling_price = $product->selling_price;
                    //formula price = total*100/vat+100
                    $ans = $selling_price;
					//echo 'yamini'.$vat;die;
                    if(isset($vat)){
                     $ans = ($selling_price*100)/($vat+100);
                    }
                    echo $CURRENCY_TYPE.$ans ;echo "</br>";
                    echo "(".$CURRENCY_TYPE.$selling_price .")";
		?>&nbsp;</td>
                
                <td><?= $product->has('brand') ? $this->Html->link($brands[$product->brand->id], ['controller' => 'Brands', 'action' => 'view', $product->brand->id]) : '' ?> &nbsp;</td>
               
                <td><?= h($product->color) ?> &nbsp;</td>
                 <td><?php # echo h($category['Category']['image']);
                   $product->image;
               //  WWW_ROOT
                $imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product->id.DS;
				$imageName = 'thumb_'.$product->image;
				 $largeImageName = 'vga_'.$product->image;
                 $absoluteImagePath = $imageDir.$imageName;
				 //echo $absoluteImagePath;die;
                $imageURL = "/thumb_no-image.png";
				 $largeImageURL = $imageURL;    
				if(!empty($imageName)){
                      $imageURL = "$adminDomainURL/files/Products/image/".$product->id."/$imageName";
					  $largeImageURL = "$adminDomainURL/files/Products/image/".$product->id."/$largeImageName"; //rasu
				}
					echo $this->Html->link(
										 $this->Html->image($imageURL, array('fullBase' => false,'width'=>'100px','height'=>'100px')), //rasu
										$largeImageURL,
										 array('escapeTitle' => false, 'title' => $product->product,'class' => "group{$key}")
                            );
				//echo $this->Html->link(
				//			  $this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')),
				//			  ['controller' => 'products','action' => 'edit', $product->id],
				//			  ['escapeTitle' => false, 'title' => $product->product]
				//			 );		
		?>&nbsp;</td>
                <?php  
               if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER):
                    $productIde = $product->id;
                    $productCode = $product->product_code ;
                    $activationStatus = array('0' => 'Inactive', '1' => 'Active');
                ?>
            <td><?php if($product->discount_status  == 1){
                 echo h($product->discount);
                }else{
                    echo "NA";
                }  ?>&nbsp;
            </td>
			<td><?= $this->Number->format($product->stock_level) ?></td>
            <td><?= $this->Number->format($product->quantity) ?></td>
            <td><?=  $active[$product->status] ; ?></td> 
			<td><?php echo $this->Form->Input('activate', array('type' => 'checkbox', 'value' => $productCode, 'label' => false, 'name' => "data[Product][activate][$productIde]",'class' => 'product_activate')); ?>&nbsp;</td>
			<td><?php echo $this->Form->Input('deactivate', array('type' => 'checkbox', 'value' => $productCode, 'label' => false, 'name' => "data[Product][deactivate][$productIde]",'class' => 'product_activate')); ?>&nbsp;</td>
			 
			 
                <td class="actions">
                      <?php 
				$editUrl = "/img/16_edit_page.png";
				$viewUrl = "/img/text_preview.png";
				$deleteUrl = "/img/list1_delete.png";
				$cloneUrl = "/img/fileview_close_right.png";
				?>
				<?php echo $this->Html->link($this->Html->image($viewUrl,array('fullBase' => true)), array('action' => 'view', $product->id),
							     array('escapeTitle' => false, 'title' => 'View', 'alt' => 'View')); ?>
				<?php echo $this->Html->link($this->Html->image($editUrl,array('fullBase' => true)), array('action' => 'edit', $product->id),
							     array('escapeTitle' => false, 'title' => 'Edit', 'alt' => 'Edit')); ?>
				<?php echo $this->Html->link($this->Html->image($cloneUrl,array('fullBase' => true)), array('action' => 'clone_product', $product->id),
							     array('escapeTitle' => false, 'title' => 'Clone Product', 'alt' => 'Clone Product')); ?>
				<?php $deleteUrl = "/img/list1_delete.png";
					echo $this->Form->postLink(
						$this->Html->image($deleteUrl,
						array("alt" => __('Delete'), "title" => __('Delete'))), 
						array('action' => 'delete', $product->id), 
						array('escape' => false, 'confirm' => __('Are you sure you want to delete # %s?', $product->id)) 
					    ); ?>
						</br>
						</br>
					<form target="_blank" method="post" action="/products/print_label">
						<input name="_csrfToken" autocomplete="off" value="<?php echo $token = $this->request->getParam('_csrfToken');?>" type="hidden">
						<input type="text" name="print_label_price" value="<?php echo $selling_price;?>" style="width: 29px;" />
						<input type="submit" name="print" value="Print Label" />
						<input type="hidden" name="id" value="<?php echo $product->id;?>" />
						<input type="hidden" name="selling_price_for_label" value="<?php echo $selling_price;?>" />
					</form>
				
                </td>
            </tr>
            <?php  endif; ?>
            <?php endforeach;
            
            ?>
            <span class="submit">
 <?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){?>
	 <?php // $options = array('label' => 'Activate', 'style' => 'float: right;');
   //  echo $this->Form->button('Activate', ['label' => 'Activate', 'style' => 'float: right;background: #62af56;']);
   // // echo  $this->Form->button(__($options)) ;
   //echo  $this->Form->end() ; 
	 ?>
     <span><input style="float: right;" value="Activate" type="submit">
       </span> 
       <span><input name="[deactivate]" value="Deactivate" type="submit">
	 </span>
</span>
                   <?php }
?>
        </tbody>
    </table>
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
		<li><?php echo $this->Html->link(__('New Product'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Categories'), array('controller' => 'categories', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Category'), array('controller' => 'categories', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
		<?php //if(AuthComponent::user('group_id') == MANAGERS || AuthComponent::user('group_id') == ADMINISTRATORS){?>
		<li><?php echo $this->Html->link(__('Send new <br/>product notification'), array('controller' => 'products', 'action' => 'new_product_push_notification'),array('escape' => false)); ?> </li>
		<li><?php echo $this->Html->link(__('Send price <br/>change notification'), array('controller' => 'products', 'action' => 'product_price_change_push_notification'),array('escape' => false)); ?> </li>
		<?php //} ?>
		
	</ul>
</div>
<script>
	function submitForm(){
		document.getElementById("display_form").submit();
		//document.getElementById("display_form").submit();
	}
     
</script>
<script type="text/javascript">
    <?php
	 foreach ($products as $key => $product):
	  $string = preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($product->product));
	  if(empty($string)){
	   $string = $product->product ;
	   //htmlspecialchars($str, ENT_NOQUOTES, "UTF-8")
	  }
	  echo "jQuery('#tooltip_{$product->id}').tooltip({content:\"".htmlspecialchars($string)."\",track:true});";
	 endforeach;
    ?>
    
  function validateForm(){
  if ($("#submit_form").val() == 1){
   $( "#ProductIndexForm" ).submit();
  }
    var checkedVals = $('.product_activate:checkbox:checked').map(function() {
     return this.value;
    }).get();
    //alert(checkedVals);
    checkedVals = checkedVals.join(",");
    if($.trim(checkedVals) == ""){
	 $("#submit_form").val(1);
	 $( "#ProductIndexForm" ).submit();
    }else{
	 msgStr = "Are you sure you want to activate/deactivate products with product code: "+checkedVals;
	  if(confirm(msgStr)){
	   $("#submit_form").val(1);
	   $( "#ProductIndexForm" ).submit();
	  }else{
	   return false;
	  }
    }
    $('id').submit(function(){
      msgStr = "Are you sure you want to activate/deactivate products with product code: "+checkedVals;
	  if(confirm(msgStr)){
	   $("#submit_form").val(1);
	   $( "#ProductIndexForm" ).submit();
	  }
     });
    /*
    $('input:checkbox.stock_activate').each(function () {
	if(this.checked){
		 alert($(this).val());
	    }
   });
    var matches = [];
    $(".stock_activate:checked").each(function() {
	    matches.push(this.value);
    });
   */
   return false;
  }
</script>
<input type='hidden' name='url_category' id='url_category' value=''/>
<script type="text/javascript">
 function update_hidden(){
   
  //var singleValues = $( "#single" ).val();
  var multipleValues = $( "#category_dropdown" ).val() || [];
    //alert(multipleValues);
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