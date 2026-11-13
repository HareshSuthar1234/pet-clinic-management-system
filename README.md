# Pet Clinic Management System

BCA 5th Semester - ERP-Based CRUD Mini Project (Advance Web Design)

A database-driven ERP-style system for a veterinary clinic. It manages pet owners, their pets, appointments with vets, and billing.

**Technology:** HTML, CSS, JavaScript (frontend) | PHP (backend) | MySQL (database) | XAMPP (server) | VS Code

## How to run (XAMPP)

1. Install XAMPP and start **Apache** and **MySQL** from the control panel.
2. Copy this folder to `C:\xampp\htdocs\pet-clinic-management-system` (Mac/Linux: `htdocs` inside the XAMPP directory).
3. Open `http://localhost/phpmyadmin` > **Import** > choose `database/pet_clinic.sql` > **Go**. This creates the `pet_clinic` database with sample data.
4. Open `http://localhost/pet-clinic-management-system/`.
5. If your MySQL has a root password, change it in `config.php`.

## Features mapped to the assignment manual

| Manual requirement | Where it is implemented |
|---|---|
| Real-world management problem | Veterinary clinic: owners, pets, visits, bills |
| Database-driven | MySQL, 4 related tables (Section 7: 2-4 tables) |
| All 4 CRUD operations | Every module: owners, pets, appointments, billing |
| Create (form -> INSERT -> database) | `owner_form.php`, `pet_form.php`, `appointment_form.php`, `billing_form.php` |
| Read (SELECT -> HTML table) | `owners.php`, `pets.php`, `appointments.php`, `billing.php` |
| Update (pre-filled form -> UPDATE) | Same form files opened with `?id=` (Edit button) |
| Delete (action -> DELETE) | Delete button on every list, with a confirm prompt |
| Search / filter (field -> SELECT ... WHERE) | Search box on every list, plus species, status and date filters |
| Basic form validation | Browser-side in `assets/script.js`, server-side in each form file |
| Clear, user-friendly interface | `assets/style.css`, shared nav, flash messages, empty states |
| Primary and foreign keys | `pets.owner_id`, `appointments.pet_id`, `billing.appointment_id` |
| Database/SQL file in repo | `database/pet_clinic.sql` |

### Required screens (Section 9)

1. Dashboard / Home - `index.php`
2. Add record forms - `owner_form.php`, `pet_form.php`, `appointment_form.php`, `billing_form.php`
3. View records tables - `owners.php`, `pets.php`, `appointments.php`, `billing.php`
4. Search / filter - search bar on each list page
5. Edit / update form - same form files with `?id=`
6. Delete operation - Delete button on each list page
7. Report / summary - `reports.php` (printable)

### Modules (Section 5)

Dashboard, Master Data (Owners, Pets), Transaction/Management (Appointments, Billing), Search/Filter, Reports/Summary.

## Database design

| Table | Important fields | Purpose |
|---|---|---|
| owners | owner_id (PK), name, phone, email, address | Owner information |
| pets | pet_id (PK), owner_id (FK), pet_name, species, breed, age, gender | Pet information |
| appointments | appointment_id (PK), pet_id (FK), appointment_date, appointment_time, vet_name, reason, status | Visit records |
| billing | bill_id (PK), appointment_id (FK), description, amount, payment_date, status | Payments |

Relationships: one owner has many pets, one pet has many appointments, one appointment has many bills. All foreign keys use `ON DELETE CASCADE`, so deleting an owner removes their pets, visits and bills (the delete prompt warns about this).

## Extra touches worth mentioning in the viva

- **Prepared statements** (`mysqli` `prepare` + `bind_param`) on every query, which prevents SQL injection.
- **`htmlspecialchars` through `e()`** on all output, which prevents XSS.
- **Double validation:** JavaScript for quick feedback, PHP because the browser cannot be trusted.
- **Double-booking check:** a vet cannot have two scheduled appointments at the same date and time.
- **Post/Redirect/Get:** after saving, the page redirects, so refreshing does not resubmit the form.

## Project structure

```
pet-clinic-management-system/
|-- index.php                dashboard
|-- owners.php / owner_form.php
|-- pets.php / pet_form.php
|-- appointments.php / appointment_form.php
|-- billing.php / billing_form.php
|-- reports.php
|-- config.php               DB connection + helpers
|-- includes/header.php, footer.php
|-- assets/style.css, script.js
|-- database/pet_clinic.sql
`-- docs/                    diagrams + report guide
```

## GitHub submission (Sections 14-15)

```
git init
git add .
git commit -m "Pet clinic management system"
git branch -M main
git remote add origin https://github.com/GroupName/pet-clinic-management-system.git
git push -u origin main
```

Then: make sure the repo is accessible to your faculty (public, or add them as a collaborator), open the link in a private window to confirm it works, and submit the link with the report on Google Classroom.

## Group members

| Name | Enrollment no. | Contribution |
|---|---|---|
| | | |
| | | |
| | | |
| | | |
