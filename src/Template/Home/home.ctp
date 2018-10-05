<table cellspacing='0' cellpadding='0' width='100%'><tr><td align='center'>
<?php if ($this->request->session()->read('Auth.User.id')){ ?>
<?php echo $this->Html->link(__('Dashboard'), array('controller' => 'home', 'action' => 'dashboard'),array('style' => 'font-size:20px;')); ?>
<?php }else{ ?>
<?php echo $this->Html->link(__('Login'), array('controller' => 'users',
                                                                       'action' => 'login'),array('style' => 'font-size:20px;'));
            ?>
            
<?php }
?>
</td></tr>
            </table>