# Challenge Symfony Friendly (CSF) [![Build Status](https://travis-ci.org/Proglab/csf.svg?branch=master)](https://travis-ci.org/Proglab/csf) [![codecov](https://codecov.io/gh/Proglab/csf/branch/master/graph/badge.svg)](https://codecov.io/gh/Proglab/csf)

This challenge is to make a Wordpress like.

## Pre-requisite

* Php7.4.1+ or docker

## Installation and use

Create de database : 

php bin/console doctrine:database:create

Create the structure of the database :

php bin/console doctrine:migration:migrate

Laod the fixtures :

php bin/console doctrine:fixtures:load

You can connect to the admin with :

To be a ROLE_USER :

      email: 'user@csf.com'
      password: 'user'
      
To be a ROLE_ADMIN :
    
      email: 'admin@csf.com'
      password: 'admin'

To be a ROLE_SUPERADMIN :

      email: 'superadmin@csf.com'
      password: 'superadmin'

## Contribute

see [CONTRIBUTING](./CONTRIBUTING.md)

## Arcitecture

### Initial module

* User management.
    * :white_check_mark: register a user
    * :white_check_mark: login
    * :white_check_mark: crud
    * :white_check_mark: lost password
* Rights management.
* Module management.
* Mail management.
* Translation management.
* Static pages.
* Menus.

### What is a module

* Module take the form of a bundle.
* Module can be activate/desactivate.
* Module management show the full list of available module.
* If we permit to anyone to add module, need to check security.
* Module can interact to other module thanks to event.
* Module can add view element thanks to widget.
* @Todo we need to think about how to do widget in widget.
  * A widget of blog page, and add a widget of downloading/printing pdf on it.

### additionnal module proposition

* Blog.
* Wiki.
* Forum.
* Newsletter.
* Online talking.
* Online talking with intermediate (talking with facebook messenger, discord, etc).
* Flux RSS.
* Api to access website element.
* Sso management.
* DB management.
* Backup And Restore (BAR).
* Supervision.
* Ged.

