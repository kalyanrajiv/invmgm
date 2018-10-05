<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * DeviceCookieDetail Entity
 *
 * @property int $id
 * @property string $device_cookie
 * @property int $device_id
 * @property string $ip_address
 * @property string $user_agent
 * @property int $kiosk_id
 * @property int $logged_in
 * @property string $description
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Device $device
 * @property \App\Model\Entity\Kiosk $kiosk
 */
class DeviceCookieDetail extends Entity
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
