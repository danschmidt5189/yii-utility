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
class ActiveRecordSet extends Set implements ActiveRecordInterface
{
    /**
     * @var integer  counter that keeps track of how many new records were added to the class using [[populate()]]
     */
    protected $population = 0;

    /**
     * This method is overridden so that attributes of records in the set can be accessed like properties
     */
    public function __set($property, $value)
    {
        if (false === $this->loadEach(array($property =>$value), false)) {
            parent::__set($property, $value);
        }
    }

    /**
     * This method is overridden so that attributes of records in the set can be access like properties
     *
     * The returned properties are in an array indexed by key and then property name.
     */
    public function __get($property)
    {
        try {
            return parent::__get($property);
        } catch (Exception $e) {
            $attributes = $this->getAttribute($property);
            if (!empty($attributes)) {
                return $attributes;
            }
            throw $e;
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
        for ($i=0; $i<$number; $i++) {
            $this->replace('new_'.$this->population++, Yii::createComponent($className, $scenario));
        }
    }

    /**
     * Returns a specific attribute from each record in the set
     *
     * @param string $attribute  attribute name
     * @return array  attribute values indexed by record key
     */
    public function getAttribute($attribute)
    {
        $data = array();
        foreach ($this as $key =>$record) {
            $data[$key] = $record->getAttribute($attribute);
        }
        return $data;
    }

    /**
     * Returns attributes of each record in the set indexed by the record key
     *
     * @param array $attributes  attributes to retrieve. Use null to retrieve all attributes.
     * @return array  attribute arrays indexed by record key
     */
    public function getAttributes($attributes=null)
    {
        $data = array();
        foreach ($this as $key =>$record) {
            $data[$key] = $record->getAttributes($attributes);
        }
        return $data;
    }

    /**
     * Sets attributes of records in the set
     *
     * @param array   $indexedAttributes  arrays of record attributes indexed by record key
     * @param boolean $safeOnly    whether to only set safe attributes
     * @return array  keys of the records that were modified. This will be empty if no records were modified.
     */
    public function setAttributes($indexedAttributes, $safeOnly=true)
    {
        return $this->load($indexedAttributes, $safeOnly);
    }

    /**
     * Loads data into specific records in the set
     *
     * @param array   $indexedAttributes  arrays of record attributes indexed by record key
     * @param boolean $safeOnly           whether to only set safe attributes
     * @return boolean  whether any data was loaded
     */
    public function load($indexedAttributes, $safeOnly=true)
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
     * Loads data into each record in the set
     *
     * The same set of data is loaded into each record, hence the method name. To load different data into
     * different records based on the key, use [[load()]].
     *
     * @param array   $attributes  data to load. This is loaded into each record in the set.
     * @param boolean $safeOnly    whether to only set safe attributes
     * @return boolean  whether any records were modified
     */
    public function loadEach($attributes, $safeOnly=true)
    {
        $loaded = false;
        if (is_array($attributes)) {
            foreach ($this as $key =>$record) {
                $oldAttributes = $record->getAttributes();
                $record->setAttributes($attributes, $safeOnly);
                $loaded = $loaded || ($oldAttributes !== $record->getAttributes());
            }
        }
        return $loaded;
    }

    /**
     * Validates all records in the set
     *
     * @param array $attributes  attribute names. Use NULL to validate all attributes.
     * @return boolean  whether all records in the set are valid
     */
    public function validate($attributes=null)
    {
        $allValid = true;
        foreach ($this as $key =>$record) {
            $allValid = $record->validate($attributes) && $allValid;
        }
        return $allValid;
    }

    /**
     * Saves all records in the set
     *
     * @param boolean $runValidation  whether to perform validation on all records
     * @param array   $attributes     attribute names. Use NULL to save all attributes.
     * @return boolean  whether all records in the set saved successfully
     */
    public function save($runValidation=true, $attributes=null)
    {
        if ($runValidation && !$this->validate($attributes)) {
            return false;
        }
        $allSaved = true;
        foreach ($this as $key =>$record) {
            $allSaved = $record->save(false, $attributes) && $allSaved;
        }
        return $allSaved;
    }

    /**
     * Deletes all records in the set
     *
     * @return boolean  whether all records in the set were deleted
     */
    public function delete()
    {
        $allDeleted = true;
        foreach ($this as $key =>$record) {
            $allDeleted = $record->delete() && $allDeleted;
        }
        return $allDeleted;
    }

    /**
     * Returns whether any record in the set has errors
     *
     * @param string $attributes  name of the attribute to check for errors. Use NULL to check all attributes.
     * @return boolean  whether any record in the set has errors
     */
    public function hasErrors($attribute=null)
    {
        $anyErrors = false;
        foreach ($this as $key =>$record) {
            $anyErrors = $anyErrors || $record->hasErrors($attribute);
        }
        return $anyErrors;
    }

    /**
     * Returns errors for each record in the set indexed by the record key
     *
     * @param string $attribute  attribute name. Use NULL to get errors for all attributes.
     * @return array  arrays of errors indexed by record key
     */
    public function getErrors($attribute=null)
    {
        $errors = array();
        foreach ($this as $key =>$record) {
            if ($record->hasErrors($attribute)) {
                $errors[$key] = $record->getErrors($attribute);
            }
        }
        return $errors;
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
        $set = new $className();
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

    public function clear()
    {
        $this->population = 0;
        parent::clear();
    }
}