<?php
namespace App\Controller;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\ORM\Entity;
 use Cake\ORM\AssociationCollection;
 use Cake\ORM\Association;
 use Cake\Datasource\ConnectionManager;
use Cake\Database\Schema\Collection;
use Cake\Database\Schema\TableSchema;

class GroupsController extends AppController
{

     public function initialize(){
        parent::initialize();
        $activeOptions = $active = Configure::read('active');
        $featuredOptions = Configure::read('options.featured');
        $this->set(compact('featuredOptions'));
        $this->set(compact('activeOptions'));
     }
     
    public function index()
    {
          if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
               $this->Flash->success(__('You are not authorized to access that location.'));
               return $this->redirect(['controller' => 'home','action' => 'dashboard']);
              
          }else{
               $groups = $this->paginate($this->Groups);
               $this->set(compact('groups'));
               $this->set('_serialize', ['groups']);
          }
        
    }

    
    public function view($id = null){
         if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
		   $this->Flash->success(__('You are not authorized to access that location.'));
           return $this->redirect(['controller' => 'home','action' => 'dashboard']);
		  
          }else{
                $group = $this->Groups->get($id, [
                    'contain' => ['Users']
               ]);
               $this->set('group', $group);
               $this->set('_serialize', ['group']);
          }
       
    }

    
    public function add(){
      if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
		   $this->Flash->success(__('You are not authorized to access that location.'));
           return $this->redirect(['controller' => 'home','action' => 'dashboard']);
		  
	  }else{
          $group = $this->Groups->newEntity();
          if ($this->request->is('post')) {
              $group = $this->Groups->patchEntity($group, $this->request->data);
              if ($this->Groups->save($group)) {
                  $this->Flash->success(__('The group has been saved.'));
  
                  return $this->redirect(['action' => 'index']);
              }
              $this->Flash->error(__('The group could not be saved. Please, try again.'));
          }
          $this->set(compact('group'));
          $this->set('_serialize', ['group']);
      }
    }

    
    public function edit($id = null)
    {
           if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
               $this->Flash->success(__('You are not authorized to access that location.'));
               return $this->redirect(['controller' => 'home','action' => 'dashboard']);
		  
          }else{
               $group = $this->Groups->get($id, [
                         'contain' => []
               ]);
               if ($this->request->is(['patch', 'post', 'put'])) {
                   $group = $this->Groups->patchEntity($group, $this->request->data);
                   if ($this->Groups->save($group)) {
                       $this->Flash->success(__('The group has been saved.'));
       
                       return $this->redirect(['action' => 'index']);
                   }
                   $this->Flash->error(__('The group could not be saved. Please, try again.'));
               }
               $this->set(compact('group'));
               $this->set('_serialize', ['group']);
          }
        
    }
 
    public function delete($id = null)
    {
          if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){
               $this->Flash->success(__('You are not authorized to access that location.'));
               return $this->redirect(['controller' => 'home','action' => 'dashboard']);
		  
          }else{
               $this->request->allowMethod(['post', 'delete']);
               $group = $this->Groups->get($id);
               if ($this->Groups->delete($group)) {
                   $this->Flash->success(__('The group has been deleted.'));
               } else {
                   $this->Flash->error(__('The group could not be deleted. Please, try again.'));
               }
               return $this->redirect(['action' => 'index']);
          }
        
    }
}
