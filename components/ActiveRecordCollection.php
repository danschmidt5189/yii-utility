<?php
/**
 * ActiveRecordSet.php class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

/**
 * Represents a collection of ActiveRecord objects
 *
 * @property string $_index    the record property used for indexing
 * @property array  $_records  the records in the set
 *
 * @method boolean load()           loads attribute data into each record
 * @method boolean loadMultiple()   loads attribute data into records specified by a primary key
 * @method boolean save()           saves all records in the set
 * @method boolean delete()         deletes all records in the set
 * @method boolean validate()       validates all records in the set
 * @method array   getAttributes()  returns record attributes (arrays) indexed by the index property
 * @method array   getErrors()      returns record error messages (arrays) indexed by the index property
 * @method boolean hasErrors()      returns whether any record in the set is invalid
 *
 * @package  yii-utility
 */
class ActiveRecordSet extends CComponent implements SetInterface, Iterator, ArrayAccess, Countable
{
    /**
     * @var array  the records in the set
     */
    private $_records = array();

    /**
     * Returns attributes of each record in the set indexed by the record key
     *
     * @param array $attributes  attributes to retrieve. Use null to retrieve all attributes.
     * @return array  attribute arrays indexed by record key
     */
    public function getAttributes($attributes=null)
    {
        $data = array();
        $this->map(function ($model, $key) use (&$data, $attributes) {
            $data[$key] = $model->getAttributes($attributes);
        });
        return $data;
    }

    /**
     * Sets attributes to each record in the set
     *
     * @param array   $attributes  name =>value attribute pairs
     * @param boolean $safeOnly    whether to only set safe attributes
     * @return array  keys of the records that were modified. This will be empty if no records were modified.
     */
    public function setAttributes($attributes, $safeOnly=true)
    {
        $modified = array();
        $attributeNames = is_array($attributes) ? array_keys($attributes) : array();
        $this->map(function ($model, $key) use ($attributes, $safeOnly, $attributeNames, &$modified) {
            $oldAttributes = $model->getAttributes($attributeNames);
            $model->setAttributes($attributes, $safeOnly);
            if ($oldAttributes !== $model->getAttributes($attributeNames)) {
                $modified[] = $key;
            }
        });
        return $modified;
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
        return $this->_records[$key];
    }

    /**
     * Adds a new record to the set
     *
     * @param mixed         $key     the key at which to store the item
     * @param CActiveRecord $record  the record
     * @return void
     * @throws SetException  if a value is already stored at the key
     */
    public function add($key, CActiveRecord $record)
    {
        if ($this->contains($key)) {
            throw new SetException("Value at `$key` is already set.");
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
        unset($this->_records[$key]);
    }

    /**
     * Replaces a record in the set with a new value
     *
     * @param mixed $key  the key at which to replace the value
     * @param CActiveRecord $record  the record to add
     */
    public function replace($key, CActiveRecord $record)
    {
        $this->_records[$key] = $record;
    }

    /**
     * Returns whether there is a value stored at the given key
     *
     * @param mixed $key  the key
     * @return boolean  whether a value is stored at the key
     */
    public function contains($key)
    {
        return isset($this->_records[$key]);
    }

    /**
     * @return boolean  whether there are no records in the set
     */
    public function isEmpty()
    {
        return empty($this->_records);
    }

    /**
     * @return integer  the number of records in the set
     */
    public function count()
    {
        return count($this->_records);
    }

    /**
     * Executes a callback on each record in the set
     *
     * @param callable $callback  the callback function to invoke on each record in the set
     */
    public function map(callable $callback)
    {
        array_walk($callback, $this->_records);
    }

    /**
     * Returns the subset of records satisfying a given filter callback
     *
     * @param callable $filter  the filter callback function
     * @return ActiveRecordSet  the filtered set
     */
    public function filter(callable $filter)
    {
        return new ActiveRecordSet(array_filter($this->_records, $filter));
    }
}