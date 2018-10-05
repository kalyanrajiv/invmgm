<table>
    <tr>
        <td>
            <?php echo $this->Html->link(__('Transient Orders'), array('controller' => 'kiosk_orders',
                                                                       'action' => 'transient_orders'));
            ?>
        </td>
        <td>
            <?php if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER): ?>
            <?php echo $this->Html->link(__('Placed Orders'), array('controller' => 'kiosk_orders',
                                                                       'action' => 'placed_orders'));
            ?>
            <?php else: ?>
            <?php echo $this->Html->link(__('Place Order'), array('controller' => 'kiosk_orders',
                                                                       'action' => 'place_order'));
            ?>
            <?php endif; ?>
        </td>
        <td>
            <?php /*echo $this->Html->link(__('Receive Order'), array('controller' => 'kiosk_orders',
                                                                       'action' => 'receive_order'));*/
            ?>
        </td>
       <td>
            <?php /*echo $this->Html->link(__('Order Requests'), array('controller' => 'kiosk_orders',
                                                                       'action' => 'order_requests'));*/
            ?>
        </td>
        <td>
            <?php if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER): ?>
            <?php echo $this->Html->link(__('Kiosk Stock'), array('controller' => 'stock_transfer',
                                                                       'action' => 'kiosk_stock'));
            ?>
            <?php endif; ?>
        </td>
         <td>
            <?php echo $this->Html->link(__('Order Dispute'), array('controller' => 'stock_transfer',
                                                                       'action' => 'disputed_orders'));
            ?>
        </td>
    </tr>
</table>