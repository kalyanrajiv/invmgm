<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TempProductOrders Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $ProductReceipts
 * @property \Cake\ORM\Association\HasMany $TempProductDetails
 *
 * @method \App\Model\Entity\TempProductOrder get($primaryKey, $options = [])
 * @method \App\Model\Entity\TempProductOrder newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\TempProductOrder[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TempProductOrder|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TempProductOrder patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\TempProductOrder[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\TempProductOrder findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TempProductOrdersTable extends Table
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

        $this->table('temp_product_orders');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('ProductReceipts', [
            'foreignKey' => 'product_receipt_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('TempProductDetails', [
            'foreignKey' => 'temp_product_order_id'
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
            ->numeric('total_amount')
            ->requirePresence('total_amount', 'create')
            ->notEmpty('total_amount');

        $validator
            ->allowEmpty('fname');

        $validator
            ->allowEmpty('lname');

        $validator
            ->allowEmpty('mobile');

        $validator
            ->email('email')
            ->allowEmpty('email');

        $validator
            ->allowEmpty('zip');

        $validator
            ->allowEmpty('address_1');

        $validator
            ->integer('address_2')
            ->allowEmpty('address_2');

        $validator
            ->allowEmpty('city');

        $validator
            ->allowEmpty('state');

        $validator
            ->requirePresence('remarks', 'create')
            ->notEmpty('remarks');

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
        $rules->add($rules->isUnique(['email']));
        $rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['product_receipt_id'], 'ProductReceipts'));

        return $rules;
    }
}
