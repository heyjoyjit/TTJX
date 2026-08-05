<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
requireAdmin();

$destination_id = isset($_GET['destination_id']) ? intval($_GET['destination_id']) : 0;
if (!$destination_id) exit;

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM package_sizes WHERE destination_id = ? ORDER BY sort_order");
$stmt->execute([$destination_id]);
$sizes = $stmt->fetchAll();

foreach ($sizes as $size) {
    echo '<div class="bg-white p-4 rounded shadow mb-4">';
    echo '<div class="flex justify-between items-center">';
    echo '<h2 class="text-xl font-semibold">' . escape($size['size_name']) . ' (ID: ' . $size['id'] . ')</h2>';
    echo '<button class="delete-size text-red-500 hover:text-red-700" data-id="' . $size['id'] . '"><i class="fas fa-trash"></i></button>';
    echo '</div>';

    // Add Category form
    echo '<form class="add-category-form mt-2 flex items-center gap-2" data-size-id="' . $size['id'] . '">';
    echo '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
    echo '<input type="text" name="category_name" placeholder="Category Name (e.g., Standard)" required class="border border-gray-300 rounded p-1 flex-1">';
    echo '<button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-sm">Add Category</button>';
    echo '</form>';

    // List categories
    $stmtCat = $db->prepare("SELECT * FROM package_categories WHERE size_id = ?");
    $stmtCat->execute([$size['id']]);
    $categories = $stmtCat->fetchAll();
    if ($categories) {
        echo '<div class="mt-4 space-y-2">';
        foreach ($categories as $cat) {
            echo '<div class="border-l-4 border-blue-400 pl-4 py-2 bg-gray-50 rounded">';
            echo '<div class="flex justify-between items-center">';
            echo '<span class="font-medium">' . escape($cat['category_name']) . '</span>';
            echo '<div>';
            echo '<button class="add-package text-blue-500 hover:text-blue-700 mr-2" data-category-id="' . $cat['id'] . '"><i class="fas fa-plus"></i> Add Package</button>';
            echo '<button class="delete-category text-red-500 hover:text-red-700" data-id="' . $cat['id'] . '"><i class="fas fa-trash"></i></button>';
            echo '</div>';
            echo '</div>';
            // List packages under this category
            $stmtPkg = $db->prepare("SELECT id, title, price FROM package_details WHERE category_id = ?");
            $stmtPkg->execute([$cat['id']]);
            $packages = $stmtPkg->fetchAll();
            if ($packages) {
                echo '<ul class="ml-4 mt-2 list-disc">';
                foreach ($packages as $pkg) {
                    echo '<li>';
                    echo escape($pkg['title']) . ' - $' . number_format($pkg['price'], 2);
                    echo ' <button class="edit-package text-yellow-500 hover:text-yellow-700 ml-2" data-package-id="' . $pkg['id'] . '" data-category-id="' . $cat['id'] . '"><i class="fas fa-edit"></i></button>';
                    echo ' <button class="delete-package text-red-500 hover:text-red-700" data-id="' . $pkg['id'] . '"><i class="fas fa-trash"></i></button>';
                    echo '</li>';
                }
                echo '</ul>';
            } else {
                echo '<div class="text-gray-500 text-sm mt-1">No packages yet.</div>';
            }
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<div class="text-gray-500 text-sm mt-2">No categories yet.</div>';
    }
    echo '</div>';
}
