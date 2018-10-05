<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

 
class CategoriesTable extends Table
{
 
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('categories');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
        
         $this->addBehavior('Josegonzalez/Upload.Upload', [
            'image' => [
                'fields' => [
                    // if these fields or their defaults exist
                    // the values will be set.
                    // 'path' =>'webroot{DS}files{DS}{model}{DS}{field}{DS}{primaryKey}{DS}',
                
                    'dir' => 'image_dir', // defaults to `dir`
                    'size' => 'photo_size', // defaults to `size`
                   'type' => 'photo_type', // defaults to `type`
                ],
                 'thumbnailMethod'  => 'php', //or php
                                                'thumbnailSizes' => [ 
                                                            'xvga' => '1024x768',
                                                            'vga' => '640x480',
                                                            'thumb' => '80x80',
                                                            'mini' => '30x30',
                       ],
												'keepFilesOnDelete' => true,
            ],
        ]);
        
        
        $this->belongsTo('ParentCategories', [
            'className' => 'Categories',
            'foreignKey' => 'parent_id'
        ]);
        $this->hasMany('ChildCategories', [
            'className' => 'Categories',
            'foreignKey' => 'parent_id'
        ]);
       
        $this->hasMany('Products', [
            'foreignKey' => 'category_id'
        ]);
        
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');
        
        // $validator->add('category',[
        //'notEmptyCheck'=>[
        //'rule'=>'checkDuplicate',
        //'provider'=>'table',
        //'message'=>'category already existing!'
        // ]
        //]);
          $validator->add('category',[
        'notEmptyCheck'=>[
        'rule'=>'checkDuplicate',
        'provider'=>'table',
        'message'=>'category already existing!'
         ]
        ]);
       $validator
         ->allowEmpty('image');

     

        return $validator;
    }
    //public function notEmptyCheck($value,$context){
    //    pr($context);die;
    //    if(empty($context['data']['title'])) {
    //        return false;
    //    } else {
    //        return true;
    //    }
    //}
    public function checkDuplicate($value,$context) {
     // pr($context); 
	    if(array_key_exists('id',$context['data'])){
           // echo $context['newRecord'];die;
		//if record already existing
		$category_query = $this->find('all', array(
						       'fields' => array('parent_id','id'),
							'conditions' => array('id' => $context['data']['id']),
							'recursive' => -1
							)
					);
        //$category_query
         $category_query = $category_query->hydrate(false);
         if(!empty($category_query)){
            $category = $category_query->toArray();
         }else{
            $category = array();
         }
       // pr($category); 
		$categoryCount_query = $this->find('all', array(
							    'conditions' => array(
									'category' => $context['data']['category'],
									'parent_id' => $category['0']['parent_id'],
									'id <>' => $context['data']['id']
									  ),
							    'recursive' => -1
							)
					    );
         if(!empty($categoryCount_query)){
               $categoryCount = $categoryCount_query->count();
            }else{
                $categoryCount = '';
            }
        
	    }else{
		//if record not exisitng
       //  pr($check['category']);die;
             $categoryCount_query =  $this->find('all',array(
                                                                        'conditions' => array(
                                                                                    'category' => $context['data']['category'],
                                                                                    'parent_id' => $context['data']['parent_id']
                                                                                ) 
                                                                         )
                                                            );  
            //$count_unlock_Prices = $count_unlock_Prices_query->count();
            if(!empty($categoryCount_query)){
               $categoryCount = $categoryCount_query->count();
            }else{
                $categoryCount = '';
            }
        }
       // echo $categoryCount;die;
	    return $categoryCount < 1;
    
	}
    
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['parent_id'], 'ParentCategories'));

        return $rules;
    }
    
}
