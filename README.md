<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# IDG Back-End Course (with Basic Front-End)

Welcome! This project will guide you through setting up a PHP/Laravel development environment using Docker and Docker Compose.

## Quick Start Guide

**Step 1: Install Docker**  
Learn how to install Docker for Windows, Linux, or macOS:  
👉 [How to Install Docker](./instructions/1.InstallDocker.md)

**Step 2: Use Dockerfile, Build and Run with Docker, Log in to Docker Hub, Build Your Image, Push It to Docker Hub and Pull It**  
How to build your image and manage containers using Docker commands:  
👉 [Dockerfile Usage: Build, Run, Start, Stop, Up, Down, Push, and Pull](./instructions/2.DockerfileUsage.md)

**Step 3: Use Docker Compose**  
Simplify container management with Docker Compose:  
👉 [Docker Compose Usage: Build, Up, and Down](./instructions/3.DockerComposeUsage.md)

**Step 4: Create a Laravel Project Inside the Container**  
Step-by-step guide to creating a new Laravel project from within your Docker container:  
👉 [Create Laravel Project Inside Docker Container](./instructions/4.CreateLaravelProjectInsideDockerContainer.md)

**Step 5: Update docker-compose.yaml to Include MySQL (MariaDB), phpMyAdmin, and Override Laravel .env**
Expand your setup to include a database and admin interface:  
👉 [Update docker-compose.yaml for MySQL, phpMyAdmin, and Laravel .env](./instructions/5.UpdateDockerCompose.md)

**Step 6: Prepare GitHub Actions for CI/CD Deployment to Docker Hub (Test: Only push to main branch triggers build, test, and push to Docker Hub)**
Set up automated workflows for continuous integration and deployment:  
👉 [Prepare GitHub Actions for CI/CD Deployment to Docker Hub](./instructions/6.PrepareGitHubActionsForCIDCDeploymentToDockerHub.md)

**Step 7: Introduction to API with Laravel Sanctum**  
Learn how to set up API authentication using Laravel Sanctum:  
👉 [Introduction to API Setup (Laravel Sanctum)](./instructions/7.IntroductionToApi.md)

**Step 8: Implement Swagger UI for Laravel API Authentication**  
Document and visualize your API authentication endpoints using Swagger UI:  
👉 [Implement Swagger UI for Laravel API Authentication](./instructions/8.ImplementSwaggerUI.md)
---

## Project Structure

- `Dockerfile` — Defines your PHP/Apache environment.
- `docker-compose.yml` — Manages multi-container setup.
- `instructions/` — Contains detailed guides for each setup step.

---

## Need Help?

If you get stuck, refer to the detailed instructions in the `instructions/` folder or rea# How to Update docker-compose.yaml to Include MySQL (MariaDB) and phpMyAdmin

ch out to your course instructor.

---

r the [MIT license](https://opensource.org/licenses/MIT).ach out to your course instructor.

---er the [MIT license](https://opensource.org/licenses/MIT).
ch out to your course instructor.

---
