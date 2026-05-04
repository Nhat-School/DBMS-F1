# 🏎️ F1 Championship System: Kiến trúc & Luồng hoạt động (End-to-End Tour)

Hệ thống Quản lý Giải đua F1 của bạn là một ứng dụng "Database-Centric" (Lấy cơ sở dữ liệu làm trung tâm). Điều này có nghĩa là thay vì để PHP xử lý các logic phức tạp, PHP chỉ đóng vai trò "cầu nối", còn lại phần lớn "bộ não" nằm sâu bên trong MySQL.

Dưới đây là chuyến tham quan chi tiết từ Giao diện (Frontend) -> Xử lý (Backend) -> Lõi Dữ liệu (Database).

---

## 1. 🔐 Luồng Đăng nhập & Xác thực (Authentication)

### Tầng Frontend (HTML/CSS)
- Người dùng truy cập `index.php`. Giao diện hiển thị một form đăng nhập tối giản, hiện đại với hiệu ứng "glassmorphism" (kính mờ).

### Tầng Backend (PHP)
- Form submit dữ liệu `username` và `password` qua phương thức `POST`.
- Code PHP trong `index.php` sẽ gọi tới DB:
  ```php
  SELECT id, username, full_name, role FROM user WHERE username = ? AND password = MD5(?)
  ```
- Nếu đúng, hệ thống lưu thông tin vào `$_SESSION` và chuyển hướng (redirect) tới `dashboard.php`.
- Từ lúc này, mọi file PHP đều gọi hàm `requireLogin()` (trong `database.php`) để chặn những ai cố tình truy cập thẳng vào trang trong mà không đăng nhập.

---

## 2. 🛡️ Giao diện Quản trị & Phân quyền (RBAC)

### Frontend & Backend giao thoa (`header.php` & `database.php`)
- Khi trang web tải lên, hàm `getCurrentUser()` sẽ đọc quyền (`role`) từ Session.
- Nếu người dùng là `staff`:
  - Họ chỉ thấy badge `STAFF` màu nhạt trên góc phải.
  - Nút **Đăng ký (Register)** trên thanh menu bị ẩn đi.
- Nếu người dùng là `admin`:
  - Họ thấy badge `ADMIN` và có thể vào mục Đăng ký.

---

## 3. ✍️ Luồng Đăng ký Tay đua (Registration Flow)
Đây là tính năng độc quyền của Admin, thể hiện rõ nhất cách PHP giao tiếp với Stored Procedure.

### Frontend (`register.php`)
- Admin chọn **Chặng đua (Stage)** và **Đội đua (Team)**.
- Giao diện hiển thị danh sách tay đua. Những tay đua nào bị thương (Injured) hoặc đã đăng ký rồi thì Checkbox sẽ bị khóa (Disabled).

### Backend (PHP)
- Khi Admin tick chọn và nhấn "Lưu", PHP gom danh sách tay đua gửi xuống DB thông qua vòng lặp.
- Lệnh gọi: `CALL sp_register_racer(role, user_id, stage_id, contract_id)`

### Tầng Database (MySQL Stored Procedure)
- Thủ tục `sp_register_racer` kích hoạt và làm 3 việc:
  1. **Bảo mật:** Kiểm tra `p_user_role`. Nếu không phải Admin -> Bắn lỗi SQL, từ chối thực thi.
  2. **Kiểm tra nghiệp vụ (Validation):**
     - Tay đua có trạng thái 'Active' không?
     - Đội đua đã có đủ 2 người ở chặng này chưa? (Ràng buộc tối đa 2 người/đội).
     - Tay đua này đã đăng ký trước đó chưa?
  3. **Lưu trữ (Audit):** Nếu tất cả "qua ải", lệnh `INSERT` mới chạy, đồng thời lưu `user_id` của Admin vào cột `registered_by` để phục vụ Auditing (Lưu vết).

---

## 4. 🏁 Luồng Cập nhật Kết quả (Result Update & Transactions)
Đây là chức năng phức tạp nhất và mạnh mẽ nhất của hệ thống.

### Frontend (`update_results.php`)
- Staff hoặc Admin chọn một Chặng đua.
- Giao diện nạp lên danh sách toàn bộ tay đua đã được duyệt (từ bảng `registration`).
- Nhân viên điền thời gian (ví dụ: `1:22:30`), số vòng chạy (`55`) và trạng thái (`Finished` hoặc `DNF`).

### Backend (PHP Data Prep)
- Vì người dùng có thể gõ thiếu số 0 (`1:2:2`), PHP sử dụng hàm `timeToMs` để quy đổi thời gian ra mili-giây, sau đó sắp xếp danh sách tay đua để tính Hạng (`finish_rank`) từ 1 đến hết.
- Gom toàn bộ dữ liệu này thành một **Chuỗi JSON** và bắn xuống DB trong 1 lệnh duy nhất:
  ```php
  CALL sp_save_results('staff', 1, 2, '[{contract_id: 1, rank: 1, ...}, ...]')
  ```

### Tầng Database (MySQL Transaction & Triggers)
1. **Tiếp nhận & Bung JSON:** Thủ tục `sp_save_results` sử dụng lệnh `JSON_TABLE` để biến chuỗi JSON thành các dòng dữ liệu ảo (như một bảng thật).
2. **Batch Upsert (Cập nhật hàng loạt):** Nó dùng cú pháp `INSERT ... ON DUPLICATE KEY UPDATE`. Lệnh này đẩy toàn bộ dữ liệu vào bảng `RESULT`.
3. **Kích hoạt Triggers (Bộ lọc ngầm):** Trước khi dữ liệu thực sự nằm lại trong ổ cứng, các Trigger trong `02_triggers.sql` bật lên:
   - **`trg_validate_time`:** Dùng Regex `^[0-5]?[0-9]:[0-5]?[0-9]$` kiểm tra xem phút/giây có >= 60 không. Nếu có lỗi? Lập tức bắn `SIGNAL`.
   - **`trg_validate_laps`:** Nếu tay đua ghi là `Finished` nhưng số vòng thấp hơn quy định? Bắn `SIGNAL`.
   - **`trg_assign_score`:** Đọc Hạng (`NEW.finish_rank`) và tự động thưởng điểm (`score`): Hạng 1 (25đ), Hạng 2 (18đ)...
4. **Rollback (Hoàn tác tự động):** Vì `sp_save_results` đã bọc toàn bộ quy trình trong khối `START TRANSACTION`, nên nếu có BẤT KỲ MỘT tay đua nào nhập sai (bị Trigger bắn lỗi), toàn bộ mẻ dữ liệu của cả 40 tay đua sẽ bị từ chối lưu. Giữ cho CSDL luôn sạch sẽ 100%.

---

## 5. 📊 Báo cáo & Thống kê (Views)
Sau khi kết quả được lưu, làm sao để hiển thị bảng xếp hạng mùa giải?

### Tầng Database (MySQL Views)
- Thay vì bắt PHP phải viết lệnh SQL siêu dài `SELECT... JOIN... GROUP BY... SUM()...`, chúng ta đã có `03_views.sql`.
- **`vw_racer_standings`**: Tự động gom nhóm (`GROUP BY racer_id`), cộng tổng điểm (`SUM(score)`) và đếm số lần về nhất.
- **`vw_team_rankings`**: Tự động lấy điểm của cả 2 tay đua trong đội cộng dồn lại với nhau để ra thứ hạng Đội Đua (Constructor Standings).

### Tầng Frontend & Backend (`racer_standings.php`)
- PHP lúc này cực kỳ thảnh thơi, nó chỉ cần gọi:
  ```sql
  SELECT * FROM vw_racer_standings
  ```
- Kết quả trả về đã được tính toán sẵn. PHP chỉ việc đắp HTML/CSS lên là hiện ra một bảng xếp hạng cực ngầu trên màn hình.

---

## 🎯 Tổng kết (The Big Picture)

Hệ thống này được thiết kế theo nguyên lý **"Fat Database, Thin Client"** (Database chịu tải, Code PHP mỏng/nhẹ).
- **PHP** chỉ làm nhiệm vụ vẽ giao diện (UI) và định tuyến (Routing).
- Mọi nghiệp vụ khắt khe nhất: Phân quyền, Ràng buộc dữ liệu, Xử lý giao dịch, Hoàn tác lỗi, Tính toán điểm số... **đều được gói gọn vào trong MySQL**.

Kiến trúc này đảm bảo rằng: Ngay cả khi bạn vứt bỏ toàn bộ source code PHP đi và dùng Python hoặc Node.js kết nối vào, hệ thống vẫn KHÔNG THỂ bị nhập liệu sai quy định, và vẫn tự động tính điểm chính xác tuyệt đối. Đây chính là mục đích lớn nhất của môn học **Hệ quản trị CSDL (DBMS)**.
