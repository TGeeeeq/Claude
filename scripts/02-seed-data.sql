-- Insert default admin (username: admin, password: admin123 - CHANGE THIS!)
-- Password hash for "admin123" using bcrypt
INSERT INTO admins (username, password_hash, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@nechmerust.org');

-- Insert categories matching your Shopify store
INSERT INTO categories (name, slug, description, display_order) VALUES 
('Dřevovýroba', 'drevovyroba', 'Ručně vyráběné dřevěné výrobky a bylinkové přípravky z místních zdrojů', 1),
('Bylinkárna', 'bylinkarna', 'Bylinkové produkty a přírodní přípravky', 2),
('Výrobky našich přátel', 'vyrobky-nasich-pratel', 'Produkty od místních výrobců a přátel', 3);

-- Sample products from your store
INSERT INTO products (name, slug, description, price, stock_quantity, category_id, image_url) VALUES 
(
    'Taštička s ruční malbou osla Karlíka',
    'tasticka-s-rucni-malbou-osla-karlika',
    'Jedinečná plátěná taška s ručně malovaným portrétem osla Karlíka. Každý kus je originál vytvořený s láskou a péčí od talentované umělkyně Elianne.justtic. Taška je praktická i krásná - nostalgic umění pro každodenní použití. Rozměr: 36x40cm, zaběhané barvy na textil prát na 40°C',
    1000,
    5,
    1,
    '/images/products/karlicek-taska.jpg'
),
(
    'Dřevěná podložka pod hrnek',
    'drevena-podlozka-pod-hrnek',
    'Ručně vyrobená dřevěná podložka z místního dřeva. Přírodní povrchová úprava bezpečná pro potraviny.',
    150,
    20,
    1,
    '/images/products/podlozka.jpg'
),
(
    'Bylinkový čaj Louky',
    'bylinkovy-caj-louky',
    'Směs bylinek z našich luk. Bez pesticidů a chemie. 50g',
    120,
    15,
    2,
    '/images/products/caj-louky.jpg'
);
