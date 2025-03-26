# Task Management App for FrankenPHP on Clever Cloud

Deploy this task management application with [FrankenPHP on Clever Cloud](https://www.clever-cloud.com/developers/doc/applications/frankenphp/). It uses [Materia KV with JSON commands](https://www.clever-cloud.com/developers/doc/addons/materia-kv/#json-commands) and [Predis](https://github.com/predis/predis) client. To follow this tutorial, you need a [Clever Cloud account](https://console.clever-cloud.com) and [Clever Tools](https://github.com/CleverCloud/clever-tools):

```bash
npm i -g clever-tools
clever login
```

You can also install Clever Tools with [many packages managers](https://www.clever-cloud.com/developers/doc/cli/install/).

## Create resources

Create and configure a FrankenPHP application with a linked [MateriaKV](https://www.clever-cloud.com/materia/materia-kv/) add-on:

```bash
git clone https://github.com/CleverCloud/frankenphp-kv-json-example
cd frankenphp-kv-json-example

clever create -t frankenphp
clever env set CC_WEBROOT "/public"
clever addon create kv kvFrankenPHP --link frankenphp-kv-json-example
```

## Deploy the application

Everything is now ready, just deploy the application and open it:

```bash
clever deploy
clever open
```
