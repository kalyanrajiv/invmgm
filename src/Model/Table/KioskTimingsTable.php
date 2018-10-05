<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * KioskTimings Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 *
 * @method \App\Model\Entity\KioskTiming get($primaryKey, $options = [])
 * @method \App\Model\Entity\KioskTiming newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\KioskTiming[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\KioskTiming|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\KioskTiming patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\KioskTiming[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\KioskTiming findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class KioskTimingsTable extends Table
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

        $this->table('kiosk_timings');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

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
            ->time('mon_time_in')
            ->requirePresence('mon_time_in', 'create')
            ->notEmpty('mon_time_in');

        $validator
            ->time('mon_time_out')
            ->requirePresence('mon_time_out', 'create')
            ->notEmpty('mon_time_out');

        $validator
            ->time('tues_time_in')
            ->requirePresence('tues_time_in', 'create')
            ->notEmpty('tues_time_in');

        $validator
            ->time('tues_time_out')
            ->requirePresence('tues_time_out', 'create')
            ->notEmpty('tues_time_out');

        $validator
            ->time('wed_time_in')
            ->requirePresence('wed_time_in', 'create')
            ->notEmpty('wed_time_in');

        $validator
            ->time('wed_time_out')
            ->requirePresence('wed_time_out', 'create')
            ->notEmpty('wed_time_out');

        $validator
            ->time('thrus_time_in')
            ->requirePresence('thrus_time_in', 'create')
            ->notEmpty('thrus_time_in');

        $validator
            ->time('thrus_time_out')
            ->requirePresence('thrus_time_out', 'create')
            ->notEmpty('thrus_time_out');

        $validator
            ->time('fri_time_in')
            ->requirePresence('fri_time_in', 'create')
            ->notEmpty('fri_time_in');

        $validator
            ->time('fri_time_out')
            ->requirePresence('fri_time_out', 'create')
            ->notEmpty('fri_time_out');

        $validator
            ->time('sat_time_in')
            ->requirePresence('sat_time_in', 'create')
            ->notEmpty('sat_time_in');

        $validator
            ->time('sat_time_out')
            ->requirePresence('sat_time_out', 'create')
            ->notEmpty('sat_time_out');

        $validator
            ->time('sun_time_in')
            ->requirePresence('sun_time_in', 'create')
            ->notEmpty('sun_time_in');

        $validator
            ->time('sun_time_out')
            ->requirePresence('sun_time_out', 'create')
            ->notEmpty('sun_time_out');

        $validator
            ->integer('status')
            ->requirePresence('status', 'create')
            ->notEmpty('status');

        $validator
            ->dateTime('modifed')
            ->requirePresence('modifed', 'create')
            ->notEmpty('modifed');

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

        return $rules;
    }
}
