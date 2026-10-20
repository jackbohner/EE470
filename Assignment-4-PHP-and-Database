
-- EE 470 Database Assignment
-- Create two tables: sensor_register and sensor_data
-- Register 5 sensors, insert 4 readings per sensor,
-- create a combined VIEW, and test the foreign key constraint.


-- 1. CREATE SENSOR TABLES

-- Create the sensor_register table to store registered sensor information
-- node_name is the primary key and can contain up to 10 characters
-- manufacturer can contain up to 10 characters
-- longitude and latitude can contain up to 15 digits total

CREATE TABLE sensor_register (
    node_name VARCHAR(10) PRIMARY KEY,
    manufacturer VARCHAR(10) NOT NULL,
    longitude DECIMAL(15,10) NOT NULL,
    latitude DECIMAL(15,10) NOT NULL
);

-- Create the sensor_data table to store sensor readings
-- time_received stores the date and time of each reading
-- temperature must be between -10 and 100 degrees Celsius
-- humidity must be between 0 and 100
-- The combined primary key allows multiple readings from each sensor
-- The foreign key ensures that only registered sensors can submit data

CREATE TABLE sensor_data (
    node_name VARCHAR(10) NOT NULL,
    time_received DATETIME NOT NULL,
    temperature DECIMAL(6,2) NOT NULL,
    humidity DECIMAL(6,2) NOT NULL,
    PRIMARY KEY (node_name, time_received),
    FOREIGN KEY (node_name)
        REFERENCES sensor_register(node_name),
    CHECK (temperature BETWEEN -10 AND 100),
    CHECK (humidity BETWEEN 0 AND 100)
);


-- 2. REGISTER FIVE SENSORS

-- Register five sensors with different manufacturers
-- Sensor names follow the required node_1, node_2, etc. format
-- Longitude and latitude values are example locations

INSERT INTO sensor_register
(node_name, manufacturer, longitude, latitude)
VALUES
('node_1', 'Bosch', -122.6742000000, 38.3396000000),
('node_2', 'Honeywell', -122.6751000000, 38.3401000000),
('node_3', 'Siemens', -122.6760000000, 38.3405000000),
('node_4', 'Sensirion', -122.6772000000, 38.3410000000),
('node_5', 'Omron', -122.6781000000, 38.3415000000);

-- Display all registered sensors to verify the entries

SELECT * FROM sensor_register;


-- 3. INSERT SENSOR READINGS

-- Each sensor sends four readings at 30-minute intervals
-- Starting date and time: October 1, 2022 at 11:00 AM
-- Reading times: 11:00, 11:30, 12:00, and 12:30
-- Five sensors with four readings each gives 20 total readings

INSERT INTO sensor_data
(node_name, time_received, temperature, humidity)
VALUES
-- Sensor 1 readings
('node_1', '2022-10-01 11:00:00', 22.5, 45),
('node_1', '2022-10-01 11:30:00', 23.0, 46),
('node_1', '2022-10-01 12:00:00', 23.5, 47),
('node_1', '2022-10-01 12:30:00', 24.0, 48),

-- Sensor 2 readings
('node_2', '2022-10-01 11:00:00', 21.0, 50),
('node_2', '2022-10-01 11:30:00', 21.5, 51),
('node_2', '2022-10-01 12:00:00', 22.0, 52),
('node_2', '2022-10-01 12:30:00', 22.5, 53),

-- Sensor 3 readings
('node_3', '2022-10-01 11:00:00', 25.0, 40),
('node_3', '2022-10-01 11:30:00', 25.5, 41),
('node_3', '2022-10-01 12:00:00', 26.0, 42),
('node_3', '2022-10-01 12:30:00', 26.5, 43),

-- Sensor 4 readings
('node_4', '2022-10-01 11:00:00', 19.0, 60),
('node_4', '2022-10-01 11:30:00', 19.5, 61),
('node_4', '2022-10-01 12:00:00', 20.0, 62),
('node_4', '2022-10-01 12:30:00', 20.5, 63),

-- Sensor 5 readings
('node_5', '2022-10-01 11:00:00', 27.0, 35),
('node_5', '2022-10-01 11:30:00', 27.5, 36),
('node_5', '2022-10-01 12:00:00', 28.0, 37),
('node_5', '2022-10-01 12:30:00', 28.5, 38);

-- Display all sensor readings to verify the entries

SELECT * FROM sensor_data;


-- 4. CREATE THE COMBINED VIEW

-- Create a VIEW called sensor_combined
-- INNER JOIN combines sensor_data and sensor_register
-- The tables are matched using the node_name field
-- The VIEW displays sensor readings along with manufacturer
-- and location information in one combined result

CREATE VIEW sensor_combined AS
SELECT
    sensor_data.node_name,
    sensor_register.manufacturer,
    sensor_data.time_received,
    sensor_data.temperature,
    sensor_data.humidity,
    sensor_register.longitude,
    sensor_register.latitude
FROM sensor_data
INNER JOIN sensor_register
ON sensor_data.node_name = sensor_register.node_name;

-- Display the combined VIEW
-- The result should contain 20 rows

SELECT * FROM sensor_combined;


-- 5. TEST AN UNREGISTERED SENSOR

-- Attempt to insert a reading from node_6
-- Only node_1 through node_5 are registered
-- The foreign key should prevent this insertion
-- Expected result: Error 1452, foreign key constraint fails
-- This proves that unregistered sensors cannot submit data

-- Run the following INSERT separately in phpMyAdmin
-- It is intentionally expected to fail

INSERT INTO sensor_data
(node_name, time_received, temperature, humidity)
VALUES
('node_6', '2022-10-01 11:00:00', 25, 50);

-- Verify that node_6 was not added to sensor_data
-- The query should return zero rows

SELECT *
FROM sensor_data
WHERE node_name = 'node_6';


-- 6. VERIFY DATABASE REQUIREMENTS

-- Display all registered sensors
-- Expected result: 5 registered sensors

SELECT * FROM sensor_register;

-- Display all sensor readings
-- Expected result: 20 readings

SELECT * FROM sensor_data;

-- Count the number of registered sensors
-- Expected result: 5

SELECT COUNT(*) AS total_sensors
FROM sensor_register;

-- Count the number of readings from each sensor
-- Expected result: 4 readings for each sensor

SELECT node_name, COUNT(*) AS reading_count
FROM sensor_data
GROUP BY node_name;

-- Display all readings in chronological order
-- Readings should start on October 1, 2022 at 11:00 AM
-- Each sensor should have readings every 30 minutes

SELECT *
FROM sensor_data
ORDER BY node_name, time_received;

-- Verify the foreign key relationship
-- The table definition should show the reference
-- from sensor_data.node_name to sensor_register.node_name

SHOW CREATE TABLE sensor_data;

-- Display the combined VIEW
-- Expected result: 20 rows containing information
-- from both sensor_data and sensor_register

SELECT * FROM sensor_combined;


-- 7. FINAL VERIFICATION

-- The database contains two tables:
-- sensor_register and sensor_data
--
-- Five sensors are registered with different manufacturers
-- Each sensor has four readings at 30-minute intervals
-- The first reading is October 1, 2022 at 11:00 AM
-- Temperature values are restricted to -10 through 100
-- Humidity values are restricted to 0 through 100
-- A foreign key prevents unregistered sensors from submitting data
-- The sensor_combined VIEW joins both tables into one result
