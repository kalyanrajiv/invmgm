<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * StockTransfer Model
 *
 * @property \Cake\ORM\Association\BelongsTo $KioskOrders
 * @property \Cake\ORM\Association\BelongsTo $KioskPlacedOrders
 * @property \Cake\ORM\Association\BelongsTo $Products
 *
 * @method \App\Model\Entity\StockTransfer get($primaryKey, $options = [])
 * @method \App\Model\Entity\StockTransfer newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\StockTransfer[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\StockTransfer|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\StockTransfer patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\StockTransfer[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\StockTransfer findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class StockTransferTable extends Table
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

        $this->table('stock_transfer');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('KioskOrders', [
            'foreignKey' => 'kiosk_order_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('KioskPlacedOrders', [
            'foreignKey' => 'kiosk_placed_order_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
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
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->integer('quantity')
            ->requirePresence('quantity', 'create')
            ->notEmpty('quantity');

        $validator
            ->numeric('sale_price')
            ->requirePresence('sale_price', 'create')
            ->notEmpty('sale_price');

        $validator
            ->integer('cost_price')
            ->requirePresence('cost_price', 'create')
            ->notEmpty('cost_price');

        $validator
            ->allowEmpty('remarks');

        $validator
            ->integer('status')
            ->requirePresence('status', 'create')
            ->notEmpty('status');

        $validator
            ->integer('product_processed')
            ->requirePresence('product_processed', 'create')
            ->notEmpty('product_processed');

        $validator
            ->integer('flag')
            ->requirePresence('flag', 'create')
            ->notEmpty('flag');

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
        $rules->add($rules->existsIn(['kiosk_order_id'], 'KioskOrders'));
        $rules->add($rules->existsIn(['kiosk_placed_order_id'], 'KioskPlacedOrders'));
        $rules->add($rules->existsIn(['product_id'], 'Products'));

        return $rules;
    }
}
