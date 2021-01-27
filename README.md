# oneplace-pos

[![Build Status](https://travis-ci.com/OnePlc/PLC_X_POS.svg?branch=master)](https://travis-ci.com/OnePlc/PLC_X_POS)
[![Coverage Status](https://coveralls.io/repos/github/OnePlc/PLC_X_POS/badge.svg?branch=master)](https://coveralls.io/github/OnePlc/PLC_X_POS?branch=master)

## Introduction

This is the POS Module for onePlace Software Framework based on Laminas Project (former Zend Framework)

Create your own Point of Sale - based on onePlace ! 

This Module is for a local oneplace installation on the POS itself.

We use Raspberry Pi 3 Model B with Touchscreen as a Hardware Basis

Needs a second onePlace Installation (Server) to communicate with - normally,
thats the online store.

## POS Module

This pos module is a starting point for your own onePlace modules.
It expands on [oneplace-core](https://github.com/OnePlc/PLC_X_Core) and uses the onePlace / Laminas MVC layer and module systems.

## Installation

The easiest way to install onePlace POS is via composer
```shell script
composer require oneplace/oneplace-pos

./node_modules/yarn/bin/yarn add bootstrap4-toggle
```

## Requirements
[escpos-php](https://github.com/mike42/escpos-php)

## Getting started

how to utilize user manager

how to create own modules

## Documentation

Documentation will be extended soon.