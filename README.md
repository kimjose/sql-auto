# SQL Auto Execution Script

This PHP script allows you to execute SQL queries against multiple sites or a specific site using IP addresses defined in `sites.json`.

## Files

- `script.php` - Main script for executing SQL queries
- `sites.json` - Configuration file containing site names and IP addresses
- `list_sites.php` - Helper script to list available sites
- `README.md` - This documentation

## Usage

### 1. Run Query for All Sites (Original Behavior)
```bash
php script.php path/to/your/query.sql database_name
```

### 2. Run Query for Specific Site by IP Address
```bash
php script.php path/to/your/query.sql database_name 10.202.30.5
```

### 3. List All Available Sites
```bash
php list_sites.php
```

### 4. Search for Specific Sites
```bash
php list_sites.php karen
php list_sites.php 10.202.30
```

## Examples

### Example 1: Run query for all sites
```bash
php script.php /path/to/my_query.sql openmrs
```
This will execute the SQL query against the `openmrs` database on all sites defined in `sites.json`.

### Example 2: Run query for babadogo site only
```bash
php script.php /path/to/my_query.sql openmrs 10.202.30.5
```
This will:
1. Look up IP `10.202.30.5` in `sites.json`
2. Find it corresponds to site name `babadogo`
3. Execute the query against the `openmrs` database only for the babadogo site
4. Save output to `/home/joseph/Documents/test_output/babadogo.csv`

### Example 3: Find a site's IP address
```bash
php list_sites.php karen
```
Output:
```
Available Sites:
============================================================
Site Name                 IP Address     
------------------------------------------------------------
karen                     10.202.30.9    
------------------------------------------------------------
Total sites (filtered): 1
```

## Output

- Query results are saved as CSV files in `/home/joseph/Documents/test_output/`
- File naming convention: `{site_name}.csv`
- The script provides colored output showing success/failure status

## Error Handling

- If an invalid IP address is provided, the script will show all available IPs
- File existence checks for both SQL file and sites.json
- Proper error messages with colored output for better visibility

## Database Connection

- Default database user: `dwapi`
- Default database password: `dwapi`
- Database name: Specified as command line parameter (e.g., `openmrs`)
- Connection uses the IP addresses defined in `sites.json`

## Color Codes

- 🔵 Blue: Informational messages
- 🟡 Yellow: Processing/running queries
- 🟢 Green: Success messages
- 🔴 Red: Error messages

## Site Configuration

Sites are configured in `sites.json` with the following structure:
```json
[
    {
        "name": "site_name",
        "ip": "10.202.30.X"
    }
]
```

To add a new site, simply add a new object to the JSON array with the site name and IP address.

