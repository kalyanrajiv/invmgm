<?php
    namespace App\Controller\Component;
    use Cake\Controller\Component;
    use Cake\ORM\TableRegistry;
    use Cake\Network\Session;
    
    //usage e.g : $this->TableDefinition->get_table_defination('product_table',$kiosk_id);
    class SessionRestoreComponent extends Component {
        public $uses = array('SessionRestoreComponent', 'SessionBackups');
        
        public function update_session_backup_table($currentController = '', $currentAction = '', $session_key = '', $session_detail = array(), $kioskId = ''){
            
            $SessionBackups = TableRegistry::get("SessionBackups");
            
            $json_session_detail = json_encode($session_detail);
            $userId = $this->request->session()->read('Auth.User.id');
            //$kioskId = CakeSession::read('kiosk_id');
            //echo "update_session_backup_table - Kiosk ID::".$kioskId;
            if(empty($kioskId)){
                $kioskId = 10000;
            }
            //$this->SessionBackup = ClassRegistry::init('SessionBackup');
            $checkIfExist_query = $SessionBackups->find('all', array('conditions' => array('SessionBackups.controller' => $currentController, 'SessionBackups.action' => $currentAction, 'SessionBackups.session_key' => $session_key, 'SessionBackups.user_id' => $userId, 'SessionBackups.kiosk_id' => $kioskId)));
            $checkIfExist_query = $checkIfExist_query->hydrate(false);
            if(!empty($checkIfExist_query)){
                $checkIfExist = $checkIfExist_query->first();
            }else{
                $checkIfExist = array();
            }
            //--------code for reading cake query---
                //$dbo = $this->SessionBackup->getDatasource();
                //$logData = $dbo->getLog();
                //$getLog = end($logData['log']);
                //echo "SessionBackup:".$getLog['query'];
                //die;
                //--------code for reading cake query---
            if((int)$userId){
                //pr($checkIfExist);die;
                if(count($checkIfExist)){
                    $sessionBackupId = $checkIfExist['id'];
                    $entity = $SessionBackups->get($sessionBackupId);
                    $sessionBackupData = array(
                                'id' => $sessionBackupId,
                                'session_detail' => $json_session_detail
                                        );
                    $SessionBackup = $SessionBackups->patchEntity($entity, $sessionBackupData,['validate' => false]);
                }else{
                    $sessionBackupData = array(
                                'controller' => $currentController,
                                'action' => $currentAction,
                                'session_key' => $session_key,
                                'session_detail' => $json_session_detail,
                                'user_id' => $userId,
                                'kiosk_id' => $kioskId
                                           );
                    $SessionBackup = $SessionBackups->newEntity($sessionBackupData,['validate' => false]);
                    $SessionBackup = $SessionBackups->patchEntity($SessionBackup, $sessionBackupData,['validate' => false]);
                }
                
               //pr($SessionBackup);die;
                $SessionBackups->save($SessionBackup);
                //--------code for reading cake query---
                //$dbo = $this->SessionBackup->getDatasource();
                //$logData = $dbo->getLog();
                //$getLog = end($logData['log']);
                //echo "SessionBackup:".$getLog['query'];
                //--------code for reading cake query---
            }
        }
        
        public function delete_from_session_backup_table($currentController = '', $currentAction = '', $session_key = '', $kioskId = ''){
           
           $SessionBackups = TableRegistry::get("SessionBackups");
             $userId = $this->request->session()->read('Auth.User.id');
            //$kioskId = CakeSession::read('kiosk_id');
            if(empty($kioskId)){
                $kioskId = 10000;
            }
            $checkIfExist_query = $SessionBackups->find('all', array('conditions' => array('SessionBackups.controller' => $currentController, 'SessionBackups.action' => $currentAction, 'SessionBackups.session_key' => $session_key, 'SessionBackups.user_id' => $userId, 'SessionBackups.kiosk_id' => $kioskId)));
            
            $checkIfExist_query = $checkIfExist_query->hydrate(false);
            if(!empty($checkIfExist_query)){
                $checkIfExist = $checkIfExist_query->first();
            }else{
                $checkIfExist = array();
            }

            if((int)$userId){
                if(count($checkIfExist)){
                    $sessionBackupId = $checkIfExist['id'];
                    $entity = $SessionBackups->get($sessionBackupId);
                    $SessionBackups->delete($entity);
                }
            }
        }
        
        public function restore_from_session_backup_table($currentController = '', $currentAction = '', $session_key = '', $kioskId = ''){
           $userId = $this->request->session()->read('Auth.User.id');
            //$kioskId = CakeSession::read('kiosk_id');
            echo "Kiosk ID:".$kioskId;
            if(empty($kioskId)){
                $kioskId = 10000;
            }
             $SessionBackups = TableRegistry::get("SessionBackups");
            if($currentAction == 'search')$currentAction='index';
            
            $checkIfExist_query = $SessionBackups->find('all', array('conditions' => array('controller' => $currentController, 'action' => $currentAction, 'session_key' => $session_key, 'user_id' => $userId, 'kiosk_id' => $kioskId)));
            //pr($checkIfExist_query);die;
            $checkIfExist_query = $checkIfExist_query->hydrate(false);
            if(!empty($checkIfExist_query)){
                $checkIfExist = $checkIfExist_query->first();
            }else{
                $checkIfExist = array();
            }
            
            //pr($checkIfExist);die;
            if((int)$userId){  
                if(count($checkIfExist)){
                    $retrievedSession = json_decode($checkIfExist['session_detail'], true);
                    if(count($retrievedSession)){
                        $this->session = new Session();
                        $messages = $this->request->session()->write($checkIfExist['session_key'],$retrievedSession);
                        
                        //CakeSession::write($checkIfExist['session_key'], $retrievedSession);
                        $success = 'Success';
                    }else{
                        $success = 'Failure';
                    }
                }else{
                    if($currentController == 'stock_initializers' && $currentAction == 'search' && $session_key == 'StockInitBasket'){
                        $currentController = 'stock_transfer';
                        $currentAction = 'index';
                        $session_key = 'Basket';
                        $checkIfExist_query = $SessionBackups->find('all', array('conditions' => array('controller' => $currentController, 'action' => $currentAction, 'session_key' => $session_key, 'user_id' => $userId, 'kiosk_id' => $kioskId)));
                         $checkIfExist_query = $checkIfExist_query->hydrate(false);
                        if(!empty($checkIfExist_query)){
                            $checkIfExist = $checkIfExist_query->first();
                        }else{
                            $checkIfExist = array();
                        }
                        if(count($checkIfExist)){
                            $retrievedSession = json_decode($checkIfExist['session_detail'], true);
                            if(count($retrievedSession)){
                                $this->request->session()->write($checkIfExist['session_key'], $retrievedSession);
                                return $success = 'Success';
                            }
                        }else{
                            return 'Failure';
                        }
                    }
                    $success = 'Failure';
                }
            }else{
                $success = 'Failure';
            }
            return $success;
        }
        
        public function append_2_backup_table($currentController = '', $currentAction = '', $session_key = '', $session_detail = array(), $kioskId = ''){
            $SessionBackups = TableRegistry::get("SessionBackups");
            $json_session_detail = json_encode($session_detail);
            $userId = $this->request->session()->read('Auth.User.id');
            //$kioskId = CakeSession::read('kiosk_id');
            //echo "update_session_backup_table - Kiosk ID::".$kioskId;
            if(empty($kioskId)){
                $kioskId = 10000;
            }
            //$this->SessionBackup = ClassRegistry::init('SessionBackup');
            $checkIfExist_query = $SessionBackups->find('all', array('conditions' => array('controller' => $currentController, 'action' => $currentAction, 'session_key' => $session_key, 'user_id' => $userId, 'kiosk_id' => $kioskId)));
            $checkIfExist_query = $checkIfExist_query->hydrate(false);
            if(!empty($checkIfExist_query)){
                $checkIfExist = $checkIfExist_query->first();
            }else{
                $checkIfExist = array();
            }
            if((int)$userId){
                if(count($checkIfExist)){
                    
                    $session_old_detail = $checkIfExist['session_detail'];
                    $session_old_detail =  (array)json_decode($session_old_detail);
                    foreach($session_detail as $s_key => $s_value){
                        if(array_key_exists($s_key,$session_old_detail)){
                            $session_old_detail[$s_key] = $s_value;
                        }else{
                            $session_old_detail[$s_key] = $s_value;
                        }
                    }
                    $json_session_new_detail = json_encode($session_old_detail);
                    $sessionBackupId = $checkIfExist['id'];
                    $sessionBackupData = array(
                                'session_detail' => $json_session_new_detail
                                        );
                    $SessionBackupsEntity = $SessionBackups->get($sessionBackupId);
                }else{
                    $sessionBackupData = array(
                                'controller' => $currentController,
                                'action' => $currentAction,
                                'session_key' => $session_key,
                                'session_detail' => $json_session_detail,
                                'user_id' => $userId,
                                'kiosk_id' => $kioskId
                                           );
                    $SessionBackupsEntity = $SessionBackups->newEntity($sessionBackupData,['validate' => false]);
                }
                //pr($sessionBackupData);die;
                $SessionBackupsEntity = $SessionBackups->patchEntity($SessionBackupsEntity,$sessionBackupData,['validate' => false]);
                $SessionBackups->save($SessionBackupsEntity,['validate' => false]);
            }
        }
        
        
    }
?>