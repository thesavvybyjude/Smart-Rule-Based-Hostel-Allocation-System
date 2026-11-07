# Smart Rule-Based Hostel Allocation System

A web app that allocates university hostel rooms by clear, auditable rules instead of first-come-first-served. Students register and request a room; an admin runs the allocation and everyone is either placed or waitlisted in one pass.

## How allocation works

1. Every student who requested a room is reset to `pending`, and room occupancy is cleared.
2. Each student gets a priority score: **+10** for a medical need, then **+5 / +3 / +2 / +1** for 400 / 300 / 200 / 100 level.
3. Students are sorted by score, highest first.
4. Each student is placed in the first room in a hostel matching their gender with a free slot; if none is left, they are waitlisted.
5. The run returns how many students were allocated and waitlisted.

## Features

- **Students** — register, log in, request a room, see their allocation, send and view feedback.
- **Admins** — manage hostels and rooms, run allocation, review feedback, view reports.

## Stack

Vanilla HTML/CSS/JS frontend · Node.js API functions (Vercel serverless, Express for local dev) · Supabase (Postgres + Auth)

An earlier PHP/MySQL version is kept in `old_php_version/` for reference.

## Run it locally

```bash
git clone https://github.com/thesavvybyjude/Smart-Rule-Based-Hostel-Allocation-System.git
cd Smart-Rule-Based-Hostel-Allocation-System
npm install
```

1. Create a Supabase project and run `supabase_schema.sql` in the SQL editor.
2. Add a `.env` file:
   ```
   SUPABASE_URL=...
   SUPABASE_ANON_KEY=...
   SUPABASE_SERVICE_ROLE_KEY=...
   ```
3. Seed sample hostels and rooms, then start the server:
   ```bash
   node seed.js
   node server.js        # http://localhost:3000
   ```

Deploys to Vercel as-is (`vercel.json` routes `/api/*` to the functions and everything else to `frontend/`).

## Project structure

```
api/          allocation, hostels, rooms, feedback, reports endpoints
frontend/     login/register, student and admin pages
supabase_schema.sql
seed.js       sample data
server.js     local Express server mirroring the Vercel routes
```

## Licence

[MIT](LICENSE)

---

Built by [Jude Thompson](https://github.com/thesavvybyjude)
