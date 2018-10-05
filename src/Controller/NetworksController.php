<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig; 
class NetworksController extends AppController
{
	public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];
   public function index(){
        $networks = $this->paginate($this->Networks);
        $this->set(compact('networks'));
        $this->set('_serialize', ['networks']);
    }

    public function view($id = null){
        $network = $this->Networks->get($id);
        $this->set('network', $network);
        $this->set('_serialize', ['network']);
    }
    
   public function deleteCacheFiles(){
      $site_path = dirname(__FILE__);
	  $isMbwaheguru = strpos($site_path,"mbwaheguru");
      $path = array();
	  $domain_name = "";
	  $sites = Configure::read('site_full_url');
	  foreach($sites as $site_name => $site_path1){
			$isMbwaheguru = strpos($site_path,$site_name);
			if($isMbwaheguru){
				$domain_name = $site_path1;
			}
	  }
	  
	  $path[] = "/var/www/vhosts/$domain_name/httpdocs/tmp/cache/persistent/";
         $path[] = "/var/www/vhosts/$domain_name/httpdocs/tmp/cache/models/";
	  
      
      if(count($path) > 0){
         $count = 0;
         foreach($path as $key => $value){
             $scanned_directory = array_diff(scandir($value), array('..', '.'));
             if(!empty($scanned_directory)){
               foreach($scanned_directory as $scanned_key =>$scanned_value ){
                  $fullpath  = $value.$scanned_value;
                  unlink($fullpath);
                  $count++;
               }
            }
         }
      }
      $msg = $count." Files deleted";
      $this->Flash->success(__($msg));
      return $this->redirect(['action' => 'index']);
   }
   
    public function add(){
        $network = $this->Networks->newEntity();
        if ($this->request->is('post')) {
            $network = $this->Networks->patchEntity($network, $this->request->data);
            if ($this->Networks->save($network)) {
                $this->Flash->success(__('The network has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The network could not be saved. Please, try again.'));
        }
        $this->set(compact('network'));
        $this->set('_serialize', ['network']);
    }

    public function edit($id = null) {
        $network = $this->Networks->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $network = $this->Networks->patchEntity($network, $this->request->data);
            if ($this->Networks->save($network)) {
                $this->Flash->success(__('The network has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The network could not be saved. Please, try again.'));
        }
        $this->set(compact('network'));
        $this->set('_serialize', ['network']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Network id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $network = $this->Networks->get($id);
        if ($this->Networks->delete($network)) {
            $this->Flash->success(__('The network has been deleted.'));
        } else {
            $this->Flash->error(__('The network could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
