<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DeviceActivationRequests Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 *
 * @method \App\Model\Entity\DeviceActivationRequest get($primaryKey, $options = [])
 * @method \App\Model\Entity\DeviceActivationRequest newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\DeviceActivationRequest[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DeviceActivationRequest|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DeviceActivationRequest patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DeviceActivationRequest[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\DeviceActivationRequest findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DeviceActivationRequestsTable extends Table
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

        $this->table('device_activation_requests');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id',
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
            ->requirePresence('kiosk_name', 'create')
            ->notEmpty('kiosk_name');

        $validator
            ->requirePresence('email_address', 'create')
            ->notEmpty('email_address');

        $validator
            ->requirePresence('description', 'create')
            ->notEmpty('description');

        $validator
            ->requirePresence('otp', 'create')
            ->notEmpty('otp');

        $validator
            ->requirePresence('device_cookie', 'create')
            ->notEmpty('device_cookie');

        $validator
            ->integer('status')
            ->requirePresence('status', 'create')
            ->notEmpty('status');

        $validator
            ->integer('type')
            ->requirePresence('type', 'create')
            ->notEmpty('type');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));

        return $rules;
    }
}
