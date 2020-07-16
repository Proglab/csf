# Challenge Symfony Friendly (CSF) [![Build Status](https://travis-ci.org/Proglab/csf.svg?branch=master)](https://travis-ci.org/Proglab/csf) [![codecov](https://codecov.io/gh/Proglab/csf/branch/master/graph/badge.svg)](https://codecov.io/gh/Proglab/csf)

This challenge is to make a Wordpress like.

## Pre-requisite

* Php7.2+ or docker

## Installation and use

* **Dev:** You just need to launch `php -S localhost:8000 -t public`.
* **Prod:** You need to install php-fpm and a server.

@Todo make a better install guide

## Contribute

see [CONTRIBUTING](./CONTRIBUTING.md)

## Arcitecture

### Initial module

* User management.
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

