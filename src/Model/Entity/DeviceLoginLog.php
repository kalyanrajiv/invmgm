<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * DeviceLoginLog Entity
 *
 * @property int $id
 * @property int $device_id
 * @property string $device_cookie
 * @property string $ip_address
 * @property string $user_agent
 * @property \Cake\I18n\Time $login_time
 * @property \Cake\I18n\Time $log_out_time
 * @property string $location
 * @property string $longitude
 * @property string $lattitude
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Device $device
 */
class DeviceLoginLog extends Entity
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
