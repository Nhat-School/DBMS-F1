# 🏎️ F1 Formula Championship Management System

Hệ thống quản lý giải đua xe Công thức 1 (F1) chuyên nghiệp được xây dựng trên nền tảng **PHP 8.2** và **MySQL 8.0**, triển khai bằng **Docker**. Dự án tập trung tối ưu hóa tầng Cơ sở dữ liệu (DBMS) với các kỹ thuật nâng cao như Triggers, Stored Procedures, Views và Tuning hiệu năng.

---

## 🚀 Tính năng chính
- **Quản lý Đăng ký:** Đăng ký tay đua vào các chặng đua với các quy tắc kiểm tra nghiêm ngặt.
- **Cập nhật Kết quả:** Nhập kết quả đua hàng loạt, tự động tính điểm và xếp hạng.
- **Bảng xếp hạng Tay đua:** Thống kê tổng điểm tích lũy qua các chặng đua trong mùa giải.
- **Bảng xếp hạng Đội đua:** Tính tổng điểm của các đội dựa trên thành tích của các tay đua thành viên.

---

## 🛠️ Công nghệ sử dụng
- **Backend:** PHP 8.2 (Apache)
- **Database:** MySQL 8.0 (InnoDB Engine)
- **Containerization:** Docker & Docker Compose
- **Frontend:** Vanilla CSS (F1 Dark Mode Theme), JavaScript (Form Validation & Toasts)

---

## 💎 Database Optimization & Logic

Dự án này tập trung tối ưu hóa hiệu năng và tính toàn vẹn dữ liệu trực tiếp từ tầng Database:

### 1. Storage Engine & Buffer Tuning
- **Storage Engine:** Sử dụng **InnoDB** để hỗ trợ Transaction (ACID) và Row-level locking, đảm bảo dữ liệu không bị sai sót khi nhiều người cùng cập nhật.
- **Buffer Optimization:** Cấu hình tùy chỉnh `innodb_buffer_pool_size = 256M` giúp cache dữ liệu vào RAM, giảm thiểu đọc ghi ổ cứng (Disk I/O).
- **Direct I/O:** Sử dụng `innodb_flush_method = O_DIRECT` để ghi dữ liệu trực tiếp xuống ổ cứng, tối ưu hóa tốc độ của Engine.

### 2. Indexes (Chỉ mục chiến lược)
Hệ thống sử dụng các Composite Index (Chỉ mục kép) để tối ưu tốc độ truy vấn:
- `idx_tournament_order` (Bảng `stage`): Tối ưu việc lọc chặng theo mùa giải và sắp xếp theo thứ tự.
- `idx_stage_rank` (Bảng `result`): Tối ưu hóa việc xuất bảng xếp hạng chặng đua (`WHERE stage_id` và `ORDER BY finish_rank`).
- `idx_racer_status` (Bảng `racer`): Tối ưu hóa việc lọc danh sách tay đua đang hoạt động.

### 3. Stored Procedures (Thủ tục lưu trữ)
Đóng gói logic nghiệp vụ vào Database để tăng tính bảo mật và hiệu năng:
- `sp_register_racer`: Xử lý đăng ký tay đua một cách nguyên tử (Atomic), kiểm tra quyền (RBAC) và các quy tắc (hết hạn hợp đồng, giới hạn 2 tay đua/đội).
- `sp_save_results`: Sử dụng định dạng **JSON** để cập nhật hàng loạt kết quả đua trong một giao dịch duy nhất.

### 4. Triggers (Trình kích hoạt tự động)
- `trg_assign_score`: Tự động tính toán điểm số dựa trên thứ hạng (1st: 25pts, 2nd: 18pts,...) ngay khi lưu kết quả.
- `trg_validate_time`: Tự động kiểm tra định dạng thời gian và tính hợp lệ của số vòng đua trước khi chèn dữ liệu.

### 5. Views (Bảng ảo thống kê)
- `vw_racer_standings`: Tổng hợp điểm tích lũy và thời gian của tay đua qua tất cả các chặng.
- `vw_team_standings`: Tổng hợp điểm số cho các đội đua (Constructors' Championship).

---

## 📦 Hướng dẫn cài đặt

1. **Yêu cầu:** Máy tính đã cài đặt [Docker Desktop]
2. **Khởi động hệ thống:**
   ```bash
   docker-compose up -d
   ```
3. **Truy cập:**
   - Website: [http://localhost:8081](http://localhost:8081)
   - Database: `localhost:3307` (User: `root` | Pass: `securepassword`)


---
