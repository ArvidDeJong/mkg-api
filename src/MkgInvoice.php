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
     *
     * @var array
     * opde = Open Posten Debiteuren
     */
    public $fieldDescriptions = [
        'opde_boekstuk' => 'Boekstuk/factuurnummer',
        'opde_afgewerkt' => 'Of de factuur is afgewerkt/betaald',
        'opde_bdr_open_ov' => 'Openstaand bedrag',
        'opde_bet_wijze' => 'Betaalwijze',
        'opde_dat_factuur' => 'Factuurdatum',
        'opde_dat_verval' => 'Vervaldatum',
        'opde_dat_ingave' => 'Datum van ingave',
        'opde_dat_historisch' => 'Datum historie afgehandeld/betaald',
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
