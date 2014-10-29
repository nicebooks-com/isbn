<?php

namespace Nicebooks\Isbn;

/**
 * Configuration for ISBN classes.
 */
class IsbnConfiguration
{
    /**
     * @var boolean
     */
    private $cleanupBeforeValidate = true;

    /**
     * @var boolean
     */
    private $validateCheckDigit = true;

    /**
     * Gets or sets whether to clean up the ISBN number before validation of the format.
     *
     * Cleanup involves removing any non-alphanumeric character from the string.
     *
     * @param boolean|null $value
     *
     * @return boolean The previous value of the setting.
     */
    public function cleanupBeforeValidate($value = null)
    {
        $previousValue = $this->cleanupBeforeValidate;

        if ($value !== null) {
            $this->cleanupBeforeValidate = (bool) $value;
        }

        return $previousValue;
    }

    /**
     * Gets or sets whether to clean up the ISBN number before validation of the format.
     *
     * Cleanup involves removing any non-alphanumeric character from the string.
     *
     * @param boolean|null $value
     *
     * @return boolean The previous value of the setting.
     */
    public function validateCheckDigit($value = null)
    {
        $previousValue = $this->validateCheckDigit;

        if ($value !== null) {
            $this->validateCheckDigit = (bool) $value;
        }

        return $previousValue;
    }
}
