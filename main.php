<?php
// Handle API requests
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$host = 'sql12.freesqldatabase.com';
$dbname = 'sql12775590';
$username = 'sql12775590';
$password = 'uIKK5R544w';
$port = 3306;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    if (isset($_GET['action'])) {
        echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
        exit;
    }
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action) {
    switch ($action) {
        case 'get_templates':
            $stmt = $pdo->query('SELECT * FROM templates');
            $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($templates);
            break;

        case 'add_template':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = uniqid();
            $title = $data['title'] ?? '';
            $image = $data['image'] ?? '';
            $description = $data['description'] ?? '';

            if (empty($title) || empty($image)) {
                echo json_encode(['error' => 'Title and image are required']);
                exit;
            }

            try {
                $stmt = $pdo->prepare('INSERT INTO templates (id, title, image, description) VALUES (?, ?, ?, ?)');
                $stmt->execute([$id, $title, $image, $description]);
                echo json_encode(['success' => 'Template added successfully', 'id' => $id]);
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    echo json_encode(['error' => 'A template with this title already exists']);
                } else {
                    echo json_encode(['error' => 'Failed to add template: ' . $e->getMessage()]);
                }
            }
            break;

        case 'update_template':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? '';
            $title = $data['title'] ?? '';
            $image = $data['image'] ?? '';
            $description = $data['description'] ?? '';

            if (empty($id) || empty($title) || empty($image)) {
                echo json_encode(['error' => 'ID, title, and image are required']);
                exit;
            }

            try {
                $stmt = $pdo->prepare('UPDATE templates SET title = ?, image = ?, description = ? WHERE id = ?');
                $stmt->execute([$title, $image, $description, $id]);
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => 'Template updated successfully']);
                } else {
                    echo json_encode(['error' => 'Template not found']);
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    echo json_encode(['error' => 'A template with this title already exists']);
                } else {
                    echo json_encode(['error' => 'Failed to update template: ' . $e->getMessage()]);
                }
            }
            break;

        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    exit;
}

// If no action, render the HTML
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template Management System</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @font-face {
            font-family: 'Poppins';
            src: url('data:font/woff2;base64,...') format('woff2'); /* Inline Poppins font (placeholder) */
            font-weight: 400 700;
        }

        body {
            background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
            min-height: 100vh;
            padding: 10px;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 10px;
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            padding: 30px 0;
            background: transparent;
            border-radius: 20px;
            animation: headerFadeIn 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes headerFadeIn {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .header-content:hover { transform: translateY(-2px); }
        .header-content:active { transform: translateY(0); }

        .header-icon {
            font-size: 32px;
            background: linear-gradient(135deg, #1b4d3e 0%, #2ecc71 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header h1 {
            font-size: clamp(32px, 5vw, 42px);
            background: linear-gradient(135deg, #1b4d3e 0%, #2ecc71 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header h1::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #2ecc71 0%, #1b4d3e 100%);
            border-radius: 2px;
            transition: width 0.3s ease;
        }

        .header:hover h1::after { width: 100px; }

        .close-btn {
            position: fixed;
            right: 30px;
            top: 30px;
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #1a365d;
            cursor: pointer;
            border: 2px solid rgba(26, 54, 93, 0.1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .close-btn:hover {
            background: #ffffff;
            transform: translateY(-2px) rotate(90deg);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            color: #2563eb;
        }

        .close-btn::before { content: "✕"; font-weight: 600; }

        .slider-container {
            position: relative;
            background: #808080;
            padding: 60px;
            border-radius: 15px;
            margin: 0 auto;
            width: 100%;
            max-width: 800px;
            min-height: 400px;
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .templates-slider {
            display: flex;
            gap: 20px;
            width: 100%;
            overflow-x: hidden;
            scroll-behavior: smooth;
            scrollbar-width: none;
            scroll-snap-type: x mandatory;
        }

        .templates-slider::-webkit-scrollbar { display: none; }

        .template-group {
            flex: 0 0 100%;
            display: flex;
            gap: 20px;
            scroll-snap-align: start;
        }

        .template-slide { flex: 1; display: flex; align-items: center; justify-content: center; }

        .template-card {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            width: 100%;
            position: relative;
            cursor: pointer;
            height: 250px;
        }

        .template-image {
            width: 100%;
            height: 200px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            object-fit: cover;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .template-actions {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            gap: 15px;
            opacity: 0;
            transition: all 0.3s ease;
            background: rgba(0, 0, 0, 0.7);
            padding: 15px 20px;
            border-radius: 8px;
            backdrop-filter: blur(4px);
        }

        .template-card:hover .template-actions {
            opacity: 1;
            transform: translate(-50%, calc(-50% - 20px));
        }

        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: white;
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .add-template-card {
            border: 2px dashed rgba(255, 255, 255, 0.5);
            border-radius: 8px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            height: 200px;
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .add-template-card:hover {
            border-color: white;
            background: rgba(255, 255, 255, 0.15);
        }

        .add-template-icon {
            font-size: 64px;
            color: white;
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .nav-button {
            position: absolute;
            top: calc(50% - 30px);
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .nav-button:hover { background: white; box-shadow: 0 3px 8px rgba(0,0,0,0.2); }
        .nav-button.prev { left: 10px; }
        .nav-button.next { right: 10px; }
        .nav-button i { font-size: 20px; color: #333; }

        .template-title {
            font-size: clamp(16px, 3vw, 20px);
            color: white;
            margin-top: 10px;
            padding: 8px 15px;
            border-radius: 5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            font-weight: 500;
        }

        .preview-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .preview-content {
            position: relative;
            max-width: 90%;
            max-height: 90vh;
            margin: 20px;
        }

        .preview-image {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .watermark {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-template-rows: repeat(6, 1fr);
            gap: 10px;
            padding: 20px;
        }

        .watermark span {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.3);
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            transform: rotate(-30deg);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .preview-controls {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 15px;
            background: rgba(255, 255, 255, 0.1);
            padding: 15px 25px;
            border-radius: 50px;
            backdrop-filter: blur(10px);
        }

        .preview-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: none;
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .preview-btn.reset-btn {
            width: auto;
            padding: 0 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 500;
        }

        .preview-btn:hover {
            background: white;
            transform: scale(1.1);
        }

        .preview-close { position: fixed; top: 20px; right: 20px; }

        .zoom-level {
            color: white;
            font-size: 14px;
            min-width: 60px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .editor-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin: 0 auto;
            width: 100%;
            max-width: 1200px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: none;
            transform: translateY(20px);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .editor-container.active {
            display: flex;
            flex-direction: column;
            transform: translateY(0);
            opacity: 1;
        }

        .editor-content {
            display: flex;
            gap: 30px;
            margin-top: 20px;
        }

        .editor-form { flex: 1; max-width: 600px; }

        .preview-section {
            flex: 1;
            position: sticky;
            top: 30px;
            height: fit-content;
            padding: 30px;
            background: #f8f8f8;
            border-radius: 8px;
            text-align: center;
        }

        .preview-section img {
            max-width: 100%;
            max-height: 500px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            object-fit: contain;
        }

        .editor-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .editor-title {
            font-size: 24px;
            color: #333;
            font-weight: 600;
        }

        .editor-close {
            background: none;
            border: none;
            font-size: 20px;
            color: #666;
            cursor: pointer;
            padding: 5px;
            transition: all 0.3s ease;
        }

        .editor-close:hover { color: #f44336; transform: rotate(90deg); }

        .form-group { margin-bottom: 20px; }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            color: #333;
            transition: all 0.3s ease;
            background: #f8f8f8;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            background: white;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }

        .form-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-secondary { background: #f0f0f0; color: #666; }
        .btn-secondary:hover { background: #e0e0e0; color: #333; }
        .btn-primary { background: #4a90e2; color: white; }
        .btn-primary:hover {
            background: #357abd;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.2);
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast {
            background: white;
            color: #333;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 300px;
            max-width: 400px;
            transform: translateX(120%);
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .toast.show { transform: translateX(0); opacity: 1; }
        .toast.success { border-left: 4px solid #2ecc71; }
        .toast.error { border-left: 4px solid #e74c3c; }

        .toast-icon { font-size: 20px; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; }
        .toast.success .toast-icon { color: #2ecc71; }
        .toast.error .toast-icon { color: #e74c3c; }

        .toast-message { flex: 1; font-size: 14px; font-weight: 500; }
        .toast-close { color: #666; cursor: pointer; padding: 4px; border-radius: 4px; transition: all 0.3s ease; }
        .toast-close:hover { background: rgba(0, 0, 0, 0.1); color: #333; }

        .top-editor-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin: 0 auto 20px auto;
            width: 100%;
            max-width: 1200px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .top-editor-container .preview-section {
            flex: 0 0 300px;
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 300px;
            border: 2px dashed #e0e0e0;
        }

        .top-editor-container .preview-section.has-image { border-style: solid; border-color: #2ecc71; }
        .top-editor-container .preview-section img { max-width: 100%; max-height: 260px; border-radius: 8px; object-fit: contain; }
        .top-editor-container .preview-placeholder { color: #adb5bd; text-align: center; font-size: 14px; line-height: 1.5; }
        .top-editor-container .preview-placeholder i { font-size: 48px; margin-bottom: 15px; display: block; }

        @media (max-width: 768px) {
            .header { margin-bottom: 30px; padding: 20px 0; }
            .header h1 { font-size: clamp(24px, 4vw, 32px); }
            .close-btn { right: 20px; top: 20px; width: 45px; height: 45px; font-size: 20px; }
            .slider-container { padding: 40px; min-height: 350px; }
            .template-slide { flex: 0 0 100%; }
            .top-editor-container { padding: 15px; margin-bottom: 15px; }
            .top-editor-container .form-row { flex-direction: column; gap: 10px; }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header">
            <div class="header-content">
                <div class="header-icon"><i class="fas fa-layer-group"></i></div>
                <h1>TEMPLATE MANAGEMENT</h1>
            </div>
            <button class="close-btn" onclick="window.history.back()"></button>
        </div>

        <div class="top-editor-container" id="topEditorContainer">
            <div class="editor-content flex flex-col md:flex-row gap-8">
                <div class="preview-section" id="topPreviewSection">
                    <div class="preview-placeholder" id="topPreviewPlaceholder">
                        <i class="fas fa-image"></i>
                        <div>Preview will appear here</div>
                        <div>after entering image URL</div>
                    </div>
                    <img src="" alt="Template Preview" id="topTemplatePreview" style="display: none;">
                </div>
                <div class="editor-form flex-1">
                    <div class="form-row flex flex-col md:flex-row gap-5">
                        <div class="form-group flex-1">
                            <label for="top-template-title" class="text-sm font-medium">Template Title</label>
                            <input type="text" id="top-template-title" class="w-full p-3 border-2 rounded-lg" placeholder="Enter template title" required>
                        </div>
                        <div class="form-group flex-1">
                            <label for="top-template-image" class="text-sm font-medium">Image URL</label>
                            <input type="url" id="top-template-image" class="w-full p-3 border-2 rounded-lg" placeholder="Enter image URL" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="top-template-description" class="text-sm font-medium">Description</label>
                        <textarea id="top-template-description" rows="4" class="w-full p-3 border-2 rounded-lg" placeholder="Enter template description"></textarea>
                    </div>
                    <div class="form-actions flex justify-end mt-6">
                        <button class="btn btn-primary flex items-center gap-2" id="topSaveButton" onclick="saveTemplateDetails(event, true)">
                            <i class="fas fa-plus"></i>Add Template
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="slider-container">
            <button class="nav-button prev"><i class="fas fa-chevron-left"></i></button>
            <div class="templates-slider" id="templatesSlider"></div>
            <button class="nav-button next"><i class="fas fa-chevron-right"></i></button>
        </div>

        <div class="editor-container" id="editorContainer">
            <div class="editor-header flex justify-between items-center">
                <h2 class="editor-title" id="editorTitle">Edit Template</h2>
                <button class="editor-close" onclick="closeEditor()"><i class="fas fa-times"></i></button>
            </div>
            <div class="editor-content flex flex-col md:flex-row gap-8">
                <div class="editor-form flex-1">
                    <div class="form-group">
                        <label for="template-title" class="text-sm font-medium">Template Title</label>
                        <input type="text" id="template-title" class="w-full p-3 border-2 rounded-lg" required>
                        <div class="help-text text-xs text-gray-600 mt-1">Enter a descriptive title for the template</div>
                    </div>
                    <div class="form-group">
                        <label for="template-image" class="text-sm font-medium">Image URL</label>
                        <input type="url" id="template-image" class="w-full p-3 border-2 rounded-lg" required>
                        <div class="help-text text-xs text-gray-600 mt-1">Enter a valid URL for the template image</div>
                    </div>
                    <div class="form-group">
                        <label for="template-description" class="text-sm font-medium">Description</label>
                        <textarea id="template-description" rows="4" class="w-full p-3 border-2 rounded-lg"></textarea>
                    </div>
                    <div class="form-actions flex justify-center gap-4">
                        <button type="button" class="btn btn-secondary" onclick="closeEditor()">Cancel</button>
                        <button class="btn btn-primary" id="saveButton" onclick="saveTemplateDetails(event, false)">Save Changes</button>
                    </div>
                </div>
                <div class="preview-section">
                    <img id="template-preview" src="" alt="Template Preview">
                </div>
            </div>
        </div>

        <div class="toast-container" id="toastContainer"></div>

        <div class="preview-modal" id="previewModal">
            <div class="preview-content">
                <div class="preview-image-container">
                    <img src="" alt="Preview" class="preview-image" id="previewImage">
                    <div class="watermark" id="watermark"></div>
                </div>
                <button class="preview-btn preview-close"><i class="fas fa-times"></i></button>
                <div class="preview-controls">
                    <button class="preview-btn" onclick="zoomOut()"><i class="fas fa-search-minus"></i></button>
                    <button class="preview-btn reset-btn" onclick="resetZoom()">Reset</button>
                    <button class="preview-btn" onclick="zoomIn()"><i class="fas fa-search-plus"></i></button>
                    <div class="zoom-level">100%</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let templates = [];
        const slider = document.getElementById('templatesSlider');
        let currentPage = 0;
        let currentEditingId = null;
        let isAddingNew = false;

        // Fetch templates
        async function fetchTemplates() {
            try {
                const response = await fetch('?action=get_templates', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                if (data.error) {
                    showNotification(data.error, 'error');
                    return;
                }
                templates = data;
                renderTemplates();
            } catch (error) {
                showNotification('Failed to fetch templates', 'error');
            }
        }

        // Render templates
        function renderTemplates() {
            const addNewTemplate = { id: 'new', title: 'ADD NEW TEMPLATE', isAdd: true };
            const allItems = [...templates, addNewTemplate];
            const groups = [];
            for (let i = 0; i < allItems.length; i += 2) {
                groups.push(allItems.slice(i, i + 2));
            }

            slider.innerHTML = groups.map(group => `
                <div class="template-group">
                    ${group.map(item => `
                        <div class="template-slide">
                            <div class="template-card ${!item.isAdd ? 'template-' + item.id : ''}">
                                ${item.isAdd ? `
                                    <div class="add-template-card">
                                        <i class="fas fa-plus-circle add-template-icon"></i>
                                    </div>
                                    <div class="template-title">ADD NEW TEMPLATE</div>
                                ` : `
                                    <img src="${item.image}" alt="${item.title}" class="template-image">
                                    <div class="template-actions">
                                        <button class="action-btn" onclick="previewTemplate('${item.image}'); event.stopPropagation();">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="action-btn" onclick="loadTemplateToForm1('${item.id}'); event.stopPropagation();">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                    <div class="template-title">${item.title}</div>
                                `}
                            </div>
                        </div>
                    `).join('')}
                </div>
            `).join('');
            updateNavButtons();
        }

        // Navigation
        function scrollSlider(direction) {
            const totalPages = Math.ceil((templates.length + 1) / 2);
            if (direction === 'next' && currentPage < totalPages - 1) {
                currentPage++;
            } else if (direction === 'prev' && currentPage > 0) {
                currentPage--;
            }
            slider.scrollTo({ left: currentPage * slider.clientWidth, behavior: 'smooth' });
            updateNavButtons();
        }

        function updateNavButtons() {
            const prevButton = document.querySelector('.nav-button.prev');
            const nextButton = document.querySelector('.nav-button.next');
            const totalPages = Math.ceil((templates.length + 1) / 2);
            prevButton.style.display = currentPage <= 0 ? 'none' : 'flex';
            nextButton.style.display = currentPage >= totalPages - 1 ? 'none' : 'flex';
        }

        document.querySelector('.nav-button.next').addEventListener('click', () => scrollSlider('next'));
        document.querySelector('.nav-button.prev').addEventListener('click', () => scrollSlider('prev'));
        document.querySelector('.templates-slider').addEventListener('wheel', (e) => e.preventDefault(), { passive: false });

        document.addEventListener('click', (e) => {
            if (e.target.closest('.add-template-card')) {
                const form = document.getElementById('topTemplateForm');
                form.reset();
                form.dataset.editingId = '';
                const previewImg = document.getElementById('topTemplatePreview');
                const placeholder = document.getElementById('topPreviewPlaceholder');
                const previewSection = document.getElementById('topPreviewSection');
                previewImg.style.display = 'none';
                placeholder.style.display = 'block';
                previewSection.classList.remove('has-image');
                document.getElementById('topSaveButton').innerHTML = '<i class="fas fa-plus"></i>Add Template';
                document.getElementById('topEditorContainer').scrollIntoView({ behavior: 'smooth' });
            }
        });

        // Preview
        let currentZoom = 1;
        const zoomStep = 0.1;
        const previewModal = document.getElementById('previewModal');
        const previewImage = document.getElementById('previewImage');

        function createWatermarkGrid() {
            const watermark = document.getElementById('watermark');
            watermark.innerHTML = '';
            for (let i = 0; i < 24; i++) {
                const span = document.createElement('span');
                span.textContent = 'Lurey_Creation@2025-26';
                watermark.appendChild(span);
            }
        }

        function previewTemplate(imageUrl) {
            previewImage.src = imageUrl;
            previewModal.style.display = 'flex';
            resetZoom();
            createWatermarkGrid();
        }

        function closePreview() {
            previewModal.style.display = 'none';
        }

        function updateZoomLevel() {
            document.querySelector('.zoom-level').textContent = `${Math.round(currentZoom * 100)}%`;
            previewImage.style.transform = `scale(${currentZoom})`;
        }

        function zoomIn() {
            currentZoom = Math.min(currentZoom + zoomStep, 3);
            updateZoomLevel();
        }

        function zoomOut() {
            currentZoom = Math.max(currentZoom - zoomStep, 0.5);
            updateZoomLevel();
        }

        function resetZoom() {
            currentZoom = 1;
            updateZoomLevel();
        }

        document.querySelector('.preview-close').addEventListener('click', closePreview);
        previewModal.addEventListener('click', (e) => {
            if (e.target === previewModal) closePreview();
        });
        document.addEventListener('keydown', (e) => {
            if (previewModal.style.display === 'flex') {
                if (e.key === 'Escape') closePreview();
                if (e.key === '+' || e.key === '=') zoomIn();
                if (e.key === '-') zoomOut();
                if (e.key === '0') resetZoom();
            }
        });

        // Editor
        function openEditor(templateId) {
            isAddingNew = templateId === 'new';
            currentEditingId = templateId;
            document.getElementById('editorTitle').textContent = isAddingNew ? 'Add New Template' : 'Edit Template';
            document.getElementById('saveButton').textContent = isAddingNew ? 'Add Template' : 'Save Changes';
            if (isAddingNew) {
                document.getElementById('template-title').value = '';
                document.getElementById('template-image').value = '';
                document.getElementById('template-description').value = '';
                document.getElementById('template-preview').src = '';
            } else {
                const template = templates.find(t => t.id === templateId);
                if (!template) return;
                document.getElementById('template-title').value = template.title;
                document.getElementById('template-image').value = template.image;
                document.getElementById('template-description').value = template.description || '';
                document.getElementById('template-preview').src = template.image;
            }
            document.getElementById('editorContainer').classList.add('active');
        }

        function closeEditor() {
            document.getElementById('editorContainer').classList.remove('active');
            currentEditingId = null;
            isAddingNew = false;
        }

        async function saveTemplateDetails(event, isTopForm) {
            event.preventDefault();
            const prefix = isTopForm ? 'top-' : '';
            const editingId = isTopForm ? document.getElementById('topTemplateForm').dataset.editingId : currentEditingId;
            const templateData = {
                title: document.getElementById(`${prefix}template-title`).value,
                image: document.getElementById(`${prefix}template-image`).value,
                description: document.getElementById(`${prefix}template-description`).value
            };

            if (!templateData.title || !templateData.image) {
                showNotification('Title and image are required', 'error');
                return;
            }

            try {
                const action = editingId && !isAddingNew ? 'update_template' : 'add_template';
                const body = editingId && !isAddingNew ? { ...templateData, id: editingId } : templateData;
                const response = await fetch(`?action=${action}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body)
                });
                const result = await response.json();

                if (result.error) {
                    showNotification(result.error, 'error');
                    return;
                }

                showNotification(result.success, 'success');

                if (isTopForm) {
                    document.getElementById('topTemplateForm').reset();
                    document.getElementById('topTemplateForm').dataset.editingId = '';
                    document.getElementById('topSaveButton').innerHTML = '<i class="fas fa-plus"></i>Add Template';
                    const previewImg = document.getElementById('topTemplatePreview');
                    const placeholder = document.getElementById('topPreviewPlaceholder');
                    const previewSection = document.getElementById('topPreviewSection');
                    previewImg.style.display = 'none';
                    placeholder.style.display = 'block';
                    previewSection.classList.remove('has-image');
                } else {
                    closeEditor();
                }

                await fetchTemplates();
            } catch (error) {
                showNotification('Failed to save template', 'error');
            }
        }

        function loadTemplateToForm1(templateId) {
            const template = templates.find(t => t.id === templateId);
            if (!template) return;
            document.getElementById('top-template-title').value = template.title;
            document.getElementById('top-template-image').value = template.image;
            document.getElementById('top-template-description').value = template.description || '';
            const previewImg = document.getElementById('topTemplatePreview');
            const placeholder = document.getElementById('topPreviewPlaceholder');
            const previewSection = document.getElementById('topPreviewSection');
            previewImg.src = template.image;
            previewImg.style.display = 'block';
            placeholder.style.display = 'none';
            previewSection.classList.add('has-image');
            document.getElementById('topSaveButton').innerHTML = '<i class="fas fa-save"></i>Update Template';
            document.getElementById('topTemplateForm').dataset.editingId = templateId;
            document.getElementById('topEditorContainer').scrollIntoView({ behavior: 'smooth' });
        }

        function showNotification(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <div class="toast-icon"><i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i></div>
                <div class="toast-message">${message}</div>
                <div class="toast-close"><i class="fas fa-times"></i></div>
            `;
            toastContainer.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            toast.querySelector('.toast-close').addEventListener('click', () => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 500);
            });
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 500);
                }
            }, 5000);
        }

        document.getElementById('top-template-image').addEventListener('input', function(e) {
            const imageUrl = e.target.value;
            const previewSection = document.getElementById('topPreviewSection');
            const previewImg = document.getElementById('topTemplatePreview');
            const placeholder = document.getElementById('topPreviewPlaceholder');
            if (imageUrl) {
                previewImg.src = imageUrl;
                previewImg.onload = () => {
                    previewImg.style.display = 'block';
                    placeholder.style.display = 'none';
                    previewSection.classList.add('has-image');
                };
                previewImg.onerror = () => {
                    previewImg.style.display = 'none';
                    placeholder.style.display = 'block';
                    previewSection.classList.remove('has-image');
                    showNotification('Invalid image URL', 'error');
                };
            } else {
                previewImg.style.display = 'none';
                placeholder.style.display = 'block';
                previewSection.classList.remove('has-image');
            }
        });

        document.getElementById('template-image').addEventListener('input', function(e) {
            document.getElementById('template-preview').src = e.target.value;
        });

        function resetToHome() {
            document.getElementById('topTemplateForm').reset();
            document.getElementById('topTemplateForm').dataset.editingId = '';
            const previewImg = document.getElementById('topTemplatePreview');
            const placeholder = document.getElementById('topPreviewPlaceholder');
            const previewSection = document.getElementById('topPreviewSection');
            previewImg.style.display = 'none';
            placeholder.style.display = 'block';
            previewSection.classList.remove('has-image');
            document.getElementById('topSaveButton').innerHTML = '<i class="fas fa-plus"></i>Add Template';
            document.getElementById('editorContainer').classList.remove('active');
            currentPage = 0;
            slider.scrollTo({ left: 0, behavior: 'smooth' });
            updateNavButtons();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        document.querySelector('.header-content').addEventListener('click', resetToHome);

        fetchTemplates();
    </script>
</body>
</html>
<?php
// Close PDO connection
$pdo = null;
?>
