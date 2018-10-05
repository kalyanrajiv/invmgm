<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * DeviceActivationRequest Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int $kiosk_id
 * @property string $kiosk_name
 * @property string $email_address
 * @property string $description
 * @property string $otp
 * @property string $device_cookie
 * @property int $status
 * @property int $type
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Kiosk $kiosk
 */
class DeviceActivationRequest extends Entity
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
