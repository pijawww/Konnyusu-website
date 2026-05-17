<?php
// data/products.php

$products = [
    [
        'id'          => 1,
        'name'        => 'Caramel Tart Latte',
        'price'       => 32000,
        'category'    => 'coffee',
        'image'       => 'caramel-tart.jpeg',
        'description' => 'Espresso lembut dengan sirup karamel asli dan foam susu tebal. Manis di lidah, hangat di hati.',
        'sold'        => 238,
        'is_new'      => false,
        'is_best'     => true,
    ],
    [
        'id'          => 2,
        'name'        => 'Sea Salt Cream Latte',
        'price'       => 28000,
        'category'    => 'coffee',
        'image'       => 'oat-latte.jpg',
        'description' => 'Perpaduan espresso segar dengan krim garam laut yang menggoda. Sensasi gurih-manis yang unik.',
        'sold'        => 195,
        'is_new'      => false,
        'is_best'     => true,
    ],
    [
        'id'          => 3,
        'name'        => 'Signetone Oat Latte',
        'price'       => 30000,
        'category'    => 'coffee',
        'image'       => 'oat-latte.jpg',
        'description' => 'Single origin arabika dengan susu oat organik. Pilihan sehat tanpa kompromi rasa.',
        'sold'        => 140,
        'is_new'      => true,
        'is_best'     => false,
    ],
    [
        'id'          => 4,
        'name'        => 'Signature Series',
        'price'       => 45000,
        'category'    => 'coffee',
        'image'       => 'signature.jpg',
        'description' => 'Racikan eksklusif barista terbaik kami. Blend sempurna dari tiga varietas biji kopi premium.',
        'sold'        => 89,
        'is_new'      => false,
        'is_best'     => true,
    ],
    [
        'id'          => 5,
        'name'        => 'Matcha Oat Latte',
        'price'       => 28000,
        'category'    => 'non-coffee',
        'image'       => 'oat-latte.jpg',
        'description' => 'Matcha ceremonial grade dari Jepang dengan susu oat hangat. Sehat, segar, dan menenangkan.',
        'sold'        => 312,
        'is_new'      => false,
        'is_best'     => true,
    ],
    [
        'id'          => 6,
        'name'        => 'Hojicha Milk',
        'price'       => 25000,
        'category'    => 'tea',
        'image'       => 'savory.jpg',
        'description' => 'Teh hojicha panggang asal Kyoto dengan susu full cream. Aroma sangit yang khas dan memikat.',
        'sold'        => 167,
        'is_new'      => true,
        'is_best'     => false,
    ],
    [
        'id'          => 7,
        'name'        => 'Caramel Tart',
        'price'       => 18000,
        'category'    => 'dessert',
        'image'       => 'caramel-tart.jpeg',
        'description' => 'Pastri karamel lembut dengan lapisan custard krem yang kaya. Cocok menemani kopi pagi.',
        'sold'        => 201,
        'is_new'      => false,
        'is_best'     => false,
    ],
    [
        'id'          => 8,
        'name'        => 'Savory Croissant',
        'price'       => 22000,
        'category'    => 'dessert',
        'image'       => 'savory.jpg',
        'description' => 'Croissant renyah berlapis mentega premium dengan isian keju edam dan ham smoke.',
        'sold'        => 178,
        'is_new'      => false,
        'is_best'     => false,
    ],
];

/**
 * Helper: format rupiah
 */
function formatRupiah(int $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Helper: find product by id
 */
function findProduct(int $id, array $products): ?array {
    foreach ($products as $p) {
        if ($p['id'] === $id) return $p;
    }
    return null;
}
?>
