# CarBase

CarBase is a fully responsive, modern web architecture for an automotive dealership and customer portal.

## Quickstart Guide


### 1. Clone & Enter the Directory
Grab the latest push from GitHub and `cd` into the project root folder.
```bash
git clone https://github.com/yourusername/CarBase.git
cd CarBase
```

### 2. Boot the PHP Server
We use PHP's native built-in server. Run this command inside the `CarBase` root directory:
```bash
php -S localhost:8000
```
*Note: Make sure port `8000` is free, otherwise you can use `8080`!*

### 3. Open Your Browser
Pop open your favorite browser and head directly to: 
[http://localhost:8000](http://localhost:8000)

---

## 📂 Project Structure

This project has been heavily modularized so that logic controllers and rendering views never clash. 

```
/CarBase
├── /actions            # The Brains. All form handlers, POST processors, and redirect routing (Login, Signup, Process Vehicle, etc). 
├── /config             # The Anchors. Contains db_connect.php mapping directly to the SQLite PDO and our reference create.sql schema.
├── /css                # The Paint. Core cascading stylesheets dictating our custom UI layouts.
├── /assets             # The Static Images. Holds the transparent floating hero PNGs used across the application headers.
├── carbase.db          # Our live, fully saturated SQLite database packed with thousands of dynamically injected entries!
├── README.md           # You are here.
└── *.php               # The Front Views. index.php, search.php, profile.php, dealer_portal.php serving rendering points. 
```

## Security Implementations

*   **100% Parameterized Inputs**: All user searches, logins, and registrations map natively to `execute([$param])` array injections natively shielding against payload hacks.
*   **Dual-Session Isolation**: Used raw PHP session headers (`$_SESSION['dealership_id']` and `$_SESSION['customer_id']`) to physically blind dealerships from customer views, enforcing explicit log-outs.
*   **Data Integrity Checkers**: The backend uses tight schema validation (`filter_var()` + specific ranges mapping to the SQL table bounds) instantly tossing bad user traffic.

## Demo Environment

To view the backend dealership administrative portal:
1. Navigate to "Dealership Login" from the top right navbar.
2. Enter one of the seeded generic test arrays:
   - **ID**: `1001` (Elite Auto Dallas)
   - **ID**: `1002` (Texas Trucks Direct)

Enjoy CarBase!
