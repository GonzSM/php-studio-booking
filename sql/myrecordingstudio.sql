CREATE DATABASE IF NOT EXISTS myrecordingstudio
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE myrecordingstudio;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    type VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255) NOT NULL,
    num_studios INT NOT NULL,
    cost_per_hour DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    location_id INT NOT NULL,
    booking_date DATE NOT NULL,
    start_time VARCHAR(8) NOT NULL,
    duration INT NOT NULL,
    status VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (location_id) REFERENCES locations(id)
);


-- ============================================================
-- SAMPLE DATA
-- ============================================================
-- Test accounts (all passwords shown below are the plain text;
-- the stored values are bcrypt hashes generated with password_hash):
--   Administrator -> admin@test.com  / admin123
--   Client        -> john@test.com   / john123
--   Client        -> maria@test.com  / maria123
--   Client        -> carlos@test.com / carlos123
-- ============================================================

INSERT INTO users (name, phone, email, password, type) VALUES
('Admin One',    '+61 412 345 678', 'admin@test.com',  '$2y$10$5LB3JBKaMcL0e/k7FIn9OuTwj3ycLZaOAD8dz1vYLsgLIwrL5hfF.', 'admin'),
('John Client',  '+61 498 765 432', 'john@test.com',   '$2y$10$kDTtzUf38CzvGikaqjbGS.2LEjFr6x2S.YpNht04lI23o4y6dIFl.', 'client'),
('Maria Studio', '+61 411 222 333', 'maria@test.com',  '$2y$10$1MVBiOExj0WibVS50tb6KuXzgp4a8gbJZjrNnpOmXzOT3uiTeJayO', 'client'),
('Carlos Sound', '+61 444 555 666', 'carlos@test.com', '$2y$10$7R2TLJOs.HQBb0xN1TaAZuKezrvIur6k8aEputBNjR3E3rMWnNM8m', 'client');

INSERT INTO locations (description, num_studios, cost_per_hour) VALUES
('Downtown Studio NYC',    3, 55.00),
('Sunset Blvd Studio LA',  2, 75.00),
('Brooklyn Sound Lab',     1, 40.00),
('Abbey Road Rooms',       4, 90.00);

-- Bookings use dates relative to "today" so they always stay meaningful:
--   - upcoming bookings (tomorrow / in 2 days) -> appear in current & future lists
--   - a past booking (yesterday)               -> appears in completed sessions / history
--   - an all-day booking today on Brooklyn (1 studio) -> makes that client "active now"
--     and that location "fully booked" during opening hours
INSERT INTO bookings (user_id, location_id, booking_date, start_time, duration, status) VALUES
(2, 1, CURDATE() + INTERVAL 1 DAY,  '14:00:00', 3,  'confirmed'),
(3, 2, CURDATE() + INTERVAL 2 DAY,  '10:00:00', 2,  'confirmed'),
(2, 1, CURDATE() - INTERVAL 1 DAY,  '11:00:00', 2,  'confirmed'),
(4, 3, CURDATE(),                   '10:00:00', 12, 'confirmed');
