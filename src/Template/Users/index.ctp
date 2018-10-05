<div class="users index">
	<?php if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
		
	}else{
		$value = '';
	}?>
	<form action='<?php echo $this->request->webroot;?>users/search' method='get'>
		<div class="search_div">
		  <fieldset>
				<legend>Users</legend>
				<table>
					<tr>
						<td><div id='remote'>
						<input name='search_kw' class='typeahead' type="text" value = '<?= $value;?>' placeholder="user@mail.com or username" style = "width:520px"autofocus /></div></td>
						<td><input type="submit" name='submit' value='Search User' /></td>
					</tr>
				</table>
			</fieldset>
		   
		</div>
	</form>
    <h2><?php echo __('Users'); ?></h2>
   	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th nowrap><?php echo $this->Paginator->sort('f_name'); ?></th>
			 
			<th><?php echo $this->Paginator->sort('email'); ?></th>
			<th><?php echo $this->Paginator->sort('username'); ?></th>
			 
			<th><?php echo $this->Paginator->sort('group_id'); ?></th>
			<th><?php echo $this->Paginator->sort('mobile'); ?></th>
			 
			<th nowrap><?php echo $this->Paginator->sort('address_1'); ?></th>
		 
			<th><?php echo $this->Paginator->sort('active'); ?></th>
			 
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
    </thead>
        <tbody>
          <?php foreach ($users as $user):
		  ?>
            <tr>
                <td><?= $this->Number->format($user->id) ?></td>
                <td><?= h($user->f_name) ?></td>
                
                <td><?= h($user->email) ?></td>
                <td><?= h($user->username) ?></td>
                
                <td><?= $user->has('group') ? $this->Html->link($user->group->name, ['controller' => 'Groups', 'action' => 'view', $user->group->id]) : '' ?></td>
                <td><?= h($user->mobile) ?></td>
               
                <td><?= h($user->address_1) ?></td>
               
                <td><?= $active[$this->Number->format($user->active)] ?></td>
                <td><?= h(date('d-m-Y',strtotime($user->modified))) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $user->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $user->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $user->id], ['confirm' => __('Are you sure you want to delete # {0}?', $user->id)]) ?>
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
		<li><?php echo $this->Html->link(__('New User'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Groups'), array('controller' => 'groups', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Group'), array('controller' => 'groups', 'action' => 'add')); ?> </li>
	</ul>
</div>
<script>
 var user_dataset = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  //prefetch: "/products/data",
  remote: {
    url: "/users/kioskUsers?search=%QUERY",
    wildcard: "%QUERY"
  }
});

$('#remote .typeahead').typeahead(null, {
  name: 'username',
  display: 'username',
  source: user_dataset,
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
    suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:400px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{username}}</a></strong></div>'),
	header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------hpwaheguru.co.uk---------</b></div>"),
  }
});
</script>