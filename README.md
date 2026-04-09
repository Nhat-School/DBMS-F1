# 🏎️ F1 Championship Management System

Hệ thống quản lý giải đua Xe công thức 1 (F1) được xây dựng dựa trên sự kết hợp giữa PHP và quản trị cơ sở dữ liệu mạnh mẽ (MariaDB). Dự án tập trung vào việc thực thi logic nghiệp vụ (Business Logic) trực tiếp tại tầng Database thông qua các Triggger và Stored Procedure.

## 🚀 Tính năng chính

- **Quản lý xếp hạng**: Tự động tính điểm và xếp hạng tay đua/đội đua theo mùa giải.
- **Cập nhật kết quả**: Nhập thời gian về đích, số vòng và trạng thái chặng đua.
- **Đăng ký thi đấu**: Kiểm soát chặt chẽ danh sách tay đua tham gia mỗi chặng.
- **Dữ liệu đa dạng**: Bao gồm 20 đội đua và 40 tay đua (50% là Việt Nam) với các chặng đua tại Hà Nội, Đà Nẵng, TP.HCM...

## 🔐 Tài khoản truy cập

Dưới đây là các tài khoản mặc định có sẵn trong Seed Data để bạn kiểm tra:

| Vai trò | Username | Password |
| :--- | :--- | :--- |
| **Quản trị viên (Admin)** | `admin` | `admin123` |
| **Nhân viên (Staff)** | `staff1` | `staff123` |
| **Nhân viên (Staff)** | `staff2` | `staff456` |

## ⚙️ Ràng buộc & Logic Database (DBMS Triggers)

Hệ thống được thiết kế để đảm bảo tính toàn vẹn dữ liệu ngay cả khi không có code ứng dụng can thiệp:

1.  **Tự động tính điểm (Scoring)**:
    - Sử dụng Trigger `trg_assign_score_insert/update`.
    - Tự động cộng điểm cho Top 10 về đích theo tiêu chuẩn FIA (25, 18, 15, 12, 10, 8, 6, 4, 2, 1).
2.  **Kiểm soát định dạng thời gian (Time Validation)**:
    - Sử dụng Regex trong Trigger để kiểm tra định dạng `H:M:S`.
    - Chặn các trường hợp nhập sai phút hoặc giây (>= 60).
3.  **Logic về đích & Số vòng (Status Consistency)**:
    - Nếu trạng thái là `Finished` (Về đích), bắt buộc `laps_completed` phải bằng số vòng quy định của chặng đó.
4.  **Giới hạn đăng ký (Registration Limit)**:
    - Sử dụng Stored Procedure `sp_validate_registration`.
    - Ràng buộc: Một đội đua tối đa chỉ được đăng ký **2 tay đua** cho mỗi chặng đua.

## 🛠️ Hướng dẫn cài đặt (Docker)

Để chạy dự án cục bộ, bạn cần cài đặt Docker và Docker Compose:

1.  **Clone dự án** về máy.
2.  Mở terminal tại thư mục gốc và chạy:
    ```bash
    docker-compose up -d --build
    ```
3.  Truy cập ứng dụng tại: `http://localhost:8081`
4.  Database (MariaDB) có thể truy cập qua cổng `3307`.

## 📂 Cấu trúc SQL

- `01_schema.sql`: Khởi tạo bảng và các ràng buộc khóa ngoại.
- `02_triggers.sql`: Chứa toàn bộ Trigger và Procedure (Trái tim của hệ thống).
- `03_views.sql`: Các View tính toán bảng xếp hạng tổng sắp.
- `04_seed_data.sql`: Dữ liệu mẫu phong phú cho 20 đội đua và 40 tay đua.

---
*Dự án được phát triển nhằm mục đích học tập và nghiên cứu hệ quản trị cơ sở dữ liệu (DBMS).*
