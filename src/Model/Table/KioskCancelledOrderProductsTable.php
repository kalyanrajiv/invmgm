<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * KioskCancelledOrderProducts Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $KioskPlacedOrders
 * @property \Cake\ORM\Association\BelongsTo $Products
 * @property \Cake\ORM\Association\BelongsTo $Categories
 *
 * @method \App\Model\Entity\KioskCancelledOrderProduct get($primaryKey, $options = [])
 * @method \App\Model\Entity\KioskCancelledOrderProduct newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\KioskCancelledOrderProduct[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\KioskCancelledOrderProduct|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\KioskCancelledOrderProduct patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\KioskCancelledOrderProduct[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\KioskCancelledOrderProduct findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class KioskCancelledOrderProductsTable extends Table
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

        $this->table('kiosk_cancelled_order_products');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id',
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
        //$this->belongsTo('Categories', [
        //    'foreignKey' => 'category_id',
        //    'joinType' => 'INNER'
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
            ->integer('cancelled_by')
            ->requirePresence('cancelled_by', 'create')
            ->notEmpty('cancelled_by');

        $validator
            ->integer('quantity')
            ->requirePresence('quantity', 'create')
            ->notEmpty('quantity');

        $validator
            ->integer('difference')
            ->allowEmpty('difference');

        $validator
            ->allowEmpty('remarks');

        $validator
            ->integer('status')
            ->requirePresence('status', 'create')
            ->notEmpty('status');

        $validator
            ->integer('is_on_demand')
            ->requirePresence('is_on_demand', 'create')
            ->notEmpty('is_on_demand');

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
        $rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));
        $rules->add($rules->existsIn(['kiosk_placed_order_id'], 'KioskPlacedOrders'));
        $rules->add($rules->existsIn(['product_id'], 'Products'));
       // $rules->add($rules->existsIn(['category_id'], 'Categories'));

        return $rules;
    }
}
