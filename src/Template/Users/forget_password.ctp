<div class="users form">
    <?php echo $this->Form->create('User',array('url' => array('action' => 'forget_password'))); ?>
    <fieldset>
        <legend><?php echo __('Forgot Password'); ?></legend>
        <?php echo $this->Form->input('email',array('style' => 'width:200px;','name' => 'User[email]'));?>
        <?php echo $this->Form->input('mobile',array('label' => 'Mobile','style' => 'width:200px;','name' => 'User[mobile]'));?>
    </fieldset>
    <?php
    echo $this->Form->submit('Recover',array('div' => false));
    echo $this->Form->end(); ?>
</div>
<link rel="stylesheet" href="resources/stylesheets/eText/infoboxes.css" type="text/css">