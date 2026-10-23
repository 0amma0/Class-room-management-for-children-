# Revisi UI dan Fitur Sistem Pengelolaan Kelas Anak

Lakukan perubahan berikut langsung pada project. Periksa implementasi yang ada seperlunya, pertahankan fitur yang sudah berjalan, dan jangan mengubah bagian yang tidak berkaitan.

## 1. Scroll Navbar Admin dan Guru
Saat ini dashboard Admin dan Guru memiliki scrollbar yang terlihat. Hilangkan tampilan scrollbar tersebut, tetapi navbar tetap harus bisa di-scroll secara vertikal. Jangan sampai menu terpotong atau tidak bisa diakses.

## 2. Tampilan Kelas pada Admin dan Guru
- **Admin:** pada halaman kelas, tampilkan bagian **Refleksi Guru**, nama guru, dan informasi jumlah lesson yang sudah diperbarui. Jangan tampilkan Todo List lesson pada halaman Admin.
- Tambahkan filter wilayah agar Admin dapat melihat pembaruan kelas anak berdasarkan wilayah yang dipilih.
- **Guru:** pertahankan tampilan halaman Classes yang sekarang karena sudah sesuai. Todo List lesson tetap tersedia pada halaman Guru.

## 3. Pisahkan Form Profil dan Ganti Password
Pada halaman profil, pisahkan pengaturan avatar/foto profil dari pengaturan password. Sediakan pilihan atau tombol yang jelas agar pengguna dapat memilih untuk mengubah foto profil melalui form profil atau mengganti password melalui form khusus. Tombol **Save Changes** untuk profil dan tombol penyimpanan password harus terpisah dan menjalankan fungsi masing-masing.

## 4. Perbarui Bagian Communities
- Ganti judul **Our Communities in West Java** menjadi **Our Communities in Indonesia**.
- Pada bagian **West Java Region**, pertahankan keterangan `1 active communities · schematic map, not to geographic scale` sesuai data dan kebutuhan desain yang berlaku.
- Tambahkan filter wilayah berjenjang: pengguna memilih wilayah **West Java**, lalu memilih **klaster**, kemudian memilih **lingkungan** yang memiliki kelas anak.
- Tampilkan lokasi lingkungan yang sesuai pada peta kecil. Gunakan data yang tersedia dan jangan mengarang lokasi atau jumlah komunitas.

## 5. Samakan Halaman Classes dengan Mockup
Ketika menu/navbar **Classes** diklik, halaman yang tampil harus mengikuti mockup `docs/desain/classes.png` semirip mungkin, termasuk tata letak, aset gambar anak, dan elemen visual lainnya. Cari dan gunakan aset yang tersedia di folder `public/images`. Jangan mengganti desain dengan layout buatan sendiri yang berbeda dari mockup.

## 6. Sistem Chat Publik, Admin, dan Guru

Buat sistem chat sederhana yang terintegrasi dengan website.

- **Beranda publik:** pengunjung dapat mengirim pertanyaan atau melaporkan masalah melalui widget chat.
- **Dashboard Admin:** Admin dapat menerima dan membalas chat dari pengunjung publik maupun Guru dalam satu halaman.
- **Dashboard Guru:** Guru dapat menghubungi Admin jika mengalami masalah atau membutuhkan bantuan.

Pikirkan sendiri solusi terbaik untuk autentikasi dan notifikasi, terutama bagi pengunjung publik yang belum login. Pilih mekanisme paling sederhana agar pengunjung tetap bisa menerima balasan, misalnya kode atau tautan percakapan yang dapat digunakan kembali, atau opsi kontak jika diperlukan. Pastikan mekanisme notifikasi benar-benar berfungsi dan jelaskan pilihan yang diterapkan.

Simpan pesan dan riwayat percakapan dengan benar. Batasi akses berdasarkan hak pengguna, pastikan Guru hanya dapat mengakses percakapannya sendiri, dan Admin dapat mengelola semua percakapan yang menjadi tanggung jawabnya.

Gunakan teknologi dan struktur project yang sudah tersedia. Hindari sistem yang terlalu rumit jika solusi sederhana sudah mencukupi. Implementasikan langsung, uji pengiriman dan balasan pesan, lalu laporkan hasilnya secara singkat.


## Ketentuan
Kerjakan perubahan secepat mungkin dan langsung pada project. Jangan memberi analisis panjang atau berhenti di tahap perencanaan. Setelah selesai, periksa fungsi dan tampilan yang diubah, lalu laporkan secara singkat file yang diubah dan hasil pengujian. Jika ada data atau aset yang tidak tersedia, sampaikan dengan jelas dan jangan mengarangnya.
