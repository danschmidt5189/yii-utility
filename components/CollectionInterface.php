<?php
/**
 * CollectionInterface.php class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

/**
 * Interface for collections
 *
 * @package  yii-utility
 */
interface CollectionInterface
{
    /**
     * Returns the item stored at the key
     */
    public function lookup($key);

    /**
     * Adds a new key ->value pair
     */
    public function add($key, $value);

    /**
     * Replaces the item stored at the key with a new value
     */
    public function replace($key, $value);

    /**
     * Removes the item stored at the key
     */
    public function remove($key);
}