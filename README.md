# NextLead – Online Voting System (PHP + MySQL)

This is a PHP + MySQL app. **GitHub Pages cannot run it** (Pages only serves static HTML).
It deploys from git to **Render** using the included `Dockerfile`, with a free cloud MySQL database.

## 1. Create a free MySQL database
Use **TiDB Cloud Starter** (tidbcloud.com, free, MySQL-compatible). Aiven's free MySQL plan was retired in Nov 2026.
Copy its host, port, user, password and database name.
The app creates its tables automatically the first time it connects (from `database.sql`).

## 2. Deploy on Render (auto-deploys on every git push)
1. render.com → sign in with GitHub → **New → Blueprint** → pick this repo (it reads `render.yaml`).
   (Or **New → Web Service** → this repo → Runtime: Docker → Free.)
2. Fill in the environment variables:

| Variable  | Value |
|-----------|-------|
| DB_HOST   | your MySQL host |
| DB_PORT   | your MySQL port (TiDB: 4000) |
| DB_NAME   | database name (TiDB: `test`) |
| DB_USER   | database user |
| DB_PASS   | database password |
| DB_SSL    | `true` |
| SMTP_USER | your Gmail address |
| SMTP_PASS | a **new** Gmail App Password |

3. Click **Deploy**. Your site will be at `https://nextlead-xxxx.onrender.com`.
   Every `git push` to `main` redeploys automatically.

## Running locally (MAMP)
Nothing changes: with no environment variables set, `connection.php` falls back to
`localhost / root / root / voting_system`. Run `composer install` once to get PHPMailer.

Never put passwords in the code — use the environment variables above.
