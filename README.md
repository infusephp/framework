infuse
=====

PHP MVC framework for rapid development of web applications

## What is Infuse?

Infuse is a node.js inspired framework to make developing web applications quick and fun. In addition to MVC goodness, the framework also gives you a free REST API and administrator dashboard for models. This makes Infuse Framework an incredibly powerful tool for building a modern web application today. Infuse does not take long to learn due to its carefully chosen directory structure, which was a main frustration of mine when using other frameworks. Everything outside of core functionality has been contained inside of a module to make navigating the framework easy.

## Why another MVC framework?

Infuse is the culmination of best practices that I learned through developing PHP web applications. After using node.js and switching back to PHP, I immediately missed what node and it's awesome frameworks had to offer. I decided to port over some of the patterns learned from node over to PHP with Infuse, despite an already crowded PHP framework space. I have gotten a lot out of the Infuse Framework so am open sourcing it in hopes that someone else may benefit. This project has been built to my own taste. If something is off base, I would love to hear about it in the issues.

## Demo

A demo has been setup at [nfuse.jaredtking.com](http://nfuse.jaredtking.com).

## Features

- MVC pattern
- Modular
- Built-in authentication
- Robust permissions system
- Database agnostic with PDO
- Flexible URL routing
- Automatic REST API for models
- Dashboard to view, create, edit, and delete models
- Templating with [Smarty](http://smarty.net)
- CSS asset minification with LESS
- Javascript minification

## Getting Started

An installer is on the way. Until then, there are a couple of steps to take before getting started.

### Database setup

To get started for now, run the included `insall.sql` script.

### Configuration
 
The framework configuration can be found in `includes/config.yml`.

### Web Server

The app is actually served through the `/app` directory to prevent the framework files from being publicly accessible. Whether using apache or nginx, make sure to set the site directory to `{FRAMEWORK_PATH}/app` or else the URL routing will not work properly.

## Contributing

Please feel free to contribute by participating in the issues or by submitting a pull request. :-)

## License

The MIT License (MIT)

Copyright © 2013 Jared King

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.