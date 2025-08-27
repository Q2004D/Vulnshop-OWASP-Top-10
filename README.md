# VulnShop - OWASP Top 10 Training Project

## 📌 Overview
VulnShop is an intentionally vulnerable e-commerce web application developed in PHP and MySQL.  
It is designed **for educational and training purposes only** to help students and security enthusiasts learn, test, and demonstrate the **OWASP Top 10 vulnerabilities**.

---
## 📌 Report for the first five Vuln. only
https://github.com/Q2004D/Vulnshop-OWASP-Top-10/blob/main/report.pdf
---

## 🚀 Features
- Demonstrates **all OWASP Top 10 (2021)** vulnerabilities:
  - A01: Broken Access Control
  - A02: Cryptographic Failures
  - A03: Injection
  - A04: Insecure Design
  - A05: Security Misconfiguration
  - A06: Vulnerable & Outdated Components
  - A07: Identification & Authentication Failures
  - A08: Software & Data Integrity Failures
  - A09: Security Logging & Monitoring Failures
  - A10: Server-Side Request Forgery (SSRF)
- Simple vulnerable shop design (users, products, cart, admin dashboard).

---

## 🛠️ Installation
1. Clone the repository:
   ```bash
   git clone https://github.com/Q2004D/Vulnshop-OWASP-Top-10.git
   ```
2. Import the database:
   - Create a database `vulnshop`.
   - Import `vulnshop.sql`.
3. Start a local server (XAMPP, WAMP, or LAMP).
4. Access the application:
   ```
   http://localhost/vulnshop/
   ```
---

## 📚 References
- [OWASP Top 10 (2021)](https://owasp.org/Top10/)
- [OWASP Testing Guide](https://owasp.org/www-project-web-security-testing-guide/)
