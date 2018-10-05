<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MobileTransferLogs Model
 *
 * @property \Cake\ORM\Association\BelongsTo $MobilePurchases
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $Networks
 * @property \Cake\ORM\Association\BelongsTo $MobileResales
 *
 * @method \App\Model\Entity\MobileTransferLog get($primaryKey, $options = [])
 * @method \App\Model\Entity\MobileTransferLog newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MobileTransferLog[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MobileTransferLog|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MobileTransferLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MobileTransferLog[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MobileTransferLog findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MobileTransferLogsTable extends Table
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

        $this->table('mobile_transfer_logs');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        //$this->belongsTo('MobilePurchases', [
        //    'foreignKey' => 'mobile_purchase_id',
        //    'joinType' => 'INNER'
        //]);
        //$this->belongsTo('Users', [
        //    'foreignKey' => 'user_id',
        //    'joinType' => 'INNER'
        //]);
        //$this->belongsTo('Kiosks', [
        //    'foreignKey' => 'kiosk_id'
        //]);
        //$this->belongsTo('Networks', [
        //    'foreignKey' => 'network_id'
        //]);
        //$this->belongsTo('MobileResales', [
        //    'foreignKey' => 'mobile_resale_id'
        //]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    
    //public function buildRules(RulesChecker $rules)
    //{
    //    $rules->add($rules->existsIn(['mobile_purchase_id'], 'MobilePurchases'));
    //    $rules->add($rules->existsIn(['user_id'], 'Users'));
    //    $rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));
    //    $rules->add($rules->existsIn(['network_id'], 'Networks'));
    //    $rules->add($rules->existsIn(['mobile_resale_id'], 'MobileResales'));
    //
    //    return $rules;
    //}
}
