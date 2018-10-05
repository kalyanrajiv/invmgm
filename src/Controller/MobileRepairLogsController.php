<?php
namespace App\Controller;
use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;
use Cake\I18n;


class MobileRepairLogsController extends AppController
{
    public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
       public function initialize(){
        parent::initialize();
        $paymentType=Configure::read('payment_type');
	   $newDiscountArr = array();
	    for($i=0; $i<=50; $i++){
			  $newDiscountArr[$i] = "$i %";
		  }
		Configure::write('new_discount',$newDiscountArr);
		$newDiscountArr = Configure::read('new_discount');
		$this->set(compact('paymentType','newDiscountArr'));
        $this->set(compact('paymentType'));
		
        $this->loadComponent('ScreenHint');
		$this->loadComponent('SessionRestore');
		$this->loadComponent('TableDefinition');
		
        $this->loadModel('ProductReceipts');
        $this->loadModel('Customers');
        $this->loadModel('PaymentDetails');
        $this->loadModel('Users');
		$this->loadModel('KioskProductSales');
        $this->loadModel('Categories');
        $this->loadModel('Kiosks');
		$this->loadModel('MobileRepairLogs');
        $this->loadModel('PaymentDetails');
        $this->loadModel('ProductPayments');
        $this->loadModel('ProductReceipts');
        $this->loadModel('SaleLogs');
		$this->loadModel('FaultyProduct');
		$this->loadModel('RetailCustomers');
		$CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
		$this->set(compact('CURRENCY_TYPE' ));
    }
    
    public function viewLogs($id=null){
		$repairStatusUserOptions = Configure::read('repair_statuses_user');
		$repairStatusTechnicianOptions = Configure::read('repair_statuses_technician');
		$kiosks_query = $this->Kiosks->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'conditions' => ['Kiosks.status' => 1]
                                             ]
                                      );
        $kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		$mobileRepairLogs_query = $this->MobileRepairLogs->find('all',array(
								'conditions' => array('MobileRepairLogs.mobile_repair_id' => $id),
								'order' => 'MobileRepairLogs.id DESC'
								)
								 );
        $mobileRepairLogs_query = $mobileRepairLogs_query->hydrate(false);
        if(!empty($mobileRepairLogs_query)){
            $mobileRepairLogs = $mobileRepairLogs_query->toArray();
        }else{
            $mobileRepairLogs = array();
        }
		$users_query = $this->Users->find('list',[
                                                'keyField' => 'id',
                                                'valueField' => 'username'
                                           ]
                                    );
        $users_query = $users_query->hydrate(false);
        if(!empty($users_query)){
            $users = $users_query->toArray();
        }else{
            $users = array();
        }
		$repairStatus = $repairStatusUserOptions+$repairStatusTechnicianOptions;
		$this->set(compact('mobileRepairLogs','kiosks','users','repairStatus'));		
	}
}
?>