# PHP App Starter

## Prerequisites

You'll need a working understanding of:

- **PHP** (8.2+): the app is plain PHP with no framework
- **JavaScript**: small vanilla JS modules, bundled by Vite
- **CSS / Tailwind CSS** (v4)

And installed on your machine:

- **Node.js** and npm
- **Docker** with Docker Compose

**NOTE** On Windows, run the project from WSL.

## Introduction

A lightweight starter template for building PHP web apps without a framework. It gives you a working foundation (routing, database, auth and a front-end build) so you can start on your app's actual features straight away.

**Stack**

| Layer | Tech |
| --- | --- |
| Server | PHP 8.2 on Apache (Docker) |
| Database | MySQL (Docker) with a simple migration runner |
| Email | PHPMailer over SMTP, with [MailHog](https://github.com/mailhog/MailHog) catching email locally |
| Front end | Vite, Tailwind CSS v4, vanilla JS/TypeScript |

**Out of the box**

- File-based page routing and reusable PHP components (header, footer, form inputs, modals, toast notifications, loader)
- User accounts: sign up, log in/out, edit profile (name, bio, profile picture), forgotten password emails and account deletion
- Security basics: hashed passwords, hardened sessions, same-origin checks on endpoints, output escaping, safe image uploads and security headers
- Accessible markup: keyboard-friendly modals, labelled form errors, skip link and AA colour contrast
- Hot reload in development, including a full page reload when a `.php` file is saved

## Running locally

1. **Start the app.** From the project root:

   ```bash
   ./start-app.sh
   ```

   This creates `.env` from `.env.example` if it doesn't exist, starts the Docker containers, waits for MySQL, installs Composer dependencies and runs the database migrations.

2. **Start the front end.** In a separate terminal:

   ```bash
   npm install
   npm run dev
   ```

3. **Open the app** at [http://localhost:8000](http://localhost:8000). Use `localhost` rather than `127.0.0.1`: endpoints only accept requests from the origin set in `APP_ORIGIN`.

| Service | URL |
| --- | --- |
| App | http://localhost:8000 |
| MailHog (view sent emails) | http://localhost:8025 |
| Vite dev server | http://localhost:5178 |
| MySQL | localhost:3306 |

Ports and credentials are set in `.env`.

**Other commands**

```bash
npm run build                                      # Build production assets into dist/
./preview-app.sh                                   # Build, then preview the app in production mode
./preview-app.sh dev                               # Switch back to development mode
docker-compose exec web php migrations/index.php   # Run new migrations
docker-compose down                                # Stop the containers
```

When `APP_ENV` is anything other than `local`, pages load the built assets from `dist/` instead of the Vite dev server, and PHP errors are hidden. `./preview-app.sh` restarts the web container with `APP_ENV=production` without editing `.env`, so you can check a production build on localhost. Safari won't keep you logged in during a preview, because the session cookie is marked `Secure`. Use Chrome or Firefox instead.

## Architecture

```
index.php              Front controller: bootstraps the app and routes to a page
pages/                 One file per route (/account → pages/account.php)
components/            Reusable PHP partials, loaded with get_component()
includes/
  functions.php        Global helper functions
  classes/class-user.php
  endpoints/           AJAX endpoints (handle-*.php), return JSON
src/                   Front-end JS/TS modules and style.css
migrations/            Database migrations and the runner (index.php)
public/images/         Icons and user uploads
```

**Request flow.** `.htaccess` sends every request that isn't a real file to `index.php`. That calls `init_app()`, connects to the database and loads the matching file from `pages/`, or `404.php` if there isn't one. `.htaccess` also blocks direct access to internal files such as `.env`, `vendor/`, `components/` and `migrations/`.

**Endpoints.** Forms submit with `fetch()` to `includes/endpoints/handle-*.php`. Each endpoint calls `init_app()` and `require_same_origin_post()`, then does its work and replies with `json_response()` using a consistent shape:

```json
{ "status": 400, "message": "…", "data": { "error_field": "email", "error_message": "…" } }
```

**Front-end modules.** `src/main.ts` looks for `data-component="name"` attributes on the page and loads only the matching `src/name.js` or `.ts` modules. For example, `data-component="modal"` loads `src/modal.js`.

**Migrations.** Files are grouped by table (for example `migrations/user/`) and named `YYYYMMDDHHMMSS_description.php`. They run in timestamp order across all folders, and each one only runs once.

### Key functions (`includes/functions.php`)

| Function | Purpose |
| --- | --- |
| `init_app()` | Bootstraps every request: error display, security headers and the session. Call it first in any new entry point. |
| `env($key, $default)` | Reads an environment variable, whether it came from Docker or `.env`. |
| `is_dev()` | `true` when `APP_ENV=local`. |
| `init_db()` / `get_db_connection()` | Connect to MySQL. The PDO connection is shared through the global `$db_connection`. |
| `get_logged_in_user()` | The current user's data as an array, or `false` if nobody is logged in. |
| `get_component($path, $args)` | Includes `components/{$path}.php`. Inside the component, `parse_component_args($defaults)` merges the passed args over its defaults. |
| `esc($value)` | Escapes a value for HTML output. Use it on anything user-supplied. |
| `get_svg_icon($name)` | Returns an icon from `public/images/icons/` as inline SVG, hidden from screen readers. |
| `json_response()` / `json_server_error()` | Send a JSON response and end the request. `json_server_error()` logs the details and returns a generic message. |
| `require_same_origin_post()` | Rejects anything that isn't a POST from the app's own origin. |
| `send_email($subject, $to, $message)` | Sends an HTML email through the configured SMTP server. |
| `upload_image($input_name)` / `delete_uploaded_image($path)` | Save or remove images in `public/images/uploads/`. Uploads are checked by their contents and given random names. |
| `debug_log($message)` | Appends to `debug.log` in development only. |

### The `User` class (`includes/classes/class-user.php`)

| Method | Purpose |
| --- | --- |
| `create($email, $password)` | Validates and creates a user with a hashed password. |
| `get($email_or_id)` | Fetches a user as an array. The password hash is never included. |
| `update($email, $fields)` | Updates allowed fields: `first_name`, `last_name`, `bio`, `image_url` and `password`. |
| `delete($email_or_id)` | Deletes a user and their profile picture. Pending password resets are removed by a foreign key. |
| `login($email, $password)` | Verifies credentials and starts a fresh session. |
| `send_password_reset($email)` | Emails a single-use reset link that expires after one hour. |
| `reset_password($token, $password)` | Sets a new password using a valid reset token. |
| `validate_password($password)` | Checks the password rules: at least 8 characters, with an uppercase letter, a lowercase letter and a number. |

`create()`, `login()`, `send_password_reset()` and `reset_password()` return an `Error` object on failure, so check the result with `instanceof Error`. `update()` and `delete()` return `false` on failure.
