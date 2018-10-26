<?php
/**
  * @var \App\View\AppView $this
  */
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\I18n\Time;
?>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
    <ul>
		<li><?php echo $this->Html->link(__('New product Model'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Brand'), array('controller' => 'brands', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Mobile <br/>Unlock Prices'), ['controller' => 'mobile-unlock-prices', 'action' => 'index'],['escape' => false]); ?> </li>
		<li><?php echo $this->Html->link(__('New Mobile <br/>Unlock Price'), ['controller' => 'mobile-unlock-prices', 'action' => 'add'],['escape' => false]); ?> </li>
	</ul>
</div>
<div class="mobileModels index large-9 medium-8 columns content">
   
    <?php
		
		$queryStr = "";
		$rootURL = "";//$this->html->url('/', true);
		if( isset($this->request->query['search_kw']) ){
			$queryStr.="search_kw=".$this->request->query['search_kw'];
		}
		//pr($this->request->query);
		if( isset($this->request->query['brand']) ){
			foreach($this->request->query['brand'] as $key => $brandID){
				if(!empty($queryStr))
					 $queryStr.="&brand[$key] = $brandID";
				else
					$queryStr.="&brand[$key] = $brandID";
			}
		}
		
	?>
	<h2><?php echo __('Product Models'); ?>&nbsp;<a href='<?php echo $this->request->webroot;?>ProductModels/export/?<?php echo $queryStr;?>' target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png',array('fullBase' => true));?></a></h2>
     <?php if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
		
	}else{
		$value = '';
	}
	//pr($selectedBrandIds);
	?>
	<form action='<?php echo $this->request->webroot;?>product-models/search' method = 'get'>
		<div class="search_div">
			<fieldset>
				<legend>Search</legend>
			<td> 
				<table>
					<tr>
						<td>
						</td>
						<td colspan='2'><strong>Find by Brand &raquo;</strong></td>
					</tr>
					<tr>
						<td><div id='remote'><input type = "text" class='typeahead' name = "search_kw" value = '<?=$value;?>' placeholder = "Model" style = "width:343px" autofocus/></div></td>
						<td rowspan="3">
							<select name='brand[]' multiple="multiple" size='6'>
								<?php
										foreach($brands as $key => $brandid)
										{
                                            if(array_key_exists($key,$selectedBrandsArr)){
												$selected = "selected";
												echo "<option value =".$key." selected=".$selected.">".$brandid."</option>";
											}else{
												$selected = "";
												echo "<option value =".$key." select=".$selected.">".$brandid."</option>";
											}
										}
								?>
								
							</select> 
						</td>
						
							
					<tr>
						<td><h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4></td>
					</tr>					
					<tr>
						<td colspan='2'><input type = "submit" value = "Search Mobile" name = "submit"/></td>
					</tr>
				</table>							
		</fieldset>
		</div>
	</form>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('brand_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('model') ?></th>
                <th scope="col"><?= $this->Paginator->sort('brief_description') ?></th>
				<th scope="col"><?= ('Products Count') ?></th>
                <th scope="col"><?= $this->Paginator->sort('status') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productModels as $productModel): ?>
            <tr>
                <td><?= $this->Number->format($productModel->id) ?></td>
                <td><?= $productModel->has('brand') ? $this->Html->link($brands[$productModel->brand->id], ['controller' => 'Brands', 'action' => 'view', $productModel->brand->id]) : '' ?></td>
                <td><?= h($productModel->model) ?></td>
                <td><?= h($productModel->brief_description) ?></td>
				<td><?= ($productCount[$productModel->id]) ?></td>
                <td><?=  $activeOptions[$productModel->status] ?></td>
                <?php
                        $created = $productModel->created;
                          $created->i18nFormat(
                                                                            [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                                                    );
                        $created_1 =  $created->i18nFormat('dd-MM-yyyy HH:mm:ss');
                        $modified = $productModel->modified;
                         $modified->i18nFormat(
                                                                            [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                                                    );
						$modified_1 =  $modified->i18nFormat('dd-MM-yyyy HH:mm:ss');
                        
                ?>
                <td><?= date("d-m-Y",strtotime($created_1)) ?></td>
                <td><?= date("d-m-Y",strtotime($modified_1)) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $productModel->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $productModel->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $productModel->id], ['confirm' => __('Are you sure you want to delete # {0}?', $productModel->id)]) ?>
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
	   url: "/mobile-repair-prices/brand_suggestions?search=%QUERY",
	  wildcard: "%QUERY"
	}
      });
      
      $('#remote .typeahead').typeahead(null, {
	name: 'model',
	display: 'model',
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
	  suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:400px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{model}}</a></strong></div>'),
	      header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	      footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------<?php echo ADMIN_DOMAIN;?>---------</b></div>"),
	}
      });
</script>