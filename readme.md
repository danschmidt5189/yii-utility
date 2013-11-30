# Yii PHP Framework Utility Classes

## ActiveRecordSet

ActiveRecord set represents an iteratable, countable set of AR objects. It's mainly a helper
class for saving, validating, and deleting multiple AR objects.

Here's what you can do with it:

```php
$customers = new ActiveRecordSet(Customer::model()->findAll(['limit' =>5]));

// Acts like an array
echo count($customers); // '5'
foreach ($customers as $customer) {
    // $customer is a Customer object
}
echo $customers[0]['firstname'];

// Set attributes and detect if changes were made
if ($customers->load($data)) { /* Changes made */ }
if ($customers->loadMultiple($indexedData)) { /* Changes made */ }

// Validate, save, delete all records
if ($customers->validate()) { /* All validated */ }
if ($customers->save()) { /* All saved */ }
if ($customers->delete()) { /* All deleted */ }
```