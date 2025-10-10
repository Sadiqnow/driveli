@echo off
echo Adding missing columns to drivers_normalized table...

mysql -u root -p drivelink -e "ALTER TABLE drivers_normalized ADD COLUMN IF NOT EXISTS city VARCHAR(100) NULL;"
mysql -u root -p drivelink -e "ALTER TABLE drivers_normalized ADD COLUMN IF NOT EXISTS postal_code VARCHAR(10) NULL;"
mysql -u root -p drivelink -e "ALTER TABLE drivers_normalized ADD COLUMN IF NOT EXISTS residential_address TEXT NULL;"
mysql -u root -p drivelink -e "ALTER TABLE drivers_normalized ADD COLUMN IF NOT EXISTS bank_id BIGINT UNSIGNED NULL;"
mysql -u root -p drivelink -e "ALTER TABLE drivers_normalized ADD COLUMN IF NOT EXISTS account_number VARCHAR(20) NULL;"
mysql -u root -p drivelink -e "ALTER TABLE drivers_normalized ADD COLUMN IF NOT EXISTS account_name VARCHAR(100) NULL;"
mysql -u root -p drivelink -e "ALTER TABLE drivers_normalized ADD COLUMN IF NOT EXISTS bvn VARCHAR(11) NULL;"
mysql -u root -p drivelink -e "ALTER TABLE drivers_normalized ADD COLUMN IF NOT EXISTS years_of_experience INT NULL;"
mysql -u root -p drivelink -e "ALTER TABLE drivers_normalized ADD COLUMN IF NOT EXISTS previous_company VARCHAR(100) NULL;"
mysql -u root -p drivelink -e "ALTER TABLE drivers_normalized ADD COLUMN IF NOT EXISTS license_issue_date DATE NULL;"

echo Done! Missing columns have been added.
pause