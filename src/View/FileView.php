<?php

namespace Filezer\View;

/**
 * Class FileView
 *
 * View class responsible for rendering the file upload form.
 *
 * @package Filezer\View
 */
class FileView {
    /**
     * Displays the file upload form.
     *
     * Renders an HTML form for uploading a text file.
     */
    public function display(): void {
        echo '<h1>Upload a Text File</h1>';
        echo '<form action="?upload=true" method="post" enctype="multipart/form-data">';
        echo '<input type="file" name="file" accept=".txt">';
        echo '<button type="submit">Upload</button>';
        echo '</form>';
    }
}