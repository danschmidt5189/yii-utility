# Yii PHP Framework Utility Classes

## ActiveRecordSet

ActiveRecord set represents an iteratable, countable set of AR objects.

Sets support the following CActiveRecord methods, which do pretty much what you expect:

- save(): Saves all records in the set and returns whether all saved.
- validate(): Validates all records in the set and returns whether all are valid.
- getErrors(): Returns errors for each record in the set indexed by the set's index.
- hasErrors(): Whether any record in the set has validation errors.
- getAttributes(): Get attributes for each record in the set, indexed by the set's index.
- `setAttributes()`: Set attributes to all records in the set. Alias of `load()`.

In addition, sets support:

- `add()`: Add record(s) to the set.
- `remove()`: Remove record(s) from the set.
- `retrieve()`: Return record(s) from the set.
- `clone()`: Create an immutable copy of the set.

```php
$customers = new ActiveRecordSet(Customer::model()->limit(10)->findAll());
echo count($customers); // '10'
if (!$customers->loadMultiple($request->getParam('Customer'))) {
    // No data loaded
} else if (!$customers->validate()) {
    // Validation error
    echo CJSON::encode($customers->getErrors()); // errors indexed by primary key
} else if (!$customers->save()) {
    // Internal error
} else {
    // Saved
}
```