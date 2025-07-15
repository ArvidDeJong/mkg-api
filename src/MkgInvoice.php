<?php

namespace Darvis\Mkg;

/**
 * MKG CRM Factuur Integratie
 *
 * Deze klasse verzorgt de specifieke functionaliteit voor facturen in het MKG CRM systeem.
 *
 * @package Darvis\Mkg
 * @author Arvid de Jong <info@arvid.nl>
 */
class MkgInvoice extends Mkg
{
    /**
     * Beschrijving van de velden die worden opgevraagd bij de MKG API
     * Bevat voor elk veld een description en format property
     *
     * @var array
     * opde = Open Posten Debiteuren
     */
    public $fieldDescriptions = [
        'opde_boekstuk' => [
            'description' => 'Boekstuk/factuurnummer',
            'format' => 'varchar'
        ],
        'opde_afgewerkt' => [
            'description' => 'Of de factuur is afgewerkt/betaald',
            'format' => 'ja/nee'
        ],
        'opde_bdr_open_ov' => [
            'description' => 'Openstaand bedrag',
            'format' => '>>>,>>>,>>9.99-'
        ],
        'opde_bet_wijze' => [
            'description' => 'Betaalwijze',
            'format' => 'int'
        ],
        'opde_dat_factuur' => [
            'description' => 'Factuurdatum',
            'format' => '99-99-9999'
        ],
        'opde_dat_verval' => [
            'description' => 'Vervaldatum',
            'format' => '99-99-9999'
        ],
        'opde_dat_ingave' => [
            'description' => 'Datum van ingave',
            'format' => '99-99-9999'
        ],
        'opde_dat_historisch' => [
            'description' => 'Datum historie afgehandeld/betaald',
            'format' => '99-99-9999'
        ],
    ];

    /**
     * Lijst van velden die worden opgevraagd bij de MKG API
     * Wordt automatisch gegenereerd uit de sleutels van fieldDescriptions
     *
     * @var string
     */
    public $fieldList;

    /**
     * Initialiseer de MKG Invoice client
     */
    public function __construct()
    {
        parent::__construct();
        $this->fieldList = implode(',', array_keys($this->fieldDescriptions));
    }
    
    /**
     * Geeft een eenvoudig overzicht van de velden en hun omschrijvingen
     *
     * @return array Associatieve array van veldnamen en omschrijvingen
     */
    public function getSimpleFieldDescriptions(): array
    {
        $result = [];
        foreach ($this->fieldDescriptions as $field => $details) {
            $result[$field] = $details['description'];
        }
        return $result;
    }
    
    /**
     * Geeft een overzicht van de velden en hun formaten
     *
     * @return array Associatieve array van veldnamen en formaten
     */
    public function getFieldFormats(): array
    {
        $result = [];
        foreach ($this->fieldDescriptions as $field => $details) {
            $result[$field] = $details['format'];
        }
        return $result;
    }

    /**
     * Haal factuurgegevens op uit het MKG systeem
     *
     * @param string $invoice_nr Het factuurnummer
     * @return array|null De factuurgegevens of null bij een fout
     */
    public function get($invoice_nr)
    {
        return parent::get("/opde/2+{$invoice_nr}?FieldList={$this->fieldList}");
    }
}
