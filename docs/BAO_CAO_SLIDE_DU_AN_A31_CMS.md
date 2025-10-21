# BÁO CÁO DỰ ÁN A31 CMS
## Phần mềm điều hành sản xuất - Nhà máy A31

---

## **SLIDE 1: TỔNG QUAN DỰ ÁN**

### **Mục đích chính**
- **Chuyển đổi số** trên các mặt công tác quản lý
- **Nền tảng website** tích hợp đầy đủ chức năng
- **Sử dụng chữ ký số nội bộ** khi phê duyệt

### **Phạm vi triển khai**
- Quản lý nhân sự và cơ cấu tổ chức
- Báo cáo quân số và thống kê
- Đăng ký nghỉ phép và phương tiện
- Quản lý sổ sách và hồ sơ
- Workflow phê duyệt với chữ ký số

---

## **SLIDE 2: CÔNG NGHỆ SỬ DỤNG**

### **Nền tảng chính**
- **PHP Laravel** - Khung web hiện đại
- **Cơ sở dữ liệu MySQL** - Lưu trữ dữ liệu
- **Giao diện Bootstrap** - Thiết kế đáp ứng

### **Tính năng đặc biệt**
- **Chữ ký số PDF** - Tích hợp chữ ký Adobe
- **Hệ thống phân quyền** - Quản lý quyền hạn chi tiết
- **Quy trình tự động** - Phê duyệt tự động
- **Báo cáo PDF** - Xuất báo cáo tự động

---

## **SLIDE 3: HỆ THỐNG PHÂN QUYỀN**

### **Cấu trúc phân quyền theo phòng ban**
- **Phòng ban**: Phòng Kế hoạch, Phòng KCS, Phòng Hành chính
- **Phân xưởng**: PX Sửa chữa, PX Sản xuất, PX Lắp ráp

### **Cấp bậc quản lý**
1. **Giám đốc (GD)** - 2. **Chính ủy** - 3. **Phó Giám đốc (PGD)**
4. **Trưởng phòng** - 5. **Phó phòng** - 6. **Quản đốc**
7. **Phó quản đốc** - 8. **Trợ lý/Nhân viên**

### **Phạm vi dữ liệu**
- **Cá nhân** - **Phòng ban** - **Toàn công ty** - **Tất cả**

---

## **SLIDE 4: CÁC PHẦN MỀM ĐÃ THỰC HIỆN**

### **6 Module hoàn thành**
1. **Báo cáo quân số** - Thống kê nhân sự hàng ngày
2. **Cơ cấu tổ chức** - Quản lý phòng ban, phân xưởng
3. **Quản lý nhân sự** - Hồ sơ cán bộ, chứng chỉ
4. **Đăng ký nghỉ phép** - Quy trình phê duyệt tự động
5. **Đăng ký xe** - Quản lý phương tiện, giấy tờ
6. **Quản lý sổ sách** - Lưu trữ tài liệu số hóa

---

# DỰ ÁN ĐIỀU HÀNH SẢN XUẤT - SỬA CHỮA
## Nhà máy A31 - Phòng Kế hoạch

---

## **SLIDE 6: QUY TRÌNH QUẢN LÝ SỬA CHỮA SẢN PHẨM**

### **Giai đoạn 1: Nhập dữ liệu**
- **Nhập sản phẩm** - Tên, số hiệu, dạng sửa chữa
- **Nhập tiến độ** - Ngày sửa chữa, xuất xưởng, bàn giao
- **Nhập phương kỹ thuật** - Phân xưởng, công đoạn, giờ lao động
- **Nhập phương án vật tư** - Danh mục, số lượng, mã hiệu

### **Giai đoạn 2: Thực hiện**
- **Nhập tiến độ thực hiện** - Công đoạn hoàn thành
- **Triển khai thực hiện** - Phân xưởng báo cáo kết quả
- **KCS kiểm tra** - Xác nhận hoàn thành công đoạn

---

## **SLIDE 7: QUY TRÌNH NGHIỆM THU**

### **Giai đoạn 3: Nghiệm thu**
- **Báo cáo hoàn thành** - PX đề nghị nghiệm thu
- **Kiểm tra trước nghiệm thu** - KCS kiểm tra sản phẩm
- **Tổ chức nghiệm thu** - Báo cáo kết quả, hồ sơ sản phẩm

### **Tính năng hệ thống**
- **Theo dõi tiến độ** - Từng công đoạn
- **Báo cáo tự động** - Thống kê thời gian thực
- **Chữ ký số** - Phê duyệt điện tử
- **Lưu trữ hồ sơ** - Quản lý tài liệu

---





---

# PHẦN MỀM QUẢN LÝ VĂN BẢN
## Văn bản đến - Văn bản đi - Nhà máy A31

---

## **SLIDE 6: TỔNG QUAN HỆ THỐNG VĂN BẢN**

### **Mục đích chính**
- **Số hóa quy trình** quản lý văn bản đến và đi
- **Tự động hóa workflow** phê duyệt và ký số
- **Quản lý tập trung** tất cả văn bản nội bộ và bên ngoài
- **Bảo mật cao** cho văn bản mật và thường

### **Phạm vi triển khai**
- Văn bản đến từ các đơn vị trong và ngoài quân đội
- Văn bản đi từ các phòng ban, phân xưởng
- Văn bản mật và văn bản thường
- Quy trình phê duyệt đa cấp với chữ ký số

---

## **SLIDE 7: LUỒNG VĂN BẢN ĐI - THƯỜNG**

### **Quy trình chuẩn**
1. **Dự thảo** - Trợ lý, Nhân viên, Chỉ huy CQ, PX
2. **Trình ký** - Gửi lên cấp trên
3. **Ký bảo đảm** - Chỉ huy CQ, PX
4. **Ký ban hành** - Thủ trưởng Ban Giám đốc
5. **Hoàn thành** - Văn thư xử lý cuối cùng

### **Tính năng hệ thống**
- **Tạo văn bản** - Soạn thảo trực tuyến
- **Phê duyệt đa cấp** - Workflow tự động
- **Chữ ký số** - Ký điện tử an toàn
- **Theo dõi tiến độ** - Trạng thái real-time

---

## **SLIDE 8: LUỒNG VĂN BẢN ĐI - NỘI BỘ**

### **Quy trình nội bộ**
1. **Dự thảo** - Trợ lý, Nhân viên, Chỉ huy CQ, PX
2. **Trình nội bộ** - Chọn "nội bộ" khi gửi
3. **Ký bảo đảm** - Chỉ huy CQ, PX
4. **Duyệt** - Phó Giám đốc phê duyệt
5. **Ký ban hành** - Giám đốc ký chính thức
6. **Hoàn thành** - Văn thư hoàn tất

### **Đặc điểm nội bộ**
- **Phê duyệt trung gian** - Phó Giám đốc duyệt trước
- **Kiểm soát chặt chẽ** - Quy trình 2 cấp
- **Bảo mật cao** - Chỉ nội bộ xử lý
- **Truy xuất nguồn gốc** - Lịch sử đầy đủ

---

## **SLIDE 9: LUỒNG VĂN BẢN ĐI - MẬT**

### **Quy trình văn bản mật**
1. **Dự thảo** - Tạo văn bản mật
2. **Mã hóa** - Sử dụng sản phẩm mật mã
3. **Ký số đầy đủ** - Chữ ký số toàn bộ
4. **Mã hóa chuyển giao** - Bảo mật trong quá trình
5. **Hoàn thành** - Văn thư xử lý cuối

### **Bảo mật đặc biệt**
- **Mã hóa toàn bộ** - Bảo vệ nội dung
- **Chữ ký số nâng cao** - Xác thực mạnh
- **Truy cập hạn chế** - Chỉ người có thẩm quyền
- **Audit trail** - Theo dõi chi tiết

---

## **SLIDE 10: LUỒNG VĂN BẢN ĐẾN - THƯỜNG**

### **Quy trình xử lý**
1. **Tiếp nhận** - Văn thư nhận và vào số
2. **Trình duyệt** - Gửi Ban Giám đốc duyệt
3. **Duyệt văn bản** - Ban Giám đốc phê duyệt
4. **Xử lý** - Trưởng phòng, Quản đốc xử lý
5. **Hoàn thành** - P. Trưởng phòng, P. Quản đốc, Trợ lý, Nhân viên

### **Tính năng quản lý**
- **Phân loại tự động** - Theo loại văn bản
- **Ưu tiên xử lý** - Theo mức độ quan trọng
- **Phân công** - Giao việc tự động
- **Nhắc nhở** - Thông báo tiến độ

---

## **SLIDE 11: LUỒNG VĂN BẢN ĐẾN - MẬT**

### **Quy trình bảo mật**
1. **Tiếp nhận** - Văn thư nhận và vào số
2. **In bản giấy** - Tạo bản cứng
3. **Trình duyệt** - Gửi Ban Giám đốc
4. **Duyệt văn bản** - Ban Giám đốc phê duyệt
5. **Sao văn bản** - Văn thư sao chép
6. **Xử lý** - BGĐ, Trưởng phòng, Quản đốc
7. **Hoàn thành** - P. Trưởng phòng, P. Quản đốc, Trợ lý, Nhân viên

### **Bảo mật đặc biệt**
- **Bản giấy** - In ra để xử lý
- **Sao chép** - Tạo bản sao an toàn
- **Truy cập hạn chế** - Chỉ người được ủy quyền
- **Lưu trữ riêng** - Kho lưu trữ mật

---

## **SLIDE 12: TÍNH NĂNG HỆ THỐNG VĂN BẢN**

### **Quản lý văn bản đến**
- **Tiếp nhận tự động** - Scan và nhận diện
- **Phân loại thông minh** - AI phân loại
- **Phân công tự động** - Giao việc theo quy tắc
- **Theo dõi tiến độ** - Dashboard real-time

### **Quản lý văn bản đi**
- **Soạn thảo trực tuyến** - Editor tích hợp
- **Template chuẩn** - Mẫu văn bản có sẵn
- **Phê duyệt workflow** - Quy trình tự động
- **Chữ ký số** - Ký điện tử an toàn

---

*Báo cáo được chuẩn bị bởi Phòng Kế hoạch - Nhà máy A31*