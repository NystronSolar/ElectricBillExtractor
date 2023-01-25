<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Smalot\PdfParser\Parser;

abstract class CustomTestCase extends TestCase
{
    /**
     * Check if a File Exists
     * @param string $file The File Path
     * @return bool
     */
    private function checkFile(string $file): bool
    {
        return file_exists($file);
    }

    /**
     * Check if a PDF File is Valid
     * @param string $pdf The PDF Path
     * @return bool
     */
    protected function checkPDF(string $pdf): bool
    {
        $exists = $this->checkFile($pdf);
        if (!$exists) {
            return false;
        }

        $parser = new Parser();
        try {
            $parser->parseFile($pdf);
        } catch (\Exception) {
            return false;
        }

        return true;
    }

    /**
     * Check if a JSON File is Valid
     * @param string $json The JSON Path
     * @return bool
     */
    protected function checkJSON(string $json): bool
    {
        $exists = $this->checkFile($json);
        if (!$exists) {
            return false;
        }

        $fileContent = file_get_contents($json);
        $valid = !is_null(json_decode($fileContent));
        if (!$valid) {
            return false;
        }

        return true;
    }
}