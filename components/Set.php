<?php
/**
 * Set.php class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

/**
 * Base class for sets
 *
 * @package  yii-utility
 */
class Set extends CComponent implements SetInterface, ArrayableInterface, ArrayAccess
{
    /**
     * @var array  data stored in the set
     */
    private $_data = array();

    /**
     * Constructs the set with initial data
     */
    public function __construct($data=null)
    {
        $this->mergeWith($data);
    }

    /**
     * @return datatype description
     */
    public function toArray()
    {
        return $_data;
    }

    /**
     * Merges this set with another set of data
     *
     * @param mixed $data  the data to merge with
     * @return this
     */
    public function mergeWith($data)
    {
        if (is_array($data) || $data instanceof Traversable) {
            foreach ($data as $key =>$value) {
                $this->replace($key, $value);
            }
        }
        return $this;
    }

    /**
     * Calculates the union of this set with another set
     *
     * Data in the second set takes precedence, so if different values are stored in the same key in both
     * sets, the value in the second set is added to the union.
     *
     * @param Set|array $set  the set to be merged into this set
     * @return Set  the merged set
     */
    public function union($set)
    {
        $className = get_class($this);
        $union = new $className();
        $union->mergeWith($this);
        $union->mergeWith($set);
        return $union;
    }

    /**
     * Computes the intersection of this set with another set
     *
     * Intersection is computed by comparing key values.
     *
     * @param mixed $set  the set to compare against. This can be another set, or an array of key =>value pairs.
     * @return set  the intersection between the two sets
     */
    public function intersect($set)
    {
        $className = get_class($this);
        $intersection = new $className();
        foreach ($set as $key =>$value) {
            if ($this->contains($key)) {
                $intersection->add($key, $value);
            }
        }
        return $intersection;
    }

    /**
     * Computes the difference between this set and another set
     *
     * @param mixed $set  the set to difference against
     * @return Set  the set containing elements in one set but not the other
     */
    public function difference($set)
    {
        $className = get_class($this);
        $difference = new $className();
        foreach ($set as $key =>$value) {
            if (!$this->contains($key)) {
                $difference->add($key, $value);
            }
        }
        foreach ($this as $key =>$value) {
            if (!$set->contains($key)) {
                $difference->add($key, $value);
            }
        }
        return $difference;
    }

    /**
     * Empties the set
     */
    public function clear()
    {
        $this->_data = array();
    }

    /**
     * Returns the record stored at the key
     *
     * @return CActiveRecord  the record stored at the key, or null if it is not set
     */
    public function lookup($key)
    {
        if (!$this->contains($key)) {
            return null;
        }
        return $this->_data[$key];
    }

    /**
     * Adds a new record to the set
     *
     * @param mixed         $key     the key at which to store the item
     * @param CActiveRecord $record  the record
     * @return void
     * @throws SetException  if a value is already stored at the key
     */
    public function add($key, $record)
    {
        if ($this->contains($key)) {
            throw new SetException("There is already a record stored at key `$key`");
        }
        $this->replace($key, $record);
    }

    /**
     * Removes a record from the set
     *
     * @param mixed $key  key of the item to be removed
     * @return void
     */
    public function remove($key)
    {
        unset($this->_data[$key]);
    }

    /**
     * Replaces a record in the set with a new value
     *
     * @param mixed $key  the key at which to replace the value
     * @param CActiveRecord $record  the record to add
     */
    public function replace($key, $record)
    {
        $this->_data[$key] = $record;
    }

    /**
     * Returns whether there is a value stored at the given key
     *
     * @param mixed $keys  array of keys, or another set
     * @return boolean  whether this set contains values at each of the keys
     */
    public function contains($keys)
    {
        $className = get_class($this);
        if ($keys instanceof $className) {
            $keys = array_keys($keys->toArray());
        }
        if (!is_array($keys)) {
            $keys = array($keys);
        }
        foreach (array_unique($keys) as $key) {
            if (!isset($this->_data[$key])) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return boolean  whether there are no records in the set
     */
    public function isEmpty()
    {
        return empty($this->_data);
    }

    /**
     * Executes a callback on each record in the set
     *
     * @param callable $callback  the callback function to invoke on each record in the set
     */
    public function map(callable $callback)
    {
        array_walk($this->_data, $callback);
    }

    /**
     * Returns the subset of records satisfying a given filter callback
     *
     * @param callable $filter  the filter callback function
     * @return Set  the filtered set
     */
    public function filter(callable $filter)
    {
        $className = get_class($this);
        return new $className(array_filter($this->_data, $filter));
    }

    /**
     * @return array  data keys
     */
    public function keys()
    {
        return array_keys($this->_data);
    }

    public function count()
    {
        return count($this->_data);
    }
    public function getIterator()
    {
        return new ArrayIterator($this->_data);
    }
    public function offsetGet($key)
    {
        return $this->lookup($key);
    }
    public function offsetSet($key, $value)
    {
        $this->replace($key, $value);
    }
    public function offsetUnset($key)
    {
        $this->remove($key);
    }
    public function offsetExists($key)
    {
        return $this->contains($key);
    }
}

class SetException extends CException {}