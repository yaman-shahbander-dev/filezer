# Filezer

## Introduction

Filezer is a PHP application designed for analyzing text files. It allows users to upload plain text files, which are then processed to extract insights such as word count, sentence count, entity recognition, and categorization. The application leverages the **TextRazor API** for advanced text analysis, making it an effective tool for anyone needing to analyze textual data.

## Features

* **File Upload**: Users can upload **.txt** files through a simple web interface.

* **Text Analysis**: The application performs various analyses, including:
  * Total characters, words, lines, and sentences.
  * Average word and sentence length.
  * Word frequency analysis, displaying the top ten most frequent words.
  * Entity recognition and categorization using the TextRazor API.
    
* **Report Generation**: After analysis, users can download a detailed report summarizing the analysis results.
  
* **User-Friendly Interface**: The application presents a clean and straightforward interface for uploading files and viewing results.


## How to Run the Application

### Prerequisites

* **PHP**: Ensure you have PHP 8.2 or higher installed on your server.
* **Composer**: Install Composer to manage dependencies.

## Installation Steps

* **Clone the Repository**: Download or clone the Filezer project to your local or server environment.
  
   ```
   git clone https://github.com/yaman-shahbander-dev/filezer/
   ```
   
   ```
   cd filezer
   ```
* **Install Dependencies**: Navigate to the project directory and run Composer to install required packages.
  
  ```
  composer install
  ```
* **Set Up Environment Variables**: Navigate to the project directory and run Composer to install required packages.
  * Create a **.env** file in the root directory of the project.
  * Copy the contents of **.env.example** to **.env** and set your TextRazor API key:
    
  ```
  TEXTRAZOR_API_KEY=your_api_key_here
  ```
* **Create Uploads Directory**: Ensure there is an **uploads** directory at the root of the project to store uploaded files. If it doesn't exist, create it:
  
  ```
  mkdir uploads
  ```
* **Run the Application**: You can use PHP's built-in server to run the application locally. In the root directory, execute:
  
  ```
  php -S localhost:8000 -t public
  ```
* **Access the Application**: Open your web browser and navigate to **http://localhost:8000**. You should see the file upload form.

## Usage

* **Upload a Text File**: Click on the "Choose File" button to select a **.txt** file from your computer, then click "Upload".
* **View Results**: After the file is uploaded, the application will analyze the text and display the results on the screen.
* **Download Report**: A text report summarizing the analysis will be generated and offered for download.

## Packages Used

[Textrazor](https://github.com/TextRazor/textrazor-php)

[phpdotenv](https://github.com/vlucas/phpdotenv)
