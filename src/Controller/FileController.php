<?php

namespace Filezer\Controller;

use Filezer\View\FileView;
use TextRazor;
use Dotenv\Dotenv;

/**
 * Class FileController
 * Controller class responsible for handling file upload, analysis, and report generation.
 * @package Filezer\Controller
 */
class FileController {
    protected FileView $fileView;
    protected ?string $uploadedFilePath = null;
    protected TextRazor $textRazor;

    /**
     * FileController constructor.
     * Initializes the FileController with necessary dependencies.
     * Loads environment variables and initializes TextRazor API.
     */
    public function __construct() {
    	$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
        $apiKey = $_ENV['TEXTRAZOR_API_KEY'];

        $this->fileView = new FileView();
        $this->textRazor = new TextRazor($apiKey);
        $this->textRazor->addExtractor('entities');
        $this->textRazor->addExtractor('categories');
    }

  		
  	/**
     * Handles the incoming request.
     * Checks for file upload parameter and delegates file handling accordingly.
     */
    public function handleRequest() {
        if (isset($_GET['upload']) && $_GET['upload']) $this->handleFileUpload($_FILES['file'] ?? null);
        $this->displayView();
    }

    /**
     * Displays the file upload form.
     * Invokes the display method of the FileView class.
     */
    protected function displayView() {
        $this->fileView->display();
    }

    /**
     * Handles the uploaded file.
     * Validates the file type and moves the file to the uploads directory.
     * Initiates file analysis and report generation upon successful upload.
     * @param array|null $file The uploaded file information.
     */
    protected function handleFileUpload(?array $file): void {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK || $file['type'] !== 'text/plain') {
            echo "Invalid file or upload error.";
            return;
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

        if (strtolower($extension) !== 'txt') {
            echo "Invalid file extension. Only .txt files are allowed.";
            return;
        }

        $this->uploadedFilePath = __DIR__ . '/../../uploads/' . time() . '_' . basename($file['name']);

        if (move_uploaded_file($file['tmp_name'], $this->uploadedFilePath)) {
            echo "File uploaded successfully.<br>";
            $analysisResults = $this->analyzeFile();
            $this->generateTextReport($analysisResults);
        } else echo "Failed to move uploaded file.";
        
    }


    /**
     * Analyzes the uploaded file content.
     * Performs various text analysis tasks such as word count, sentence count,
     * entity recognition, categorization, etc.
     * @return array The analysis results.
     */
    protected function analyzeFile(): array {
        ob_start(); // Start output buffering
        echo "Analyzing file...<br>";

        $fileContent = file_get_contents($this->uploadedFilePath);
        if ($fileContent === false) {
            echo "Failed to read file content.";
            return [];
        }

        // Analysis data structure
        $analysisResults = [];

        // Total characters
        $analysisResults['total_characters'] = strlen($fileContent);

        // Remove punctuation and convert to lowercase
        $fileContent = strtolower(preg_replace('/[^\w\s]/', '', $fileContent));

        // Split content into words
        $words = preg_split('/\s+/', $fileContent, -1, PREG_SPLIT_NO_EMPTY);

        // Count the total number of words
        $analysisResults['total_words'] = count($words);

        // Count the total number of lines
        $analysisResults['total_lines'] = count(file($this->uploadedFilePath));

        // Split content into sentences
        $sentences = preg_split('/[.!?]/', $fileContent, -1, PREG_SPLIT_NO_EMPTY);
        $analysisResults['total_sentences'] = count($sentences);

        // Average word length
        $analysisResults['average_word_length'] = array_sum(array_map('strlen', $words)) / count($words);

        // Average sentence length
        $analysisResults['average_sentence_length'] = count($words) / count($sentences);

        // Calculate the frequency of each word
        $wordFrequency = array_count_values($words);

        // Sort the words by frequency in descending order
        arsort($wordFrequency);

        // Top 10 most frequent words
        $analysisResults['top_words'] = array_slice($wordFrequency, 0, 10, true);

        // Perform entity recognition using TextRazor
        $entities = $this->performEntityRecognition($fileContent);
        $analysisResults['entities'] = $entities;

        // Perform categorization using TextRazor
        $categories = $this->performCategorization($fileContent);
        $analysisResults['categories'] = $categories;

        // Display the analysis results (for debugging purposes)
        $this->outputAnalysisResults($analysisResults);

        ob_end_flush(); // Flush (send) the output buffer and turn off output buffering

        return $analysisResults;
    }

    /**
     * Performs entity recognition on the given text using TextRazor API.
     * @param string $text The text content to analyze.
     * @return array The recognized entities with their details.
     */
    protected function performEntityRecognition(string $text): array {
	    $entities = [];

	    try {
	        $response = $this->textRazor->analyze($text);

	        if (isset($response['error'])) {
	            echo "TextRazor API request failed. Error: " . $response['error'];
	            return [];
	        }

	        if (isset($response['response']) && isset($response['response']['entities'])) {
	            foreach ($response['response']['entities'] as $entity) {
	                $entities[] = [
	                    'type' => $entity['type'] ?? 'unknown',
	                    'entity' => $entity['entityId'] ?? 'unknown',
	                    'relevance' => $entity['relevanceScore'] ?? 0,
	                    'confidence' => $entity['confidenceScore'] ?? 0,
	                ];
	            }
	        }
	    } catch (\Exception $e) {
	        echo "Error performing entity recognition: " . $e->getMessage();
	    }

	    return $entities;
	}


	/**
     * Performs categorization on the given text using TextRazor API.
     * @param string $text The text content to analyze.
     * @return array The categories assigned to the text.
     */
	protected function performCategorization(string $text): array {
	    $categories = [];

	    try {
	        $response = $this->textRazor->analyze($text);

	        if (isset($response['error'])) {
	            echo "TextRazor API request failed. Error: " . $response['error'];
	            return [];
	        }

	        if (isset($response['response']) && isset($response['response']['categories'])) {
	            foreach ($response['response']['categories'] as $category) {
	                $categories[] = $category['label'] ?? 'unknown';
	            }
	        }
	    } catch (\Exception $e) {
	        echo "Error performing categorization: " . $e->getMessage();
	    }

	    return $categories;
	}

	/**
     * Outputs the analysis results to the browser.
     * Includes basic file statistics, top words, recognized entities, and categories.
     * @param array $analysisResults The analysis results to display.
     */
    protected function outputAnalysisResults(array $analysisResults): void {
        // Output basic analysis results
        foreach ($analysisResults as $key => $value) {
            if ($key === 'top_words' || $key === 'entities' || $key === 'categories') {
                continue; // Skip printing entities, categories, and top words here
            } else  echo ucfirst(str_replace('_', ' ', $key)) . ": " . $value . "\n";
        }

        // Output entities
        echo "Entities:\n";
        foreach ($analysisResults['entities'] as $entity) {
            echo "{$entity['entity']} (Type: {$entity['type']}, Relevance: {$entity['relevance']}, Confidence: {$entity['confidence']})\n";
        }

        // Output categories
        echo "Categories:\n";
        foreach ($analysisResults['categories'] as $category) {
            echo "{$category}\n";
        }

        // Output top words
        echo "Top Words:\n";
        foreach ($analysisResults['top_words'] as $word => $count) {
            echo htmlspecialchars($word) . ": " . $count . "\n";
        }
    }

	/**
     * Generates a text report based on the analysis results.
     * Saves the report to a file and sends it as a download to the user.
     * @param array $analysisResults The analysis results to include in the report.
     */
    protected function generateTextReport(array $analysisResults): void {
        try {
            $filePath = __DIR__ . '/../../uploads/file_analysis_report.txt';

            $file = fopen($filePath, 'w');

            fwrite($file, "File Analysis Report\n\n");
            fwrite($file, "Total Characters: " . $analysisResults['total_characters'] . "\n");
            fwrite($file, "Total Words: " . $analysisResults['total_words'] . "\n");
            fwrite($file, "Total Lines: " . $analysisResults['total_lines'] . "\n");
            fwrite($file, "Total Sentences: " . $analysisResults['total_sentences'] . "\n");
            fwrite($file, "Average Word Length: " . number_format($analysisResults['average_word_length'], 2) . "\n");
            fwrite($file, "Average Sentence Length: " . number_format($analysisResults['average_sentence_length'], 2) . "\n\n");

            fwrite($file, "Top 10 Most Frequent Words:\n");
            foreach ($analysisResults['top_words'] as $word => $count) {
                fwrite($file, htmlspecialchars($word) . ": " . $count . "\n");
            }

            fwrite($file, "\nEntities:\n");
            foreach ($analysisResults['entities'] as $entity) {
                fwrite($file, "{$entity['entity']} (Type: {$entity['type']}, Relevance: {$entity['relevance']}, Confidence: {$entity['confidence']})\n");
            }

            fwrite($file, "\nCategories:\n");
            foreach ($analysisResults['categories'] as $category) {
                fwrite($file, "{$category}\n");
            }

            fclose($file);

            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="file_analysis_report.txt"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);

            unlink($filePath);

            exit; // Ensure script stops after file download
        } catch (\Exception $e) {
            echo 'Error generating text report: ' . $e->getMessage();
        }
    }
}