<?php
/**
 * ArrayableInterface.php class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

/**
 * Interface for objects that are convertable to arrays
 *
 * @package  yii-utility
 */
interface ArrayableInterface
{
    /**
     * @return array  returns the object as an array
     */
    public function toArray();
}