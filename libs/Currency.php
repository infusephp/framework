<?php

namespace infuse;

class Currency
{
	/**
	 * Generates a select box for the currencies
	 *
	 * @return string html
	 */
	static function options( $selectedCurrency )
	{
		$return = '<select name="currency">' . "\n";
		foreach (self::$currencies as $code => $currency) {
			$codeLower = strtolower($code);
			$selected = ($selectedCurrency == $codeLower) ? 'selected="selected"' : '';
			$return .= '<option value="' . $codeLower . '" ' . $selected . '>' . $code . ' - ' . $currency['name'] . '</option>' . "\n";
		}
		$return .= '</select>';
		return $return;
	}

	/**
	 * @staticvar $currencies
	 *
	 * List of currency codes, names, and symbols
	 **/
	static $currencies = array(
		'AED' => array(
			'name' => 'United Arab Emirates Dirham',
			'symbol' => 'د.إ'
		),
		'AFN' => array(
			'name' => 'Afghanistan Afghani'
		),
		'ALL' => array(
			'name' => 'Albania Lek'
		),
		'AMD' => array(
			'name' => 'Armenia Dram'
		),
		'ANG' => array(
			'name' => 'Netherlands Antilles Guilder'
		),
		'AOA' => array(
			'name' => 'Angola Kwanza'
		),
		'ARS' => array(
			'name' => 'Argentina Peso'
		),
		'AUD' => array(
			'name' => 'Australia Dollar',
			'symbol' => '$'
		),
		'AWG' => array(
			'name' => 'Aruba Guilder'
		),
		'AZN' => array(
			'name' => 'Azerbaijan New Manat'
		),
		'BAM' => array(
			'name' => 'Bosnia and Herzegovina Convertible Marka'
		),
		'BBD' => array(
			'name' => 'Barbados Dollar'
		),
		'BDT' => array(
			'name' => 'Bangladesh Taka'
		),
		'BGN' => array(
			'name' => 'Bulgaria Lev'
		),
		'BHD' => array(
			'name' => 'Bahrain Dinar'
		),
		'BIF' => array(
			'name' => 'Burundi Franc'
		),
		'BMD' => array(
			'name' => 'Bermuda Dollar'
		),
		'BND' => array(
			'name' => 'Brunei Darussalam Dollar'
		),
		'BOB' => array(
			'name' => 'Bolivia Boliviano'
		),
		'BRL' => array(
			'name' => 'Brazil Real'
		),
		'BSD' => array(
			'name' => 'Bahamas Dollar'
		),
		'BTN' => array(
			'name' => 'Bhutan Ngultrum'
		),
		'BWP' => array(
			'name' => 'Botswana Pula'
		),
		'BYR' => array(
			'name' => 'Belarus Ruble'
		),
		'BZD' => array(
			'name' => 'Belize Dollar'
		),
		'CAD' => array(
			'name' => 'Canada Dollar',
			'symbol' => '$'
		),
		'CDF' => array(
			'name' => 'Congo/Kinshasa Franc'
		),
		'CHF' => array(
			'name' => 'Switzerland Franc',
			'symbol' => 'CHF'
		),
		'CLP' => array(
			'name' => 'Chile Peso'
		),
		'CNY' => array(
			'name' => 'China Yuan Renminbi',
			'symbol' => '¥'
		),
		'COP' => array(
			'name' => 'Colombia Peso'
		),
		'CRC' => array(
			'name' => 'Costa Rica Colon'
		),
		'CUC' => array(
			'name' => 'Cuba Convertible Peso'
		),
		'CUP' => array(
			'name' => 'Cuba Peso'
		),
		'CVE' => array(
			'name' => 'Cape Verde Escudo'
		),
		'CZK' => array(
			'name' => 'Czech Republic Koruna'
		),
		'DJF' => array(
			'name' => 'Djibouti Franc'
		),
		'DKK' => array(
			'name' => 'Denmark Krone'
		),
		'DOP' => array(
			'name' => 'Dominican Republic Peso'
		),
		'DZD' => array(
			'name' => 'Algeria Dinar'
		),
		'EGP' => array(
			'name' => 'Egypt Pound'
		),
		'ERN' => array(
			'name' => 'Eritrea Nakfa'
		),
		'ETB' => array(
			'name' => 'Ethiopia Birr'
		),
		'EUR' => array(
			'name' => 'Euro Member Countries',
			'symbol' => '€'
		),
		'FJD' => array(
			'name' => 'Fiji Dollar'
		),
		'FKP' => array(
			'name' => 'Falkland Islands (Malvinas) Pound'
		),
		'GBP' => array(
			'name' => 'United Kingdom Pound',
			'symbol' => '£'
		),
		'GEL' => array(
			'name' => 'Georgia Lari'
		),
		'GGP' => array(
			'name' => 'Guernsey Pound'
		),
		'GHS' => array(
			'name' => 'Ghana Cedi'
		),
		'GIP' => array(
			'name' => 'Gibraltar Pound'
		),
		'GMD' => array(
			'name' => 'Gambia Dalasi'
		),
		'GNF' => array(
			'name' => 'Guinea Franc'
		),
		'GTQ' => array(
			'name' => 'Guatemala Quetzal'
		),
		'GYD' => array(
			'name' => 'Guyana Dollar'
		),
		'HKD' => array(
			'name' => 'Hong Kong Dollar',
			'symbol' => 'HK$'
		),
		'HNL' => array(
			'name' => 'Honduras Lempira'
		),
		'HRK' => array(
			'name' => 'Croatia Kuna',
			'symbol' => 'kn'
		),
		'HTG' => array(
			'name' => 'Haiti Gourde'
		),
		'HUF' => array(
			'name' => 'Hungary Forint'
		),
		'IDR' => array(
			'name' => 'Indonesia Rupiah'
		),
		'ILS' => array(
			'name' => 'Israel Shekel'
		),
		'IMP' => array(
			'name' => 'Isle of Man Pound'
		),
		'INR' => array(
			'name' => 'India Rupee',
			'symbol' => '₹'
		),
		'IQD' => array(
			'name' => 'Iraq Dinar'
		),
		'IRR' => array(
			'name' => 'Iran Rial'
		),
		'ISK' => array(
			'name' => 'Iceland Krona'
		),
		'JEP' => array(
			'name' => 'Jersey Pound'
		),
		'JMD' => array(
			'name' => 'Jamaica Dollar'
		),
		'JOD' => array(
			'name' => 'Jordan Dinar'
		),
		'JPY' => array(
			'name' => 'Japan Yen',
			'symbol' => '¥'
		),
		'KES' => array(
			'name' => 'Kenya Shilling'
		),
		'KGS' => array(
			'name' => 'Kyrgyzstan Som'
		),
		'KHR' => array(
			'name' => 'Cambodia Riel'
		),
		'KMF' => array(
			'name' => 'Comoros Franc'
		),
		'KPW' => array(
			'name' => 'Korea (North) Won'
		),
		'KRW' => array(
			'name' => 'Korea (South) Won'
		),
		'KWD' => array(
			'name' => 'Kuwait Dinar'
		),
		'KYD' => array(
			'name' => 'Cayman Islands Dollar'
		),
		'KZT' => array(
			'name' => 'Kazakhstan Tenge'
		),
		'LAK' => array(
			'name' => 'Laos Kip'
		),
		'LBP' => array(
			'name' => 'Lebanon Pound'
		),
		'LKR' => array(
			'name' => 'Sri Lanka Rupee'
		),
		'LRD' => array(
			'name' => 'Liberia Dollar'
		),
		'LSL' => array(
			'name' => 'Lesotho Loti'
		),
		'LTL' => array(
			'name' => 'Lithuania Litas'
		),
		'LVL' => array(
			'name' => 'Latvia Lat'
		),
		'LYD' => array(
			'name' => 'Libya Dinar'
		),
		'MAD' => array(
			'name' => 'Morocco Dirham'
		),
		'MDL' => array(
			'name' => 'Moldova Leu'
		),
		'MGA' => array(
			'name' => 'Madagascar Ariary'
		),
		'MKD' => array(
			'name' => 'Macedonia Denar'
		),
		'MMK' => array(
			'name' => 'Myanmar (Burma) Kyat'
		),
		'MNT' => array(
			'name' => 'Mongolia Tughrik'
		),
		'MOP' => array(
			'name' => 'Macau Pataca'
		),
		'MRO' => array(
			'name' => 'Mauritania Ouguiya'
		),
		'MUR' => array(
			'name' => 'Mauritius Rupee'
		),
		'MVR' => array(
			'name' => 'Maldives (Maldive Islands) Rufiyaa'
		),
		'MWK' => array(
			'name' => 'Malawi Kwacha'
		),
		'MXN' => array(
			'name' => 'Mexico Peso'
		),
		'MYR' => array(
			'name' => 'Malaysia Ringgit',
			'symbol' => 'RM'
		),
		'MZN' => array(
			'name' => 'Mozambique Metical'
		),
		'NAD' => array(
			'name' => 'Namibia Dollar'
		),
		'NGN' => array(
			'name' => 'Nigeria Naira'
		),
		'NIO' => array(
			'name' => 'Nicaragua Cordoba'
		),
		'NOK' => array(
			'name' => 'Norway Krone'
		),
		'NPR' => array(
			'name' => 'Nepal Rupee'
		),
		'NZD' => array(
			'name' => 'New Zealand Dollar',
			'symbol' => '$'
		),
		'OMR' => array(
			'name' => 'Oman Rial'
		),
		'PAB' => array(
			'name' => 'Panama Balboa'
		),
		'PEN' => array(
			'name' => 'Peru Nuevo Sol'
		),
		'PGK' => array(
			'name' => 'Papua New Guinea Kina'
		),
		'PHP' => array(
			'name' => 'Philippines Peso',
			'symbol' => '₱'
		),
		'PKR' => array(
			'name' => 'Pakistan Rupee'
		),
		'PLN' => array(
			'name' => 'Poland Zloty'
		),
		'PYG' => array(
			'name' => 'Paraguay Guarani'
		),
		'QAR' => array(
			'name' => 'Qatar Riyal'
		),
		'RON' => array(
			'name' => 'Romania New Leu'
		),
		'RSD' => array(
			'name' => 'Serbia Dinar'
		),
		'RUB' => array(
			'name' => 'Russia Ruble'
		),
		'RWF' => array(
			'name' => 'Rwanda Franc'
		),
		'SAR' => array(
			'name' => 'Saudi Arabia Riyal',
			'symbol' => '﷼'
		),
		'SBD' => array(
			'name' => 'Solomon Islands Dollar'
		),
		'SCR' => array(
			'name' => 'Seychelles Rupee'
		),
		'SDG' => array(
			'name' => 'Sudan Pound'
		),
		'SEK' => array(
			'name' => 'Sweden Krona'
		),
		'SGD' => array(
			'name' => 'Singapore Dollar',
			'symbol' => '$'
		),
		'SHP' => array(
			'name' => 'Saint Helena Pound'
		),
		'SLL' => array(
			'name' => 'Sierra Leone Leone'
		),
		'SOS' => array(
			'name' => 'Somalia Shilling'
		),
		'SPL*' => array(
			'name' => 'Seborga Luigino'
		),
		'SRD' => array(
			'name' => 'Suriname Dollar'
		),
		'STD' => array(
			'name' => '	São Tomé and Príncipe Dobra'
		),
		'SVC' => array(
			'name' => 'El Salvador Colon'
		),
		'SYP' => array(
			'name' => 'Syria Pound'
		),
		'SZL' => array(
			'name' => 'Swaziland Lilangeni'
		),
		'THB' => array(
			'name' => 'Thailand Baht',
			'symbol' => '฿'
		),
		'TJS' => array(
			'name' => 'Tajikistan Somoni'
		),
		'TMT' => array(
			'name' => 'Turkmenistan Manat'
		),
		'TND' => array(
			'name' => 'Tunisia Dinar'
		),
		'TOP' => array(
			'name' => 'Tonga Pa\'anga'
		),
		'TRY' => array(
			'name' => 'Turkey Lira',
			'symbol' => 'TRY'
		),
		'TTD' => array(
			'name' => 'Trinidad and Tobago Dollar'
		),
		'TVD' => array(
			'name' => 'Tuvalu Dollar'
		),
		'TWD' => array(
			'name' => 'Taiwan New Dollar'
		),
		'TZS' => array(
			'name' => 'Tanzania Shilling'
		),
		'UAH' => array(
			'name' => 'Ukraine Hryvna'
		),
		'UGX' => array(
			'name' => 'Uganda Shilling'
		),
		'USD' => array(
			'name' => 'United States Dollar',
			'symbol' => '$'
		),
		'UYU' => array(
			'name' => 'Uruguay Peso'
		),
		'UZS' => array(
			'name' => 'Uzbekistan Som'
		),
		'VEF' => array(
			'name' => 'Venezuela Bolivar'
		),
		'VND' => array(
			'name' => 'Viet Nam Dong'
		),
		'VUV' => array(
			'name' => 'Vanuatu Vatu'
		),
		'WST' => array(
			'name' => 'Samoa Tala'
		),
		'XAF' => array(
			'name' => 'CommunautÃ© FinanciÃ¨re Africaine (BEAC) CFA Franc BEAC'
		),
		'XCD' => array(
			'name' => 'East Caribbean Dollar'
		),
		'XDR' => array(
			'name' => 'International Monetary Fund (IMF) Special Drawing Rights'
		),
		'XOF' => array(
			'name' => 'Communauté Financière Africaine (BCEAO) Franc'
		),
		'XPF' => array(
			'name' => 'Comptoirs Français du Pacifique (CFP) Franc'
		),
		'YER' => array(
			'name' => 'Yemen Rial'
		),
		'ZAR' => array(
			'name' => 'South Africa Rand',
			'symbol' => 'R'
		),
		'ZMW' => array(
			'name' => 'Zambia Kwacha'
		),
		'ZWD' => array(
			'name' => 'Zimbabwe Dollar'
		) );
}