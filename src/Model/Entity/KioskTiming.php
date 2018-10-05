<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * KioskTiming Entity
 *
 * @property int $id
 * @property int $kiosk_id
 * @property \Cake\I18n\Time $mon_time_in
 * @property \Cake\I18n\Time $mon_time_out
 * @property \Cake\I18n\Time $tues_time_in
 * @property \Cake\I18n\Time $tues_time_out
 * @property \Cake\I18n\Time $wed_time_in
 * @property \Cake\I18n\Time $wed_time_out
 * @property \Cake\I18n\Time $thrus_time_in
 * @property \Cake\I18n\Time $thrus_time_out
 * @property \Cake\I18n\Time $fri_time_in
 * @property \Cake\I18n\Time $fri_time_out
 * @property \Cake\I18n\Time $sat_time_in
 * @property \Cake\I18n\Time $sat_time_out
 * @property \Cake\I18n\Time $sun_time_in
 * @property \Cake\I18n\Time $sun_time_out
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modifed
 *
 * @property \App\Model\Entity\Kiosk $kiosk
 */
class KioskTiming extends Entity
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
