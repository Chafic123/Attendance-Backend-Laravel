# 🎓 Attendance Management System - Backend

This is the **backend** component of our **Attendance Management System using Facial Detection and Recognition**, designed to automate attendance tracking through real-time face detection and recognition.

---

## 📌 Project Overview

Our system leverages:
- **YOLO** and **CNN models** for **face detection**.
- **LBPH (Local Binary Patterns Histogram)** for **face recognition**.
- A robust backend to handle **data processing**, **authentication**, and **API services**.

This project was recognized as one of the **Top 5 Senior Projects** for its innovation and impact.

---

## 🚀 Features

- ✅ Real-time face detection and recognition (via integrated models)
- ✅ RESTful API endpoints for attendance management
- ✅ Secure authentication system
- ✅ Backend architecture ready for scaling and real-time data handling
- ✅ Database integration for storing users, attendance records, and logs

---

## 🛠️ Backend Tech Stack

| **Component**     | **Technology**                |
|-------------------|-----------------------------|
| Backend Framework | **Laravel With Sanctum**  |
| Database          | **PostgreSQL**    |
| Authentication    | **Sanctum Token** |
| Computer Vision   | YOLO, CNN, LBPH (Python libraries) |
| API Design        | REST APIs                    |

---

## ⚙️ System Architecture (Backend)

The backend is designed for **modular scalability** and **real-time data handling**, following **RESTful API principles**. It interacts with the facial detection module and manages data flow for the entire system.

### 🏗️ Workflow Overview

1. **Face Capture (External Python Module)**  
   - The Python module (YOLO, CNN, LBPH) processes video frames and detects faces.
   - Recognized face data is sent to the backend .

2. **Backend Processing**  
   - Backend receives face data via API endpoints.
   - Verifies and authenticates requests.
   - Updates attendance records in the **PostgreSQL** database.
   - Provides API responses for frontend or admin dashboard.

3. **Data Management**  
   - Stores users, roles (e.g., student, admin), attendance logs, and face data.
   - Supports real-time updates for the system’s frontend.

---

## 🌐 Integration with Detection Module

- The Python module (external) handles:
  - Face detection via **YOLO/CNN**
  - Face recognition using **LBPH**
  - Sending detected face data (ID, timestamp) to the backend via API requests.

- The backend verifies the data, marks attendance, and stores it in the database.

---

## 🚀 Running the Backend Locally

### Prerequisites

- Java 11+  
- PostgreSQL 
- Python 3.x (for external detection module)
- Composer
  
### Setup Steps

```bash
# Clone the repository
git clone https://github.com/yourusername/attendance-backend.git
cd attendance-backend

# Install PHP dependencies via Composer
composer install

# Install Laravel Sanctum for authentication
composer require laravel/sanctum

# Publish Sanctum configuration and migration files
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Run database migrations
php artisan migrate

# Generate application key
php artisan key:generate

# Start the local development server
php artisan serve

