<?php
namespace App\Controller;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\ORM\TableRegistry;
use Cake\ORM\Entity;
use Cake\Datasource\ConnectionManager;
use App\Controller\AppController;

class CategoriesController extends AppController{
     public $helpers = [
         'Paginator' => ['templates' => 'paginatortemplates']
         ];

    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Categories');
        $activeOptions = Configure::read('active');
        $featuredOptions = Configure::read('featured');
        $CURRENCY_TYPE = Configure::read('CURRENCY_TYPE');
        $this->set(compact('activeOptions','CURRENCY_TYPE'));
        $this->set(compact('featuredOptions'));
        
    }
    public function index(){
          $active = Configure::read('active');
        $this->paginate = [
           'contain' => ['ParentCategories','ChildCategories'],
            
             'order' => ['Categories.id desc'] 
        ];
         $Categories_query = $this->paginate($this->Categories);
         if(!empty($Categories_query)){
             $categories = $Categories_query->toArray();
        }
		$product_count = array();
		foreach($categories as $key => $value){
		  $id = $value->id;
		  $catids = $this->getChildren($id);
		  if(empty($catids)){
			$catids = array($id);   
		  }else{
			   $catids[] =$id;
		  }
		  $product_res = $this->Products->find('all',['conditions' => ['category_id IN' => $catids]])->toArray();
		  $product_count[$id] = count($product_res);
		}
       // pr($categories);
        $this->set(compact('categories','active','product_count'));
        $this->set('_serialize', ['categories']);
    }
    
    private function generate_condition_array(){
		$searchKW = trim($this->request->query['search_kw']);
		$conditionArr = array();
		if(!empty($searchKW)){
            $conditionArr['OR']['Categories.category like'] =  strtolower("%$searchKW%");
            $conditionArr['OR']['Categories.description like'] =  strtolower("%$searchKW%");
		}
		return $conditionArr;
	}
    public function search($keyword = ""){
		  $active = Configure::read('active');
		  $this->loadModel('Categories');
		  $conditionArr = $this->generate_condition_array();
		  $query = $this->Categories->find('all', [
                'conditions' => [$conditionArr],
                'limit' => 20
		  ]);
          
		  $results =    $this->paginate($query);
		  $product_count = array();
		  if(!empty($results)){
			   $categories = $results->toArray();
			   foreach($categories as $key => $value){
			      $id = $value->id;
			      $catids = $this->getChildren($id);
			      if(empty($catids)){
					$catids = array($id);   
			      }else{
					$catids[] =$id;
		          }
					$product_res = $this->Products->find('all',['conditions' => ['category_id IN' => $catids]])->toArray();
					$product_count[$id] = count($product_res);
					
			   }
        }
        $this->set(compact('categories','active','product_count'));
		$this->render('index');		
	}
	
	 public function catSuggestions($search_kw = ""){
	 
		//if(array_key_exists('search',$this->request->query)){
		//	$search = strtolower($this->request->query['search']);
		//}
		//pr($this->request->query);
		  $searchKW = trim($this->request->query['search_kw']);
		$conditionArr = array();
		if(!empty($searchKW)){
            $conditionArr['OR']['Categories.category like'] =  strtolower("%$searchKW%");
            $conditionArr['OR']['Categories.description like'] =  strtolower("%$searchKW%");
		}
		$categoryArr = array();
		if(!empty($conditionArr)){
		     
           $query = $this->Categories->find('all',array(
										'fields' => array('category','description'),
										 'conditions' => [$conditionArr],
										//'recursive' => -1,
										  ));
          
            if(!empty($query)){
                $Categories = $query->toArray();
            }
        }
//       pr($brands);
		foreach($Categories as $sngCategories){
			$categoryArr[] = array('category' => $sngCategories->category, 'description' => $sngCategories->description);
		}
		// pr($brandArr);die;
		echo json_encode($categoryArr);
		$this->viewBuilder()->layout(false);
		die;
	}
	
    public function view($id = null) {
        $category = $this->Categories->get($id, [
           'contain' => ['ParentCategories',  'ChildCategories','Products']
        ]);

        $this->set('category', $category);
        $this->set('_serialize', ['category']);
    }

    public function add() {
	  $sites = Configure::read('sites');
	 $path = dirname(__FILE__);
		$isboloRam = strpos($path, ADMIN_DOMAIN);
		if($isboloRam == false){
            $this->Flash->error(__("This function works only on ". ADMIN_DOMAIN));
			return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
		}
        $category = $this->Categories->newEntity();
        if ($this->request->is('post')) {
		  $conn = ConnectionManager::get('default');
		  $stmt = $conn->execute("SELECT now() as created"); 
		  $createdArr = $stmt ->fetchAll('assoc');
		  
			$created = $createdArr[0]['created'];
			if((int)$this->request->data['Category']['parent_id']){
				
				//print_r($this->request->data['Category']);
				//echo (int)$this->request->data['Category']['parent_id'];
				$this->request->data['Category']['parent_id'];
				$parentCategory_query = $this->Categories->find('all',array('conditions' => array('Categories.id' => (int)$this->request->data['Category']['parent_id'])));
			   $parentCategory_query = $parentCategory_query->hydrate(false);
			   if(!empty($parentCategory_query)){
					$parentCategory = $parentCategory_query->first();
			   }else{
					$parentCategory = array();
			   }
				//new addition
				//$errors = $this->Data->invalidFields();
				//$this->Data->validates());
	   
				$parentIdNamePath = $parentCategory['id_name_path'];
				$tempVar = $parentCategory['id'].":".$parentCategory['category'];
				if(empty($parentIdNamePath)){
					$this->request->data['Category']['id_name_path'] = $parentIdNamePath.$tempVar;
				}else{
					$this->request->data['Category']['id_name_path'] = $parentIdNamePath."|".$tempVar;
				}
			}
	
			//capitalizing the first letter of category		
			$this->request->data['Category']['category'] = ucwords(trim(preg_replace('/\s+/',' ',$this->request['data']['Category']['category'])));
			//$this->Category->create();
			//$this->request->data['Category']['status'] = 1;
			$this->request->data['Category']['created'] = $created;
			$this->request->data['Category']['modified'] = $created;
		  
		  //pr($this->request);die;
		  $image_name = "";
		  if(array_key_exists('Category',$this->request->data)){
			   if(array_key_exists('image',$this->request->data['Category'])){
					$image_name = $this->request->data['Category']['image']['name'];
			   }
		  }
            $category = $this->Categories->patchEntity($category, $this->request->data['Category']); //,['validate' => false]
            if ($this->Categories->save($category)) {
			   
			   $id = $category->id;
                if(!empty($image_name)){
                   $id = $category->id;
					//$id = $user->id;
					$path = $this->request->webroot;
                    if(array_key_exists('Category',$this->request->data)){
                        if(!file_exists(WWW_ROOT."files/Categories/image/{$id}")){
                            if(mkdir(WWW_ROOT."files/Categories/image/{$id}")){      
                                if(rename(WWW_ROOT."files/Categories/image/{$image_name}", WWW_ROOT."files/Categories/image/{$id}/{$image_name}")){
                                  $query = "UPDATE categories SET image_dir = {$id} where id = {$id}";
                                    $query2 = "UPDATE categories SET image = '$image_name' WHERE id = $id";
                                    $conn = ConnectionManager::get('default');
                                    $stmt = $conn->execute($query);
                                    $stmt1 = $conn->execute($query2); 
                                }
                            }
                        }else{
                            if(rename(WWW_ROOT."files/Categories/image/{$image_name}", WWW_ROOT."files/Categories/image/{$id}/{$image_name}")){
                                  $query = "UPDATE categories SET image_dir = {$id} where id = {$id}";
                                    $query2 = "UPDATE categories SET image = '$image_name' WHERE id = $id";
                                    $conn = ConnectionManager::get('default');
                                    $stmt = $conn->execute($query);
                                    $stmt1 = $conn->execute($query2); 
                                }
                        }
                        
                           //if(mkdir(WWW_ROOT."files/Categories/image/{$id}")){      
                           //     if(rename(WWW_ROOT."files/Categories/image/{$image_name}", WWW_ROOT."files/Categories/image/{$id}/{$image_name}")){
                           //       $query = "UPDATE categories SET image_dir = {$id} where id = {$id}";
                           //         $query2 = "UPDATE categories SET image = '$image_name' WHERE id = $id";
                           //         $conn = ConnectionManager::get('default');
                           //         $stmt = $conn->execute($query);
                           //         $stmt1 = $conn->execute($query2); 
                           //     }
                           // }else{
                           //     if(rename(WWW_ROOT."files/Categories/image/{$image_name}", WWW_ROOT."files/Categories/image/{$id}/{$image_name}")){
                           //       $query = "UPDATE categories SET image_dir = {$id} where id = {$id}";
                           //         $query2 = "UPDATE categories SET image = '$image_name' WHERE id = $id";
                           //         $conn = ConnectionManager::get('default');
                           //         $stmt = $conn->execute($query);
                           //         $stmt1 = $conn->execute($query2); 
                           //     }
                           // }
                    }
                }
			   if(!empty($sites)){
					foreach($sites as $site_id => $site_value){
						 $this->create_boloram_catagory($this->request->data['Category'],$site_value);
					}
			   }
                $this->Flash->success(__('The category has been saved.'));
			   
                return $this->redirect(['action' => 'index']);
            }else{
                $errors = $category->errors();
               $err = array();
			   foreach($errors as $error){
					foreach($error as $key){
						 $err[] = $key;
					}
					//$err[] = $key." already in use";
			   }
			   $this->Flash->error(implode("</br>",$err),['escape' => false]);
            }
            $this->Flash->error(__('The category could not be saved. Please, try again.'));
        }
        $parentCategories_query = $this->Categories->ParentCategories->find('list', ['keyField' => 'id',
																					 'valueField' => 'category',
																					 'limit' => 200]);
		$parentCategories_query = $parentCategories_query->hydrate(false);
		if(!empty($parentCategories_query)){
		  $parentCategories = $parentCategories_query->toArray();
		}else{
		  $parentCategories = array();
		}
        $this->set(compact('category', 'parentCategories'));
        $this->set('_serialize', ['category']);
     }
    public function edit1($id = null) {
        $category = $this->Categories->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $category = $this->Categories->patchEntity($category, $this->request->data);
            if ($this->Categories->save($category)) {
                $this->Flash->success(__('The category has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The category could not be saved. Please, try again.'));
        }
        $parentCategories = $this->Categories->ParentCategories->find('list', ['limit' => 200]);
        $this->set(compact('category', 'parentCategories'));
        $this->set('_serialize', ['category']);
    }
	
	 public function edit($id = null) {
		    $sites = Configure::read('sites');
		  $path = dirname(__FILE__);
		$isboloRam = strpos($path, ADMIN_DOMAIN);
		if($isboloRam == false){
            $this->Flash->error(__("This function works only on ". ADMIN_DOMAIN));
			return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
		}
		  if (!$this->Categories->exists($id)) {
				  throw new NotFoundException(__('Invalid category'));
		  }
		  $category = $this->Categories->get($id);
		  if($this->request->is(array('post', 'put'))) {
			  //  pr($this->request->data);die;
			   $prevCategory = $this->request['data']['prev_category'];
			  //capitalizing the first letter of category
				$this->request->data['category'] = ucwords(trim(preg_replace('/\s+/',' ',$this->request['data']['category'])));
			//	  pr($this->request->data);die;
            $remove_value = $this->request->data['remove'];
              //unset($this->request->data['remove']);
			   $category = $this->Categories->patchEntity($category, $this->request->data);
               //pr($category);die;
					if ($this->Categories->save($category)) {
						  //update where
                    	  $path =  WWW_ROOT."files".DS."Categories".DS."image".DS.$id.DS;
						  if(array_key_exists('image',$this->request->data)){
							   $image_name = $this->request->data['image']['name'];
						  }else{
                             $image_name = "";
                          }
                         //pr($this->request->data ); die;
                           if(array_key_exists('remove',$this->request->data)){
                                 $remove = $this->request->data['remove'];
                                $image_delete = $this->request->data['image']['name'];
                                if($remove == '1'){
                                    $fullpath  = $path  ; 
                                    if($path){
                                        $scanned_directory = array_diff(scandir($fullpath), array('..', '.'));
                                      
                                         if(!empty($scanned_directory)){
                                            foreach($scanned_directory as  $sngscanned_directory){
                                               $fullimagepath = $fullpath.$sngscanned_directory;
                                              unlink($fullimagepath);
                                               $sngscanned_directory."  image Delete Succesfully !";
                                            } 
                                        }else{
                                           echo "No Image";
                                       }
                                }
							  }
						   } 
						  if(!empty($image_name)){
							   $query1 = "UPDATE categories SET image_dir = {$id} where id = {$id}";
                                $query2 = "UPDATE categories SET image = {$image_name} where id = {$id}";
							   $conn1 = ConnectionManager::get('default');
							   $stmt = $conn1->execute($query1);
						  }
						 
						  $currentCategory = $this->request['data']['id'].":".$this->request['data']['category'];
						  if($currentCategory != $prevCategory){
							  $query = "UPDATE `categories` SET `id_name_path` = REPLACE(id_name_path, '$prevCategory','$currentCategory' ) WHERE `id_name_path` LIKE '%$prevCategory%'";
							  $conn = ConnectionManager::get('default');
							   $stmt = $conn->execute($query);
							   
						  }
						  if(!empty($sites)){
							  foreach($sites as $site_id => $site_value){
								   $this->update_boloram_catagory($this->request->data,$site_value);
							  }
						  }
						 $this->Flash->success(__('The category has been saved.'));
						 return $this->redirect(array('action' => 'index'));
					}else{
						 $errors = $category->errors();
							$err = array();
						 foreach($errors as $error){
							  foreach($error as $key){
								   $err[] = $key;
							  }
							  //$err[] = $key." already in use";
						 }
						$this->Flash->error(implode("</br>",$err),['escape' => false]);
						$this->Flash->error(__('The category could not be saved. Please, try again.'));
					}
		  }else{
			   $options = array('conditions' => array('Categories.id' => $id));
			   $res_query = $this->Categories->find('all', $options);
			   $res_query = $res_query->hydrate(false);
			   if(!empty($res_query)){
				   $res = $res_query->first();
			   }else{
				   $res = array();
			   }
			   $this->request->data = $res;
		  }
			 
			   $parentCategories_query = $this->Categories->find('list',array(
																		'keyField' => 'id',
																		'valueField' => 'category',
																		  'conditions' => array('Categories.status' => 1)));
			   $parentCategories_query = $parentCategories_query->hydrate(false);
			   if(!empty($parentCategories_query)){
				  $parentCategories = $parentCategories_query->toArray();
			   }else{
				  $parentCategories = array();
			   }
			   //print_r($parentCategories);
			   //$this->Category->set(array('image' => $image_url));
			   $this->set(compact('parentCategories'));
	 }

	 public function delete($id = null) {
		//$this->Flash->error(__("You are unauthouriesed to delete Category"));
		//return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
		  $path = dirname(__FILE__);
		$isboloRam = strpos($path, ADMIN_DOMAIN);
		if($isboloRam == false){
            $this->Flash->error(__("This function works only on ". ADMIN_DOMAIN));
			return $this->redirect(array('controller' => 'home','action' => "dashboard"));die;
		}
		 $this->request->allowMethod(['post', 'delete']);
		 $category = $this->Categories->get($id);
		  $res = $this->getChildren($id);
		  if(empty($res)){
			   $res = array($id);
		  }else{
			   $res[] =$id;
		  }
		  $product_res = $this->Products->find('all',['conditions' => ['category_id IN' => $res]])->toArray();
		  
		  if(count($product_res) == 0){
			   foreach($res as $key => $val){
					$new_category = $this->Categories->get($val);
					if ($this->Categories->delete($new_category)) {
							  $this->delete_to_mb($id);
					}
			   }
					$this->Flash->success(__('The category has been deleted.'));	
		  }else{
			   $this->Flash->success(__('This category has products.'));	
		  }
		 return $this->redirect(['action' => 'index']);
	}
	
	function getChildren($parent_id) {
		  $tree = Array();
		  if (!empty($parent_id)) {
			  $tree = $this->getOneLevel($parent_id);
			  foreach ($tree as $key => $val) {
				  $ids = $this->getChildren($val);
					$tree = array_merge($tree, $ids);
			  }
		  }
		  return $tree;
	 }
	 
	 function getOneLevel($catId){
		  $cat_id = array();
		  $res = $this->Categories->find('all',array('conditions' => array('parent_id' => $catId)))->toArray();
		  if(!empty($res)){
			   foreach($res as $key => $value){
					$cat_id[] = $value->id;	
			   }
		  }
		  return $cat_id;
	  }
	
	private function delete_to_mb($id){
		  $sites = Configure::read('sites');
		  foreach($sites  as $key => $value){
			 $connection = ConnectionManager::get($value);
			 $stmt = $connection->execute("SELECT * FROM `categories` WHERE `id`=$id");
			 $Catdata = $stmt ->fetchAll('assoc');
			 //pr($Catdata);die;
			 if(count($Catdata) > 0){
				 $connection->execute("DELETE FROM `categories` WHERE `id`=$id");
			 }  
		  }
	}
	
	 public function create_boloram_catagory($boloram_catagory=array(),$site_value){
		
		//  if(!array_key_exists("Category",$boloram_catagory)){
		//	  return;
		//  }
		  if(array_key_exists('image',$boloram_catagory)){
			   $image_name = $boloram_catagory['image']['name'];
			   unset($boloram_catagory['image']);
		  }else{
			   $image_name = "";
		  }
		  $boloram_catagory['image'] = $image_name;
		  if(!array_key_exists('category',$boloram_catagory)){
			  return;
		  }
		  if(!array_key_exists('parent_id',$boloram_catagory)){
			  return;
		  }
		  $connection = ConnectionManager::get($site_value);
		  $query = $connection->insert('categories',$boloram_catagory);
		  $id = $query->lastInsertId('categories');
		  $stmt = $connection->execute("UPDATE `categories` SET `image_dir`=$id WHERE `id`=$id"); 
	 }
	 
	 public function update_boloram_catagory($update_boloram_catagory=array(),$site_value){
		  //if(!array_key_exists("Category",$update_boloram_catagory)){
		  //	return;
		  //}
         // pr($update_boloram_catagory);die;
          if(array_key_exists('submit',$update_boloram_catagory)){
			  	   unset($update_boloram_catagory['submit']);
		  }
          
		  $prevCategory = $update_boloram_catagory['prev_category'];
		  if(array_key_exists('image',$update_boloram_catagory)){
			   $image_name = $update_boloram_catagory['image']['name'];
			   unset($update_boloram_catagory['image']);
		  }else{
			   $image_name = "";
		  }
           if(array_key_exists('remove',$update_boloram_catagory)){
			  	   unset($update_boloram_catagory['remove']);
		  }
		  $update_boloram_catagory['image'] = $image_name;
		  if(array_key_exists('prev_category',$update_boloram_catagory)){
			  unset($update_boloram_catagory['prev_category']);
		  }
		  if(!array_key_exists('id',$update_boloram_catagory)){
			  return;
		  }
		  $id = $update_boloram_catagory['id'];
		  if(!array_key_exists('category',$update_boloram_catagory)){
			  return;
		  }
		//  if(!array_key_exists('prev_category',$update_boloram_catagory)){
		//	  return;
		//  }
		
		  $connection = ConnectionManager::get($site_value);
		  
	 	  $stmt = $connection->execute("SELECT * FROM  `categories` WHERE `id`=$id"); 
		  $currentTimeInfo = $stmt ->fetchAll('assoc');
		  if(count($currentTimeInfo)<=0){
			  return;
		  }
		  
		  if($connection->update('categories',$update_boloram_catagory,['id'=>$id])){
			  $currentCategory = $update_boloram_catagory['id'].":".$update_boloram_catagory['category'];
			  if($currentCategory != $prevCategory){
				 $stmt1 = $connection->execute("UPDATE `categories` SET `id_name_path` = REPLACE(id_name_path, '$prevCategory','$currentCategory' ) WHERE `id_name_path` LIKE '%$prevCategory%'"); 
				  
			  }
		  }
	 }
	
}