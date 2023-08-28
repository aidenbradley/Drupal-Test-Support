[Introduction](#introduction)

[Interacts With Authentication](#interacts-with-authentication)
- [Logging in as a user](#logging-in-as-a-user)
- [Acting as an anonymous user](#acting-as-an-anonymous-user)
- [Acting as user with a single role](#acting-as-user-with-a-single-role)
- [Acting as user with multiple roles](#acting-as-user-with-multiple-roles)

# Introduction
The purpose of the Support API is to provide convenient methods to improve the developer experience when writing automated tests.

There is no single trait for the Support API. Rather there are many traits, where each trait aims to address the developer experience when working with certain areas of Drupal and automated testing.

## Interacts With Authentication
There is a trait called [InteractsWithAuthentication](.././tests/src/Traits/Support/InteractsWithAuthentication.php) that contains an API to improve the developer experience of logging in as a certain user or as a user with certain role(s).

### Logging in as a user
To log in as a certain user in a test, call the `actingAs` method.

```php
public function acting_as(): void
{
    $user = $this->loadUser(50);

    $this->actingAs($user);
}
```

### Acting as an anonymous user
To set the test run to use an anonymous user, call the `actingAsAnonymous` method.

There is no need to create or pass an anonymous user. The `actingAsAnonymous` method will handle this for you.

```php
public function acting_as_anonymous(): void
{
    $this->actingAsAnonymous();
}
```

### Acting as user with a single role
To log in as a user with a certain role set against them, call the `actingAsRole` method.

Under the hood, this method will create a user for you, attribute the given role to the user and log in as them.

```php
public function acting_as_role(): void
{
    $editorRole = $this->loadRole('editor');

    $this->actingAsRole($editorRole);
}
```

### Acting as user with multiple roles
To log in as a user with certain roles set against them, call the `actingAsRoles` method.

Under the hood, this method will create a user for you, attribute the given array of roles to the user and log in as them.

```php
public function acting_as_roles(): void
{
    $writerRole = $this->loadRole('writer');
    $editorRole = $this->loadRole('editor');

    $this->actingAsRoles([
        $writerRole,
        $editorRole,
    ]);
}
```
