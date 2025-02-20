<?php
// Set the response type to JSON.
header('Content-Type: application/json');

// Define the upload directory relative to this file (admin folder).
$uploadDir = '../blog-uploads/'; 

// Check if the file was uploaded successfully.
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    // Define allowed MIME types.
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (in_array($_FILES['image']['type'], $allowedTypes)) {
        $fileName = time() . "_" . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            // Return the URL relative to the site root.
            echo json_encode(['success' => true, 'url' => "blog-uploads/" . $fileName]);
            exit;
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid file type.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No file uploaded or an error occurred.']);
    exit;
}
?>
