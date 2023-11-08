<?php
namespace Drupal\wso2_with_jwt;

/**
 * Class SpidUserinfoAttributes.
 *
 */
class SpidUserinfoAttributes
{
    public static $Attributes = [
        'name' => [
            'scope' => 'profile',
            'title' => 'Nome',
            'type' => 'string',
            'description' => 'Stringa composta da una sequenza di una o piu sottostringhe non vuote con carattere iniziale in maiuscolo intervallate da uno (solo) spazio'
        ],
        'surname' => [
            'scope' => 'profile',
            'title' => 'Cognome',
            'type' => 'string',
            'description' => 'Stringa composta da una sequenza di una o piu sottostringhe non vuote con carattere iniziale in maiuscolo intervallate da uno (solo) spazio'
        ],
        'placeOfBirth' => [
            'scope' => 'profile',
            'title' => 'Luogo di nascita',
            'type' => 'string',
            'description' => 'Stringa corrispondente al codice catastale (Codice Belfiore) del Comune o della nazione estera di nascita.'
        ],
        'countryOfBirth' => [
            'scope' => 'profile',
            'title' => 'Provincia di nascita',
            'type' => 'string',
            'description' => 'Stringa corrispondente alla sigla della provincia di nascita.'
        ],
        'dateOfBirth' => [
            'scope' => 'profile',
            'title' => 'Nome',
            'type' => 'date',
            'description' => 'Secondo specifica xs:date nel formato YYYY-MM-DD'
        ],
        'mobilePhone' => [
            'scope' => 'profile',
            'title' => 'Nome',
            'type' => 'string',
            'description' => 'Stringa numerica senza spazi intermedi'
        ],
        'email' => [
            'scope' => 'profile',
            'title' => 'Nome',
            'type' => 'string',
            'description' => 'Formato standard indirizzo di posta elettronica'
        ],
        'domicileStreetAddress' => [
            'scope' => 'profile',
            'title' => 'Nome',
            'type' => 'string',
            'description' => 'via, viale, piazza'
        ],
        'domicilePostalCode' => [
            'scope' => 'profile',
            'title' => 'Nome',
            'type' => 'string',
            'description' => 'CAP'
        ],
        'domicileMunicipality' => [
            'scope' => 'profile',
            'title' => 'Nome',
            'type' => 'string',
            'description' => 'Comune'
        ],
        'domicileProvince' => [
            'scope' => 'profile',
            'title' => 'Nome',
            'type' => 'string',
            'description' => ''
        ],
        'address' => [
            'scope' => 'profile',
            'title' => 'Nome',
            'type' => 'string',
            'description' => 'Deprecated: use domicile fields instead! Stringa composta da una sequenza di sottostringhe non vuote intervallate da uno (solo) spazio rappresentanti:\n• Tipologia (via, viale, piazza. . . );\n• Indirizzo;\n• Nr. civico;\n• CAP;\n• Luogo;\n• Provincia.'
        ],
        'domicileNation' => [
            'scope' => 'profile',
            'title' => 'Nome',
            'type' => 'string',
            'description' => ''
        ],
        'expirationDate' => [
            'scope' => 'profile',
            'title' => 'Nome',
            'type' => 'date',
            'description' => 'Secondo specifica xs:date'
        ],
        'digitalAddress' => [
            'scope' => 'profile',
            'title' => 'Nome',
            'type' => 'string',
            'description' => 'Indirizzo casella PEC'
        ],
    ];

    public function __construct(){}
}
