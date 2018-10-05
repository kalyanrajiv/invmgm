<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Images Model
 *
 * @property \Cake\ORM\Association\HasMany $CsvProducts
 * @property \Cake\ORM\Association\HasMany $Kiosk10000Products
 * @property \Cake\ORM\Association\HasMany $Kiosk10Products
 * @property \Cake\ORM\Association\HasMany $Kiosk11Products
 * @property \Cake\ORM\Association\HasMany $Kiosk12Products
 * @property \Cake\ORM\Association\HasMany $Kiosk13Products
 * @property \Cake\ORM\Association\HasMany $Kiosk14Products
 * @property \Cake\ORM\Association\HasMany $Kiosk15Products
 * @property \Cake\ORM\Association\HasMany $Kiosk16Products
 * @property \Cake\ORM\Association\HasMany $Kiosk17Products
 * @property \Cake\ORM\Association\HasMany $Kiosk18Products
 * @property \Cake\ORM\Association\HasMany $Kiosk19Products
 * @property \Cake\ORM\Association\HasMany $Kiosk1Products
 * @property \Cake\ORM\Association\HasMany $Kiosk20Products
 * @property \Cake\ORM\Association\HasMany $Kiosk21Products
 * @property \Cake\ORM\Association\HasMany $Kiosk22Products
 * @property \Cake\ORM\Association\HasMany $Kiosk2Products
 * @property \Cake\ORM\Association\HasMany $Kiosk3Products
 * @property \Cake\ORM\Association\HasMany $Kiosk4Products
 * @property \Cake\ORM\Association\HasMany $Kiosk5Products
 * @property \Cake\ORM\Association\HasMany $Kiosk7Products
 * @property \Cake\ORM\Association\HasMany $Kiosk8Products
 * @property \Cake\ORM\Association\HasMany $KioskProducts
 * @property \Cake\ORM\Association\HasMany $Products
 *
 * @method \App\Model\Entity\Image get($primaryKey, $options = [])
 * @method \App\Model\Entity\Image newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Image[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Image|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Image patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Image[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Image findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ImagesTable extends Table
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

        $this->table('images');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('CsvProducts', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Kiosk10000Products', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Kiosk10Products', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Kiosk11Products', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Kiosk12Products', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Kiosk13Products', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Kiosk14Products', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Kiosk15Products', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Kiosk16Products', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Kiosk17Products', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Kiosk18Products', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Kiosk19Products', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Kiosk1Products', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Kiosk20Products', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Kiosk21Products', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Kiosk22Products', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Kiosk2Products', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Kiosk3Products', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Kiosk4Products', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Kiosk5Products', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Kiosk7Products', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Kiosk8Products', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('KioskProducts', [
            'foreignKey' => 'image_id'
        ]);
        $this->hasMany('Products', [
            'foreignKey' => 'image_id'
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
            ->requirePresence('image', 'create')
            ->notEmpty('image');

        $validator
            ->requirePresence('mime_type', 'create')
            ->notEmpty('mime_type');

        $validator
            ->requirePresence('path', 'create')
            ->notEmpty('path');

        return $validator;
    }
}
