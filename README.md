<p align="center"><a href="https://oneup.az.lhgroup.de" target="_blank"><img src="https://oneup.az.lhgroup.de/images/logos/oneup_logo.png" width="300" alt="OneUp Logo"></a></p>
<p align="center">
   <a href="https://dev.azure.com/Lufthansa-Group/oneup/_release?_a=releases&view=mine&definitionId=1" target="_parent"><img src="https://vsrm.dev.azure.com/Lufthansa-Group/_apis/public/Release/badge/8373dadd-edae-4fa4-a13f-bdb0f1099f49/1/1" alt="Deployment Status"></a>
   <a href="https://dev.azure.com/Lufthansa-Group/oneup/_build?definitionId=18" target="_parent"><img src="https://dev.azure.com/Lufthansa-Group/oneup/_apis/build/status/oneup?branchName=main" alt="Build Status"></a>
</p>

## OneUp

Based on the following Frameworks:

- Laravel `9.2`
- Sanctum `2.14.1`
- Livewire `v2.10.5`
- Tailwind CSS `3.0.23`
- AlpineJS `3.0`

## Contribution

Follow this guide to develop and contribute on `OneUp`

1. Create work-items in **Azure-DevOps Board**
2. Create a new branch from `main` and link the corresponding work-items
3. publish your changes and raise a pull-request.
    1. Your code must have PhpUnit Tests with a minimum coverage of 85%
    2. Your PR will be reviewed by the product owner
    3. CI/CD Pipelines will push the code to the kubernetes platform for you

> Make sure you exclude any secrets with `.gitignore` AND `.dockerignore`. 😖

### Setup a local development

**clone this repository**

```bash
git clone https://Lufthansa-Group@dev.azure.com/Lufthansa-Group/oneup/_git/oneup
```

This is a private repository and require authentication. The easiest way to do so is using [Git Credential Manager to generate tokens](https://docs.microsoft.com/en-us/azure/devops/repos/git/set-up-credential-managers?view=azure-devops)

GCT is available for all systems.

**Cd in `/oneup` and create an .env file based on the template in this repo**

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

You can change hostname and port number using the artisan options. For more information type `php artisan help serve`

**You can also run a development environment on Docker**

```bash
./vendor/bin/sail up
```

To run the environment in interactive mode, use the `-d` option the end of the command. To stop the environment type `./vendor/bin/sail down`

However, instead of repeatedly typing `vendor/bin/sail` to execute Sail commands, you may wish to configure a Bash alias
that allows you to execute Sail's commands more easily:

```bash
alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'
```

Once the Bash alias has been configured, you may execute Sail commands by simply typing `sail`.

```bash
sail up
```

Further information about `sail` and `artisan`commands can be found
on [Laravel`s Documentation](https://laravel.com/docs/9.x/sail#executing-sail-commands) page. 