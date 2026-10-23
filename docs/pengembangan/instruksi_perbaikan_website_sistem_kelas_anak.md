# Instruksi Perbaikan Website — Sistem Pengelolaan Kelas Anak

## Aturan Utama
- Periksa implementasi yang sudah ada sebelum mengubah kode.
- Terapkan perbaikan langsung pada project; jangan hanya membuat UI atau contoh kode.
- Pertahankan struktur, fitur, alur, dan desain lain yang sudah berjalan.
- Gunakan mockup dan aset project sebagai acuan. Jangan mengganti gambar dengan gambar buatan AI atau gambar pilihan otomatis.
- Uji setiap perbaikan dan laporkan hasilnya. Jangan menyatakan berhasil jika belum diuji.

## 1. Sesuaikan Profile Setting Admin dengan Mockup
Perbaiki halaman **Profile Setting** di Dashboard Admin agar sesuai dengan desain pada:

`docs/design/screens/05-profile-settings.png`

Periksa mockup dan implementasikan layout, komponen, informasi, jarak, serta tampilan yang relevan agar halaman Admin mengikuti referensi tersebut. Jangan mengubah halaman lain yang tidak terkait.

## 2. Perbaiki Gambar Card Beranda Publik
Saat Admin memilih gambar dari komputer melalui **Choose File** untuk card kegiatan di halaman Admin, status penyimpanan menyatakan berhasil, tetapi gambar tidak muncul di beranda publik.

Perbaiki alur upload dan penayangan gambar secara menyeluruh:
- Pastikan file benar-benar diunggah dan disimpan pada lokasi yang sesuai dengan konfigurasi project.
- Pastikan path/URL gambar tersimpan dengan benar di database dan dapat diakses oleh halaman publik.
- Pastikan halaman beranda publik membaca dan menampilkan gambar yang tersimpan, termasuk setelah halaman dimuat ulang.
- Tambahkan penanganan error yang jelas jika upload atau pemuatan gambar gagal.
- Jangan hanya mengubah status menjadi berhasil tanpa memastikan file dan data gambar benar-benar tersimpan serta dapat ditampilkan.

## 3. Tambahkan Fitur Delete pada Tabel Admin
Periksa seluruh halaman/tabel pengelolaan data di Dashboard Admin, termasuk **Region, Teacher, Participant**, dan entitas lain yang sudah memiliki fitur CRUD.

Pastikan setiap tabel yang relevan memiliki tombol/aksi:
- Create
- Read/View
- Update/Edit
- Delete

Ketentuan:
- Implementasikan penghapusan sampai ke backend/database, bukan hanya menambahkan tombol.
- Gunakan konfirmasi sebelum menghapus data.
- Tangani relasi dan foreign key dengan aman agar penghapusan tidak merusak data terkait.
- Tampilkan pesan berhasil/gagal setelah tindakan.
- Pertahankan aturan hak akses yang berlaku.

## 4. Perbaiki Login Akun Guru yang Dibuat Admin
Saat ini akun Guru yang dibuat melalui Dashboard Admin tidak dapat digunakan untuk login.

Telusuri dan perbaiki alur pembuatan akun Guru hingga autentikasi:
- Periksa penyimpanan akun, email/username, password, hashing password, status akun, role, guard/provider, serta proses validasi login sesuai struktur autentikasi project.
- Pastikan akun Guru yang dibuat Admin tersimpan dengan benar dan dapat digunakan untuk login melalui halaman/login Guru yang sesuai.
- Pastikan role dan hak akses Guru tetap benar serta tidak memperoleh akses Admin.
- Jangan melewati autentikasi atau membuat akses khusus yang tidak aman.
- Uji dengan akun Guru yang dibuat melalui Dashboard Admin.

## 5. Gambar Card “Bahá’í Children’s Classes for Spiritual Education”
Pada card **“Bahá’í Children’s Classes for Spiritual Education”** yang ditampilkan di halaman publik dan dikelola melalui Dashboard Admin, gambar harus dipilih dan diatur oleh Admin.

Ketentuan:
- Tambahkan atau perbaiki input **Choose File/upload gambar** pada form Admin untuk card tersebut.
- Gambar harus berasal dari file yang dipilih Admin dan disimpan dengan benar, lalu ditampilkan di card publik.
- Jangan mengambil, membuat, atau mengganti gambar secara otomatis menggunakan AI atau sumber lain.
- Pastikan Admin dapat mengganti gambar dan hasilnya tampil setelah disimpan serta halaman dimuat ulang.

## 6. Pengujian dan Laporan
Setelah perbaikan:
1. Cocokkan Profile Setting Admin dengan `docs/design/screens/05-profile-settings.png`.
2. Uji upload gambar card kegiatan dan card “Bahá’í Children’s Classes for Spiritual Education” dari komputer sampai tampil di beranda publik.
3. Uji tombol Delete pada setiap tabel Admin yang relevan, termasuk konfirmasi dan penanganan relasi.
4. Buat akun Guru melalui Admin, lalu uji login dan pastikan hak aksesnya sesuai.
5. Periksa error pada log, route, validasi, database, dan console jika diperlukan.
6. Laporkan file yang diubah, penyebab utama setiap masalah, perbaikan yang dilakukan, hasil pengujian, dan kendala yang belum terselesaikan.
