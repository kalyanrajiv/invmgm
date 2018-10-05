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

class AclController extends AppController{
     public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
     public function initialize(){
        parent::initialize();
        $activeOptions = $active = Configure::read('active');
        $featuredOptions = Configure::read('options.featured');
        $this->set(compact('featuredOptions'));
        $this->set(compact('activeOptions'));
     }
    public function permissions()
    {
         $this->Flash->success(__('Under Development'));
         return $this->redirect([ 'controller' => '/home','action' => 'dashboard']);
    }
}
