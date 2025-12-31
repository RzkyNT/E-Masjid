-- Tabel untuk booking Gedung Serba Guna
CREATE TABLE IF NOT EXISTS gsg_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_date DATE NOT NULL,
    nama VARCHAR(255) NOT NULL,
    no_telp VARCHAR(20) NOT NULL,
    alamat TEXT NOT NULL,
    keterangan TEXT NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Index untuk performa query
    INDEX idx_booking_date (booking_date),
    INDEX idx_status (status)
);

-- Sample data untuk testing
INSERT INTO gsg_bookings (booking_date, nama, no_telp, alamat, keterangan, status) VALUES
-- Booking yang sudah dikonfirmasi (akan muncul merah di kalender)
('2024-01-15', 'Ahmad Fauzi', '081234567890', 'Jl. Masjid No. 1', 'Acara pernikahan', 'confirmed'),
('2024-01-16', 'Ahmad Fauzi', '081234567890', 'Jl. Masjid No. 1', 'Acara pernikahan (hari ke-2)', 'confirmed'),
('2024-01-17', 'Ahmad Fauzi', '081234567890', 'Jl. Masjid No. 1', 'Acara pernikahan (hari ke-3)', 'confirmed'),
('2024-01-25', 'Siti Aminah', '082345678901', 'Jl. Pondok No. 5', 'Pengajian akbar', 'confirmed'),
('2024-02-14', 'Fatimah', '084567890123', 'Jl. Melati No. 3', 'Acara valentine islami', 'confirmed'),
('2024-02-15', 'Fatimah', '084567890123', 'Jl. Melati No. 3', 'Acara valentine islami (hari ke-2)', 'confirmed'),

-- Booking yang menunggu konfirmasi (tidak muncul di kalender publik, tapi ada di admin)
('2024-02-10', 'Budi Santoso', '083456789012', 'Jl. Raya No. 10', 'Seminar bisnis', 'pending'),
('2024-02-20', 'Rina Sari', '085678901234', 'Jl. Melur No. 7', 'Workshop kewirausahaan', 'pending'),
('2024-02-25', 'Dedi Kurniawan', '086789012345', 'Jl. Anggrek No. 12', 'Pelatihan komputer', 'pending');