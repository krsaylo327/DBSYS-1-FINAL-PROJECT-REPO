<?php
// =============================================
// DATABASE CONNECTION FILE (FULLY FIXED)
// =============================================

$host = 'localhost';
$dbname = 'barangay_system';
$username = 'root';
$password = '';

// Create connection WITHOUT selecting database first
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    // Database created or already exists
} else {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbname);

// =============================================
// CREATE TABLES DIRECTLY IN PHP (NO SQL FILE NEEDED)
// =============================================

// Check if tables exist, if not create them
$table_check = $conn->query("SHOW TABLES LIKE 'households'");
if ($table_check->num_rows == 0) {
    
    // Create households table
    $conn->query("CREATE TABLE households (
        household_id INT AUTO_INCREMENT PRIMARY KEY,
        household_number VARCHAR(50) NOT NULL UNIQUE,
        purok_zone VARCHAR(50) NOT NULL,
        street_address VARCHAR(200) NOT NULL,
        source VARCHAR(100) DEFAULT 'Barangay Census',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create barangay_staff table
    $conn->query("CREATE TABLE barangay_staff (
        staff_id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        position VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        contact_number VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE
    )");
    
    // Create barangay_residents table (FIXED: no typo)
    $conn->query("CREATE TABLE barangay_residents (
        resident_id INT AUTO_INCREMENT PRIMARY KEY,
        household_id INT NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        birth_date DATE NOT NULL,
        gender ENUM('Male', 'Female', 'Other') NOT NULL,
        contact_number VARCHAR(20),
        date_registered TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (household_id) REFERENCES households(household_id) ON DELETE CASCADE
    )");
    
    // Create certificates table
    $conn->query("CREATE TABLE certificates (
        certificate_id INT AUTO_INCREMENT PRIMARY KEY,
        certificate_name VARCHAR(100) NOT NULL UNIQUE,
        base_fee DECIMAL(10,2) NOT NULL DEFAULT 50.00,
        description TEXT,
        is_active BOOLEAN DEFAULT TRUE
    )");
    
    // Create certificate_request table
    $conn->query("CREATE TABLE certificate_request (
        request_id INT AUTO_INCREMENT PRIMARY KEY,
        resident_id INT NOT NULL,
        certificate_id INT NOT NULL,
        staff_id INT NULL,
        purpose VARCHAR(255) NOT NULL,
        status ENUM('Pending', 'Approved', 'Rejected', 'Released') DEFAULT 'Pending',
        requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        resolved_at TIMESTAMP NULL,
        FOREIGN KEY (resident_id) REFERENCES barangay_residents(resident_id) ON DELETE CASCADE,
        FOREIGN KEY (certificate_id) REFERENCES certificates(certificate_id) ON DELETE CASCADE,
        FOREIGN KEY (staff_id) REFERENCES barangay_staff(staff_id) ON DELETE SET NULL
    )");
    
    // Create resident_accounts table
    $conn->query("CREATE TABLE resident_accounts (
        account_id INT AUTO_INCREMENT PRIMARY KEY,
        resident_id INT NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        role ENUM('Resident', 'Staff', 'Admin') DEFAULT 'Resident',
        account_status ENUM('Active', 'Inactive', 'Suspended') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        approved_by_staff_id INT NULL,
        FOREIGN KEY (resident_id) REFERENCES barangay_residents(resident_id) ON DELETE CASCADE,
        FOREIGN KEY (approved_by_staff_id) REFERENCES barangay_staff(staff_id) ON DELETE SET NULL
    )");
    
    // Create INDEXES
    $conn->query("CREATE INDEX idx_residents_household ON barangay_residents(household_id)");
    $conn->query("CREATE INDEX idx_residents_name ON barangay_residents(last_name, first_name)");
    $conn->query("CREATE INDEX idx_requests_resident ON certificate_request(resident_id)");
    $conn->query("CREATE INDEX idx_requests_certificate ON certificate_request(certificate_id)");
    $conn->query("CREATE INDEX idx_requests_status ON certificate_request(status)");
    $conn->query("CREATE INDEX idx_requests_staff ON certificate_request(staff_id)");
    
    // Create STORED PROCEDURE
    $conn->query("DROP PROCEDURE IF EXISTS ProcessCertificateRequest");
    $conn->query("CREATE PROCEDURE ProcessCertificateRequest(
        IN p_request_id INT,
        IN p_staff_id INT,
        IN p_status VARCHAR(20)
    )
    BEGIN
        DECLARE v_resident_id INT;
        DECLARE v_certificate_id INT;
        DECLARE v_certificate_name VARCHAR(100);
        DECLARE v_fee DECIMAL(10,2);
        
        SELECT resident_id, certificate_id 
        INTO v_resident_id, v_certificate_id
        FROM certificate_request
        WHERE request_id = p_request_id;
        
        SELECT certificate_name, base_fee 
        INTO v_certificate_name, v_fee
        FROM certificates
        WHERE certificate_id = v_certificate_id;
        
        UPDATE certificate_request 
        SET 
            staff_id = p_staff_id,
            status = p_status,
            resolved_at = NOW()
        WHERE request_id = p_request_id;
        
        SELECT 
            'Request processed successfully!' AS message,
            v_certificate_name AS certificate_name,
            v_fee AS fee,
            p_status AS new_status;
    END");
    
    // Create VIEWS
    $conn->query("DROP VIEW IF EXISTS certificate_request_summary");
    $conn->query("CREATE VIEW certificate_request_summary AS
        SELECT 
            cr.request_id,
            CONCAT(r.first_name, ' ', r.last_name) AS resident_name,
            c.certificate_name,
            c.base_fee AS fee,
            cr.purpose,
            cr.status,
            cr.requested_at,
            cr.resolved_at,
            CONCAT(s.first_name, ' ', s.last_name) AS processed_by
        FROM certificate_request cr
        INNER JOIN barangay_residents r ON cr.resident_id = r.resident_id
        INNER JOIN certificates c ON cr.certificate_id = c.certificate_id
        LEFT JOIN barangay_staff s ON cr.staff_id = s.staff_id
        ORDER BY cr.requested_at DESC");
    
    $conn->query("DROP VIEW IF EXISTS resident_dashboard_summary");
    $conn->query("CREATE VIEW resident_dashboard_summary AS
        SELECT 
            r.resident_id,
            CONCAT(r.first_name, ' ', r.last_name) AS full_name,
            h.household_number,
            h.purok_zone,
            h.street_address,
            COUNT(DISTINCT cr.request_id) AS total_requests,
            COUNT(DISTINCT CASE WHEN cr.status = 'Pending' THEN cr.request_id END) AS pending_requests,
            COUNT(DISTINCT CASE WHEN cr.status = 'Approved' THEN cr.request_id END) AS approved_requests,
            COUNT(DISTINCT CASE WHEN cr.status = 'Released' THEN cr.request_id END) AS released_requests,
            MAX(cr.requested_at) AS last_request_date
        FROM barangay_residents r
        LEFT JOIN households h ON r.household_id = h.household_id
        LEFT JOIN certificate_request cr ON r.resident_id = cr.resident_id
        GROUP BY r.resident_id, h.household_number, h.purok_zone, h.street_address");
    
    // Insert SEED DATA
    $conn->query("INSERT INTO households (household_number, purok_zone, street_address, source) VALUES
        ('001', 'Purok 1', '123 Mabini Street', 'Barangay Census 2025'),
        ('002', 'Purok 1', '124 Mabini Street', 'Barangay Census 2025'),
        ('003', 'Purok 2', '101 Rizal Avenue', 'Barangay Census 2025'),
        ('004', 'Purok 2', '102 Rizal Avenue', 'Barangay Census 2025'),
        ('005', 'Purok 3', '45 Bonifacio Street', 'Barangay Census 2025'),
        ('006', 'Purok 3', '46 Bonifacio Street', 'Barangay Census 2025'),
        ('007', 'Purok 4', '78 Luna Street', 'Barangay Census 2025'),
        ('008', 'Purok 4', '79 Luna Street', 'Barangay Census 2025'),
        ('009', 'Purok 5', '12 Del Pilar Street', 'Barangay Census 2025'),
        ('010', 'Purok 5', '13 Del Pilar Street', 'Barangay Census 2025')");
    
    $conn->query("INSERT INTO barangay_staff (first_name, last_name, position, username, password_hash, contact_number) VALUES
        ('Juan', 'Santos', 'Barangay Captain', 'captain.santos', SHA2('admin123', 256), '09171234567'),
        ('Maria', 'Cruz', 'Barangay Secretary', 'secretary.cruz', SHA2('secretary123', 256), '09181234568'),
        ('Pedro', 'Reyes', 'Barangay Treasurer', 'treasurer.reyes', SHA2('treasurer123', 256), '09191234569')");
    
    $conn->query("INSERT INTO certificates (certificate_name, base_fee, description) VALUES
        ('Barangay Clearance', 50.00, 'Official certification of residency and good moral character'),
        ('Certificate of Residency', 50.00, 'Proof of residence in the barangay'),
        ('Certificate of Indigency', 30.00, 'Certification for financial assistance programs'),
        ('Barangay Business Clearance', 100.00, 'Required for business permits and licenses'),
        ('Certificate of Good Moral Character', 50.00, 'For employment and school applications')");
    
    $conn->query("INSERT INTO barangay_residents (household_id, first_name, last_name, birth_date, gender, contact_number) VALUES
        (1, 'Juan', 'Dela Cruz', '1990-05-15', 'Male', '09171234567'),
        (1, 'Maria', 'Dela Cruz', '1992-08-22', 'Female', '09181234568'),
        (1, 'Jose', 'Dela Cruz', '2015-03-10', 'Male', '09191234569'),
        (2, 'Pedro', 'Reyes', '1985-11-30', 'Male', '09201234570'),
        (2, 'Ana', 'Reyes', '1988-07-12', 'Female', '09211234571'),
        (3, 'Carlos', 'Santos', '1975-01-05', 'Male', '09221234572'),
        (3, 'Elena', 'Santos', '1978-09-18', 'Female', '09231234573'),
        (4, 'Ramon', 'Garcia', '1995-04-25', 'Male', '09241234574'),
        (4, 'Liza', 'Garcia', '1997-06-14', 'Female', '09251234575'),
        (5, 'Manuel', 'Fernandez', '1980-02-20', 'Male', '09261234576'),
        (5, 'Teresa', 'Fernandez', '1983-10-05', 'Female', '09271234577'),
        (6, 'Gregorio', 'Mendoza', '1992-12-01', 'Male', '09281234578'),
        (6, 'Sofia', 'Mendoza', '1994-03-28', 'Female', '09291234579'),
        (7, 'Andres', 'Bonifacio', '1970-08-15', 'Male', '09301234580'),
        (7, 'Julia', 'Bonifacio', '1973-11-22', 'Female', '09311234581'),
        (8, 'Emilio', 'Aguinaldo', '1988-05-09', 'Male', '09321234582'),
        (8, 'Hilaria', 'Aguinaldo', '1990-07-19', 'Female', '09331234583'),
        (9, 'Jose', 'Rizal', '1982-06-19', 'Male', '09341234584'),
        (9, 'Leonor', 'Rizal', '1985-12-30', 'Female', '09351234585'),
        (10, 'Marcelo', 'Del Pilar', '1977-09-12', 'Male', '09361234586')");
    
    $conn->query("INSERT INTO resident_accounts (resident_id, username, password_hash, email, role) VALUES
        (1, 'juan.delacruz', SHA2('password123', 256), 'juan.delacruz@email.com', 'Resident'),
        (2, 'maria.delacruz', SHA2('password123', 256), 'maria.delacruz@email.com', 'Resident'),
        (5, 'ana.reyes', SHA2('password123', 256), 'ana.reyes@email.com', 'Resident')");
    
    $conn->query("INSERT INTO certificate_request (resident_id, certificate_id, staff_id, purpose, status, requested_at, resolved_at) VALUES
        (1, 1, 1, 'Employment application', 'Approved', '2026-01-15 10:00:00', '2026-01-16 14:30:00'),
        (2, 2, 1, 'School enrollment requirement', 'Released', '2026-01-20 09:30:00', '2026-01-21 11:00:00'),
        (3, 3, 2, 'Medical financial assistance', 'Pending', '2026-01-25 14:15:00', NULL),
        (4, 4, 2, 'Business permit renewal', 'Approved', '2026-02-01 08:45:00', '2026-02-02 16:00:00'),
        (5, 1, 1, 'Job interview requirement', 'Rejected', '2026-02-05 11:20:00', '2026-02-06 09:00:00'),
        (6, 2, 3, 'Government ID application', 'Pending', '2026-02-10 13:00:00', NULL),
        (7, 5, 1, 'College admission requirement', 'Approved', '2026-02-15 10:30:00', '2026-02-16 15:45:00'),
        (8, 1, 3, 'Work abroad requirement', 'Pending', '2026-02-20 09:00:00', NULL),
        (9, 2, 1, 'Voter registration requirement', 'Released', '2026-03-01 14:00:00', '2026-03-02 10:00:00'),
        (10, 3, 2, 'Financial assistance program', 'Approved', '2026-03-05 08:30:00', '2026-03-06 11:30:00'),
        (11, 4, 1, 'Small business registration', 'Pending', '2026-03-10 13:45:00', NULL),
        (12, 5, 3, 'Employment requirement', 'Released', '2026-03-15 10:00:00', '2026-03-16 09:30:00'),
        (13, 1, 2, 'Barangay ID application', 'Approved', '2026-03-20 11:15:00', '2026-03-21 14:00:00'),
        (14, 2, 1, 'Senior citizen benefits', 'Pending', '2026-04-01 09:00:00', NULL),
        (15, 3, 3, 'Medical assistance', 'Released', '2026-04-05 15:30:00', '2026-04-06 10:00:00')");
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Function to sanitize input
function sanitize($input) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($input))));
}

// Function to display success/error messages
function showMessage($text, $type = 'success') {
    $class = ($type == 'success') ? 'alert-success' : 'alert-danger';
    return '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">
            ' . $text . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}

// Function to get status badge
function getStatusBadge($status) {
    $colors = [
        'Pending' => 'bg-warning',
        'Approved' => 'bg-success',
        'Rejected' => 'bg-danger',
        'Released' => 'bg-info'
    ];
    $color = isset($colors[$status]) ? $colors[$status] : 'bg-secondary';
    return '<span class="badge ' . $color . '">' . $status . '</span>';
}
?>
