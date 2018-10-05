<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ImportOrderDetails Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Products
 * @property \Cake\ORM\Association\BelongsTo $ImportOrders
 * @property \Cake\ORM\Association\BelongsTo $ImportOrderReferences
 *
 * @method \App\Model\Entity\ImportOrderDetail get($primaryKey, $options = [])
 * @method \App\Model\Entity\ImportOrderDetail newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\ImportOrderDetail[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ImportOrderDetail|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ImportOrderDetail patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ImportOrderDetail[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\ImportOrderDetail findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ImportOrderDetailsTable extends Table
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

        $this->table('import_order_details');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
            'joinType' => 'INNER'
        ]);
        //$this->belongsTo('ImportOrders', [
        //    'foreignKey' => 'import_order_id',
        //    'joinType' => 'INNER'
        //]);
        $this->belongsTo('ImportOrderReferences', [
            'foreignKey' => 'import_order_reference_id',
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
        //$rules->add($rules->existsIn(['import_order_id'], 'ImportOrders'));
        $rules->add($rules->existsIn(['import_order_reference_id'], 'ImportOrderReferences'));

        return $rules;
    }
}
