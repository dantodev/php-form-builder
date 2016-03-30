[![Latest Stable Version](https://poser.pugx.org/dtkahl/php-form-builder/v/stable)](https://packagist.org/packages/dtkahl/php-form-builder)
[![License](https://poser.pugx.org/dtkahl/php-form-builder/license)](https://packagist.org/packages/dtkahl/php-form-builder)
[![Build Status](https://travis-ci.org/dtkahl/php-form-builder.svg?branch=master)](https://travis-ci.org/dtkahl/php-form-builder)

# PHP Form Builder

## Dependencies

* `PHP  >=5.6.0`
* `dtkahl/php-array-tools ^2.0`

## Installation

Install with [Composer](http://getcomposer.org):

```
composer require dtkahl/php-form-builder
```


## Example Usage (with slim framework)

This Example demonstrates the implementation of a user registration form. The `FormBuilder` is designed for more complex forms than a registration form, but for demonstration purposes, we make an exception. ;)

The builder is based on `Form` and `FormElement` classes. `Form` classes get registered on `FormBuilder` while `FormElement` classes are associated with `Form` instances.

The example uses some traits (e.g. `Dtkahl\FormBuilder\Traits\FormTrait`) with some basic methods which are required by the interfaces, you have to implement for `Form` and `FormElement` classes. I do highly recommend to use them to save a bunch of time. :)


#### Create Container

The container provides an instance the `FormBuilder` class. We pass an array of properites to the constructor. (Optional, but needed in this example).

Properties on FormBuilder (and later FormTrait and FormElementTrait) are implemented by using [dtkahl/php-array-tools](https://github.com/dtkahl/php-array-tools).

```php
$container['FormBuilder'] = function ($c) {
  return new \Dtkahl\FormBuilder\FormBuilder([
    'renderer' => $c->get('renderer')
  ]);
};
```

#### Create Form

Before we can build a form, we have to create a new class which implements the interface `Dtkahl\FormBuilder\Interfaces\FormInterface` and optional (but recommended) use the trait `Dtkahl\FormBuilder\Traits\FormTrait`.

```php
<?php namespace App\Forms;

use Dtkahl\FormBuilder\Traits\FormTrait;
use Dtkahl\FormBuilder\Interfaces\FormInterface;
use Dtkahl\SimpleView\ViewRenderer;

class RegisterForm implements FormInterface
{
  use FormTrait;

  public function render()
  {
    if (!$this->_builder->properties->get('renderer') instanceof ViewRenderer) {
      return $this->_builder->properties->get('renderer')->render('registerForm.php', [
        'form' => $this
      ]);
    }
    throw new \RuntimeException("Property 'renderer' missing!");
  }
  
  public function save()
  {
    foreach ($this->getElements() as $element) {
      $element->save();
    }
    return $this;
  }

}
```

As you can see in the implemented interface, you need to declare a `render()` and a `save()` method. The save method should iterate over the form elements and call their save method.

Now we need the view for the renderer. (In this example `registerForm.php`)

```php
<form action="/register" method="POST">
  <?php
    foreach ($form->getElements() as $element) {
      echo $element->render();
    }
  ?>
  <button type="submit">Submit</button>
</form>
```

The view should iterate over the associated form elements and call their render method.

*I do __not__ recommend to make a dedicated form class for each use case. Rather, it is more useful to define only one flexible form with properties (like `method`, `action`, etc.)  which will be evaluated in the view.* 

#### Create FormElement

In this example, we only create a simple form element with label and text input. It is possible to build far more complex elements than this.

We create a new class which implements the interface `Dtkahl\FormBuilder\Interfaces\FormElementInterface` and optional (but recommended) use the trait `Dtkahl\FormBuilder\Traits\FormElementTrait`.

```php
<?php namespace  App\Forms\Elements;

use Dtkahl\FormBuilder\Traits\FormElementTrait;
use Dtkahl\FormBuilder\Interfaces\FormElementInterface;
use Dtkahl\SimpleView\ViewRenderer;

class InputFormElement implements FormElementInterface
{
  use FormElementTrait;

  public function render()
  {
    if ($this->_builder->properties->get('renderer') instanceof ViewRenderer) {
      return $this->_builder->properties->get('renderer')->render('inputElement.php', [
        'element' => $this
      ]);
    }
    throw new \RuntimeException("Property 'renderer' missing!");
  }

  public function save()
  {
    // TODO validate request data and perhaps save user
    // TODO $form->properties->set('success')
  }

}
```

As you can see in the implemented interface, you need to declare a `render()` and a `save()` method. **You should add some functionality to the save method.**

Now we need the view for the renderer. (In this example `inputElement.php`)

```php
<div>
  <label><?php echo $form->getProperty('label') ?>:
    <input type="text" name="<?php echo $form->properties->get('name') ?>" 
        value="<?php echo $form->properties->get('value', '') ?>">
  </label>
</div>
```

#### Register middleware and routes (register Form)

We register a simple middleware where we can can configure the form , and use this middleware on the routes which needs the form (GET and POST `/register`, because there we render or rather save). We use the `success` property to check if saving was successfully and perhaps redirect to `/done`. 

```php
$mw = function ($request, $response, $next) {
  $form = $container->get('FormBuilder')->registerForm('register', \App\Forms\registerForm::class);
  
  $form->addElement('username', [
    'label' => 'Username',
    'name' => 'username',
  ]);
  
  $form->addElement('mail', \App\Forms\Elements\InputFormElement::class, [
    'label' => 'Mail address',
    'name' => 'mail',
  ]);
  
  $form->addElement('password', [
    'label' => 'Password',
    'name' => 'password',
  ]);
  
  $response = $next($request, $response);
  return $response;
};

$app->get('/register', function ($request, $response, $args) {
	$response->getBody()->write(
	  $container->get('FormBuilder')->getForm('register')->render();
	);
	return $response;
})->add($mw);

$app->post('/register', function ($request, $response, $args) {
  $form = $container->get('FormBuilder')->getForm('register');
  $form->save();
  if ($form->properties->get('success')) {
    return $response->withRedirect('/done');
  }
	return $response->withRedirect('/register');
})->add($mw);

$app->post('/done', function ($request, $response, $args) {
	$response->getBody()->write("Thanks for registration!");
	return $response;
});
```
