<?php
	use Cake\Core\Configure;
	use Cake\Core\Configure\Engine\PhpConfig;	
	$currency = Configure::read('CURRENCY_TYPE');
	$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
	$adminDomainURL = URL_SCHEME.ADMIN_DOMAIN;
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
	$kiosk_id = $value = '';
	if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
	}//pr($products);
	
	if(empty($kiosk_id)){
		if(array_key_exists(0,$this->request->params['pass'])){
			$kiosk_id = $this->request->params['pass'][0];
		}
	}
	if(empty($this->request->query) || count($this->request->query)){
		if(!array_key_exists('display_type',$this->request->query)){
			$displayType = "more_than_zero";
		}
	}	//pr($this->request);
?>
    <div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('Kiosk Stock'), array('controller' => 'stock_transfer', 'action' => 'kiosk_stock')); ?> </li>
	</ul>
</div>
	
	<div class="centralStocks index">
		<?php echo $this->Form->create(null,array('url' => array('controller' => 'stock-transfer','action' => 'view_stock'),'id'=>"ProductViewStockForm")); ?>
		<form id="ProductViewStockForm" method="post">
	<fieldset>
	<legend><span><strong>Kiosk</strong><span style='color:red'><sup>*</sup></span></legend> <?php
	if( $this->request->session()->read('Auth.User.group_id')== MANAGERS){
		echo $this->Form->input(null, array(
									       'options' => $kiosks,
									       'label' => false,
									       'div' => false,
									       'name' => 'KioskStock[kiosk_id]',
									      'value' => $kiosk_id,
									      'onChange'=>'select_change();',
									       'empty' => 'Warehouse',
									       'id'=>'Product'
									      
									       )
													      );	
	}else{
		if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS){
			echo $this->Form->input(null, array(
									       'options' => $kiosks,
									       'label' => false,
									       'div' => false,
									       'name' => 'KioskStock[kiosk_id]',
									      'value' => $kiosk_id,
									      'onChange'=>'select_change();',
									       'empty' => 'Warehouse',
									       'id'=>'Product'
									      
									       )
													      );	
		}else{
			echo $this->Form->input(null, array(
									       'options' => $kiosks,
									       'label' => false,
									       'div' => false,
									       'name' => 'KioskStock[kiosk_id]',
									      'value' => $kiosk_id,
									      'onChange'=>'select_change();',
									       //'empty' => 'Warehouse',
									       'id'=>'Product'
									      
									       )
													      );
		}
		
	}
	?></span>
		<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
		<span style="margin-left: 465px;"><strong>Total Cost =</strong> <?=number_format($sumTotalCostPrice,2);?></span>
		<?php } ?>
		
	</fieldset>
	</form>
	<?php #echo $this->Form->end(); ?>
		<form action='<?php echo $this->request->webroot; ?>stock-transfer/search_view_stock/<?php echo $kiosk_id;?>' method = 'get'>
		<fieldset>
			<legend>Search</legend>			
				<table>
					<tr>
						<td>
						</td>
						<td colspan='2'><strong>Find by category &raquo;</strong></td>
					</tr>
					<tr>
						<td><div id='remote'><input type = "text" class = "typeahead" name = "search_kw" value = '<?=$value;?>' placeholder = "product, product code or description" style = "width:343px" autofocus/></div></td>
						<td rowspan="3">
							<select id='category_dropdown' name='category[]' multiple="multiple" size='6' onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select></td>
					<tr>
						<td><h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4></td>
					</tr>					
					<tr>
						<td colspan='2'>
							<?php if(array_key_exists('KioskStock',$this->request['data']) &&
								$this->request['data']['KioskStock']['kiosk_id']!='all' ||
								      (array_key_exists('pass',$this->request->params) &&
								       array_key_exists(0,$this->request->params['pass']) &&
								       $this->request->params['pass'][0]!="all") ||
								      empty($this->request->params['pass'])
								      ){ ?>
						<input type="hidden" name="display_type" value="<?php echo $displayType;?>">
						<?php }?>
						<input type = "submit" value = "Search Product" name = "submit1"/></td>
					</tr>					
				</table>
		</fieldset>	
	</form>
	<?php
	$screenHint = $hintId = "";
		if(!empty($hint)){
		   $screenHint = $hint["hint"];
		   $hintId = $hint["id"];
		}
      $updateUrl = "/img/16_edit_page.png";
	?>
	<table>
		<tr>
			<td><h2><?php echo __('Product Stock')."<span style='background: skyblue;color: blue;' title=\"$screenHint\">?</span>"; ?>
			<?php echo $this->Html->link($this->Html->image($updateUrl,array('fullBase' => true)), array('controller' => 'screen_hints', 'action' => 'edit',$hintId), array('escapeTitle' => false, 'title' => 'Edit Hint', 'alt' => 'Update payment','target' => '_blank'));?>
			</h2></td>
			<?php
			if(array_key_exists('KioskStock',$this->request['data']) &&
			   $this->request['data']['KioskStock']['kiosk_id']!='all' ||
				 (array_key_exists('pass',$this->request->params) &&
				  array_key_exists(0,$this->request->params['pass']) &&
				  $this->request->params['pass'][0]!="all") ||
				 empty($this->request->params['pass'])
				 ){?>
			<td style="width: 25%;">Show items with zero quantity</td>
			<form name="display_form" id="display_form" method="get">
			<td style="width: 7%;"><input type="radio" name="display_type" value="show_all" id="show_all" onclick="submitForm();" <?php echo $displayType=="show_all"? "checked":"" ?>>&nbsp;Yes</td>
			<td><input type="radio" name="display_type" value="more_than_zero" id="more_than_zero" onclick="submitForm();" <?php echo $displayType=="more_than_zero"? "checked":"" ?>>&nbsp;No</td>
			</form>
			<?php } ?>
		</tr>
	</table>
	<table>
		<tr>
			<th><?php echo $this->Paginator->sort('product_code'); ?></th>
			<th><?php echo $this->Paginator->sort('product'); ?></th>
			<th><?php echo $this->Paginator->sort('color'); ?></th>
			<th><?php echo $this->Paginator->sort('quantity','Current Stock'); ?></th>
			<th>Image</th>
			<th><?php echo $this->Paginator->sort('selling_price'); ?></th>
			<?php if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){ ?>
			<th><?php echo $this->Paginator->sort('cost_price'); ?></th>
			<?php  } ?>
		</tr>
		<?php
        //pr($products);die;
		$groupStr = "";
			foreach($products as $key => $product){
				$groupStr.="\n$(\".group{$key}\").colorbox({rel:'group{$key}'});";
				$truncatedProduct =  \Cake\Utility\Text::truncate(
                                                                        $product->product,
                                                                        30,
                                                                        [
                                                                                'ellipsis' => '...',
                                                                                'exact' => false
                                                                        ]
                                                                );
			
				$imageDir = WWW_ROOT.DS."files".DS.'Products'.DS.'image'.DS.$product->id.DS;
				$imageName = $product->image;
				$largeImageName = 'vga_'.$imageName;
				$absoluteImagePath = $imageDir.$imageName;
				$imageURL = "/thumb_no-image.png";
				$largeImageURL = $imageURL;
				if(!empty($imageName)){
					$imageURL = $adminDomainURL.'/files/Products/image/'.$product->id.DS."thumb_".$imageName;
					$largeImageURL = $adminDomainURL.'/files/Products/image/'.$product->id.DS.$largeImageName;
				}
		?>
		<tr>
			<td>
				<?php echo $product->product_code; ?>
			</td>
			<td>
				<?php echo $this->Html->link($truncatedProduct,
					array('controller' => 'products', 'action' => 'view', $product->id),
					array('escapeTitle' => false, 'title' => $product->product,'id' => "tooltip_{$product->id}")
					//array('escapeTitle' => false, 'title' => $product->product,'id' => "tooltip_{$product->id}")
				);
				?>
			</td>
			<td>
				<?php echo $product->color; ?>
			</td>
			<td>
				<?php
				//pr($this->request);
				if((!empty($this->request->query) &&
				    array_key_exists('submit',$this->request->query) &&
				    $this->request->query['submit']=="Search Product" &&
				   array_key_exists('pass',$this->request->params) &&
				   array_key_exists('0',$this->request->params['pass']) &&
				      $this->request->params['pass'][0]=="all")||
				   (array_key_exists('KioskStock',$this->request['data']) &&
				   $this->request['data']['KioskStock']['kiosk_id']=="all") ||
				   (array_key_exists('pass',$this->request->params) &&
				   array_key_exists('0',$this->request->params['pass']) &&
				      $this->request->params['pass'][0]=="all")
				   ){
					//for case: all
					if($sum_quantity[$product->id]>0){
						echo $this->Html->link($sum_quantity[$product->id],array('action'=>'product_per_kiosk',$product->id));
					}else{
						echo $sum_quantity[$product->id]; 
					}
				}else{
					//for case other than all
					echo $product->quantity; 
				}
				?>
			</td>
			<td>
				<?php
					echo $this->Html->link(
					$this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')),
					$largeImageURL,
					array('escapeTitle' => false, 'title' => $product->product,'class' => "group{$key}")
				);
				?>
			</td>
			<td>
				<?php echo $CURRENCY_TYPE.$product->selling_price; ?>
			</td>
			<td>
				<?php
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){
					if($kiosk_id=="all" || $kiosk_id==""){
						echo $CURRENCY_TYPE.$product->cost_price; 
					}elseif((int)$kiosk_id){
						if(array_key_exists($product->id,$costPriceArr)){
							echo $costPriceArr[$product->id];
						}else{
							echo "--";
						} 
					}
				}
				?>
			</td>
			<?php } ?>
		</tr>
	</table>
	<div class="paging">
	<p>
	<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>

<input type='hidden' name='url_category' id='url_category' value=''/>
	
		<script>
			function update_hidden(){
				var multipleValues = $( "#category_dropdown" ).val() || [];
				$('#url_category').val(multipleValues.join( "," ));
			}
			function select_change(){
				var z = document.getElementById("Product").value;
				var y = document.getElementById("ProductViewStockForm").action;
				//alert(y);
				var newAction = y+'/'+z;
				document.getElementById("ProductViewStockForm").action = newAction;
				document.getElementById("ProductViewStockForm").submit();
			}
			
			//window.onload = select_change();//break;
			
			function submitForm(){
				document.getElementById("display_form").submit();
			}
		</script>
		<script>
		var product_dataset = new Bloodhound({
		 datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
		 queryTokenizer: Bloodhound.tokenizers.whitespace,
		 //prefetch: "/products/data",
		 remote: {
		   url: "/stock-transfer/admin_data?category=%CID&search=%QUERY",
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
		<script type="text/javascript">
		<?php
			foreach ($products as $product):
				$string = preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($product->product));
				if(empty($string)){
					echo  $string = $product->product;
				}
				echo "jQuery('#tooltip_{$product->id}').tooltip({content:\"".htmlspecialchars($string)."\",track:true});";
			endforeach;
		?>
		</script>
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
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>