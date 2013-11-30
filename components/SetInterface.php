<?php
/**
 * SetInterface.php class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

/**
 * Interface for sets
 *
 * @package  yii-utility
 */
interface SetInterface extends Countable, IteratorAggregate
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

    /**
     * @return array  list of keys in the set
     */
    public function keys();

    /**
     * @return SetInterface  the set formed by taking elements that are in either set
     */
    public function union($anotherSet);

    /**
     * @return SetInterface  the set formed by taking elements in each set that are not part of the other
     */
    public function difference($anotherSet);

    /**
     * @return SetInterface  the set formed by taking elements that are in each set
     */
    public function intersect($anotherSet);

    /**
     * @return boolean  whether the set contains a given value
     */
    public function contains($value);

    /**
     * Empties the set
     */
    public function clear();

    /**
     * @return boolean  whether the set is empty
     */
    public function isEmpty();

    /**
     * Executes a function on all members of the set
     */
    public function map(callable $callback);

    /**
     * Filters members of the set using a callback
     */
    public function filter(callable $filter);
}