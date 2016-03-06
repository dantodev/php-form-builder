# PHP Form Builder


## Example Usage (with slim framework)

This Example demonstrates the implementation of a user registration form. The `FormBuilder` is designed for more complex forms than a registration form, but for demonstraition purpose, we make an exception. ;)

The builder based on `Form` and `FormElement` classes. `Form` classes get registered on `FormBuilder` while `FormElement` are associated with `Form` instances.

The example uses some traits (e.g. `Dtkahl\FormBuilder\Traits\FormTrait`) which contains some basic methods which are required by the interfaces you have to implement for `Form` and `FormElement` classes. I do highly recommend to use them to save a bunch of time. :)


#### Create Container

The container provides an instance the `FormBuilder` class. We pass an array of parameters to the constructor. (Optional, but needed in this example).

Parameters on FormBuilder (and later FormTrait and FormElementTrait) are implemented by using [dtkahl/php-parameter-trait](https://github.com/dtkahl/php-parameter-trait).

    $container['FormBuilder'] = function ($c) {
      return new \Dtkahl\FormBuilder\FormBuilder([
        'renderer' => $c->get('renderer')
      ]);
    };


#### Create Form

Before we can build a form, we have to create a new class which implements the interface `Dtkahl\FormBuilder\Interfaces\FormInterface` and optional (but recommended) use the trait `Dtkahl\FormBuilder\Traits\FormTrait`.

    <?php namespace App\Forms;
    
    use Dtkahl\FormBuilder\Traits\FormTrait;
    use Dtkahl\FormBuilder\Interfaces\FormInterface;
    use Dtkahl\SimpleView\ViewRenderer;
    
    class RegisterForm implements FormInterface
    {
      use FormTrait;
    
      public function render()
      {
        if (!$this->_builder->getParameter('renderer') instanceof ViewRenderer) {
          return $this->_builder->getParameter('renderer')->render('registerForm.php', [
            'form' => $this
          ]);
        }
        throw new \RuntimeException("Parameter 'renderer' missing!");
      }
      
      public function save()
      {
        foreach ($this->getElements() as $element) {
          $element->save();
        }
        return $this;
      }
    
    }

As you can see in the implemented interface, you need to declare a `render()` and a `save()` method. The save method should iterate over the form elements and call their save method.

Now we need the view for the renderer. (In this example `registerForm.php`)

    <form action="/register" method="POST">
      <?php
        foreach ($form->getElements() as $element) {
          echo $element->render();
        }
      ?>
      <button type="submit">Submit</button>
    </form>

The view should iterate over the associated form elements and call their render method.

*I do __not__ recommend to make a dedicated form class for each use case. Rather, it is more useful to define only on flexible form with parameters (like `method`, `action`, etc.)  which will be evaluated in the view.* 

#### Create FormElement

In this example, we only create a simple form element with label and text input. It is possible to build far more complex elements than this.

We create a new class which implements the interface `Dtkahl\FormBuilder\Interfaces\FormElementInterface` and optional (but recommended) use the trait `Dtkahl\FormBuilder\Traits\FormElementTrait`.

    <?php namespace  App\Forms\Elements;
    
    use Dtkahl\FormBuilder\Traits\FormElementTrait;
    use Dtkahl\FormBuilder\Interfaces\FormElementInterface;
    use Dtkahl\SimpleView\ViewRenderer;
    
    class InputFormElement implements FormElementInterface
    {
      use FormElementTrait;
    
      public function render()
      {
        if ($this->_builder->getParameter('renderer') instanceof ViewRenderer) {
          return $this->_builder->getParameter('renderer')->render('inputElement.php', [
            'element' => $this
          ]);
        }
        throw new \RuntimeException("Parameter 'renderer' missing!");
      }
    
      public function save()
      {
        // TODO validate request data and perhaps save user
        // TODO $form->setParameter('success')
      }
    
    }

As you can see in the implemented interface, you need to declare a `render()` and a `save()` method. **You should add some functionality to the save method.**

Now we need the view for the renderer. (In this example `inputElement.php`)

    <div>
      <label><?php echo $form->getParameter('label') ?>:
        <input type="text" name="<?php echo $form->getParameter('name') ?>" 
            value="<?php echo $form->getParameter('value', '') ?>">
      </label>
    </div>

#### Register Middleware and Routes (register Form)

We register a simple middleware where we register the Form to the `FormBuilder`, and use this middleware on the routes which needs the form (GET and POST `/register`, because there we render or rather save). We use the `success` parameter to check if save was successfully and perhaps redirect to `/done`. 

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
      if ($form->getParameter('success')) {
        return $response->withRedirect('/done');
      }
    	return $response->withRedirect('/register');
    })->add($mw);
    
    $app->post('/done', function ($request, $response, $args) {
    	$response->getBody()->write("Thanks for registration!");
    	return $response;
    });
