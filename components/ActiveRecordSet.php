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
 * @method boolean validate()       validates all records in the set
 * @method array   getAttributes()  returns record attributes (arrays) indexed by the index property
 * @method array   getErrors()      returns record error messages (arrays) indexed by the index property
 * @method boolean hasErrors()      returns whether any record in the set is invalid
 *
 * @package  yii-utility
 */
class ActiveRecordSet extends CComponent implements Iterator, ArrayAccess, Countable
{
    /**
     * @var string  value used to concatenate composite keys into a string
     */
    const COMPOSITE_KEY_SEPARATOR = '_';

    /**
     * @var mixed  index of the current record. Implements Iterator interface.
     */
    private $_key;

    /**
     * @var string  record property used for indexing the result set
     */
    private $_index = 'primaryKey';

    /**
     * @var array  ActiveRecord objects indexed by the [[index]]
     */
    private $_records = array();

    /**
     * Loads attributes into all records
     *
     * @param array   $attributes  attribute values indexed by name
     * @param boolean $safeOnly    whether to only set safe attributes
     * @return boolean  whether any records were loaded with data
     */
    public function load($attributes, $safeOnly=true)
    {
        $anyLoaded = false;
        foreach ($this as $record) {
            $anyLoaded = self::loadRecordAttributes($record, $attributes, $safeOnly) || $anyLoaded;
        }
        return $anyLoaded;
    }

    /**
     * Loads data into a record with the appropriate index
     *
     * @param array   $indexedAttributes  attribute arrays indexed by the index of the record they correspond to
     * @param boolean $safeOnly           whether to only set safe attributes
     * @return boolean  whether any records were loaded with data
     */
    public function loadMultiple($indexedAttributes, $safeOnly=true)
    {
        $anyLoaded = false;
        if (is_array($indexedAttributes)) {
            foreach ($indexedAttributes as $key =>$attributes) {
                if ($record = $this->retrieve($key)) {
                    $anyLoaded = self::loadRecordAttributes($record, $attributes, $safeOnly) || $anyLoaded;
                }
            }
        }
        return $anyLoaded;
    }

    /**
     * Gets attributes for all records in the set
     *
     * @return array  attributes indexed by record primary key
     */
    public function getAttributes($attributes=null)
    {
        $data = array();
        foreach ($this as $record) {
            $data[$this->getRecordKey($record)] = $record->getAttributes($attributes);
        }
        return $data;
    }

    /**
     * Sets attributes to each record in the set
     *
     * @param array   $attributes  attributes to set into each model. This is a single array of attribute values
     *                             indexed by the attribute name.
     * @param boolean $safeOnly    whether to only set safe attributes
     * @return boolean  whether any records were loaded with data
     */
    public function setAttributes($attributes, $safeOnly=true)
    {
        return $this->load($attributes, $safeOnly);
    }

    /**
     * Saves all records in the set
     *
     * @param boolean $runValidation  whether to perform validation
     * @param array   $attributes     attributes to save. Defaults to null, meaning all attributes are saved.
     * @return boolean  whether all records saved successfully
     */
    public function save($runValidation=true, $attributes=null)
    {
        if ($runValidation && !$this->validate()) {
            return false;
        }
        $allSaved = true;
        foreach ($this as $key =>$record) {
            $allSaved = $record->save(false, $attributes) && $allSaved;
        }
        return $allSaved;
    }

    /**
     * Validates all records in the set
     *
     * @param array $attributes  names of attributes to validate on each record
     * @return boolean  whether all records are valid
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
     * Get record errors
     *
     * @param string $attribute  attribute name. Use null to retrieve errors for all attributes
     * @return array  errors for all attributes or the specified attribute for each record in the set,
     *                indexed by the [[_index]] of the set.
     */
    public function getErrors($attribute=null)
    {
        $errors = array();
        foreach ($this as $key =>$record) {
            $errors[$key] = $record->getErrors($attribute);
        }
        return $errors;
    }

    /**
     * Returns whether any record in the set has errors
     *
     * @param string $attribute  attribute name. Use null to check all attributes.
     * @return boolean  whether any record in the set has errors
     */
    public function hasErrors($attribute=null)
    {
        $errors = $this->getErrors($attribute);
        foreach ($errors as $recordSpecificErrors) {
            if (!empty($recordSpecificErrors)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array $records description
     */
    public function __construct($records=null, $index='primaryKey')
    {
        $this->_index = $index;
        $this->add($records);
    }

    /**
     * Returns keys of the record set
     *
     * @return array  keys
     */
    public function keys()
    {
        return array_keys($this->_records);
    }

    /**
     * Returns the [[_records]] property
     */
    public function all()
    {
        return $this->_records;
    }

    /**
     * Removes all records from the set
     */
    public function clear()
    {
        $this->_records = array();
    }

    /**
     * Returns whether the set contains a given record
     *
     * @param CActiveRecord $record  record or record primary key
     * @return boolean  whether the set contains the record
     */
    public function contains($record)
    {
        $key = is_object($record) ? $this->getRecordKey($record) : $record;
        return isset($this->_records[$key]);
    }

    /**
     * Copies the set into a new set
     *
     * @return ActiveRecordSet  the copied set
     */
    public function cloneSet($attributes=null)
    {
        $set = new ActiveRecordSet(null, $this->_index);
        if ($this->count() === 0) {
            return $set;
        }
        foreach ($this->_records as $key =>$record) {
            $set->add(self::cloneRecord($record));
        }
        return $set;
    }

    /**
     * Returns records from the set
     *
     * @param mixed $record  record or records to retrieve
     * @return CActiveRecord  the records that are part of this set
     */
    public function retrieve($record)
    {
        if (is_array($record)) {
            return $this->retrieveMultiple($record);
        } else if (null !== $record) {
            return $this->retrieveItem($record);
        }
    }

    /**
     * Returns a record from the set
     *
     * @param mixed $record  record or key
     * @return CActiveRecord  the record, or null if it is not in the set
     */
    protected function retrieveItem($record)
    {
        $key = is_object($record) ? $this->getRecordKey($record) : $record;
        return $this->contains($record) ? $this->_records[$key] : null;
    }

    /**
     * Returns multiple records from the set
     *
     * @param array $records  record objects or keys
     * @return array  the records that are part of this set
     */
    protected function retrieveMultiple($records)
    {
        $found = array();
        if (is_array($records)) {
            foreach ($records as $record) {
                if ($item = $this->retrieveItem($record)) {
                    $found[$this->getRecordKey($item)] = $item;
                }
            }
        }
        return $found;
    }

    /**
     * Adds a new record to the set
     *
     * @param CActiveRecord $record  record or records to add to the set
     * @return mixed  the value(s) replaced by the new record
     */
    public function add($records)
    {
        if ($records instanceof ActiveRecordSet) {
            return $this->addMultiple($records->all());
        } else if (is_array($records)) {
            return $this->addMultiple($records);
        } else if (null !== $records) {
            return $this->addItem($records);
        }
    }

    /**
     * Removes a record from the set
     *
     * @param mixed $record  record or index of the record
     * @return mixed  the removed record, or null if it was not part of the set
     */
    public function remove($records)
    {
        if ($records instanceof ActiveRecordSet) {
            return $this->removeMultiple($records->all());
        } else if (is_array($records)) {
            return $this->removeMultiple($records);
        } else if (null !== $records) {
            return $this->removeItem($records);
        }
    }

    /**
     * Returns the index at which a record will be stored
     *
     * Composite keys are imploded using an underscore.
     *
     * @param CActiveRecord $record  the record
     * @return mixed  the index for the record. See [[_index]].
     */
    public function getRecordKey($record)
    {
        $key = is_object($record) ? $record->{$this->_index} : $record;
        if (is_array($key)) {
            $key = implode(self::COMPOSITE_KEY_SEPARATOR, $key);
        }
        return $key;
    }

    /**
     * Returns the [[_index]] property
     */
    public function getIndex()
    {
        return $this->_index;
    }

    /**
     * Sets the [[_index]] value
     *
     * Records will be reindexed using the new index.
     *
     * @return boolean  whether the index was changed
     */
    public function setIndex($value)
    {
        if ($value !== $this->_index) {
            $this->_index = $value;
            $this->replace($this->_records);
            return true;
        }
        return false;
    }

    public function offsetExists($record)
    {
        return $this->contains($record);
    }

    public function offsetGet($record)
    {
        return $this->retrieve($record);
    }

    public function offsetSet($key, $record)
    {
        if ($key !== $this->getRecordKey($record)) {
            throw new InvalidArgumentException('Key must be the index used by the ActiveRecordSet');
        }
        return $this->add($record);
    }

    public function offsetUnset($key)
    {
        return $this->remove($key);
    }

    public function count()
    {
        return count($this->_records);
    }

    /**
     * Returns the current key
     *
     * Implements Iterator interface.
     */
    public function key()
    {
        return $this->_key;
    }

    /**
     * Rewinds the set and resets the key
     *
     * Implements Iterator interface.
     */
    public function rewind()
    {
        reset($this->_records);
        $this->_key = key($this->_records);
    }

    /**
     * Returns the next record in the set and increments the key
     *
     * Implements Iterator interface.
     */
    public function next()
    {
        next($this->_records);
        $this->_key = key($this->_records);
    }

    /**
     * Returns the current record in the set
     *
     * Implements Iterator interface.
     */
    public function current()
    {
        return $this->_records[$this->_key];
    }

    /**
     * Returns whether the current [[_key]] is valid
     *
     * Implements Iterator interface.
     */
    public function valid()
    {
        return isset($this->_records[$this->_key]);
    }

    /**
     * Loads attributes into a model
     *
     * @param CModel $record  the model
     * @param array $data     model attributes
     * @return boolean  whether any attributes were modified on the model
     */
    public static function loadRecordAttributes($record, $data, $safeOnly=true)
    {
        $oldAttributes = $record->getAttributes();
        $record->setAttributes($data, $safeOnly);
        return $oldAttributes !== $record->getAttributes();
    }

    /**
     * Clone a record
     *
     * @param CActiveRecord $record  the record to clone
     * @param array $attributes  names of the attributes to copy to the cloned record. Leave null to copy all attributes.
     *                           Copying attributes is not limited to safe attributes.
     * @return CActiveRecord  a copy of the record
     */
    public static function cloneRecord($record, $attributes=null)
    {
        $className = get_class($record);
        $copy = new $className($record->scenario);
        $copy->setAttributes($record->getAttributes($attributes), false);
        $copy->setIsNewRecord($record->getIsNewRecord());
        return $copy;
    }

    /**
     * Adds multiple records to the set
     *
     * @param array $records  the records to add
     * @return array  the replaced records
     */
    protected function addMultiple($records)
    {
        $replaced = array();
        if (is_array($records)) {
            foreach ($records as $record) {
                $replaced[$this->getRecordKey($record)] = $this->removeItem($record);
                $this->addItem($record);
            }
        }
        return $replaced;
    }

    /**
     * Adds a single record to the set
     *
     * @param CActiveRecord $record  the record
     * @return mixed  the value replaced by the new record
     */
    protected function addItem(CActiveRecord $record)
    {
        $key = $this->getRecordKey($record);
        $current = $this->retrieve($key);
        $this->_records[$key] = $record;
        return $current;
    }

    /**
     * Removes multiple records from the set
     *
     * @param array $records  records or keys to remove
     * @return array  the records that were removed
     */
    protected function removeMultiple($records)
    {
        $removed = array();
        if (is_array($records)) {
            $removed[$this->getRecordKey($record)] = $this->removeItem($record);
        }
        return $removed;
    }

    /**
     * Removes a single record from the set
     */
    protected function removeItem($record)
    {
        $key = is_object($record) ? $this->getRecordKey($record) : $record;
        $removed = $this->retrieve($key);
        unset($this->_records[$key]);
        return $removed;
    }
}