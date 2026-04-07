<?php

/**
 * Template Name: Admin Page S3
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

    <main class="firstSectionPadding">
        <?php

        use Aws\S3\S3Client;

        $s3 = new S3Client([
            'version'     => 'latest',
            'region'      => 'eu-central-1',
            'credentials' => [
                'key' => S3_KEY,
                'secret' => S3_SECRET,
            ],
        ]);

        $bucketName = S3_BUCKET;

        try {
            // Отримуємо список файлів у S3
            $objects = $s3->listObjectsV2([
                'Bucket' => $bucketName,
            ]);

            $fileTree = [];

            // Формуємо дерево файлів
            if (!empty($objects['Contents'])) {
                foreach ($objects['Contents'] as $object) {
                    $pathParts = explode('/', $object['Key']);
                    $currentLevel = &$fileTree;

                    foreach ($pathParts as $part) {
                        if (!isset($currentLevel[$part])) {
                            $currentLevel[$part] = [];
                        }
                        $currentLevel = &$currentLevel[$part];
                    }
                }
            } else {
                echo "<p>Бакет порожній або немає доступу до файлів.</p>";
            }

            // Функція для рекурсивного виводу дерева файлів
            function renderTree($tree, $prefix = '') {
                echo '<ul>';
                foreach ($tree as $name => $subtree) {
                    echo '<li>' . $name;
                    if (!empty($subtree)) {
                        renderTree($subtree, $prefix . $name . '/');
                    }
                    echo '</li>';
                }
                echo '</ul>';
            }

        } catch (Exception $e) {
            echo "Помилка: " . $e->getMessage();
        }

        function clear_s3_bucket()
        {
            $s3 = new S3Client([
                'version'     => 'latest',
                'region'      => 'eu-central-1',
                'credentials' => [
                    'key'    => S3_KEY,
                    'secret' => S3_SECRET,
                ],
            ]);

            $bucketName = S3_BUCKET;

            try {
                // Отримуємо список файлів у бакеті
                $objects = $s3->listObjectsV2([
                    'Bucket' => $bucketName,
                ]);

                if (!empty($objects['Contents'])) {
                    // Формуємо масив ключів файлів для видалення
                    $keysToDelete = [];
                    foreach ($objects['Contents'] as $object) {
                        $keysToDelete[] = ['Key' => $object['Key']];
                    }

                    // Видаляємо всі файли одним запитом
                    $s3->deleteObjects([
                        'Bucket'  => $bucketName,
                        'Delete'  => ['Objects' => $keysToDelete],
                    ]);

                    echo "✅ Усі файли успішно видалені з бакету $bucketName.\n";
                } else {
                    echo "ℹ Бакет $bucketName вже порожній.\n";
                }
            } catch (AwsException $e) {
                echo "❌ Помилка очищення бакету: " . $e->getMessage() . "\n";
            }
        }

        /*clear_s3_bucket();*/

        ?>

        <style>
            .s3-explorer { font-family: Arial, sans-serif; max-width: 600px; margin: 20px auto; }
            .s3-explorer ul { list-style: none; padding-left: 20px; }
            .s3-explorer li { padding: 5px 0; cursor: pointer; }
            .s3-explorer li:hover { font-weight: bold; }
        </style>

        <div class="s3-explorer">
            <h2>📂 AWS S3 File Explorer</h2>
            <?php renderTree($fileTree); ?>
        </div>

    </main>

<?php
get_footer();
