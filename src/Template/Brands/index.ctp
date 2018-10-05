  <?php
use Cake\Utility\Text;
use Cake\Routing\Router;
?>
<div class="brands index large-9 medium-8 columns content">
    <?php

	 if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
	}else{
		$value = '';
	}
	?>
    <?php
    //pr($this->request->query);
     $queryStr = "";
     $rootURL = Router::url('/', true);
        if( isset($this->request->query['search_kw']) ){
             $queryStr.="?search_kw=".$this->request->query['search_kw'];
        }
       // echo $queryStr;
	?>

    <form action='<?php echo $this->request->webroot;?>brands/search' method = 'get'>
    <div class="search_div">
        <fieldset>
            <legend>Search</legend>
			 <div id='remote'>
				 <div id='remote'>
						 
				<input type = "text" name = "search_kw" class='typeahead' placeholder = "name" style = "width:500px" value = '<?php echo $value;?>'autofocus/></div>
            <input type = "submit" name = "submit" value = "Search brand"/></p>
        </fieldset>	
    </div>
    </form>
    <h2><?php echo __('Brands'); ?>
     <a href="<?php echo $rootURL;?>brands/export/<?php echo $queryStr;?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png', array('fullBase' => true));?></a></h2> 
   
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('brand') ?></th>
                <th scope="col"><?= $this->Paginator->sort('company') ?></th>
				<th>Products Count</th>
                <th scope="col"><?= $this->Paginator->sort('status') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
			
			foreach ($brands as $brand):
			$count = 0;
			$count = count($brand->products);
			?>
            <tr>
                <td><?= $this->Number->format($brand->id) ?></td>
                <td><?= h($brand->brand) ?></td>
                <td><?= h($brand->company) ?></td>
				<td><?=$count?></td>
                <td><?= $active[$brand->status] ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $brand->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $brand->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $brand->id], ['confirm' => __('Are you sure you want to delete # {0}?', $brand->id)]) ?>
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
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Brand'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
	var user_dataset = new Bloodhound({
	datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	//prefetch: "/products/data",
	remote: {
	  url: "/brands/brand_suggestions?search=%QUERY",
	//  url: "/mobile-repair-prices/brandsuggestions?search=%QUERY",
	  wildcard: "%QUERY"
	}
      });
      
      $('#remote .typeahead').typeahead(null, {
	name: 'brand',
	display: 'brand',
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
	  suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:400px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{brand}}</a></strong></div>'),
	      header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	      footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------hpwaheguru.co.uk---------</b></div>"),
	}
      });
</script>