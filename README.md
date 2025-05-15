# SMS (Student Management System)

ระบบจัดการข้อมูลนักเรียน พัฒนาด้วย PHP, MySQL และ Bootstrap 5

## คุณสมบัติหลัก

- 🔐 ระบบล็อกอินและจัดการผู้ใช้
- 👥 จัดการข้อมูลนักเรียน (เพิ่ม, แก้ไข, ลบ, ค้นหา)
- 📊 แสดงสถิติและรายงานข้อมูลนักเรียน
- 📱 รองรับการแสดงผลบนอุปกรณ์ทุกขนาด (Responsive Design)
- 📥 นำเข้าข้อมูลนักเรียนจากไฟล์ Excel
- 📤 ส่งออกรายงานในรูปแบบต่างๆ

## การติดตั้ง

### วิธีที่ 1: ใช้ Docker

1. ติดตั้ง Docker และ Docker Compose
2. Clone repository:
```bash
git clone https://github.com/MarkeloPuangpoo/SMS.git
cd SMS
```
3. รันคำสั่ง:
```bash
docker-compose up -d
```
4. เข้าใช้งานที่ http://localhost:8000

### วิธีที่ 2: ติดตั้งเอง

#### ความต้องการของระบบ
- PHP 8.2+
- MySQL 5.7+
- Composer
- Web Server (Apache/Nginx)

#### ขั้นตอนการติดตั้ง
1. Clone repository
2. ติดตั้ง dependencies:
```bash
composer install
```
3. สร้างฐานข้อมูล และ import โครงสร้าง:
```sql
mysql -u root -p < init.sql
```
4. แก้ไขการตั้งค่าฐานข้อมูลที่ไฟล์ `app/config/database.php`
5. ตั้งค่า web server ให้ชี้ไปที่โฟลเดอร์ `app`

## การใช้งาน

1. เข้าสู่ระบบด้วยบัญชีเริ่มต้น:
   - Username: admin
   - Password: admin123

2. เมนูหลัก:
   - Dashboard: ภาพรวมและสถิติ
   - จัดการนักเรียน: เพิ่ม/แก้ไข/ลบข้อมูลนักเรียน
   - นำเข้าข้อมูล: Import ข้อมูลจาก Excel
   - รายงาน: ดูและส่งออกรายงาน
   - ตั้งค่าระบบ: จัดการผู้ใช้และการตั้งค่าต่างๆ

## การพัฒนาเพิ่มเติม

สามารถพัฒนาต่อยอดได้โดย:
1. Fork repository นี้
2. สร้าง branch ใหม่สำหรับฟีเจอร์ที่ต้องการ
3. Commit การเปลี่ยนแปลง
4. สร้าง Pull request
