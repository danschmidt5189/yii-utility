# Yii PHP Framework Utility Classes

## ActiveRecordSet

ActiveRecordSet is a helper class for dealing with collections of ActiveRecord objects.

`__get()`, `__set()`, and `__call()` are overridden to allow access to methods of the
AR models contained in the set. Results of method calls obtained by accessing the records
within the set are returned in a `MultiResult` object.

### Construction

```php
// Get some customers from the DB
// Note that the index is important! If you don't specify an index, you
// may get unexpected results.
$customers = Customer::model()->findAll(['limit' =>10, 'index' =>'id']);

// Explicitly set the class name and load data
$set = new ActiveRecordSet('Customer', $customers);

// Implicitly set the class name and load data
$set = new ActiveRecordSet($customers);

// Explicitly set the class name, but load data later
$set = new ActiveRecordSet('Customer');
$set->add($customers);
foreach ($customers as $id =>$record) {
    $set->add($id, $record);
}
```

### Loading Data

You can load data into each record in the set using `load()`, or into records specified by a
given key using `loadByKey()`. The methods `setAttributes()` and `setAttributesByKey()` are
aliases of these methods.

Both methods return a MultiResult indicating whether any record was modified.

```php
// Here's some data...
$data = [
    'firstname' =>'John',
    'lastname'  =>'Doe',
];

// Equivalent to calling setAttributes($data) on each Customer in the set
// This is safe-only
$set->attributes = $data;

// You can also be unsafe...
$set->setAttributes($data, false);

// ... or unsafe and set specific attributes...
$set->balance = 0;

// ... or specify which records to set data on
$set->loadByKey([
    1 =>['firstname' =>'Dave', 'lastname' =>'Graham'],
    2 =>['firstname' =>'Nalle', 'lastname' =>'Hukkataival'],
]);
```

## MultiResult

A multi-result is a set used to express the result of bulk-operations on the underlying
records in an ActiveRecordSet. For example, calling `$set->validate()` returns a MultiResult.

The keys of the MultiResult are the record keys, and the values are the results returned
by the corresponding record. Fetching properties, calling methods, or setting properties
all return a multiresult if performed on the entire record set.

Use `allTrue`, `partlyTrue`, `allFalse`, `partlyFalse`, and `toArray()` to interpret your MultiResult:

```php
if ($set->validate()->allTrue) {
    // All Valid:
    // Note that $set->validate() returned a MultiResult object, which we determined
    // was completely valid by calling allTrue().
} else if ($set->hasErrors()->partlyTrue) {
    // hasErrors() is a MultiResult, and partlyTrue indicates at least one record
    // had an error. $set->getErrors() also returns a MultiResult, which can be
    // converted into an array.
    echo CJSON::encode([
        'message' =>'Validation errors',
        'errors' =>$set->errors->toArray(),
    ]);
} else {
    echo CJSON::encode([
        'message' =>'Internal error',
    ]);
}
```