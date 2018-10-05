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
use Cake\Datasource\ConnectionManager;

class TroubleshootController extends AppController
{
    public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('ScreenHint');
        $this->loadComponent('TableDefinition');
        $this->loadComponent('SessionRestore');
        $this->loadModel('Kiosks');

    }
    
    public function troubleshootProducts(){
		$kiosk_id = '';
		$productRS = array();
		
		$kiosks_query = $this->Kiosks->find('list',[
                                                'conditions' => ['Kiosks.status' => 1],
                                                //'recursive' => -1,
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'order' => ['Kiosks.name asc'],
                                             ]
                                    );
		$kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		//ksort($kiosks);
		if ($this->request->is(array('post', 'put'))) {
			
            //pr($this->request['data']);die;
			 $kiosk_id = $this->request['data']['Troubleshoot']['kiosk_id'];
			if($kiosk_id == 0){
				unset($kiosks[0]);
				foreach($kiosks as $key => $value){
					$kiosk_id = $key;
					$productQuery = "SELECT `kp`.`id`, `kp`.`product_code`, `p`.`id`, `p`.`product_code` FROM `kiosk_{$kiosk_id}_products` as kp LEFT JOIN `products` as p ON `kp`.`id` = `p`.`id` WHERE `kp`.`product_code` <> `p`.`product_code`";
					//$productRS[$kiosk_id] = $this->Product->query($productQuery);
                    $conn = ConnectionManager::get('default');
                    $stmt = $conn->execute($productQuery); 
                    $productRS[$kiosk_id] = $stmt ->fetchAll('assoc');
				}
				$kiosk_id = 0;
			}else{
					$productQuery = "SELECT `kp`.`id`, `kp`.`product_code`, `p`.`id`, `p`.`product_code` FROM `kiosk_{$kiosk_id}_products` as kp LEFT JOIN `products` as p ON `kp`.`id` = `p`.`id` WHERE `kp`.`product_code` <> `p`.`product_code`";
					//$productRS[$kiosk_id] = $this->Product->query($productQuery);
                    $conn = ConnectionManager::get('default');
                    $stmt = $conn->execute($productQuery); 
                    $productRS[$kiosk_id] = $stmt ->fetchAll('assoc');
			}
		}
		
		$kiosks[0] = "All";
		$this->set(compact('kiosks','kiosk_id','productRS'));
	}
    
    public function troubleshootW2k(){
		$kiosk_id = '';
		$productRS = array();
		
		$kiosks_query = $this->Kiosks->find('list',[
                                                'conditions' => ['Kiosks.status' => 1],
                                                //'recursive' => -1,
                                                'keyField' => 'id',
                                                'valueField' => 'name',
                                                'order' => ['Kiosks.name asc'],
                                             ]
                                    );
		$kiosks_query = $kiosks_query->hydrate(false);
        if(!empty($kiosks_query)){
            $kiosks = $kiosks_query->toArray();
        }else{
            $kiosks = array();
        }
		//ksort($kiosks);
		if ($this->request->is(array('post', 'put'))) {
			 $kiosk_id = $this->request['data']['Troubleshoot']['kiosk_id'];
			if($kiosk_id == 0){
				unset($kiosks[0]);
				foreach($kiosks as $key => $value){
					$kiosk_id = $key;
					$productQuery = "SELECT `kp`.`id`, `kp`.`product_code`, `p`.`id`, `p`.`product_code` FROM `products` as p LEFT JOIN `kiosk_{$kiosk_id}_products` as kp ON `p`.`id` = `kp`.`id` WHERE `p`.`product_code` <> `kp`.`product_code`";
					//$productRS[$kiosk_id] = $this->Product->query($productQuery);
                    $conn = ConnectionManager::get('default');
                    $stmt = $conn->execute($productQuery); 
                    $productRS[$kiosk_id] = $stmt ->fetchAll('assoc');
				}
				$kiosk_id = 0;
			}else{
					$productQuery = "SELECT `kp`.`id`, `kp`.`product_code`, `p`.`id`, `p`.`product_code` FROM `products` as p LEFT JOIN `kiosk_{$kiosk_id}_products` as kp ON `p`.`id` = `kp`.`id` WHERE `p`.`product_code` <> `kp`.`product_code`";
					//$productRS[$kiosk_id] = $this->Product->query($productQuery);
                    $conn = ConnectionManager::get('default');
                    $stmt = $conn->execute($productQuery); 
                    $productRS[$kiosk_id] = $stmt ->fetchAll('assoc');
			}
		}
		$kiosks[0] = "All";
		$this->set(compact('kiosks','kiosk_id','productRS'));
	}
}
?>