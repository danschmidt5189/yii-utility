# Yii PHP Framework Utility Classes

## ActiveRecordSet

ActiveRecord set represents an iteratable, countable set of AR objects.

The value of this class can be summarized in a simple bulk-update example:

```php
$customers = new ActiveRecordSet(Customer::model()->limit(10)->findAll());

// Countable
echo count($customers); // '10'

// Array access
$customers[1];
$customers[1]['firstname'];
$customers->retrieve(1);
$customers->retrieve(Customer::model()->findByPk(1));

// Iterator
foreach ($customers as $id =>$customer) { /* cool stuff */ }

// Load data to all models
// All customers in the set now have firstname 'John'.
// $loaded tells you whether any attributes were changed.
$loaded = $customers->load(['firstname' =>'John']);

// Validate all models
// $valid tells you if all models are valid
$valid = $customers->validate();

// Error-handling
if ($customers->hasErrors()) {
    echo CJSON::encode($customers->getErrors(););
}

// Save all models
// $saved tells you if all models saved successfully
$saved = $customers->save();

// Delete all models
// $deleted tells you if all models were deleted
$deleted = $customers->delete();

```