# drupal_web

A Drupal 11 site running in Docker, with:

- **Database**: MariaDB, created automatically the first time the `db`
  container boots (name/user/password come from `.env`).
- **Docker**: `drupal` (Apache + PHP + Drush) and `db` (MariaDB), plus
  **Adminer** for a quick visual look at the database.
- **Custom theme**: `drupal_web_theme` at
  `web/themes/custom/drupal_web_theme`.
- **Custom pages**: `drupal_web_pages` module at
  `web/modules/custom/drupal_web_pages`, providing `/home` (set as the
  front page), `/about`, and `/faqs`.

## 1. Start the containers

From this folder (`drupal_web/`):

```bash
docker compose up -d --build
```

First run builds the image (installs Drush) and starts MariaDB and Apache.
Give MariaDB a few seconds to finish initializing — `docker compose logs -f db`
if you want to watch it — before installing the site in the next step.

## 2. Install the site (creates tables in the new DB)

This runs Drupal's installer against the database that was just created,
using the credentials from `.env`:

```bash
docker compose exec drupal drush site:install standard \
  --db-url=mysql://drupal_web_user:drupal_web_pass@db/drupal_web \
  --site-name="Drupal Web" \
  --account-name=admin \
  --account-pass=admin_change_me \
  -y
```

> Change `--account-pass` to something you'll actually use — this creates
> the site's admin user.

Prefer clicking through the installer instead? Visit `http://localhost:8080`,
choose **Standard**, and on the database screen enter:
- Database name: `drupal_web`
- Username: `drupal_web_user`
- Password: `drupal_web_pass`
- Host: `db` (advanced options — this is the Docker service name, not `localhost`)

## 3. Enable the custom theme and pages module

```bash
docker compose exec drupal drush theme:enable drupal_web_theme -y
docker compose exec drupal drush config-set system.theme default drupal_web_theme -y
docker compose exec drupal drush en drupal_web_pages -y
docker compose exec drupal drush cache:rebuild
```

Enabling `drupal_web_pages` also sets `/home` as the site's front page
(see `drupal_web_pages_install()` in the module).

## 4. Visit the site

- Home: http://localhost:8080/
- About: http://localhost:8080/about
- FAQs: http://localhost:8080/faqs
- Admin login: http://localhost:8080/user/login
- Adminer (DB browser): http://localhost:8080:8081 → System: MySQL,
  Server: `db`, Username/Password from `.env`, Database: `drupal_web`

## Where things live

```
drupal_web/
├── docker-compose.yml         Drupal + MariaDB + Adminer services
├── Dockerfile                 Official Drupal image + Drush
├── .env                       DB name/user/password, ports
└── web/
    ├── themes/custom/drupal_web_theme/
    │   ├── drupal_web_theme.info.yml       theme + regions
    │   ├── drupal_web_theme.libraries.yml  CSS/JS/fonts
    │   ├── drupal_web_theme.theme          preprocess hooks (body classes)
    │   ├── css/style.css                   all styling
    │   ├── js/behaviors.js                 active nav-link highlighting
    │   └── templates/page.html.twig        header/content/footer layout
    └── modules/custom/drupal_web_pages/
        ├── drupal_web_pages.routing.yml    /home, /about, /faqs routes
        ├── drupal_web_pages.links.menu.yml nav links for the 3 pages
        ├── drupal_web_pages.module         hook_theme + sets front page
        ├── src/Controller/PageController.php
        ├── templates/                      one Twig template per page
        └── config/install/                 places blocks into the theme's
                                             content & primary_menu regions
```

## Editing content

- **Home / About / FAQ copy**: edit the Twig templates in
  `web/modules/custom/drupal_web_pages/templates/`, or the `$faqs` array in
  `PageController::faqs()`.
- **Colors, type, spacing**: all in
  `web/themes/custom/drupal_web_theme/css/style.css` — the palette and font
  choices are set as CSS custom properties at the top of the file.
- **Turning these into real Drupal content** (editable from the admin UI
  instead of code): create Basic Page nodes for About/FAQs, then either
  update the routes to redirect to those nodes, or delete
  `drupal_web_pages.routing.yml`'s entries for the pages you've replaced.

## Stopping / resetting

```bash
docker compose down          # stop containers, keep the database volume
docker compose down -v       # stop and DELETE the database volume (fresh start)
```

## Troubleshooting

- **"Access denied" during install**: give MariaDB more time to finish
  initializing before running `drush site:install` (`docker compose logs db`
  should show `ready for connections`).
- **Installer says `sites/default/files` or `settings.php` is not writable**:
  the Dockerfile now fixes this automatically on every container start (see
  `fix-permissions-entrypoint.sh`) — rebuild with
  `docker compose up -d --build` and re-run the installer. If you're on an
  older container that was already running, fix it in place without
  rebuilding:
  ```bash
  docker compose exec -u root drupal bash -c \
    "mkdir -p sites/default/files && chown -R www-data:www-data sites/default && chmod -R 775 sites/default"
  ```
  Then click "try again" on the installer page.
- **Blank/500 page**: run `docker compose exec drupal drush cache:rebuild`
  and check `docker compose logs drupal`.
- **Theme not applying**: confirm step 3's `config-set system.theme default`
  command ran, then hard-refresh the browser.
