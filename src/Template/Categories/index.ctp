<?php
use Cake\Utility\Text;
$adminDomainURL = URL_SCHEME.ADMIN_DOMAIN;
?>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Category'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Categories'), array('controller' => 'categories', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Parent <br/>Category'), ['controller' => 'categories', 'action' => 'add'],['escape' => false]); ?> </li>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php #echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div>
  <?php

	 if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
	}else{
		$value = '';
	}
	?>
<div class="categories index large-9 medium-8 columns content">
    <h2><?= __('Categories') ?></h2>
    <form action='<?php echo $this->request->webroot;?>categories/search' method = 'get'>
		<div class="search_div">
			<fieldset>
				<legend>Search</legend>
				 <div id='remote'>
						 
				<input type = "text" name = "search_kw" class='typeahead' placeholder = "category or description" style = "width:500px" value = '<?php echo $value;?>'autofocus/></div>
				 
				<input type = "submit" name = "submit" value = "Search Category"/></p>
			</fieldset>	
		</div>
	</form>	
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('category') ?></th>
				<th>Product Count</th>
                <th scope="col"><?= $this->Paginator->sort('id_name_path','Path'); ?></th>
                <th scope="col"><?= $this->Paginator->sort('description') ?></th>
                <th scope="col"><?= $this->Paginator->sort('image') ?></th>
                <th scope="col"><?= $this->Paginator->sort('parent_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('top') ?></th>
                <th scope="col"><?= $this->Paginator->sort('sort_order') ?></th>
                <th scope="col"><?= $this->Paginator->sort('status') ?></th>
              
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
			$i = $counter = 0;
			$groupStr = "";
			foreach ($categories as $category): ?>
            <tr><?php //pr($category);die;  ?>
                <td><?php echo $this->Html->link($category->id, ['action' => 'edit',$category->id]); ?>&nbsp;</td>
                <td><?php
                $text = $category->category;
               $truncatedCategory =  Text::truncate(
                    $text,
                    40,
                    [
                        'ellipsis' => '...',
                        'exact' => false
                    ]
                );
                echo $this->Html->link(
									     $truncatedCategory,
									     ['controller' => 'categories', 'action' => 'view',$category->id],
                                            [ 
												'escapeTitle' => false,
												'title' => $category->category,
												'id' => "tooltip_".$category->id
                                            ]
										);
                   
                 ?></td>
				<td><?php echo $product_count[$category->id];?></td>
                <td><?php echo h($category->id_name_path); ?>&nbsp;</td>
                <td><?php echo h($category->description); ?>&nbsp;</td>
                <td><?php # echo h($category['Category']['image']);
                         $category->image;
						$imageDir = WWW_ROOT."files".DS.'Categories'.DS.'image'.DS.$category->id.DS;
						$imageName =  $category->image;
						$largeImageName = 'vga_'.$category->image;
						$absoluteImagePath = $imageDir.$imageName;
						//$imageURL = "/thumb_no-image.png";
						$LargeimageURL = $imageURL = "/thumb_no-image.png";
						if(!empty($imageName)){
							$imageURL = "$adminDomainURL/files/Categories/image/".$category->id."/thumb_$imageName";
							$LargeimageURL = "$adminDomainURL/files/Categories/image/".$category->id."/"."$largeImageName";
						}
					 $i++;
					$groupStr.="\n$(\".group{$i}\").colorbox({rel:'group{$i}'});";
					echo  $this->Html->link(
					$this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')),
									$LargeimageURL,
									array('escapeTitle' => false, 'title' => $category->category,'class' => "group{$i}")
									
									);
					//echo $this->Html->link(
					//		  $this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')),
					//		  ['controller' => 'categories','action' => 'edit', $category->id],
					//		  ['escapeTitle' => false, 'title' => $category->category]
					//		 );		
		?>&nbsp;</td>
			 <?php //pr($category);die; ?>
                <td><?= $category->has('parent_category') ? $this->Html->link($category->parent_category->id, ['controller' => 'Categories', 'action' => 'view', $category->parent_category->id]) : '' ?></td>
                <td><?= h($category->top) ?></td>
              
                <td><?= $this->Number->format($category->sort_order) ?></td>
                <td><?=$activeOptions[$category->status] ?></td>
               
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $category->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $category->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $category->id], ['confirm' => __('Are you sure you want to delete # {0}?', $category->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
<script>
	var user_dataset = new Bloodhound({
	datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	//prefetch: "/products/data",
	remote: {
	  url: "/categories/catSuggestions?search_kw=%QUERY",
	//  url: "/mobile-repair-prices/brandsuggestions?search=%QUERY",
	  wildcard: "%QUERY"
	}
      });
      
      $('#remote .typeahead').typeahead(null, {
	name: 'category',
	display: 'category',
	source: user_dataset,
	limit:100,
	minlength:3,
	classNames: {
	  input: 'Typeahead-input',
	  hint: 'Typeahead-hint',
	  selectable: 'Typeahead-selectable'
	},
	highlight: true,
	hint:true,
	templates: {
	  suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:400px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{category}}</a></strong></div>'),
	      header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	      footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------hpwaheguru.co.uk---------</b></div>"),
	}
      });
</script>
<script type="text/javascript">
<?php
	foreach ($categories as $category):
         $id = $category->id;
		$string = preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($category->category));
		if(empty($string)){
			$string = $category->category;
			//htmlspecialchars($str, ENT_NOQUOTES, "UTF-8")
		}
		echo "jQuery('#tooltip_'.$id).tooltip({content:\"".htmlspecialchars($string)."\",track:true});";
	endforeach;
?>
</script>
<script>
	$(document).ready(function(){
	<?php echo $groupStr;?>
	});
</script>