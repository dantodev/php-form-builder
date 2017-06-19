[![Latest Stable Version](https://poser.pugx.org/dtkahl/php-form-builder/v/stable)](https://packagist.org/packages/dtkahl/php-form-builder)
[![License](https://poser.pugx.org/dtkahl/php-form-builder/license)](https://packagist.org/packages/dtkahl/php-form-builder)
[![Build Status](https://travis-ci.org/dtkahl/php-form-builder.svg?branch=master)](https://travis-ci.org/dtkahl/php-form-builder)

# PHP Form Builder

## Dependencies

* `PHP  >=7.0.0`

## Installation

Install with [Composer](http://getcomposer.org):

```
composer require dtkahl/php-form-builder
```


## Usage

This package provides two classes to easily build forms: `FieldSet` and `Field`. The wasiest way to explain how this package works is to show an implementation example.

As example we build a simple shop checkout form. Let us have a look at the (simplified) code first.

```php
<?php

use Dtkahl\FormBuilder\FieldSet;
use Dtkahl\FormBuilder\Field;
use Respect\Validation\Validator;

class CheckoutForm extends FieldSet
{
    public function setUp()
    {
        $this->setField('order_id', new NumerField);
        $this->setFieldSet('delivery_address', new AddressFieldSet);
        $this->setFieldSet('payment', new PaymentFieldSet);
    }
    public function setUpValidators() {
         $this->setValidator('order_id', Validator::intType()->length(10, 10));
    }
}

class AddressFieldSet extends FieldSet
{
    public function setUp()
    {
        $this->setField('first_name', new TextField)->setLabel('First Name');
        $this->setField('last_name', new TextField)->setLabel('Last Name');
        $this->setField('street', new TextField)->setLabel('Street');
        $this->setField('number', new NumberField)->setLabel('Street Number');
        $this->setField('post_code', new TextField)->setLabel('Post Code');
        $this->setField('city', new TextField)->setLabel('City');
        $this->setField('country', new SelectField(["options"=>["Germany", "Switzerland", "Austria"]))->setLabel('Country');
    }
    public function setUpValidators() {
         $this->setValidator('first_name', Validator::stringType()->length(1,50));
         $this->setValidator('last_name', Validator::stringType()->length(1,50));
         $this->setValidator('street', Validator::stringType()->length(1,50));
         $this->setValidator('number', Validator::stringType()->length(1,5));
         $this->setValidator('post_code', Validator::stringType()->length(1,5));
         $this->setValidator('city', Validator::stringType()->length(1,50));
         $this->setValidator('country', Validator::stringType()->length(1,50));
    }
}

class PaymentFieldSet extends FieldSet
{
    public function setUp()
    {
        $this->setField('type', new SelectField(["options"=>["Credit Card", "PayPal"]))->setLabel('Payment Type');
        $this->setField('credit_card_number', new TextField)->setLabel('Credit Card Number');
        $this->setField('paypal_address', new NumberField)->setLabel('PayPal Address');
    }
    public function setUpValidators() {
        $this->setValidator('type', Validator::notEmpty());
        if ($this->getValue('type') === 0) {
            $this->setValidator('credit_card_number', Validator::creditCard()->length(10,20));
        } else {
            $this->setValidator('paypal_address', Validator::email());
        }
    }
}

public function TextField extends Field
{
    public function render()
    {
        // keep in mind, this is only an simplified example. In a real project you would use a template engine for this
        return sprintf("<input type=\"text\" name=\"%s\" value=\"%s\"/>", $this->getName(), $this->getValue());
    }
}

public function NumberField extends Field
{
    public function render()
    {
        // keep in mind, this is only an simplified example. In a real project you would use a template engine for this
        return sprintf("<input type=\"number\" name=\"%s\" value=\"%s\"/>", $this->getName(), $this->getValue());
    }
}

public function SelectField extends Field
{
    protected $config = ["options"=>[]];
    public function __construct($config)
    {
        $this->config = array_merge($this->config, $config);
    }
    public function render()
    {
        // keep in mind, this is only an simplified example. In a real project you would use a template engine for this
        $html = sprintf("<select name="%s">", $this->getName());
        foreach ($this->config["options"] as $value=>$label) {
            if ($key === $this->getValue()) {
                $html += sprintf("<option value=\"%s\" selected>%s</option>", $value, $label);
            } else {
                $html += sprintf("<option value=\"%s\">%s</option>", $value, $label);
            }
        }
        return $html + "</select>";
    }
}
```

## TODO
* link to respect/validation docs for Rules
* render method is not mandatory
* how to use gettext for custom messages
* rendering/validation
* dependency injection
