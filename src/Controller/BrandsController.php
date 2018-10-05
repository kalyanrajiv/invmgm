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

class BrandsController extends AppController{
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

    public function index() {
	  $active = Configure::read('active');
	  $this->paginate = array(
							//'conditions' => array('system_user' => 0),
							'limit' => 50,
							'contain' => ['products']
							);
		$brands = $this->paginate('Brands');
        $this->set(compact('brands','active'));
        $this->set('_serialize', ['brands']);
    }

   
    public function view($id = null)
    {
        $brand = $this->Brands->get($id, [
            'contain' => ['Products']
        ]);
//pr($brand);die;
        $this->set('brand', $brand);
        $this->set('_serialize', ['brand']);
    }

   
    public function add_boloram($brand_data,$site_value){
       $conn = ConnectionManager::get($site_value);
        $statement = $conn->insert('brands', 
            [
               'brand' => $brand_data['brand'],
               'company' => $brand_data['company'],
               'status' => $brand_data['status']
            ]
         );
        
   }
   
    public function add() {
	  $sites = Configure::read('sites');
	 $path = dirname(__FILE__);
		$isboloRam = strpos($path, ADMIN_DOMAIN);
		if($isboloRam == false){
            $this->Flash->error(__("This function works only on ". ADMIN_DOMAIN));
			return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
		}
        $brand = $this->Brands->newEntity();
        if ($this->request->is('post')) {
            $brand = $this->Brands->patchEntity($brand, $this->request->data);
           // pr($brand);die;
            if ($this->Brands->save($brand)) {
                $brand_data = $this->request->data;
				 if(!empty($sites)){
					foreach($sites as $site_id => $site_value){
						 $this->add_boloram($brand_data,$site_value);
					}
				 }
                $this->Flash->success(__('The brand has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The brand could not be saved. Please, try again.'));
        }
        $this->set(compact('brand'));
        $this->set('_serialize', ['brand']);
    }

    
	public function edit($id = null) {
	  $sites = Configure::read('sites');
	 $path = dirname(__FILE__);
		$isboloRam = strpos($path, ADMIN_DOMAIN);
		if($isboloRam == false){
            $this->Flash->error(__("This function works only on ". ADMIN_DOMAIN));
			return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
		}
        $brand = $this->Brands->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            //pr($this->request);die;
		  unset($this->request->data['submit']);
            $brand = $this->Brands->patchEntity($brand, $this->request->data);
            if ($this->Brands->save($brand)) {
                $brand_data = $this->request->data;
			   if(!empty($sites)){
					foreach($sites as $site_id => $site_value){
						 $conn = ConnectionManager::get($site_value);
						 $statement = $conn->update('brands',$brand_data,['id' => $id]);		 
					}
			   }
                $this->Flash->success(__('The brand has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The brand could not be saved. Please, try again.'));
        }
        $this->set(compact('brand'));
        $this->set('_serialize', ['brand']);
	}

    public function delete($id = null)
    {
	//$this->Flash->error(__("You are unauthouriesed to delete Brand"));
	//return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
	 $path = dirname(__FILE__);
		$isboloRam = strpos($path, ADMIN_DOMAIN);
		if($isboloRam == false){
            $this->Flash->error(__("This function works only on ". ADMIN_DOMAIN));
			return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
		}
        $this->request->allowMethod(['post', 'delete']);
        $brand = $this->Brands->get($id,['contain' => "products"]);
		if(count($brand->products)  == 0){
		  $brand = $this->Brands->get($id);
			   if ($this->Brands->delete($brand)) {
					$this->delete_on_mb($id);
					$this->Flash->success(__('The brand has been deleted.'));
				} else {
					$this->Flash->error(__('The brand could not be deleted. Please, try again.'));
				}  
		}else{
		  $this->Flash->error(__('The brand could not be deleted. Please, try again.'));
		}
        

        return $this->redirect(['action' => 'index']);
    }
	
	private function delete_on_mb($id){
	  $sites = Configure::read('sites');
		  foreach($sites  as $key => $value){
			   $connection = ConnectionManager::get($value);
			   $stmt = $connection->execute("SELECT * FROM `brands` WHERE `id`=$id");
			   $Catdata = $stmt ->fetchAll('assoc');
			   //pr($Catdata);die;
			   if(count($Catdata) > 0){
				   $connection->execute("DELETE FROM `brands` WHERE `id`=$id");
			   }
		  }
	}
	
    private function generate_condition_array(){
		$searchKW = trim(strtolower($this->request->query['search_kw']));
		$conditionArr = array();
		if(!empty($searchKW)){
			$conditionArr['Brands.brand like'] =  strtolower("%$searchKW%");
		}
		return $conditionArr;
	}
	public function export(){
       $conditionArr = array();
		if(array_key_exists('search_kw',$this->request->query)){
			$conditionArr = $this->generate_condition_array();
		}
		if(count($conditionArr)>=1){
			 $query = $this->Brands->find('all', [
                'conditions' => $conditionArr,
                 'limit' => 20
            ]);
        }else{
			 $query = $this->Brands->find('all');
             
		}
		$query = $query->hydrate(false);
        $brands = $query->toArray();
        $tmpBrands = array(); 
		foreach($brands as $key => $brand){
		 $tmpBrands[] = $brand ;
		}
       // pr($tmpBrands);die;
		$this->outputCsv('Brand_'.time().".csv" ,$tmpBrands);
		$this->autoRender = false;
	}	
	public function search($keyword = ""){
        $this->loadModel('Brands');
        $active = Configure::read('active');
		$conditionArr = $this->generate_condition_array();
        /*$query = $this->Brands->find('all', [
            'conditions' => $conditionArr,
             'limit' => 50
        ]);*///die;
	   $this->paginate = [
					'conditions'=>	$conditionArr,
					'limit' => 50		
					];
        $results =    $this->paginate($this->Brands);
        $brands = $results->toArray();
		$this->set(compact('brands','active'));
		//$this->layout = 'default'; 
		//$this->viewPath = 'Products';
		$this->render('index');
	}
	 
	  public function brandSuggestions($search = ""){
	//echo'hi';die;
		if(array_key_exists('search',$this->request->query)){
			$search = strtolower($this->request->query['search']);
		}
		$brandArr = array();
		if(!empty($search)){
           $query = $this->Brands->find('all',array(
										'fields' => array('id','brand'),
										'conditions' => array("LOWER(`Brands`.`brand`) like '%$search%'"),
										//'recursive' => -1,
										  ));
          
            if(!empty($query)){
                $brands = $query->toArray();
            }
        }
//       pr($brands);
		foreach($brands as $brand){
			$brandArr[] = array('id' => $brand->id, 'brand' => $brand->brand);
		}
		// pr($brandArr);die;
		echo json_encode($brands);
		$this->viewBuilder()->layout(false);
		die;
	}
}
