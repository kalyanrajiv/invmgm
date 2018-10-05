<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Profile Entity
 *
 * @property int $id
 * @property \Cake\I18n\Time $date_of_birth
 * @property string $national_insurance
 * @property string $visa_type
 * @property \Cake\I18n\Time $visa_expiry_date
 * @property string $memo
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 * @property int $user_id
 *
 * @property \App\Model\Entity\User $user
 */
class Profile extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
