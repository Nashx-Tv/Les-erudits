CREATE TABLE IF NOT EXISTS display_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    screen_code VARCHAR(100) NOT NULL UNIQUE,
    screen_name VARCHAR(150) NOT NULL,
    template_title VARCHAR(150) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO display_assignments (screen_code, screen_name, template_title)
VALUES
('hall_admin', 'Hall administration', ''),
('vie_scolaire_cse', 'Vie scolaire C.S.E', ''),
('ateliers_w', 'Ateliers W', ''),
('physique_s', 'Physique S', ''),
('refectoire', 'Réfectoire', '')
ON DUPLICATE KEY UPDATE screen_name = VALUES(screen_name);
