<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CsvProducts Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Categories
 * @property \Cake\ORM\Association\BelongsTo $Brands
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $Images
 *
 * @method \App\Model\Entity\CsvProduct get($primaryKey, $options = [])
 * @method \App\Model\Entity\CsvProduct newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\CsvProduct[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CsvProduct|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CsvProduct patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CsvProduct[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\CsvProduct findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CsvProductsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('csv_products');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        //$this->belongsTo('Categories', [
        //    'foreignKey' => 'category_id',
        //    'joinType' => 'INNER'
        //]);
        //$this->belongsTo('Brands', [
        //    'foreignKey' => 'brand_id',
        //    'joinType' => 'INNER'
        //]);
        //$this->belongsTo('Users', [
        //    'foreignKey' => 'user_id'
        //]);
        //$this->belongsTo('Images', [
        //    'foreignKey' => 'image_id'
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
        //$validator
        //    ->requirePresence('prefix', 'create')
        //    ->notEmpty('prefix');
        //
        //$validator
        //    ->integer('id')
        //    ->allowEmpty('id', 'create');
        //
        //$validator
        //    ->requirePresence('product', 'create')
        //    ->notEmpty('product');
        //
        //$validator
        //    ->integer('quantity')
        //    ->requirePresence('quantity', 'create')
        //    ->notEmpty('quantity');
        //
        //$validator
        //    ->requirePresence('description', 'create')
        //    ->notEmpty('description');
        //
        //$validator
        //    ->allowEmpty('location');
        //
        //$validator
        //    ->numeric('cost_price')
        //    ->requirePresence('cost_price', 'create')
        //    ->notEmpty('cost_price');
        //
        //$validator
        //    ->dateTime('lu_cp')
        //    ->allowEmpty('lu_cp');
        //
        //$validator
        //    ->numeric('vat_excluded_wholesale_price')
        //    ->allowEmpty('vat_excluded_wholesale_price');
        //
        //$validator
        //    ->numeric('vat_exclude_retail_price')
        //    ->allowEmpty('vat_exclude_retail_price');
        //
        //$validator
        //    ->numeric('retail_cost_price')
        //    ->allowEmpty('retail_cost_price');
        //
        //$validator
        //    ->dateTime('lu_rcp')
        //    ->allowEmpty('lu_rcp');
        //
        //$validator
        //    ->numeric('selling_price')
        //    ->requirePresence('selling_price', 'create')
        //    ->notEmpty('selling_price');
        //
        //$validator
        //    ->dateTime('lu_sp')
        //    ->allowEmpty('lu_sp');
        //
        //$validator
        //    ->numeric('retail_selling_price')
        //    ->allowEmpty('retail_selling_price');
        //
        //$validator
        //    ->dateTime('lu_rsp')
        //    ->allowEmpty('lu_rsp');
        //
        //$validator
        //    ->requirePresence('model', 'create')
        //    ->notEmpty('model');
        //
        //$validator
        //    ->date('manufacturing_date')
        //    ->requirePresence('manufacturing_date', 'create')
        //    ->notEmpty('manufacturing_date');
        //
        //$validator
        //    ->integer('sku')
        //    ->requirePresence('sku', 'create')
        //    ->notEmpty('sku');
        //
        //$validator
        //    ->requirePresence('country_make', 'create')
        //    ->notEmpty('country_make');
        //
        //$validator
        //    ->requirePresence('product_code', 'create')
        //    ->notEmpty('product_code');
        //
        //$validator
        //    ->numeric('weight')
        //    ->allowEmpty('weight');
        //
        //$validator
        //    ->requirePresence('color', 'create')
        //    ->notEmpty('color');
        //
        //$validator
        //    ->integer('featured')
        //    ->allowEmpty('featured');
        //
        //$validator
        //    ->numeric('discount')
        //    ->allowEmpty('discount');
        //
        //$validator
        //    ->numeric('retail_discount')
        //    ->allowEmpty('retail_discount');
        //
        //$validator
        //    ->integer('discount_status')
        //    ->requirePresence('discount_status', 'create')
        //    ->notEmpty('discount_status');
        //
        //$validator
        //    ->integer('rt_discount_status')
        //    ->allowEmpty('rt_discount_status');
        //
        //$validator
        //    ->numeric('max_discount')
        //    ->allowEmpty('max_discount');
        //
        //$validator
        //    ->numeric('min_discount')
        //    ->allowEmpty('min_discount');
        //
        //$validator
        //    ->integer('special_offer')
        //    ->requirePresence('special_offer', 'create')
        //    ->notEmpty('special_offer');
        //
        //$validator
        //    ->integer('festival_offer')
        //    ->requirePresence('festival_offer', 'create')
        //    ->notEmpty('festival_offer');
        //
        //$validator
        //    ->integer('retail_special_offer')
        //    ->requirePresence('retail_special_offer', 'create')
        //    ->notEmpty('retail_special_offer');
        //
        //$validator
        //    ->requirePresence('image', 'create')
        //    ->notEmpty('image');
        //
        //$validator
        //    ->requirePresence('image_dir', 'create')
        //    ->notEmpty('image_dir');
        //
        //$validator
        //    ->requirePresence('manufacturer', 'create')
        //    ->notEmpty('manufacturer');
        //
        //$validator
        //    ->integer('stock_level')
        //    ->requirePresence('stock_level', 'create')
        //    ->notEmpty('stock_level');
        //
        //$validator
        //    ->integer('dead_stock_level')
        //    ->requirePresence('dead_stock_level', 'create')
        //    ->notEmpty('dead_stock_level');
        //
        //$validator
        //    ->integer('status')
        //    ->requirePresence('status', 'create')
        //    ->notEmpty('status');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        //$rules->add($rules->existsIn(['category_id'], 'Categories'));
        //$rules->add($rules->existsIn(['brand_id'], 'Brands'));
        //$rules->add($rules->existsIn(['user_id'], 'Users'));
        //$rules->add($rules->existsIn(['image_id'], 'Images'));

        return $rules;
    }
}
