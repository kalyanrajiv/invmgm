<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DeviceLoginLogs Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Devices
 *
 * @method \App\Model\Entity\DeviceLoginLog get($primaryKey, $options = [])
 * @method \App\Model\Entity\DeviceLoginLog newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\DeviceLoginLog[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DeviceLoginLog|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DeviceLoginLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DeviceLoginLog[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\DeviceLoginLog findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DeviceLoginLogsTable extends Table
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

        $this->table('device_login_logs');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Devices', [
            'foreignKey' => 'device_id',
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
            ->requirePresence('device_cookie', 'create')
            ->notEmpty('device_cookie');

        $validator
            ->requirePresence('ip_address', 'create')
            ->notEmpty('ip_address');

        $validator
            ->requirePresence('user_agent', 'create')
            ->notEmpty('user_agent');

        $validator
            ->time('login_time')
            ->requirePresence('login_time', 'create')
            ->notEmpty('login_time');

        $validator
            ->time('log_out_time')
            ->requirePresence('log_out_time', 'create')
            ->notEmpty('log_out_time');

        $validator
            ->requirePresence('location', 'create')
            ->notEmpty('location');

        $validator
            ->requirePresence('longitude', 'create')
            ->notEmpty('longitude');

        $validator
            ->requirePresence('lattitude', 'create')
            ->notEmpty('lattitude');

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
        $rules->add($rules->existsIn(['device_id'], 'Devices'));

        return $rules;
    }
}
