<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DeviceCookieDetails Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Devices
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 *
 * @method \App\Model\Entity\DeviceCookieDetail get($primaryKey, $options = [])
 * @method \App\Model\Entity\DeviceCookieDetail newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\DeviceCookieDetail[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DeviceCookieDetail|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DeviceCookieDetail patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DeviceCookieDetail[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\DeviceCookieDetail findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DeviceCookieDetailsTable extends Table
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

        $this->table('device_cookie_details');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Devices', [
            'foreignKey' => 'device_id',
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
            ->requirePresence('device_cookie', 'create')
            ->notEmpty('device_cookie');

        $validator
            ->requirePresence('ip_address', 'create')
            ->notEmpty('ip_address');

        $validator
            ->requirePresence('user_agent', 'create')
            ->notEmpty('user_agent');

        $validator
            ->integer('logged_in')
            ->requirePresence('logged_in', 'create')
            ->notEmpty('logged_in');

        $validator
            ->requirePresence('description', 'create')
            ->notEmpty('description');

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
        $rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));

        return $rules;
    }
}
