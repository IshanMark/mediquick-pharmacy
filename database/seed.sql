-- Sample data so the site has something to show. Import AFTER schema.sql.
-- Passwords below are all: Password123
USE mediquick_db;

INSERT INTO users (role, full_name, email, phone, password_hash, address, city) VALUES
 ('admin', 'Nimal Perera',  'admin@mediquick.lk', '0771234567',
  '$2y$12$FR8I7kyIILjXd9DEexWz.OiuKgLkBYA.fv3hbflAUTjegGtYeLfBi', 'No. 12, Kandy Road', 'Kurunegala'),
 ('staff', 'Kamal Silva',   'staff@mediquick.lk', '0772345678',
  '$2y$12$FR8I7kyIILjXd9DEexWz.OiuKgLkBYA.fv3hbflAUTjegGtYeLfBi', 'No. 12, Kandy Road', 'Kurunegala'),
 ('customer', 'Sanduni Fernando', 'customer@example.com', '0773456789',
  '$2y$12$FR8I7kyIILjXd9DEexWz.OiuKgLkBYA.fv3hbflAUTjegGtYeLfBi', '45 Lake Road', 'Kurunegala');

INSERT INTO products (category_id, name, generic_name, brand, description, dosage_guidelines, safety_info, price, stock_qty, reorder_level, requires_prescription, expiry_date) VALUES
 (1, 'Amoxicillin 500mg', 'Amoxicillin', 'Cipla',
  'Broad-spectrum antibiotic for bacterial infections.',
  'One capsule every 8 hours for 5-7 days, or as directed by your doctor.',
  'Complete the full course even if you feel better. Inform your pharmacist of any penicillin allergy.',
  450.00, 60, 15, 1, '2027-06-30'),
 (1, 'Amlodipine 5mg', 'Amlodipine', 'Hemas',
  'Calcium channel blocker used to treat high blood pressure.',
  'One tablet daily, same time each day.',
  'May cause dizziness. Avoid grapefruit juice.',
  320.00, 40, 10, 1, '2027-03-15'),
 (2, 'Paracetamol 500mg (20s)', 'Paracetamol', 'GSK',
  'Pain reliever and fever reducer for everyday aches.',
  'One to two tablets every 4-6 hours. Do not exceed 8 tablets in 24 hours.',
  'Avoid alcohol. Do not combine with other paracetamol-containing products.',
  120.00, 200, 30, 0, '2027-12-31'),
 (2, 'Cetirizine 10mg (10s)', 'Cetirizine', 'Sunshine',
  'Antihistamine for allergy relief, sneezing and itchy eyes.',
  'One tablet daily.',
  'May cause mild drowsiness in some people.',
  180.00, 150, 25, 0, '2027-09-30'),
 (3, 'Vitamin C 1000mg (30s)', null, 'Nature\'s Way',
  'Daily immune support supplement.',
  'One tablet daily with food.',
  'Discontinue if stomach upset occurs.',
  850.00, 80, 20, 0, '2027-11-30'),
 (3, 'Multivitamin Complete (30s)', null, 'Centrum',
  'Daily multivitamin covering essential nutrients.',
  'One tablet daily with breakfast.',
  'Keep out of reach of children.',
  1250.00, 55, 15, 0, '2027-10-15'),
 (4, 'Aloe Vera Gel 200ml', null, 'Nivea',
  'Soothing gel for sunburn and dry skin.',
  'Apply to affected area 2-3 times daily.',
  'For external use only.',
  650.00, 90, 20, 0, '2028-01-31'),
 (4, 'Baby Diaper Rash Cream 50g', null, 'Sudocrem',
  'Protective barrier cream for diaper rash.',
  'Apply a thin layer at each diaper change.',
  'For external use only. Discontinue if irritation occurs.',
  980.00, 65, 15, 0, '2027-08-20');

INSERT INTO articles (author_id, title, slug, body, published_at) VALUES
 (2, 'Five Tips for Taking Antibiotics Safely',
  'antibiotics-safety-tips',
  'Antibiotics are powerful medicines, but only when used correctly.\n\n1. Always complete the full course, even if you feel better after a few days.\n2. Take doses at evenly spaced times to keep a steady level in your body.\n3. Never share antibiotics with someone else, even for similar symptoms.\n4. Tell your pharmacist about any known drug allergies before starting a new course.\n5. Store medicines away from direct sunlight and out of reach of children.\n\nIf you experience a rash, swelling, or difficulty breathing after taking any medicine, seek medical attention immediately.',
  NOW()),
 (2, 'Managing Seasonal Allergies at Home',
  'seasonal-allergies-at-home',
  'Seasonal allergies can be managed with a combination of medication and simple home habits.\n\nKeep windows closed on high-pollen days, shower after spending time outdoors, and wash bedding weekly in hot water. Over-the-counter antihistamines like cetirizine can help with sneezing and itchy eyes, but speak to our pharmacists if symptoms persist for more than two weeks.',
  NOW());

INSERT INTO testimonials (customer_id, rating, message, is_approved) VALUES
 (3, 5, 'Fast delivery and the pharmacist called to confirm my prescription before shipping. Really reassuring.', 1),
 (3, 4, 'Good range of wellness products, prices are fair compared to the shop near my house.', 1);

-- To make these accounts usable immediately, log in with:
--   admin@mediquick.lk    / Password123
--   staff@mediquick.lk    / Password123
--   customer@example.com  / Password123
