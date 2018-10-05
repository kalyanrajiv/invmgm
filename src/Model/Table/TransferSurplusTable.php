<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TransferSurplus Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Customers
 * @property \Cake\ORM\Association\BelongsTo $Products
 * @property \Cake\ORM\Association\BelongsTo $Categories
 * @property \Cake\ORM\Association\BelongsTo $ProductReceipts
 *
 * @method \App\Model\Entity\TransferSurplus get($primaryKey, $options = [])
 * @method \App\Model\Entity\TransferSurplus newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\TransferSurplus[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TransferSurplus|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TransferSurplus patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\TransferSurplus[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\TransferSurplus findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TransferSurplusTable extends Table
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

        $this->table('transfer_surplus');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id'
        ]);
        $this->belongsTo('Products', [
            'foreignKey' => 'product_id'
        ]);
        //$this->belongsTo('Categories', [
        //    'foreignKey' => 'category_id'
        //]);
        $this->belongsTo('ProductReceipts', [
            'foreignKey' => 'product_receipt_id'
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

        $validator
            ->allowEmpty('invoice_reference');

        $validator
            ->allowEmpty('category');

        $validator
            ->integer('quantity')
            ->allowEmpty('quantity');

        $validator
            ->numeric('cost_price')
            ->requirePresence('cost_price', 'create')
            ->notEmpty('cost_price');

        $validator
            ->numeric('sale_price')
            ->requirePresence('sale_price', 'create')
            ->notEmpty('sale_price');

        $validator
            ->requirePresence('bulk_discount', 'create')
            ->notEmpty('bulk_discount');

        $validator
            ->integer('vat_applied')
            ->allowEmpty('vat_applied');

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
        $rules->add($rules->existsIn(['customer_id'], 'Customers'));
        $rules->add($rules->existsIn(['product_id'], 'Products'));
       // $rules->add($rules->existsIn(['category_id'], 'Categories'));
        $rules->add($rules->existsIn(['product_receipt_id'], 'ProductReceipts'));

        return $rules;
    }
}
