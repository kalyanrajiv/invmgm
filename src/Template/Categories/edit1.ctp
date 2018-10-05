<div class="categories form">
<?php echo $this->Form->create('Category',  array('enctype' => 'multipart/form-data')); ?>
	<fieldset>
		<legend><?php echo __('Edit Category'); ?></legend>
	<?php
    //pr($this->request);
		echo $this->Form->input('id',array('type' => 'hidden'));
		echo $this->Form->input('category');
		$prev_category = $this->request['data']['id'].":".$this->request['data']['category'];
		echo $this->Form->hidden('prev_category',array('value' => $prev_category));
		//echo $this->Form->input('id_name_path');
		echo $this->Form->input('description');
        echo $this->Form->input('image', array('between' => '<br />','type' => 'file'));
		echo $this->Form->input('image_dir', array('type' => 'hidden'));
		$imageDir = WWW_ROOT."files".DS.'Categories'.DS.'image'.DS.$this->request['data']['id'].DS;
		$imageName1 = $this->request['data']['image'];
		//pr($imageDir);
        //pr($imageName);die;
        if(is_array($imageName1)){
            $imageName = $imageName1['name'];
        }else{
            $imageName = $imageName1;
        }
        $absoluteImagePath = $imageDir.$imageName;
        //pr($absoluteImagePath);die;
		$imageURL = "/thumb_no-image.png";
		if(file_exists($absoluteImagePath)){
					$imageURL = "/files/Categories/image/".$this->request['data']['id']."/$imageName";
				}
					
					echo $this->Html->link(
							  $this->Html->image($imageURL, array('fullBase' => true,'width' => '100px','height' => '100px')),
							  array('controller' => 'category','action' => 'edit', $this->request['data']['id']),
							  array('escapeTitle' => false, 'title' => $this->request['data']['category'])
							 );
		echo $this->Form->input('remove', array('type' => 'checkbox', 'label' => 'Remove existing file'));
		
		echo $this->Form->input('parent_id',array('label' => 'Parent',
							  'options' => $parentCategories,
							  'empty' => 'Choose Category',
							  'disabled' => 'disabled'));
		//echo $this->Form->input('parent_id', array('options' => $parentCategories, 'type' => 'select','readonly' => 'readonly'));
		//echo $this->Form->input('top');
		//echo $this->Form->input('column');
		echo $this->Form->input('sort_order');
		echo $this->Form->input('status',['type' => 'checkbox']);
	?>
	</fieldset>
<?php
echo $this->Form->submit('Submit');
echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Category.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('Category.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Categories'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('New Parent Category'), array('controller' => 'categories', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div>
