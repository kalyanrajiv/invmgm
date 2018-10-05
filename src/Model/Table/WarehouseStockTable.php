<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
 
class WarehouseStockTable extends Table
{

   
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('warehouse_stock');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('WarehouseVendors', [
            'foreignKey' => 'warehouse_vendor_id',
            'joinType' => 'INNER'
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
        //$validator
        //    ->integer('id')
        //    ->allowEmpty('id', 'create');

        //$validator
        //    ->requirePresence('reference_number', 'create')
        //    ->notEmpty('reference_number');

        $validator
            ->integer('quantity')
            ->requirePresence('quantity', 'create')
            ->notEmpty('quantity');

        $validator
            ->numeric('price')
            ->requirePresence('price', 'create')
            ->notEmpty('price');

        //$validator
        //    ->integer('in_out')
        //    ->requirePresence('in_out', 'create')
        //    ->notEmpty('in_out');
        //
        //$validator
        //    ->requirePresence('remarks', 'create')
        //    ->notEmpty('remarks');

        $validator
            ->integer('status')
            ->requirePresence('status', 'create')
            ->notEmpty('status');

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
        $rules->add($rules->existsIn(['product_id'], 'Products'));
      //  $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['warehouse_vendor_id'], 'WarehouseVendors'));

        return $rules;
    }
}
