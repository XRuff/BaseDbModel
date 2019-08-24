Nette database utility
======

Requirements
------------

Package requires PHP 5.6 or higher

- [nette/database](https://github.com/nette/security)
- [nette/utils](https://github.com/nette/application)

Installation
------------

The best way to install XRuff/BaseDbModel is using  [Composer](http://getcomposer.org/):

```sh
$ composer require xruff/basedbmodel
```

Short Documentation
------------

UsersRepository for handling database table `users`. You should use `Repository` suffix after table name.

```php

namespace MyModels;

use XRuff\App\Model\BaseDbModel;

class UsersRepository extends BaseDbModel
{
	// no implementation needed
}
```

If for some reason you can not use the table name or word Repository in class name, follow these steps:

```php

namespace MyModels;

use Nette\Database\Context;
use XRuff\App\Model\BaseDbModel;

class MyAnyNameRepo extends BaseDbModel
{
	public function __construct(Context $db)
	{
		parent::__construct($db, 'my_db_able_name');
	}
}
```

Using model in presenter (or anywhere else):

```php
use Nette;
use MyModels\UsersRepository;

class MyUsersPresenter extends Nette\Application\UI\Presenter
{
	/** @var UsersRepository $usersModel */
	public $usersModel;

	public function __construct(
		UsersRepository $usersModel
	) {
		$this->usersModel = $usersModel;
	}

	public function actionDefault()
	{
		// returns Nette\Database\Table\ActiveRow
		// with first user with name John in table users
		$this->usersModel->getOneBy(['name' => 'John']);

		// or
		// set name as Joe for user with id 5 in table users
		// and returns Nette\Database\Table\ActiveRow with updated values
		$this->usersModel->save(['id' => 5, 'name' => 'Joe']);

		// or add new user with name Jane
		// and returns Nette\Database\Table\ActiveRow with just added row
		$this->usersModel->save(['name' => 'Jane']);

		// or some another method inherited from BaseDbModel
	}
}
```

-----

Repository [https://github.com/XRuff/BaseDbModel](https://github.com/XRuff/BaseDbModel).
