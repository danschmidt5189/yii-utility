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

`load()` and `loadMultiple()` simplify setting attributes to many models. Both functions return
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
$customers->loadMultiple($indexedData);

// Set the same set of data to every record in the set
// After this, all customers will have the firstname 'John'
$data = ['firstname' =>'John'];
$customer->load($data);
```

#### Delete / Save / Validate

`delete()`, `save()`, and `validate()` implement the same interface as their CActiveRecord counterparts
but apply the method to every record in the set. They return true if the operation succeeded for all
records, and false otherwise.

```php
// Validate all customers in the set
$customers->validate();

// Save all customers in the set
$customers->save();

// Delete all customers in the set
$customers->delete();
```

#### Get Errors

```php
// Whether any record in the set has an error
$customers->hasError();

// Errors indexed by record key
$customers->getErrors();
```