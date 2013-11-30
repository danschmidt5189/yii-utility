# Yii PHP Framework Utility Classes

## ActiveRecordSet

ActiveRecord set represents an iteratable, countable set of AR objects. It's mainly a helper
class for saving, validating, and deleting multiple AR objects.

### Acts like an array

```php
// Load records from an array
$customers = new ActiveRecordSet(Customer::model()->findAll(['limit' =>5]));

// Countable
echo count($customers); // '5'

// Traversable
foreach ($customers as $customer) { ... }

// Array access
echo $customers[0]['firstname'];
```

### Implements common model methods

#### Loading Data

`load()` and `loadToEach()` simplify setting attributes to many models. Both functions return
whether any attributes were modified.

```php
// Load data into the appropriate records based on an index
// loadMultiple() and setAttributes() are equivalent
// Only the customers with keys 0, 1, and 2 will be modified
$indexedData = [
    0 =>['firstname' =>'Tom'],
    1 =>['firstname' =>'Dick'],
    2 =>['firstname' =>'Harry'],
];
$customers->setAttributes($indexedData);
$customers->load($indexedData);

// Set the same set of data to every record in the set
// After this, all customers will have the firstname 'John'
// These are equivalent:
$data = ['firstname' =>'John'];
$customers->loadToEach($data);
foreach ($customers as $customer) {
    $customer->attributes = $data;
}
```

#### Delete / Save / Validate

`delete()`, `save()`, and `validate()` implement the same interface as their CActiveRecord counterparts
but apply the method to every record in the set. They return true if the operation succeeded for all
records, and false otherwise.

```php
// Validate all customers in the set
if ($customers->validate()) { /* all are valid */ }

// Save all customers in the set
if ($customers->save()) { /* all saved */ }

// Delete all customers in the set
if ($customers->delete()) { /* all deleted */ }
```

#### Get Errors

```php
// Whether any record in the set has an error
if ($customers->hasError()) { /* at least one record has an error */ }

// Errors indexed by record key
$customers->getErrors();
```

### Re-Indexing

You can specify any index you want when adding records to the set. You can also create a new set indexed by a model
attribute by using the `reindex()` method:

```php
$customers = new ActiveRecordSet([new Customer(), new Customer(), new Customer()]);
$customers->loadToEach($data);
if ($customers->save()) {
    // Reindex the set based on the newly-set primary key
    // The following are equivalent
    $customers = $customers->reindex();
    $customers = $customers->reindex('id');
    $customers = $customers->reindex('primaryKey');
}
```

Note that reindexing on a non-unique attribute can throw an exception if there is already a value stored at that key.
You can overwrite the existing value by passing `true` as the second argument to `reindex()`. This tells the reindexer
to use `Set::replace()` instead of `Set::add()` when re-inserting records into the set.

## Set Methods

The Set class implements standard set functions, including `union()`, `intersect()`, `difference()`, `contains()`,
`add()`, `remove()`, `clear()`, and `merge()`.