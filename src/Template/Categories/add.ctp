<div class="categories form">
<?php echo $this->Form->create($category,array('enctype' => 'multipart/form-data'));
?>
	<fieldset>
		<legend><?php echo __('Add Category'); ?></legend>
	<?php
		echo $this->Form->input('Category.category' ,array('enctype' => 'multipart/form-data'));
		//echo $this->Form->input('id_name_path');
		echo $this->Form->input('Category.description');
		//echo $this->Form->input('image');
		echo $this->Form->input('Category.image', array('between' => '<br />','type' => 'file'));
		echo $this->Form->input('Category.image_dir', array('type' => 'hidden'));
		echo $this->Form->input('Category.parent_id',array('label' => 'Parent',
							  'options' => $parentCategories,
							  'empty' => 'Choose Category'));
		//echo $this->Form->input('top');
		//echo $this->Form->input('column');
		echo $this->Form->input('Category.sort_order',array('type' => 'hidden'));
		echo $this->Form->input('Category.status',array('value' => 1, 'type' => 'hidden'));
	?>
	</fieldset>
<?php
echo $this->Form->submit("Submit",array('name'=>'submit'));
echo $this->Form->end(); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Categories'), array('controller' => 'categories', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Category'), array('controller' => 'categories', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
	</ul>
</div>
