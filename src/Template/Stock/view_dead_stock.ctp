<?php
	use Cake\Core\Configure;
	use Cake\Core\Configure\Engine\PhpConfig;
	$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
	$adminDomainURL = URL_SCHEME.ADMIN_DOMAIN;
?>
<?php if(!isset($search_kw)){$search_kw = "";}?>
<?php
	$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
      $updateUrl = "/img/16_edit_page.png";
?>
<div class="stock index">
	
	<h2><?php echo __('Dead Stock')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
	<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
	</h2>
	<?php echo $this->Form->create('Stock',array('type'=>'get')); ?>
    <fieldset>
			<legend>Search</legend>	
		<table>
			<?php
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
					$kioskArr['0'] =  'Warehouse' ;
				?>
			<tr>
					<td><div id='remote'><input class="typeahead" type = "text" value = '<?= $search_kw ?>' name = "search_kw"  id  = "search_kw" placeholder = "Search by product title or product code" style = "width:300px;margin-top:10px;" autofocus/></div></td>
					<td><strong>Category</strong> </td>
			</tr>
			<tr>
			<?php
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
					$kioskArr['0'] =  'Warehouse' ;
				?>
				<td><?php echo $this->form->input('kiosklist',
													  array(
															'options'=>$kioskArr,
															//'empty'=>'Warehouse',
															'label'=>'Choose kiosk:',
															'default'=>$kiskId,
															'id'=>'kioskId'
															
												));?>
												<?php }?>
												<br>
												<h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4>
												</td>
					<td><select id='category_dropdown' name='category[]' multiple="multiple" size='6'onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select></td>
				</tr>
				<?php }else{ ?>
					<tr>
						<td>
						</td>
						<td colspan='2'><strong>Find by category &raquo;</strong></td>
					</tr>
					<tr>
						<td><div id='remote'><input class="typeahead" type = "text" value = '<?= $search_kw ?>' name = "search_kw"  id  = "search_kw" placeholder = "Search by product title or product code" style = "width:300px;margin-top:10px;" autofocus/></div></td>
							<td><select id='category_dropdown' name='category[]' multiple="multiple" size='6'onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select></td>
						 
					</tr>
					<tr>
						<td><h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4></td>
					</tr>	
					
				<?php } ?>
				<tr>
				
				<td><?php
				echo $this->Form->submit('Submit',array('name'=>'submit'));
				echo $this->Form->end(); ?></td>
				<td> <input type='button' name='reset' id = 'reset' value='Reset Search' style = "width:155px" onClick='reset_search();'/>
				</tr>
				</table> 
			
			</fieldset>
    
    
    
		<?php
			if(!empty($product)){
		?>
		<p style="float: right;margin-top: -34px;">
		<?php
			echo $this->Paginator->counter(array(
		'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
		));
		?>
	</p>
	<div class="paging" style="float: right;margin-top: -15px;">
		<?php
			echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
			echo $this->Paginator->numbers(array('separator' => ''));
			echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
		?>
	</div>
		<?php
	}
		if(empty($products)){
			echo "<h4>No products found!!</h4>";
		}else{?>
		<table cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<th><?php echo $this->Paginator->sort('product_code'); ?></th>
					<th><?php echo $this->Paginator->sort('product_id'); ?></th>
					<th><?php echo $this->Paginator->sort('color');?></th>
					<th><?php echo $this->Paginator->sort('quantity'); ?></th>
					<th><?php echo $this->Paginator->sort('image'); ?></th>
					<th class="actions"><?php #echo __('Actions'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php $currentPageNumber = $this->Paginator->current();?>
				<?php
				$groupStr = "";
				foreach ($products as $key => $product):
				$groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
				?>
				<?php				
					$truncatedProduct =
										\Cake\Utility\Text::truncate(
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
									     array('escapeTitle' => false, 'title' => $product->product, 'id' => "tooltip_{$product->id}")
									     ); ?>
					</td>
					<td><?php echo $product->color; ?></td>
					<td><?php echo $this->Form->input(null,array(
											'type' => 'text',
											'name' => "Stock[quantity][$key]",
											'value' => $product->quantity,
											'label' => false,
											'style' => 'width:80px;',
											'readonly' => true
									  )
							      ); ?>
							      <?php echo $this->Form->input(null,array(
										     'type' => 'hidden',
											'name' => "Stock[product_id][$key]",
											'value' => $product->id
									  )
							      ); ?>&nbsp;</td>
					<td>
			<?php
				$imageDir = WWW_ROOT."files".DS.'Products'.DS.'image'.DS.$product->id.DS;
				$imageName = $product->image;
				$absoluteImagePath = $imageDir.$imageName;
				$LargeimageURL = $imageURL = "/thumb_no-image.png";
                //echo $absoluteImagePath;die;
				if(!empty($imageName)){
					$imageURL = "{$adminDomainURL}/files/Products/image/".$product->id."/thumb_".$imageName; //rasu
					$LargeimageURL = "{$adminDomainURL}/files/Products/image/".$product->id."/vga_"."$imageName";
				}
					
					echo $this->Html->link(
							  $this->Html->image($imageURL, array('fullBase' => false,'width' => '100px','height' => '100px')), //rasu
							  $LargeimageURL,
							  array('escapeTitle' => false, 'title' => $product->product,'class' => "group{$key}")
							 );

?>&nbsp;</td>
					<td class="actions">
						<?php #echo $this->Html->link(__('View'), array('controller' => 'products','action' => 'view', $product['Product']['id'])); ?>
					<?php if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER): ?>
						<?php #echo $this->Html->link(__('Edit'), array('controller' => 'products','action' => 'edit', $product['Product']['id'])); ?>
					<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
				<?php echo $this->Form->input(null,array('type' => 'hidden','value' => $currentPageNumber,'name' => 'current_page'));?>
			</tbody>
		</table>
	<?php if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER): ?>
	<?php echo $this->Form->end(); ?>
	<?php endif; ?>
	<p>
		<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
	<?php } ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script type="text/javascript">
<?php
	foreach ($products as $product):
		$string = preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($product->product));
		if(empty($string)){
			$string = $product->product;
		}
		echo "jQuery('#tooltip_{$product->id}').tooltip({content:\"".htmlspecialchars($string)."\",track:true});";
    endforeach;
?>
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
    url: "/Products/admin_data?category=%CID&search=%QUERY",
                    replace: function (url,query) {
					 var multipleValues = $( "#category_dropdown" ).val() || [];
                     $('#url_category').val(multipleValues.join( "," ));
					// alert($('#url_category').val());
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
    
    suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:400px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{product_code}}</a></strong></div>'),
	header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------<?php echo ADMIN_DOMAIN;?>---------</b></div>"),
  }
});
</script>
<script>
	 function reset_search(){
		jQuery( "#category_dropdown").val('');
		jQuery("#search_kw").val("");
	 
       }
        
 
</script>
		<script>
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>