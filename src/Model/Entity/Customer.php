<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Customer Entity
 *
 * @property int $id
 * @property int $kiosk_id
 * @property string $business
 * @property string $fname
 * @property string $lname
 * @property string $vat_number
 * @property \Cake\I18n\Time $date_of_birth
 * @property string $email
 * @property string $mobile
 * @property string $landline
 * @property string $city
 * @property string $state
 * @property string $country
 * @property string $zip
 * @property string $address_1
 * @property string $address_2
 * @property int $same_delivery_address
 * @property string $imei
 * @property int $status
 * @property string $del_city
 * @property string $del_state
 * @property string $del_zip
 * @property string $del_address_1
 * @property string $del_address_2
 * @property \Cake\I18n\Time $modified
 * @property \Cake\I18n\Time $created
 */
class Customer extends Entity
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
