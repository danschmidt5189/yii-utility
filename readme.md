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

### Set Indexing

Each set is indexed by an attribute (or getter method) of the records it contains. The default is to use each record's primary key.

Composite keys are concatened together using an underscore.

You can specify your own composite key when constructing the set, or later using `setIndex()`. Setting the index will re-index the
records of the set and may result in some records being overridden.

```php
$customers = new ActiveRecordSet(Customer::model()->order('t.id ASC')->limit(10)->findAll());
print_r($customers->keys()); // 0 1 2 3...
$customers->setIndex('firstname');
print_r($customers->keys()); // 'Dan' 'Mike' 'Joe' 'Rasmus'...
```