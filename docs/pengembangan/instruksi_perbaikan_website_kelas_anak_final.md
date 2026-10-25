# Instruksi Perbaikan dan Audit Website Sistem Pengelolaan Kelas Anak

## Tujuan Utama

Lakukan perbaikan pada fitur dan UI yang disebutkan di bawah ini tanpa
mengubah alur kerja, logika bisnis, struktur, atau tampilan yang sudah
berjalan dengan baik di bagian lain website. Sebelum mengubah kode,
periksa struktur project, implementasi yang sudah ada, relasi database,
route, controller, view, middleware, serta pola desain yang digunakan.

**Penting:** pertahankan teknologi, pola implementasi, dan cara
perbaikan yang sudah digunakan dalam project. Jangan melakukan refactor
besar atau mengganti pendekatan yang sudah ada jika tidak diperlukan.
Hindari perubahan di luar ruang lingkup instruksi ini.

## 1. Rapikan Form Edit UI Participants

-   Perbaiki tampilan **form edit Participants** yang saat ini masih
    berantakan.
-   Rapikan tata letak field, label, jarak antarelemen, ukuran input,
    tombol aksi, serta responsivitasnya.
-   Gunakan gaya desain yang konsisten dengan halaman dan komponen lain
    yang sudah ada.
-   Pertahankan seluruh field, validasi, proses penyimpanan, serta
    perilaku form yang sudah berjalan.
-   Pastikan form nyaman digunakan pada ukuran layar yang didukung dan
    tidak menyebabkan elemen bertumpuk atau terpotong.

## 2. Perbaiki Pilihan Region, Klaster, dan Lingkungan pada Penugasan Kelas dan Guru Utama

Pada bagian **Penugasan Kelas dan Guru Utama**, pilihan lingkungan saat
ini belum menampilkan seluruh lingkungan yang tersedia.

Ubah pemilihan lokasi menjadi filter bertingkat seperti berikut:

1.  Pengguna memilih **Region** terlebih dahulu.
2.  Setelah Region dipilih, tampilkan **Klaster** yang sesuai dengan
    Region tersebut.
3.  Setelah Klaster dipilih, tampilkan seluruh **Lingkungan** yang
    terdaftar pada Klaster tersebut.

Ketentuan: - Pastikan seluruh data lingkungan yang valid tersedia dan
dapat dipilih. - Setiap pilihan harus mengikuti relasi data yang benar
di database. - Ketika Region diubah, reset pilihan Klaster dan
Lingkungan yang sudah tidak sesuai. - Ketika Klaster diubah, reset
pilihan Lingkungan yang sudah tidak sesuai. - Pastikan data yang
tersimpan tetap menggunakan ID atau relasi yang benar sesuai
implementasi project saat ini. - Terapkan perilaku filter yang konsisten
dengan filter lokasi yang sudah tersedia di form lain. - Periksa
validasi, data lama, serta kemungkinan nilai kosong agar perubahan tidak
menimbulkan error.

## 3. Tambahkan Tombol Delete All pada Activity Review

Pada bagian **Activity Review**, tambahkan tombol **Delete All** agar
daftar aktivitas dapat dibersihkan ketika diperlukan.

Ketentuan: - Gunakan gaya tombol dan pola interaksi yang konsisten
dengan UI yang sudah ada. - Tampilkan konfirmasi sebelum seluruh data
aktivitas dihapus. - Jelaskan dengan jelas bahwa tindakan tersebut akan
menghapus semua item pada daftar terkait. - Pastikan proses penghapusan
hanya memengaruhi data **Activity Review** yang memang menjadi target,
bukan data lain. - Terapkan pemeriksaan otorisasi dan validasi yang
sesuai dengan role pengguna. - Tampilkan notifikasi hasil yang jelas
ketika proses berhasil atau gagal. - Pastikan daftar diperbarui setelah
penghapusan selesai dan tidak menampilkan data lama akibat cache atau
state yang belum diperbarui.

## 4. Tambahkan Tombol Delete All pada Available Backups

Pada bagian **Available Backups**, tambahkan tombol **Delete All** untuk
menghapus daftar backup jika diperlukan.

Ketentuan: - Tampilkan konfirmasi sebelum penghapusan. - Pastikan
cakupan penghapusan jelas dan sesuai dengan item backup yang dipilih
sebagai target. - Periksa bagaimana backup disimpan, termasuk apakah
berupa file fisik, metadata database, atau keduanya. - Jika penghapusan
memang diizinkan, hapus file dan metadata terkait secara konsisten agar
tidak meninggalkan data yatim atau tautan yang rusak. - Jangan menghapus
backup di luar cakupan yang dimaksud. - Terapkan otorisasi sesuai role,
penanganan error, serta notifikasi keberhasilan atau kegagalan. -
Pastikan daftar Available Backups diperbarui setelah proses selesai.

## 5. Batasi Fitur Read dan Download pada Halaman Class

Pada halaman **Class**, fitur **Read** dan **Download** tidak boleh
langsung diakses oleh pengguna tanpa izin admin.

Alur yang diharapkan: 1. Pengguna memilih Read atau Download. 2. Sistem
meminta izin admin terlebih dahulu atau membuat permintaan izin sesuai
mekanisme yang paling sesuai dengan arsitektur project saat ini. 3.
Konten atau file hanya dapat dibaca atau diunduh setelah izin admin
diberikan. 4. Jika izin belum diberikan atau ditolak, pengguna tidak
boleh memperoleh akses ke konten atau file tersebut.

Ketentuan: - Periksa dan terapkan pembatasan di sisi backend, bukan
hanya menyembunyikan tombol pada UI. - Pastikan route, endpoint, dan URL
file tidak dapat digunakan untuk melewati pemeriksaan izin. - Buat atau
gunakan status permintaan izin yang jelas, misalnya menunggu, disetujui,
atau ditolak, jika sesuai dengan struktur yang sudah ada. - Admin harus
dapat memeriksa dan menindaklanjuti permintaan sesuai alur yang
diterapkan. - Berikan pesan yang jelas kepada pengguna mengenai status
izin. - Pertahankan fitur Class lain yang tidak berkaitan dengan
pembatasan ini. - Jangan mengubah alur akses lain yang sudah berjalan
dengan baik tanpa alasan teknis yang jelas.

## 6. Sinkronisasi Avatar Guru

Ketika guru mengganti avatar melalui halaman **Profile**, avatar yang
baru harus ikut diperbarui di **Dashboard Guru** dan bagian lain yang
menampilkan avatar guru tersebut.

Ketentuan: - Gunakan satu sumber data avatar yang konsisten. - Pastikan
perubahan tersimpan dengan benar dan langsung tercermin setelah profil
diperbarui atau halaman dimuat ulang. - Periksa kemungkinan cache, path
file, relasi akun, serta fallback jika avatar belum tersedia atau gagal
dimuat. - Jangan sampai avatar guru lain ikut berubah. - Pertahankan
fungsi edit profil dan komponen dashboard yang sudah ada.

## 7. Tambahkan Fitur Hapus pada Gallery Kelas Guru

Pada **Gallery Kelas Guru**, tambahkan fitur untuk menghapus gambar yang
sebelumnya sudah diunggah atau ditambahkan.

Ketentuan: - Setiap item galeri yang dapat dikelola guru harus memiliki
aksi hapus yang jelas. - Tampilkan konfirmasi sebelum gambar dihapus. -
Pastikan guru hanya dapat menghapus gambar yang memang menjadi hak
aksesnya sesuai aturan project. - Periksa penyimpanan gambar dan
metadata database agar penghapusan dilakukan secara konsisten. - Tangani
kondisi ketika file sudah tidak tersedia atau proses penghapusan
gagal. - Perbarui tampilan galeri setelah penghapusan tanpa memerlukan
tindakan yang membingungkan. - Pertahankan fitur tambah/unggah gambar
yang sudah ada.

## 8. Tambahkan Animasi pada Beranda Publik

Tambahkan animasi UI pada **Beranda Publik** dengan memanfaatkan aset
dan komponen yang sudah tersedia di dalam project.

Ketentuan: - Animasi hanya menjadi peningkatan visual; jangan mengubah
desain asli, struktur halaman, konten, atau alur navigasi yang sudah
ada. - Pertahankan identitas visual, warna, tipografi, komposisi, dan
responsivitas halaman. - Gunakan animasi yang halus dan tidak
berlebihan, misalnya transisi saat elemen muncul, hover ringan, atau
gerakan dekoratif yang sesuai dengan desain yang ada. - Prioritaskan
penggunaan aset lokal yang sudah tersedia. Jangan mengganti aset yang
ada tanpa kebutuhan. - Hindari animasi yang menyebabkan layout bergeser,
teks sulit dibaca, performa menurun, atau elemen menjadi sulit diklik. -
Hormati preferensi `prefers-reduced-motion` jika menggunakan CSS atau
mekanisme sejenis. - Pastikan animasi tetap berfungsi pada ukuran layar
yang didukung dan tidak menimbulkan error JavaScript atau CSS.

## 9. Pengujian Menyeluruh Semua Fitur dan UI

Setelah semua perubahan diterapkan, lakukan pengujian terhadap fitur
yang diubah serta fitur lain yang berpotensi terdampak.

### Pengujian minimum

-   Form edit Participants: tampilan, validasi, penyimpanan, dan
    pemuatan data.
-   Penugasan Kelas dan Guru Utama: filter Region → Klaster →
    Lingkungan, kelengkapan pilihan, perubahan filter, validasi, dan
    penyimpanan.
-   Activity Review: Delete All, konfirmasi, otorisasi, pembaruan
    daftar, dan penanganan error.
-   Available Backups: Delete All, konfirmasi, konsistensi file dan
    metadata, otorisasi, serta pembaruan daftar.
-   Halaman Class: permintaan izin Read/Download, persetujuan dan
    penolakan admin, serta pencegahan akses langsung melalui URL atau
    endpoint.
-   Profil Guru: perubahan avatar dan sinkronisasi pada dashboard serta
    komponen terkait.
-   Gallery Kelas Guru: unggah/tambah, tampilkan, hapus, dan pemeriksaan
    hak akses.
-   Beranda Publik: animasi, tampilan responsif, navigasi, dan
    konsistensi desain.
-   Dashboard Admin dan Dashboard Guru: pastikan keduanya tetap dapat
    digunakan tanpa error.
-   Login, logout, navigasi, notifikasi, dan fitur lain yang berhubungan
    dengan perubahan.

### Standar pengujian

-   Periksa error pada browser console, log aplikasi, request jaringan,
    validasi form, dan proses backend yang relevan.
-   Periksa route, controller, query, relasi database, middleware,
    permission, dan file storage yang terdampak.
-   Uji kondisi data kosong, data tidak valid, pengguna tanpa izin,
    kegagalan proses, serta data yang sudah ada.
-   Jangan menyatakan semua pengujian berhasil jika belum benar-benar
    dijalankan.
-   Jika ditemukan bug, perbaiki dan ulangi pengujian terkait.
-   Jika ada pengujian yang tidak dapat dilakukan, jelaskan alasannya
    dan sebutkan bagian yang masih perlu diuji secara manual.
-   Jangan menghapus atau mereset data pengguna yang sudah ada untuk
    keperluan pengujian tanpa izin.

## 10. Dokumentasi Lengkap Website

Setelah implementasi dan pengujian selesai, buat dokumentasi teknis yang
lengkap, akurat, dan sesuai dengan kondisi project yang benar-benar ada.
Dokumentasi harus menjelaskan website secara rinci, bukan hanya
mencantumkan daftar fitur.

Dokumentasikan setidaknya bagian berikut:

### A. Gambaran Umum

-   Tujuan dan fungsi utama website Sistem Pengelolaan Kelas Anak.
-   Jenis pengguna dan perbedaan hak akses setiap role.
-   Alur penggunaan website dari awal sampai akhir.

### B. Daftar Fitur

Untuk setiap fitur, jelaskan: - Nama fitur dan lokasi menu/halamannya. -
Tujuan dan kegunaannya. - Role yang dapat mengaksesnya. - Cara kerja dan
alur penggunaannya. - Data yang dibaca atau diubah. - Validasi dan
pembatasan akses yang diterapkan. - Hubungannya dengan fitur lain.

### C. Teknologi dan Tools

-   Bahasa pemrograman, framework, library, dan tools yang benar-benar
    digunakan.
-   Fungsi masing-masing teknologi di dalam project.
-   Versi yang dapat diverifikasi dari konfigurasi project.
-   Tools untuk database, frontend, backend, pengelolaan aset,
    pengujian, dan kebutuhan lain yang relevan.
-   Jangan mencantumkan teknologi atau versi yang tidak dapat
    diverifikasi.

### D. Struktur Project

-   Struktur folder dan file utama.
-   Fungsi folder penting, termasuk route, controller, model, view,
    middleware, konfigurasi, migration, seeder, public assets, dan
    storage jika tersedia.
-   Lokasi implementasi fitur-fitur utama.
-   Jelaskan berdasarkan struktur project yang benar-benar ada, bukan
    struktur asumsi.

### E. Dokumentasi Database

-   Nama database dan DBMS yang digunakan sesuai konfigurasi.
-   Daftar tabel yang ditemukan beserta fungsi masing-masing.
-   Kolom penting, primary key, foreign key, dan relasi antartabel.
-   Hubungan data Region, Klaster, Lingkungan, Kelas, Guru,
    Participants, galeri, aktivitas, backup, permintaan izin, dan data
    lain yang benar-benar ada.
-   Jelaskan migration, model, relasi, serta mekanisme penyimpanan data
    jika digunakan.
-   Sertakan ERD atau diagram relasi jika memungkinkan berdasarkan
    struktur database aktual.
-   Jangan menampilkan password, token, secret, atau kredensial
    sensitif.

### F. Alur Teknis

-   Alur request dari UI ke route, controller, model/database, dan
    kembali ke UI sesuai arsitektur yang digunakan.
-   Alur login, autentikasi, otorisasi, dan pembatasan akses.
-   Alur unggah dan hapus file.
-   Alur backup dan penghapusan backup.
-   Alur permintaan izin Read/Download.
-   Alur sinkronisasi avatar.
-   Jelaskan proses yang memang ada; tandai bagian yang belum
    diimplementasikan jika ditemukan.

### G. Pengujian dan Hasil

-   Daftar skenario pengujian.
-   Status setiap pengujian: berhasil, gagal, atau belum diuji.
-   Bug yang ditemukan dan perbaikannya.
-   Risiko atau keterbatasan yang masih tersisa.
-   Langkah pengujian manual yang perlu dilakukan pengguna jika
    diperlukan.

### H. Petunjuk Penggunaan dan Pemeliharaan

-   Cara menjalankan website di lingkungan pengembangan.
-   Konfigurasi penting tanpa membuka rahasia atau kredensial.
-   Cara menjalankan migration atau seeder jika relevan.
-   Cara mengelola file upload dan backup.
-   Saran backup, pemeliharaan, dan troubleshooting berdasarkan
    implementasi aktual.

Simpan dokumentasi dalam file Markdown (`.md`) di folder dokumentasi
project yang paling sesuai dengan struktur yang sudah ada. Jika sudah
ada dokumentasi utama, perbarui atau lengkapi dokumen tersebut daripada
membuat dokumentasi duplikat yang membingungkan.

## 11. Ketentuan Implementasi

1.  **Audit sebelum mengubah:** periksa kode dan struktur yang sudah ada
    terlebih dahulu.
2.  **Perubahan terarah:** ubah hanya bagian yang diperlukan untuk
    memenuhi instruksi.
3.  **Jaga kompatibilitas:** jangan merusak UI, fitur, route, data, atau
    alur kerja yang sudah berjalan.
4.  **Ikuti pola project:** gunakan konvensi penamaan, komponen,
    validasi, otorisasi, dan struktur kode yang sudah diterapkan.
5.  **Keamanan backend:** semua operasi penting dan pembatasan izin
    harus divalidasi di sisi server.
6.  **Penanganan error:** tampilkan pesan yang jelas kepada pengguna dan
    catat detail teknis melalui mekanisme logging yang sesuai.
7.  **Jangan mengarang hasil:** dokumentasi harus sesuai dengan kode,
    database, dan pengujian aktual.
8.  **Jangan berhenti pada perubahan UI:** pastikan alur backend,
    database, hak akses, dan penyimpanan ikut berfungsi.
9.  **Jangan mengubah hal yang tidak diminta:** jika ada perubahan
    tambahan yang benar-benar diperlukan, jelaskan alasan dan dampaknya.
10. **Laporan akhir:** setelah selesai, berikan ringkasan perubahan,
    daftar file yang diubah, hasil pengujian, kendala yang belum
    terselesaikan, dan lokasi dokumentasi Markdown yang dibuat atau
    diperbarui.

## Kriteria Selesai

Pekerjaan dinyatakan selesai apabila semua perubahan yang diminta sudah
diimplementasikan sesuai arsitektur project, fitur terkait telah diuji,
dashboard Admin dan Guru tetap berjalan, tidak ada regresi yang
diketahui pada fitur terdampak, serta dokumentasi teknis lengkap telah
dibuat berdasarkan kondisi project yang sebenarnya.
