# 🏎️ F1 Championship Management System

Hệ thống quản lý giải đua Xe công thức 1 (F1) được xây dựng dựa trên sự kết hợp giữa PHP và quản trị cơ sở dữ liệu mạnh mẽ (MySQL). Dự án tập trung vào việc thực thi logic nghiệp vụ (Business Logic) và bảo mật trực tiếp tại tầng Database thông qua các Trigger và Stored Procedure.

## 🚀 Tính năng chính

- **Quản lý xếp hạng**: Tự động tính điểm và xếp hạng tay đua/đội đua theo mùa giải.
- **Cập nhật kết quả**: Nhập kết quả hàng loạt thông qua giao thức truyền tải JSON.
- **Bảo mật phân quyền**: Kiểm soát quyền Admin/Staff trực tiếp từ tầng Database.
- **Dữ liệu đa dạng**: Bao gồm 20 đội đua và 44 tay đua (50% là Việt Nam) với các chặng đua tại Hà Nội, Đà Nẵng, TP.HCM, Hoàng Sa...

## 🔐 Tài khoản truy cập & Phân quyền (RBAC)

Hệ thống triển khai phân quyền dựa trên vai trò (Role-Based Access Control) để đảm bảo an toàn dữ liệu:

| Vai trò | Username | Password | Quyền hạn |
| :--- | :--- | :--- | :--- |
| **Quản trị viên** | `admin` | `admin123` | Toàn quyền (Đăng ký chặng, Cập nhật kết quả, Xem báo cáo) |
| **Nhân viên** | `staff1` | `staff123` | Chỉ được phép cập nhật kết quả thi đấu |
| **Nhân viên** | `staff2` | `staff456` | Chỉ được phép cập nhật kết quả thi đấu |

## ⚙️ Logic Database & Bảo mật tầng thấp (DBMS Centric)

Dự án này đẩy tối đa logic xử lý xuống Database thay vì xử lý bằng mã ứng dụng:

### 1. Quản lý Giao dịch (SQL Transactions)
- **Tập trung hóa**: Mọi giao dịch quan trọng (như Lưu kết quả) đều được bọc trong `START TRANSACTION` và `COMMIT/ROLLBACK` bên trong Stored Procedure.
- **Nhập liệu hàng loạt**: Sử dụng `JSON_TABLE` để xử lý danh sách kết quả hàng loạt trong một lần gọi lệnh duy nhất, đảm bảo tính nhất quán (Atomicity).

### 2. Bảo mật thủ tục (Stored Procedure Authorization)
- **Xác thực vai trò**: Các Stored Procedure như `sp_register_racer` yêu cầu tham số `p_user_role`. Nếu người gọi không phải 'admin', SQL sẽ chủ động chặn đứng bằng lệnh `SIGNAL SQLSTATE`.
- Điều này đảm bảo ngay cả khi giao diện Web bị can thiệp, dữ liệu gốc vẫn được bảo vệ tuyệt đối bởi Database.

### 3. Ràng buộc dữ liệu (Triggers)
- **Tự động tính điểm**: Trigger `trg_assign_score` tự động tính điểm theo hạng về đích ( FIA Standard).
- **Kiểm soát thời gian**: Sử dụng Regular Expression để kiểm tra định dạng `H:M:S` và chặn giá trị phút/giây >= 60.
- **Ràng buộc chặng đua**: Đội đua chỉ được đăng ký tối đa **2 tay đua** cho mỗi chặng (xác thực qua Procedure).

## 🛠️ Hướng dẫn cài đặt (Docker)

1.  **Clone dự án** về máy.
2.  Mở terminal và chạy:
    ```bash
    docker-compose up -d --build
    ```
3.  Truy cập ứng dụng tại: `http://localhost:8081`

## 📂 Cấu trúc SQL

- `01_schema.sql`: Khởi tạo bảng, ràng buộc khóa ngoại và Index.
- `02_triggers.sql`: Chứa toàn bộ Trigger, Stored Procedure và logic bảo mật (Trái tim hệ thống).
- `03_views.sql`: Các View thống kê bảng xếp hạng tay đua và đội đua.
- `04_seed_data.sql`: Dữ liệu mẫu (20 Teams, 44 Racers, 6 VN Stages).

---
*Dự án tập trung vào việc mô phỏng các nghiệp vụ thực tế của một Hệ quản trị cơ sở dữ liệu (DBMS) chuyên nghiệp.*
