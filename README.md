# VulnShop - OWASP Top 10 Training Project

## 📌 Overview
VulnShop is an intentionally vulnerable e-commerce web application developed in PHP and MySQL.  
It is designed **for educational and training purposes only** to help students and security enthusiasts learn, test, and demonstrate the **OWASP Top 10 vulnerabilities**.

⚠️ **Disclaimer:** This project contains numerous security vulnerabilities and should **never** be used in production environments.

---

## 🚀 Features
- Demonstrates **all OWASP Top 10 (2021)** vulnerabilities:
  - A01: Broken Access Control
  - A02: Cryptographic Failures
  - A03: Injection (SQLi, XSS)
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
   git clone https://github.com/YOUR-USERNAME/vulnshop-owasp-top10-training.git
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

## 🎯 Usage
- Use this project for **Vulnerability Assessment & Penetration Testing (VAPT)** practice.
- Try exploiting vulnerabilities with tools like:
  - Burp Suite
  - sqlmap
  - Hydra
  - curl / wget
  - nmap
  - whatweb

---

## 📷 Screenshots
_Add screenshots of successful exploitation (XSS, SQLi, SSRF, etc.) here when documenting your report._

---

## ⚠️ Disclaimer
This project is **for training and educational purposes only**.  
The authors are **not responsible** for any misuse or damage caused by this code.

---

## 📚 References
- [OWASP Top 10 (2021)](https://owasp.org/Top10/)
- [OWASP Testing Guide](https://owasp.org/www-project-web-security-testing-guide/)
