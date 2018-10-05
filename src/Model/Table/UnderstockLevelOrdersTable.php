<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * UnderstockLevelOrders Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $Products
 * @property \Cake\ORM\Association\BelongsTo $Orders
 *
 * @method \App\Model\Entity\UnderstockLevelOrder get($primaryKey, $options = [])
 * @method \App\Model\Entity\UnderstockLevelOrder newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\UnderstockLevelOrder[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UnderstockLevelOrder|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UnderstockLevelOrder patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\UnderstockLevelOrder[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\UnderstockLevelOrder findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UnderstockLevelOrdersTable extends Table
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

        $this->table('understock_level_orders');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
            'joinType' => 'INNER'
        ]);
        //$this->belongsTo('Orders', [
        //    'foreignKey' => 'order_id'
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
        $rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['product_id'], 'Products'));
        //$rules->add($rules->existsIn(['order_id'], 'Orders'));

        return $rules;
    }
}
