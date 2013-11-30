<?php
/**
 * ActiveRecordInterface.php class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

/**
 * Interface for ActiveRecord
 *
 * @package  yii-utility
 */
interface ActiveRecordInterface
{
    public function save($runValidation=true, $attributes=null);
    public function validate($attributes=null);
    public function delete();
    public function getErrors($attribute=null);
    public function hasErrors($attribute=null);
    public function setAttributes($attributes, $safeOnly=true);
    public function getAttributes($attributes=null);
}