# PHP Resume Builder

A simple, beginner‑friendly PHP Resume Builder built with PHP 8, Bootstrap 5, and TCPDF.  
Users can enter their information, select a resume template, preview it live, and download it as a PDF.

---

## Features

- Collects personal information:
  - Name, email, phone, address
- Education:
  - School, degree, year
- Work experience:
  - Company, position, years
- Skills:
  - Comma‑separated list
- Optional profile picture upload (JPG/PNG/GIF, up to 2 MB)
- 2 resume templates:
  - Template 1: Classic
  - Template 2: Modern
- Live HTML preview before generating the PDF
- PDF generation using TCPDF
- Session‑based data storage (no database required)
- Simple OOP structure using a `Resume` class
- Basic input validation and XSS protection
- Responsive layout using Bootstrap 5
- Clean, well‑commented code

---

## Requirements

- PHP 8.0 or higher
- Web server capable of running PHP (Apache, Nginx, etc.)
- TCPDF library

---

## Installation

1. **Download or clone the project**

   Place all files in a web‑accessible directory, for example:

   ```text
   /var/www/html/php-resume-builder/