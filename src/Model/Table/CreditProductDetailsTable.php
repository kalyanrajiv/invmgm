<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CreditProductDetails Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $Products
 * @property \Cake\ORM\Association\BelongsTo $Customers
 * @property \Cake\ORM\Association\BelongsTo $CreditReceipts
 *
 * @method \App\Model\Entity\CreditProductDetail get($primaryKey, $options = [])
 * @method \App\Model\Entity\CreditProductDetail newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\CreditProductDetail[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CreditProductDetail|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CreditProductDetail patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CreditProductDetail[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\CreditProductDetail findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CreditProductDetailsTable extends Table
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

        $this->table('credit_product_details');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('CreditReceipts', [
            'foreignKey' => 'credit_receipt_id',
            'joinType' => 'INNER'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    //public function validationDefault(Validator $validator)
    //{
    //    $validator
    //        ->integer('id')
    //        ->allowEmpty('id', 'create');
    //
    //    $validator
    //        ->integer('quantity')
    //        ->requirePresence('quantity', 'create')
    //        ->notEmpty('quantity');
    //
    //    $validator
    //        ->numeric('sale_price')
    //        ->requirePresence('sale_price', 'create')
    //        ->notEmpty('sale_price');
    //
    //    $validator
    //        ->numeric('credit_price')
    //        ->requirePresence('credit_price', 'create')
    //        ->notEmpty('credit_price');
    //
    //    $validator
    //        ->integer('discount')
    //        ->requirePresence('discount', 'create')
    //        ->notEmpty('discount');
    //
    //    $validator
    //        ->integer('credit_by')
    //        ->requirePresence('credit_by', 'create')
    //        ->notEmpty('credit_by');
    //
    //    $validator
    //        ->requirePresence('type', 'create')
    //        ->notEmpty('type');
    //
    //    $validator
    //        ->integer('status')
    //        ->requirePresence('status', 'create')
    //        ->notEmpty('status');
    //
    //    $validator
    //        ->integer('credit_status')
    //        ->requirePresence('credit_status', 'create')
    //        ->notEmpty('credit_status');
    //
    //    $validator
    //        ->requirePresence('remarks', 'create')
    //        ->notEmpty('remarks');
    //
    //    return $validator;
    //}
    //
    ///**
    // * Returns a rules checker object that will be used for validating
    // * application integrity.
    // *
    // * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
    // * @return \Cake\ORM\RulesChecker
    // */
    //public function buildRules(RulesChecker $rules)
    //{
    //    $rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));
    //    $rules->add($rules->existsIn(['product_id'], 'Products'));
    //    $rules->add($rules->existsIn(['customer_id'], 'Customers'));
    //    $rules->add($rules->existsIn(['credit_receipt_id'], 'CreditReceipts'));
    //
    //    return $rules;
    //}
}
