# Tesseract PWA MINI App

Demo: **[app.gscloud.cz](https://app.gscloud.cz)**  
Repository:  **[github.com/GSCloud/app](https://github.com/GSCloud/app)**

Distributed under **MIT** license.

## Installation

* `git clone https://github.com/GSCloud/app.git`
* `make install update doctor` - do all the dirty work and checks
* modify accordingly to your requirements:
  * **.env** - environment
  * **app/ApiPresenter.php** - REST API presenter
  * **app/AppPresenter.php** - main presenter
  * **app/config.neon** - main configuration
  * **app/router.neon** - main router
  * **app/router_api.neon** - REST API router
  * **www/img/logo.png** - application logo
  * **www/manifest.json** - PWA manifest
  * **www/partials/body_content.mustache** - main application
  * **www/partials/content.mustache** - page content
  * **www/partials/navigation.mustache** - navigation
* point Apache vhost to **www/**
* `make update test sync prod` - update, local test, synchronization, production test

## Docker

TBD.
