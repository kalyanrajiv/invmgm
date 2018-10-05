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
        //$this->hasMany('CsvProducts', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('Kiosk10000Products', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('Kiosk10Products', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('Kiosk11Products', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('Kiosk12Products', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('Kiosk13Products', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('Kiosk14Products', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('Kiosk15Products', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('Kiosk16Products', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('Kiosk17Products', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('Kiosk18Products', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('Kiosk19Products', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('Kiosk1Products', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('Kiosk20Products', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('Kiosk21Products', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('Kiosk22Products', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('Kiosk2Products', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('Kiosk3Products', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('Kiosk4Products', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('Kiosk5Products', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('Kiosk7Products', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('Kiosk8Products', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('KioskProducts', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('KioskCancelledOrderProducts', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('OnDemandProducts', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('ProductSaleStats', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('ProductSellStats', [
        //    'foreignKey' => 'category_id'
        //]);
        $this->hasMany('Products', [
            'foreignKey' => 'category_id'
        ]);
        //$this->hasMany('ReservedProducts', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('TransferSurplus', [
        //    'foreignKey' => 'category_id'
        //]);
        //$this->hasMany('TransferUnderstock', [
        //    'foreignKey' => 'category_id'
        //]);
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

        $validator
            ->requirePresence('category', 'create')
            ->notEmpty('category');

        //$validator
        //    ->requirePresence('id_name_path', 'create')
        //    ->notEmpty('id_name_path');
        //
        //$validator
        //    ->requirePresence('description', 'create')
        //    ->notEmpty('description');
        //
        //$validator
        //    ->allowEmpty('image');
        //
        //$validator
        //    ->requirePresence('image_dir', 'create')
        //    ->notEmpty('image_dir');
        //
        //$validator
        //    ->boolean('top')
        //    ->requirePresence('top', 'create')
        //    ->notEmpty('top');
        //
        //$validator
        //    ->integer('column')
        //    ->requirePresence('column', 'create')
        //    ->notEmpty('column');
        //
        //$validator
        //    ->integer('sort_order')
        //    ->allowEmpty('sort_order');
        //
        //$validator
        //    ->boolean('status')
        //    ->requirePresence('status', 'create')
        //    ->notEmpty('status');

        return $validator;
    }

    
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['parent_id'], 'ParentCategories'));

        return $rules;
    }
}
