# Energy Desk

Energy Desk is an Indonesian-language dashboard for PLN electricity tariffs and subsidized fuel prices. It fetches live data through Laravel's server-side proxy, displays a sortable and searchable dataset with charts, and provides local spreadsheet, PDF, and analysis-note tools.

## Run locally

1. Install PHP/Composer dependencies with `composer install`.
2. Copy `.env.example` to `.env` if needed, set `DB_DATABASE=db_energy`, then run `php artisan key:generate`.
3. Create an empty `db_energy` database in your configured MySQL server, then run `php artisan migrate`.
4. Configure the API key as described below.
5. Install frontend dependencies with `npm install`.
6. Start Laravel with `php artisan serve` and Vite with `npm run dev`.

## Configure the API key

1. Sign in to your [API Indonesia dashboard](https://dashboard.apiindonesia.id/register) and create an API key for the energy API.
2. Open the `.env` file in the project root and set `API_INDONESIA_API_KEY=your_api_key`, replacing the example value with your key.
3. Save `.env`, then run `php artisan config:clear` so Laravel reloads the setting.
4. Start or refresh the app. The energy dashboard requests data through Laravel; the key is not exposed to the browser.

Do not paste the key into chat, commit `.env`, or share it publicly. If the key is exposed, revoke it in the API Indonesia dashboard and create a replacement.

## Accounts and admin guides

- Register through the site to create a regular user account. Users can edit their profile and manage their private notes.
- To make an existing account an administrator, run `php artisan users:make-admin admin@example.com` and replace the email with that account's address.
- Administrators can create and publish energy guides. Only published guides appear publicly.

## Energy data API

The API key is used by Laravel and is not sent to the browser. The app proxies:

- `GET https://use.apiindonesia.id/api/v1/energi/tarif-listrik`
- `GET https://use.apiindonesia.id/api/v1/energi/bbm`

The matching local routes are `/api/energi/tarif-listrik` and `/api/energi/bbm`. If Laravel configuration is cached, run `php artisan config:clear` after changing the API key. See the [API Indonesia energy documentation](https://docs.apiindonesia.id/#energi) for authentication, source attribution, and data details.

## Working with files

- Export the visible, filtered energy data as an Excel `.xlsx` workbook.
- Download the Excel template for the selected dataset, fill in its `Data` sheet, and import the `.xlsx` file. The required columns are included in the template.
- Exact duplicate rows in an imported workbook are removed automatically. Invalid rows are skipped, and imported data stays in browser memory; files are not uploaded.
- View local PDF files in the browser, or use the PDF report button to print/save the current dataset as a PDF.
- Analysis notes are stored in the browser's local storage.

Run `npm run build` to generate production assets in `public/build`.
