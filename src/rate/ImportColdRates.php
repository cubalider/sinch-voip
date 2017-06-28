<?php

namespace Yosmy\Voip\Sinch;

use PhpOffice\PhpSpreadsheet;
use Yosmy\Country;
use Yosmy\Voip;

/**
 * @di\service({
 *     tags: ['yosmy.voip.import_rates']
 * })
 */
class ImportColdRates
{
    /**
     * @var Country\ResolvePhone
     */
    private $resolvePhone;

    /**
     * @var Voip\ColdRate\SelectCollection
     */
    private $selectCollection;

    /**
     * @param Country\ResolvePhone           $resolvePhone
     * @param Voip\ColdRate\SelectCollection $selectCollection
     */
    public function __construct(
        Country\ResolvePhone $resolvePhone,
        Voip\ColdRate\SelectCollection $selectCollection
    ) {
        $this->resolvePhone = $resolvePhone;
        $this->selectCollection = $selectCollection;
    }

    /**
     */
    public function import()
    {
        $excel = sprintf('%s/excel.xls', sys_get_temp_dir());

        $worksheet = $this->prepareExcel($excel);

        $rates = [];
        foreach ($worksheet->getRowIterator() as $i => $row) {
            if ($i <= 10) {
                continue;
            }

            /** @var PhpSpreadsheet\Worksheet\RowCellIterator $rowIterator */
            $rowIterator = $row->getCellIterator();

            try {
                $country = (string) $rowIterator->seek('A')->current()->getValue();
            } catch (PhpSpreadsheet\Exception $e) {
                throw new \LogicException();
            }

            try {
                $country = $this->resolvePhone->resolve($country);
            } catch (Country\NotFoundException $e) {
                throw new \LogicException(sprintf('Country "%s" not found.', $country));
            }

            try {
                $rate = (float) $rowIterator->seek('D')->current()->getValue();
            } catch (PhpSpreadsheet\Exception $e) {
                throw new \LogicException();
            }

            // + inbound call
            $rate += 0.004;

            $rates[] = new Voip\ColdRate(
                uniqid(),
                'sinch',
                $country,
                $rate
            );
        }

        unlink($excel);

        foreach ($rates as $rate) {
            $this->selectCollection->select()->insertOne($rate);
        }
    }

    /**
     * @param string $excel
     *
     * @return PhpSpreadsheet\Worksheet\Worksheet
     *
     * @throws \LogicException
     */
    private function prepareExcel($excel)
    {
        file_put_contents(
            $excel,
            fopen('https://www.sinch.com/voice-price-list', 'r')
        );

        try {
            $spreadsheet = PhpSpreadsheet\IOFactory::load($excel);
        } catch (PhpSpreadsheet\Exception $e) {
            throw new \LogicException();
        }

        $worksheet = $spreadsheet->getSheetByName('Voice');

        return $worksheet;
    }
}