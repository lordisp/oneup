<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## New OneUp

Based on the following Packages:

- Laravel `9.2`
- Passport `10.4`
- Sanctum `2.14.1`
- Livewire `v2.10.5`
- TailwindCss `3.0.23`

## Contribution
Follow this guide to develop and contribute on `OneUp`

1. Create work-items in **Azure-DevOps Board**
2. Create a new branch from `main` and link the corresponding work-items
3. publish your changes and raise a pull-request.
   1. Your code must have PhpUnit Tests with a minimum coverage of 85%
   2. Your PR will be reviewed by the product owner
   3. CI/CD Pipelines will push the code to the kubernetes platform for you
> Make sure you exclude any credentials in `.gitignore` and `.dockerignore`!!!
### Setup a local development

**clone this repository** 
```bash
git clone https://Lufthansa-Group@dev.azure.com/Lufthansa-Group/cloud-governance/_git/oneup-app
```
> This is a private repository and require authentication. The easiest way to do so is using [Git Credential Manager to generate tokens](https://docs.microsoft.com/en-us/azure/devops/repos/git/set-up-credential-managers?view=azure-devops). GCT is available for all systems.

**Cd in `/oneup-app` and create an .env file based on the template in this repo**
```bash
cp .env.example .env
```
**You may generate an App-Key for bcrypt support using the `artisan key:generate` command**
```bash
php artisan key:generate
```

**Install **composer** and **npm** dependencies**
```bash
composer install
```
```bash
npm install && npm run dev
```


### Start a development server

```bash
php artisan serve
```
> you can change hostname and port number using the artisan options. For more information type `php artisan help serve`

**You can also run a development environment on Docker**
```bash
./vendor/bin/sail up
```
> to run the environment in interactive mode, add a `-d` and the end of the command. To stop the environment type `./vendor/bin/sail down`

However, instead of repeatedly typing `vendor/bin/sail` to execute Sail commands, you may wish to configure a Bash alias that allows you to execute Sail's commands more easily:
```bash
alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'
```
Once the Bash alias has been configured, you may execute Sail commands by simply typing `sail`.
```bash
sail up
```
foo
Further information about `sail` and `artisan`commands can be found on [Laravel`s Documentation](https://laravel.com/docs/9.x/sail#executing-sail-commands) page. 