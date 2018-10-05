<div id='cssmenu'>
   <ul>
	  <li><?php echo $this->Html->link(__('Unlock'), array('controller' => 'mobile_unlocks', 'action' => 'index')); ?>
	  <!--<a href='#'><span>Unlock</a></span> -->
		 <ul>
			  <li><?php echo $this->Html->link(__('Mobile Unlocks'), array('controller' => 'mobile_unlocks', 'action' => 'index')); ?></li>
			  <li><?php echo $this->Html->link(__('Unlocking Prices'), array('controller' => 'mobile_unlock_prices', 'action' => 'index')); ?></li>
		 </ul>
	  </li>
	  <li><?php echo $this->Html->link(__('Brands'), array('controller' => 'brands', 'action' => 'index')); ?></li>
	  <li><?php echo $this->Html->link(__('Mobile Models'), array('controller' => 'mobile_models', 'action' => 'index')); ?></li>
	  <li class='last'><a href='/messages/inbox'><span>Misc</span></a>
		 <ul>
			<li><?php echo $this->Html->link(__('Mail ('.$newEmailCount.')'), array('controller' => 'messages', 'action' => 'inbox')); ?></li>
		 </ul>
	  </li>
	  <?php echo $notificationsMenu;?>
   </ul>
</div>