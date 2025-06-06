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

## Add Redirection.io support (optional)

If you want to use [Redirection.io](https://redirection.io) in front of a FrankenPHP application, [configure it as a proxy](https://www.clever-cloud.com/developers/doc/reference/reference-environment-variables/#redirectionio-support) on Clever Cloud.

In the following example, FrankenPHP listens on port `4242`, Redirection.io's agent is enabled, listens on port `8080` and forward requests to the application. Don't forget to create a Redirection.io account and [get a project key for a self-hosted instance](https://redirection.io/documentation/user-documentation/managing-instances).

```bash
clever env set CC_FRANKENPHP_PORT 4242
clever env set CC_ENABLE_REDIRECTIONIO true
clever env set CC_REDIRECTIONIO_FORWARD_PORT 4242
clever env set CC_REDIRECTIONIO_PROJECT_KEY "<your-redirectionio-project-key>"
```

## Deploy the application

Everything is now ready, just deploy the application and open it:

```bash
clever deploy
clever open
```
