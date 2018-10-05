<?php
return [
    /*
     * my configuration
     */
    'app_id' => ['app_id'=>'34381'],
    'app_key' => ['app_key'=> '120501a14c54e8edad13'],
    'app_secret' => ['app_secret' => '0bd19622e4fcfc9aeeee'],
    'months' => [
					    '1' => 'January',
                        '2' => 'February',
                        '3' => 'March',
                        '4' => 'April',
                        '5' => 'May',
                        '6' => 'June',
                        '7' => 'July',
                        '8' => 'August',
                        '9' => 'September',
                        '10' => 'October',
                        '11' => 'November',
                        '12' => 'December'
         ],
    'visa_type' => [
					    'BC' => 'British Citizen',
					    'EC' => 'European Citizen',
					    'WP' => 'Work Permit',
					    'BV' => 'Business Visa',
					    'FTV' => 'Full Time Visa',
					    'SV20' => 'Student Visa 20 hrs',
					    'SV10' => 'Student Visa 10 hrs',
					    'SV0' => 'Student Visa 0 hrs',
					    'OTH' => 'Other'
				],
   'grade_type' => [
                    '1'=>'Grade A',
                    '2'=>'Grade B',
                    '3'=>'Grade C',
                    '4'=>'Grade D'
       ],
    
    //payment method
    'payment_type' => [
					    'Cheque' => 'Cheque',
					    'Cash' => 'Cash',
					    'Bank Transfer' => 'Bank Transfer',
					    'Card' => 'Card',
					    'On Credit' => 'On Credit'
					],
    
    //kiosk type
    'kiosk_type'=>[
                   1 => 'Kiosk',
                   2 => 'Service Center',
                   3 => 'Unlocking Center'
                  ],
    
    
     'refund_status'=> [
                       '0' => 'Not Refunded',
                       '1' => 'Normal Refund',
                       '2' => 'Faulty Refund'
                       ],
    'selling_status'=>['1' => 'Sold','0' => 'Refunded'],
    'order_status'=>['1' => 'Transient','2' => 'Confirmed'],
    
    //yes no options
    'yes_no'=>['1' => 'Yes','0' => 'No'],
    
    //approval status
    'approval_status'=>['1' => 'Approved','0' => 'Not Approved'],
    
     //color options
//    'color'=>[
//                    '1' => 'Black',
//                    '2' => 'White',
//                    '5' => 'Pink',
//                    '3' => 'Brown',
//                    '4' => 'Blue',
//                    '6' => 'Purple',
//                    '7' => 'Green',
//                    '8' => 'Yellow',
//                    '9' => 'Orange',
//                    '10' => 'Maroon',
//                    '11' => 'Golden',
//                    '12' => 'Rose Pink',
//                    '13' => 'Peach',
//                    '14' => 'Cream',
//                    '15' => 'Lylac'
//				],
    
      //color options for product
//    'colour'=>[
//					    'Black' => 'Black',
//					    'Baby Pink' => 'Baby Pink',
//					    'Brown' => 'Brown',
//					    'Blue' => 'Blue',
//					    'Clean' => 'Clear',
//					    'Dark Blue' => 'Dark Blue',
//					    'Golden' => 'Golden',
//					    'Grey' => 'Grey',
//                        'Green' => 'Green',
//					    'Hot Pink' => 'Hot Pink',                                            
//					    'Lime Green' => 'Lime Green',
//					    'Mehroon' => 'Mehroon',
//					    'Orange' => 'Orange',
//					    'Pink' => 'Pink',
//					    'Purple' => 'Purple',
//					    'Red' => 'Red',
//					    'Sky Blue' => 'Sky Blue',
//					    'Union Jack Print' => 'Union Jack Print',					    			    
//					    'White' => 'White',
//					    'Yellow' => 'Yellow',
//					    'Chocolate' => 'Chocolate',
//					    'Silver' => 'Silver',
//					    'Multi Color' => 'Multi Color',
//						'Rose Pink' => 'Rose Pink',
//						'Peach' => 'Peach',
//						'Cream' => 'Cream',
//						'Lylac' => 'Lylac',
//                        'Other' => 'Other'
//					],
    
    'dispute'=>[
					    '-1' => 'Received Less',
					    '1' => 'Received More',
					],
     'sites'=>[
					'1' => 'mbwaheguru',
                    '2' => 'fonerevive',
					    ],
          'external_sites' =>
                    [
                        '1' => 'hifiprofile',
                        '2' => 'fonerevive',
                    ],
          'site_full_url' => [
                              'mbwaheguru' => 'mbwaheguru.co.uk',
                              'hifiprofile' => 'hifiprofile.net',
                              'fonerevive' => 'fonerevive.net',
                              ],
          'disnt_for_diff_sites'=>['1'=>'fonerevive'],
          'external_sites_for_bulk' =>
                    [
                        '1' => 'mbwaheguru',
                        '2' => 'fonerevive',
                    ],
          'send_by_email' => "sales@hifiprofile.net",
          "EMAIL_SENDER" => "hifiprofile",
           'EXT_RETAIL' => ['fonerevive'],
           //'INT_RETAIL' => '',
    //problem type
    'problem_type'=>[
						    '1' => 'touch problem',
						    '2' => 'LCD',
						    '3' => 'MIC',
						    '4' => 'Speaker',
						    '5' => 'Charging Blocks',
						    '6' => 'Water Damage',
						    '7' => 'battery problem',
						    '8' => 'Ear piece',
                            '9' => 'Ringer or Speaker',
						    '10' => 'Power button',
						    '11' => 'Volume button',
						    '12' => 'Hands free port',
						    '13' => 'Front Camera',
						    '14' => 'Back Camera',
						    '15' => 'Battery back cover',
						    '16' => 'Sim slot',
                            '17' => 'Memory card slot',
						    '18' => 'Internal Keypad',
						    '19' => 'Dead phone',
						    '20' => 'Battery connector',
						    '21' => 'Home button',
						    '22' => 'Network problem',
						    '23' => 'Wifi problem',
						    '24' => 'Sensor problem',
                            '25' => 'Silent button',
						    '26' => 'Bluetooth problem',
						    '27' => 'Mother board damaged',
						    '28' => 'Ribbon problem',
						    '29' => 'Vibrator problem',
                            '30' => 'Other',
                            '31' => 'Complete LCD + Touch',
                            '32' => 'Scroll',
                            '33' => 'Internal Home Button',
                            '34' => 'External Home Button',
                            '35' => 'External Keypad'
						],
     
    //renewal weeks alert
    'renewal_weeks'=>[
						    '0' => 'None',
						    '1' => 'One week',
						    '2' => 'Two weeks',
						    '3' => 'Three weeks',
						    '4' => 'Four weeks',
						    '5' => 'Five weeks',
						    '6' => 'Six weeks',
						],
        
    //renewal months alert
    'renewal_months'=>[
						    '0' => 'None',
						    '1' => 'One month',
						    '2' => 'Two months',
						    '3' => 'Three months',
						    '4' => 'Four months',
						    '5' => 'Five months',
						    '6' => 'Six months',
						],
        
    //customer identification
    'identification'=>[
						    'Driving License' => 'Driving License',
						    'Passport' => 'Passport',
                             'Bank Statement' => 'Bank Statement',
						    'Utility bills' => 'Utility bills',
						    'Others' => 'Others',
						],
    
    'contract_type'=>[
						    'Lease',
						    'Temporary Contract',
						],
        
    'featured'=>['1' => 'Yes','0' => 'No','' => '--  --'],	
    
    //active options
    'active'=>['1' => 'Active','0' => 'Inactive'],
    'SITE_BASE_URL'=> defined('URL_SCHEME') ? URL_SCHEME.'hifiprofile.net' : 'http://hifiprofile.net',
    'SITE_BASE_DOMAIN_NAME'=>'hifiprofile',
    'WAREHOUSE_KIOSK_ID' =>'10000',
    'CURRENCY_TYPE'=>'&pound;',
    'FROM_EMAIL'=>'hifiprofile',
    "MAIN_SITE_DB" => "hifiprofile",
//    //Discount options
//   $discountArr = [],
//   $discountArr = [],
//    for($i = 0; $i <= 50; $i++){
//	if($i==0){
//	    $discountArr[0] = "None";
//	    continue;
//	}
//	/*if($i%5==0){
//	    $discountArr[$i] = "$i %";
//	}*/
//            $discountArr[$i] = "$i %";
//
//    }
//        
//    $config['options']['discount'] = $discountArr;					
//    
//    for($i=0; $i<=50; $i++){
//        $newDiscountArr[$i] = "$i %";
//    }
//    
//    $config['options']['new_discount'] = $newDiscountArr;
    
    
    //mobile condtions
    'mobile_conditions'=>[
                                                        '0' => 'Like New',
                                                        '1' => 'Good Condition',
                                                        '2' => 'Scratch at Back',
                                                        '3' => 'Scratch at Screen',
                                                        '4' => 'Dents/Crack on Frame',
                                                        '5' => 'Loose Frame/Back Cover',
                                                        '6' => 'Damaged Keypad',
                                                        '7' => 'Damaged Scroll/Home Button',
                                                        '8' => 'Missing/Loose Power Button',
                                                        '9' => 'Missing/Loose Camera Button',
                                                        '10' => 'Missing/Loose Silent Button',
                                                        '11' => 'Missing/Loose Volume Buttons',
                                                        '12'=> 'Phone not turning on (No Function checked)'
						    ],
    //repair_status kiosk users
    'repair_statuses_user'=>[
							'1' => 'Booked',
							'2' => 'Rebooked',
							'3' => 'Dispatched to repair centre',
							'4' => 'Received repaired from repair centre',
							'5' => 'Received unrepaired from repair centre',
							'6' => 'Delivered: Repaired by Kiosk',
							'7' => 'Delivered: Unrepaired by Kiosk',
							'8' => 'Delivered: Repaired by repair centre',
							'9' => 'Delivered: Unrepaired by repair centre'                                                
						    ],
    
    //repair_status technician
    'repair_statuses_technician'=>[
								'16' => 'Received by repair centre',
								'17' => 'Repair under process',
								'18' => 'Dispatched to Kiosk: Repaired',
								'19' => 'Dispatched to Kiosk: Unrepaired',
								'20' => 'Waiting for dispatch: Repaired'
							    ],
        
    //unlock_status kiosk users
    'unlock_statuses_user'=>[
                                                        0 => 'Virtually Booked: Request forwarded to Unlock Center',
							'1' => 'Physically booked',
							'2' => 'Unlock request sent to Unlocking center',
							'3' => 'Dispatched to Unlocking Center',
							'4' => 'Unlocked: Confirmation passed to customer',
							'5' => 'Unlocking Failed: Refund to customer',
							'6' => 'Received unlocked from Unlocking Center',
							'7' => 'Received unprocessed from Unlocking Center',
							'8' => 'Refund raised',
							'9' => 'Delivered: Unlocked by Unlocking Center',
							'10' => 'Delivered: Unlocking failed at Unlocking Center',
							'11' => 'Delivered: Unlocked by Kiosk',
							'12' => 'Delivered: Unlocking failed at Kiosk'
						    ],
        
    // unlock_status technician
    'unlock_statuses_technician'=>[
							    '16' => 'Unlock request received: In process',
							    '17' => 'Phone received by Unlocking Center',
							    '18' => 'Unlock under Process',
							    '19' => 'Waiting for Dispatch: Unlocked',
							    '20' => 'Unlock processed: Confirmation sent to Kiosk',
							    '21' => 'Dispatched to Kiosk: Unlocked',
							    '22' => 'Dispatched to Kiosk: Unprocessed',
                                                            '23' => 'Unlock failed: Confirmation sent to Kiosk',
							],
        
    //purchase_statuses
    'purchase_statuses'=>[
							'1' => 'Excellent Condition',
							'2' => 'Good Condition',
							'3' => 'Average',
							'4' => 'Below Average',
							'' => '-- --'
						    ],
    
    //resale_statuses
    'resale_statuses'=>[
						    '1' => 'Status 1',
						    '2' => 'Status 2',
						    '3' => 'Status 3',
						    '4' => 'Status 4',
						    '' => '-- --'
						],
	
    //campaign status options
    'campaign_status'=>[
						    '1' => 'Active',
						    '0' => 'Disabled',
						    '2' => 'Refused',
						    '3' => 'Prohibited',
						    '4' => 'Not Offered',
						],	
	
	
    //gender options
    'gender'=>['M' => 'Male','F' => 'Female'],
    
    //UK-Non UK options
    
    'uk_non_uk'=>[
                                        'GB' => 'United Kingdom',
                                        'OTH' => 'Other'
                                            ],
    //UK state options
    
    'uk_counties'=>[
                                         'Beds' => 'Bedfordshire',
                                         'Berks' => 'Berkshire',
                                         'Bucks' => 'Buckinghamshire',
                                         'Cambs' => 'Cambridgeshire',
                                         'Ches' => 'Cheshire',
                                         'Corn' => 'Cornwall',
                                         'Cumb' => 'Cumberland',
                                         'Derbys' => 'Derbyshire',     
                                         'Dev' => 'Devon',
                                         'Dor' => 'Dorset',
                                         'Co Dur' => 'County Durham',
                                         'Es' => 'Essex',
                                         'Glos' => 'Gloucestershire',
                                         'Hants' => 'Hampshire',
                                         'Here' => 'Herefordshire',
                                         'Herts' => 'Hertfordshire',     
                                         'Hunts' => 'Huntingdonshire',
                                         'Kent' => 'Kent',
                                         'Lancs' => 'Lancashire',
                                         'Leics' => 'Leicestershire',
                                         'Lincs' => 'Lincolnshire',
                                         'Mx' => 'Middlesex',
                                         'Norf' => 'Norfolk',
                                         'Northants' => 'Northamptonshire',     
                                         'Northumb' => 'Northumberland',
                                         'Notts' => 'Nottinghamshire',
                                         'Oxon' => 'Oxfordshire',
                                         'Rut' => 'Rutland',
                                         'Shrops' => 'Shropshire',
                                         'Som' => 'Somerset',
                                         'Staffs' => 'Staffordshire',
                                         'Suff' => 'Suffolk',
                                         'Sy' => 'Surrey',
                                         'Sx' => 'Sussex',
                                         'Warks' => 'Warwickshire',
                                         'Westm' => 'Westmorland',
                                         'Wilts' => 'Wiltshire',
                                         'Worcs' => 'Worcestershire',
                                         'Yorks' => 'Yorkshire'
                                              ],
    //country options
    'country'=>[
					    "AF" => "Afghanistan",
					    "AX" => "ÅLand Islands",
					    "AL" => "Albania",
					    "DZ" => "Algeria",
					    "AS" => "American Samoa",
					    "AD" => "Andorra",
					    "AO" => "Angola",
					    "AI" => "Anguilla",
					    "AQ" => "Antarctica",
					    "AG" => "Antigua And Barbuda",
					    "AR" => "Argentina",
					    "AM" => "Armenia",
					    "AW" => "Aruba",
					    "AU" => "Australia",
					    "AT" => "Austria",
					    "AZ" => "Azerbaijan",
					    "BS" => "Bahamas",
					    "BH" => "Bahrain",
					    "BD" => "Bangladesh",
					    "BB" => "Barbados",
					    "BY" => "Belarus",
					    "BE" => "Belgium",
					    "BZ" => "Belize",
					    "BJ" => "Benin",
					    "BM" => "Bermuda",
					    "BT" => "Bhutan",
					    "BO" => "Bolivia",
					    "BA" => "Bosnia And Herzegovina",
					    "BW" => "Botswana",
					    "BV" => "Bouvet Island",
					    "BR" => "Brazil",
					    "IO" => "British Indian Ocean Territory",
					    "BN" => "Brunei Darussalam",
					    "BG" => "Bulgaria",
					    "BF" => "Burkina Faso",
					    "BI" => "Burundi",
					    "KH" => "Cambodia",
					    "CM" => "Cameroon",
					    "CA" => "Canada",
					    "CV" => "Cape Verde",
					    "KY" => "Cayman Islands",
					    "CF" => "Central African Republic",
					    "TD" => "Chad",
					    "CL" => "Chile",
					    "CN" => "China",
					    "CX" => "Christmas Island",
					    "CC" => "Cocos (Keeling) Islands",
					    "CO" => "Colombia",
					    "KM" => "Comoros",
					    "CG" => "Congo",
					    "CD" => "Congo, The Democratic Republic Of The",
					    "CK" => "Cook Islands",
					    "CR" => "Costa Rica",
					    "CI" => "Cote D'Ivoire",
					    "HR" => "Croatia",
					    "CU" => "Cuba",
					    "CY" => "Cyprus",
					    "CZ" => "Czech Republic",
					    "DK" => "Denmark",
					    "DJ" => "Djibouti",
					    "DM" => "Dominica",
					    "DO" => "Dominican Republic",
					    "EC" => "Ecuador",
					    "EG" => "Egypt",
					    "SV" => "El Salvador",
					    "GQ" => "Equatorial Guinea",
					    "ER" => "Eritrea",
					    "EE" => "Estonia",
					    "ET" => "Ethiopia",
					    "FK" => "Falkland Islands (Malvinas)",
					    "FO" => "Faroe Islands",
					    "FJ" => "Fiji",
					    "FI" => "Finland",
					    "FR" => "France",
					    "GF" => "French Guiana",
					    "PF" => "French Polynesia",
					    "TF" => "French Southern Territories",
					    "GA" => "Gabon",
					    "GM" => "Gambia",
					    "GE" => "Georgia",
					    "DE" => "Germany",
					    "GH" => "Ghana",
					    "GI" => "Gibraltar",
					    "GR" => "Greece",
					    "GL" => "Greenland",
					    "GD" => "Grenada",
					    "GP" => "Guadeloupe",
					    "GU" => "Guam",
					    "GT" => "Guatemala",
					    "Gg" => "Guernsey",
					    "GN" => "Guinea",
					    "GW" => "Guinea-Bissau",
					    "GY" => "Guyana",
					    "HT" => "Haiti",
					    "HM" => "Heard Island And Mcdonald Islands",
					    "VA" => "Holy See (Vatican City State)",
					    "HN" => "Honduras",
					    "HK" => "Hong Kong",
					    "HU" => "Hungary",
					    "IS" => "Iceland",
					    "IN" => "India",
					    "ID" => "Indonesia",
					    "IR" => "Iran, Islamic Republic Of",
					    "IQ" => "Iraq",
					    "IE" => "Ireland",
					    "IM" => "Isle Of Man",
					    "IL" => "Israel",
					    "IT" => "Italy",
					    "JM" => "Jamaica",
					    "JP" => "Japan",
					    "JE" => "Jersey",
					    "JO" => "Jordan",
					    "KZ" => "Kazakhstan",
					    "KE" => "Kenya",
					    "KI" => "Kiribati",
					    "KP" => "Korea, Democratic People'S Republic Of",
					    "KR" => "Korea, Republic Of",
					    "KW" => "Kuwait",
					    "KG" => "Kyrgyzstan",
					    "LA" => "Lao People'S Democratic Republic",
					    "LV" => "Latvia",
					    "LB" => "Lebanon",
					    "LS" => "Lesotho",
					    "LR" => "Liberia",
					    "LY" => "Libyan Arab Jamahiriya",
					    "LI" => "Liechtenstein",
					    "LT" => "Lithuania",
					    "LU" => "Luxembourg",
					    "MO" => "Macao",
					    "MK" => "Macedonia, The Former Yugoslav Republic Of",
					    "MG" => "Madagascar",
					    "MW" => "Malawi",
					    "MY" => "Malaysia",
					    "MV" => "Maldives",
					    "ML" => "Mali",
					    "MT" => "Malta",
					    "MH" => "Marshall Islands",
					    "MQ" => "Martinique",
					    "MR" => "Mauritania",
					    "MU" => "Mauritius",
					    "YT" => "Mayotte",
					    "MX" => "Mexico",
					    "FM" => "Micronesia, Federated States Of",
					    "MD" => "Moldova, Republic Of",
					    "MC" => "Monaco",
					    "MN" => "Mongolia",
					    "MS" => "Montserrat",
					    "MA" => "Morocco",
					    "MZ" => "Mozambique",
					    "MM" => "Myanmar",
					    "NA" => "Namibia",
					    "NR" => "Nauru",
					    "NP" => "Nepal",
					    "NL" => "Netherlands",
					    "AN" => "Netherlands Antilles",
					    "NC" => "New Caledonia",
					    "NZ" => "New Zealand",
					    "NI" => "Nicaragua",
					    "NE" => "Niger",
					    "NG" => "Nigeria",
					    "NU" => "Niue",
					    "NF" => "Norfolk Island",
					    "MP" => "Northern Mariana Islands",
					    "NO" => "Norway",
					    "OM" => "Oman",
					    "PK" => "Pakistan",
					    "PW" => "Palau",
					    "PS" => "Palestinian Territory, Occupied",
					    "PA" => "Panama",
					    "PG" => "Papua New Guinea",
					    "PY" => "Paraguay",
					    "PE" => "Peru",
					    "PH" => "Philippines",
					    "PN" => "Pitcairn",
					    "PL" => "Poland",
					    "PT" => "Portugal",
					    "PR" => "Puerto Rico",
					    "QA" => "Qatar",
					    "RE" => "Reunion",
					    "RO" => "Romania",
					    "RU" => "Russian Federation",
					    "RW" => "Rwanda",
					    "SH" => "Saint Helena",
					    "KN" => "Saint Kitts And Nevis",
					    "LC" => "Saint Lucia",
					    "PM" => "Saint Pierre And Miquelon",
					    "VC" => "Saint Vincent And The Grenadines",
					    "WS" => "Samoa",
					    "SM" => "San Marino",
					    "ST" => "Sao Tome And Principe",
					    "SA" => "Saudi Arabia",
					    "SN" => "Senegal",
					    "CS" => "Serbia And Montenegro",
					    "SC" => "Seychelles",
					    "SL" => "Sierra Leone",
					    "SG" => "Singapore",
					    "SK" => "Slovakia",
					    "SI" => "Slovenia",
					    "SB" => "Solomon Islands",
					    "SO" => "Somalia",
					    "ZA" => "South Africa",
					    "GS" => "South Georgia And The South Sandwich Islands",
					    "ES" => "Spain",
					    "LK" => "Sri Lanka",
					    "SD" => "Sudan",
					    "SR" => "Suriname",
					    "SJ" => "Svalbard And Jan Mayen",
					    "SZ" => "Swaziland",
					    "SE" => "Sweden",
					    "CH" => "Switzerland",
					    "SY" => "Syrian Arab Republic",
					    "TW" => "Taiwan, Province Of China",
					    "TJ" => "Tajikistan",
					    "TZ" => "Tanzania, United Republic Of",
					    "TH" => "Thailand",
					    "TL" => "Timor-Leste",
					    "TG" => "Togo",
					    "TK" => "Tokelau",
					    "TO" => "Tonga",
					    "TT" => "Trinidad And Tobago",
					    "TN" => "Tunisia",
					    "TR" => "Turkey",
					    "TM" => "Turkmenistan",
					    "TC" => "Turks And Caicos Islands",
					    "TV" => "Tuvalu",
					    "UG" => "Uganda",
					    "UA" => "Ukraine",
					    "AE" => "United Arab Emirates",
					    "GB" => "United Kingdom",
					    "US" => "United States",
					    "UM" => "United States Minor Outlying Islands",
					    "UY" => "Uruguay",
					    "UZ" => "Uzbekistan",
					    "VU" => "Vanuatu",
					    "VE" => "Venezuela",
					    "VN" => "Viet Nam",
					    "VG" => "Virgin Islands, British",
					    "VI" => "Virgin Islands, U.S.",
					    "WF" => "Wallis And Futuna",
					    "EH" => "Western Sahara",
					    "YE" => "Yemen",
					    "ZM" => "Zambia",
					    "ZW" => "Zimbabwe"
					],

	//priority options for campaigns
	'priority'=>[
						'10' => '10',
						'20' => '20',
						'30' => '30',
						'40' => '40',
						'50' => '50',
						'60' => '60',
						'70' => '70',
						'80' => '80',
						'90' => '90',
						'100' => '100',
					    ],
    
    'pusher_credentials' => [
        'key' => '6d915fc6ee5916612811',
        'srcert' => 'f9e4ee2857b3298a56cb',
        'app_id' => '350851',
    ],
    
    'address_api_credentials' => [
        'account' => '2643',
        'password' => 'vpe84rxm',
    ],
    
    'text_message_credentials' => [
        'username' => 'mobilebooth',
        'password' => 'bn1469',
        'source' => 'MBooth'
    ],
    
    
   
        //if(!isset($productTable)){
        //    $productTable = '';
        //}
        //if(!isset($receiptTable)){
        //    $receiptTable = '';
        //}
        //if(!isset($saleTable)){
        //    $saleTable = '';
        //}
        //if(!isset($stockTransferTable)){
        //    $stockTransferTable = '';
        //}
        //
        //if(!isset($paymentTable)){
        //    $paymentTable = '';
        //}
        //
        //if(!isset($invoiceTable)){
        //    $invoiceTable = '';
        //}
        //
        //if(!isset($invoiceDetailTable)){
        //    $invoiceDetailTable = '';
        //}
        //
        //if(!isset($creditReceiptTable)){
        //    $creditReceiptTable = '';
        //}
        //
        //if(!isset($creditProductDetailTable)){
        //    $creditProductDetailTable = '';
        //}
        //
        //if(!isset($creditPaymentTable)){
        //    $creditPaymentTable = '';
        //}
        //
        //if(!isset($dailyStockTable)){
        //    $dailyStockTable = '';
        //}
        //
        //'table_definition'=>[
        //    //Table No: 1
        //                            'product_table' => "CREATE TABLE IF NOT EXISTS `$productTable` (
        //                                                    `prefix` varchar(2) NOT NULL DEFAULT 'SD',
        //                                                    `id` int(11) unsigned NOT NULL,
        //                                                    `product` varchar(150) NOT NULL,
        //                                                    `quantity` int(11) unsigned NOT NULL,
        //                                                    `description` text NOT NULL,
        //                                                    `category_id` int(11) unsigned NOT NULL,
        //                                                    `cost_price` float(10,2) unsigned NOT NULL,
        //                                                    `lu_cp` datetime DEFAULT NULL,
        //                                                    `retail_cost_price` float(10,2) DEFAULT NULL,
        //                                                    `lu_rcp` datetime DEFAULT NULL,
        //                                                    `selling_price` float(10,2) NOT NULL,
        //                                                    `lu_sp` datetime DEFAULT NULL,
        //                                                    `retail_selling_price` float(10,2) DEFAULT NULL,
        //                                                    `lu_rsp` datetime DEFAULT NULL,
        //                                                    `brand_id` int(11) unsigned NOT NULL,
        //                                                    `model` varchar(100) NOT NULL,
        //                                                    `manufacturing_date` date NOT NULL,
        //                                                    `sku` int(11) unsigned NOT NULL,
        //                                                    `country_make` varchar(80) NOT NULL,
        //                                                    `product_code` varchar(50) NOT NULL,
        //                                                    `weight` float(8,2) DEFAULT NULL,
        //                                                    `color` varchar(30) NOT NULL,
        //                                                    `user_id` int(11) unsigned DEFAULT '1',
        //                                                    `featured` tinyint(4) unsigned DEFAULT '0',
        //                                                    `discount` int(11) DEFAULT NULL COMMENT 'In %',
        //                                                    `discount_status` tinyint(4) unsigned NOT NULL,
        //                                                    `max_discount` tinyint(5) unsigned NOT NULL,
        //                                                    `min_discount` tinyint(5) unsigned NOT NULL,
        //                                                    `image_id` int(11) DEFAULT NULL,
        //                                                    `image` varchar(255) NOT NULL,
        //                                                    `image_dir` varchar(255) NOT NULL,
        //                                                    `manufacturer` varchar(255) NOT NULL COMMENT 'Address details of manufacturer',
        //                                                    `stock_level` int(11) NOT NULL,
        //                                                    `dead_stock_level` int(11) NOT NULL,
        //                                                    `status` tinyint(4) NOT NULL,
        //                                                    `created` datetime NOT NULL,
        //                                                    `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        //                                                    PRIMARY KEY (`id`),
        //                                                    UNIQUE KEY `prefix` (`prefix`,`id`),
        //                                                    UNIQUE KEY `id` (`id`),
        //                                                    UNIQUE KEY `product_code` (`product_code`)
        //                                                ) ENGINE=InnoDB",
        //    //Table No: 2                                                
        //                            'product_receipt_table' => "CREATE TABLE IF NOT EXISTS `$receiptTable` (
        //                                                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        //                                                    `customer_id` int(11) unsigned NOT NULL,
        //                                                    `fname` varchar(70) NOT NULL,
        //                                                    `lname` varchar(70) NOT NULL,
        //                                                    `email` varchar(100) NOT NULL,
        //                                                    `mobile` varchar(15) NOT NULL,
        //                                                    `address_1` varchar(255) NOT NULL,
        //                                                    `address_2` varchar(255) NOT NULL,
        //                                                    `city` varchar(150) NOT NULL,
        //                                                    `state` varchar(150) NOT NULL,
        //                                                    `zip` varchar(12) NOT NULL,
        //                                                    `vat` int(10) unsigned NOT NULL,
        //                                                    `bill_amount` float(10,2) unsigned NOT NULL,
        //                                                    `bulk_discount` tinyint(5) unsigned DEFAULT NULL,
        //                                                    `processed_by` int(11) unsigned NOT NULL,
        //                                                    `status` tinyint(4) unsigned NOT NULL,
        //                                                    `created` datetime NOT NULL,
        //                                                    `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        //                                                    PRIMARY KEY (`id`)
        //                                                ) ENGINE=InnoDB",
        //    //Table No: 3                                                
        //                            'product_sale_table' => "CREATE TABLE IF NOT EXISTS `$saleTable` (
        //                                                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        //                                                    `kiosk_id` int(11) unsigned NOT NULL,
        //                                                    `product_id` int(11) unsigned NOT NULL,
        //                                                    `quantity` int(11) unsigned NOT NULL,
        //                                                    `sale_price` float(10,2) unsigned NOT NULL,
        //                                                    `refund_price` float(10,2) unsigned NOT NULL,
        //                                                    `discount` TINYINT(5) DEFAULT NULL COMMENT 'In %',
        //                                                    `discount_status` TINYINT( 4 ) UNSIGNED NOT NULL,					
        //                                                    `refund_gain` float(10,2) unsigned NOT NULL,
        //                                                    `sold_by` int(11) unsigned NOT NULL,
        //                                                    `refund_by` INT( 11 ) UNSIGNED NOT NULL,
        //                                                    `status` tinyint(4) unsigned NOT NULL DEFAULT '1',
        //                                                    `refund_status` TINYINT( 4 ) UNSIGNED NOT NULL DEFAULT '0',
        //                                                    `refund_remarks` VARCHAR( 255 ) NOT NULL,
        //                                                    `product_receipt_id` int(11) unsigned NOT NULL,
        //                                                    `remarks` varchar(255) NOT NULL,
        //                                                    `created` datetime NOT NULL,
        //                                                    `modified` datetime NOT NULL,
        //                                                    PRIMARY KEY (`id`)
        //                                              ) ENGINE=InnoDB",
        //    //Table No: 4                                              
        //                            'transferred_stock' => "CREATE TABLE IF NOT EXISTS `$stockTransferTable` (
        //                                                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        //                                                    `kiosk_order_id` int(11) unsigned NOT NULL,
        //                                                    `product_id` int(11) unsigned NOT NULL,
        //                                                    `quantity` int(11) unsigned NOT NULL,
        //                                                    `sale_price` float(8,2) unsigned NOT NULL,
        //                                                    `status` int(11) NOT NULL,
        //                                                    `created` datetime NOT NULL,
        //                                                    `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        //                                                    PRIMARY KEY (`id`)
        //                                                ) ENGINE=InnoDB",
        //    //Table No: 5                                                
        //                            'payment_table' => "CREATE TABLE IF NOT EXISTS `$paymentTable` (
        //                                                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        //                                                    `invoice_order_id` int(11) unsigned NOT NULL,
        //                                                    `product_receipt_id` int(11) unsigned NOT NULL,
        //                                                    `payment_method` varchar(20) NOT NULL,
        //                                                    `description` varchar(255) NOT NULL,
        //                                                    `amount` float(8,2) unsigned NOT NULL,
        //                                                    `payment_status` tinyint(4) unsigned NOT NULL,
        //                                                    `status` tinyint(4) unsigned DEFAULT NULL,
        //                                                    `created` datetime NOT NULL,
        //                                                    `modified` datetime NOT NULL,
        //                                                    PRIMARY KEY (`id`)
        //                                                ) ENGINE=InnoDB",
        //    //Table No: 6                                                  
        //                            'invoice_table' => "CREATE TABLE IF NOT EXISTS `$invoiceTable` (
        //                                                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        //                                                    `kiosk_id` int(11) unsigned NOT NULL,
        //                                                    `user_id` int(11) unsigned NOT NULL,
        //                                                    `customer_id` int(11) unsigned DEFAULT NULL,
        //                                                    `fname` varchar(50) NOT NULL,
        //                                                    `lname` varchar(50) NOT NULL,
        //                                                    `email` varchar(50) NOT NULL,
        //                                                    `mobile` varchar(12) NOT NULL,
        //                                                    `bulk_discount` float(5,2) unsigned DEFAULT NULL,
        //                                                    `del_city` varchar(50) NOT NULL,
        //                                                    `del_state` varchar(50) NOT NULL,
        //                                                    `del_zip` varchar(20) NOT NULL,
        //                                                    `del_address_1` varchar(255) NOT NULL,
        //                                                    `del_address_2` varchar(255) NOT NULL,
        //                                                    `invoice_status` tinyint(4) unsigned NOT NULL,
        //                                                    `status` tinyint(4) unsigned NOT NULL,
        //                                                    `amount` float(10,2) unsigned NOT NULL,
        //                                                    `created` datetime NOT NULL,
        //                                                    `modified` datetime NOT NULL,
        //                                                    PRIMARY KEY (`id`)
        //                                                ) ENGINE=InnoDB",
        //    //Table No: 7                                                  
        //                            'invoice_details_table' => "CREATE TABLE IF NOT EXISTS `$invoiceDetailTable` (
        //                                                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        //                                                    `kiosk_id` int(11) unsigned NOT NULL,
        //                                                    `invoice_order_id` int(11) unsigned NOT NULL,
        //                                                    `product_id` int(11) unsigned NOT NULL,
        //                                                    `price` float(8,2) unsigned NOT NULL,
        //                                                    `quantity` int(11) unsigned NOT NULL,
        //                                                    `discount` tinyint(4) unsigned NOT NULL,
        //                                                    `status` tinyint(4) unsigned NOT NULL,
        //                                                    `created` datetime NOT NULL,
        //                                                    `modified` datetime NOT NULL,
        //                                                    PRIMARY KEY (`id`)
        //                                                ) ENGINE=InnoDB",
        //    //Table No: 8                                                    
        //                            'credit_receipts_table' => "CREATE TABLE IF NOT EXISTS `$creditReceiptTable` (
        //                                                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        //                                                    `customer_id` int(11) unsigned NOT NULL,
        //                                                    `fname` varchar(70) DEFAULT NULL,
        //                                                    `lname` varchar(70) DEFAULT NULL,
        //                                                    `email` varchar(100) NOT NULL,
        //                                                    `mobile` varchar(12) DEFAULT NULL,
        //                                                    `address_1` varchar(255) DEFAULT NULL,
        //                                                    `address_2` varchar(255) DEFAULT NULL,
        //                                                    `city` varchar(150) DEFAULT NULL,
        //                                                    `state` varchar(150) DEFAULT NULL,
        //                                                    `zip` varchar(12) DEFAULT NULL,
        //                                                    `credit_amount` float(10,2) unsigned NOT NULL,
        //                                                    `bulk_discount` tinyint(5) unsigned NOT NULL,
        //                                                    `processed_by` int(11) unsigned NOT NULL,
        //                                                    `status` tinyint(4) unsigned NOT NULL,
        //                                                    `created` datetime NOT NULL,
        //                                                    `modified` datetime NOT NULL,
        //                                                    PRIMARY KEY (`id`)
        //                                                ) ENGINE=InnoDB",
        //    //Table No: 9                                                        
        //                            'credit_product_details_table' => "CREATE TABLE IF NOT EXISTS `$creditProductDetailTable` (
        //                                                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        //                                                    `kiosk_id` int(11) unsigned NOT NULL,
        //                                                    `product_id` int(11) unsigned NOT NULL,
        //                                                    `customer_id` int(11) unsigned NOT NULL,
        //                                                    `quantity` int(11) unsigned NOT NULL,
        //                                                    `sale_price` float(10,3) unsigned NOT NULL,
        //                                                    `credit_price` float(10,2) unsigned NOT NULL,
        //                                                    `discount` tinyint(5) unsigned NOT NULL,
        //                                                    `credit_by` int(11) unsigned NOT NULL,
        //                                                    `status` tinyint(4) unsigned NOT NULL DEFAULT '1',
        //                                                    `credit_status` tinyint(4) unsigned NOT NULL DEFAULT '1',
        //                                                    `credit_receipt_id` int(11) unsigned NOT NULL,
        //                                                    `remarks` varchar(255) NOT NULL,
        //                                                    `created` datetime NOT NULL,
        //                                                    `modified` datetime NOT NULL,
        //                                                    PRIMARY KEY (`id`)
        //                                                ) ENGINE=InnoDB",
        //    //Table No: 10                                            
        //                            'credit_payment_table' => "CREATE TABLE IF NOT EXISTS `$creditPaymentTable` (
        //                                                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        //                                                    `credit_receipt_id` int(11) unsigned NOT NULL,
        //                                                    `payment_method` varchar(20) NOT NULL,
        //                                                    `description` varchar(255) NOT NULL,
        //                                                    `amount` float(8,2) unsigned NOT NULL,
        //                                                    `payment_status` tinyint(4) unsigned NOT NULL,
        //                                                    `status` tinyint(4) unsigned DEFAULT NULL,
        //                                                    `created` datetime NOT NULL,
        //                                                    `modified` datetime NOT NULL,
        //                                                    PRIMARY KEY (`id`)
        //                                                ) ENGINE=InnoDB",
        //    //Table No: 11                                            
        //                            'daily_stock_table' => "CREATE TABLE IF NOT EXISTS `$dailyStockTable` (
        //                                                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        //                                                    `product_id` int(11) unsigned NOT NULL,
        //                                                    `cost_price` float(8,2) unsigned NOT NULL,
        //                                                    `selling_price` float(8,2) unsigned NOT NULL,
        //                                                    `quantity` int(11) unsigned NOT NULL,
        //                                                    `status` tinyint(4) unsigned NOT NULL,
        //                                                    `created` datetime NOT NULL,
        //                                                    `modified` datetime NOT NULL,
        //                                                    PRIMARY KEY (`id`)
        //                                                ) ENGINE=InnoDB"
        //],
    
];
?>
