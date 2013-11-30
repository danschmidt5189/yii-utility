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
     * Sets attributes to each record in the set
     *
     * This is equivalent to [[load]]
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
    public function load($indexedAttributes, $safeOnly)
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
     * @param array   $attributes  data to load. This is loaded into each record in the set.
     * @param boolean $safeOnly    whether to only set safe attributes
     * @return boolean  whether any records were modified
     */
    public function loadToEach($attributes, $safeOnly)
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
        $set = new ActiveRecordSet();
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
}