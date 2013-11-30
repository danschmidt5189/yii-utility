# Yii PHP Framework Utility Classes

## ActiveRecordSet

ActiveRecord set represents an iteratable, countable set of AR objects. You can validate, save, set/get attributes
on the set just as you would an individual AR object.

```php
$customers = new ActiveRecordSet(Customer::model()->limit(10)->findAll());
if (!$customers->loadMultiple($request->getParam('Customer'))) {
    // No data loaded
} else if (!$customers->validate()) {
    // Validation error
} else if (!$customers->save()) {
    // Internal error
} else {
    // Saved
}
```