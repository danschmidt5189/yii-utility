<?php
/**
 * ActiveRecordSet.php class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

/**
 * Represents a set of ActiveRecord objects
 *
 * @package  yii-utility
 */
class ActiveRecordSet extends Set
{
    /**
     * @var integer  counter that keeps track of how many new records were added to the class using [[populate()]]
     *               this is reset with every call to clear().
     */
    protected $population = 0;

    /**
     * @var string  name of the ActiveRecord class contained in this set
     */
    private $_recordClassName;

    /**
     * Overrides the parent implementation to inject the AR class name
     *
     * The following are valid ways of instantiating the set:
     * 1. __construct($className)
     * 2. __construct($className, $records)
     * 3. __construct(array $records)
     * 4. __construct($record)
     *
     * If using methods (3) or (4), the className will be inferred from the class of the
     * first record passed to the constructor.
     */
    public function __construct()
    {
        $args = func_get_args();
        $numArgs = func_num_args();
        if (1 === $numArgs) {
            $records = $args[0];
            if (!is_array($records)) {
                $records = array($records);
            }
            $className = get_class(current($records));
        } else if (2 === $numArgs) {
            $records = $args[1];
            $className = $args[0];
        } else {
            throw new InvalidArgumentException(sprintf(
                'Invalid arguments to %1$s::construct()'
            ), __CLASS__);
        }
        $this->_recordClassName = $className;
        parent::__construct($records);
    }

    /**
     * This method is overridden so that attributes of records in the set can be access like properties
     *
     * Returned properties are contained in a MultiResult object indexed by the record key.
     */
    public function __get($property)
    {
        try {
            return parent::__get($property);
        } catch (Exception $e) {
            $result = new MultiResult();
            $this->map(function ($record, $key) use ($property, $result) {
                if (!isset($record->{$property})) {
                    throw new SetException(sprintf(
                        '%1$s, its behaviors, and its records do not have a property named "%2$s".',
                        __CLASS__,
                        $property
                    ));
                }
                $result->add($key, $record->{$property});
            });
            return $result;
        }
    }

    /**
     * This method is overridden so that attributes of records in the set can be accessed like properties
     *
     * Return values are contained in a MultiResult object indexed by the record key.
     */
    public function __set($property, $value)
    {
        try {
            parent::__set($property, $value);
        } catch (CException $e) {
            try {
                $result = new MultiResult();
                $this->map(function ($record, $key) use ($property, $value, $result) {
                    $result->add($key, ($record->{$property} = $value));
                });
                return $result;
            } catch (CException $e) {
                throw new SetException(sprintf(
                    '%1$s, its behaviors, and its records do not have a property named "%2$s".',
                    __CLASS__,
                    $method
                ), 0, $e);
            }
        }
    }

    /**
     * Overrides the parent implementation to call methods on every record in the set
     *
     * Returned values are contained in a MultiResult object indexed by the record key. You
     * can call allTrue() and allFalse() on the result to determine if, for example, all records
     * are valid.
     */
    public function __call($method, $arguments)
    {
        try {
            return parent::__call($method, $arguments);
        } catch (CException $e) {
            $result = new MultiResult();
            $this->map(function ($record, $key) use ($method, $arguments, $result) {
                if (!method_exists($record, $method)) {
                    throw new SetException(sprintf(
                        '%1$s, its behaviors, and its records do not have a method or closure named "%2$s".',
                        __CLASS__,
                        $method
                    ));
                }
                $result->add($key, call_user_func_array(array($record, $method), $arguments));
            });
            return $result;
        }
    }

    /**
     * Populates the set with new records
     *
     * @param string  $className  ActiveRecord class name
     * @param integer $number     # of records to create
     * @param string  $scenario   scenario in which to instantiate the records
     */
    public function populate($className, $number, $scenario='insert')
    {
        for ($i=0; $i<(integer)$number; $i++) {
            $this->replace('new_'.$this->population++, Yii::createComponent($className, $scenario));
        }
    }

    /**
     * Overrides setAttributes() to use the [[load()]] method
     */
    public function setAttributes($attributes, $safeOnly)
    {
        return $this->load($attributes, $safeOnly);
    }

    /**
     * Loads data into each record in the set
     *
     * @param array   $attributes  data to load into each record
     * @param boolean $safeOnly    whether to only set safe attributes
     * @return boolean  whether any data was loaded
     */
    public function load($attributes, $safeOnly=true)
    {
        $loaded = false;
        if (is_array($indexedAttributes)) {
            foreach ($this as $key =>$record) {
                $oldAttributes = $record->getAttributes();
                $record->setAttributes($indexedAttributes[$key], $safeOnly);
                $loaded = $loaded || ($oldAttributes !== $record->getAttributes());
            };
        }
        return $loaded;
    }

    /**
     * Loads data into specific records in the set
     *
     * To load the same data into each record in the set, use $set->attributes = $data.
     *
     * @param array   $indexedAttributes  arrays of record attributes indexed by record key
     * @param boolean $safeOnly           whether to only set safe attributes
     * @return boolean  whether any data was loaded
     */
    public function loadByKey($indexedAttributes, $safeOnly=true)
    {
        $loaded = false;
        if (is_array($indexedAttributes)) {
            foreach ($this as $key =>$record) {
                if (isset($indexedAttributes[$key])) {
                    $oldAttributes = $record->getAttributes();
                    $record->setAttributes($indexedAttributes[$key], $safeOnly);
                    $loaded = $loaded || ($oldAttributes !== $record->getAttributes());
                }
            };
        }
        return $loaded;
    }

    /**
     * Returns a new set made by reindexing this set using a model attribute
     *
     * @param string  $attribute  the attribute used for indexing. Defaults to the primaryKey.
     * @param boolean $replace    whether to use 'replace' or 'add' when inserting records into the new set
     * @return ActiveRecordSet  a new set indexed by the attribute
     */
    public function reindex($attribute='primaryKey', $replace=false)
    {
        $className = get_class($this);
        $set = new $className($this->getRecordClassName());
        foreach ($this as $record) {
            $newKey = isset($record->{$attribute}) && !empty($record->{$attribute}) ? $record->{$attribute} : uniqid();
            $newKey = is_array($newKey) ? implode('_', $newKey) : $newKey;
            if ($replace) {
                $set->replace($newKey, $record);
            } else {
                $set->add($newKey, $record);
            }
        }
        return $set;
    }

    /**
     * Overrides the parent implementation to reset the [[population]] counter
     */
    public function clear()
    {
        $this->population = 0;
        parent::clear();
    }

    /**
     * Overrides the parent implementation to ensure records are all of the same class
     */
    public function replace($key, $record)
    {
        if (!$record instanceof $this->_recordClassName) {
            throw new SetException(sprintf(
                'Record must be an instance of %1$s for inclusion in this instance of %2$s'
            ), $this->_recordClassName, __CLASS__);
        }
        return parent::replace($key, $record);
    }

    /**
     * @return string  the [[_recordClassName]] property
     */
    public function getRecordClassName()
    {
        return $this->_recordClassName;
    }
}

/**
 * Helper class representing the result of a bulk action
 *
 * This is used by magic methods in the ActiveRecordSet class to summarize
 * the results returned by records in the set.
 */
class MultiResult extends Set
{
    /**
     * @param boolean $strict  whether to use strict (===) truth checking
     * @return boolean  whether every result in the set is true
     */
    public function allTrue($strict=false)
    {
        $allTrue = true;
        $this->map(function ($value, $key) use ($strict, &$allTrue) {
            $allTrue = $allTrue && ($strict ? true === $value : $value);
        });
        return $allTrue;
    }

    /**
     * @param boolean $strict  whether to use strict (===) false checking
     * @return boolean  whether every result in the set is false
     */
    public function allFalse($strict=false)
    {
        $allFalse = true;
        $this->map(function ($value, $key) use ($strict, &$allFalse) {
            $allFalse = $allFalse && ($strict ? false === $value : !$value);
        });
        return $allFalse;
    }
}