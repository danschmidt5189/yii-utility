<?php
/**
 * MultiResult.php class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

/**
 * Helper class representing the result of a bulk action
 *
 * This is used by magic methods in the ActiveRecordSet class to summarize
 * the results returned by records in the set.
 *
 * @package yii-utility
 */
class MultiResult extends Set
{
    /**
     * @param boolean $strict  whether to use strict (===) truth checking
     * @return boolean  whether every result in the set is true
     */
    public function getAllTrue($strict=false)
    {
        $allTrue = true;
        $this->map(function ($value, $key) use ($strict, &$allTrue) {
            $allTrue = $allTrue && ($strict ? true === $value : $value);
        });
        return $allTrue;
    }

    public function getPartlyTrue($strict=false)
    {
        $partlyTrue = false;
        $this->map(function ($value, $key) use ($strict, &$partlyTrue) {
            $partlyTrue = $partlyTrue || ($strict ? true === $value : $value);
        });
        return $partlyTrue;
    }

    /**
     * @param boolean $strict  whether to use strict (===) false checking
     * @return boolean  whether every result in the set is false
     */
    public function getAllFalse($strict=false)
    {
        $allFalse = true;
        $this->map(function ($value, $key) use ($strict, &$allFalse) {
            $allFalse = $allFalse && ($strict ? false === $value : !$value);
        });
        return $allFalse;
    }

    /**
     *
     */
    public function getPartlyFalse($strict=false)
    {
        $partlyFalse = false;
        $this->map(function ($value, $key) use ($strict, &$partlyFalse) {
            $partlyFalse = $partlyFalse || ($strict ? false === $value : !$value);
        });
        return $partlyFalse;
    }
}